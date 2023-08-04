<?php

class Cron_Catalog_InsertCollectionProductRel extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);

        $DB = OSC::core('database');
        $paged = 0;
        $page_size = 100;
        $type = $params['type'];

        if ($type === 'product') {
            try {
                $product = OSC::model('catalog/product')->load($params['product_id']);
                $product_id = $product->getId();
                //Remove all rel by product_id
                $DB->query("DELETE FROM {$DB->getTableName('collection_product_rel')} WHERE `product_id` =:value", ['value' => $product_id], 'clean_old_items');
            } catch (Exception $e) {
            }

            // Insert new record
            $collections = OSC::helper('catalog/collectionProductRel')->getCollectionsByProduct($product, false);
            foreach ($collections as $collections_id) {
                try {
                    OSC::model('catalog/collectionProductRel')
                        ->setData([
                            'collection_id' => $collections_id,
                            'product_id' => $product_id,
                            'added_timestamp' => time(),
                            'modified_timestamp' => time()
                        ])
                        ->save();
                } catch (Exception $e) {
                }
            }

            return;

        } elseif ($type === 'collection') {
            try {
                $collection = OSC::model('catalog/collection')->load($params['collection_id']);
                $collection_id = $collection->getId();
                //Remove all rel by collection_id
                $DB->query("DELETE FROM {$DB->getTableName('collection_product_rel')} WHERE `collection_id` =:value", ['value' => $collection_id], 'clean_old_items');
            } catch (Exception $e) {
            }

            //Get products per page
            $options = [
                'page_size' => $page_size,
                'page' => intval($paged)
            ];

            $products_collection = OSC::helper('catalog/collectionProductRel')->getProductsByCollection($collection, $options, false, false);
            $pages_length = $products_collection->getTotalPage();

            while ($paged < $pages_length) {
                $paged = $paged + 1;
                $options = [
                    'order_by' => 'product_id',
                    'order' => 'ASC',
                    'page_size' => $page_size,
                    'page' => intval($paged)
                ];
                $products = OSC::helper('catalog/collectionProductRel')->getProductsByCollection($collection, $options, false, false);
                foreach ($products as $product) {
                    try {
                        OSC::model('catalog/collectionProductRel')
                            ->setData([
                                'product_id' => $product->getId(),
                                'collection_id' => $collection_id,
                                'added_timestamp' => time(),
                                'modified_timestamp' => time()
                            ])
                            ->save();
                    } catch (Exception $e) {
                    }
                }
            }

            if ($paged >= $pages_length) {
                return;
            }

        }

        return;
    }
}
