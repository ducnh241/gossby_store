<?php

class Model_Filter_Collection extends Abstract_Core_Model {
    protected $_table_name = 'filter_collection';
    protected $_pk_field = 'id';

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['collection_id'])) {
            $data['collection_id'] = intval($data['collection_id']);
            if ($data['collection_id'] < 0) {
                $errors[] = 'collection id not found';
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == self::INSERT_FLAG) {

                if (!isset($data['added_timestamp'])) {
                    $data['added_timestamp'] = time();
                }

                if (!isset($data['modified_timestamp'])) {
                    $data['modified_timestamp'] = time();
                }
            } else {
                if (!isset($data['modified_timestamp'])) {
                    $data['modified_timestamp'] = time();
                }
            }
        }
        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['filter_setting'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['filter_setting'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key]);
            }
        }
    }

}