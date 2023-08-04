<?php

class Helper_Dls_Catalog_Checkout extends OSC_Object {

    public function getShippingEstimate(
        $variant = null,
        $product_type = null,
        $quantity = 1,
        $country_code = null,
        $province_code = null
    ) {
        /* @var $cart Model_Catalog_Cart */

        $package_items = [];

        $package_detail_items = [];

        $flag_view_detail = null;

        $product_type_variant_ids = [];
        $product_type_ids = [];

        $variant_detail_ids = [];

        if ($variant instanceof Model_Catalog_Product_Variant) {
            $product_type_variant_id = 0;
            $product_type_id = 0;

            if ($variant->isCampaign()) {
                $product_type_variant_id = $variant->getProductTypeVariant()->getId();
                $product_type_id = $variant->getProductType()->getId();
            }

            $package_detail_items[$variant->getId()] = [
                'quantity' => $quantity,
                'require_packing' => $variant->data['require_packing'],
                'keep_flat' => $variant->data['keep_flat'],
                'weight' => $variant->getWeightInGram(),
                'width' => $variant->data['dimension_width'],
                'height' => $variant->data['dimension_height'],
                'length' => $variant->data['dimension_length']
            ];

            $variant_detail_ids[$variant->getId()] = [
                'product_type_variant_id' => $product_type_variant_id,
                'product_type_id' => $product_type_id,
                'quantity' => $quantity
            ];

            $product_type_variant_ids[] = $product_type_variant_id;
            $product_type_ids[] = $product_type_id;
        }

        $total_price = 0;
        $cart_id = null;
        $currency_code = 'USD';
        $cart_item_ids = [];

        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);
            
            if (!($cart instanceof Model_Catalog_Cart)) {
                throw new Exception('cart null');
            }

            $data_update = [];

            $country_code = $country_code ?? $cart->data['shipping_country_code'];
            if ($country_code != $cart->data['shipping_country_code']) {
                $data_update['shipping_country_code'] = $country_code;
                $data_update['shipping_country'] = OSC::helper('core/country')->getCountryTitle($country_code);
            }

            $province_code = $province_code ?? $cart->data['shipping_province_code'];

            if ($province_code != $cart->data['shipping_province_code']) {
                $data_update['shipping_province_code'] = $province_code;
                $data_update['shipping_province'] = OSC::helper('core/country')->getProvinceTitle($country_code, $province_code);
            }

            if (!empty($data_update)) {
                $cart->setData($data_update)->save();
            }

            foreach ($cart->getLineItems() as $line_item) {
                $product_type_variant_id = 0;
                $product_type_id = 0;

                if ($line_item->isCampaignMode()) {
                    $product_type_variant_id = $line_item->getProductTypeVariantId();
                    $product_type_id = $line_item->getVariant()->getProductType()->getId();
                }

                $package_items[$line_item->getId()] = [
                    'quantity' => $line_item->data['quantity'],
                    'require_packing' => $line_item->data['require_packing'],
                    'keep_flat' => $line_item->data['keep_flat'],
                    'weight' => $line_item->getWeightInGram(),
                    'width' => $line_item->data['dimension_width'],
                    'height' => $line_item->data['dimension_height'],
                    'length' => $line_item->data['dimension_length'],
                    'info' => [
                        'variant_id' => $line_item->data['variant_id'],
                        'ukey' => $line_item->data['ukey']
                    ]
                ];

                $product_type_variant_ids[] = $product_type_variant_id;
                $product_type_ids[] = $product_type_id;

                $cart_item_ids[$line_item->getId()] = [
                    'product_type_variant_id' => $product_type_variant_id,
                    'product_type_id' => $product_type_id
                ];
            }

            if ($cart) {
                $total_price = $cart->getFloatSubtotal();
                $cart_id = $cart->getId();
                $currency_code = $cart->data['currency_code'];
            }
        } catch (Exception $ex) {}

        if (count($cart_item_ids) > 0 && count($package_items) < 1) {
            return null;
        }

        if (!$country_code) {
            $location = OSC::helper('catalog/common')->getCustomerShippingLocation();

            $country_code = $location['country_code'];
            $province_code = $location['province_code'];
        }

        // if country US, province empty => default CA
        if (strtoupper($country_code) == 'US' && empty($province_code)) {
            $province_code = 'CA';
        }

        $carrier = OSC::obj('Observer_Catalog_Shipping')->collectRates([
            'cart_id' => $cart_id,
            'total_price' => $total_price,
            'packages' => OSC::helper('catalog/checkout')->calculatePackages($package_items),
            'currency_code' => $currency_code,
            'shipping_address' => ['country_code' => $country_code, 'province_code' => $province_code],
            'ship_from' => OSC::helper('core/setting')->get('catalog/store/address') ?? [],
            'product_type_variant_ids' => $product_type_variant_ids,
            'product_type_ids' => $product_type_ids,
            'packages_detail' => OSC::helper('catalog/checkout')->calculatePackages($package_detail_items),
            'cart_item_ids' => $cart_item_ids,
            'variant_detail_ids' => $variant_detail_ids
        ]);

        return $carrier ? $carrier->getRates() : null;
    }

}
