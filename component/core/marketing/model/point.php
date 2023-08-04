<?php

class Model_Marketing_Point extends Abstract_Core_Model
{
    protected $_table_name = 'marketing_point';
    protected $_pk_field = 'record_id';

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['meta_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['meta_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }

        $data['meta_data'] = is_array($data['meta_data']) ? $data['meta_data'] : [];
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        foreach (['point', 'vendor_point'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval(round($data[$key])); // Round before save to DB

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        foreach (['added_timestamp', 'modified_timestamp'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [

                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'order_id' => 0,
                    'order_line_item_id' => 0,
                    'product_id' => 0,
                    'variant_id' => 0,
                    'member_id' => 0,
                    'point' => 0,
                    'vendor' => '',
                    'vendor_point' => 0,
                    'meta_data' => [
                        'day_after_product_created' => 0
                    ],
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

    protected function _afterSave() {
        parent::_afterSave();
    }
}