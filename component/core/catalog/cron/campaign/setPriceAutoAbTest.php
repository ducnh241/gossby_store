<?php

class Cron_Catalog_Campaign_SetPriceAutoAbTest extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '2000M');

        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 10;

        $counter = 0;

        $count_error = 0;

        while ($counter < $limit && $count_error < 4) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'setPriceAutoAb'", '`added_timestamp` ASC, `queue_id` ASC', 1, 'fetch_queue_auto_ab_price');

            $row = $DB->fetchArray('fetch_queue_auto_ab_price');

            $DB->free('fetch_queue_auto_ab_price');

            if (!$row) {
                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            $data = $model->data['queue_data'];

            $product_id = $data['product_id'];

            $config_id = $data['config_id'];

            $best_ab_test_price = $data['best_ab_test_price'];

            $setting_mode = $data['setting_mode'];

            $revenue = $data['revenue'];

            $country_codes = $data['country_codes'];

            $log_price_ab_test = $data['log_price_ab_test'];

            try {
                $model_config = OSC::model('autoAb/productPrice_config')->load($config_id);

                $auto_ab_set_price_product_type_variants = OSC::helper('core/common')->parseProductTypeVariantIds($model_config->data['variant_data']);

                if ($setting_mode == Model_AutoAb_ProductPrice_Config::CONDITION_CAMPAIGN ||
                    $setting_mode == Model_AutoAb_ProductPrice_Config::CONDITION_FIXED_CAMPAIGN
                ) {
                    //set price campaign

                    $product = OSC::model('catalog/product')->load($product_id);

                    $this->_setPriceAbByCampaign($product, $auto_ab_set_price_product_type_variants, $best_ab_test_price, $country_codes, $revenue, $log_price_ab_test);

                    /* Handle for auto off ab test when finish */
                    $model_config = OSC::model('autoAb/productPrice_config')->load($config_id);
                    $fixed_product_ids = $model_config->data['fixed_product_ids'];
                    if (!empty($fixed_product_ids)) {
                        $product_ids_have_best_price = OSC::model('autoAb/productPrice_log')
                            ->getCollection()
                            ->addCondition('product_id', $fixed_product_ids, OSC_Database::OPERATOR_IN)
                            ->addField('product_id')
                            ->load()
                            ->toArray();
                        $product_ids_have_best_price = is_array($product_ids_have_best_price) && !empty($product_ids_have_best_price) ?
                            array_unique(array_column($product_ids_have_best_price, 'product_id')) :
                            [];

                        if (count($product_ids_have_best_price) === count($fixed_product_ids)) {
                            $model_config->setData(['status' => Model_AutoAb_ProductPrice_Config::STATUS_OFF])->save();
                            OSC::model('autoAb/productPrice')->getCollection()
                                ->addCondition('config_id', $config_id)
                                ->load()
                                ->delete();
                        }
                    }
                } else {
                    //set price store

                    $version = time();

                    if (count($auto_ab_set_price_product_type_variants) > 0) {
                        $collection_product_type_variant = OSC::model('catalog/productType_variant')->getCollection()->load($auto_ab_set_price_product_type_variants);

                        foreach ($collection_product_type_variant as $product_type_variant) {
                            $best_price_data = $product_type_variant->data['best_price'];

                            if (!is_array($best_price_data) || count($best_price_data) < 1) {
                                $best_price_data = [];
                            }

                            $best_price_int = $product_type_variant->data['price'] + $best_ab_test_price;

                            foreach ($country_codes as $country_code) {
                                $best_price_data[$country_code] = [$best_price_int => $version];
                            }

                            $product_type_variant->setData(['best_price' => $best_price_data])->save();

                            $best_price_float = OSC::helper('catalog/common')->integerToFloat($best_price_int);

                            try {
                                OSC::model('autoAb/productPrice_log')->setData([
                                    'product_id' => 0,
                                    'product_variant_id' => 0,
                                    'product_type_variant_id' => $product_type_variant->getId(),
                                    'note' => [
                                        'country_codes' => $country_codes,
                                        'old_price' => OSC::helper('catalog/common')->integerToFloat($product_type_variant->data['price']),
                                        'new_price' => $best_price_float,
                                        'list_price_tracking' => $log_price_ab_test,
                                        'list_revenue' => $revenue
                                    ]
                                ])->save();
                            } catch (Exception $exception) {

                            }
                        }

                        $model_config = OSC::model('autoAb/productPrice_config')->load($config_id);
                        $model_config->setData(['status' => Model_AutoAb_ProductPrice_Config::STATUS_OFF])->save();
                        OSC::model('autoAb/productPrice')->getCollection()
                            ->addCondition('config_id', $config_id)
                            ->load()
                            ->delete();

                        if (BOX_TELEGRAM_TELEGRAM_GROUP_ID) {
                            $message = 'Product Type variants #' . implode(', ', array_unique($auto_ab_set_price_product_type_variants)) . ' has been set the best price in: ' .
                                implode(', ', $country_codes) . "\n" .
                                ' FOR STORE ' .
                                '$ -Extra New Price: ' .  OSC::helper('catalog/common')->integerToFloat($best_ab_test_price) . '$';

                            OSC::helper('core/telegram')->sendMessage($message, BOX_TELEGRAM_TELEGRAM_GROUP_ID);
                        }
                    }
                }

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 2, 'added_timestamp' => time()])->save();
                $count_error++;
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }

    protected function _setPriceAbByCampaign(Model_Catalog_Product $product, $auto_ab_set_price_product_type_variants, $best_ab_test_price, $country_codes, $revenue, $log_price_ab_test) {
        $product_id = $product->getId();

        $product_variant_id_send_tele = [];

        $variants = $product->getVariants(true, false);

        $version = time();

        foreach ($variants as $variant) {
            $product_type_variant_id = $variant->isSemiTest() ? 0 : $variant->getProductTypeVariant()->getId();

            if ($product_type_variant_id !== 0 && !in_array($product_type_variant_id, $auto_ab_set_price_product_type_variants)) {
                continue;
            }

            $default_price_data = $variant->getDefaultPriceData();

            $default_price = $default_price_data['price'];

            $best_price_int = $default_price + $best_ab_test_price;

            // Set best price for product variant
            $best_price_data = $variant->data['best_price_data'];

            if (!is_array($best_price_data) || count($best_price_data) < 1) {
                $best_price_data = [];
            }

            foreach ($country_codes as $country_code) {
                $best_price_data[$country_code] = [$best_price_int => $version];
            }

            $best_price_float = OSC::helper('catalog/common')->integerToFloat($best_price_int);

            try {
                $variant->setData(['best_price_data' => $best_price_data])->save();

                OSC::model('autoAb/productPrice_log')->setData([
                    'product_id' => $product_id,
                    'product_variant_id' => $variant->getId(),
                    'product_type_variant_id' => $product_type_variant_id,
                    'note' => [
                        'country_codes' => $country_codes,
                        'old_price' => OSC::helper('catalog/common')->integerToFloat($default_price),
                        'new_price' => $best_price_float,
                        'list_price_tracking' => $log_price_ab_test,
                        'list_revenue' => $revenue
                    ]
                ])->save();
            } catch (Exception $exception) {

            }

            $product_variant_id_send_tele[] = $variant->getId();
        }

        if (BOX_TELEGRAM_TELEGRAM_GROUP_ID && count($product_variant_id_send_tele) > 0) {
            $message = 'Product variants #' . implode(', ', $product_variant_id_send_tele) . ' has been set the best price in: ' .
                implode(', ', $country_codes) . "\n" .
                'Product Title: ' . $product->data['title'] . "\n" .
                '$ -Extra New Price: ' . OSC::helper('catalog/common')->integerToFloat($best_ab_test_price) . '$';

            OSC::helper('core/telegram')->sendMessage($message, BOX_TELEGRAM_TELEGRAM_GROUP_ID);
        }
    }
}