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
class Observer_Facebook_Common
{

    public static function initialize($tracking_events)
    {
        $pixel_ids = [];
        $page_tags = [];

        $facebook_events = ['PageView' => null];

        $group_product_mode_on = intval(OSC::helper('core/setting')->get('catalog/feed_facebook/group_product_mode_on')) == 1;
        $enable_facebook_pixel = OSC::helper('core/setting')->get('tracking/facebook_pixel');

        $flag_react = defined('OSC_REACTJS') && OSC_REACTJS == 1;

        $user_data_email = null;
        $user_data_phone = null;
        $user_data_first_name = null;
        $user_data_last_name = null;
        $user_data_zip_code = null;

        $ip_address = OSC::getClientIP();

        $location = OSC::helper('core/common')->getIPLocation($ip_address);
        $country_code_hash = hash('sha256', strtolower($location['country_code']));
        $city = strtolower($location['city']);
        $state_code = strtolower($location['region_code']);

        foreach ($tracking_events as $event => $event_data) {
            switch ($event) {
                case "catalog/product_view":
                    $variant = OSC_Database_Model::getPreLoadedModel('catalog/product_variant', $event_data['variant_id']);
                    if (!($variant instanceof Model_Catalog_Product_Variant)) {
                        continue;
                    }

                    $product = $variant->getProduct();

                    $product_variant_id = $variant->data['id'];
                    if ($group_product_mode_on) {
                        $product_variant_id = $product->data['product_id'];
                    }

                    $prices = $variant->getPriceForCustomer();
                    $event_id = $ip_address . '_' . $product->getId() . '_' . time();
                    $facebook_events['ViewContent'] = [
                        'data' => [
                            'content_type' => $group_product_mode_on ? 'product_group' : 'product',
                            'content_ids' => strval($product_variant_id),
                            'value' => OSC::helper('catalog/common')->integerToFloat(intval($prices['price'])),
                            'content_name' => $variant->getVariantTitle(),
                            'currency' => 'USD',
                            'country' => $country_code_hash,
                            'ct' => $city,
                            'st' => $state_code
                        ],
                        'event' => [
                            'eventID' => $event_id,
                            'event_name' => 'ViewContent'
                        ]
                    ];

                    $page_tags = array_merge($page_tags, $variant->getProduct()->data['tags']);

                    $private_pixel_ids = static::_fetchProductPixelIds($variant->getProduct());

                    foreach ($private_pixel_ids as $private_pixel_id) {
                        $pixel_ids[] = $private_pixel_id;
                    }

                    $product_type_pixel_ids = static::_fetchPixelIdsGroupByProductType($variant);
                    foreach ($product_type_pixel_ids as $product_type_pixel_id) {
                        $pixel_ids[] = $product_type_pixel_id;
                    }
                    break;
                case "catalog/add_to_cart":
                    $cart_item = OSC_Database_Model::getPreLoadedModel('catalog/cart_item', $event_data['line_item_id']);

                    if (!($cart_item instanceof Model_Catalog_Cart_Item)) {
                        continue;
                    }
                    if ($cart_item->isCrossSellMode()) {
                        continue;
                    }

                    $variant = $cart_item->getVariant();
                    $product = $cart_item->getProduct();

                    $product_variant_id = $variant->data['id'];
                    if ($group_product_mode_on) {
                        $product_variant_id = $product->data['product_id'];
                    }

                    $event_id = $ip_address . '_cart_item_' . $cart_item->data['item_id'];
                    $facebook_events['AddToCart'] = [
                        'data' => [
                            'content_type' => $group_product_mode_on ? 'product_group' : 'product',
                            'content_ids' => strval($product_variant_id),
                            'value' => $cart_item->getFloatPrice(),
                            'content_name' => $cart_item->getProduct()->getProductTitle(),
                            'currency' => 'USD',
                            'country' => $country_code_hash,
                            'ct' => $city,
                            'st' => $state_code
                        ],
                        'event' => [
                            'eventID' => $event_id,
                            'event_name' => 'AddToCart'
                        ]
                    ];

                    $page_tags = array_merge($page_tags, $cart_item->getProduct()->data['tags']);

                    $private_pixel_ids = static::_fetchProductPixelIds($cart_item->getProduct());

                    foreach ($private_pixel_ids as $private_pixel_id) {
                        $pixel_ids[] = $private_pixel_id;
                    }

                    $product_type_pixel_ids = static::_fetchPixelIdsGroupByProductType($variant);
                    foreach ($product_type_pixel_ids as $product_type_pixel_id) {
                        $pixel_ids[] = $product_type_pixel_id;
                    }
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

                    $event_id = $ip_address . '_cart_' . $cart->data['cart_id'];
                    $facebook_events['events']['InitiateCheckout'] = [
                        'data' => [
                            'content_type' => $group_product_mode_on ? 'product_group' : 'product',
                            'content_ids' => [],
                            'value' => $cart->getFloatSubtotal(),
                            'num_items' => 0,
                            'content_name' => 'Initiate Checkout',
                            'currency' => 'USD',
                            'country' => $country_code_hash,
                            'ct' => $city,
                            'st' => $state_code
                        ],
                        'event' => [
                            'eventID' => $event_id,
                            'event_name' => 'InitiateCheckout'
                        ]
                    ];

                    foreach ($cart->getLineItems() as $line_item) {
                        if ($line_item->isCrossSellMode()) {
                            continue;
                        }
                        $variant = $line_item->getVariant();
                        $product = $line_item->getProduct();

                        $page_tags = array_merge($page_tags, $product->data['tags']);
                        $private_pixel_ids = static::_fetchProductPixelIds($product);

                        foreach ($private_pixel_ids as $private_pixel_id) {
                            $pixel_ids[] = $private_pixel_id;
                        }

                        $product_type_pixel_ids = static::_fetchPixelIdsGroupByProductType($variant);
                        foreach ($product_type_pixel_ids as $product_type_pixel_id) {
                            $pixel_ids[] = $product_type_pixel_id;
                        }

                        $product_variant_id = $variant->data['id'];
                        if ($group_product_mode_on) {
                            $product_variant_id = $product->data['product_id'];
                        }

                        $facebook_events['InitiateCheckout']['data']['num_items'] += $line_item->data['quantity'];
                        $facebook_events['InitiateCheckout']['data']['content_ids'][] = strval($product_variant_id);
                    }

                    $facebook_events['InitiateCheckout']['data']['content_ids'] = array_values(array_unique($facebook_events['InitiateCheckout']['data']['content_ids']));
                    break;
                case "catalog/purchase":
                    $order_id = $event_data['order_id'] ?? $event_data;
                    $order = OSC_Database_Model::getPreLoadedModel('catalog/order', $order_id);

                    if (!($order instanceof Model_Catalog_Order)) {
                        continue;
                    }

                    $user_data_email = $order->data['email'];
                    $user_data_phone = $order->data['billing_phone'] ?: $order->data['shipping_phone'];
                    $full_name = $order->data['billing_full_name'] ?: $order->data['shipping_full_name'];
                    $user_data_first_name = self::_getFirstName($full_name);
                    $user_data_last_name = self::_getLastName($full_name);
                    $user_data_zip_code = $order->data['billing_zip'] ?: $order->data['shipping_zip'];

                    $event_id = $ip_address . '_order_' . $order->getId();
                    $facebook_events['Purchase'] = [
                        'data' => [
                            'content_type' => $group_product_mode_on ? 'product_group' : 'product',
                            'content_ids' => [],
                            'value' => $order->getFloatSubtotalPrice(),
                            'num_items' => 0,
                            'content_name' => 'Purchase',
                            'currency' => 'USD',
                            'order_id' => $order->data['order_id'],
                            'country' => $country_code_hash,
                            'ct' => $city,
                            'st' => $state_code
                        ],
                        'event' => [
                            'eventID' => $event_id,
                            'event_name' => 'Purchase'
                        ]
                    ];

                    foreach ($order->getLineItems() as $line_item) {
                        if ($line_item->isCrossSellMode()) {
                            continue;
                        }
                        $variant = $line_item->getVariant();
                        $product = $line_item->getProduct();
                        $page_tags = array_merge($page_tags, $product->data['tags']);
                        if (strpos(OSC::$domain, 'gossby.us') === false) {
                            $private_pixel_ids = static::_fetchProductPixelIds($product);

                            foreach ($private_pixel_ids as $private_pixel_id) {
                                $pixel_ids[] = $private_pixel_id;
                            }
                        }

                        $product_type_pixel_ids = static::_fetchPixelIdsGroupByProductType($variant);
                        foreach ($product_type_pixel_ids as $product_type_pixel_id) {
                            $pixel_ids[] = $product_type_pixel_id;
                        }

                        $product_variant_id = $variant->data['id'];
                        if ($group_product_mode_on) {
                            $product_variant_id = $product->data['product_id'];
                        }

                        $facebook_events['Purchase']['data']['num_items'] += $line_item->data['quantity'];
                        $facebook_events['Purchase']['data']['content_ids'][] = strval($product_variant_id);
                    }

                    $facebook_events['Purchase']['data']['content_ids'] = array_values(array_unique($facebook_events['Purchase']['data']['content_ids']));

                    break;
                default:
                    break;
            }
        }

        $pixel_ids = [];  // (temporary) not get Pixel IDs by Product and GroupByProductType
        $pixel_ids = array_merge($pixel_ids, self::_getPixelIdFromSetting($page_tags));
        $pixel_ids = array_unique($pixel_ids);
        if (count($pixel_ids) < 1 || !$enable_facebook_pixel) {
            return;
        }

        $tracking_init = [];
        $tracking_data = '';
        $tracking_codes = [];

        foreach ($pixel_ids as $pixel_id) {
            $tracking_init[] = "fbq('init', '{$pixel_id}')";
        }

        foreach ($facebook_events as $event_key => $event_data) {
            if ($event_data) {
                $event_data = ',' . OSC::encode($event_data);
            } else {
                $event_data = '';
            }

            if ($event_key !== "PageView" && !empty($event_data)) {
                $tracking_data = "var fbTrackData = " . ltrim($event_data, ',') . ";window.fbTrackDataGlobal = " . ltrim($event_data, ',');
            }
            $tracking_codes[] = "fbq('track', '{$event_key}'{$event_data})";
        }

        $tracking_init = implode(";", $tracking_init);
        $tracking_codes = implode(";", $tracking_codes);

        if ($flag_react) {
            unset($facebook_events['PageView']);
            return [
                'social_chanel' => 'facebook',
                'position' => 'bottom_body',
                'events' => $facebook_events,
                'pixels' => array_values($pixel_ids)
            ];
        }

        OSC::core('template')->push(<<<EOF
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','//connect.facebook.net/en_US/fbevents.js');
{$tracking_init};{$tracking_data};{$tracking_codes};
EOF
            , 'js_separate');
    }

    protected static function _fetchProductPixelIds($product)
    {
        return OSC::helper('catalog/product')->fetchProductPixelIds($product);
    }

    protected static function _fetchPixelIdsGroupByProductType(Model_Catalog_Product_Variant $product_variant)
    {
        try {
            $product_type_variant = $product_variant->getProductTypeVariant(false, true);
            $pixel_ids_group_by_product_type = OSC::helper('facebook/common')->getFacebookPixelGroupByProductType();
            if (isset($pixel_ids_group_by_product_type[$product_type_variant->data['product_type_id']]) &&
                is_array($pixel_ids_group_by_product_type[$product_type_variant->data['product_type_id']])) {
                return $pixel_ids_group_by_product_type[$product_type_variant->data['product_type_id']];
            }
        } catch (Exception $ex) {

        }
        return [];
    }

    public static function validateFacebookPixel($fb_pixel)
    {
        if (preg_match("/^[0-9]{15,16}$/", $fb_pixel)) {
            return true;
        }
    }

    protected static function _getFacebookPixelCookieKey()
    {
        return '_px_ads';
    }

    public static function getFacebookPixelCookie()
    {
        return OSC::cookieGet(self::_getFacebookPixelCookieKey());
    }

    protected static function _getPixelIdFromSetting($page_tags, $type = 'browser')
    {
        $pixel_ids = [];
        $page_tags = array_map(function ($tag) {
            return strtolower(trim($tag));
        }, $page_tags);
        $page_tags = array_filter($page_tags, function ($tag) {
            return $tag !== '';
        });
        $page_tags = array_unique($page_tags);

        if ($type == 'browser') {
            $setting_lines = strtolower(trim(OSC::helper('core/setting')->get('tracking/facebook_pixel/code')));
        } else {
            $setting_lines = strtolower(trim(OSC::helper('core/setting')->get('tracking/facebook_pixel_api/pixel_id')));
        }
        $setting_lines = explode("\n", $setting_lines);

        foreach ($setting_lines as $line) {
            $line = trim($line);

            if (!preg_match('/^(\d+)(\:(.+))?$/', $line, $matches)) {
                continue;
            }

            $matches[3] = explode(',', $matches[3]);
            $matches[3] = array_map(function ($tag) {
                return trim($tag);
            }, $matches[3]);
            $matches[3] = array_filter($matches[3], function ($tag) {
                return $tag !== '';
            });
            $matches[3] = array_unique($matches[3]);

            if (count($matches[3]) < 1 || count(array_intersect($matches[3], $page_tags))) {
                $pixel_ids[] = $matches[1];
            }
        }
        return $pixel_ids;
    }

    protected static function _getFirstName($full_name)
    {
        $full_name_segments = explode(' ', $full_name, 2);
        return $full_name_segments[0];
    }

    protected static function _getLastName($full_name)
    {
        $full_name_segments = explode(' ', $full_name, 2);
        return $full_name_segments[1];
    }

    protected static function _getSourceUrl()
    {
        return (OSC::isSSL() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    protected static function _addQueueExeCuteSetEvent($pixel_ids, $data_events, $page_tags)
    {
        $enable_facebook_pixel_api = intval(OSC::helper('core/setting')->get('tracking/facebook_pixel_api/enable')) === 1;
        $access_token = OSC::helper('core/setting')->get('tracking/facebook_pixel_api/access_token');

        if (!$enable_facebook_pixel_api || !$access_token) {
            return;
        }

        $pixel_id_from_setting = self::_getPixelIdFromSetting($page_tags, 'api');

        $pixel_ids = array_merge($pixel_ids, $pixel_id_from_setting);
        $pixel_ids = array_unique($pixel_ids);

        if (count($pixel_ids) < 1) {
            return;
        }

        $DB = OSC::core('database');
        try {
            $data = [
                'pixel_ids' => OSC::encode($pixel_ids),
                'data_events' => OSC::encode($data_events),
                'queue_flag' => Cron_Facebook_ExecuteSetEvent::FLAG_QUEUE,
                'error_message' => '',
                'added_timestamp' => time(),
            ];
            $DB->insert('facebook_api_queue', $data, 'insert_facebook_api_queue');
        } catch (Exception $ex) {
            OSC::logFile('[API] Facebook error: ' . $ex->getMessage(), 'facebook_' . date('Y_m_d'));
        }
    }
}