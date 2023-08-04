<?php

class Observer_TikTok_Common
{
    public static function initialize($tracking_events)
    {
        $pixel_ids = [];

        $tiktok_events = ['Browse' => null];

        $enable_tiktok_pixel = intval(OSC::helper('core/setting')->get('tracking/tiktok/enable')) === 1;
        $tiktok_pixels = OSC::helper('core/setting')->get('tracking/tiktok/pixels');

        if (!$enable_tiktok_pixel || !$tiktok_pixels) {
            return;
        }

        foreach ($tracking_events as $event => $event_data) {
            switch ($event) {
                case "catalog/product_view":
                    //content_type, quantity, description, content_id, currency, value
                    $variant = OSC_Database_Model::getPreLoadedModel('catalog/product_variant', $event_data['variant_id']);
                    if (!($variant instanceof Model_Catalog_Product_Variant)) {
                        continue;
                    }

                    $product_variant_sku = $variant->data['sku'];

                    $prices = $variant->getPriceForCustomer();
                    $tiktok_events['ViewContent'] = [
                        'content_type' => 'product_group',
                        'quantity' => 1,
                        'content_id' => $product_variant_sku,
                        'currency' => 'USD',
                        'value' => OSC::helper('catalog/common')->integerToFloat(intval($prices['price'])),
                        'content_name' => $variant->getVariantTitle(),
                    ];
                    break;
                case "catalog/add_to_cart":
                    //content_type, quantity, description, content_id, currency, value
                    $cart_item = OSC_Database_Model::getPreLoadedModel('catalog/cart_item', $event_data['line_item_id']);

                    if (!($cart_item instanceof Model_Catalog_Cart_Item) || $cart_item->isCrossSellMode()) {
                        continue;
                    }

                    $variant = $cart_item->getVariant();
                    $product_variant_sku = $variant->data['sku'];

                    $tiktok_events['AddToCart'] = [
                        'content_type' => 'product_group',
                        'quantity' => $cart_item->data['quantity'],
                        'content_id' => $product_variant_sku,
                        'currency' => 'USD',
                        'price' => $cart_item->getFloatPrice(),
                        'value' => $cart_item->getFloatAmount(),
                        'content_name' => $cart_item->getProduct()->getProductTitle()
                    ];

                    break;
                case "catalog/checkout_initialize":
                    try {
                        /* @var $cart Model_Catalog_Cart */
                        $cart = OSC::helper('catalog/common')->getCart(false);
                    } catch (Exception $ex) {
                        continue;
                    }

                    if (!($cart instanceof Model_Catalog_Cart)) {
                        continue;
                    }

                    $checkout_data = [];
                    foreach ($cart->getLineItems() as $line_item) {
                        $variant = $line_item->getVariant();
                        $content = [
                            'content_type' => 'product',
                            'content_id' => $variant->data['sku'],
                            'content_name' => 'Initiate Checkout',
                            'quantity' => $line_item->data['quantity'],
                            'price' => $line_item->getFloatPrice()
                        ];

                        $checkout_data['contents'][] = $content;
                    }
                    $checkout_data['value'] = $cart->getFloatSubtotal();
                    $checkout_data['currency'] = 'USD';
                    $tiktok_events['InitiateCheckout'] = $checkout_data;

                    break;
                case "catalog/purchase":
                    $order_id = $event_data['order_id'] ?? $event_data;
                    $order = OSC_Database_Model::getPreLoadedModel('catalog/order', $order_id);

                    if (!($order instanceof Model_Catalog_Order)) {
                        continue;
                    }

                    $contents = [];
                    foreach ($order->getLineItems() as $line_item) {
                        $variant = $line_item->getVariant();
                        $content = [
                            'content_type' => 'product',
                            'content_id' => $variant->data['sku'],
                            'content_name' => 'Place an Order',
                            'quantity' => $line_item->data['quantity'],
                            'price' => $line_item->getFloatPrice()
                        ];

                        $contents[] = $content;
                    }

                    $tiktok_events['CompletePayment'] = [
                        'contents' => $contents,
                        'value' => $order->getFloatSubtotalPrice(),
                        'currency' => 'USD'
                    ];

                    $tiktok_events['PlaceAnOrder'] = [
                        'contents' => $contents,
                        'value' => $order->getFloatSubtotalPrice(),
                        'currency' => 'USD'
                    ];

                    $tiktok_events['contact_infor'] = self::_validateInfoContact($order);

                    break;
                default:
                    break;
            }
        }

        $tiktok_pixels = explode("\n", $tiktok_pixels);

        foreach ($tiktok_pixels as $tiktok_pixel) {
            $tiktok_pixel = trim($tiktok_pixel);

            if (!self::_validateTikTokPixel($tiktok_pixel)) {
                continue;
            }

            $pixel_ids[] = $tiktok_pixel;
        }

        if (count($pixel_ids) < 1) {
            return;
        }

        $pixel_ids = array_unique($pixel_ids);

        unset($tiktok_events['Browse']);

        return [
            'social_chanel' => 'tiktok',
            'position' => 'header',
            'events' => $tiktok_events,
            'pixels' => $pixel_ids
        ];
    }

    protected static function _validateTikTokPixel($pixel)
    {
        //C383OAB521OOMP6VTEGG
        return preg_match("/^[a-zA-Z0-9]+$/", $pixel);
    }

    protected function _validateInfoContact(Model_Catalog_Order $order) {
        $phone_prefix_countries = OSC::helper('core/country')->getPhonePrefixCountries();

        if ($order->data['billing_phone']) {
            $phone_number = $order->data['billing_phone'];
            $country_code = $order->data['billing_country_code'];
        } else {
            $phone_number = $order->data['shipping_phone'];
            $country_code = $order->data['shipping_country_code'];
        }

        $phone_prefix = (isset($phone_prefix_countries[$country_code]) && $phone_prefix_countries[$country_code]) 
            ? '+' . $phone_prefix_countries[$country_code] 
            : '+1';

        return [
            'email' => $order->data['email'],
            'phone_number' => $phone_prefix . ltrim($phone_number, '0')
        ];
    }
}