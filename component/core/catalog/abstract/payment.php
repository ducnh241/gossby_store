<?php

abstract class Abstract_Catalog_Payment extends OSC_Object {

    /**
     * @return string
     */
    abstract public function getKey();

    /**
     * @return string
     */
    abstract public function getTextTitle();

    /**
     * @return string
     */
    abstract public function getHtmlTitle();

    /**
     * @return string
     */
    abstract public function getPaymentForm();

    /**
     * 
     * @param float $amount
     * @param string $currency_code
     * @param string $description
     * @return mixed
     */
    abstract public function charge(array $order_info);

    /**
     * 
     * @param float $amount
     * @param string $currency_code
     * @param string $description
     * @return mixed
     */
    abstract public function authorize(array $order_info);

    /**
     *
     * @param array $order_info
     * @param array $payment_data
     * @return mixed
     */
    abstract public function update(array $order_info, array $payment_data);

    /**
     * 
     * @param mixed $payment_data
     * @param float $amount
     * @param string $currency_code
     * @return mixed
     */
    abstract public function void($payment_data, float $amount, string $currency_code, int $added_timestamp);

    /**
     * 
     * @param mixed $payment_data
     * @param float $amount
     * @param string $currency_code
     * @return mixed
     */
    abstract public function capture($payment_data, float $amount, string $currency_code);

    /**
     * 
     * @param mixed $payment_data
     * @param float $amount
     * @param string $currency_code
     * @param string $reason
     * @return mixed
     */
    abstract public function refund($payment_data, float $amount, string $currency_code, string $description, string $reason = '');

    protected $_account = null;

    public function setAccount($account) {
        $this->_account = $account;

        return $this;
    }

    public function getAccount() {
        return $this->_account;
    }

    public function getPriority() {
        return 0;
    }

    public function isInAuthorizeMode() {
        return true;
    }

    /**
     * 
     * @param Model_Catalog_Order $order
     * @return string
     */
    public function getTextTitleWithInfo(Model_Catalog_Order $order): string {
        return $this->getTextTitle();
    }

    public function compareTransaction($a, $b) {
        return true;
    }

}
