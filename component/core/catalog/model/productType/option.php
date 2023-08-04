<?php

class Model_Catalog_ProductType_Option extends Abstract_Core_Model {
    protected $_table_name = 'product_type_option';
    protected $_pk_field = 'id';
    protected $_ukey_field = 'ukey';

    protected $_option_value_collection = null;

    /**
     * @param boolean $reload
     * @return Model_Catalog_ProductType_OptionValue_Collection
     * @throws OSC_Exception_Runtime
     */
    public function getOptionValues($reload = false) {
        if ($this->_option_value_collection === null || $reload) {
            $this->_option_value_collection = OSC::model('catalog/productType_optionValue')->getCollection();

            if ($this->getId() > 0) {
                $this->_option_value_collection
                    ->addCondition('product_type_option_id', $this->getId())
                    ->addCondition('status', 1)
                    ->sort('position', OSC_Database::ORDER_ASC)
                    ->load();
                $this->_option_value_collection->preLoadModelData();

                foreach ($this->_option_value_collection as $option_value) {
                    $option_value->setOption($this);
                }
            }
        }

        return $this->_option_value_collection;
    }
}