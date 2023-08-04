<?php

class Controller_Catalog_Checkout extends Abstract_Frontend_Controller {

    /**
     *
     * @var Model_Catalog_Cart 
     */
    protected $_cart = null;
    protected $_checkout_url = '';

    public function __construct() {
        parent::__construct();

        $this->getTemplate()->setPageTitle($this->setting('theme/site_name') . ' | Checkout');
        $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
        $this->_checkout_url = OSC_FRONTEND_BASE_URL . '/' . $current_lang_key . '/checkout';
    }

    /**
     * 
     * @return \Model_Catalog_Cart
     * @throws Exception
     */
    protected function _getCart($check_calculate_discount = true): Model_Catalog_Cart {
        if ($this->_cart === null) {
            try {
                /* @var $cart Model_Catalog_Cart */
                $this->_cart = OSC::helper('catalog/common')->getCart(false, $check_calculate_discount);

                if (!$this->_cart) {
                    throw new Exception('Please add least a product to your cart');
                }

                if ($check_calculate_discount) {
                    $this->_cart->calculateDiscount();
                }
            } catch (Exception $ex) {
                OSC::helper('catalog/checkout')->insertFootprint('GET_CART', $ex);
                $this->error($ex->getMessage());
            }
        }

        return $this->_cart;
    }

    public function actionIndex() {
        static::redirect($this->_checkout_url);

        try {
            $this->_getCart()->verifyShippingLine();
            static::redirect($this->getUrl('*/*/paymentMethod'));
        } catch (Exception $ex) {
            if ($this->_getCart()->data['shipping_country_code']) {
                static::redirect($this->getUrl('*/*/shippingMethod'));
            }
        }

        static::redirect($this->getUrl('*/*/address'));
    }

    public function actionAddress() {
        static::redirect($this->_checkout_url);

        if ($this->_request->get('save')) {
            try {
                $data = [];

                $shipping_address = OSC::helper('checkout/common')->getRequestAddress($this->_request->getAll(), 'shipping_address');
                foreach ($shipping_address as $key => $value) {
                    if (in_array($key, array_keys(Helper_Core_Country::ADDRESS_FIELDS))) {
                        $data['shipping_' . $key] = $value;
                    }
                }

                if ($this->_request->get('diff_billing_address') == '1') {
                    foreach (OSC::helper('checkout/common')->getRequestAddress($this->_request->getAll(), 'billing_address') as $key => $value) {
                        if (in_array($key, array_keys(Helper_Core_Country::ADDRESS_FIELDS))) {
                            $data['billing_' . $key] = trim($value);
                        }
                    }
                } else {
                    foreach (Helper_Core_Country::ADDRESS_FIELDS as $field_key => $field_title) {
                        if ($field_key == 'email') {
                            continue;
                        }

                        $data['billing_' . $field_key] = '';
                    }
                }

                $this->_getCart()->setData($data)->save();

                static::redirect($this->getUrl('*/*/shippingMethod'));
            } catch (Exception $ex) {
                OSC::helper('catalog/checkout')->insertFootprint('SAVE_ADDRESS', $ex);
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $address_form = $this->getTemplate()->setPageKey('catalog/checkout/address')->build('catalog/checkout/address', ['cart' => $this->_getCart()]);

        $this->output($this->getTemplate()->build('catalog/checkout/main', ['content' => $address_form, 'cart' => $this->_getCart()]), 'catalog/checkout/layout');
    }

    public function actionShippingMethod() {
        static::redirect($this->_checkout_url);

        if (!$this->_getCart()->data['shipping_city']) {
            OSC::helper('catalog/checkout')->insertFootprint('SHIPPING_METHOD_VERIFY_ADDRESS');
            $this->addErrorMessage('Please enter shipping address');
            static::redirect($this->getUrl('*/*/address'));
        }

        try {
            $carriers = OSC::helper('catalog/checkout')->collectShippingCarriers($this->_getCart());
        } catch (Exception $ex) {
            OSC::helper('catalog/checkout')->insertFootprint('SHIPPING_METHOD', $ex);
        }

        if ($this->_request->get('save')) {
            $method_key = $this->_request->get('shipping_method', '');
            $method_key = explode('/', $method_key, 2);

            try {
                if (count($method_key) != 2) {
                    throw new Exception('Please select a shipping method');
                }

                foreach ($carriers as $carrier) {
                    if ($carrier->getKey() != $method_key[0]) {
                        continue;
                    }

                    foreach ($carrier->getRates() as $rate_index => $rate) {
                        if ($rate->getKey() == $method_key[1]) {
                            $this->_getCart()->setCarrier($carrier->selectRate($rate_index));
                            static::redirect($this->getUrl('*/*/paymentMethod'));
                        }
                    }
                }

                throw new Exception('Please select a shipping method');
            } catch (Exception $ex) {
                OSC::helper('catalog/checkout')->insertFootprint('SHIPPING_METHOD', $ex);
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $shipping_method_form = $this->getTemplate()->setPageKey('catalog/checkout/shippingMethod')->build('catalog/checkout/shipping_method', ['cart' => $this->_getCart(), 'carriers' => $carriers]);

        $this->output($this->getTemplate()->build('catalog/checkout/main', ['content' => $shipping_method_form, 'cart' => $this->_getCart()]), 'catalog/checkout/layout');
    }

    public function actionPaymentMethod() {
        static::redirect($this->_checkout_url);

        try {
            $this->_getCart()->verifyShippingLine();
        } catch (Exception $ex) {
            OSC::helper('catalog/checkout')->insertFootprint('PAYMENT_METHOD_VERIFY_SHIPPING_LINE', $ex);
            $this->addErrorMessage($ex->getMessage());
            static::redirect($this->getUrl('*/*/shippingMethod'));
        }

        $payment_methods = OSC::helper('catalog/checkout')->collectPaymentMethods($this->_getCart());

        $payment_method_form = $this->getTemplate()->setPageKey('catalog/checkout/paymentMethod')->build('catalog/checkout/payment_method', ['cart' => $this->_getCart(), 'payment_methods' => $payment_methods, 'selected_method' => $this->_request->get('payment_method')]);

        $this->output($this->getTemplate()->build('catalog/checkout/main', ['content' => $payment_method_form, 'cart' => $this->_getCart()]), 'catalog/checkout/layout');
    }

    public function actionPayment() {
        static::redirect($this->_checkout_url);

        try {
            $this->_getCart()->verifyShippingLine();
        } catch (Exception $ex) {
            OSC::helper('catalog/checkout')->insertFootprint('PAYMENT_VERIFY_SHIPPING_LINE', $ex);
            $this->addErrorMessage($ex->getMessage());
            static::redirect($this->getUrl('*/*/shippingMethod'));
        }

        try {
            $payment_method_key = $this->_request->get('payment_method');

            if (!$payment_method_key) {
                throw new Exception('Please select a payment method');
            }

            $payment_methods = OSC::helper('catalog/checkout')->collectPaymentMethods($this->_getCart());

            /* @var $payment_method Abstract_Catalog_Payment */

            foreach ($payment_methods as $payment_method) {
                if ($payment_method->getKey() == $payment_method_key) {
                    $order = $this->_getCart()->placeOrder($payment_method);

                    static::redirect($order->getDetailUrl(true));
                }
            }

            throw new Exception('Please select a payment method');
        } catch (Exception $ex) {
            OSC::helper('catalog/checkout')->insertFootprint('PAYMENT', $ex);
            $this->addErrorMessage($ex->getMessage());
            static::redirect($this->getUrl('*/*/paymentMethod', ['payment_method' => $this->_request->get('payment_method')]));
        }
    }

    public function actionDiscountCodeApply() {
        try {
            try {
                $code = $this->_request->get('code');
                $code = trim($code);
                if (strlen($code) < 4 || strlen($code) > 20) {
                    $this->_ajaxError('Discount code length should be in range from 4 to 20 characters.');
                }

                $cart = $this->_getCart(false);

                if (!$cart->getCarrier()) {
                    $rates = OSC::helper('dls/catalog_checkout')->getShippingEstimate();

                    $rate_default = null;

                    foreach ($rates as $_rate) {
                        if ($_rate->isRateDefault()) {
                            $rate_default = $_rate;
                            break;
                        }
                    }

                    $rate = $rate_default;

                } else {
                    $rate = $cart->getCarrier()->getRate();
                }

                $cart->setCarrier($rate->getCarrier()->selectRateByInstance($rate));

                OSC::helper('catalog/discountCode')->apply($this->_request->get('code'), $this->_getCart(false));
            } catch (Exception $ex) {
                $discount_codes = $this->_getCart(false)->getDiscountCodes();

                if ($ex->getCode() != 404 || count($discount_codes) > 0) {
                    throw new Exception($ex->getMessage(), $ex->getCode());
                }

                try {
                    $discount_code = OSC::model('catalog/discount_code');
                    $discount_code->setData([
                        'discount_code' => $this->_request->get('code'),
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

                OSC::helper('catalog/discountCode')->apply($discount_code, $this->_getCart(false));
            }

            $discount_codes = [];

            foreach ($this->_getCart(false)->getDiscountCodes() as $discount_data) {
                $discount_codes[] = $discount_data['discount_code'];
            }

            $cart = $this->_getCart(false);

            $cart->setData('discount_codes', $discount_codes)->save();
        } catch (Exception $ex) {
            OSC::helper('catalog/checkout')->insertFootprint('APPLY_DISCOUNT_CODE', $ex);
            $this->_ajaxError($ex->getCode() == 404 ? 'This promo code is invalid or has expired. Please check again or contact us for immediate assistance.' : $ex->getMessage());
        }

        $this->_ajaxResponse(['codes' => $discount_codes]);
    }

    public function actionDiscountCodeRemove() {
        try {
            $discount_code = Model_Catalog_Discount_Code::cleanCode($this->_request->get('code'));

            $discount_codes = $this->_getCart(false)->getDiscountCodes();

            if (count($discount_codes) > 0) {
                unset($discount_codes[$discount_code]);

                $discount_codes = array_keys($discount_codes);

                $cart = $this->_getCart(false);

                $cart->setData('discount_codes', $discount_codes)->save();
            }
        } catch (Exception $ex) {
            OSC::helper('catalog/checkout')->insertFootprint('REMOVE_DISCOUNT_CODE', $ex);
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['codes' => $discount_codes]);
    }

    public function actionGetSummary() {
        static::redirect($this->_checkout_url);

        $this->_ajaxResponse(['summary' => $this->getTemplate()->build('catalog/checkout/summary', ['cart' => $this->_getCart()])]);
    }

    public function actionGetSummaryButtonToggle() {
        static::redirect($this->_checkout_url);

        $this->_ajaxResponse(['button' => $this->getTemplate()->build('catalog/checkout/buttonToggle', ['cart' => $this->_getCart()])]);
    }

}
