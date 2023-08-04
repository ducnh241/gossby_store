<?php

class Model_PersonalizedDesign_Design_Config extends Abstract_Core_Model
{
    protected $_table_name = 'personalized_design_config';
    protected $_pk_field = 'config_id';

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['slice_data'])) {
            $data['slice_data'] = OSC::encode($data['slice_data']);
        }

        if (isset($data['meta_data'])) {
            $data['meta_data'] = OSC::encode($data['meta_data']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['slice_data'])) {
            $data['slice_data'] = OSC::decode($data['slice_data'], true);
        }

        if (isset($data['meta_data'])) {
            $data['meta_data'] = OSC::decode($data['meta_data'], true);
        }
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['slice_data'])) {
            if (!is_array($data['slice_data'])) {
                $errors[] = 'Slice data is incorrect data';
            }
        }

        if (isset($data['meta_data'])) {
            if (!is_array($data['meta_data'])) {
                $errors[] = 'Meta data is incorrect data';
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'url' => 'Url is empty',
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
            } else {
                $data['modified_timestamp'] = time();
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }
}