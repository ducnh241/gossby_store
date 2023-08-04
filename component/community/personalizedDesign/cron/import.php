<?php

class Cron_PersonalizedDesign_Import extends OSC_Cron_Abstract {

    public function process($data, $queue_added_timestamp) {
        $counter = 0;
        $limit = 500;

        foreach ($data['url_list'] as $type => $list) {
            if ($type == 'font') {
                foreach ($list as $font_key => $font_files) {
                    foreach ($font_files as $file_type => $font_file) {
                        $font_file_s3 = OSC::core('aws_s3')->getStoragePath($font_file);
                        if (OSC::core('aws_s3')->doesObjectExist($font_file_s3)) {
                            continue;
                        }

                        $url = 'http://' . $data['store'] . '/storage/' . $font_file;

                        $tmp_file_path = OSC::getTmpDir() . '/' . md5($url);

                        if (!file_exists($tmp_file_path)) {
                            $url_info = OSC::core('network')->curl($url, array('browser', 'timeout' => 15));

                            if (!$url_info['content']) {
                                throw new Exception('Unable get response from URL');
                            }

                            if (OSC::writeToFile($tmp_file_path, $url_info['content']) === false) {
                                throw new Exception('Unable to save [' . $url . '] TMP file');
                            }
                        }

//                        $extension = OSC_File::verifyExtension($tmp_file_path);
//
//                        if ($extension != 'txt' && $extension != $file_type) {
//                            throw new Exception('File mime type is incorrect');
//                        }

                        $options = [
                            'overwrite' => true,
                            'permission_access_file' => 'public-read'
                        ];
                        OSC::core('aws_s3')->upload($tmp_file_path, $font_file_s3, $options);

                        $counter ++;

                        if ($counter >= $limit) {
                            break;
                        }
                    }
                }
            } else if ($type == 'image') {
                foreach ($list as $url => $file_name) {
                    if (OSC::core('aws_s3')->doesStorageObjectExist($file_name[0])) {
                        continue;
                    }

                    $tmp_file_path = OSC::getTmpDir() . '/' . md5($url);

                    try {
                        if (!file_exists($tmp_file_path)) {
                            $url_info = OSC::core('network')->curl($url, array('browser', 'timeout' => 15));

                            if (!$url_info['content']) {
                                throw new Exception('Unable get response from URL');
                            }

                            if (OSC::writeToFile($tmp_file_path, $url_info['content']) === false) {
                                throw new Exception('Unable to save [' . $url . '] TMP file');
                            }
                        }

                        OSC_File::verifyImage($tmp_file_path);

                        $file_name_s3 = OSC::core('aws_s3')->getStoragePath($file_name[0]);

                        $options = [
                            'overwrite' => true,
                            'permission_access_file' => 'public-read'
                        ];
                        OSC::core('aws_s3')->upload($tmp_file_path, $file_name_s3, $options);

                    } catch (Exception $ex) {
                        if ($file_name[1]) {
                            throw new Exception($url . ':' . $ex->getMessage());
                        }

                        continue;
                    }

                    $counter ++;

                    if ($counter >= $limit) {
                        break;
                    }
                }
            }
        }

        if ($counter >= $limit) {
            OSC::core('cron')->addQueue('personalizedDesign/import', ['store' => $data['store'], 'ukey' => $data['ukey'], 'title' => $data['title'], 'url_list' => $data['url_list'], 'design_data' => $data['design_data']], ['requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60*60]);
            return;
        }

        $model = OSC::model('personalizedDesign/design');

        if ($data['store'] == OSC::$domain) {
            try {
                $model->loadByUkey($data['ukey']);
            } catch (Exception $ex) {
                if ($ex->getCode() !== 404) {
                    throw new Exception($ex->getMessage());
                }
            }
        }

        $model->setData([
            'title' => $data['title'],
            'design_data' => $data['design_data'],
            'member_id' => $data['member_id'],
        ])->save();
    }

    public static function getFileList(&$objects, &$url_list) {
        foreach ($objects as $idx => $object) {
            if (isset($object['personalized'])) {
                $personalized_fetcher = '_getFileListPersonalized_' . lcfirst($object['personalized']['type']);

                if (method_exists('Cron_PersonalizedDesign_Import', $personalized_fetcher)) {
                    static::$personalized_fetcher($objects[$idx]['personalized']['config'], $url_list);
                }
            }

            if ($object['type'] == 'image') {
                static::_parseImageUrl($objects[$idx]['type_data']['url'], $url_list);
            } else if ($object['type'] == 'text') {
                $font_key = preg_replace('/[^a-zA-Z0-9]/', '_', $object['type_data']['style']['font_name']);
                $font_key = preg_replace('/(^_+|_+$)/', '', $font_key);
                $font_key = preg_replace('/_{2,}/', '_', $font_key);
                $font_key = strtolower($font_key);

                $font_path_css = 'personalizedDesign/fonts/' . $font_key . '/' . $font_key . '.css';

                if (!OSC::core('aws_s3')->doesStorageObjectExist($font_path_css)) {
                    $url_list['font'][$font_key] = [
                        'css' => $font_path_css,
                        'ttf' => 'personalizedDesign/fonts/' . $font_key . '/' . $font_key . '.ttf',
                        'woff2' => 'personalizedDesign/fonts/' . $font_key . '/' . $font_key . '.woff2',
                        'svg' => 'personalizedDesign/fonts/' . $font_key . '/' . $font_key . '.svg'
                    ];
                }
            } else if ($object['type'] == 'group') {
                static::getFileList($objects[$idx]['type_data']['children'], $url_list);
            }
        }
    }

    protected static function _getFileListPersonalized_switcher(&$config, &$url_list) {
        foreach ($config['options'] as $option_key => $option) {
            if (isset($option['image']) && $option['image']) {
                static::_parseImageUrl($config['options'][$option_key]['image'], $url_list, true);
            }

            if ($config['default_option_key'] == $option_key) {
                continue;
            }

            static::getFileList($config['options'][$option_key]['data']['objects'], $url_list);
        }
    }

    protected static function _getFileListPersonalized_imageSelector(&$config, &$url_list) {
        foreach ($config['groups'] as $group_key => $group) {
            foreach ($group['images'] as $image_key => $image) {
                if ($image_key == $config['default_key']) {
                    continue;
                }

                static::_parseImageUrl($config['groups'][$group_key]['images'][$image_key]['data']['type_data']['url'], $url_list);
            }
        }
    }

    protected static function _parseImageUrl(&$url, &$url_list, $skip_parse_thumb = false) {
        $url = trim($url);

        if (!$url) {
            return '';
        }

        if (!preg_match('/^((https?\:)?\/\/[^\/]+\.[a-z0-9]+\/+storage\/+)((.+?\.)([a-zA-Z0-9]+))$/i', $url, $matches)) {
            throw new Exception('Cannot detect storage path of URL: ' . $url);
        }

        $url = OSC::core('aws_s3')->getStorageUrl($matches[3]);

        if ($skip_parse_thumb) {
            $file_name = $matches[4] . $matches[5];
            $url_list['image'][$matches[1] . $matches[4] . $matches[5]] = [$file_name, true];
        } else {
            foreach (['preview.', 'thumb.'] as $suffix) {
                $file_name = $matches[4] . $suffix . $matches[5];

                if (!OSC::core('aws_s3')->doesStorageObjectExist($file_name)) {
                    $url_list['image'][$matches[1] . $matches[4] . $suffix . $matches[5]] = [$file_name, true];
                }
            }
        }
    }

}
