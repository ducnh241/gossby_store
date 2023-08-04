<?php

/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Controller_CatalogItemCustomize_Index extends Abstract_Frontend_Controller {

    public function actionUpload() {
        /* @var $model Model_CatalogItemCustomize_Item */

        $id = intval($this->_request->get('id'));

        if ($id < 1) {
            $this->_ajaxError('Data is incorrect');
        }

        $config_key = trim($this->_request->get('ckey'));

        if (!$config_key) {
            $this->_ajaxError('Data is incorrect');
        }

        try {
            $model = OSC::model('catalogItemCustomize/item')->load($id);

            $config = $this->_fetchUploadConfig($model->data['config'], $config_key);

            if (!$config) {
                throw new Exception('Config key is not exist');
            }
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getCode() == 404 ? 'Customize is not exist' : $ex->getMessage());
        }

        try {
            $uploader = new OSC_Uploader();

            $original_file_name = $uploader->getName();

            $tmp_file_path = OSC::getTmpDir() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $uploader->getExtension();

            try {
                $uploader->save($tmp_file_path, true);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }

            try {
                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }
        } catch (Exception $ex) {
            if ($ex->getCode() == 500) {
                $this->_ajaxError($ex->getMessage());
            }

            $file_url = trim(strval($this->_request->decodeValue($this->_request->get('url'))));

            try {
                if (!$file_url) {
                    throw new Exception('No input data');
                }

                $tmp_file_path = OSC::getTmpDir() . '/' . md5($file_url);

                if (!file_exists($tmp_file_path)) {
                    $url_info = OSC::core('network')->curl($file_url, array('browser'));

                    if (!$url_info['content']) {
                        throw new Exception('Unable get response from URL');
                    }

                    if (OSC::writeToFile($tmp_file_path, $url_info['content']) === false) {
                        throw new Exception('Unable to save TMP file');
                    }
                }

                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }

            $original_file_name = preg_replace('/^.+\/([^\/]+?)([\?\#].*)?$/', '\\1', $file_url);
        }

        try {
            $tmp_thumb_file_path = $tmp_file_path . '.thumb';

            $img_processor = new OSC_Image();
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path);

            if ($config['min_width'] > 0 && $img_processor->getWidth() < $config['min_width']) {
                throw new Exception('Image width is need greater than or equal ' . $config['min_width'] . 'px');
            } else if ($config['min_height'] > 0 && $img_processor->getHeight() < $config['min_height']) {
                throw new Exception('Image height is need greater than or equal ' . $config['min_height'] . 'px');
            }

            $img_processor->save();

            $img_processor->makeThumbnail($tmp_thumb_file_path, 100, 100);

            $file_name = 'catalogCustomize/upload/' . date('d.m.Y') . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;
            $file_name_s3 = OSC::core('aws_s3')->getStoragePath($file_name);
            $thumb_file_name = preg_replace('/^(.+)\.([^\.]+)$/', '\\1.thumb.\\2', $file_name);
            $thumb_file_name_s3 = OSC::core('aws_s3')->getStoragePath($thumb_file_name);

            $options = [
                'overwrite' => true,
                'permission_access_file' => 'public-read'
            ];
            OSC::core('aws_s3')->upload($tmp_file_path, $file_name_s3, $options);
            OSC::core('aws_s3')->upload($tmp_file_path, $thumb_file_name_s3, $options);

            $this->_ajaxResponse([
                'name' => $original_file_name,
                'url' => OSC::core('aws_s3')->getStorageUrl($file_name),
                'thumb_url' => OSC::core('aws_s3')->getStorageUrl($thumb_file_name)
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    protected function _fetchUploadConfig($components, $config_key) {
        foreach ($components as $component) {
            if ($component['component_type'] == 'imageUploader') {
                if ($component['key'] == $config_key) {
                    return $component;
                }
            } else if (in_array($component['component_type'], ['switcherBySelect', 'switcherByColor', 'switcherByImage'], true)) {
                foreach ($component['scenes'] as $scene) {
                    $config = $this->_fetchUploadConfig($scene['components'], $config_key);

                    if ($config) {
                        return $config;
                    }
                }
            } else if ($component['component_type'] == 'listItem') {
                $config = $this->_fetchUploadConfig($component['components'], $config_key);

                if ($config) {
                    return $config;
                }
            }
        }

        return null;
    }

}
