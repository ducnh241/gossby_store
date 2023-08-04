<?php

class Model_Catalog_ProductType_OptionValue extends Abstract_Core_Model {
    protected $_table_name = 'product_type_option_value';
    protected $_pk_field = 'id';
    protected $_ukey_field = 'ukey';

    protected $_option = null;

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['meta_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

    /**
     *
     * @param Model_Catalog_ProductType_Option $option
     * @return $this
     */
    public function setOption($option) {
        $this->_option = ($option instanceof Model_Catalog_ProductType_Option) ? $option : null;

        return $this;
    }
}