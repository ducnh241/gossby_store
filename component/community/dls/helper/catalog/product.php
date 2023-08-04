<?php

class Helper_Dls_Catalog_Product extends OSC_Object {

    public function getCampaignExpireEstimate(Model_Catalog_Product $product) {
        $segment = 60 * 60 * 24 * 2;

        $added_timestamp = intval($product->data['added_timestamp']);

        return $added_timestamp + ((ceil((time() - $added_timestamp) / $segment) + (($segment - ((time() - $added_timestamp) % $segment)) < (60 * 60) ? 1 : 0)) * $segment);
    }

    public function getDynamicSolds(Model_Catalog_Product $product) {
        return $product->data['solds'] + intval(substr($product->data['added_timestamp'], -2));
    }

    protected function __getPriorityPriceEstimate($product_type, $data, $country_code) {
        $data_item = [];
        foreach ($data[$country_code] as $p_type => $item) {
            if (strtolower($product_type) == strtolower($p_type)) {
                $data_item = $item;
            }
        }
        if (count($data_item) < 1 && isset($data[$country_code]['*'])) {
            $data_item = $data[$country_code]['*'];
        }
        return $data_item;
    }

    public function calculatePriceByCondition($current_price, $condition) {
        $new_price = 0;

        if ($condition['price_type'] === 'fixed_amount') {
            $new_price = OSC::helper('catalog/common')->floatToInteger(floatval($condition['price']));
        } elseif ($condition['price_type'] === 'percent') {
            $price = round(floatval($condition['price']) * $current_price / 10000, 2);

            $new_price = OSC::helper('catalog/common')->floatToInteger($price);
        }

        return $new_price;
    }

}
