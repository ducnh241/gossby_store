<?php

class Cron_PersonalizedDesign_Sync extends OSC_Cron_Abstract {

    const CRON_TIMER = '* * * * *';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($data, $queue_added_timestamp) {
        $store_info = OSC::getStoreInfo();

        /* @var $DB OSC_Database_Adapter */
        /* @var $storage OSC_Storage */
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 1000;
        $counter = 0;

        $requeue = intval(SETTING_PERSONALIZE_SYNC_REQUEUE);
        if ($requeue < 1) {
            $requeue = 5;
        }

        $next_time = intval(SETTING_PERSONALIZE_SYNC_NEXT_TIME);
        if ($next_time < 1 || $next_time > 300) {
            $next_time = 60;
        }

        $table_name = OSC::model('personalizedDesign/sync')->getTableName();

        $condition = ['syncing_flag = 0'];

        if (isset($data['sync_type']) && is_array($data['sync_type']) && count($data['sync_type']) > 0) {
            $condition[] = 'sync_type in ("'.implode('","', $data['sync_type']).'")';
        }

        $condition[] = 'requeue <= ' . $requeue . ' AND next_timestamp < ' . time();
        
        $condition = implode(' AND ', $condition);

        while ($counter < $limit) {
            $DB->select('*', $table_name, $condition, 'added_timestamp ASC, record_id ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }
            
            $modified_timestamp_flag = time() - (60 * 15);

            if ($row['syncing_flag'] == 1 && $row['modified_timestamp'] > $modified_timestamp_flag) {
                $counter = $limit;
                break;
            }

            $DB->update($table_name, [
                'syncing_flag' => 1,
                'modified_timestamp' => time()
            ], 'record_id=' . $row['record_id'] . ' AND (syncing_flag=0 OR modified_timestamp < ' . $modified_timestamp_flag . ')', 1, 'update_queue');

            $counter ++;
            
            if ($DB->getNumAffected('update_queue') != 1) {
                continue;
            }

            $DB->begin();

            $locked_key = OSC::makeUniqid();

            OSC_Database_Model::lockPreLoadedModel($locked_key);

            $recron = intval($row['requeue']);

            try {
                $DB->delete($table_name, 'record_id=' . $row['record_id'], 1, 'delete_queue');

                $row['sync_data'] = OSC::decode($row['sync_data'], true);

                $request_data = OSC::encode([
                    'sync_data' => $row['sync_data']
                ]);

                $request_file = tempnam(sys_get_temp_dir(), 'POST');
                file_put_contents($request_file, $request_data);

                $files = ['api_request' => $request_file];

                if ($row['sync_type'] == 'font') {
                    $ttf_file_s3_path = OSC::core('aws_s3')->getStoragePath($row['sync_data']['ttf']);
                    $ttf_file_local_path = OSC_Storage::preDirForSaveFile($row['sync_data']['ttf']);
                    $woff2_file_s3_path = OSC::core('aws_s3')->getStoragePath($row['sync_data']['woff2']);
                    $woff2_file_local_path = OSC_Storage::preDirForSaveFile($row['sync_data']['woff2']);
                    $svg_file_s3_path = OSC::core('aws_s3')->getStoragePath($row['sync_data']['svg']);
                    $svg_file_local_path = OSC_Storage::preDirForSaveFile($row['sync_data']['svg']);
                    $css_file_s3_path = OSC::core('aws_s3')->getStoragePath($row['sync_data']['css']);
                    $css_file_local_path = OSC_Storage::preDirForSaveFile($row['sync_data']['css']);

                    $files['ttf'] = OSC::core('aws_s3')->download($ttf_file_s3_path, $ttf_file_local_path);
                    $files['woff2'] = OSC::core('aws_s3')->download($woff2_file_s3_path, $woff2_file_local_path);
                    $files['svg'] = OSC::core('aws_s3')->download($svg_file_s3_path, $svg_file_local_path);
                    $files['css'] = OSC::core('aws_s3')->download($css_file_s3_path, $css_file_local_path);
                } else if ($row['sync_type'] == 'image') {
                    $image_file_s3_path = OSC::core('aws_s3')->getStoragePath($row['sync_data']);
                    $image_file_local_path = OSC_Storage::preDirForSaveFile($row['sync_data']);
                    $files['image'] = OSC::core('aws_s3')->download($image_file_s3_path, $image_file_local_path);
                } else if ($row['sync_type'] == 'imagelib') {
                    $image_file_s3_path = OSC::core('aws_s3')->getStoragePath($row['sync_data']);
                    $image_file_local_path = OSC_Storage::preDirForSaveFile($row['sync_data']);
                    $files['image'] = OSC::core('aws_s3')->download($image_file_s3_path, $image_file_local_path);
                }

//                if (OSC_ENV == 'production') {
//                    $response = OSC::core('network')->curl(
//                        OSC::getServiceUrlPersonalizedDesign() . '/personalizedDesign/api_sync/' . $row['sync_type'], [
//                        'files' => $files,
//                        'timeout' => 900,
//                        'headers' => [
//                            'Osc-Api-Request' => 'file',
//                            'Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum($request_data, $store_info['secret_key'])
//                        ],
//                        'proxy' => [
//                            'ip' => '125.212.192.112',
//                            'port' => '3128',
//                            'user' => 'dls_supplier',
//                            'password' => 'u1yHUzAk3T1En3dtCZBY'
//                        ]
//                    ]);
//                } else {
                    $response = OSC::core('network')->curl(
                        OSC::getServiceUrlPersonalizedDesign() . '/personalizedDesign/api_sync/' . $row['sync_type'], [
                        'files' => $files,
                        'timeout' => 900,
                        'headers' => [
                            'Osc-Api-Request' => 'file',
                            'Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum($request_data, $store_info['secret_key'])
                        ]
                    ]);
//                }

                unlink($request_file);

                if (!is_array($response['content']) || !isset($response['content']['result'])) {
                    throw new Exception('Response data is incorrect: ' . print_r($response['content'], 1));
                }

                if ($response['content']['result'] != 'OK') {
                    throw new Exception($response['content']['message']);
                }

                $DB->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                if ($row['sync_type'] == 'image') {
                    OSC::core('aws_s3')->deleteStorageFile($row['sync_data']);
                } else if ($row['sync_type'] == 'imagelib') {
                    OSC::core('aws_s3')->deleteStorageFile($row['sync_data']);
                }
            } catch (Exception $ex) {
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                if ($recron < $requeue) {
                    $syncing_flag = 0;
                    $recron = $recron + 1;
                } else {
                    $syncing_flag = 2;
                }

                $DB->update($table_name, [
                    'syncing_flag' => $syncing_flag,
                    'requeue' => $recron,
                    'next_timestamp' => time() + $next_time,
                    'sync_error' => $ex->getMessage(),
                    'modified_timestamp' => time()
                ], 'record_id=' . $row['record_id'], 1, 'update_queue');

                break;
            }
        }

//        if ($limit >= $counter) {
//            OSC::core('cron')->addQueue('personalizedDesign/sync', null, ['skip_realtime', 'requeue_limit' => -1]);
//        }
    }

}
