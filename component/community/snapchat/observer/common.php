<?php

class Observer_Snapchat_Common
{
    public static function initialize($tracking_events)
    {
        $pixel_ids = [];

        $snapchat_events = ['PAGE_VIEW' => null];

        $enable_snapchat_pixel = intval(OSC::helper('core/setting')->get('tracking/snapchat/enable')) === 1;
        $snapchat_pixels = OSC::helper('core/setting')->get('tracking/snapchat/pixels');

        if (!$enable_snapchat_pixel || !$snapchat_pixels) {
            return;
        }

        $flag_react = defined('OSC_REACTJS') && OSC_REACTJS == 1;
        $user_email = '';

        try {
            $cart = OSC::helper('catalog/common')->getCart(false);
            $user_email = $cart->data['email'] ?: '';
        } catch (Exception $ex) {

        }

        foreach ($tracking_events as $event => $event_data) {
            switch ($event) {
                case "catalog/product_view":
                    $variant = OSC_Database_Model::getPreLoadedModel('catalog/product_variant', $event_data['variant_id']);
                    if (!($variant instanceof Model_Catalog_Product_Variant)) {
                        continue;
                    }
                    $product_variant_sku = $variant->data['sku'];
                    $client_deduplication_id = 'product_view_' . $product_variant_sku . time();

                    $prices = $variant->getPriceForCustomer();
                    $snapchat_events['VIEW_CONTENT'] = [
                        'price' => OSC::helper('catalog/common')->integerToFloat(intval($prices['price'])),
                        'client_deduplication_id' => $client_deduplication_id,
                        'currency' => 'USD',
                        'item_ids' => [$product_variant_sku],
                        'item_category' => $variant->getProduct()->data['product_type'],
                    ];
                    break;
                case "catalog/add_to_cart":
                    $cart_item = OSC_Database_Model::getPreLoadedModel('catalog/cart_item', $event_data['line_item_id']);

                    if (!($cart_item instanceof Model_Catalog_Cart_Item) || $cart_item->isCrossSellMode()) {
                        continue;
                    }

                    $variant = $cart_item->getVariant();
                    $product_variant_sku = $variant->data['sku'];
                    $client_deduplication_id = 'add_cart_' . $cart_item->data['item_id'];

                    $snapchat_events['ADD_CART'] = [
                        'price' => $cart_item->getFloatAmount(),
                        'number_items' => $cart_item->data['quantity'],
                        'client_deduplication_id' => $client_deduplication_id,
                        'currency' => 'USD',
                        'item_ids' => [$product_variant_sku],
                        'item_category' => $variant->getProduct()->data['product_type'],
                    ];

                    $cart = $cart_item->getCart();
                    $user_email = $cart->data['email'] ?: $user_email;

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

                    $client_deduplication_id = 'start_checkout_' . $cart->data['cart_id'];
                    $snapchat_events['START_CHECKOUT'] = [
                        'price' => $cart->getFloatSubtotal(),
                        'number_items' => $cart->getQuantity(),
                        'client_deduplication_id' => $client_deduplication_id,
                        'currency' => 'USD',
                        'payment_info_available' => 1
                    ];

                    $user_email = $cart->data['email'] ?: $user_email;

                    break;
                case "catalog/purchase":
                    $order_id = isset($event_data['order_id']) ? $event_data['order_id'] : $event_data;
                    $order = OSC_Database_Model::getPreLoadedModel('catalog/order', $order_id);

                    if (!($order instanceof Model_Catalog_Order)) {
                        continue;
                    }

                    $client_deduplication_id = 'purchase_' . $order->data['order_id'];

                    $snapchat_events['PURCHASE'] = [
                        'price' => $order->getFloatSubtotalPrice(),
                        'number_items' => 0,
                        'client_deduplication_id' => $client_deduplication_id,
                        'currency' => 'USD',
                        'payment_info_available' => 1,
                        'transaction_id' => $order->data['order_id']
                    ];

                    $user_email = $order->data['email'];

                    foreach ($order->getLineItems() as $line_item) {
                        $snapchat_events['PURCHASE']['number_items'] += $line_item->data['quantity'];
                    }
                    break;
                default:
                    break;
            }
        }

        $snapchat_pixels = explode("\n", $snapchat_pixels);

        foreach ($snapchat_pixels as $snapchat_pixel) {
            $snapchat_pixel = trim($snapchat_pixel);

            if (!self::_validateSnapchatPixel($snapchat_pixel)) {
                continue;
            }

            $pixel_ids[] = $snapchat_pixel;
        }

        if (count($pixel_ids) < 1) {
            return;
        }

        $data_init = [
            'ip_address' => OSC::getClientIP(),
        ];
        if ($user_email) {
            $data_init['user_email'] = $user_email;
        }
        $pixel_ids = array_unique($pixel_ids);

        if ($flag_react) {
            unset($snapchat_events['PAGE_VIEW']);
            return [
                'social_chanel' => 'snapchat',
                'position' => 'header',
                'events' => $snapchat_events,
                'pixels' => $pixel_ids,
                'data_init' => $data_init
            ];
        }

        $tracking_codes = [];
        $tracking_init = [];
        $data_init = OSC::encode($data_init);

        foreach ($snapchat_events as $event_key => $event_data) {
            if ($event_data) {
                $event_data = ',' . OSC::encode($event_data);
            } else {
                $event_data = '';
            }

            $tracking_codes[] = "snaptr('track', '{$event_key}'{$event_data})";
        }

        foreach ($pixel_ids as $pixel_id) {
            $tracking_init[] = "snaptr('init', '{$pixel_id}', $data_init)";
        }

        $tracking_codes = implode(";", $tracking_codes);
        $tracking_init = implode(";", $tracking_init);

        OSC::core('template')->push(<<<EOF
(function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function()
{a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};
a.queue=[];var s='script';r=t.createElement(s);r.async=!0;
r.src=n;var u=t.getElementsByTagName(s)[0];
u.parentNode.insertBefore(r,u);})(window,document,
'https://sc-static.net/scevent.min.js');

{$tracking_init}

{$tracking_codes}
EOF
            , 'js_separate');
    }

    protected static function _validateSnapchatPixel($pixel)
    {
        //8f97c562-ac28-4da4-a05a-d9da5a919b62
        return preg_match("/^[0-9a-zA-Z-]+$/", $pixel);
    }
}