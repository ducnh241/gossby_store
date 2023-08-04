<?php

class Helper_PostOffice_Email {

    public static function getTokenMarker() {
        static $marker = null;

        if ($marker === null) {
            $marker = '--email_token.' . OSC::makeUniqid() . '--';
        }

        return $marker;
    }

    public function create($data, $force_make_queue = false) {
        $email_queue = OSC::model('postOffice/email_queue');
        $email_queue->setData($data)->save();

        if ($email_queue->data['running_timestamp'] <= time() || $force_make_queue) {
            $queue_options = ['requeue_limit' => -1, 'estimate_time' => 60];

            if ($email_queue->data['running_timestamp'] > time()) {
                $queue_options[] = 'skip_realtime';
                $queue_options['running_time'] = $email_queue->data['running_timestamp'];
            }

//            OSC::core('cron')->addQueue('postOffice/email_queue_send', ['id' => $email_queue->getId()], $queue_options);
        }
    }

    public function getClickUrl(string $url): string {
        return OSC::getUrl('postOffice/email/click', ['token' => Helper_PostOffice_Email::getTokenMarker(), 'ref' => base64_encode($url)], false);
    }

    public function getViewUrl(): string {
        return OSC::getUrl('postOffice/email/view', ['token' => Helper_PostOffice_Email::getTokenMarker()], false);
    }

    public function getTrackingContent(): string {
        return '<img width="1px" height="1px" alt="" src="' . OSC::getUrl('postOffice/email/track', ['token' => Helper_PostOffice_Email::getTokenMarker()], false) . '?t=' . time() . '" />';
    }

    public function getUnsubsribingUrl() {
        return OSC::getUrl('postOffice/email/unsubscribing', ['token' => Helper_PostOffice_Email::getTokenMarker()], false);
    }

    public function executeQueue(Model_PostOffice_Email_Queue $queue) {
        if ($queue->data['running_timestamp'] > time()) {
            return;
        }

        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        $queue_id = $queue->getId();

        try {
            $email_data = [];

            foreach (['token', 'email_key', 'member_id', 'note', 'sender_name', 'sender_email', 'receiver_name', 'receiver_email', 'subject'] as $key) {
                $email_data[$key] = $queue->data[$key];
            }

            OSC::model('postOffice/email')->setData($email_data)->save();

            $queue->delete();

            $callback = is_array($queue->data['email_callback']) ? $queue->data['email_callback'] : [];

            $validate_result = true;

            if (isset($callback['validate'])) {
                $validator = OSC::helper($callback['validate']['helper']);
                $validate_result = call_user_func_array([$validator, $callback['validate']['function']], $callback['validate']['params']);
            }

            if ($validate_result) {
                if ($queue->data['attachments']) {
                    $attachments = OSC::decode($queue->data['attachments']);
                } else {
                    $attachments = [];
                }

                if ($queue->data['html_content']) {
                    OSC::helper('core/mailer')->sendHTML($queue->data['receiver_email'], $queue->data['subject'], $queue->data['html_content'], $attachments, $queue->data['text_content'] ? $queue->data['text_content'] : '', ['sender_email' => $queue->data['sender_email'], 'sender_name' => $queue->data['sender_name']]);
                } else {
                    OSC::helper('core/mailer')->send($queue->data['receiver_email'], $queue->data['subject'], $queue->data['text_content'], $attachments, ['sender_email' => $queue->data['sender_email'], 'sender_name' => $queue->data['sender_name']]);
                }
            }

            $DB->commit();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);
        } catch (Exception $ex) {
            $DB->rollback();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            $DB->update($queue->getTableName(), ['state' => 'error', 'error_message' => substr($ex->getMessage(), 0, 255)], $queue->getPkFieldName() . "=" . $queue_id, 1, 'update_email_queue');
        }
    }

}
