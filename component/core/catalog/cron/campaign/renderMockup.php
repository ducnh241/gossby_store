<?php

class Cron_Catalog_Campaign_RenderMockup extends OSC_Cron_Abstract {
    const SYNC_KEY = 'request_render_mockup' . '_' . OSC_SITE_KEY;
    const CACHE_KEY = 'KAFKA_SERVICE_TIMES';

    public function process($params, $queue_added_timestamp) {
        $store_info = OSC::getStoreInfo();

        $DB = OSC::core('database');

        $limit = 30;
        $counter = 0;

        $error_flag = false;

        while ($counter < $limit) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'renderCampaignMockup'", '`added_timestamp` ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            try {
//                $mockup_dir_path = OSC_Storage::getStoragePath('catalog/campaign/mockup/' . $model->data['queue_data']['product_id']);

//                foreach ($model->data['queue_data'] as $queue_data) {
//                    if (!OSC::removeDir($mockup_dir_path . '/' . $queue_data['callback_data']['variant_id'])) {
//                        throw new Exception('Unable to remove directory [' . $mockup_dir_path . '/' . $queue_data['callback_data']['variant_id'] . ']');
//                    }

//                    if (file_exists($mockup_dir_path . '/' . $product_type . '.png') && @unlink($mockup_dir_path . '/' . $product_type . '.png') === false) {
//                        throw new Exception('Unable to remove file [' . $mockup_dir_path . '/' . $product_type . '.png]');
//                    }
//                }

//                if (OSC_ENV == 'production') {
//                    $response = OSC::core('network')->curl(
//                        OSC::getServiceUrlPersonalizedDesign() . '/personalizedDesign/api/v2RenderCampaignMockup', [
//                        'timeout' => 900,
//                        'headers' => [
//                            'Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum(OSC::encode($model->data['queue_data']), $store_info['secret_key'])
//                        ],
//                        'json' => $model->data['queue_data'],
//                        'proxy' => [
//                            'ip' => '125.212.192.112',
//                            'port' => '3128',
//                            'user' => 'dls_supplier',
//                            'password' => 'u1yHUzAk3T1En3dtCZBY'
//                        ]
//                    ]);
//                } else {

                $sync_service_times = OSC::core('cache')->get($store_info['id'] . '_' .static::CACHE_KEY) ?: 0;

                $maximum_request_sync = OSC::helper('core/setting')->get('catalog/synchronize_service/maximum_request');

                if (intval($sync_service_times) < intval($maximum_request_sync)) {
                    $kafka_config = OSC::systemRegistry('kafka');

                    $current_milliseconds = floor(microtime(true) * 1000);

                    $payload = $model->data['queue_data'];
                    $payload['store_key'] = $store_info['id'];

                    $value = [
                        'router' => '/personalizedDesign/syncService/v2RenderCampaignMockup',
                        'syncKey' => static::SYNC_KEY,
                        'payload' => $payload,
                        'source' => $store_info['id'],
                        'timestamp' => $current_milliseconds,
                        'groupKey' => OSC::makeUniqid(),
                        'secretCode' => OSC_Controller::makeRequestChecksum(static::SYNC_KEY . $current_milliseconds, $store_info['secret_key'])
                    ];

                    OSC::core('kafka_client')->sendData($value, $kafka_config['topic']);
                    OSC::core('cache')->set($store_info['id'] . '_' .static::CACHE_KEY, $sync_service_times + 1);
                } else {
                    $response = OSC::core('network')->curl(
                        OSC::getServiceUrlPersonalizedDesign() . '/personalizedDesign/api/v2RenderCampaignMockup', [
                        'timeout' => 900,
                        'headers' => [
                            'Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum(OSC::encode($model->data['queue_data']), $store_info['secret_key'])
                        ],
                        'json' => $model->data['queue_data'],
                    ]);

                    if (!is_array($response['content']) || !isset($response['content']['result'])) {
                        throw new Exception('Response data is incorrect: ' . print_r($response['content'], 1));
                    }

                    if ($response['content']['result'] != 'OK') {
                        throw new Exception($response['content']['message']);
                    }
                }

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => substr($ex->getMessage(), 0, 255), 'queue_flag' => 1, 'added_timestamp' => time()])->save();
                $error_flag = true;
            }
        }

        if ($counter == $limit || $error_flag) {
            return false;
        }
    }

}
