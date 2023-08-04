<?php

class Model_Catalog_ProductType_Variant_Collection extends Abstract_Core_Model_Collection {
    protected $_product_type_preloaded = false;

    public function preLoadProductTypes() {
        if ($this->_product_type_preloaded) {
            return $this;
        }

        $product_type_ids = [];

        foreach ($this as $product_type_variant) {
            $product_type_ids[] = $product_type_variant->data['product_type_id'];
        }

        if (count($product_type_ids) > 0) {
            $product_types = OSC::model('catalog/productType')->getCollection()->load($product_type_ids);

            foreach ($this as $product_type_variant) {
                $product_type = $product_types->getItemByPK($product_type_variant->data['product_type_id']);

                if ($product_type) {
                    $product_type_variant->setProductType($product_type);
                }
            }
        }

        $this->_product_type_preloaded = true;

        return $this;
    }
}