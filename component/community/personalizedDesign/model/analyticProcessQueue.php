<?php

class Model_PersonalizedDesign_AnalyticProcessQueue extends Abstract_Core_Model {

    protected $_table_name = 'personalized_design_analytic_process_queue';
    protected $_pk_field = 'record_id';

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['queue_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['queue_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key]);
            }
        }
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['queue_data'])) {
            if (!is_array($data['queue_data'])) {
                $errors[] = 'Queue data is incorrect data';
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'queue_data' => 'Queue data is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'locked_key' => '',
                    'locked_timestamp' => 0,
                    'added_timestamp' => time()
                ];

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
