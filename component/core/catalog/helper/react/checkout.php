<?php

class Helper_Catalog_React_Checkout {

    public function getDataPrice(Model_Catalog_Cart $cart, &$results) {
        $discount_code = $this->getDataDiscountCodes($cart);
        $shipping_data = $cart->getShippingPriceData();
        $without_discount_subtotal = OSC::helper('catalog/cart')->getSubtotalWithoutDiscountOfCart($cart);

        $price_datas = [
            'discount_code' => $discount_code,
            'subtotal_price' => $without_discount_subtotal - $cart->getItemsDiscountPrice(),
            'total_price' => $cart->getTotal(true, false, true, false),
            'tip_price' => $cart->getTipPrice(),
            'tax_price' => $cart->getTaxPrice(false),
            'buy_design_price' => $cart->getBuyDesignPrice(),
            'shipping_price' => $shipping_data['price'],
        ];

        $results = array_merge($price_datas, $results);
    }

    public function getDataItems(Model_Catalog_Cart $cart, &$results) {
        /* @var $line_item Model_Catalog_Cart_Item */
        try {
            $error_messages = $this->getNotifyMessage($cart);

            $line_items = $cart->getLineItems();

            if (isset($error_messages['design_in_product']) && !empty($error_messages['design_in_product'])) {
                $line_items = $cart->getSortItemErrorDesign();
            }

            foreach ($line_items as $line_item) {
                if ($line_item->isCrossSellMode()) {
                    $results['items'][] = OSC::helper('crossSell/common')->getDataCartItemCrossSell($line_item);
                    continue;
                }
                $variant = $line_item->getVariant();
                $product = $variant->getProduct();
                $cut_off_timestamp = OSC::helper('catalog/common')->getCutOffTimestamp($variant);
                $cut_off_title = trim(OSC::helper('core/setting')->get('shipping/cut_off_time/title'));
                $country_code_place_of_manufacture = OSC::helper('core/country')->getCountryCodePlaceOfManufacture();
                $is_disable_preview = Model_Catalog_Product::STATUS_PRODUCT_PREVIEW['DISABLE'];

                $options = [];
                foreach ($variant->getOptions() as $option) {
                    $options[$option['title']] = $option['value'];
                }

                $campaign_config = null;
                try {
                    $campaign_config = OSC::helper('catalog/react_common')->getCampaignConfig($line_item->getId(), 'cart');
                } catch (Exception $ex) {

                }

                $semitest_config = null;
                if ($campaign_config == null) {
                    try {
                        $semitest_config = OSC::helper('catalog/react_common')->getSemitestConfig($line_item->getId(), 'cart');
                    } catch (Exception $ex) {

                    }
                }

                $shipping_semitest = null;
                if (isset($product->data['meta_data']['shipping_price']) && count($product->data['meta_data']['shipping_price']) > 0) {
                    if (count($product->data['meta_data']['shipping_price']) > 0) {
                        foreach ($product->data['meta_data']['shipping_price'] as $shipping) {
                            if ($line_item->data['quantity'] >= $shipping['quantity']) {
                                $shipping_semitest = $shipping;
                            }
                        }
                    }
                }

                if (isset($product->data['meta_data']['is_disable_preview'])) {
                    $is_disable_preview = $product->data['meta_data']['is_disable_preview'];
                }

                $error_cart_item = null;

                if (!$line_item->isAvailableToOrder()) {
                    $error_cart_item = [
                        'title' => '',
                        'message' => 'Shipping to the current country is not supported for this item, please remove them from your cart to finish checkout.'
                    ];
                }

                if (!$line_item->checkDeignIdInProduct()) {
                    $error_cart_item = [
                        'title' => '',
                        'message' => 'The design of this item has been updated.'
                    ];
                }

                $discount = $line_item->getDiscount();

                $discount_data = null;

                if (!empty($discount)) {
                    $discount_data = [
                        'discount_code' => $discount['discount_code'],
                        'discount_price' => $discount['discount_price']
                    ];
                }

                $results['items'][] = [
                    'id' => $line_item->getId(),
                    'quantity' => $line_item->data['quantity'],
                    'product_title' => ($variant->getTitle() != '' ? ('('.$variant->getTitle().') ') : ''). $product->getProductTitle(),
                    'product_type_title' => $line_item->getCampaignData() !== null ? $line_item->getCampaignData()['product_type']['title'] : null,
                    'pack' => $line_item->getPackData(),
                    'price' => $line_item->getPrice(),
                    'subtotal_price' => $line_item->getPrice() + $line_item->getAddonServicePrice(false),
                    'options'=> $options,
                    'url' => $variant->getDetailUrl(),
                    'semitest_config' => $semitest_config,
                    'campaign_config' => $campaign_config,
                    'crosssell_config' => null,
                    'shipping_semitest' => $shipping_semitest,
                    'error_cart_item' => $error_cart_item,
                    'cut_off_time' => [
                        'title' => $cut_off_timestamp > 0 && $cut_off_title ? str_replace(['{{date}}', '{{tdate}}', '{{sdate}}', '{{tsdate}}', '{{date_time}}', '{{tdate_time}}', '{{sdate_time}}', '{{tsdate_time}}'], [date('d/m/Y', $cut_off_timestamp), date('M d, Y', $cut_off_timestamp), date('d/m', $cut_off_timestamp), date('M d', $cut_off_timestamp), date('H:i d/m/Y', $cut_off_timestamp), date('H:i M d, Y', $cut_off_timestamp), date('H:i d/m', $cut_off_timestamp), date('H:i M d', $cut_off_timestamp)], $cut_off_title) : '',
                        'message' => $cut_off_timestamp > 0 ? str_replace(['{{date}}', '{{tdate}}', '{{sdate}}', '{{tsdate}}', '{{date_time}}', '{{tdate_time}}', '{{sdate_time}}', '{{tsdate_time}}'], [date('d/m/Y', $cut_off_timestamp), date('M d, Y', $cut_off_timestamp), date('d/m', $cut_off_timestamp), date('M d', $cut_off_timestamp), date('H:i d/m/Y', $cut_off_timestamp), date('H:i M d, Y', $cut_off_timestamp), date('H:i d/m', $cut_off_timestamp), date('H:i M d', $cut_off_timestamp)], OSC::helper('core/setting')->get('shipping/cut_off_time/' . ($cut_off_timestamp <= time() ? 'message_after_time' : 'message_before_time'))) : '',
                        'is_reached' => $cut_off_timestamp < time()
                    ],
                    'personalized_in' => [
                        'country_code' => mb_strtolower($country_code_place_of_manufacture),
                        'country_name' => OSC::helper('core/country')->getCountryTitle($country_code_place_of_manufacture)
                    ],
                    'delivery_time' => OSC::helper('catalog/react_common')->getDeliveryTimeByCheckout($cart, $variant),
                    'is_available' => $line_item->isAvailableToOrder(),
                    'discount' => $discount_data,
                    'price_with_discount_code' => $line_item->getAmountWithDiscount(true),
                    'addon_services' => $line_item->getAddonServices(),
                    'is_disable_preview' => $is_disable_preview
                ];
            }
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('getDataItems error', $ex->getMessage(), $cart->getUkey());
        }
    }

    public function getDataApi($is_new_updated = false, $payment_data = []) {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);

            $this->verifyTipPrice($cart);

            $shipping_methods = $this->getDataShippingMethods($cart);

            $error_messages = $this->getNotifyMessage($cart);

            $list_methods = OSC::helper('catalog/checkout')->collectPaymentMethods($cart);

            $methods = [];
            foreach ($list_methods as $method) {
                $methods[$method->getKey()] = $method;
            }

            $paypal_account = isset($methods['paypal']) && $methods['paypal'] instanceof Helper_Paypal_Payment ? $methods['paypal']->getAccount() : [];

            $results = [
                'quantity' => $cart->getQuantity(),
                'error_messages' => $error_messages,
                'shipping_methods' => $shipping_methods,
                'payment_methods' => $this->getDataPaymentMethods($cart),
                'tip' => $this->getDataTip($cart),
                'address' => $this->getDataAddress($cart),
                'page_urls' => $this->getDataPageUrls(),
                'flag_updated' => $is_new_updated == true ? 1 : 0,
                'paypal' => [
                    'id' => $paypal_account['id'] ?? '',
                    'client_id' => $paypal_account['account_info']['client_id'] ?? ''
                ]
            ];

            $this->getDataPrice($cart, $results);

            $this->getDataItems($cart, $results);

            if ($is_new_updated && !empty($payment_data['stripe']['payment_account_id']) && !empty($payment_data['stripe']['payment_intent_id'])) {
                // update payment intent for cart
                $this->updatePaymentIntentOfCart(
                    $cart,
                    $results,
                    [
                        'payment_account_id' => $payment_data['stripe']['payment_account_id'],
                        'payment_intent_id' => $payment_data['stripe']['payment_intent_id']
                    ]
                );
            } else {
                // create payment intent for cart
                $this->createPaymentIntentOfCart($cart, $results);
            }

            if (empty($results)) {
                OSC::helper('core/common')->writeLog('getDataApi result null', $results, $cart->getUkey());
            }

            return $results;
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('getDataApi error', $ex->getMessage() . ' - code: ' . $ex->getCode(), $cart->getUkey());

            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public function getDataApiV2($is_new_updated = false, $payment_data = []) {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);

            $this->verifyTipPrice($cart);

            $shipping_methods = $this->getDataShippingMethods($cart);

            $error_messages = $this->getNotifyMessage($cart);

            $list_methods = OSC::helper('catalog/checkout')->collectPaymentMethods($cart);

            $methods = [];
            foreach ($list_methods as $method) {
                $methods[$method->getKey()] = $method;
            }

            $paypal_account = isset($methods['paypal']) && $methods['paypal'] instanceof Helper_Paypal_Payment ? $methods['paypal']->getAccount() : [];

            $results = [
                'quantity' => $cart->getQuantity(),
                'error_messages' => $error_messages,
                'shipping_methods' => $shipping_methods,
                'payment_methods' => $this->getDataPaymentMethods($cart),
                'tip' => $this->getDataTip($cart),
                'address' => $this->getDataAddress($cart),
                'page_urls' => $this->getDataPageUrls(),
                'flag_updated' => $is_new_updated == true ? 1 : 0,
                'paypal' => [
                    'id' => $paypal_account['id'] ?? '',
                    'client_id' => $paypal_account['account_info']['client_id'] ?? ''
                ]
            ];

            $this->getDataPrice($cart, $results);

            if ($is_new_updated && !empty($payment_data['stripe']['payment_account_id']) && !empty($payment_data['stripe']['payment_intent_id'])) {
                // update payment intent for cart
                $this->updatePaymentIntentOfCart(
                    $cart,
                    $results,
                    [
                        'payment_account_id' => $payment_data['stripe']['payment_account_id'],
                        'payment_intent_id' => $payment_data['stripe']['payment_intent_id']
                    ]
                );
            } else {
                // create payment intent for cart
                $this->createPaymentIntentOfCart($cart, $results);
            }

            if (empty($results)) {
                OSC::helper('core/common')->writeLog('getDataApiV2 result null', $results, $cart->getUkey());
            }

            return $results;
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('getDataApiV2 error', $ex->getMessage() . ' - code: ' . $ex->getCode(), $cart->getUkey());

            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public function getDataCartItems() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);

            $results = [];

            $this->getDataItems($cart, $results);

            if (empty($results)) {
                OSC::helper('core/common')->writeLog('getDataCartItems result null', $results, $cart->getUkey());
            }

            return $results;
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('getDataCartItems error', $ex->getMessage() . ' - code: ' . $ex->getCode(), $cart->getUkey());

            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public function getDataPaymentMethods(Model_Catalog_Cart $cart) {
        $results = [];
        try {
            $payment_methods = OSC::helper('catalog/checkout')->collectPaymentMethods($cart);

            $payment_methods = OSC::helper('catalog/checkout')->collectPaymentBuildForm($payment_methods);

            foreach ($payment_methods as $payment_method) {
                $results[] = $payment_method->getKey();
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return $results;
    }

    public function getNotifyMessage(Model_Catalog_Cart $cart) {
        $error_messages = [];
        try {
            if (!$cart->isAvailableToOrder()) {
                $error_messages['available_to_order'] = [
                    'title' => 'Error notification',
                    'message' => 'We are sorry, some item(s) in your cart are not available to ship to your provided address. Please remove them from the cart before going to check out.'
                ];
            }
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('getNotifyMessage error 1', $ex->getMessage(), $cart->getUkey());
        }

        try {
            if (!$cart->isDeignIdInProduct()) {
                $error_messages['design_in_product'] = [
                    'title' => 'Error notification',
                    'message' => "Whoops, one of our busy bees updated your product(s) to make it better, but that means the item in your cart is outdated. Kindly personalize the item again by hitting the ‘Edit Item’ button. Once you do, we’ll replace the outdated item in your cart with it."
                ];
            }
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('getNotifyMessage error 2', $ex->getMessage(), $cart->getUkey());
        }

        return $error_messages;
    }

    public function getMessageDiscount(Model_Catalog_Cart $cart) {
        $error_message = null;

        try {
            $show_discount_not_support_shipping = $cart->showDiscount();

            switch ($show_discount_not_support_shipping) {
                case 1:
                    $error_message = 'This promo code is not applicable to your chosen shipping method.';
                    break;
                case 2:
                    $error_message = 'This promo code is not applicable to express shipping.';
                    break;
            }
        } catch (Exception $ex) { }

        return $error_message;
    }

    public function getDataDiscountCodes(Model_Catalog_Cart $cart) {
        $discount_codes = [];

        try {
            try {
                $cart->calculateDiscount(true);

                $cart_discounts = $cart->getDiscountCodes();

                if (count($cart_discounts) < 1) {
                    throw new Exception('Not have discount code');
                }
                $error_message = $this->getMessageDiscount($cart);

                foreach ($cart_discounts as $discount_code) {
                    $discount_codes[] = [
                        'description' => $discount_code['description'],
                        'discount_code' => $discount_code['discount_code'],
                        'price' => $discount_code['discount_price'] + $discount_code['discount_shipping_price'],
                        'display_summary' => in_array($discount_code['apply_type'], ['entire_order', 'shipping', 'entire_order_include_shipping']) ? true : false,
                        'error_message' => $error_message
                    ];
                }
            } catch (Exception $ex) {
                if ($ex->getCode() == Model_Catalog_Discount_Code::DISCOUNT_CODE_ERROR) {
                    $cart_discounts = $cart->data['discount_codes'];
                    foreach ($cart_discounts as $discount_code) {
                        $discount_codes[] = [
                            'discount_code' => $discount_code,
                            'display_summary' => false,
                            'error_message' => $ex->getMessage()
                        ];
                    }
                }
            }


        } catch (Exception $ex) { }

        return $discount_codes;
    }

    public function getDataTip(Model_Catalog_Cart $cart) {
        $without_discount_subtotal = OSC::helper('catalog/cart')->getSubtotalWithoutDiscountOfCart($cart);

        $checkAvailableTip = OSC::helper('checkout/common')->checkAvailableTip($cart);

        $result = [
            'enable' => $checkAvailableTip,
            'title' => OSC::helper('core/setting')->get('tip/title'),
            'maximum' => $without_discount_subtotal,
            'description' => OSC::helper('core/setting')->get('tip/description'),
            'table' => []
        ];

        if ($checkAvailableTip) {
            try {
                $tables = OSC::decode(OSC::helper('core/setting')->get('tip/table'));

                $result['table'] = array_map(
                    function ($value) {
                        return (float)$value;
                    },
                    $tables
                );
            } catch (Exception $ex) {
            }
        }

        return $result;
    }

    protected $_list_shipping_methods = null;

    public function getDataShippingMethods(Model_Catalog_Cart $cart) {
        if ($this->_list_shipping_methods == null) {
            try {
                $results = [];

                $this->calculatorShippingMethod($cart);

                $rate_active = $cart->getCarrier()->getRate();

                $rates = OSC::helper('dls/catalog_checkout')->getShippingEstimate();

                foreach ($rates as $rate) {
                    $rate_to_array = $rate->toArray();
                    $results[$rate->getKey()] = [
                        'key' => $rate_to_array['key'],
                        'title' => $rate_to_array['title'],
                        'amount' => $rate_to_array['amount'],
                        'estimate_timestamp' => $rate_to_array['estimate_timestamp']
                    ];

                    if ($rate_active->getKey() == $rate->getKey()) {
                        $results[$rate->getKey()]['active'] = 1;
                    }
                }

                $this->_list_shipping_methods = array_values($results);
            } catch (Exception $ex) {
                OSC::helper('core/common')->writeLog('getDataShippingMethods error', $ex->getMessage(), $cart->getUkey());
            }

        }
        return $this->_list_shipping_methods;
    }

    public function getDataPageUrls() {
        $page_urls = [
            'term_of_service' => '#!',
            'privacy_policy' => '#!'
        ];

        $query_string = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
        try {
            $page_collection = OSC::model('page/page')
                ->getCollection()
                ->addCondition('page_key', ['term_of_service', 'privacy_policy'], OSC_Database::OPERATOR_FIND_IN_SET)
                ->setLimit(2)
                ->load();

            foreach ($page_collection as $page) {
                $page_urls[$page->data['page_key']] = $page->getDetailUrl() . $query_string;
            }
        } catch (Exception $ex) {

        }

        return $page_urls;
    }

    public function getDataContactInfo(Model_Catalog_Cart $cart) {
        try {
            $results = [
                'email' => $cart->data['email'],
                'shipping_address' => count($cart->getShippingAddress()) ? $cart->getShippingAddress() : null,
                'billing_address' => count($cart->getBillingAddress()) ? $cart->getBillingAddress() : null,
                'shipping_address_id' => $cart->data['shipping_address_id'],
                'billing_address_id' => $cart->data['billing_address_id']
            ];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return $results;
    }

    public function getDataAddress(Model_Catalog_Cart $cart) {
        $results = [];
        try {
            $results['email'] = $cart->data['email'];
            $results['shipping_phone'] = $cart->data['shipping_phone'];
            $results['shipping_address_id'] = $cart->data['shipping_address_id'];
            $results['billing_address_id'] = $cart->data['billing_address_id'];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
        return $results;
    }

    public function calculatorShippingMethod(Model_Catalog_Cart $cart, $reload = false, $shipping_method_key = null) {
        try {
            if ($cart->getCarrier() && $cart->getCarrier()->getRate() && $reload == false) {
                return $cart;
            }

            $rate_active = $rate_default = null;

            $rates = OSC::helper('dls/catalog_checkout')->getShippingEstimate();

            foreach ($rates as $rate) {
                if ($shipping_method_key && $rate->getKey() == $shipping_method_key) {
                    $rate_active = $rate;
                    break;
                }

                if ($rate->isRateDefault()) {
                    $rate_default = $rate;
                }
            }

            if (!$rate_active && $rate_default) {
                $rate_active = $rate_default;
            }

            if (!$rate_active) {
                throw new Exception('Not have shipping method active');
            }

            $cart->register('valid_contact_info', 1);

            $cart->setCarrier($rate_active instanceof Helper_Catalog_Shipping_Carrier_Rate ?
                $rate_active->getCarrier()->selectRateByInstance($rate_active) : null);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function calculatorTax(Model_Catalog_Cart $cart) {
        try {
            if (!$cart->data['shipping_country_code']) {
                throw new Exception('Not have country code to calculator tax');
            }

            $cart->setData('taxes', [
                'country_code' => $cart->data['shipping_country_code'],
                'province_code' => $cart->data['shipping_province_code']
            ])->save();

            $preload_tax_settings = $cart->preloadTaxSettings();
            $product_type_variant_map = $cart->preloadProductTypeIdsOfItem();

            foreach ($cart->getLineItems() as $item) {
                $item_product_type_id = $item->isSemiTest() ? 0 : $product_type_variant_map[$item->getProductTypeVariantId()];

                $tax_value = OSC::helper('core/common')->getTaxValueByLocation(
                    $item_product_type_id,
                    $cart->data['shipping_country_code'] ?? '',
                    $cart->data['shipping_province_code'] ?? '',
                    $preload_tax_settings
                );

                $item->setData(['tax_value' => $tax_value])->save();
            }
        } catch (Exception $ex) {}
    }

    public function verifyTipPrice(Model_Catalog_Cart $cart) {
        try {
            if (!$cart->getTipPrice() || OSC::helper('checkout/common')->checkAvailableTip($cart)) {
                throw new Exception('Cannot reset tip');
            }
            $custom_price_data = $cart->data['custom_price_data'];

            unset($custom_price_data['tip']);

            $cart->setData(['custom_price_data' => $custom_price_data])
                ->save();
        } catch (Exception $ex) {
        }
    }

    /**
     * @param Model_Catalog_Cart $cart
     * @param $results
     * @return void
     */
    public function createPaymentIntentOfCart(Model_Catalog_Cart $cart, &$results) {
        try {
            $payment_intent_data = OSC::helper('catalog/checkout')->createPaymentIntentOfCart($cart);

            $payment_intent = $payment_intent_data['payment_intent'];
            $payment_account_id = $payment_intent_data['payment_account_id'];
            $public_key = $payment_intent_data['public_key'];

            $payment_intent_data = [
                'payment_intent' => [
                    'client_secret' => !empty($payment_intent) ? $payment_intent->client_secret : null,
                    'public_key' => $public_key,
                    'payment_account_id' => $payment_account_id,
                    'payment_intent_id' => !empty($payment_intent) ? $payment_intent->id : null
                ]
            ];

            $results = array_merge($results, $payment_intent_data);
        } catch (Exception $exception) {
            OSC::helper('core/common')->writeLog('createPaymentIntentOfCart error', $exception->getMessage(), $cart->getUkey());
        }
    }

    public function updatePaymentIntentOfCart(Model_Catalog_Cart $cart, &$results, $paymentIntentData = []) {
        if (empty($paymentIntentData['payment_account_id']) || empty($paymentIntentData['payment_intent_id'])) {
            return;
        }

        $payment_intent_data = OSC::helper('catalog/checkout')->updatePaymentIntentOfCart($cart, $paymentIntentData);

        $payment_intent = $payment_intent_data['payment_intent'];
        $payment_account_id = $payment_intent_data['payment_account_id'];
        $public_key = $payment_intent_data['public_key'];

        $payment_intent_data = [
            'payment_intent' => [
                'client_secret' => !empty($payment_intent) ? $payment_intent->client_secret : null,
                'public_key' => $public_key,
                'payment_account_id' => $payment_account_id,
                'payment_intent_id' => !empty($payment_intent) ? $payment_intent->id : null
            ]
        ];

        $results = array_merge($results, $payment_intent_data);
    }
}
