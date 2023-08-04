<?php

class Helper_Paypal_Common extends OSC_Object {

    /**
     *
     * @var Helper_Paypal_Payment
     */
    protected $_payment = null;

    /**
     * 
     * @return Helper_Paypal_Payment
     */
    public function getPayment($params) {
        if ($this->_payment === null) {
            $this->_payment = OSC::helper('paypal/payment')->setAccount(OSC::helper('multiPaymentAccount/common')->getAccount('paypal', $params['country_code']));
        }

        return $this->_payment;
    }

}
