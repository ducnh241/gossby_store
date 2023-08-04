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
 * @copyright	Copyright (C) 2011 by SNETSER JSC (http://www.snetser.com). All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Le Tuan Sang - batsatla@gmail.com
 */
class Observer_Pinterest_Common
{

    public static function initialize($tracking_events)
    {
        $pinterest_id = trim(OSC::helper('core/setting')->get('tracking/pinterest/tag/id'));
        $pinterest_enable = OSC::helper('core/setting')->get('tracking/pinterest/tag');
        $email = OSC::helper('core/setting')->get('tracking/pinterest/tag/email');

        $pinterest_events = [];

        $flag_react = defined('OSC_REACTJS') && OSC_REACTJS == 1;

        if (isset($pinterest_id) && $pinterest_enable == 1) {
            foreach ($tracking_events as $event => $event_data) {
                if ($event == 'catalog/product_view') {
                    $variant = OSC_Database_Model::getPreLoadedModel('catalog/product_variant', $event_data['variant_id']);

                    if (!($variant instanceof Model_Catalog_Product_Variant)) {
                        continue;
                    }

                    $pinterest_events['pagevisit'] = [
                        'line_items' => [[
                            'product_name' => $variant->getProduct()->getProductTitle(),
                            'product_id' => $variant->getProduct()->getId(),
                            'product_variant_id' => $variant->getId()
                        ]]
                    ];

                } else if ($event == 'catalog/add_to_cart') {
                    $cart_item = OSC_Database_Model::getPreLoadedModel('catalog/cart_item', $event_data['line_item_id']);

                    if (!($cart_item instanceof Model_Catalog_Cart_Item) || $cart_item->isCrossSellMode()) {
                        continue;
                    }

                    $pinterest_events['addtocart'] = [
                        'value' => $cart_item->getFloatPrice(),
                        'order_quantity' => $cart_item->data['quantity'],
                        'currency' => 'USD',
                        'promo_code' => $cart_item->getDiscount(),
                        'line_items' => [[
                            'product_id' => $cart_item->getProduct()->getId(),
                            'product_name' => $cart_item->getProduct()->getProductTitle(),
                            'product_variant_id' => $cart_item->data['variant_id']
                        ]]
                    ];

                } else if ($event == 'catalog/purchase') {
                    $order_id = isset($event_data['order_id']) ? $event_data['order_id'] : $event_data;
                    $order = OSC_Database_Model::getPreLoadedModel('catalog/order', $order_id);
                    if (!($order instanceof Model_Catalog_Order)) {
                        continue;
                    }
                    $getFloatPrice = $order->getFloatTotalPrice();
                    $order_quantity = 0;
                    $line_items = [];
                    foreach ($order->getLineItems() as $line_item) {
                        if ($line_item->isCrossSellMode()) {
                            continue;
                        }
                        $order_quantity += $line_item->data['quantity'];
                        $line_items[] = [
                            'product_id' => $line_item->getProduct()->getId(),
                            'product_name' => $line_item->getProduct()->getProductTitle(),
                            'product_variant_id' => $line_item->data['variant_id']
                        ];
                    }

                    $pinterest_events['checkout'] = [
                        'value' => $getFloatPrice,
                        'order_quantity' => $order_quantity,
                        'currency' => 'USD',
                        'line_items' => $line_items
                    ];
                }
            }
            $event_key = array_keys($pinterest_events)[0];

            $event_data = OSC::encode($pinterest_events[$event_key]);

            $event = null;

            if ($event_key != '') {
                $event = <<<EOF
pintrk('track', '{$event_key}', {$event_data}, function(didInit, error) { if (!didInit) { console.log(error); }});
EOF;
            }

            $hash_email = hash('sha256', $email);

            if ($flag_react) {
                return [
                    'social_chanel' => 'pinterest',
                    'position' => 'header',
                    'events' => $pinterest_events,
                    'pinterest_id' => $pinterest_id,
                    'hash_email' => $hash_email,
                    'event_key' => $event_key
                ];
            }

            OSC::core('template')->push(<<<EOF
        !function(e){if(!window.pintrk){window.pintrk = function () {
            window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var
            n=window.pintrk;n.queue=[],n.version="3.0";var
            t=document.createElement("script");t.async=!0,t.src=e;var
            r=document.getElementsByTagName("script")[0];
            r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");
        pintrk('load', {$pinterest_id}, {em: '{$hash_email}'});
        pintrk('page');
EOF
                , 'js_init')->push($event, 'js_init');
        }
    }

}
