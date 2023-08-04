<?php

class Model_Catalog_SupplierVariantRel_Collection extends Abstract_Core_Model_Collection {
    public function getSuppliersByProductTypeAndPrintTemplate($product_type_variant_id, $print_template_id) {
        return $this->addCondition('product_type_variant_id', intval($product_type_variant_id))
            ->addCondition('print_template_id', intval($print_template_id))
            ->load();
    }
}