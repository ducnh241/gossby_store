<?php

/**
 * Created by PhpStorm.
 * User: ManhTienpt
 * Date: 05/2019
 * Time: 22:07 PM
 */
class Model_Catalog_ProductTabs extends Abstract_Core_Model {

    protected $_table_name = 'catalog_product_tabs';
    protected $_pk_field = 'record_id';
    protected $_ukey_field = 'ukey';

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['title'])) {
            $data['title'] = trim($data['title']);

            if (!$data['title']) {
                $errors[] = 'Title is empty';
            }
        }


        $block_image_config = array(
            'url_processor' => array(Helper_Core_Editor, 'imageUrlProcessor'),
            'control_align_enable' => true,
            'control_align_level_enable' => true,
            'control_align_overflow_mode' => true,
            'control_align_full_mode' => true,
            'control_zoom_enable' => true,
            'control_caption_enable' => true
        );
        $embed_block_config = array(
            'control_zoom_enable' => false,
            'control_align_level_enable' => true,
            'control_caption_enable' => true
        );

        foreach (['content'] as $key) {
            if (isset($data[$key])) {
                try {
                    $data[$key] = OSC::core('editor')->config(['image_enable' => false])
                            ->addPlugins(['name' => 'textColor'], ['name' => 'highlight'], ['name' => 'blockImage', 'config' => $block_image_config], ['name' => 'embedBlock', 'config' => $embed_block_config])
                            ->clean($data[$key]);
                } catch (Exception $ex) {
                    $data[$key] = '';
                }
            }
        }

        if (isset($data['ukey'])) {
            $data['ukey'] = trim($data['ukey']);

            if (!$data['ukey']) {
                $errors[] = 'Ukey is empty';
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'title' => 'Title is empty',
                    'ukey' => 'Ukey is empty',
                    'content' => 'Content is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

}
