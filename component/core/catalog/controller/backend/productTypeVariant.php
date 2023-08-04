<?php

class Controller_Catalog_Backend_ProductTypeVariant extends Abstract_Catalog_Controller_Backend {
    public function actionGetList() {
        $data = [];
        $product_type_collection = OSC::model('catalog/productType')
            ->getCollection()
            ->addField('id', 'title')
            ->addCondition('status', Model_Catalog_ProductType::STATE_ENABLE, OSC_Database::OPERATOR_EQUAL)
            ->sort('title')
            ->load();
        foreach ($product_type_collection as $product_type) {
            $data['_' . $product_type->getId()]['product_type_id'] = $product_type->getId();
            $data['_' . $product_type->getId()]['product_type_name'] = $product_type->data['title'];
        }

        $product_type_variants = OSC::model('catalog/productType_variant')
            ->getCollection()
            ->addField('id', 'product_type_id', 'title')
            ->sort('title')
            ->load();

        foreach ($product_type_variants as $product_type_variant) {
            $product_type_id = '_' . $product_type_variant->data['product_type_id'];

            if (isset($data[$product_type_id])) {
                $data[$product_type_id]['variants'][] = [
                    'id' => $product_type_variant->getId(),
                    'title' => $product_type_variant->data['title']
                ];
            }
        }

        $this->_ajaxResponse([
            'result' => 200,
            'data' => $data
        ]);
    }
}
