<?php

class Helper_Catalog_Validation_Checkout extends OSC_Object {
    protected $_request_data = [];

    public function validateCheckoutForm(array $request_data, Model_Catalog_Cart $cart, &$updated_fields = []) {
        $this->_request_data = $request_data;

        try {
            $this->validateEmail();
            $this->validateShippingAddress();
            $this->validateBillingAddress();
            $this->validateShippingAddressCountry();
            $this->validateTip($cart, $updated_fields);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    public function validateEmail() {
        $email = $this->_request_data['email'] ?? null;

        if (empty($email)) {
            throw new Exception('Please enter a valid email address.');
        }

        if (OSC::helper('personalizedDesign/common')->hasEmoji($email)) {
            throw new Exception('Please do not include special characters in the Email field.');
        }

        OSC::core('validate')->validEmail($email);
    }

    public function validateShippingAddress() {
        $address_fields = Helper_Core_Country::ADDRESS_FIELDS;
        $shipping_address_fields = $this->_request_data['shipping_address'] ?? null;

        if (empty($shipping_address_fields) || !is_array($shipping_address_fields)) {
            throw new Exception('Please enter shipping address.');
        }

        foreach ($shipping_address_fields as $shipping_address_key => $shipping_address_value) {
            $shipping_address_value = OSC::helper('personalizedDesign/common')->escapeString($shipping_address_value);

            if (OSC::helper('personalizedDesign/common')->hasEmoji($shipping_address_value)) {
                if (in_array($shipping_address_key, array_keys($address_fields))) {
                    $holder = $address_fields[$shipping_address_key];
                } else {
                    $holder = ucwords(str_replace('_', ' ', $shipping_address_key));
                }

                throw new Exception("Please do not include special characters in the {$holder} field.");
            }
        }
    }

    public function validateBillingAddress() {
        $address_fields = Helper_Core_Country::ADDRESS_FIELDS;
        $billing_address_fields = $this->_request_data['billing_address'] ?? null;
        $billing_option_input = $this->_request_data['billing_option_input'] ?? null;

        if ($billing_option_input === 'another' && empty($billing_address_fields)) {
            throw new Exception('Please enter billing address.');
        }

        if (!empty($billing_address_fields) && is_array($billing_address_fields)) {
            foreach ($billing_address_fields as $billing_address_key => $billing_address_value) {
                $billing_address_value = OSC::helper('personalizedDesign/common')->escapeString($billing_address_value);

                if (OSC::helper('personalizedDesign/common')->hasEmoji($billing_address_value)) {
                    if (in_array($billing_address_key, array_keys($address_fields))) {
                        $holder = $address_fields[$billing_address_key];
                    } else {
                        $holder = ucwords(str_replace('_', ' ', $billing_address_key));
                    }

                    throw new Exception("Please do not include special characters in the {$holder} field.");
                }
            }
        }
    }

    public function validateShippingAddressCountry() {
        $shipping_address_fields = $this->_request_data['shipping_address'] ?? null;

        if (empty($shipping_address_fields)) {
            throw new Exception('Please enter shipping address.');
        }

        if (empty($shipping_address_fields['country'])) {
            throw new Exception('Please enter shipping address country.');
        }

        if (OSC::helper('core/country')->checkCountryDeactive($shipping_address_fields['country'])) {
            throw new Exception("We are sorry! {$shipping_address_fields['country']} is not supported, please select another country.");
        }
    }

    public function validateTip(Model_Catalog_Cart $cart, &$updated_fields = []) {
        $tip = round(floatval($this->_request_data['tip'] ?? 0));

        if (!$tip || $tip == $cart->getTipPrice()) {
            return;
        }

        $subtotal = OSC::helper('catalog/cart')->getSubtotalWithoutDiscount($cart);

        if ($tip > $subtotal) {
            throw new Exception(
                'Enter a tip no more than $' . OSC::helper('catalog/common')->integerToFloat($subtotal),
                400
            );
        }

        if (!OSC::helper('checkout/common')->checkAvailableTip($cart)) {
            throw new Exception('Cannot change tip', 400);
        }

        $custom_price_data = is_array($cart->getData('custom_price_data')) ? $cart->getData('custom_price_data') : [];

        if ($tip > 0) {
            $custom_price_data['tip'] = $tip;
        } else {
            unset($custom_price_data['tip']);
        }

        $updated_fields['custom_price_data'] = $custom_price_data;
    }
}
