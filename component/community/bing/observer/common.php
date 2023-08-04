<?php

class Observer_Bing_Common
{
    /**
     * @param $tracking_events
     * @return array|void
     * @throws OSC_Exception_Runtime
     * @document:
     * https://community.tealiumiq.com/t5/Client-Side-Tags/Bing-Ads-Universal-Event-Tracking-UET-Tag-Setup-Guide/ta-p/12092
     * https://help.ads.microsoft.com/#apex/ads/en/help:app51203/1/en-US/#ext:vnext_conversiongoals
     * https://help.ads.microsoft.com/#apex/ads/en/56910/1-500
     */
    public static function initialize($tracking_events)
    {
        $universal_event_tracking = [];

        $bing_events = ['view_page' => []];

        $enable_tracking_bing = intval(OSC::helper('core/setting')->get('tracking/bing/enable')) === 1;
        $tracking_bing_id = trim(OSC::helper('core/setting')->get('tracking/bing/id'));

        if (!$enable_tracking_bing || !$tracking_bing_id || !self::_validateUniversalEventTracking($tracking_bing_id)) {
            return;
        }

        $flag_react = defined('OSC_REACTJS') && OSC_REACTJS == 1;

        foreach ($tracking_events as $event => $event_data) {
            switch ($event) {
                case "catalog/product_view":
                    $variant = OSC_Database_Model::getPreLoadedModel('catalog/product_variant', $event_data['variant_id']);
                    if (!($variant instanceof Model_Catalog_Product_Variant)) {
                        break;
                    }

                    $product_variant_sku = $variant->data['sku'];

                    $prices = $variant->getPriceForCustomer();
                    $product = $variant->getProduct();
                    if (!$product instanceof Model_Catalog_Product) {
                        break;
                    }
                    // This action no need any param
                    $bing_events['view_item'] = [
                        'event_value' => OSC::helper('catalog/common')->integerToFloat(intval($prices['price'])),
                        'content_type' => 'product_group',
                        'product_id' => $product_variant_sku,
                        'product_name' => $product->getProductTitle(),
                        'product_brand' => OSC::helper('core/setting')->get('theme/site_name'),
                        'product_category' => $product->data['product_type'],
                        'product_quantity' => 1,
                    ];
                    break;
                case "catalog/add_to_cart":
                    $cart_item = OSC_Database_Model::getPreLoadedModel('catalog/cart_item', $event_data['line_item_id']);

                    if (!($cart_item instanceof Model_Catalog_Cart_Item) || $cart_item->isCrossSellMode()) {
                        break;
                    }

                    $variant = $cart_item->getVariant();
                    $product_variant_sku = $variant->data['sku'];

                    $product = $variant->getProduct();
                    if (!$product instanceof Model_Catalog_Product) {
                        break;
                    }

                    // This action no need any param
                    $bing_events['add_to_cart'] = [
                        'event_value' => $cart_item->getFloatAmount(),
                        'content_type' => 'product_group',
                        'content_id' => $product_variant_sku,
                        'product_id' => $product_variant_sku,
                        'product_name' => $product->getProductTitle(),
                        'product_brand' => OSC::helper('core/setting')->get('theme/site_name'),
                        'product_category' => $product->data['product_type'],
                        'product_quantity' => $cart_item->data['quantity'],
                    ];

                    break;
                case "catalog/checkout_initialize":
                    try {
                        /* @var $cart Model_Catalog_Cart */
                        $cart = OSC::helper('catalog/common')->getCart(false);
                    } catch (Exception $ex) {
                        break;
                    }

                    if (!($cart instanceof Model_Catalog_Cart)) {
                        break;
                    }
                    $ecomm_prodid = [];

                    // This action need revenue_value, currency
                    $bing_events['begin_checkout'] = [
                        'event_value' => $cart->getFloatSubtotal(),
                        'revenue_value' => $cart->getFloatSubtotal(),
                        'currency' => 'USD',
                        'content_type' => 'product_group',
                        'product_quantity' => $cart->getQuantity(),
                        'product_brand' => OSC::helper('core/setting')->get('theme/site_name')
                    ];

                    foreach ($cart->getLineItems() as $line_item) {
                        $ecomm_prodid[] = $line_item->getVariant()->data['sku'];
                    }
                    $bing_events['begin_checkout']['product_id'] = array_values($ecomm_prodid);

                    break;
                case "catalog/purchase":
                    $order_id = $event_data['order_id'] ?? $event_data;
                    $order = OSC_Database_Model::getPreLoadedModel('catalog/order', $order_id);

                    if (!($order instanceof Model_Catalog_Order)) {
                        break;
                    }

                    $ecomm_prodid = [];

                    // This action need revenue_value, currency
                    $bing_events['purchase'] = [
                        'order_id' => $order->getUkey(),
                        'order_total' => $order->getFloatTotalPrice(),
                        'order_subtotal' => $order->getFloatSubtotalPrice(),
                        'order_currency' => 'USD',
                        'product_quantity' => 0,
                        'event_value' => $order->getFloatSubtotalPrice(),
                        'revenue_value' => $order->getFloatSubtotalPrice(),
                        'currency' => 'USD'
                    ];

                    foreach ($order->getLineItems() as $line_item) {
                        $bing_events['purchase']['product_quantity'] += $line_item->data['quantity'];
                        $ecomm_prodid[] = $line_item->getVariant()->data['sku'];
                    }
                    $bing_events['purchase']['product_id'] = array_values($ecomm_prodid);
                    break;
                default:
                    break;
            }
        }

        if ($flag_react) {
            unset($bing_events['view_page']);
            return [
                'social_chanel' => 'bing',
                'position' => 'header',
                'events' => $bing_events,
                'tracking_id' => $tracking_bing_id
            ];
        }

        foreach ($bing_events as $event_key => $event_data) {
            $product_id = $event_data['product_id'] ?? [];
            if ($event_data) {
                $event_data_destination = OSC::encode([
                        'revenue_value' => $event_data['revenue_value'] ?? 0,
                        'currency' => 'USD'
                    ]
                );
                $event_data = OSC::encode($event_data);
                $event_remarketing = OSC::encode([
                    'ecomm_prodid' => $product_id,
                    'ecomm_pagetype' => 'other'
                ]);
            } else {
                $event_data = '{}';
                $event_data_destination = '{}';
                $event_remarketing = '';
            }
            //window.uetq.push('event', 'purchase', {});
            //window.uetq.push('event', '', {"revenue_value":20,"currency":"USD"});
            //window.uetq.push('event', '', { 'ecomm_prodid': 'REPLACE_WITH_PRODUCT_ID', 'ecomm_pagetype': 'REPLACE_WITH_PAGE_TYPE' });
            $tracking_codes[] = "window.uetq.push('event', '{$event_key}', $event_data)";
            $tracking_codes[] = "window.uetq.push('event', '', $event_data_destination)";
            if ($event_remarketing) {
                $tracking_codes[] = "window.uetq.push('event', '', $event_remarketing)";
            }
        }

        $tracking_codes = implode('; ', $tracking_codes);

        OSC::core('template')->push(<<<EOF
(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"{$tracking_bing_id}"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");

window.uetq = window.uetq || [];

{$tracking_codes};
EOF
            , 'js_separate');
    }

    protected static function _validateUniversalEventTracking($pixel)
    {
        //134623687
        return preg_match("/^[0-9]+$/", $pixel);
    }
}