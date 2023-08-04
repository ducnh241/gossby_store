<?php

/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright    Copyright (C) 2011 by SNETSER JSC (http://www.snetser.com). All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Le Tuan Sang - batsatla@gmail.com
 */
class Observer_Google_Common
{
    public static function initialize($tracking_events)
    {
        $data_layer = [];
        $google_merchant_id = intval(OSC::helper('core/setting')->get('tracking/google/survey/merchant_id'));

        foreach ($tracking_events as $event => $event_data) {
            if ($event == 'catalog/product_view') {

                $product_variant = OSC_Database_Model::getPreLoadedModel('catalog/product_variant', $event_data['variant_id']);
                if (!$product_variant instanceof Model_Catalog_Product_Variant) {
                    continue;
                }

                $product = $product_variant->getProduct();
                if (!$product instanceof Model_Catalog_Product) {
                    continue;
                }

                $price_for_customer = $product_variant->getPriceForCustomer();
                $price = OSC::helper('catalog/common')->integerToFloat(intval($price_for_customer['price']));

                $data_layer['view_item']['ecommerce']['currency'] = 'USD';
                $data_layer['view_item']['ecommerce']['value'] = $price;
                $data_layer['view_item']['ecommerce']['items'][] = static::_ecommerceProductData($product_variant->data['sku'], $product, $price, 1);
                $data_layer['view_item']['ecommerce']['remarketing'] = static::_remarketingData($price, [$product->getData('sku')]);

            } else if ($event == 'catalog/add_to_cart') {

                $cart_item = OSC_Database_Model::getPreLoadedModel('catalog/cart_item', $event_data['line_item_id']);
                if (!$cart_item instanceof Model_Catalog_Cart_Item || $cart_item->isCrossSellMode()) {
                    continue;
                }

                $product = $cart_item->getProduct();
                if (!$product instanceof Model_Catalog_Product) {
                    continue;
                }

                $product_variant = $cart_item->getVariant();
                $price_for_customer = $product_variant->getPriceForCustomer();
                $price = OSC::helper('catalog/common')->integerToFloat(intval($price_for_customer['price']));

                $data_layer['add_to_cart']['ecommerce']['currency'] = 'USD';
                $data_layer['add_to_cart']['ecommerce']['value'] = $price;
                $data_layer['add_to_cart']['ecommerce']['items'][] = static::_ecommerceProductData($product_variant->data['sku'], $product, $price, $cart_item->data['quantity']);
                $data_layer['add_to_cart']['ecommerce']['remarketing'] = static::_remarketingData($price, [$product->getData('sku')]);

            } else if ($event == 'catalog/remove_from_cart') {
                $product_variant = OSC_Database_Model::getPreLoadedModel('catalog/product_variant', $event_data['variant_id']);
                $product = $event_data['product'];
                $quantity = $event_data['quantity'] ?? 1;

                if (!$product_variant instanceof Model_Catalog_Product_Variant || !$product instanceof Model_Catalog_Product) {
                    continue;
                }

                $price_for_customer = $product_variant->getPriceForCustomer();
                $price = OSC::helper('catalog/common')->integerToFloat(intval($price_for_customer['price']));

                $data_layer['remove_from_cart']['ecommerce']['currency'] = 'USD';
                $data_layer['remove_from_cart']['ecommerce']['value'] = $price;
                $data_layer['remove_from_cart']['ecommerce']['items'][] = static::_ecommerceProductData($product_variant->data['sku'], $product, $price, $quantity);
                $data_layer['remove_from_cart']['ecommerce']['remarketing'] = static::_remarketingData($price, [$product->getData('sku')]);

            } else if ($event == 'catalog/checkout_initialize') {
                try {
                    /* @var $cart Model_Catalog_Cart */
                    $cart = OSC::helper('catalog/common')->getCart(false);
                } catch (Exception $ex) {
                    continue;
                }
                if (!($cart instanceof Model_Catalog_Cart)) {
                    continue;
                }

                $item_ids = [];
                $ecommerce_items = [];
                $subtotal_price = OSC::helper('catalog/common')->integerToFloat($cart->getSubtotal());
                foreach ($cart->getLineItems() as $line_item) {
                    $product = $line_item->getProduct();
                    $product_variant = $line_item->getVariant();

                    if ($line_item->isCrossSellMode() || !$product instanceof Model_Catalog_Product || !$product_variant instanceof Model_Catalog_Product_Variant) {
                        continue;
                    }

                    $price_for_customer = $product_variant->getPriceForCustomer();
                    $price = OSC::helper('catalog/common')->integerToFloat(intval($price_for_customer['price']));

                    $ecommerce_items[] = static::_ecommerceProductData($product_variant->data['sku'], $product, $price, $line_item->data['quantity']);
                    $item_ids[] = $product->getData('sku');
                }

                $data_layer['begin_checkout']['ecommerce']['currency'] = 'USD';
                $data_layer['begin_checkout']['ecommerce']['value'] = $subtotal_price;
                $data_layer['begin_checkout']['ecommerce']['items'] = $ecommerce_items;
                $data_layer['begin_checkout']['ecommerce']['remarketing'] = static::_remarketingData($subtotal_price, $item_ids);

            } else if ($event == 'catalog/purchase') {
                $order_id = $event_data['order_id'] ?? $event_data;
                $order = OSC_Database_Model::getPreLoadedModel('catalog/order', $order_id);

                if (!($order instanceof Model_Catalog_Order)) {
                    continue;
                }

                $item_ids = [];
                $ecommerce_items = [];

                foreach ($order->getLineItems() as $line_item) {
                    $product = $line_item->getProduct();
                    $product_variant = $line_item->getVariant();
                    if ($line_item->isCrossSellMode() || !$product instanceof Model_Catalog_Product || !$product_variant instanceof Model_Catalog_Product_Variant) {
                        continue;
                    }

                    $price_for_customer = $product_variant->getPriceForCustomer();
                    $price = OSC::helper('catalog/common')->integerToFloat(intval($price_for_customer['price']));
                    $ecommerce_items[] = static::_ecommerceProductData($product_variant->data['sku'], $product, $price, $line_item->data['quantity']);

                    $item_ids[] = $product->getData('sku');
                }

                $discount_codes = is_array($order->data['discount_codes']) && count($order->data['discount_codes']) > 0 ? implode(',', array_keys($order->data['discount_codes'])) : '';

                $subtotal_price = OSC::helper('catalog/common')->integerToFloat(intval($order->data['subtotal_price']));
                $data_layer['purchase']['ecommerce']['currency'] = 'USD';
                $data_layer['purchase']['ecommerce']['transaction_id'] = $order->getId();
                $data_layer['purchase']['ecommerce']['value'] = $order->getFloatSubtotalPrice();
                $data_layer['purchase']['ecommerce']['tax'] = OSC::helper('catalog/common')->integerToFloat($order->getTaxPrice());
                $data_layer['purchase']['ecommerce']['shipping'] = OSC::helper('catalog/common')->integerToFloat($order->getShippingPrice());
                $data_layer['purchase']['ecommerce']['items'] = $ecommerce_items;
                $data_layer['purchase']['ecommerce']['coupon'] = $discount_codes;
                $data_layer['purchase']['ecommerce']['discount'] = abs($order->getFloatTotalDiscountPrice());
                $data_layer['purchase']['ecommerce']['aw_feed_country'] = $order->data['shipping_country_code'];
                $data_layer['purchase']['ecommerce']['aw_feed_language'] = 'EN';
                $data_layer['purchase']['ecommerce']['affiliation'] = 'website';
                $data_layer['purchase']['ecommerce']['remarketing'] = static::_remarketingData($subtotal_price, $item_ids);

                $data_layer['purchase']['user'] = static::_getUserInfo($order);

                if ($google_merchant_id > 0) {
                    $data_layer['purchase']['ecommerce']['aw_merchant_id'] = $google_merchant_id;
                }
            }
        }

        $google_tagmanager_code = trim(OSC::helper('core/setting')->get('tracking/google/tag_manager/code'));

        $google_survey = [];
        if ($google_merchant_id > 0 && OSC::helper('core/setting')->get('tracking/google/survey')) {
            if (isset($tracking_events['catalog/purchase'])) {
                $order_id = $tracking_events['catalog/purchase']['order_id'] ?? $tracking_events['catalog/purchase'];
                $order = OSC_Database_Model::getPreLoadedModel('catalog/order', $order_id);

                if ($order instanceof Model_Catalog_Order) {
                    $delivery_date = date('Y-m-d', $order->getCarrier()->getRate()->getEstimateTimestamp() ? ($order->data['added_timestamp'] + (60 * 60 * 24 * 25)) : $order->getCarrier()->getRate()->getEstimateTimestamp());

                    $google_survey = [
                        'merchant_id' => $google_merchant_id,
                        'order_id' => $order->getId(),
                        'email' => $order->data['email'],
                        'delivery_country' => $order->data['shipping_country_code'],
                        'estimated_delivery_date' => $delivery_date,
                        'opt_in_style' => 'CENTER_DIALOG',
                    ];
                }
            }
        }

        $data_response = [
            'social_chanel' => 'google',
        ];

        if ($data_layer) {
            $data_response['google_ecommerce'] = [
                'position' => 'bottom_body',
                'events' => $data_layer
            ];
        }

        if ($google_tagmanager_code) {
            $data_response['google_tag'] = [
                'position' => 'header',
                'code' => $google_tagmanager_code
            ];
        }

        if ($google_survey && $google_merchant_id > 0 && OSC::helper('core/setting')->get('tracking/google/survey')) {
            $data_response['google_survey'] = [
                'position' => 'top_body',
                'google_merchant_id' => $google_merchant_id,
                'events' => $google_survey
            ];
        }

        return $data_response;
    }

    protected static function _ecommerceProductData($variant_sku, Model_Catalog_Product $product, $price, $quantity)
    {
        return [
            'item_id' => trim($product->data['sku']),
            'item_name' => $product->getProductTitle(),
            'item_brand' => OSC::helper('core/setting')->get('theme/site_name'),
            'item_category' => $product->data['product_type'],
            'item_list_name' => $product->data['vendor'],
            'item_variant' => $variant_sku,
            'product_id' => $product->getId(),
            'list_position' => $product->data['product_type'],
            'quantity' => $quantity,
            'price' => $price
        ];
    }

    protected static function _remarketingData($price, $item_ids)
    {
        $data_items = [];
        foreach ($item_ids as $id) {
            $data_items[] = ['id' => $id, 'google_business_vertical' => 'retail'];
        }

        return ['value' => $price, 'items' => $data_items];
    }

    protected static function _getUserInfo(Model_Catalog_Order $order): array
    {
        $name_parts = explode(' ', $order->data['billing_full_name']);
        $first_name = implode(' ', array_slice($name_parts, 0, -1));
        $last_name = end($name_parts);
        return [
            'phone' => $order->getData('billing_phone') ?: '',
            'email' => $order->getData('email') ?: '',
            'first_name' => $first_name,
            'last_name' => $last_name,
            'street' => $order->getData('billing_address1') ?: '',
            'city' => $order->getData('billing_city') ?: '',
            'province' => $order->getData('billing_province') ?: '',
            'country' => $order->getData('billing_country') ?: '',
            'postal_code' => $order->getData('billing_zip') ?: ''
        ];
    }

}
