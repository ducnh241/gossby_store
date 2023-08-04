<?php

class Controller_Checkout_React_Index extends Abstract_Frontend_ReactApiController {

    public function actionThankyou() {
        try {
            try {
                $order = OSC::model('catalog/order')->loadByOrderUKey($this->_request->get('o') ? $this->_request->get('o') : OSC::cookieGet(OSC_SITE_KEY . '-order'));
            } catch (Exception $ex) {
                throw new Exception('Order is not found', $this::CODE_NOT_FOUND);
            }

            if (OSC::cookieGet('OSC_ORDER_PLACED') == $order->getOrderUkey()) {
                OSC::cookieRemoveCrossSite('OSC_ORDER_PLACED');
                OSC::register('data_tracking_facebook', $order);
                OSC::helper('report/common')->addRecordEvent('catalog/purchase', [
                    'order_id' => $order->getId(),
                    'total_price' => intval($order->data['total_price']),
                    'subtotal_price' => intval($order->data['subtotal_price']),
                    'quantity' => intval($order->getTotalQuantity()),
                ]);
            }

            $links = [['title' => 'Where can I track my order?', 'url' => $order->getDetailUrl()]];

            $page_keys = ['shipping', 'size_chart'];

            if (count($page_keys) > 0) {
                try {
                    $page_collection = OSC::model('page/page')->getCollection()
                        ->addCondition('page_key', $page_keys, OSC_Database::OPERATOR_FIND_IN_SET)
                        ->setLimit(count($page_keys))
                        ->sort('title', 'DESC')
                        ->load();

                    foreach ($page_collection as $page) {
                        $links[] = ['title' => $page->data['title'], 'url' => $page->getDetailUrl()];
                    }
                } catch (Exception $ex) {}
            }

            foreach ($order->getLineItems() as $line_item) {
                if ($line_item->data['product_id'] < 1) {
                    continue;
                }
                Helper_Catalog_Common::displayedProductRegister($line_item->data['product_id']);
            }

            $reference_transaction_products = $best_selling_products = $frequently_bought_together_products = [];

            if ($order->isUpsaleAvailable()) {
                try {
                    $shipping_location = $order->getShippingLocation();
                    $reference_transaction_products = OSC::model('catalog/product')
                        ->getCollection()
                        ->loadUpSale(8, $shipping_location['country_code'], $shipping_location['province_code']);
                } catch (Exception $ex) {}
            } else {
                $best_selling_products = OSC::helper('catalog/product')->getBestSelling(4);
                $frequently_bought_together_products = OSC::helper('catalog/product')->getFrequentltyBoughtTogetherByOrder($order, 8);
            }

            $this->sendSuccess([
                'is_upsale' => $order->isUpsaleAvailable(),
                'upsale_collection_description' => OSC::helper('core/setting')->get('payment/reference_transaction/upsale_collection_description'),
                'links' => $links,
                'reference_transaction_products' => $reference_transaction_products,
                'best_selling_products' => $best_selling_products,
                'recommend_products' => $frequently_bought_together_products,
                'enable_cross_sell' => OSC::helper('crossSell/common')->isEnableRecommend('thankyou') ? 1 : 0
            ]);
        } catch (Exception $exception) {
            $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function actionGetCheckoutDetail() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);

            if (!$cart instanceof Model_Catalog_Cart || $cart->getId() < 1) {
                throw new Exception('Not have cart');
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        $line_items = $cart->getLineItems();

        if ($line_items->length() < 1) {
            $this->sendError('Not have item cart to checkout', $this::CODE_LENGTH_REQUIRED);
        }

        OSC::helper('catalog/react_checkout')->calculatorTax($cart);

        try {
            if (OSC::cookieGet(OSC_SITE_KEY . '-checkout') != $cart->getId()) {
                OSC::cookieSetCrossSite(OSC_SITE_KEY . '-checkout', $cart->getId());

                $product_ids = [];

                foreach ($line_items as $line_item) {
                    $product_ids[] = $line_item->data['product_id'];
                }

                OSC::helper('report/common')->addRecordEvent('catalog/checkout_initialize', [
                    'product_ids' => array_unique($product_ids),
                    'cart_id' => $cart->getId()
                ]);
            }
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('tracking checkout error', $ex->getMessage(), $cart->getUkey());
        }

        try {
            $results = OSC::helper('catalog/react_checkout')->getDataApi();

            $this->sendSuccess($results);
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('actionGetCheckoutDetail error', $ex->getMessage() . ' - code: ' . $ex->getCode(), $cart->getUkey());
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetCheckoutDetailV2() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);

            if (!$cart instanceof Model_Catalog_Cart || $cart->getId() < 1) {
                throw new Exception('Not have cart');
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        $line_items = $cart->getLineItems();

        if ($line_items->length() < 1) {
            $this->sendError('Not have item cart to checkout', $this::CODE_LENGTH_REQUIRED);
        }

        OSC::helper('catalog/react_checkout')->calculatorTax($cart);

        try {
            if (OSC::cookieGet(OSC_SITE_KEY . '-checkout') != $cart->getId()) {
                OSC::cookieSetCrossSite(OSC_SITE_KEY . '-checkout', $cart->getId());

                $product_ids = [];

                foreach ($line_items as $line_item) {
                    $product_ids[] = $line_item->data['product_id'];
                }

                OSC::helper('report/common')->addRecordEvent('catalog/checkout_initialize', [
                    'product_ids' => array_unique($product_ids),
                    'cart_id' => $cart->getId()
                ]);
            }
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('tracking checkout error', $ex->getMessage(), $cart->getUkey());
        }

        try {
            $results = OSC::helper('catalog/react_checkout')->getDataApiV2();

            $this->sendSuccess($results);
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('actionGetCheckoutDetail error', $ex->getMessage() . ' - code: ' . $ex->getCode(), $cart->getUkey());
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetCartItems() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);

            if (!$cart instanceof Model_Catalog_Cart || $cart->getId() < 1) {
                throw new Exception('Not have cart');
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        try {
            $results = OSC::helper('catalog/react_checkout')->getDataCartItems();

            $this->sendSuccess($results);
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('actionGetCartItems error', $ex->getMessage() . ' - code: ' . $ex->getCode(), $cart->getUkey());
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetContactInfo() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);

            if (!$cart instanceof Model_Catalog_Cart || $cart->getId() < 1) {
                throw new Exception('Not have cart');
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        if ($cart->getLineItems()->length() < 1) {
            $this->sendError('Not have item cart to checkout', $this::CODE_LENGTH_REQUIRED);
        }

        try {
            $results = OSC::helper('catalog/react_checkout')->getDataContactInfo($cart);

            $this->sendSuccess($results);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionPostContactInfo() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);

            if (!$cart instanceof Model_Catalog_Cart || $cart->getId() < 1) {
                throw new Exception('Not have cart');
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        try {
            $data_update = [];

            $this->_updateContactInfo($cart, $data_update);

            // Detect change shipping country
            if (isset($data_update['shipping_country_code']) || isset($data_update['shipping_province_code'])) {
                $country_code = $data_update['shipping_country_code'] ?? $cart->data['shipping_country_code'];
                $province_code = $data_update['shipping_province_code'] ?? $cart->data['shipping_province_code'];

                $product_type_variant_map = OSC::helper('catalog/productType')->getProductTypeVariantMappingProductType();

                $data_update['taxes'] = [
                    'country_code' => $country_code,
                    'province_code' => $province_code
                ];

                foreach ($cart->getLineItems() as $item) {
                    $item_product_type_id = $item->isSemiTest() ? 0 : $product_type_variant_map[$item->getProductTypeVariantId()];

                    $tax_value = OSC::helper('core/common')->getTaxValueByLocation(
                        $item_product_type_id,
                        $country_code ?? '',
                        $province_code ?? ''
                    );
                    if ($tax_value != $item->data['tax_value']) {
                        $item->setData(['tax_value' => $tax_value])->save();
                    }
                }

                // remove cache account payment
                OSC::helper('multiPaymentAccount/common')->cleanAccountCache();
            }

            if (!empty($data_update)) {
                $cart->register('valid_contact_info', 1)
                    ->setData($data_update)
                    ->save();
                OSC::cookieSetCrossSite('customer_country_code', $cart->data['shipping_country_code']);
                OSC::cookieSetCrossSite('customer_province_code', $cart->data['shipping_province_code']);
            }
            $cart->reload();

            $shipping_method_key = null;
            if ($cart->getCarrier()) {
                $shipping_method_key = $cart->getCarrier()->getRate()->getKey();
            }
            OSC::helper('catalog/react_checkout')->calculatorShippingMethod($cart, true, $shipping_method_key);
            $results = OSC::helper('catalog/react_checkout')->getDataApi(
                true,
                [
                    'stripe' => [
                        'payment_account_id' => $this->_request->get('payment_account_id'),
                        'payment_intent_id' => $this->_request->get('payment_intent_id'),
                    ]
                ]
            );

            $this->sendSuccess($results);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }
    }

    public function actionPostContactInfoV2() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);

            if (!$cart instanceof Model_Catalog_Cart || $cart->getId() < 1) {
                throw new Exception('Not have cart');
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        try {
            $data_update = [];

            $this->_updateContactInfo($cart, $data_update);

            // Detect change shipping country
            if (isset($data_update['shipping_country_code']) || isset($data_update['shipping_province_code'])) {
                $country_code = $data_update['shipping_country_code'] ?? $cart->data['shipping_country_code'];
                $province_code = $data_update['shipping_province_code'] ?? $cart->data['shipping_province_code'];

                $product_type_variant_map = OSC::helper('catalog/productType')->getProductTypeVariantMappingProductType();

                $data_update['taxes'] = [
                    'country_code' => $country_code,
                    'province_code' => $province_code
                ];

                foreach ($cart->getLineItems() as $item) {
                    $item_product_type_id = $item->isSemiTest() ? 0 : $product_type_variant_map[$item->getProductTypeVariantId()];

                    $tax_value = OSC::helper('core/common')->getTaxValueByLocation(
                        $item_product_type_id,
                        $country_code ?? '',
                        $province_code ?? ''
                    );
                    if ($tax_value != $item->data['tax_value']) {
                        $item->setData(['tax_value' => $tax_value])->save();
                    }
                }

                // remove cache account payment
                OSC::helper('multiPaymentAccount/common')->cleanAccountCache();
            }

            if (!empty($data_update)) {
                $cart->register('valid_contact_info', 1)
                    ->setData($data_update)
                    ->save();
                OSC::cookieSetCrossSite('customer_country_code', $cart->data['shipping_country_code']);
                OSC::cookieSetCrossSite('customer_province_code', $cart->data['shipping_province_code']);
            }

            $cart->reload();

            $shipping_method_key = null;

            if ($cart->getCarrier()) {
                $shipping_method_key = $cart->getCarrier()->getRate()->getKey();
            }

            OSC::helper('catalog/react_checkout')->calculatorShippingMethod($cart, true, $shipping_method_key);
            $results = OSC::helper('catalog/react_checkout')->getDataApiV2(
                true,
                [
                    'stripe' => [
                        'payment_account_id' => $this->_request->get('payment_account_id'),
                        'payment_intent_id' => $this->_request->get('payment_intent_id'),
                    ]
                ]
            );

            $this->sendSuccess($results);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }
    }

    protected function _updateTip(Model_Catalog_Cart $cart, &$data_update , $tip) {
        $cart = OSC::helper('catalog/common')->getCart();
        try {
            $subtotal = OSC::helper('catalog/cart')->getSubtotalWithoutDiscountOfCart($cart);

            if ($tip > $subtotal) {
                throw new Exception(
                    'Enter a tip no more than $' . OSC::helper('catalog/common')->integerToFloat($subtotal),
                    $this::CODE_BAD_REQUEST
                );
            }

            if (!OSC::helper('checkout/common')->checkAvailableTip($cart)) {
                throw new Exception('Cannot change tip', $this::CODE_BAD_REQUEST);
            }

            $custom_price_data = is_array($cart->data['custom_price_data']) ? $cart->data['custom_price_data'] : [];
            if ($tip > 0) {
                $custom_price_data['tip'] = $tip;
            } else {
                unset($custom_price_data['tip']);
            }

            $data_update['custom_price_data'] = $custom_price_data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionPostTip() {
        $tip = round(floatval($this->_request->get('tip')));

        if ($tip < 0) {
            $this->sendError('Data is incorrect', $this::CODE_NOT_FOUND);
        }

        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);

            if (!$cart instanceof Model_Catalog_Cart || $cart->getId() < 1) {
                throw new Exception('Not have cart');
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        try {
            $data_update = [];

            $this->_updateTip($cart, $data_update, $tip);

            if (!empty($data_update)) {
                $cart->register('valid_contact_info', 1)
                    ->setData($data_update)
                    ->save();
            }

            OSC::helper('catalog/react_checkout')->calculatorShippingMethod($cart);

            $results = [
                'total_price' => $cart->getTotal(true, false, true, false),
                'tip_price' => $cart->getTipPrice(),
            ];
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }

        $this->sendSuccess(isset($results) ? $results : []);
    }

    public function actionPostShippingMethod() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);

            if (!$cart instanceof Model_Catalog_Cart || $cart->getId() < 1) {
                throw new Exception('Not have cart');
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        try {
            $shipping_method_key  = trim($this->_request->get('shipping_method'));

            if (!$shipping_method_key) {
                throw new Exception('Not have shipping method', $this::CODE_BAD_REQUEST);
            }

            if (!$cart->data['shipping_country_code']) {
                throw new Exception('Data cannot change', $this::CODE_BAD_REQUEST);
            }

            OSC::helper('catalog/react_checkout')->calculatorShippingMethod($cart, true, $shipping_method_key);

            $results = [];

            $cart = OSC::helper('catalog/common')->getCart(false, false);

            OSC::helper('catalog/react_checkout')->getDataPrice($cart, $results);

            $flag_method_exist = $shipping_method_key == $cart->getCarrier()->getRate()->getKey();

            if (!$flag_method_exist) {
                $results['shipping_methods'] = OSC::helper('catalog/react_checkout')->getDataShippingMethods($cart);
            }

            $this->sendSuccess($results);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_BAD_REQUEST);
        }
    }

    public function actionPostApplyDiscountCode() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);

            if (!$cart instanceof Model_Catalog_Cart || $cart->getId() < 1) {
                throw new Exception('Not have cart');
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        try {
            $code = trim($this->_request->get('code'));

            OSC::helper('catalog/common')->validateDiscountCode($code);

            $discount_item_price_current = $cart->getItemsDiscountPrice();

            OSC::helper('catalog/react_checkout')->calculatorShippingMethod($cart);

            try {
                OSC::helper('catalog/discountCode')->apply($code, $cart);
            } catch (Exception $ex) {
                $discount_codes = $cart->getDiscountCodes();

                if ($ex->getCode() != 404 || count($discount_codes) > 0) {
                    throw new Exception($ex->getMessage(), $ex->getCode());
                }

                try {
                    $discount_code = OSC::model('catalog/discount_code');
                    $discount_code->setData([
                        'discount_code' => $code,
                        'discount_type' => 'percent',
                        'discount_value' => 5,
                        'auto_generated' => 1,
                        'deactive_timestamp' => time() + (60 * 60 * 24),
                        'note'  => 'Apply Checkout'
                    ])->save();
                } catch (Exception $ex) {
                    OSC::helper('catalog/checkout')->insertFootprint('AUTO_GENERATED_DISCOUNT_CODE', $ex);
                    throw new Exception('This promo code is invalid or has expired. Please check again or contact us for immediate assistance.');
                }

                OSC::helper('catalog/discountCode')->apply($discount_code, $cart);
            }

            $discount_codes = [];

            foreach ($cart->getDiscountCodes() as $discount_data) {
                $discount_codes[] = $discount_data['discount_code'];
            }

            $cart->register('valid_contact_info', 1);

            $cart->setData('discount_codes', $discount_codes)->save();

            $cart->calculateDiscount();

            $results = [];

            if ($discount_item_price_current != $cart->getItemsDiscountPrice()) {
                OSC::helper('catalog/react_checkout')->getDataItems($cart, $results);
            }

            OSC::helper('catalog/react_checkout')->getDataPrice($cart, $results);

            $this->sendSuccess($results);
        } catch (Exception $ex) {
            OSC::helper('catalog/checkout')->insertFootprint('APPLY_DISCOUNT_CODE', $ex);
            $this->sendError($ex->getCode() == 404 ? 'This promo code is invalid or has expired. Please check again or contact us for immediate assistance.' : $ex->getMessage(), $ex->getCode() == 404 ? $this::CODE_APPLY_DISCOUNT_CODE_BUT_NOT_FOUND : $ex->getCode());
        }
    }

    public function actionPostRemoveDiscountCode() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);

            if (!$cart instanceof Model_Catalog_Cart || $cart->getId() < 1) {
                throw new Exception('Not have cart');
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }
        try {
            $code = Model_Catalog_Discount_Code::cleanCode(trim($this->_request->get('code')));

            $discount_item_price_current = $cart->getItemsDiscountPrice();

            $discount_codes = $cart->getDiscountCodes();

            unset($discount_codes[$code]);

            $cart->setData('discount_codes', array_keys($discount_codes))
                ->save();

            $cart->calculateDiscount();

            OSC::helper('catalog/react_checkout')->calculatorShippingMethod($cart);

            $results = [];

            OSC::helper('catalog/react_checkout')->getDataPrice($cart, $results);

            if ($discount_item_price_current != $cart->getItemsDiscountPrice()) {
                OSC::helper('catalog/react_checkout')->getDataItems($cart, $results);
            }

            $this->sendSuccess($results);
        } catch (Exception $ex) {
            OSC::helper('catalog/checkout')->insertFootprint('REMOVE_DISCOUNT_CODE', $ex);
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    protected function _updateContactInfo(Model_Catalog_Cart $cart, &$data_update) {
        $cart = OSC::helper('catalog/common')->getCart();
        try {
            $email = trim($this->_request->get('email'));
            if ($email) {
                try {
                    OSC::core('validate')->validEmail($email);
                    if ($cart->data['email'] != $email) {
                        $data_update['email'] = $email;
                    }
                } catch (Exception $ex) {}
            }

            $address_fields = Helper_Core_Country::ADDRESS_FIELDS;

            $shipping_address = $this->_request->get('shipping_address');
            if ($shipping_address) {
                foreach ($address_fields as $key => $name) {
                    if (!isset($shipping_address[$key]) || $shipping_address[$key] == $cart->data['shipping_' . $key]) {
                        continue;
                    }

                    $data_update['shipping_' . $key] = $shipping_address[$key];
                    if ($flag_load_data == false && in_array($key, ['country', 'country_code', 'province', 'province_code'])) {
                        $flag_load_data = true;
                    }
                }
            }
            $shipping_address_id = intval($this->_request->get('shipping_address_id', 0));

            if ($shipping_address_id > 0 && $shipping_address_id != $cart->data['shipping_address_id']) {
                $data_update['shipping_address_id'] = $shipping_address_id;
            }

            $billing_option_input = trim($this->_request->get('billing_option_input'));

            if ($billing_option_input && $billing_option_input == 'same') {
                foreach ($address_fields as $key => $name) {
                    $data = $cart->data;
                    if (!isset($data['shipping_' .$key]) || $data['shipping_' .$key] == $cart->data['billing_' .$key]) {
                        continue;
                    }
                    $data_update['billing_' . $key] = isset($data_update['shipping_' . $key]) ? $data_update['shipping_' . $key] : $cart->data['shipping_' .$key];
                }
            } else {
                $billing_address = $this->_request->get('billing_address');
                if ($billing_address) {
                    foreach ($address_fields as $key => $name) {
                        if (!isset($billing_address[$key]) || $billing_address[$key] == $cart->data['billing_' . $key]) {
                            continue;
                        }
                        $data_update['billing_' . $key] = $billing_address[$key];
                    }
                }
            }
            $billing_address_id = intval($this->_request->get('billing_address_id', 0));

            if ($billing_address_id > 0 && $billing_address_id != $cart->data['billing_address_id']) {
                $data_update['billing_address_id'] = $billing_address_id;
            }
        } catch (Exception $ex) { }
    }

    public function actionPostPlace() {
        $payment_intent = $this->_request->get('paymentIntent');
        $payment_intent_id = $payment_intent['id'] ?? null;
        $payment_account_id = $this->_request->get('paymentAccountId');

        try {
            $tracking_key = Abstract_Frontend_Controller::getTrackingKey();

            OSC::helper('catalog/checkout')->validateFormAndUpdateCart($this->_request->getAll());

            $cart = OSC::helper('catalog/common')->getCart(false, false);
            $cart_ukey = !empty($cart) ? $cart->getUkey() : null;

            $payment_method_key = trim($this->_request->get('payment_method'));

            if (!$payment_method_key) {
                throw new Exception('Please select a payment method');
            }

            $extra_data = [
                //'remember_account' => $payment_method_key === 'paypal' ? 1 : 0
                'remember_account' => 0 //Temporary remove reference transaction
            ];

            if ($payment_method_key == 'creditCard') {
                if ($payment_intent) {
                    $payment_method_key = 'stripe';
                } else {
                    $cc = $this->_request->get('card');
                    $payment_method_key = OSC::helper('catalog/checkout')
                        ->setCreditCardPaymentMethod($cart, str_replace(' ', '', $cc['number']));
                }
            } elseif ($payment_method_key === 'applePay' && $payment_intent) {
                $payment_method_key = 'stripe';
                $extra_data['payment_method'] = 'applePay';
            }

            if (!empty($payment_intent_id)) {
                $extra_data['payment_intent_id'] = $payment_intent_id;
            }

            if ($payment_method_key === 'paypal' && !empty($this->_request->get('funding_source'))) {
                $extra_data['funding_source'] = $this->_request->get('funding_source');
            }

            /* @var $payment_method Abstract_Catalog_Payment */

            if ($payment_account_id) {
                // get $payment_method by $payment_account_id
                $payment_account = OSC::model('multiPaymentAccount/account')->load($payment_account_id);
                $payment_method = OSC::helper('stripe/payment')
                    ->setAccount(OSC::helper('multiPaymentAccount/account')->getAccountData($payment_account));
            } else {
                // get $payment_method by $payment_method_key
                $list_methods = OSC::helper('catalog/checkout')->collectPaymentMethods($cart);

                $methods = [];
                foreach ($list_methods as $method) {
                    $methods[$method->getKey()] = $method;
                }

                if (!isset($methods[$payment_method_key])) {
                    throw new Exception('Please select a payment method');
                }

                /* @var $payment_method Abstract_Catalog_Payment */
                $payment_method = $methods[$payment_method_key];
            }

            $order = $cart->placeOrder($payment_method, $extra_data);

            $ORDER_ADDR = $order->getShippingAddress();
            $ORDER_ADDR['email'] = $order->data['email'];
            $ORDER_ADDR = base64_encode(OSC::encode($ORDER_ADDR));

            if ($ORDER_ADDR != OSC::cookieGet(OSC_SITE_KEY . '-LOA')) {
                OSC::cookieSetCrossSite(OSC_SITE_KEY . '-LOA', $ORDER_ADDR);
            }

            OSC::cookieSetCrossSite('OSC_ORDER_PLACED', $order->getOrderUkey()); // For tracking event in thankyou page
            OSC::cookieSetCrossSite('order-placed', $order->getOrderUkey());
            OSC::cookieSetCrossSite(OSC_SITE_KEY . '-order', $order->getOrderUkey());
            OSC::cookieSetCrossSite('cart-quantity', 0);

            if (OSC::registry(Helper_Catalog_Payment::$_payment_order_id)) {
                $this->sendSuccess([
                    'order_id' => OSC::registry(Helper_Catalog_Payment::$_payment_order_id)
                ]);
            }

            $this->sendSuccess([
                'order_ukey' => $order->getOrderUkey(),
                'order_id' => $order->getId()
            ]);
        } catch (Exception $ex) {
            $place_order_log = [
                'tracking_key' => $tracking_key ?? null,
                'cart_ukey' => $cart_ukey ?? null,
                'flag' => 4,
                'payment_type' => $payment_method_key ?? null,
                'exception_message' => $ex->getMessage() ?? null,
                'added_timestamp' => time()
            ];

            if (!empty($payment_method_key) && $payment_method_key === 'stripe') {
                $place_order_log['payment_intent_id'] = $payment_intent_id;
                $place_order_log['action'] = 'cancel_payment_intent';
            } else {
                $place_order_log['action'] = 'place_order_exception';
            }

            // set cancel payment intent log
            OSC::core('mongodb')->insert('place_order_log', $place_order_log, 'product');

            // cancel payment intent
            OSC::helper('stripe/common')->cancelPaymentIntentByAccount($payment_account_id, $payment_intent_id);

            $log_key = OSC::helper('catalog/checkout')->insertFootprint('PAYMENT', $ex);
            $this->sendError(strpos($ex->getMessage(), 'SQLSTATE') !== false ? "We got an error [CO-{$log_key}] while process your request, please try again." : $ex->getMessage(), $this::CODE_NOT_FOUND);
        }
    }

    public function actionGetStripeClientSecret() {
        try {
            $payment_account_id = $this->_request->get('payment_account_id');
            $payment_intent_id = $this->_request->get('payment_intent_id');

            $cart = OSC::helper('catalog/common')->getCart(false, false);

            if (!$cart instanceof Model_Catalog_Cart || $cart->getId() < 1) {
                throw new Exception('Not have cart');
            }

            OSC::helper('catalog/react_checkout')->calculatorShippingMethod($cart);

            if ($payment_account_id && $payment_intent_id) {
                // update payment intent
                $payment_intent_data = OSC::helper('catalog/checkout')->updatePaymentIntentOfCart(
                    $cart,
                    [
                        'payment_account_id' => $payment_account_id,
                        'payment_intent_id' => $payment_intent_id
                    ]
                );
            } else {
                // create payment intent
                $payment_intent_data = OSC::helper('catalog/checkout')->createPaymentIntentOfCart($cart);
            }

            $payment_intent = $payment_intent_data['payment_intent'];
            $payment_account_id = $payment_intent_data['payment_account_id'];
            $public_key = $payment_intent_data['public_key'];

            $this->sendSuccess([
                'client_secret' => $payment_intent->client_secret ?? null,
                'public_key' => $public_key,
                'payment_account_id' => $payment_account_id,
                'payment_intent_id' => $payment_intent->id ?? null
            ]);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }
    }

    public function actionCancelPaymentIntent() {
        $payment_account_id = $this->_request->get('payment_account_id');
        $payment_intent_id = $this->_request->get('payment_intent_id');
        $do_check_order = $this->_request->get('do_check_order');

        if (!$payment_account_id || !$payment_intent_id) {
            $this->sendError('Payment account id or payment intent id is required');
        }

        try {
            if ($do_check_order) {
                try {
                    $cart = OSC::helper('catalog/common')->getCart(false, false);
                } catch (Exception $exception) {}

                if (!empty($cart)) {
                    try {
                        $order = OSC::model('catalog/order');
                        $order->loadByOrderUKey($cart->getUkey());
                    } catch (Exception $exception) {
                        // cancel current payment intent
                        OSC::helper('stripe/common')->cancelPaymentIntentByAccount($payment_account_id, $payment_intent_id);
                    }
                }
            } else {
                // cancel current payment intent
                OSC::helper('stripe/common')->cancelPaymentIntentByAccount($payment_account_id, $payment_intent_id);
            }

            $this->sendSuccess([
                'success' => true
            ]);
        } catch (Exception $exception) {
            $this->sendError($exception->getMessage());
        }
    }
}
