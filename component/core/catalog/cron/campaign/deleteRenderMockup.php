<?php

class Cron_Catalog_Campaign_DeleteRenderMockup extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $store_info = OSC::getStoreInfo();

        $DB = OSC::core('database');

        $limit = 15;
        $counter = 0;

        $error_flag = false;

        while ($counter < $limit) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'v2DeleteRenderCampaignMockup'", '`added_timestamp` ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            $data = $model->data['queue_data']['data'];

            if (isset($data)) {
                try {
                    $collection = OSC::model('catalog/product_bulkQueue')->getCollection()->addCondition('ukey' , $data['product_id'] . '_renderCampaignMockup_' , OSC_Database::OPERATOR_LIKE_RIGHT)->load();

                    if ($collection->length() > 0) {
                        $collection->delete();
                    }

                    $flex_key = 'campaign/createMockupCampaign';
                    $action = 'createMockupCampaign';
                    $queue_ukey = 'catalog/campaign_createMockupCampaign';

                    if (isset($data['rerender']) == 'tool') {
                        $action = 'createMockupCampaignTool';
                        $queue_ukey = 'catalog/campaign_createMockupCampaignTool';
                    }

                    try {
                        $model_bulk_queue = OSC::model('catalog/product_bulkQueue')->loadByUKey($flex_key . ':' . $data['product_id']);
                        $model_bulk_queue->delete();
                    }catch (Exception $ex) {
                        if ($ex->getCode() != 404){
                            throw new Exception($ex->getMessage());
                        }
                    }

                    OSC::model('catalog/product_bulkQueue')->setData([
                        'ukey' => $flex_key . ':' . $data['product_id'],
                        'member_id' => $data['member_id'],
                        'action' => $action,
                        'queue_data' => $data
                    ])->save();

                    OSC::core('cron')->addQueue($queue_ukey, null, ['ukey' => $queue_ukey, 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);

                }catch (Exception $ex) {

                }
            }

            try {
                $response = OSC::core('network')->curl(
                        OSC::getServiceUrlPersonalizedDesign() . '/personalizedDesign/api/v2DeleteRenderCampaignMockup', [
                    'timeout' => 900,
                    'headers' => [
                        'Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum(OSC::encode($model->data['queue_data']), $store_info['secret_key'])
                    ],
                    'json' => $model->data['queue_data']
                ]);

                if (!is_array($response['content']) || !isset($response['content']['result'])) {
                    throw new Exception('Response data is incorrect: ' . print_r($response['content'], 1));
                }

                if ($response['content']['result'] != 'OK') {
                    throw new Exception($response['content']['message']);
                }

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 1, 'added_timestamp' => time()])->save();
                $error_flag = true;
            }
        }

        if ($counter == $limit || $error_flag) {
            return false;
        }
    }
}
