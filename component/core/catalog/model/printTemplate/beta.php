<?php

class Model_Catalog_PrintTemplate_Beta extends Abstract_Core_Model {

    protected $_table_name = 'print_template_beta';
    protected $_pk_field = 'id';

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);
        $data['config'] = OSC::encode($data['config']);
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);
        $data['config'] = OSC::decode($data['config']);
    }

    protected function _beforeSave() {
        parent::_beforeSave(); // TODO: Change the autogenerated stub

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['title'])) {
            $data['title'] = trim($data['title']);

            if ($data['title'] == '') {
                $errors[] = 'Title is empty';
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'title' => 'title is empty'
                ];

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

            if ($this->getActionFlag() == static::UPDATE_FLAG) {
                $default_fields = array(
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