<?php

class Helper_Catalog_Shipping_Carrier_Rate extends OSC_Object {

    /**
     *
     * @var Helper_Catalog_Shipping_Carrier 
     */
    protected $_carrier = null;
    protected $_key = null;
    protected $_title = null;
    protected $_amount = null;
    protected $_amount_tax = null;
    protected $_amount_semitest = null;
    protected $_items_shipping_info = [];
    protected $_estimate_timestamp = null;
    protected $_processing_timestamp = null;
    protected $_is_default = 0;

    public function __construct(
        Helper_Catalog_Shipping_Carrier $carrier,
        string $key,
        string $title,
        int $amount,
        float $amount_tax,
        int $amount_semitest,
        array $items_shipping_info,
        int $estimate_timestamp = 0,
        int $processing_timestamp = 0,
        int $_is_default = 0
    ) {
        $this->_carrier = $carrier;
        $this->_key = $key;
        $this->_title = $title;
        $this->_amount = $amount;
        $this->_amount_tax = $amount_tax;
        $this->_amount_semitest = $amount_semitest;
        $this->_items_shipping_info = $items_shipping_info;
        $this->_estimate_timestamp = $estimate_timestamp;
        $this->_processing_timestamp = $processing_timestamp;
        $this->_is_default = $_is_default;
        parent::__construct();
    }

    /**
     * 
     * @return Helper_Catalog_Shipping_Carrier
     */
    public function getCarrier(): Helper_Catalog_Shipping_Carrier {
        return $this->_carrier;
    }

    public function getKey() {
        return $this->_key;
    }

    public function getTitle() {
        return $this->_title;
    }

    public function getTitleWithCarrier() {
        $carrier_title = trim($this->_carrier->getTitle());

        return ($carrier_title ? ($carrier_title . ' - ') : '') . $this->getTitle();
    }

    public function getAmount() {
        return $this->_amount;
    }

    public function getAmountTax() {
        return $this->_amount_tax;
    }

    public function getAmountSemitest() {
        return $this->_amount_semitest;
    }

    public function getFloatAmount() {
        return OSC::helper('catalog/common')->integerToFloat($this->_amount);
    }

    public function getItemsShippingInfo() {
        return $this->_items_shipping_info;
    }

    public function getEstimateTimestamp() {
        return $this->_estimate_timestamp;
    }

    public function getEstimateDate() {
        return $this->_estimate_timestamp > 0 ? date('l, F d, Y', $this->_estimate_timestamp) : 'Unknow';
    }

    public function getProcessingTimestamp() {
        return $this->_processing_timestamp;
    }

    public function getProcessingDate() {
        return $this->_processing_timestamp > 0 ? date('l, F d, Y', $this->_processing_timestamp) : 'Unknow';
    }

    public function isRateDefault() {
        return $this->_is_default == 1;
    }

    public function toArray() {
        return [
            'key' => $this->_key,
            'title' => $this->_title,
            'amount' => $this->_amount,
            'amount_tax' => $this->_amount_tax,
            'amount_semitest' => $this->_amount_semitest,
            'items_shipping_info' => $this->_items_shipping_info,
            'estimate_timestamp' => $this->_estimate_timestamp,
            'processing_timestamp' => $this->_processing_timestamp,
            'is_default' => $this->_is_default
        ];
    }

}
