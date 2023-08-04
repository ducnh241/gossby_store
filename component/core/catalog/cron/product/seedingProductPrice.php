<?php

class Cron_Catalog_Product_SeedingProductPrice extends OSC_Cron_Abstract
{
    public function process($data, $queue_added_timestamp)
    {
        try {
            $product_type_variants = OSC::model('catalog/productType_variant')
                ->getCollection()
                ->addField('id', 'price', 'compare_at_price')
                ->sort('price')
                ->load();
            $product_type_variant_prices = [];
            foreach ($product_type_variants as $product_type_variant) {
                $product_type_variant_prices[$product_type_variant->getId()] = [
                    'price' => $product_type_variant->data['price'],
                    'compare_at_price' => $product_type_variant->data['compare_at_price']
                ];
            }
            $product_variants = OSC::model('catalog/product_variant')
                ->getCollection()
                ->addField('id', 'product_id', 'product_type_variant_id', 'price', 'compare_at_price', 'best_price_data')
                ->sort('price')
                ->load();

            $products = [];
            foreach ($product_variants as $product_variant) {
                if ($product_variant->data['product_type_variant_id'] > 0) {
                    if (is_array($product_variant->data['best_price_data']) && count($product_variant->data['best_price_data'])) {
                        foreach ($product_variant->data['best_price_data'] as $country_code => $best_price_data) {
                            $products[$product_variant->data['product_id']]['price'][$product_variant->getId() . '_' . $country_code] = intval(array_key_first($best_price_data));
                        }
                    } else {
                        $products[$product_variant->data['product_id']]['price'][$product_variant->getId()] = $product_type_variant_prices[$product_variant->data['product_type_variant_id']]['price'] ?? 0;
                        $products[$product_variant->data['product_id']]['compare_at_price'][$product_variant->getId()] = $product_type_variant_prices[$product_variant->data['product_type_variant_id']]['compare_at_price'] ?? 0;
                    }
                } else {
                    $products[$product_variant->data['product_id']]['price'][$product_variant->getId()] = $product_variant->data['price'];
                    $products[$product_variant->data['product_id']]['compare_at_price'][$product_variant->getId()] = $product_variant->data['compare_at_price'];
                }
            }

            $product_collections = OSC::model('catalog/product')
                ->getCollection()
                ->addField('product_id', 'price', 'compare_at_price')
                ->load();

            $errors = [];
            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getWriteAdapter();
            foreach ($product_collections as $product_collection) {
                try {
                    $max_price = $products[$product_collection->getId()]['price'] ? max($products[$product_collection->getId()]['price']) : 0;
                    $max_compare_at_price = $products[$product_collection->getId()]['compare_at_price'] ? max($products[$product_collection->getId()]['compare_at_price']) : 0;
                    $DB->update('catalog_product', ['price' => $max_price, 'compare_at_price' => $max_compare_at_price], 'product_id=' . $product_collection->getId(), 1, 'update_product_price');
                } catch (Exception $ex) {
                    $errors[$product_collection->getId()] = $ex->getMessage();
                }
            }
            if (count($errors) > 0) {
                throw new Exception(OSC::encode($errors));
            }

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
