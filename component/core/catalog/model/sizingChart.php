<?php

/**
 * Created by PhpStorm.
 * User: ManhTienpt
 * Date: 4/5/2019
 * Time: 2:07 PM
 */
class Model_Catalog_SizingChart extends Abstract_Core_Model {

    protected $_table_name = 'catalog_sizing_chart';
    protected $_pk_field = 'record_id';
    protected $_ukey_field = 'ukey';

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['product_types'])) {
            $data['product_types'] = OSC::encode($data['product_types']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['product_types'])) {
            $data['product_types'] = OSC::decode($data['product_types'], true);
        }
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

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'title' => 'Title is empty',
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

                $data['ukey'] = OSC::makeUniqid();
            } else {
                unset($data['ukey']);
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

}
