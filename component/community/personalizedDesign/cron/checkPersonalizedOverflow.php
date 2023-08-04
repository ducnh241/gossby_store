<?php

class Cron_PersonalizedDesign_CheckPersonalizedOverflow extends OSC_Cron_Abstract {

    const TBL_NAME = 'catalog_item_overflow_queue';
    const CRON_TIMER = '* * * * *';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($data, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $store_info = OSC::getStoreInfo();
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 10000;
        $counter = 0;

        while ($counter < $limit) {
            $DB->select('*', static::TBL_NAME, 'queue_flag = 1' , 'record_id ASC', 1, 'fetch_queue_overflow');

            $row = $DB->fetchArray('fetch_queue_overflow');

            $DB->free('fetch_queue_overflow');

            if (!$row) {
                break;
            }

            $DB->update(static::TBL_NAME, ['queue_flag' => 0], 'queue_flag = 1 and record_id=' . $row['record_id'], 1, 'update_queue_overflow');

            if ($DB->getNumAffected('update_queue_overflow') != 1) {
                $this->_log('update_queue_overflow != 1 at' . $row['record_id']);
                break;
            }

            $counter ++;

            $DB->begin();

            $locked_key = OSC::makeUniqid();

            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                $DB->delete(static::TBL_NAME, 'record_id=' . $row['record_id'], 1, 'delete_queue_overflow');

                $response = OSC::core('network')->curl(
                    OSC::getServiceUrlPersonalizedDesign() . '/personalizedDesign/api/checkOverflow', [
                    'timeout' => 900,
                    'headers' => [
                        'Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum($row['data'], $store_info['secret_key'])
                    ],
                    'json' => OSC::decode($row['data'])
                ]);

                if (!is_array($response['content']) || !isset($response['content']['result'])) {
                    throw new Exception('Response data is incorrect: ' . print_r($response['content'], 1));
                }

                if ($response['content']['result'] != 'OK') {
                    throw new Exception($response['content']['message']);
                }

                $DB->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

            } catch (Exception $ex) {
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                $DB->update(static::TBL_NAME, ['queue_flag' => 1, 'error_message' => $ex->getMessage()], 'record_id=' . $row['record_id'], 1, 'update_queue_overflow');

                break;
            }
        }
    }

}
