<?php

class Helper_Catalog_ReferenceTransaction extends OSC_Object {
    public function getShippingPrice($product_type_id, $country_code, $province_code, $quantity, $pack_data) {
        $shipping_settings = OSC::helper('core/setting')->get('shipping/shipping_by_quantity/table');

        if (is_array($pack_data) && $pack_data['id'] !== 0 && count($pack_data['shipping_values']) !== 0) {
            $shipping_price = OSC::helper('catalog/common')->getShippingPackPrice(
                $country_code,
                $province_code,
                $quantity,
                []
            );
        } else {
            $shipping_configs = OSC::helper('catalog/common')->getShippingFeeConfigs(
                $country_code,
                $province_code,
                $product_type_id,
                $shipping_settings
            );
            $shipping_price = OSC::helper('catalog/common')->getBuffShipping($shipping_configs, $quantity)['price'];
        }

        return OSC::helper('catalog/common')->floatToInteger(floatval($shipping_price));
    }
}
