<?php

class Cron_MasterSync_Sync extends OSC_Cron_Abstract {

    const CRON_TIMER = '* * * * *';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($data, $queue_added_timestamp) {
        $store_info = OSC::getStoreInfo();

        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 10000;
        $counter = 0;

        while ($counter < $limit) {
            $DB->select('*', Helper_MasterSync_Common::TBL_QUEUE_NAME, '`syncing_flag` = 0 OR modified_timestamp < ' . (time() - 60), '`running_timestamp` ASC, queue_id ASC', 1, 'fetch_queue');
            
            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row || ($row['syncing_flag'] == 1 && $row['modified_timestamp'] > (time() - 60))) {
                break;
            }

            $DB->query('UPDATE ' . OSC::systemRegistry('db_prefix') . Helper_MasterSync_Common::TBL_QUEUE_NAME . ' SET syncing_flag = 1, modified_timestamp = ' . time() . ' WHERE queue_id=' . $row['queue_id'] . ' AND (syncing_flag=0 OR modified_timestamp <' . (time() - 60) . ') LIMIT 1', null, 'update_queue');

            if ($DB->getNumAffected('update_queue') != 1) {
                break;
            }

            $sync_key = OSC::makeUniqid();

            $counter ++;

            $DB->begin();

            $locked_key = OSC::makeUniqid();

            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                $collected_data = ['sync_key' => $sync_key];

                OSC::core('observer')->dispatchEvent('masterSync:' . $row['sync_key'], ['collected_data' => &$collected_data, 'sync_data' => OSC::decode($row['sync_data'])]);

                $request_data = [
                    'key' => $row['sync_key'],
                    'data' => $collected_data
                ];

                $response = OSC::core('network')->curl(
                        trim($store_info['master_store_url']) . '/masterSync/index/receive', [
                    'headers' => ['Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum(OSC::encode($request_data), $store_info['secret_key'])],
                    'json' => $request_data
                ]);

                if (!is_array($response['content']) || !isset($response['content']['result'])) {
                    throw new Exception('Response data is incorrect: ' . print_r($response['content'], 1));
                }

                if ($response['content']['result'] != 'OK') {
                    throw new Exception($response['content']['message']);
                }

                if (!isset($response['content']['data']['synchronized']) || $response['content']['data']['synchronized'] != 1){
                    throw new Exception('Sync result is fail: ' . print_r($response['content'], 1));
                }

                $DB->delete(Helper_MasterSync_Common::TBL_QUEUE_NAME, 'queue_id=' . $row['queue_id'], 1, 'delete_queue');

                $DB->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);
            } catch (Exception $ex) {
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                $DB->update(Helper_MasterSync_Common::TBL_QUEUE_NAME, ['syncing_flag' => 0, 'modified_timestamp' => time(), 'error_message' => $ex->getMessage()], 'queue_id=' . $row['queue_id'], 1, 'update_queue');

                break;
            }
        }
    }

}
