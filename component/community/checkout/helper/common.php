<?php

class Helper_Checkout_Common extends OSC_Object
{
    public function getRequestAddress($request_all, $key = 'shipping_address')
    {
        $address_id = isset($request_all[$key . '_id']) ? $request_all[$key . '_id'] : 0;
        if ($address_id > 0) {
            return OSC::helper('account/address')->getAddressDetail($address_id);
        } else {
            return isset($request_all[$key]) ? $request_all[$key] : null;
        }
    }

    public function getPriceFromTipPercent($percent, $subtotal)
    {
        return round(($subtotal * $percent) / 100, 2);
    }

    public function checkAvailableTip(Model_Catalog_Cart $cart, $country_code = '')
    {
        if (OSC::helper('core/setting')->get('tip/enable') != 1) {
            return false;
        }
        $tip_country = OSC::helper('core/setting')->get('tip/country');

        if ($tip_country === '*') {
            return true;
        }

        $country_codes = OSC::helper('core/country')->getCountryCodeByLocation([$tip_country]);
        if ($country_code != '') {
            if (in_array(strtoupper($country_code), $country_codes)) {
                return true;
            }
        } else {
            if (in_array(strtoupper($cart->data['shipping_country_code']), $country_codes)) {
                return true;
            }
        }
        return false;
    }
}