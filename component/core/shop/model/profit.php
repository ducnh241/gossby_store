<?php

class Model_Shop_Profit extends Abstract_Core_Model {
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';
    protected $_table_name = 'shop_profit';
    protected $_pk_field = 'id';
    protected $_obj_fields = ['additional_data'];

    protected function _beforeSave()
    {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        if ($this->getActionFlag() == static::INSERT_FLAG) {
            $data['added_timestamp'] = time();
        }
        $data['modified_timestamp'] = time();

        $this->resetDataModifiedMap()->setData($data);

    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach ($this->_obj_fields as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach ($this->_obj_fields as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }
}