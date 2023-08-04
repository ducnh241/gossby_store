<?php

class Cron_Klaviyo_Push extends OSC_Cron_Abstract {

    const CRON_SCHEDULER_FLAG = 0;

    public function process($data, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 100000;
        $counter = 0;

        while ($counter < $limit) {
            $DB->select('*', 'catalog_klaviyo_queue', 'queue_flag = ' . Model_Klaviyo_Item::FLAG_QUEUE_DEFAULT , 'record_id ASC', 1, 'fetch_queue_klaviyo');

            $row = $DB->fetchArray('fetch_queue_klaviyo');

            $DB->free('fetch_queue_klaviyo');

            if (!$row) {
                break;
            }

            $flag_current = $row['queue_flag'];

            $DB->update('catalog_klaviyo_queue', ['queue_flag' => Model_Klaviyo_Item::FLAG_QUEUE_RUNNING], 'queue_flag = '.Model_Klaviyo_Item::FLAG_QUEUE_DEFAULT.' and record_id=' . $row['record_id'], 1, 'update_queue_klaviyo');

            if ($DB->getNumAffected('update_queue_klaviyo') != 1) {
                $this->_log('update_queue_klaviyo != 1 at' . $row['record_id']);
                break;
            }

            $counter ++;

            $DB->begin();

            $locked_key = OSC::makeUniqid();

            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                $DB->delete('catalog_klaviyo_queue', 'record_id=' . $row['record_id'], 1, 'delete_queue_klaviyo');

                $data = is_array($row['data']) ? OSC::encode($row['data']) : $row['data'];

                $response = OSC::core('network')->curl(Helper_Klaviyo_Common::KLAVIYO_HOST. '/api/track', array(
                        'request_method' => 'POST',
                        'headers' => [
                            'Accept' => 'text/html',
                            'Content-Type' => 'application/x-www-form-urlencoded'
                        ],
                        'data' => 'data=' . rawurlencode($data)
                    )
                );

                if (!isset($response['content']) || $response['content'] != 1) {
                    $data = OSC::decode($data);
                    $message = 'klaviyo not save data tracking with id: '.$row['record_id'].', email: ' . $data['customer_properties']['$email'] . ' and order_code: ' . ($data['properties']['order_code'] ?? $data['properties']['$event_code']);
                    $telegram_group_id = OSC::helper('core/setting')->get('notify_klaviyo/telegram_group_id');

                    if ($telegram_group_id) {
                        OSC::helper('core/telegram')->sendMessage($message, $telegram_group_id);
                    }

                    throw new Exception('klaviyo not save data tracking');
                }

                $DB->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

            } catch (Exception $ex) {
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                $error_message = substr($ex->getMessage(), 0, 250);
                $DB->update('catalog_klaviyo_queue', ['queue_flag' => Model_Klaviyo_Item::FLAG_QUEUE_ERROR, 'error_message' => $flag_current . '/' . $error_message], 'record_id=' . $row['record_id'], 1, 'update_queue_klaviyo');

                break;
            }

            if($counter >= $limit) {
                OSC::core('cron')->addQueue('klaviyo/push', null, ['ukey' => 'klaviyo/push', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60*60]);
            }

        }
    }

}
