<?php

class Model_CrossSell_CrossSell_DesignColor extends Abstract_Core_Model {
    protected $_table_name = 'cross_sell_design_color';
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
}