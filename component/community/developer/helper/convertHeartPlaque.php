<?php

class Helper_Developer_ConvertHeartPlaque extends OSC_Object
{
    const PRODUCT_TYPE_VARIANT_MAP = [
        'product_type_variant' => [
            '12.5x12.5' => 749,
            '15x15' => 750,
            '5x5' => 749
        ],

        'print_template_id' => [
            '12.5x12.5' => 177,
            '15x15' => 177,
            '5x5' => 177
        ]
    ];

    const PRODUCT_SIZE = ['12.5x12.5', '15x15', '5x5'];

    const PRODUCT_SIZE_DIMENSION = [
        "width" => 1545.3740724574425,
        'height' => 1545.3740724574425
    ];

    /**
     * @param $options
     * @return string|null
     */
    public static function getSize($options) {

        $product_size = self::PRODUCT_SIZE;

        $option_strs = strtolower(str_replace(' ', '', OSC::encode($options)));

        // GET size variant of Cart Item
        if (preg_match("/({$product_size[0]}|{$product_size[2]})/i", $option_strs, $matches)) {
            return $product_size[0];
        }

        if (preg_match("/({$product_size[1]})/i", $option_strs, $matches)){
            return $product_size[1];
        }

        $product_size_l = array_filter($options, function ($option) {
            return strtolower(trim($option)) === 'l';
        });

        if (count($product_size_l) > 0) {
            return $product_size[0];
        }

        $product_size_xl = array_filter($options, function ($option) {
            return strtolower(trim($option)) === 'xl';
        });

        if (count($product_size_xl) > 0) {
            return $product_size[1];
        }

        return null;
    }
}
