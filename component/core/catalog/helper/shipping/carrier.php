<?php

class Helper_Catalog_Shipping_Carrier extends OSC_Object {

    protected $_key = null;
    protected $_title = null;
    protected $_ship_from = null;
    protected $_rates = [];
    protected $_default_rate = null;

    public function __construct(string $key, string $title, array $ship_from, array $rates) {
        $this->_key = $key;
        $this->_title = $title;

        $this->_ship_from = OSC::helper('core/country')->verifyAddress($ship_from);

        usort($rates, function($a, $b) {
            return $a['amount'] > $b['amount'] ? 1 : ($a['amount'] < $b['amount'] ? -1 : 0);
        });

        foreach ($rates as $rate) {
            try {
                $this->_rates[] = ($rate instanceof Helper_Catalog_Shipping_Carrier_Rate) ?
                    $rate :
                    new Helper_Catalog_Shipping_Carrier_Rate(
                        $this,
                        (string) $rate['key'],
                        (string) $rate['title'],
                        (int) $rate['amount'],
                        (float) $rate['amount_tax'],
                        (int) $rate['amount_semitest'],
                        (array) $rate['items_shipping_info'],
                        (int) $rate['estimate_timestamp'],
                        (int) $rate['processing_timestamp'],
                        (int) $rate['is_default']
                    );
            } catch (Exception $ex) {
                
            }
        }

        parent::__construct();
    }

    public function getKey() {
        return $this->_key;
    }

    public function getTitle() {
        return $this->_title;
    }

    public function getShipFrom() {
        return $this->_ship_from;
    }

    public function getRates() {
        return $this->_rates;
    }

    /**
     * 
     * @param int $rate_index
     * @return Helper_Catalog_Shipping_Carrier_Rate
     */
    public function getRate(int $rate_index = 0) {
        return isset($this->_rates[$rate_index]) ? $this->_rates[$rate_index] : null;
    }

    public function selectRate(int $rate_index = 0) {
        if (!isset($this->_rates[$rate_index])) {
            return null;
        }

        return new Helper_Catalog_Shipping_Carrier($this->_key, $this->_title, $this->_ship_from, [$this->_rates[$rate_index]]);
    }

    public function selectRateByInstance($rate) {
        if (!($rate instanceof Helper_Catalog_Shipping_Carrier_Rate)) {
            return null;
        }

        return new Helper_Catalog_Shipping_Carrier($this->_key, $this->_title, $this->_ship_from, [$rate->toArray()]);
    }

    public function toArray() {
        $rates = [];

        foreach ($this->_rates as $rate) {
            $rates[] = $rate->toArray();
        }

        return [
            'key' => $this->_key,
            'title' => $this->_title,
            'ship_from' => $this->_ship_from,
            'rates' => $rates
        ];
    }

}
