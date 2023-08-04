<?php

class Helper_Currency_Common extends OSC_Object {

    public function convertToMinimumUnit($value, $currency) {
        //$value = round($value, 2);
        $currency = strtolower(trim($currency));

        $converted_value = OSC::core('observer')->dispatchEvent('currency/convert_to_minimum_unit/' . $currency, $value, false);

        if (!$converted_value) {
            throw new Exception('The currency is not supported');
        }

        return $converted_value;
    }

}
