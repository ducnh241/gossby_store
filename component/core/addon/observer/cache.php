<?php

class Observer_Addon_Cache {
    public function resetCache($params) {
        try {
            $class = get_class($params['model']);
            switch ($class) {
                case Model_Addon_Service::class:
                    $addon_service_id = $params['model']->data['id'];

                    $products = OSC::model('catalog/product')
                        ->getCollection()
                        ->addField('product_id', 'sku')
                        ->addCondition('addon_service_data', ',' . $addon_service_id . ',', OSC_Database::OPERATOR_LIKE)
                        ->load();

                    foreach ($products as $product) {
                        OSC::helper('core/cache')->insertResetCacheQueue(Helper_Core_Cache::MODEL_CATALOG_PRODUCT, $product->getId(), [
                            'product_id' => $product->getId(),
                            'product_sku' => $product->data['sku']
                        ]);
                    }

                    break;
                default:
                    break;
            }
        } catch (Exception $exception) {
        }
    }
}
