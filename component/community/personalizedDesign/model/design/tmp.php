<?php

class Model_PersonalizedDesign_Design_Tmp extends Abstract_Core_Model {

    protected $_table_name = 'personalized_design_tmp';
    protected $_pk_field = 'record_id';

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['design_data'])) {
            $data['design_data'] = OSC::encode($data['design_data']);
        }

        if (isset($data['meta_data'])) {
            $data['meta_data'] = OSC::encode($data['meta_data']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['design_data'])) {
            $data['design_data'] = OSC::decode($data['design_data'], true);
        }

        if (isset($data['meta_data'])) {
            $data['meta_data'] = OSC::decode($data['meta_data'], true);
        }
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['design_id'])) {
            $data['design_id'] = trim($data['design_id']);

            if (!$data['design_id']) {
                $errors[] = 'Design ID is empty';
            }
        }

        foreach (array('added_timestamp', 'modified_timestamp') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'design_id' => 'Design ID is empty',
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