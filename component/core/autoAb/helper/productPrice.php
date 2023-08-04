<?php

class Helper_AutoAb_ProductPrice extends OSC_Object {
    const PREFIX_COOKIE_KEY_ABTEST = 'auto_abtest_price';

    /**
     * @param $product_variant
     * @param int $price
     * @param bool $skip_ab_test
     * @throws Exception $ex
     * @return int
     */
    public function getPriceFromABTest($product_variant, int $price, $skip_ab_test = false) {
        if (!$product_variant || $skip_ab_test) {
            return $price;
        }

        $location = OSC::helper('core/common')->getClientLocation();
        $country_code = $location['country_code'];
        $product_type_variant_id = $product_variant->data['product_type_variant_id'];

        if ($product_variant->isSemiTest()) {
            $config = $this->getProductPriceConfigSemitest($product_variant->data['product_id'], $country_code);
        } else {
            $config = $this->getProductPriceConfig($country_code, $product_type_variant_id);
        }

        if ($config->data['status'] != Model_AutoAb_ProductPrice_Config::STATUS_ALLOW) {
            return $price;
        }

        if ($config instanceof Model_AutoAb_ProductPrice_Config) {
            if ($config->isFixedForAnyProducts() &&
                !in_array($product_variant->data['product_id'], $config->data['fixed_product_ids'])
            ) {
                return $price;
            }

            // Check condition is finish with campaign
            if ($config->isFinish($product_variant->data['product_id'])) {
                $this->_handleCookieABTestPrice($config->getId(), $product_variant->data['product_id'], false);

                return $price;
            }

            // Check condition is start
            if ($config->isBegin($product_variant->data['product_id'])) {
                $ab_test_price_plus = $this->getABTestPricePlus($config, $product_variant->data['product_id']);

                return intval($price + $ab_test_price_plus);
            }
        }

        return $price;
    }

    protected $_tracking_configs = null;

    public function getTrackingOfConfigABTestPrice($config_id) {
        if ($this->_tracking_configs[$config_id] === null) {
            try {
                $collection_tracking = OSC::model('autoAb/productPrice_tracking')->getCollection()
                    ->addField('product_type_variant_id', 'product_variant_id', 'quantity', 'product_id', 'revenue')
                    ->addCondition('config_id', $config_id, OSC_Database::OPERATOR_EQUAL)
                    ->load();

                $this->_tracking_configs[$config_id] = [];

                if ($collection_tracking->length() < 1) {
                    return $this->_tracking_configs[$config_id];
                }

                foreach ($collection_tracking as $tracking) {
                    $this->_tracking_configs[$config_id][] = [
                        'product_type_variant_id' => intval($tracking->data['product_type_variant_id']),
                        'product_variant_id' => intval($tracking->data['product_variant_id']),
                        'quantity' => intval($tracking->data['quantity']),
                        'product_id' => intval($tracking->data['product_id']),
                        'revenue' => intval($tracking->data['revenue']),
                    ];
                }
            } catch (Exception $ex) {}
        }

        return $this->_tracking_configs[$config_id];
    }

    /**
     * @param Model_AutoAb_ProductPrice_Config $auto_ab_test_config
     * @param $product_id
     * @return int
     * @throws Exception
     */
    public function getABTestPricePlus(Model_AutoAb_ProductPrice_Config $auto_ab_test_config, $product_id) {
        $config_id = $auto_ab_test_config->getId();
        $config_price_range = $auto_ab_test_config->data['price_range'];

        $price_range = [];
        foreach ($config_price_range as $key => $value) {
            $price_range[$key] = OSC_Controller::makeRequestChecksum($value, OSC_SITE_KEY);
        }

        if (count($price_range) === 0) {
            return 0;
        }

        $key_ab_test = static::PREFIX_COOKIE_KEY_ABTEST . '_' . implode('-', [$config_id, $product_id]);
        /* Handle price of ab test for store get same value */
        if ($auto_ab_test_config->data['condition_type'] === Model_AutoAb_ProductPrice_Config::CONDITION_STORE) {
            $key_ab_test = static::PREFIX_COOKIE_KEY_ABTEST . '_' . implode('-', [$config_id, 0]);
        }
        $value_ab_test = OSC::getABTestValue($key_ab_test, $price_range)['value'];

        /* Tracking rev ab test price if found cookie value */
        $is_tracking = false;
        $ab_test_price_plus = 0;
        if (array_search($value_ab_test, $price_range) !== false) {
            $is_tracking = true;
            $ab_test_price_plus = $config_price_range[array_search($value_ab_test, $price_range)];
        }

        $this->_handleCookieABTestPrice($config_id, $product_id, $is_tracking);

        return OSC::helper('catalog/common')->floatToInteger(intval($ab_test_price_plus));
    }

    /**
     * @param $config_id
     * @param $product_id
     * @param $is_tracking
     * @throws Exception
     */
    protected function _handleCookieABTestPrice($config_id, $product_id, $is_tracking) {
        /* Handle tracking rev ab test */
        $cookie_key_tracking_abtest = OSC_Controller::makeRequestChecksum(
            'is_tracking_abtest_' . $config_id . '_' . $product_id,
            OSC_SITE_KEY
        );
        if (is_null(OSC::cookieGet($cookie_key_tracking_abtest))) {
            OSC::cookieSetCrossSite($cookie_key_tracking_abtest, $is_tracking ? 1 : 0);
        }

        /* Handle for case tracking with traffic view ab test product but buy another product */
        $key_first_ab_test_config = OSC_Controller::makeRequestChecksum('first_ab_test_price_config', OSC_SITE_KEY);
        $key_first_product_ab_test = OSC_Controller::makeRequestChecksum('first_product_ab_test_price', OSC_SITE_KEY);
        $value_first_ab_test_config = OSC::cookieGet($key_first_ab_test_config);
        $value_first_product_ab_test = OSC::cookieGet($key_first_product_ab_test);
        if (empty($value_first_ab_test_config) && empty($value_first_product_ab_test)) {
            OSC::cookieSetCrossSite($key_first_ab_test_config, $config_id);
            OSC::cookieSetCrossSite($key_first_product_ab_test, $product_id);
        }
    }

    protected $_config_cached = null;
    /**
     * @param $country_code
     * @param $product_type_variant_id
     * @return class|OSC_Database_Model|null
     * @throws OSC_Exception_Runtime
     */
    public function getProductPriceConfig($country_code, $product_type_variant_id) {
        if (!$country_code || !$product_type_variant_id) {
            return null;
        }

        $config_id = $this->_getConfigId($country_code, $product_type_variant_id);
        if ($config_id < 1) {
            return null;
        }

        try {
            if ($this->_config_cached[$config_id] === null) {
                $this->_config_cached[$config_id] = OSC::model('autoAb/productPrice_config')->load($config_id);
            }
        } catch (Exception $ex) {
            //
        }

        return $this->_config_cached[$config_id];
    }

    protected $_semitest_config_collection = null;

    /**
     * @return OSC_Database_Model_Collection
     * @throws OSC_Exception_Runtime
     */
    public function getProductPriceConfigsSemitest() {
        if ($this->_semitest_config_collection === null) {
            $this->_semitest_config_collection = OSC::model('autoAb/productPrice_config')
                ->getCollection()
                ->addCondition('status', Model_AutoAb_ProductPrice_Config::STATUS_ALLOW)
                ->addCondition('config_type', Model_AutoAb_ProductPrice_Config::CONFIG_TYPE_SEMITEST)
                ->load();
        }

        return $this->_semitest_config_collection;
    }

    /**
     * @param $product_id
     * @param $country_code
     * @return false|mixed|null
     * @throws OSC_Exception_Runtime
     */
    public function getProductPriceConfigSemitest($product_id, $country_code) {
        $configs = $this->getProductPriceConfigsSemitest();

        foreach ($configs as $model_config) {
            if (in_array($product_id, $model_config->data['fixed_product_ids']) &&
                (in_array('*', $model_config->data['location_data']) || in_array($country_code, $model_config->data['location_data']))
            ) {
                return $model_config;
            }
        }

        return null;
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    protected function _getConfigId($country_code, $product_type_variant_id) {
        $product_price_mapping = $this->getABProductPriceOfCountry($country_code);

        $result = 0;
        foreach ($product_price_mapping as $value) {
            if ($value['product_type_variant_id'] === $product_type_variant_id) {
                return $value['config_id'];
            }
        }

        return $result;
    }

    protected $_product_price_mapping = null;
    /**
     * @throws OSC_Exception_Runtime
     */
    public function getABProductPriceOfCountry($country_code) {
        if (!isset($this->_product_price_mapping[$country_code])) {
            $collection = OSC::model('autoAb/productPrice')
                ->getCollection()
                ->addField('config_id', 'product_type_variant_id')
                ->addCondition('country_code', [$country_code, '*'], OSC_Database::OPERATOR_IN)
                ->load();

            $this->_product_price_mapping[$country_code] = [];
            foreach ($collection as $model) {
                $this->_product_price_mapping[$country_code][] = [
                    'config_id' => $model->data['config_id'],
                    'product_type_variant_id' => $model->data['product_type_variant_id']
                ];
            }
        }

        return $this->_product_price_mapping[$country_code];
    }

    public function setABProductPriceOfCountry($value, $country_code) {
        $this->_product_price_mapping[$country_code] = $value;

        return $this;
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    public function getProductPriceConfigByProduct($country_code, $product_id, $product_type_variant_id) {
        static $config_by_product_cached = [];

        if (!$country_code || !$product_id) {
            return null;
        }

        $cache_key = $country_code . '_' . $product_id;

        if (isset($config_by_product_cached[$cache_key])) {
            return $config_by_product_cached[$cache_key];
        }

        $config_by_product_cached[$cache_key] = null;
        $product_price_mapping = $this->getABProductPriceOfCountry($country_code);

        try {
            foreach ($product_price_mapping as $value) {
                if ($value['product_type_variant_id'] === $product_type_variant_id) {
                    $config_by_product_cached[$cache_key] = OSC::model('autoAb/productPrice_config')->load($value['config_id']);

                    return $config_by_product_cached[$cache_key];
                }
            }
        } catch (Exception $ex) {
            //
        }

        return null;
    }

    /**
     * @param Model_AutoAb_ProductPrice_Config $config
     * @param int $product_id
     * @throws Exception
     */
    public function setBestPriceInCountry( Model_AutoAb_ProductPrice_Config $config, $product_id = 0) {
        $fee = $config->data['fee'];
        $config_id = $config->getId();
        $country_codes = in_array('*', $config->data['location_data']) ?
            array_keys(OSC::helper('core/country')->getCountries()) :
            $config->data['location_data'];
        $condition_type = $config->data['condition_type'];

        try {
            $DB = OSC::core('database');

            if ($condition_type === Model_AutoAb_ProductPrice_Config::CONDITION_CAMPAIGN) {
                $query = <<<EOF
SELECT price_ab_test, sum(revenue) as total_revenue, sum(base_cost) as total_base_cost FROM osc_auto_ab_product_price_tracking
WHERE config_id = {$config_id}
AND product_id = {$product_id}
GROUP BY price_ab_test
EOF;
            } else {
                $query = <<<EOF
SELECT price_ab_test, sum(revenue) as total_revenue, sum(base_cost) as total_base_cost FROM osc_auto_ab_product_price_tracking
WHERE config_id = {$config_id}
GROUP BY price_ab_test
EOF;
            }

            $DB->query($query);

            $data = [];
            $revenue = [];

            while ($row = $DB->fetchArray()) {
                $revenue[$row['price_ab_test']] = intval($row['total_revenue']) * (100 - $fee) / 100 - intval($row['total_base_cost']);
                $data[] = $row;
            }

            $best_ab_test_price_int = array_search(max($revenue), $revenue);

            $ukey = 'setPriceAutoAb_' . $config->getId();

            if ($condition_type === Model_AutoAb_ProductPrice_Config::CONDITION_CAMPAIGN) {
                $ukey .= '_' . $product_id;
            } else if (
                $condition_type === Model_AutoAb_ProductPrice_Config::CONDITION_STORE &&
                count($config->data['fixed_product_ids']) > 0
            ) {
                // Case special fixed campaign ab test
                $ukey .= '_' . $product_id;
                $condition_type = Model_AutoAb_ProductPrice_Config::CONDITION_FIXED_CAMPAIGN;
            } else {
                $ukey .= '_ALL';
            }

            try {
                $model_bulk_queue = OSC::model('catalog/product_bulkQueue')->loadByUKey($ukey);
                $model_bulk_queue->delete();
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }

            try {
                OSC::model('catalog/product_bulkQueue')->setData([
                    'member_id' => 1,
                    'ukey' => $ukey,
                    'action' => 'setPriceAutoAb',
                    'queue_data' => [
                        'product_id' => $product_id,
                        'config_id' => $config->getId(),
                        'best_ab_test_price' => $best_ab_test_price_int,
                        'setting_mode' => $condition_type,
                        'revenue' => $revenue,
                        'country_codes' => $country_codes,
                        'log_price_ab_test' => $data
                    ]
                ])->save();
            } catch (Exception $ex) {

            }

            OSC::core('cron')->addQueue('catalog/campaign_setPriceAutoAbTest', null, [
                'ukey' => 'catalog/campaign_setPriceAutoAbTest',
                'requeue_limit' => -1,
                'skip_realtime',
                'estimate_time' => 60 * 60
            ]);

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
}
