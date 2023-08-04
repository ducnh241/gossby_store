<?php

class Model_Dmca_Dmca extends Abstract_Core_Model {
    protected $_table_name = 'dmca';
    protected $_pk_field = 'id';
    protected $_ukey_field = 'ukey';

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['data'])) {
            if ($data['data'] == '') {
                $data['data'] = [];
            }
        }

        if (isset($data['form'])) {
            if ($data['form'] == '') {
                $data['form'] = '';
            }
        }


        if (isset($data['added_timestamp'])) {
            $data['added_timestamp'] = intval($data['added_timestamp']);

            if ($data['added_timestamp'] < 0) {
                $data['added_timestamp'] = 0;
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'data' => 'data is empty',
                    'form' => 'form is empty',
                    'added_timestamp' => 'added_timestamp is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'data' => [],
                    'form' => '',
                    'added_timestamp' => time(),
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                $data['ukey'] = OSC::makeUniqid();
            } else {
                unset($data['ukey']);
                unset($data['data']);
                unset($data['form']);
                unset($data['added_timestamp']);
            }
        }

        $this->resetDataModifiedMap()->setData($data);

    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['data'])) {
            $data['data'] = OSC::encode($data['data']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);
        if (isset($data['data'])) {
            $data['data'] = OSC::decode($data['data'], true);
        }

    }

}