<?php

class Helper_Catalog_Common extends OSC_Object {

    protected static $_displayed_product_ids = [];

    public function fetchEstimateTimeExceptWeekendDays($days) {
        $days = intval($days);

        if ($days <= 0) {
            return 0;
        }

        $current_timestamp = time();
        $count_weekend = 0;
        $count_weekend_in_plus_days = 0;

        for ($i = 1; $i <= $days; $i++) {
            $timestamp = $current_timestamp + 60 * 60 * 24 * $i;

            if ($this->isWeekend($timestamp)) {
                $count_weekend++;
            }
        }

        for ($i = 1; $i <= $count_weekend; $i++) {
            $timestamp_plus_day = $current_timestamp + 60 * 60 * 24 * $days + 60 * 60 * 24 * $i;

            if ($this->isWeekend($timestamp_plus_day)) {
                $count_weekend_in_plus_days++;
            }
        }

        return $current_timestamp + 60 * 60 * 24 * $days + 60 * 60 * 24 * $count_weekend + 60 * 60 * 24 * $count_weekend_in_plus_days;
    }

    function isWeekend($timestamp) {
        return (date('N', $timestamp) >= 6);
    }

    public function getCutOffTimestamp($variant, $product_type = null)
    {
        try {
            if (intval(OSC::helper('core/setting')->get('shipping/cut_off_time/enable')) != 1) {
                throw new Exception('Not enable cut off time');
            }

            $cut_off_time_list = OSC::helper('core/setting')->get('shipping/cut_off_time/table');
            $cut_off_table = [];

            foreach ($cut_off_time_list as $product_types_cot) {
                foreach ($product_types_cot['product_types'] as $product_type) {
                    if (in_array('manual', $product_types_cot['product_types'])) {
                        foreach ($product_types_cot['meta_data'] as $meta_data) {
                            $cut_off_table['manual'][] = $meta_data;
                        }
                    } else {
                        unset($product_types_cot['product_types']);
                        $cut_off_table[$product_type] = array_merge($cut_off_table[$product_type] ?? [], $product_types_cot);
                    }
                }
            }

            if (empty($cut_off_table)) {
                throw new Exception('Not have data setting cut off time');
            }

            if (!($variant instanceof Model_Catalog_Product_Variant) &&
                !($product_type instanceof Model_Catalog_ProductType)
            ) {
                throw new Exception('Data get cut off time is incorrect');
            }

            $product_type = ($product_type instanceof Model_Catalog_ProductType && $product_type->getId() > 0) ? $product_type : null;

            $product_type = $product_type == null ? $variant->getProductType() : $product_type;

            $location = $this->getCustomerShippingLocation();

            $country_code = $location['country_code'] ?? '';
            try {
                $country = OSC::helper('core/country')->getCountry($country_code);
                $country_location = 'c' . $country->getId();
            } catch (Exception $ex) {
                throw new Exception('Not found country to get cut off time');
            }

            $province_code = $location['province_code'] ?? '';
            try {
                $province = OSC::helper('core/country')->getCountryProvince($country_code, $province_code);
                $province_location = $province->getId() > 0 ? 'p' . $province->getId() : null;
            } catch (Exception $ex) {
                $province_location = null;
            }

            $cut_off_timestamp = 0;

            if (
                $variant instanceof Model_Catalog_Product_Variant &&
                $variant->getProduct() instanceof Model_Catalog_Product &&
                isset($cut_off_table['manual']) &&
                is_array($cut_off_table['manual'])
            ) {
                $product_id = $variant->getProduct()->getId();

                foreach ($cut_off_table['manual'] as $manual_config) {
                    if (
                        is_array($manual_config['product_ids']) &&
                        in_array($product_id, $manual_config['product_ids']) &&
                        in_array($manual_config['location'], [ '*', $province_location, $country_location ])
                    ) {
                        $cut_off_timestamp = $manual_config['time'];
                    }
                }
            }

            if (
                !$cut_off_timestamp &&
                $variant instanceof Model_Catalog_Product_Variant &&
                $variant->getProduct() instanceof Model_Catalog_Product &&
                $variant->getProduct()->isSemitestMode()
            ) {
                throw new Exception('Not set default cut off time for semitest products.');
            }

            if (!$cut_off_timestamp) {
                $list_cut_off_table = [];

                if (isset($cut_off_table[$product_type->getUkey()])) {
                    $list_cut_off_table[] = $cut_off_table[$product_type->getUkey()];
                }

                if (isset($cut_off_table['*'])) {
                    $list_cut_off_table[] = $cut_off_table['*'];
                }

                if (empty($list_cut_off_table)) {
                    throw new Exception('Data get cut off time is incorrect');
                }

                foreach ($list_cut_off_table as $cut_off_table_item) {
                    foreach ($cut_off_table_item as $location => $date) {
                        $compare_location = $province_location ? OSC::helper('core/country')->compareLocation($province_location, $location) : false;

                        $compare_location = $compare_location == false ? OSC::helper('core/country')->compareLocation($country_location, $location) : $compare_location;

                        if ($compare_location) {
                            $cut_off_timestamp = $date;
                            break;
                        }
                    }
                    if ($cut_off_timestamp != 0) {
                        break;
                    }
                }
            }

            if ($cut_off_timestamp === 0) {
                throw new Exception('Not map data cut off time');
            }

            $cut_off_timestamp = explode(' ', $cut_off_timestamp);
            $cut_off_timestamp[0] = explode('/', $cut_off_timestamp[0]);
            $cut_off_timestamp[1] = explode(':', $cut_off_timestamp[1]);
            return mktime($cut_off_timestamp[1][0], $cut_off_timestamp[1][1], 0, $cut_off_timestamp[0][1], $cut_off_timestamp[0][0], $cut_off_timestamp[0][2]);
        } catch (Exception $ex) {
            return 0;
        }
    }

    public static function recentlyViewedProductGet() {
        $recently_viewed_products = OSC::cookieGet('catalog/recently_viewed_products');

        if (!$recently_viewed_products) {
            $recently_viewed_products = [];
        } else {
            $recently_viewed_products = OSC::decode($recently_viewed_products, true);

            if (!is_array($recently_viewed_products)) {
                $recently_viewed_products = [];
            } else {
                $recently_viewed_products = array_map(function($id) {
                    return intval($id);
                }, $recently_viewed_products);

                $recently_viewed_products = array_filter($recently_viewed_products, function($id) {
                    return $id > 0;
                });
            }
        }

        return $recently_viewed_products;
    }

    public static function displayedProductRegister($product_ids) {
        if (!is_array($product_ids)) {
            $product_ids = [$product_ids];
        }

        $product_ids = array_map(function($product_id) {
            return intval($product_id);
        }, $product_ids);

        $product_ids = array_filter($product_ids, function($product_id) {
            return $product_id > 0;
        });

        Helper_Catalog_Common::$_displayed_product_ids = array_merge(Helper_Catalog_Common::$_displayed_product_ids, $product_ids);
    }

    public static function displayedProductRegistry() {
        return Helper_Catalog_Common::$_displayed_product_ids;
    }

    public function getWeightInGram(int $weight, $weight_unit) {
        switch (strtolower(trim($weight_unit))) {
            case 'kg':
                $weight *= 1000;
                break;
            case 'oz':
                $weight *= 28.35;
                break;
            case 'lb':
            case 'lbs':
                $weight *= 453.592;
                break;
        }

        return intval(OSC::helper('catalog/common')->integerToFloat($weight));
    }

    public function getWeightByGram(int $weight_in_gram, $weight_unit): float {
        switch (strtolower(trim($weight_unit))) {
            case 'kg':
                $weight_in_gram /= 1000;
                break;
            case 'oz':
                $weight_in_gram /= 28.35;
                break;
            case 'lb':
            case 'lbs':
                $weight_in_gram /= 453.592;
                break;
        }

        return round($weight_in_gram, 2);
    }

    public function floatToInteger(float $price): int {
        return round(round($price, 2) * 100);
    }

    public function integerToFloat($price): float {
        return round(intval($price) / 100, 2);
    }

    public function formatPrice(float $amount, $format = null, $price_rate = false, $keep_decimals = false) {
        if ($price_rate == true && OSC::helper('core/setting')->get('catalog/convert_currency/enable')) {
            $priceExchangeRate = $this->priceExchangeRate();

            $formats = [
                'html_with_currency' => '<span>{{negative_mark}}' . $priceExchangeRate['icon'] . '{{amount}} ' . $priceExchangeRate['currency_code'] . '</span>',
                'html_without_currency' => '<span>{{negative_mark}}' . $priceExchangeRate['icon'] . '{{amount}}</span>',
                'email_with_currency' => '{{negative_mark}}' . $priceExchangeRate['icon'] . '{{amount}} ' . $priceExchangeRate['currency_code'],
                'email_without_currency' => '{{negative_mark}}' . $priceExchangeRate['icon'] . '{{amount}}'
            ];

            $amount = $amount * $priceExchangeRate['rate'];

            $amount = floatval($amount);
            $negative_mark = $amount >= 0 ? '' : '-';
            $amount = abs($amount);
        } else {
            $formats = [
                'html_with_currency' => '<span>{{negative_mark}}${{amount}} USD</span>',
                'html_without_currency' => '<span>{{negative_mark}}${{amount}}</span>',
                'email_with_currency' => '{{negative_mark}}${{amount}} USD',
                'email_without_currency' => '{{negative_mark}}${{amount}}'
            ];

            $amount = floatval($amount);
            $negative_mark = $amount >= 0 ? '' : '-';
            $amount = abs($amount);
        }
        $amount = number_format(round($amount, 2), 2);

        $replaces = [
            '{{negative_mark}}' => $negative_mark,
            '{{amount}}' => $keep_decimals ?
                $amount :
                (strpos($amount, '.') !== false ? rtrim(rtrim($amount, '0'), '.') : $amount),
            '{{amount_no_decimals}}' => number_format(round($amount, 0))
        ];

        if (!isset($formats[$format])) {
            $format = 'html_without_currency';
        }

        return str_replace(array_keys($replaces), $replaces, $formats[$format]);
    }

    public function formatPriceByInteger(int $amount, $format = null, $price_rate = false) {
        return $this->formatPrice($this->integerToFloat($amount), $format, $price_rate);
    }

    public function formatAddress(array $address, string $line_breaker = "<br />"): string {
        $lines = [];

        $lines[] = $address['full_name'];
        $lines[] = $address['address1'];

        if (isset($address['address2']) && $address['address2']) {
            $lines[] = $address['address2'];
        }

        if (isset($address['city'])) {
            $lines[] = $address['city'] . ' ' . $address['province_code'] . ' ' . $address['zip'];
        } else {
            $lines[] = $address['province'] . ' ' . $address['city'];
        }

        $lines[] = $address['country'];
        $lines[] = $address['phone'];

        if ($line_breaker == 'div') {
            return '<div>' . implode('</div><div>', $lines) . '</div>';
        } else if ($line_breaker == 'p') {
            return '<p>' . implode('</p><p>', $lines) . '</p>';
        }

        return implode($line_breaker, $lines);
    }

    /**
     *
     * @param boolean $create_if_not_exists
     * @return Model_Catalog_Cart
     * @throws Exception
     */
    public function getCart($create_if_not_exists = true, $check_calculate_discount = true) {
        static $cache = null;

        if ($cache) {
            return $cache;
        }

        $cookie_key = OSC_SITE_KEY . '-cart';

        /* @var $cart Model_Catalog_Cart */
        $cart = OSC::model('catalog/cart');

        if (isset($_COOKIE[$cookie_key]) && $_COOKIE[$cookie_key]) {
            try {
                $cart = $this->_loadCartByUkey($cart, $_COOKIE[$cookie_key]);
            } catch (Exception $ex) {
                if ($ex->getCode() !== 404) {
                    throw new Exception($ex->getMessage());
                }
            }
        }

        if ($cart->getId() < 1) {
            if (!$create_if_not_exists) {
                return null;
            }

            $location = OSC::helper('catalog/common')->getCustomerShippingLocation();
            $country_code = $location['country_code'] ?? '';
            $province_code = $location['province_code'] ?? '';
            $shipping_province = OSC::helper('core/country')->getProvinceTitle($country_code, $province_code);
            $shipping_country = OSC::helper('core/country')->getCountryTitle($country_code);
            $cart->setData([
                'shipping_country_code' => $country_code,
                'shipping_province_code' => $province_code,
                'shipping_province' => $shipping_province,
                'shipping_country' => $shipping_country,
                'added_timestamp' => time()
            ])->save();

            $this->setCart($cart);
        } else {
            /* @var $line_item Model_Catalog_Cart_Item */
            foreach ($cart->getLineItems() as $line_item) {
                if ($line_item->isCrossSellMode()) {
                    continue;
                }
                if ( !$line_item->getVariant() ||
                    !$line_item->getVariant()->ableToOrder() ||
                    !$line_item->getProduct() ||
                    !$line_item->getProduct()->isAvailable()
                ) {
                    $cart->getLineItems()->removeItemByKey($line_item->getId());

                    try {
                        $line_item->delete();
                    } catch (Exception $ex) {

                    }
                }
            }

            if ($check_calculate_discount === true) {
                $cart->calculateDiscount();
            }
        }

        if (!$cart->data['shipping_country_code'] && count($cart->getShippingAddress()) < 1) {
            $LAST_ORDER_ADDR = OSC::cookieGet(OSC_SITE_KEY . '-LOA');

            if ($LAST_ORDER_ADDR) {
                $LAST_ORDER_ADDR = OSC::decode(base64_decode($LAST_ORDER_ADDR));

                if (is_array($LAST_ORDER_ADDR)) {
                    foreach ($LAST_ORDER_ADDR as $k => $v) {
                        if ($k == 'email') {
                            if ($cart->data['email']) {
                                continue;
                            }
                        } else {
                            $k = 'shipping_' . $k;
                        }

                        $cart->setData($k, $v);
                    }

                    try {
                        $cart->save();
                    } catch (Exception $ex) {
                        $cart->revert();
                    }
                }
            }
        }

        $cache = $cart;

        return $cart;
    }

    protected $_carts = [];

    /**
     * @param Model_Catalog_Cart $cart
     * @param $ukey
     * @return Model_Catalog_Cart
     * @throws Exception
     */
    protected function _loadCartByUkey(Model_Catalog_Cart $cart, $ukey) {
        if (isset($this->_carts[$ukey]) && $this->_carts[$ukey] instanceof Model_Catalog_Cart) {
            return $this->_carts[$ukey];
        }

        $this->_carts[$ukey] = $cart;

        try {
            $pre_cart_ukey = $ukey;
            $ukey = $cart::cleanUkey($ukey);
            if ($ukey != $pre_cart_ukey) {
                OSC::helper('core/common')->writeLog('Wrong cart ukey', $pre_cart_ukey);
            }

            if (!$ukey || !preg_match('/^[a-z0-9]{13,14}$/is', $ukey)) {
                throw new Exception('Ukey is empty');
            }

            $this->_carts[$ukey] = $cart->loadByUKey($ukey);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return $this->_carts[$ukey];
    }

    public function setCart(Model_Catalog_Cart $cart) {
        OSC::cookieSetCrossSite(OSC_SITE_KEY . '-cart', $cart->data['ukey']);
        return $this;
    }

    public function updateCartQuantity($cart) {
        if ($cart instanceof Model_Catalog_Cart) {
            OSC::cookieSetCrossSite('cart-quantity', $cart->getQuantity());
        }
    }

    public function getCartQuantity() {
        return OSC::cookieGet('cart-quantity');
    }

    public function getCartDetailUrl() {
        return OSC_FRONTEND_BASE_URL . '/cart';
    }

    public function getCustomer() {
        $member = OSC::helper('user/authentication')->getMember();

        if ($member->getId() < 1) {
            return null;
        }

        try {
            $customer = OSC::helper('account/customer')->create([
                'shop_id' => OSC::getShop()->getId(),
                'email' => $member->data['email']
            ]);

            if (isset($customer['customer_id']) && $customer['customer_id'] > 0) {
                $this->setData('customer_id', $customer['customer_id'])->save();
            }

            return $customer;
        } catch (Exception $ex) {
            return null;
        }

    }

    public function collectCustomerGroup() {
        $customer_groups = [];

        OSC::core('observer')->dispatchEvent('catalog/collectCustomerGroup', ['groups' => &$customer_groups]);

        if (!is_array($customer_groups)) {
            $customer_groups = [];
        } else {
            $customer_groups = array_map(function($data) {
                if (!is_array($data) || !isset($data['title']) || !isset($data['verifier']) || !is_callable($data['verifier'])) {
                    return false;
                }

                $data['title'] = trim($data['title']);

                if ($data['title'] === '') {
                    return false;
                }

                return $data;
            }, $customer_groups);

            $customer_groups = array_filter($customer_groups, function($data) {
                return is_array($data);
            });
        }

        return $customer_groups;
    }

    /**
     *
     * @param mixed $collection_ids
     * @return array
     */
    public function loadCollectionSelectorData($collection_ids) {
        if (!is_array($collection_ids)) {
            $collection_ids = [$collection_ids];
        }

        $collection_ids = array_map(function($collection_id) {
            return intval($collection_id);
        }, $collection_ids);
        $collection_ids = array_filter($collection_ids, function($collection_id) {
            return $collection_id > 0;
        });

        if (count($collection_ids) < 1) {
            return [];
        }

        try {
            /* @var $collection Model_Catalog_Collection_Collection */
            /* @var $catalog_collection Model_Catalog_Collection */
            $collection = OSC::model('catalog/collection')->getCollection()->load($collection_ids);

            $catalog_collections = [];

            foreach ($collection as $catalog_collection) {
                $catalog_collections[] = [
                    'id' => $catalog_collection->getId(),
                    'title' => $catalog_collection->data['title'],
                    'url' => $catalog_collection->getDetailUrl(),
                    'image' => $catalog_collection->getImageUrl()
                ];
            }

            return $catalog_collections;
        } catch (Exception $ex) {
            return [];
        }
    }

    public function loadProductSelectorData($product_ids, $variant_ids = []) {
        if (!is_array($product_ids)) {
            $product_ids = [$product_ids];
        } else {
            $product_ids = array_map(function($product_id) {
                return intval($product_id);
            }, $product_ids);
            $product_ids = array_filter($product_ids, function($product_id) {
                return $product_id > 0;
            });
        }

        if (!is_array($variant_ids)) {
            $variant_ids = [$variant_ids];
        } else {
            $variant_ids = array_map(function($variant_id) {
                return intval($variant_id);
            }, $variant_ids);
            $variant_ids = array_filter($variant_ids, function($variant_id) {
                return $variant_id > 0;
            });
        }

        $items = [];

        try {
            $variants = [];

            if (count($variant_ids) > 0) {
                foreach (OSC::model('catalog/product_variant')->getCollection()->load($variant_ids) as $variant) {
                    if (!isset($variants[$variant->data['product_id']])) {
                        $variants[$variant->data['product_id']] = [];
                    }

                    $variants[$variant->data['product_id']][] = [
                        'id' => intval($variant->getId()),
                        'option1' => $variant->data['option1'],
                        'option2' => $variant->data['option2'],
                        'option3' => $variant->data['option3'],
                        'price' => intval($variant->data['price']),
                        'compare_at_price' => intval($variant->data['compare_at_price'])
                    ];

                    $product_ids[] = $variant->data['product_id'];
                }
            }

            if (count($product_ids) > 0) {
                foreach (OSC::model('catalog/product')->getCollection()->load($product_ids) as $product) {
                    $item = [
                        'id' => intval($product->getId()),
                        'title' => $product->getProductTitle(),
                        'url' => $product->getDetailUrl(),
                        'type' => $product->data['product_type'],
                        'vendor' => $product->data['vendor'],
                        'price' => intval($product->data['price']),
                        'total_variant' => -1,
                        'options' => $product->getOrderedOptions(true),
                        'collection_ids' => $product->data['collection_ids'],
                        'image' => $product->getFeaturedImageUrl()
                    ];

                    $items[] = $item;

                    if (isset($variants[$product->getId()])) {
                        foreach ($variants[$product->getId()] as $variant) {
                            $_item = $item;
                            $_item['variant'] = $variant;
                            $items[] = $_item;
                        }
                    }
                }
            }
        } catch (Exception $ex) {

        }

        return $items;
    }

    public function loadCustomerGroupSelectorData($group_keys) {
        $groups = [];

        try {
            foreach (OSC::helper('catalog/common')->collectCustomerGroup() as $group_key => $group_data) {
                if (in_array($group_key, $group_keys, true)) {
                    $groups[] = [
                        'id' => $group_key,
                        'title' => $group_data['title']
                    ];
                }
            }
        } catch (Exception $ex) {

        }

        return $groups;
    }

    public function loadCustomerSelectorData($customer_ids) {
        if (!is_array($customer_ids)) {
            $customer_ids = [$customer_ids];
        }

        $customer_ids = array_map(function($customer_id) {
            return intval($customer_id);
        }, $customer_ids);
        $customer_ids = array_filter($customer_ids, function($customer_id) {
            return $customer_id > 0;
        });

        if (count($customer_ids) < 1) {
            return [];
        }

        try {
            $list_customers = OSC::helper('account/customer')->getCustomerByListIds($customer_ids);

            $customers = [];

            foreach ($list_customers as $customer) {
                $customers[] = array(
                    'id' => $customer['customer_id'],
                    'title' => $customer['name'],
                    'email' => $customer['email'],
                    'phone' => $customer['phone']
                );
            }

            return $customers;
        } catch (Exception $ex) {
            return [];
        }
    }

    public function loadOrderSelectorData($order_ids) {
        if (!is_array($order_ids)) {
            $order_ids = [$order_ids];
        }

        $order_ids = array_map(function($order_id) {
            return intval($order_id);
        }, $order_ids);
        $order_ids = array_filter($order_ids, function($order_id) {
            return $order_id > 0;
        });

        if (count($order_ids) < 1) {
            return [];
        }

        try {
            /* @var $collection Model_Catalog_Order_Collection */
            /* @var $order Model_Catalog_Order */
            $collection = OSC::model('catalog/order')->getCollection()->load($order_ids);

            $orders = [];

            foreach ($collection as $order) {
                if ($order->data['shop_id'] != OSC::getShop()->getId()) {
                    continue;
                }
                $orders[] = array(
                    'id' => $order->getId(),
                    'title' => $order->data['code'],
                    'email' => $order->data['email'],
                    'shipping_full_name' => $order->data['shipping_full_name'],
                    'url' => $order->getDetailUrl()
                );
            }

            return $orders;
        } catch (Exception $ex) {
            return [];
        }
    }

    public function priceExchangeRate() {
        static $model = null;

        $currency_code = OSC::cookieGet('currency_code');

        if (!isset($currency_code)) {
            $location = OSC::helper('core/common')->getClientLocation();
            OSC::cookieSetCrossSite('location_currency_code_auto_detect', $location['currency_code']);
            //            $location = OSC::helper('core/common')->getIPLocation('85.214.132.117');
            if (!OSC::helper('core/setting')->get('catalog/convert_currency/auto_convert_by_location') || in_array($location['currency_code'], ['BTC', 'BTN', 'BYR', 'CLF', 'CUC', 'ETB', 'KMF', 'LTL', 'LVL', 'MGA', 'MRO', 'STD', 'XAG', 'XAU', 'XDR', 'XOF', 'ZMK', ''])) {
                OSC::cookieSetCrossSite('currency_code', 'USD');

                $currency_code = 'USD';
            } else {
                OSC::cookieSetCrossSite('currency_code', $location['currency_code']);

                $currency_code = $location['currency_code'];
            }
        }

        if ($this->checkBlockConvertPrice() == true) {
            $currency_code = 'USD';
        }

        try {
            if ($model === null) {
                $model = OSC::model('catalog/product_priceExchangeRate');
                $cache_key = __FUNCTION__ . '_currency_code_' . $currency_code;
                $cache = OSC::core('cache')->get($cache_key);

                if ($cache) {
                    $model->bind($cache);
                } else {
                    $model->loadByUKey($currency_code);
                    OSC::core('cache')->set($cache_key, $model->data, OSC_CACHE_TIME);
                }
            }

            if (!($model instanceof Model_Catalog_Product_PriceExchangeRate) || $model->getId() < 1) {
                throw new Exception('Unable to load rate');
            }


            if ($currency_code != OSC::cookieGet('currency_code')) {
                OSC::cookieSetCrossSite('currency_code', $currency_code);
            }

            return ['currency_code' => $model->data['currency_code'], 'rate' => $model->data['exchange_rate'], 'icon' => $model->data['symbol']];
        } catch (Exception $ex) {
            return ['currency_code' => 'USD', 'rate' => 1, 'icon' => '$'];
        }
    }

    public function renderSymbolCurrency() {
        return
            ['ZWL' => '$', 'ZMW' => 'ZK', 'XPF' => '₣', 'XAF' => '₣', 'WST' => 'T', 'VUV' => 'Vt', 'UGX' => 'Sh', 'TZS' => 'Sh', 'TOP' => 'T$', 'TND' => 'د.ت', 'TMT' => 'm', 'TJS' => 'ЅМ', 'SZL' => 'L', 'SLL' => 'Le', 'SDG' => '£',
                'RWF' => '₣', 'PGK' => 'K', 'MWK' => 'MK', 'MVR' => 'ރ.', 'MOP' => 'P', 'MMK' => 'K', 'MDL' => 'L', 'MAD' => 'د.م.', 'LSL' => 'L', 'KWD' => 'د.ك', 'KES' => 'Sh', 'JOD' => 'د.ا', 'IQD' => 'ع.د', 'HTG' => 'G', 'GNF' => '₣',
                'GMD' => 'D', 'ERN' => 'Nfk', 'DZD' => 'د.ج', 'DJF' => '₣', 'CVE' => '$', 'COP' => '$', 'CDF' => '₣', 'BIF' => '₣', 'AOA' => 'Kz', 'AED' => 'د.إ', 'ALL' => 'Lek', 'AFN' => "؋", 'ARS' => '$', 'AWG' => 'ƒ', 'AUD' => '$',
                'AZN' => '₼', 'BHD' => 'BD', 'BDT' => '৳', 'AMD' => '֏', 'BSD' => '$', 'BBD' => '$', 'BYN' => 'Br', 'BZD' => 'BZ$', 'BMD' => '$', 'BOB' => '$b', 'BAM' => 'KM', 'BWP' => 'P', 'BGN' => 'лв', 'BRL' => 'R$', 'BND' => '$',
                'KHR' => '៛', 'CAD' => '$', 'KYD' => '$', 'CLP' => '$', 'CNY' => '¥', 'CRC' => '₡', 'HRK' => 'kn', 'CUP' => '₱', 'CZK' => 'Kč', 'DKK' => 'kr', 'DOP' => 'RD$', 'XCD' => '$', 'EGP' => '£', 'SVC' => '$', 'EUR' => '€',
                'FKP' => '£', 'FJD' => '$', 'GHS' => '¢', 'GIP' => '£', 'GTQ' => 'Q', 'GGP' => '£', 'GYD' => '$', 'HNL' => 'L', 'HKD' => '$', 'HUF' => 'Ft', 'ISK' => 'kr', 'INR' => 'Rs', 'IDR' => 'Rp', 'IRR' => '﷼', 'IMP' => '£',
                'ILS' => '₪', 'JMD' => 'J$', 'JPY' => '¥', 'JEP' => '£', 'KZT' => 'лв', 'KPW' => '₩', 'KRW' => '₩', 'KGS' => 'лв', 'LAK' => '₭', 'LBP' => '£', 'LRD' => '$', 'MKD' => 'ден', 'MYR' => 'RM', 'MUR' => '₨', 'MXN' => '$',
                'MNT' => '₮', 'MZN' => 'MT', 'NAD' => '$', 'NPR' => '₨', 'ANG' => 'ƒ', 'NZD' => '$', 'NIO' => 'C$', 'NGN' => '₦', 'NOK' => 'kr', 'OMR' => '﷼', 'PKR' => '₨', 'PAB' => 'B/.', 'PYG' => 'Gs', 'PEN' => 'S/.', 'PHP' => '₱',
                'PLN' => 'zł', 'QAR' => '﷼', 'RON' => 'lei', 'RUB' => '₽', 'SHP' => '£', 'SAR' => '﷼', 'RSD' => 'Дин.', 'SCR' => '₨', 'SGD' => '$', 'SBD' => '$', 'SOS' => 'S', 'ZAR' => 'R', 'LKR' => '₨', 'SEK' => 'kr', 'CHF' => 'CHF',
                'SRD' => '$', 'SYP' => '£', 'TWD' => 'NT$', 'THB' => '฿', 'TTD' => 'TT$', 'TRY' => '₺', 'TVD' => '$', 'GEL' => '₾ ', 'LYD' => 'ل.د', 'UAH' => '₴', 'GBP' => '£', 'USD' => '$', 'UYU' => '$U', 'UZS' => 'лв', 'VEF' => 'Bs',
                'VND' => '₫', 'YER' => '﷼', 'ZWD' => 'Z$'];
    }

    public function checkBlockConvertPrice() {

        $list_country_block_convert_price = OSC::helper('core/setting')->get('list/block_countries_auto_convert_price');

        $location = OSC::helper('core/common')->getClientLocation();

        if ($list_country_block_convert_price[$location['country_code']] != null) {
            return true;
        }

        return false;
    }

    public function fetchProductTypes() {
        static $product_types = null;

        if ($product_types === null) {
            $DB = OSC::core('database');

            $DB->query('SELECT DISTINCT product_type FROM ' . OSC::model('catalog/product')->getTableName(true), null, 'fetch_product_type');

            $product_types = [];

            while ($row = $DB->fetchArray('fetch_product_type')) {
                $product_type = explode(',', $row['product_type']);

                foreach ($product_type as $entry) {
                    $entry = trim($entry);

                    if (strlen($entry) < 1) {
                        continue;
                    }

                    if (!isset($product_types[$entry])) {
                        $product_types[$entry] = 0;
                    }

                    $product_types[$entry]++;
                }
            }

            $DB->free('fetch_product_type');

            $product_types = array_keys($product_types);
        }

        return $product_types;
    }

    public function reloadFileFeedFlag() {
        $feed_is_running = OSC::core('database')->count('*', 'cron_queue', 'cron_name IN ("feed/seeding", "feed/render")', 'check_cron_existed');
        if ($feed_is_running) {
            return;
        }
        if (file_exists(OSC_VAR_PATH . '/catalog/feed/.feed_flag')) {
            unlink(OSC_VAR_PATH . '/catalog/feed/.feed_flag');
            unlink(OSC_VAR_PATH . '/catalog/feed/.feed_flag_notification');
        }
    }

    public function setSettingShipping($sync_data) {
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        if (isset($sync_data['setting'])){
            try {
                if (isset($sync_data['setting']['shipping_quantity'])) {
                    try {
                        $model = OSC::model('core/setting')->loadByUKey('shipping/shipping_by_quantity/table');
                        $model->setData('setting_value',$sync_data['setting']['shipping_quantity'])->save();
                    }catch (Exception $ex){
                        if ($ex->getCode() == 404){
                            OSC::model('core/setting')->setData([
                                'setting_key' => 'shipping/shipping_by_quantity/table',
                                'setting_value' => $sync_data['setting']['shipping_quantity'],
                                'added_timestamp' => time(),
                                'modified_timestamp' => time()
                            ])->save();

                        }else{
                            throw new Exception($ex->getMessage());
                        }
                    }
                }

                if (isset($sync_data['setting']['shipping_delivery_time'])) {
                    try {
                        $model = OSC::model('core/setting')->loadByUKey('shipping/delivery_time');
                        $model->setData('setting_value', $sync_data['setting']['shipping_delivery_time'])->save();
                    } catch (Exception $ex) {
                        if ($ex->getCode() == 404) {
                            OSC::model('core/setting')->setData([
                                'setting_key' => 'shipping/delivery_time',
                                'setting_value' => $sync_data['setting']['shipping_delivery_time'],
                                'added_timestamp' => time(),
                                'modified_timestamp' => time()
                            ])->save();

                        } else {
                            throw new Exception($ex->getMessage());
                        }
                    }
                }

                if (isset($sync_data['setting']['shipping_free_option'])) {
                    try {
                        $model = OSC::model('core/setting')->loadByUKey('shipping/table_rate/free_shipping/enable');
                        $model->setData('setting_value',$sync_data['setting']['shipping_free_option'])->save();
                    }catch (Exception $ex){
                        if ($ex->getCode() == 404){
                            OSC::model('core/setting')->setData([
                                'setting_key' => 'shipping/table_rate/free_shipping/enable',
                                'setting_value' => $sync_data['setting']['shipping_free_option'],
                                'added_timestamp' => time(),
                                'modified_timestamp' => time()
                            ])->save();
                        }else{
                            throw new Exception($ex->getMessage());
                        }
                    }
                }

                if (isset($sync_data['setting']['shipping_free'])) {
                    try {
                        $model = OSC::model('core/setting')->loadByUKey('shipping/table_rate/free_shipping');
                        $model->setData('setting_value',$sync_data['setting']['shipping_free'])->save();
                    }catch (Exception $ex){
                        if ($ex->getCode() == 404){
                            OSC::model('core/setting')->setData([
                                'setting_key' => 'shipping/table_rate/free_shipping',
                                'setting_value' => $sync_data['setting']['shipping_free'],
                                'added_timestamp' => time(),
                                'modified_timestamp' => time()
                            ])->save();

                        }else{
                            throw new Exception($ex->getMessage());
                        }
                    }
                }

                $DB->commit();

                OSC::helper('core/setting')->removeCache();

            }catch (Exception $ex){
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                throw new Exception($ex->getMessage());
            }

            OSC_Database_Model::unlockPreLoadedModel($locked_key);
        }
    }

    /**
     * Get list location in location group table from customer's IP
     * @param $country_code
     * @param $province_code
     * @param $flag_feed
     * @return array
     */
    public function getGroupLocationCustomer($country_code = '', $province_code = '', $flag_feed = false) {
        $result = [];

        if ($country_code === '' && $province_code === '') {
            $location = OSC::helper('core/common')->getClientLocation();

            if (!isset($location['country_code']) && !isset($location['region_code'])) {
                return [];
            }

            $country_code = $location['country_code'];
            $province_code = $location['region_code'];
        }
        $province_code = $province_code ?? '';

        $group_locations = OSC::helper('core/country')->getLocationGroup($country_code, $province_code, $flag_feed);

        if (!is_null($group_locations['country'])) {
            $result[] = $group_locations['country'];
        }

        if (!is_null($group_locations['province'])) {
            $result[] = $group_locations['province'];
        }

        foreach ($group_locations['group'] as $location_data) {
            $result[] = $location_data;
        }

        return $result;
    }

    /**
     * @param $country_code
     * @param $province_code
     * @param $product_type_id
     * @param $setting_datas
     * @return mixed|null
     */
    public function getShippingFeeConfigs($country_code, $province_code, $product_type_id, $setting_datas): ?array {
        $shipping_configs = null;
        $default_shipping_configs = null;
        $default_product_type_shipping_configs = null;

        foreach ($setting_datas as $setting_data) {
            $flag_location = OSC::helper('core/country')->checkCountryProvinceInLocation(
                $country_code,
                $province_code,
                $setting_data['location_data']
            );
            if ($flag_location && in_array($product_type_id, $setting_data['product_types'])) {
                $shipping_configs['_static'] = $setting_data['shipping_configs'];
                $shipping_configs['dynamic'] = $setting_data['shipping_configs_dynamic'] ?? 0;
                $shipping_configs['type'] = $setting_data['shipping_configs_type'] ?? 0;
                break;
            }

            if ($flag_location && in_array('*', $setting_data['product_types'])) {
                $shipping_configs['_static'] = $setting_data['shipping_configs'];
                $shipping_configs['dynamic'] = $setting_data['shipping_configs_dynamic'] ?? 0;
                $shipping_configs['type'] = $setting_data['shipping_configs_type'] ?? 0;
            }

            if ($setting_data['location_data'] === '*' && in_array('*', $setting_data['product_types'])) {
                $default_shipping_configs['_static'] = $setting_data['shipping_configs'];
                $default_shipping_configs['dynamic'] = $setting_data['shipping_configs_dynamic'] ?? 0;
                $default_shipping_configs['type'] = $setting_data['shipping_configs_type'] ?? 0;
            }

            if ($setting_data['location_data'] === '*' && in_array($product_type_id, $setting_data['product_types'])) {
                $default_product_type_shipping_configs['_static'] = $setting_data['shipping_configs'];
                $default_product_type_shipping_configs['dynamic'] = $setting_data['shipping_configs_dynamic'] ?? 0;
                $default_product_type_shipping_configs['type'] = $setting_data['shipping_configs_type'] ?? 0;
            }
        }

        if (is_null($shipping_configs)) {
            $shipping_configs = $default_product_type_shipping_configs ?? $default_shipping_configs;
        }
        krsort($shipping_configs);

        return $shipping_configs;
    }

    /**
     * @param $shipping_configs
     * @param $quantity
     * @return array|mixed
     * @throws Exception
     */
    public function getBuffShipping($shipping_configs, $quantity): array {
        $buff = [];

        if ($shipping_configs['rate_type'] == 0) {
            foreach ($shipping_configs['quantity_rate'] as $max_quantity => $_estimate) {
                if ($max_quantity > $quantity) {
                    if (count($buff) === 0) {
                        $buff['price'] = $_estimate;
                    }

                    break;
                }

                $buff['price'] = $_estimate;
            }
        } else {
            if ($quantity < 1) {
                throw new Exception('Quantity is more than 0!');
            }
            $buff['price'] = $shipping_configs['dynamic_rate']['base'] + (($quantity - 1) * $shipping_configs['dynamic_rate']['plus']);
        }

        return $buff;
    }

    /**
     * @param $country_code
     * @param $province_code
     * @param $pack_qty
     * @param $shipping_values
     * @return mixed|null
     */
    public function getShippingPackPrice($country_code, $province_code, $pack_qty, $shipping_values) {
        $shipping_pack_price = null;

        foreach ($shipping_values as $shipping_value) {
            $flag_location = OSC::helper('core/country')->checkCountryProvinceInLocation(
                $country_code,
                $province_code,
                $shipping_value['location_data']
            );

            if ($flag_location) {
                $shipping_pack_price = $pack_qty * $shipping_value['price'];
                break;
            }

            if ($shipping_value['location_data'] === '*') {
                $shipping_pack_price = $pack_qty * $shipping_value['price'];
            }
        }

        return $shipping_pack_price;
    }

    public function applyDiscountCode() {
        try {
            /* @var $cart Model_Catalog_Cart */
            $cart = OSC::helper('catalog/common')->getCart(true);

            if (count($cart->data['discount_codes']) > 0) {
                return;
            }

            $discount_code = OSC::model('catalog/discount_code')->loadByUKey($this->_request->get('_dcode'));
            $cart->setData('discount_codes', [$discount_code->data['discount_code']]);

            $cart->save();
        } catch (Exception $ex) {

        }
    }

    public function getPopularPriceExchangeRate()
    {
        $popular_currency_code = ['USD', 'EUR', 'GBP', 'AUD', 'CAD'];

        $location_currency_code_auto_detect = OSC::cookieGet('location_currency_code_auto_detect');
        if ($location_currency_code_auto_detect) {
            $popular_currency_code[] = $location_currency_code_auto_detect;
        }

        return OSC::model('catalog/product_priceExchangeRate')->getCollection()
            ->addField('id', 'currency_code', 'exchange_rate', 'symbol')
            ->addCondition('currency_code', $popular_currency_code, OSC_Database::OPERATOR_IN)
            ->sort('currency_code')
            ->load()
            ->toArray();
    }

    public function escapeString(string $str, int $length = null)
    {
        $str = trim(preg_replace('/[^a-zA-Z0-9-|_\'"()![\] ]/', '', $str));
        return $length === null ? $str : substr($str, 0, $length);
    }

    /**
     * Get customer shipping location
     * @return array
     * @throws OSC_Exception_Runtime
     */
    public function getCustomerShippingLocation() {
        $cart = OSC::helper('catalog/common')->getCart(false);

        $customer_country_code = OSC::helper('core/common')->getCustomerCountryCodeCookie();
        $customer_province_code = OSC::helper('core/common')->getCustomerProvinceCodeCookie();

        if (($cart instanceof Model_Catalog_Cart) && !empty($cart->data['shipping_country_code'])) {
            $country_code = $cart->data['shipping_country_code'];
            $list_province_codes = $this->_getListProvinceCodes();
            $province_code = isset($list_province_codes[$country_code][$cart->data['shipping_province_code']]) ?
                $cart->data['shipping_province_code'] :
                '';
        } elseif ($customer_country_code) {
            $country_code = $customer_country_code;
            $province_code = $customer_province_code ?: null;
        } else {
            $location = $this->getCustomerIPLocation();
            $country_code = $location['country_code'];
            $province_code = $location['province_code'];
        }

        return [
            'country_code' => $country_code,
            'province_code' => $province_code
        ];
    }

    protected $_customer_ip_location = null;
    /**
     * Get customer location by IP Address
     * @return array
     */
    public function getCustomerIPLocation() {
        if ($this->_customer_ip_location) {
            return $this->_customer_ip_location;
        }

        $country_code = '';
        $province_code = '';

        $location = OSC::helper('core/common')->getClientLocation();

        if (is_array($location) && isset($location['country_code']) && $location['country_code']) {
            $country_code = $location['country_code'];
        }

        if (is_array($location) && isset($location['region_code']) && $location['region_code']) {
            $list_province_codes = $this->_getListProvinceCodes();
            $province_code = isset($list_province_codes[$country_code][$location['region_code']]) ? $location['region_code'] : '';
        }

        $this->_customer_ip_location = [
            'country_code' => $country_code,
            'province_code' => $province_code
        ];

        return $this->_customer_ip_location;
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    public function getCustomerLocationCode() {
        $location = $this->getCustomerShippingLocation();

        return ',' . $location['country_code'] . '_' . $location['province_code'] . ',';
    }

    protected $_list_province_codes = null;

    /**
     * @return null
     * @throws OSC_Exception_Runtime
     */
    protected function _getListProvinceCodes() {
        if ($this->_list_province_codes) {
            return $this->_list_province_codes;
        }

        $cache_key = __FUNCTION__;
        $cache = OSC::core('cache')->get($cache_key);

        if ($cache !== false) {
            $this->_list_province_codes = $cache;
        } else {
            $collection = OSC::model('core/country_province')
                ->getCollection()
                ->addField('country_code', 'province_code')
                ->load();
            foreach ($collection as $item) {
                $this->_list_province_codes[$item->data['country_code']][$item->data['province_code']] = $item->data['province_code'];
            }

            OSC::core('cache')->set($cache_key, $this->_list_province_codes);
        }

        return $this->_list_province_codes;
    }

    protected $_list_personalized = null;

    public function getListPersonalizedDesign() {
        if ($this->_list_personalized == null) {
            $this->_list_personalized = OSC::model('personalizedDesign/design')->getCollection()->addField('design_id', 'title')->load()->toArray();
        }
        return $this->_list_personalized;
    }

    public function genCodeUkey(int $length = 12) {
        if ($length < 8) {
            return OSC::randKey($length,7);
        } elseif ($length >= 8 && $length <= 10) {
            $key = substr(time(), -$length);
        } else {
            $key = time() . OSC::randKey($length - 10,2);
        }
        $assign_data = [
            '0' => ['A', 'B', 'C', '0'],
            '1' => ['D', 'E', 'F', '1'],
            '2' => ['G', 'H', 'I', '2'],
            '3' => ['J', 'K', 'L', '3'],
            '4' => ['M', 'N', 'O', '4'],
            '5' => ['P', 'Q', 'R', '5'],
            '6' => ['S', 'T', 'U', '6'],
            '7' => ['V', 'W', '7'],
            '8' => ['X', 'Y', '8'],
            '9' => ['Z', '9']
        ];

        foreach ($assign_data as $key_replace => $value) {
            $r = array_rand($value,1);
            $key = str_replace($key_replace, $value[$r], $key);
        }

        return $key;
    }


    public function applyDiscountCodeParamsUrl($discount) {
        /* @var $cart Model_Catalog_Cart */
        try {
            OSC::helper('catalog/common')->validateDiscountCode($discount);

            $discount_code = OSC::model('catalog/discount_code')->loadByUKey($discount);

            $cart = OSC::helper('catalog/common')->getCart(false);

            if ($cart instanceof Model_Catalog_Cart && count($cart->data['discount_codes']) > 0) {
                $current_discount_code = OSC::model('catalog/discount_code')->getCollection()->loadByUkey($cart->data['discount_codes'])->first();

                if ($discount_code->data['discount_type'] != $current_discount_code->data['discount_type'] || $discount_code->data['discount_value'] < $current_discount_code->data['discount_value']) {
                    throw new Exception('New discount code need more than current discount code in cart');
                }

                OSC::helper('catalog/discountCode')->apply($discount, $cart);

                $discount_codes = [];
                foreach ($cart->getDiscountCodes() as $discount_data) {
                    $discount_codes[] = $discount_data['discount_code'];
                }

                if (count($discount_codes) > 0) {
                    $cart->setData('discount_codes', $discount_codes)->save();
                }
            } else {
                $cart = OSC::helper('catalog/common')->getCart(true);

                $cart->setData('discount_codes', [$discount_code->data['discount_code']])->save();

                $cart->save();
            }

        } catch (Exception $ex) {
        }
    }

    public function validateDiscountCode($code)
    {
        try {
            $code = trim($code);
            $code = preg_replace('/[^\w\s]+/', '', $code);
            $code = preg_replace('/[^a-zA-Z0-9]+/', '', $code);
            if (strlen($code) < 4 || strlen($code) > 20) {
                throw new Exception('Discount code length should be in range from 4 to 20 characters.');
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function updateCartAddress(Model_Catalog_Cart $cart, $type = 'shipping')
    {
        try {
            $customer_address = OSC::helper('account/address')->getList();
            if (is_array($customer_address) && count($customer_address) > 0) {
                $address_detail = OSC::helper('account/address')->getAddressDetail($cart->data[$type . '_address_id']);
                $address_detail = count($address_detail) ? $address_detail : OSC::helper('account/address')->getDefaultAddress();

                $data_update_cart_address = [
                    'customer_id' => $address_detail['customer_id'],
                    $type . '_address_id' => $address_detail['id'],
                    $type . '_full_name' => $address_detail['full_name'],
                    $type . '_phone' => $address_detail['phone'],
                    $type . '_address1' => $address_detail['address1'],
                    $type . '_address2' => $address_detail['address2'],
                    $type . '_city' => $address_detail['city'],
                    $type . '_province' => $address_detail['province'],
                    $type . '_province_code' => $address_detail['province_code'],
                    $type . '_country' => $address_detail['country'],
                    $type . '_country_code' => $address_detail['country_code'],
                    $type . '_zip' => $address_detail['zip'],
                ];
                if (!$cart->data['email']) {
                    $data_update_cart_address['email'] = OSC::helper('account/customer')->getCustomerEmail();
                }
                $cart->setData($data_update_cart_address)->save();
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getAbsoluteCacheKey($best_selling_start, $best_selling_end)
    {
        return "catalog_collection|absolute_range:{$best_selling_end}_{$best_selling_end}|";
    }

    public function getRelativeCacheKey($relative_range)
    {
        return "catalog_collection|relative_range:{$relative_range}|";
    }

    /**
     * @param int $day # can be negative
     * @return false|int
     */
    public function startOfDays(int $day = 1)
    {
        $_anytime = strtotime("{$day} days");
        return mktime(0, 0, 0, date('m', $_anytime), date('d', $_anytime), date('Y', $_anytime));
    }

    /**
     * @param int $day # can be negative
     * @return false|int
     */
    public function endOfDays(int $day = 1)
    {
        $_anytime = strtotime("{$day} days");
        return mktime(23, 59, 59, date('m', $_anytime), date('d', $_anytime), date('Y', $_anytime));
    }
}
