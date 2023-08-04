<?php

class Model_Catalog_ProductType_VariantLocationPrice extends Abstract_Core_Model {
    protected $_table_name = 'product_type_variant_location_price';
    protected $_pk_field = 'id';

    protected $_allow_write_log = true;
    protected $_obj_fields = [
        'base_cost_configs'
    ];

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (!is_array($data['base_cost_configs'])) {
            $data['base_cost_configs'] = [];
        }

        foreach ($data['base_cost_configs'] as $key => $value) {
            $data[$key] = [
                'quantity' => intval($value['quantity']),
                'base_cost' => OSC::helper('catalog/common')->floatToInteger(floatval($value['base_cost']))
            ];
        }

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

        if (!is_array($data['base_cost_configs'])) {
            $data['base_cost_configs'] = [];
        }

        foreach ($data['base_cost_configs'] as $key => $value) {
            $data[$key] = [
                'quantity' => intval($value['quantity']),
                'base_cost' => OSC::helper('catalog/common')->integerToFloat(intval($value['base_cost']))
            ];
        }
    }
}
