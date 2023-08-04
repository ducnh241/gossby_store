<?php

class Helper_Catalog_Payment_Cod extends Abstract_Catalog_Payment {

    public function getKey() {
        return 'cod';
    }

    public function getTextTitle() {
        return 'Cash on Delivery';
    }

    public function getHtmlTitle() {
        return OSC::helper('frontend/template')->build('catalog/payment/cod/title');
    }

    public function getPaymentForm() {
        return OSC::helper('frontend/template')->build('catalog/payment/cod/form');
    }

    public function charge(array $order_info) {
        
    }

    public function authorize(array $order_info) {
        
    }

    public function capture($payment_data, float $amount, string $currency_code) {
        
    }

    public function refund($payment_data, float $amount, string $currency_code, string $description, string $reason = '') {
        
    }

    public function void($payment_data, float $amount, string $currency_code, int $added_timestamp) {
        
    }
}
