<?php

class Cron_SMSMarketing_KlaviyoPush extends OSC_Cron_Abstract
{
    const CRON_SCHEDULER_FLAG = 0;

    public function process($data, $queue_added_timestamp)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 100000;
        $counter = 0;

        $klaviyo_sms_list_id = OSC::helper('core/setting')->get('marketing/klaviyo/sms/list_id');

        while ($counter < $limit) {
            $DB->select('*', 'catalog_klaviyo_sms_queue', 'queue_flag = 0', 'record_id ASC', 1, 'fetch_klaviyo_sms_queue');
            $row = $DB->fetchArray('fetch_klaviyo_sms_queue');
            $DB->free('fetch_klaviyo_sms_queue');

            if (!$row) {
                break;
            }

            $flag_current = $row['queue_flag'];

            $DB->update('catalog_klaviyo_sms_queue', ['queue_flag' => 1], 'queue_flag = 0 and record_id=' . $row['record_id'], 1, 'update_klaviyo_sms_queue');

            if ($DB->getNumAffected('update_klaviyo_sms_queue') != 1) {
                $this->_log('update_klaviyo_sms_queue != 1 at' . $row['record_id']);
                break;
            }

            $counter++;

            $DB->begin();

            $locked_key = OSC::makeUniqid();

            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                $DB->delete('catalog_klaviyo_sms_queue', 'record_id=' . $row['record_id'], 1, 'delete_queue_klaviyo');
                $data = $row['data'];
                $response = OSC::core('network')->curl('https://a.klaviyo.com/api/v2/list/' . $klaviyo_sms_list_id . '/subscribe',
                    [
                        'request_method' => 'POST',
                        'headers' => ['Content-Type' => "application/json"],
                        'data' => $data
                    ]
                );

                if (isset($response['content']['detail'])) {
                    throw new Exception($response['content']['detail']);
                }

                if (!isset($response['content'][0]['email']) || $response['content'][0]['email'] == '') {
                    throw new Exception('klaviyo not save data tracking');
                }

                $DB->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

            } catch (Exception $ex) {
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                $error_message = substr($ex->getMessage(), 0, 250);
                $DB->update('catalog_klaviyo_sms_queue', ['queue_flag' => 2, 'error_message' => $flag_current . '/' . $error_message], 'record_id=' . $row['record_id'], 1, 'catalog_klaviyo_sms_queue');

                break;
            }

            if ($counter >= $limit) {
                OSC::core('cron')->addQueue('smsMarketing/klaviyoPush', null, ['requeue_limit' => -1, 'estimate_time' => 60 * 60]);
            }

        }
    }

}
