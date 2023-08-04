<?php

class Helper_ApplePay_Payment extends Abstract_Catalog_Payment
{
    public function __construct() {
        parent::__construct();
    }

    public function setAccount($account) {
        parent::setAccount($account);

        return $this;
    }

    public function getKey() {
        return 'applePay';
    }

    public function getTextTitle() {
        return 'Apple Pay';
    }

    public function getHtmlTitle() {
        return null;
    }

    public function getPaymentForm() {
        return null;
    }

    public function authorize(array $order_info) {}

    public function charge(array $order_info) {}

    public function void($payment_data, float $amount, string $currency_code, int $added_timestamp) {}

    public function capture($payment_data, float $amount, string $currency_code) {}

    public function refund($payment_data, float $amount, string $currency_code, string $description, string $reason = '') {}

    public function update(array $order_info, array $payment_data) {}
}
