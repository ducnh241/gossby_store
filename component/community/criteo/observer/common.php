<?php

class Observer_Criteo_Common
{
    public static function initialize($tracking_events)
    {
        $criteo_events = [];
        $criteo_react_event = [];

        $enable_criteo_tracking = intval(OSC::helper('core/setting')->get('tracking/criteo/enable')) === 1;
        $criteo_id = OSC::helper('core/setting')->get('tracking/criteo/id');

        if (!$enable_criteo_tracking || !$criteo_id) {
            return;
        }

        $flag_react = defined('OSC_REACTJS') && OSC_REACTJS == 1;
        $html_content = [];
        $tracking_init_codes = [
            'window.criteo_q = window.criteo_q || [];var deviceType = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";'
        ];

        foreach ($tracking_events as $event => $event_data) {
            switch ($event) {
                case "catalog/product_view":
                    $variant = OSC_Database_Model::getPreLoadedModel('catalog/product_variant', $event_data['variant_id']);
                    if (!($variant instanceof Model_Catalog_Product_Variant)) {
                        continue;
                    }

                    $product_variant_sku = $variant->data['sku'];
                    $criteo_events['viewItem'] = [
                        ['event' => 'setAccount', 'account' => $criteo_id],
                        ['event' => 'viewItem', 'item' => $product_variant_sku],
//                        ['event' => 'setEmail', 'email' => '', 'hash_method' => 'none'],
//                        ['event' => 'setSiteType', 'type' => 'deviceType'],
//                        ['event' => 'setZipcode', 'zipcode' => '']
                    ];
                    $tracking_init_codes[] = 'window.criteo_q.push(' . OSC::encode($criteo_events['viewItem']) . ');';
                    $criteo_react_event['viewItem'] = ['event' => 'viewItem', 'item' => $product_variant_sku];
                    break;
                case "catalog/add_to_cart":
                    $cart_item = OSC_Database_Model::getPreLoadedModel('catalog/cart_item', $event_data['line_item_id']);

                    if (!($cart_item instanceof Model_Catalog_Cart_Item) || $cart_item->isCrossSellMode()) {
                        continue;
                    }

                    $variant = $cart_item->getVariant();
                    $product_variant_sku = $variant->data['sku'];

                    $criteo_events['addToCart'] = [
                        ['event' => 'setAccount', 'account' => $criteo_id],
                        ['event' => 'addToCart', 'item' => [
                            'id' => $product_variant_sku, 'price' => $cart_item->getFloatPrice(), 'quantity' => $cart_item->data['quantity']
                        ]],
//                        ['event' => 'setEmail', 'email' => '', 'hash_method' => 'none'],
//                        ['event' => 'setSiteType', 'type' => 'deviceType'],
//                        ['event' => 'setZipcode', 'zipcode' => '']
                    ];
                    $tracking_init_codes[] = 'window.criteo_q.push(' . OSC::encode($criteo_events['addToCart']) . ');';
                    $criteo_react_event['addToCart'] = ['event' => 'addToCart', 'item' => [[
                        'id' => $product_variant_sku, 'price' => $cart_item->getFloatPrice(), 'quantity' => $cart_item->data['quantity']
                    ]]];
                    break;
                case "catalog/checkout_initialize":
                    // No action
                    break;
                case "catalog/purchase":
                    $order_id = isset($event_data['order_id']) ? $event_data['order_id'] : $event_data;
                    $order = OSC_Database_Model::getPreLoadedModel('catalog/order', $order_id);

                    if (!($order instanceof Model_Catalog_Order)) {
                        continue;
                    }

                    $item_data = [];
                    foreach ($order->getLineItems() as $line_item) {
                        $item_data[] = [
                            'id' => $line_item->getId(),
                            'quantity' => $line_item->data['quantity'],
                            'price' => $line_item->getFloatPrice()
                        ];
                    }

                    $criteo_events['trackTransaction'] = [
                        ['event' => 'setAccount', 'account' => $criteo_id],
                        ['event' => 'trackTransaction', 'id' => $order_id, 'item' => [$item_data]],
//                       ['event' => 'setSiteType', 'type' => deviceType],
//                        ['event' => 'setEmail', 'email' => '', 'hash_method' => 'none'],
//                        ['event' => 'setZipcode', 'zipcode' => '']
                    ];
                    $tracking_init_codes[] = 'window.criteo_q.push(' . OSC::encode($criteo_events['trackTransaction']) . ');';
                    $criteo_react_event['trackTransaction'] = ['event' => 'trackTransaction', 'id' => $order_id, 'item' => $item_data];
                    break;
                default:
                    break;
            }
        }

        if ($flag_react) {
            return [
                'social_chanel' => 'criteo',
                'position' => 'header',
                'events' => $criteo_react_event,
                'pixels' => $criteo_id
            ];
        }

        $html_content[] = '<script type="text/javascript" src="//dynamic.criteo.com/js/ld/ld.js?a=' . $criteo_id . '" async="true"></script>';

        $tracking_init_codes = implode('', $tracking_init_codes);
        OSC::core('template')->push($tracking_init_codes, 'js_separate');

        return implode('', $html_content);
    }
}