<?php

class Cron_Catalog_Order_PushOrderDeskMaster extends OSC_Cron_Abstract {

    const CRON_TIMER = '* * * * *';
    const CRON_SCHEDULER_FLAG = 0;
    const ORDER_PROCESS_TABLE = 'catalog_order_process_v2';

    public function process($data, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '2000M');
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 5000;
        $counter = 0;
        $count_error = 0;

        while ($counter < $limit && $count_error < 4) {
            try {
                $DB->query('SELECT * FROM ' . OSC::systemRegistry('db_prefix') . self::ORDER_PROCESS_TABLE . ' WHERE queue_flag = 0 ORDER BY RAND() LIMIT 1', null, 'fetch_queue');

                $row = $DB->fetchArray('fetch_queue');

                $DB->free('fetch_queue');

                if (!isset($row)) {
                    $this->_log('not found row');
                    break;
                }

                $DB->update(self::ORDER_PROCESS_TABLE, ['queue_flag' => 1], 'record_id=' . $row['record_id'], 1, 'update_queue');

                if ($DB->getNumAffected('update_queue') != 1) {
                    $this->_log('update_queue != 1 at' . $row['record_id']);
                    break;
                }
            } catch(Exception $ex) {
                $count_error ++ ;
                continue;
            }

            $counter ++;

            $DB->begin();

            $locked_key = OSC::makeUniqid();

            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                $data_request = array(
                    'ukey' => $row['ukey'],
                );

                $url = '/orderdesk/masterSync/processed';

                $response =  OSC::helper('master/common')->callApi($url,$data_request);

                if ($response['flag'] = 'processed'){
                    $DB->update(self::ORDER_PROCESS_TABLE, ['queue_flag' => 4,'error_message' => ''], 'record_id=' . $row['record_id'], 1, 'update_queue');
                } else {
                    throw new Exception($response);
                }

                $DB->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

            } catch (Exception $ex) {
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                $DB->update(self::ORDER_PROCESS_TABLE, ['queue_flag' => 3, 'error_message' =>'queue_flag : ' . $row['queue_flag'] . ' / ' . $ex->getMessage(),'modified_timestamp' => time()], 'record_id=' . $row['record_id'], 1, 'update_queue');
                continue;
            }

            if ($counter >= $limit) {
                OSC::core('cron')->addQueue('catalog/order_pushOrderDeskMaster', null, ['ukey'=> 'catalog/order_pushOrderDeskMaster_1','requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60*60]);
                OSC::core('cron')->addQueue('catalog/order_pushOrderDeskMaster', null, ['ukey'=> 'catalog/order_pushOrderDeskMaster_2','requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60*60]);
                OSC::core('cron')->addQueue('catalog/order_pushOrderDeskMaster', null, ['ukey'=> 'catalog/order_pushOrderDeskMaster_3','requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60*60]);
                OSC::core('cron')->addQueue('catalog/order_pushOrderDeskMaster', null, ['ukey'=> 'catalog/order_pushOrderDeskMaster_4','requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60*60]);
                OSC::core('cron')->addQueue('catalog/order_pushOrderDeskMaster', null, ['ukey'=> 'catalog/order_pushOrderDeskMaster_5','requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60*60]);
                return;
            }

        }
    }
}
