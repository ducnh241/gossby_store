<?php

class Observer_Catalog_Cache extends OSC_Object {
    public function resetCache($params, $method = 'save') {
        $mapping = [
            Model_Catalog_Product::class => ['upc', 'member_id', 'position_index', 'slug', 'title', 'topic', 'description', 'content', 'product_type', 'vendor', 'price', 'compare_at_price', 'discarded', 'listing', 'seo_status', 'tags', 'meta_tags', 'seo_tags', 'meta_tags', 'options', 'collection_ids', 'master_lock_flag'],
        ];

        try {
            $class = get_class($params['model']);
            switch ($class) {
                case Model_Catalog_Product::class:
                    $product = $params['model'];

                    if (isset($params['columns']) && !empty($params['columns'])) {
                        $data = array_map(function ($item) {
                            return trim($item);
                        }, explode(',', $params['columns']));
                    } else {
                        $data = $params['model']->getModifiedData();
                        $data = !empty($data) ? array_keys($data) : [];
                    }

                    if (!empty(array_intersect($data, $mapping[$class])) || $method === 'delete') {
                        OSC::helper('core/cache')->insertResetCacheQueue(Helper_Core_Cache::MODEL_CATALOG_PRODUCT, $product->getId(), [
                            'product_id' => $product->getId(),
                            'product_sku' => $product->data['sku']
                        ]);
                    }

                    if ($method === 'delete') {
                        OSC::helper('core/cache')->insertResetCacheQueue('DELETE_' . Helper_Core_Cache::MODEL_CATALOG_PRODUCT, $product->getId());
                    }

                    break;
                default:
                    break;
            }
        } catch (Exception $exception) { }
    }

    public function resetCacheDelete($params) {
        $this->resetCache($params, 'delete');
    }
}