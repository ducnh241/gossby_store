<?php

class Helper_CreditCard_Payment extends Abstract_Catalog_Payment
{
    public function __construct()
    {
        parent::__construct();
    }

    public function setAccount($account)
    {
        parent::setAccount($account);

        return $this;
    }

    public function getPriority()
    {
        return 10;
    }

    public function getKey()
    {
        return 'creditCard';
    }

    public function getTextTitle()
    {
        return 'Credit card';
    }

    public function getHtmlTitle()
    {
        return OSC::helper('frontend/template')->build('creditCard/title');
    }

    public function getPaymentForm()
    {
        return OSC::helper('frontend/template')->build('creditCard/form');
    }

    public function authorize(array $payment_info)
    {
    }

    public function charge(array $order_info)
    {
    }

    /**
     *
     * @param mixed $payment_data
     * @param float $amount
     * @param string $currency_code
     * @return mixed
     */
    public function void($payment_data, float $amount, string $currency_code, int $added_timestamp)
    {
    }

    /**
     *
     * @param mixed $payment_data
     * @param float $amount
     * @param string $currency_code
     * @return mixed
     */
    public function capture($payment_data, float $amount, string $currency_code)
    {
    }

    /**
     *
     * @param mixed $payment_data
     * @param float $amount
     * @param string $currency_code
     * @param string $reason
     * @return mixed
     */
    public function refund($payment_data, float $amount, string $currency_code, string $description, string $reason = '')
    {
    }


    public function update(array $order_info, array $payment_data)
    {
        // TODO: Implement update() method.
    }
}
 
