<?php

class Controller_Core_Backend_Setting extends Abstract_Backend_Controller {

    public function __construct() {
        parent::__construct();

        $this->getTemplate()->addBreadcrumb(array('cog', 'Settings'), $this->getUrl('*/*/index'));
    }

    public function actionIndex() {
        $this->checkPermission('core/setting|settings');

        $tpl = $this->getTemplate();

        $tpl->setCurrentMenuItemKey('core/setting');

        $sections = OSC::helper('core/setting')->collectSections();

        $tpl->setPageTitle('Settings');

        $this->output($tpl->build('core/setting/section_list', ['sections' => $sections]));
    }

    public function actionConfig() {
        try {
            $section_key = trim($this->_request->get('section'));
            Model_Core_Setting::validateSettingKey($section_key);
        } catch (Exception $ex) {
            $this->addErrorMessage('Setting section key is empty');
            static::redirect($this->getUrl('*/*/index'));
        }

        $this->checkPermission("settings/{$section_key}");

        OSC::helper('core/setting')->loadFromDB();

        $sections = OSC::helper('core/setting')->collectSections();

        if (!isset($sections[$section_key])) {
            $this->addErrorMessage('Setting section key is not exists');
            static::redirect($this->getUrl('*/*/index'));
        }

        $setting_items = OSC::helper('core/setting')->collectSettingItems();
        $setting_items = isset($setting_items[$section_key]) ? $setting_items[$section_key] : [];

        $setting_types = OSC::helper('core/setting')->collectSettingTypes();

        $new_setting_values = [];

        if ($this->_request->get('save') == 1) {
            $setting_values = $this->_request->get('config');

            $errors = [];

            foreach ($setting_items as $group) {
                foreach ($group['items'] as $setting_item) {
                    if (!isset($setting_values[$setting_item['key']])) {
                        if ($setting_item['require']) {
                            $errors[] = $setting_item['title'] . ' is require';
                        }

                        continue;
                    }

                    if ($setting_types[$setting_item['type']]['validator']) {
                        try {
                            $setting_values[$setting_item['key']] = $setting_types[$setting_item['type']]['validator']($setting_values[$setting_item['key']], $setting_item);
                        } catch (Exception $ex) {
                            $errors[] = $setting_item['title'] . ' :: ' . $ex->getMessage();
                            continue;
                        }
                    }

                    if ($setting_item['validator']) {
                        try {
                            $setting_values[$setting_item['key']] = $setting_item['validator']($setting_values[$setting_item['key']], $setting_item);
                        } catch (Exception $ex) {
                            $errors[] = $setting_item['title'] . ' :: ' . $ex->getMessage();
                            continue;
                        }
                    }

                    $new_setting_values[$setting_item['key']] = ['value' => $setting_values[$setting_item['key']], 'item' => $setting_item];
                }
            }

            if (count($errors) < 1) {
                $DB = OSC::core('database')->getWriteAdapter();

                $DB->begin();

                $locked_key = OSC::makeUniqid();

                OSC_Database_Model::lockPreLoadedModel($locked_key);

                try {
                    $collection = OSC::model('core/setting')->getCollection()->loadByUkey(array_keys($new_setting_values));

                    $errors = [];
                    $message = '';

                    foreach ($new_setting_values as $key => $new_setting_value) {
                        if ($new_setting_value['item']['show_change']) {
                            $current_data = OSC::helper('core/setting')->get($key);
                            $notif = OSC::helper('core/setting')->getDataChange(['key' => $key, 'current_data' => $current_data, 'new_data' => $new_setting_value['value'], 'data_type' => $new_setting_value['item']['data_type']]);
                            if ($notif != '') {
                                $message .= $notif;
                            }
                        }
                        OSC::helper('core/setting')->set($key, $new_setting_value['value'], true);

                        if ($new_setting_value['item']['after_save']) {
                            try {
                                $new_setting_value['item']['after_save']($new_setting_value['value'], $new_setting_value['item']);
                            } catch (Exception $ex) {
                                $errors[] = $ex->getMessage();
                            }
                        }
                    }

                    if (count($errors) > 0) {
                        $this->addErrorMessage($errors);
                    }

                    $DB->commit();

                    OSC::helper('core/setting')->loadFromDB();

                    OSC_Database_Model::unlockPreLoadedModel($locked_key);

                    OSC::helper('core/setting')->removeCache();
                    OSC::core('observer')->dispatchEvent('setting_updated');
                    OSC::core('observer')->dispatchEvent('setting_updated_by_user');
                    if ($message != '') {
                        $this->addMessage($message);
                    }
                    static::redirect($this->rebuildUrl(['save' => 0]));
                } catch (Exception $ex) {
                    $DB->rollback();

                    OSC_Database_Model::unlockPreLoadedModel($locked_key);

                    $this->addErrorMessage($ex->getMessage());
                }
            } else {
                $this->addErrorMessage($errors);

                static::redirect($this->getCurrentUrl());
            }
        }

        $this->getTemplate()->addBreadcrumb([$sections[$section_key]['icon'], $sections[$section_key]['title']])->setPageTitle($sections[$section_key]['title']);

        $this->output($this->getTemplate()->build('core/setting/config', ['section' => $sections[$section_key], 'groups' => $setting_items, 'setting_types' => $setting_types, 'new_setting_values' => $new_setting_values]));
    }

    /**
     * @throws Exception
     */
    public function actionImageUpload() {
        $key = trim($this->_request->get('key'));

        if (!$key) {
            $this->_ajaxError('Setting key is empty');
        }

        try {
            Model_Core_Setting::validateSettingKey($key);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $setting_items = OSC::helper('core/setting')->collectSettingItems();

        $setting_item = null;

        foreach ($setting_items as $section) {
            foreach ($section as $group) {
                if (isset($group['items'][$key])) {
                    $setting_item = $group['items'][$key];
                    break;
                }
            }
        }

        if (!$setting_item || $setting_item['type'] != 'image') {
            $this->_ajaxError('Setting key ' . $key . ' is not exists');
        }

        try {
            $uploader = new OSC_Uploader();

            $tmp_file_path = OSC::getTmpDir() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $uploader->getExtension();

            try {
                $uploader->save($tmp_file_path, true);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }

            try {
                $extension = OSC_File::verifyExtension($tmp_file_path);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }
        } catch (Exception $ex) {
            if ($ex->getCode() == 500) {
                $this->_ajaxError($ex->getMessage());
            }

            $image_url = trim(strval($this->_request->decodeValue($this->_request->get('image_url'))));

            try {
                if (!$image_url) {
                    throw new Exception($this->_('Cannot detect upload data'));
                }

                $tmp_file_path = OSC::getTmpDir() . '/' . md5($image_url);

                if (!file_exists($tmp_file_path)) {
                    $url_info = OSC::core('network')->curl($image_url, array('browser'));

                    if (!$url_info['content']) {
                        throw new Exception($this->_('Cannot fetch url content'));
                    }

                    if (OSC::writeToFile($tmp_file_path, $url_info['content']) === false) {
                        throw new Exception($this->_('Cannot save file to TMP'));
                    }
                }

                $extension = OSC_File::verifyExtension($tmp_file_path);
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }
        }

        $extensions = ['png', 'gif', 'jpg', 'svg'];

        if (isset($setting_item['extension'])) {
            if (!is_array($setting_item['extension'])) {
                $setting_item['extension'] = explode(',', $setting_item['extension']);
            }

            $setting_item['extension'] = array_map(function($ext) {
                return strtolower(trim($ext));
            }, $setting_item['extension']);

            if (in_array('ico', $setting_item['extension'], true)) {
                $extensions[] = 'ico';
            }

            $setting_item['extension'] = array_filter($setting_item['extension'], function($ext) use ($extensions) {
                return in_array($ext, $extensions, true);
            });

            if (count($setting_item['extension']) > 0) {
                $extensions = array_intersect($extensions, $setting_item['extension']);
            }
        }

        if (!in_array($extension, $extensions, true)) {
            $this->_ajaxError('Extension ' . $extension . ' is not allowed to upload');
        }

        $file_name = 'setting/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;
        $file_path = OSC_Storage::preDirForSaveFile($file_name);
        OSC::core('aws_s3')->tmpSaveFile($tmp_file_path, $file_name, true);
        $tmp_file_path_s3 = OSC::core('aws_s3')->getTmpFilePath($file_name);

        $width = 0;
        $height = 0;

        if (in_array($extension, ['png', 'jpg', 'gif'], true)) {
            $dim_config = [];

            foreach (['min_width', 'min_height', 'max_width', 'max_height'] as $k) {
                $dim_config[$k] = isset($setting_item[$k]) ? intval($setting_item[$k]) : 0;

                if ($dim_config[$k] < 0) {
                    $dim_config[$k] = 0;
                }
            }

            list($width, $height) = getimagesize($file_path);

            if ($dim_config['min_width'] > 0 && $width < $dim_config['min_width']) {
                $this->_ajaxError('The image width is need greater than or equal to ' . $dim_config['min_width'] . 'px');
            }

            if ($dim_config['min_height'] > 0 && $height < $dim_config['min_height']) {
                $this->_ajaxError('The image height is need greater than or equal to ' . $dim_config['min_height'] . 'px');
            }

            if ($dim_config['max_width'] > 0 && $dim_config['max_height'] > 0) {
                try {

                    $img_processor = new OSC_Image();
                    $img_processor->setJpgQuality(100)->setImage($file_path);

                    if ($dim_config['max_width'] > 0 && $dim_config['max_height'] > 0 && $setting_item['trim']) {
                        $img_processor->trimAndResize($dim_config['max_width'], $dim_config['max_height']);
                    } else {
                        $img_processor->resize($dim_config['max_width'], $dim_config['max_height']);
                    }

                    $img_processor->save();
                    $width = $img_processor->getWidth();
                    $height = $img_processor->getHeight();

                    OSC::core('aws_s3')->upload($file_path, $tmp_file_path_s3);

                } catch (Exception $ex) {
                    $this->_ajaxError($ex->getMessage());
                }
            }
        }

        $this->_ajaxResponse([
            'file' => $file_name,
            'url' => OSC::core('aws_s3')->getObjectUrl($tmp_file_path_s3),
            'width' => $width,
            'height' => $height
        ]);
    }
}
