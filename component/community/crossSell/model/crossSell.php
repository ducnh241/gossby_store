<?php

class Model_CrossSell_CrossSell extends Abstract_Core_Model {
    protected $_table_name = 'cross_sell';
    protected $_pk_field = 'id';
    protected $_ukey_field = 'product_type_variant_id';

    protected function _beforeSave()
    {
        parent::_beforeSave();
        $data = $this->_collectDataForSave();

        if ($this->getActionFlag() == static::INSERT_FLAG) {
            $default_fields = [
                'added_timestamp' => time(),
                'modified_timestamp' => time()
            ];

            foreach ($default_fields as $field_name => $default_value) {
                if (!isset($data[$field_name])) {
                    $data[$field_name] = $default_value;
                }
            }
        } else {
            if (!isset($data['modified_timestamp'])) {
                $data['modified_timestamp'] = time();
            }
        }
        if ($data['discount_type'] === 'fixed_amount') {
            $data['discount_value'] = OSC::helper('catalog/common')->floatToInteger(floatval($data['discount_value']));
        }
        $this->resetDataModifiedMap()->setData($data);
    }

    protected function _afterSave() {
        parent::_afterSave();
        $dataSync = [
            'key' => $this->_table_name,
            'data' => $this->data
        ];
        if ($this->getLastActionFlag() == static::INSERT_FLAG) {
            $dataSync['action'] = 'insert';
        } else {
            $dataSync['action'] = 'update';
        }
        OSC::helper('masterSync/common')->syncProductConfig($dataSync);
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);
        if ($data['discount_type'] === 'fixed_amount') {
            $data['discount_value'] = OSC::helper('catalog/common')->integerToFloat(intval($data['discount_value']));
        }
    }
}