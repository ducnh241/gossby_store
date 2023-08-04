<?php

class Observer_PaypalPro_Common {

    public static function collectMethods($params) {
        return OSC::helper('paypalPro/payment')->setAccount(OSC::helper('multiPaymentAccount/common')->getAccount('paypalPro', $params['country_code']));
    }

}
