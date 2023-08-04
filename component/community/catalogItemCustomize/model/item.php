<?php

class Model_CatalogItemCustomize_Item extends Abstract_Core_Model {

    protected $_table_name = 'catalog_item_customize';
    protected $_pk_field = 'item_id';
    protected $_ukey_field = 'ukey';

    protected $_allow_write_log = true;

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['config'])) {
            $data['config'] = OSC::encode($data['config']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['config'])) {
            $data['config'] = OSC::decode($data['config'], true);
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

        if (isset($data['config'])) {
            if (!is_array($data['config'])) {
                $errors[] = 'Config is incorrect data';
            } else {
                try {
                    OSC::helper('catalogItemCustomize/componentValidator')->validate($data['config']);
                } catch (Exception $ex) {
                    $errors[] = $ex->getMessage();
                }
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'title' => 'Title is empty',
                    'config' => 'Config is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'added_timestamp' => time(),
                    'modified_timestamp' => time(),
                    'ukey' => OSC::makeUniqid()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
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
