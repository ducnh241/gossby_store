<?php

class Cron_PersonalizedDesign_UploadToS3 extends OSC_Cron_Abstract {

    public function process($data, $queue_added_timestamp) {

        if (isset($data['folderId'])) {
            $folderId = $data['folderId'];
            $tmp_path = $data['type'] . '/' . date('d.m.Y') . '/' . $folderId;
            $tmp_path = OSC::helper('personalizedDesign/common')->getTmpDir( $tmp_path);
            foreach (glob($tmp_path . '/*') as $tmp_file_path) {
                if (str_contains($tmp_file_path, '.thumb.') || str_contains($tmp_file_path, '.preview.')) {
                    continue;
                }

                $file_name = array_pop(explode('/', $tmp_file_path));

                if ($file_name === 'thumbnail') { // upload thumbnail image to S3
                    foreach (glob($tmp_file_path . '/*') as $tmp_thumbnail_file_path) {
                        try {
                            $thumbnail_file_name = array_pop(explode('/', $tmp_thumbnail_file_path));
                            $file_name = $data['type'] . '/' . date('d.m.Y') . '/'. $folderId . '/thumbnail/' . $thumbnail_file_name;
                            $file_name_s3 = OSC::core('aws_s3')->getStoragePath($file_name);
                            
                            $options = [
                                'overwrite' => true,
                                'permission_access_file' => 'public-read'
                            ];
                            OSC::core('aws_s3')->upload($tmp_thumbnail_file_path, $file_name_s3, $options);
            
                        } catch (Exception $ex) {
                            throw new Exception($ex->getMessage());
                        }
                    }
                } else {  // upload normal image to S3
                    try {
                        $tmp_thumb_file_path = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.thumb.\\2', $tmp_file_path);
                        $tmp_preview_file_path = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.preview.\\2', $tmp_file_path);

                        $file_name = $data['type'] . '/' . date('d.m.Y') . '/'. $folderId . '/' . $file_name;
                
                        $file_name_s3 = OSC::core('aws_s3')->getStoragePath($file_name);
                        $thumb_file_name_s3 = OSC::core('aws_s3')->getStoragePath(preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.thumb.\\2', $file_name));
                        $preview_file_name_s3 = OSC::core('aws_s3')->getStoragePath(preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.preview.\\2', $file_name));

                        $options = [
                            'overwrite' => true,
                            'permission_access_file' => 'public-read'
                        ];
                        OSC::core('aws_s3')->upload($tmp_file_path, $file_name_s3, $options);
                        OSC::core('aws_s3')->upload($tmp_thumb_file_path, $thumb_file_name_s3, $options);
                        OSC::core('aws_s3')->upload($tmp_preview_file_path, $preview_file_name_s3, $options);
                
                        try {
                            $dataSync = [
                                'ukey' => 'image/' . md5($file_name),
                                'sync_type' => 'image',
                                'sync_data' => $file_name
                            ];

                            OSC::model('personalizedDesign/sync')->setData($dataSync)->save();
                        } catch (Exception $ex) {
                            if (strpos($ex->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') === false) {
                                throw new Exception($ex->getMessage());
                            }
                        }
                    } catch (Exception $ex) {
                        throw new Exception($ex->getMessage());
                    }
                }
            }

            try {
                $model = OSC::model($data['type'] . '/design')->load($data['id']);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
            $meta_data = $model->data['meta_data'];

            array_splice(
                $meta_data['image_folder_id_on_server'], 
                array_search($folderId, $meta_data['image_folder_id_on_server']), 
                1
            );
            $model->setData([
                'meta_data' => $meta_data,
            ])->save();

            if (!isset($model->data['meta_data']['image_folder_id_on_server']) || count($model->data['meta_data']['image_folder_id_on_server']) == 0) {
                $svg_content = OSC::helper($data['type'] . '/common')->renderSvg($model);
                $dataSync = [
                    'ukey' => OSC::makeUniqid(),
                    'sync_type' => 'renderDesignImage',
                    'sync_data' => [
                        'design_id' => $model->getId(),
                        'svg_content' => $svg_content
                    ]
                ];

                OSC::model('personalizedDesign/sync')->setData($dataSync)->save();

            }
        }
    }
}