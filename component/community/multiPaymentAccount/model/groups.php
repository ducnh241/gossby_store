<?php

class Model_MultiPaymentAccount_Groups extends Abstract_Core_Model
{
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';
    protected $_table_name = 'payment_groups';
    protected $_pk_field = 'group_id';

    const ID_GROUP_PAYMENT_STORE_DE = 5;

    protected function _beforeSave()
    {
        parent::_beforeSave();
        $data = $this->_collectDataForSave();
        $errors = [];

        $data['group_name'] = trim($data['group_name']);

        if (!$data['group_name']) {
            $errors[] = 'group name is empty';
        }

        if (!$data['location_data']) {
            $errors[] = 'Location group is empty';
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }

    }
    protected function _preDataForUsing(&$data) {
        if (isset($data['meta_data'])) {
            $data['meta_data'] = OSC::decode($data['meta_data']);
        }
        parent::_preDataForUsing($data);

    }

    protected function _preDataForSave(&$data) {
        if (isset($data['meta_data'])) {
            $data['meta_data'] = OSC::encode($data['meta_data']);
        }
        parent::_preDataForSave($data);

    }
}