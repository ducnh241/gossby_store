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
class Observer_Catalog_Common {

    public static function collectABTestData() {
        $ab_test_keys = [];

        if (is_array(OSC::AB_TEST)) {
            foreach (OSC::AB_TEST as $key => $config) {
                if ($config['enable'] && is_array($config['values'])) {
                    $ab_test_keys[$key] = $config['values'];
                }
            }
        }

        return $ab_test_keys;
    }

    public static function afterPlaceOrder(Model_Catalog_Order $order) {
        //Add to marketing point analytic
        try {
            if (!empty($order->data['sref_id'])) {
                foreach ($order->getLineItems() as $line_item) {
                    if ($line_item->isCrossSellMode()) {
                        continue;
                    }
                    try {
                        $point_data = OSC::helper('marketing/common')->calculatePoint($line_item, true);

                        if (!empty($point_data['marketing_point']['sref']) || !empty($point_data['marketing_point']['vendor'])) {
                            $marketing_point_model = OSC::model('marketing/point');
                            $marketing_point_model->setData([
                                'order_id' => $order->getId(),
                                'order_line_item_id' => $line_item->getId(),
                                'product_id' => $line_item->data['product_id'],
                                'variant_id' => $line_item->data['variant_id'],
                                'member_id' => $order->data['sref_id'],
                                'point' => $point_data['marketing_point']['sref'] ?? 0,
                                'vendor' => $line_item->data['vendor'],
                                'vendor_point' => $point_data['marketing_point']['vendor'] ?? 0,
                                'meta_data' => [
                                    'day_after_product_created' => $point_data['day_after_product_created'] ?? 0,
                                    'quantity' => $point_data['quantity'],
                                    'quantity_of_pack' => $point_data['quantity_of_pack'],
                                    'point_config' => $point_data['point_config'],
                                    'point_setting' => $point_data['point_setting']

                                ]
                            ])->save();
                        }
                    } catch (Exception $exception) {
                    }
                }
            }
        } catch (Exception $ex) {

        }
    }

    public static function productCreated(Model_Catalog_Product $product) {
        OSC::helper('catalog/search_product')->addProduct($product);

        OSC::helper('catalog/common')->reloadFileFeedFlag();
    }

    public static function productUpdated(Model_Catalog_Product $product) {
        OSC::helper('catalog/search_product')->addProduct($product);

        OSC::helper('catalog/common')->reloadFileFeedFlag();
    }

    public static function productDeleted(Model_Catalog_Product $product) {
        try {
            OSC::helper('catalog/search_product')->deleteProduct($product);
        } catch (Exception $ex) {

        }

        OSC::helper('catalog/common')->reloadFileFeedFlag();

        try {
            $report_model = OSC::model('report/report');
            $report_model->getWriteAdapter()->delete($report_model->getTableName(), "report_key LIKE '%catalog/item/{$product->getId()}/%'", null, 'delete_catalog_item_report');
        } catch (Exception $ex) {

        }
    }

    public static function productVariantCreated(Model_Catalog_Product_Variant $variant) {
        OSC::helper('catalog/common')->reloadFileFeedFlag();
    }

    public static function productVariantUpdated(Model_Catalog_Product_Variant $variant) {
        OSC::helper('catalog/common')->reloadFileFeedFlag();
    }

    public static function productVariantDeleted(Model_Catalog_Product_Variant $variant) {
        OSC::helper('catalog/common')->reloadFileFeedFlag();
    }

    public static function parseFullUrl($request_string) {
        $langKey = OSC::core('language')->getCurrentLanguageKey();
        $url = $request_string;

        if (preg_match('/^' .$langKey. '\/catalog\/frontend\/index\/id\/(\d+)\/page\/(\d+)(\/)?$/i', $request_string, $matches)) {
            try {
                $model = OSC::core('controller_alias_model')->loadByUkey('collection/' . $matches[1]);
                $slug = 'collection/' . $model->data['slug'];
                $url = $slug . (($matches[2] && $matches[2] != 1) ? ('/page/' . $matches[2]) : '');
            } catch (Exception $ex) {

            }

            return $url;
        } else if (preg_match('/^catalog\/collection\/(\d+)\/([a-zA-Z0-9-_]+)(\/)?$/i', $request_string, $matches)) {
            try {
                $model = OSC::core('controller_alias_model')->loadByUkey('collection/' . $matches[1]);
                $url = 'collection/' . $model->data['slug'];
            } catch (Exception $ex) {

            }

            return $url;
        }
    }

    public static function collectClientInfo($params) {
        $params['client_info']['DLS_SALE_REF'] = OSC::registry('DLS-SALE-REF');
    }

    public static function setSrefCookie($member) {
        if(! ($member instanceof Model_User_Member)) {
            return;
        }

        if (OSC::registry('flag_sref_source') == 1) {
            return;
        }

        $sale_ref_id = $member->getId();
        $sale_ref_seckey = OSC::makeUniqid();

        $checksum = static::_getSaleRefCookieChecksum($member, $sale_ref_seckey);

        OSC::cookieSetCrossSite(static::_getSaleRefCookieIDKey(), $sale_ref_id);
        OSC::cookieSetCrossSite(static::_getSaleRefCookieExpTime(), time() + (60 * 60 * 24 * 30));
        OSC::cookieSetCrossSite(static::_getSaleRefCookieSecKey(), $sale_ref_seckey);
        OSC::cookieSetCrossSite($checksum['key'], $checksum['value']);

        try {
            $cart = OSC::helper('catalog/common')->getCart(false);

            if ($cart) {
                $client_info = $cart->data['client_info'];

                if (!is_array($client_info)) {
                    $client_info = [];
                }

                $client_info['DLS_SALE_REF'] = ['id' => $member->getId(), 'username' => $member->data['username']];

                if ($client_info != $cart->data['client_info']) {
                    $cart->setData('client_info', $client_info)->save();
                }

            }
        } catch (Exception $ex) {

        }

        static::registerSref($member);
    }

    public static function registerSref($member) {
        OSC::register(
            'DLS-SALE-REF',
            [
                'id' => $member->getId(),
                'username' => $member->data['username'],
                'sref_type' => $member->data['sref_type'] ? (isset($member->getSrefTypes()[$member->data['sref_type']]) ? $member->data['sref_type'] : $member->getSrefTypeDefault()['key']) : $member->getSrefTypeDefault()['key']
            ]
        );
    }

    protected function _verifySrefParam($sref) {
        if (is_numeric($sref)) {
            return $sref;
        } elseif (is_string($sref)) {
            $sref = intval($sref);
        } elseif (is_array($sref)) {
            $sref = intval(end($sref));
        }

        return $sref;
    }

    public static function checkSaleReference() {
        if(OSC::isCrawlerRequest()) {
            return;
        }

        $member = null;

        $cache_key_sref_ip = static::_getCacheKeySref();

        if (isset($_REQUEST['sref']) || isset($_REQUEST['adref'])) {
            $sref_ids = [];
            if (isset($_REQUEST['adref'])) {
                array_push($sref_ids, $_REQUEST['adref']) ;
            }

            if (isset($_REQUEST['sref'])) {
                array_push($sref_ids, $_REQUEST['sref']) ;
            }
            static::setCookieEnableSrefSoure($_SERVER);

            static::setCookieSrefSource($_REQUEST['sref'], $_REQUEST['adref']);

            $sref_ids = array_unique($sref_ids);

            foreach ($sref_ids as $sref_id) {
                $sale_ref_id = self::_verifySrefParam($sref_id);
                if ($sale_ref_id > 0) {
                    try {
                        $member = OSC::model('user/member')->load($sale_ref_id);
                        static::setSrefCookie($member);

                        if (!empty($cache_key_sref_ip)) {
                            OSC::core('cache')->set($cache_key_sref_ip, OSC::encode(['member_id' => $member->getId()]), OSC_CACHE_TIME);
                        }

                    } catch (Exception $ex) { }

                    break;
                }
            }

        } else {
            if (!empty($cache_key_sref_ip)) {
                try {
                    $cache_value_sref_ip = OSC::core('cache')->get($cache_key_sref_ip);
                    $data_cache = OSC::decode($cache_value_sref_ip);
                    if (!empty($cache_value_sref_ip) && isset($data_cache['member_id'])) {
                        $member = OSC::model('user/member')->load($data_cache['member_id']);
                        static::setSrefCookie($member);

                        OSC::core('cache')->set($cache_key_sref_ip, OSC::encode(['member_id' => $member->getId()]), OSC_CACHE_TIME);
                    }
                } catch (Exception $ex) { }
            }
        }

        if (!$member) {
            $sale_ref_id = intval(OSC::cookieGet(static::_getSaleRefCookieIDKey()));
            $sale_ref_seckey = OSC::cookieGet(static::_getSaleRefCookieSecKey());
            $sale_ref_exptime = intval(OSC::cookieGet(static::_getSaleRefCookieExpTime()));

            if ($sale_ref_id > 0 && $sale_ref_seckey && $sale_ref_exptime >= time()) {
                try {
                    $member = OSC::model('user/member')->load($sale_ref_id);

                    $checksum = static::_getSaleRefCookieChecksum($member, $sale_ref_seckey);

                    if (OSC::cookieGet($checksum['key']) !== $checksum['value']) {
                        OSC::cookieRemoveCrossSite($checksum['key']);
                        throw new Exception('', 404);
                    }

                    static::registerSref($member);
                } catch (Exception $ex) {
                    if ($ex->getCode() == 404) {
                        OSC::cookieRemoveCrossSite(static::_getSaleRefCookieIDKey());
                        OSC::cookieRemoveCrossSite(static::_getSaleRefCookieSecKey());
                        OSC::cookieRemoveCrossSite(static::_getSaleRefCookieExpTime());
                    }
                }
            }
        }

        try {
            $tracking_key = Abstract_Frontend_Controller::getTrackingKey();

            if ($tracking_key) {
                $cart = OSC::helper('catalog/common')->getCart(false);

                if ($cart) {
                    $client_info = $cart->data['client_info'];

                    if (!is_array($client_info)) {
                        $client_info = [];
                    }

                    if (!isset($client_info['tracking_key'])) {
                        $client_info['tracking_key'] = [];
                    } else if (!is_array($client_info['tracking_key'])) {
                        $client_info['tracking_key'] = [$client_info['tracking_key']];
                    }

                    $client_info['tracking_key'][] = $tracking_key;
                    $client_info['tracking_key'] = array_map(function($key) {
                        return trim($key);
                    }, $client_info['tracking_key']);
                    $client_info['tracking_key'] = array_filter($client_info['tracking_key'], function($key) {
                        return $key != '';
                    });
                    $client_info['tracking_key'] = array_unique($client_info['tracking_key']);
                    $client_info['tracking_key'] = array_values($client_info['tracking_key']);

                    if (count($client_info['tracking_key']) == 0) {
                        $client_info['tracking_key'] = '';
                    } else if (count($client_info['tracking_key']) == 1) {
                        $client_info['tracking_key'] = $client_info['tracking_key'][0];
                    }

                    if ($client_info['tracking_key'] != $cart->data['client_info']['tracking_key']) {
                        $cart->setData('client_info', $client_info)->save();
                    }
                }
            }
        } catch (Exception $ex) {

        }
    }

    protected static function _getOrderSaleRefId(Model_Catalog_Order $order) {
        if (isset($order->data['client_info']['DLS_SALE_REF']) && is_array($order->data['client_info']['DLS_SALE_REF']) && isset($order->data['client_info']['DLS_SALE_REF']['id'])) {
            $sale_ref_id = intval($order->data['client_info']['DLS_SALE_REF']['id']);

            if ($sale_ref_id > 0) {
                return $sale_ref_id;
            }
        }

        return 0;
    }

    protected static function _getSaleRefCookieIDKey() {
        return 'sref-id';
    }

    protected static function _getSaleRefCookieSecKey() {
        return 'sref-seckey';
    }

    protected static function _getSaleRefCookieExpTime() {
        return 'sref-exptime';
    }

    protected static function _getSaleRefCookieChecksum(Model_User_Member $member, string $seckey): array {
        return [
            'key' => hash_hmac('sha256', $member->data['email'], $member->data['password_hash']),
            'value' => hash_hmac('sha256', $member->data['added_timestamp'], $member->data['password_hash'])
        ];
    }

    public function updateCollectionsProductsRel($params)
    {
        $class = get_class($params['model']);
        switch ($class) {
            case Model_Catalog_Product::class:
                $product = $params['model'];
                OSC::core('cron')->addQueue(
                    'catalog/insertCollectionProductRel',
                    [
                        'type' => 'product',
                        'product_id' => $product->getId()
                    ],
                    ['ukey' => 'catalog/insertCollectionProductRel_product_'.$product->getId() , 'requeue_limit' => 0, 'skip_realtime', 'estimate_time' => 30 * 60]
                );
                break;
            case Model_Catalog_Collection::class:
                $collection = $params['model'];
                OSC::core('cron')->addQueue(
                    'catalog/insertCollectionProductRel',
                    [
                        'type' => 'collection',
                        'collection_id' => $collection->getId()
                    ],
                    ['ukey' => 'catalog/insertCollectionProductRel_collection_'.$collection->getId() , 'requeue_limit' => 0, 'skip_realtime', 'estimate_time' => 30 * 60]
                );

                break;
            default:
        }
    }

    /**
     * Insert when order place, profit = 0, status = authorized
     * @param Model_Catalog_Order $order
     * @throws Exception
     */
    public function insertProfit(Model_Catalog_Order $order) {
        try {
            $shop = OSC::model('shop/shop')->load($order->data['shop_id']);
            OSC::model('shop/profit')->setData([
                'shop_id' => $order->data['shop_id'],
                'order_id' => $order->getId(),
                'order_payment_status' => $order->data['payment_status'],
                'currency_code' => $order->data['currency_code'],
                'amount' => 0,
                'current_tier' => $shop->data['tier'],
                'action' => $order->data['payment_status']
            ])->save();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected static function _getCacheKeySref()
    {
        if (!empty(OSC::getClientIP())) {
            return 'sref_' . OSC_SITE_KEY . '_' . OSC::getClientIP();
        }
        return null;
    }

    public static function setCookieEnableSrefSoure($server) {
        // start handle sref source dest
        $referer_url = $server['HTTP_REFERER'] ? $server['HTTP_REFERER'] : '';

        $referer_url = strtolower(trim(strval($referer_url)));

        if ($referer_url) {
            $referer_info = parse_url($referer_url);

            if ($referer_info['host'] && $referer_info['host'] != OSC::$domain) {
                OSC::cookieSetCrossSite('sref_source_dest_used', 0);
            }
        }
    }

    public static function setCookieSrefSource($sref, $adref) {
        $sref = intval($sref);
        $adref = intval($adref);

        if ($sref < 1 && $adref < 1) {
            return;
        }

        $priority_sref = $adref > 0 ? $adref : $sref;

        OSC::cookieSetCrossSite('sref_source_id', $priority_sref);
    }

    public static function handleSrefSourceDestByProduct(Model_Catalog_Product $product) {
        $dest_sref_id = 0;
        try {
            if ($product->getId() < 1) {
                throw new Exception('Product is not found');
            }

            $sref_source_id = OSC::cookieGet('sref_source_id');
            $sref_source_dest_used = OSC::cookieGet('sref_source_dest_used');

            if ($sref_source_id < 1 || intval($sref_source_dest_used) == 1) {
                throw new Exception('Cannot use sref source dest');
            }

            if (isset($product->data['meta_data']['sref']['sref_source']) &&
                $product->data['meta_data']['sref']['sref_source'] > 0 &&
                isset($product->data['meta_data']['sref']['sref_dest']) &&
                $product->data['meta_data']['sref']['sref_dest'] > 0 &&
                $product->data['meta_data']['sref']['sref_source'] == $sref_source_id &&
                $product->data['meta_data']['sref']['sref_dest'] != $sref_source_id
            ) {
                $dest_sref_id = $product->data['meta_data']['sref']['sref_dest'];
            }

            if ($dest_sref_id < 1) {
                throw new Exception('Product not have sref source dest');
            }

            $member = OSC::model('user/member')->load($dest_sref_id);

            static::setSrefCookie($member);

            OSC::cookieSetCrossSite('sref_source_dest_used', 1);
            OSC::cookieRemoveCrossSite('sref_source_id');
            OSC::cookieSetCrossSite('adref-id', $dest_sref_id);

            OSC::register('flag_sref_source', 1);

            $cache_key_sref_ip = static::_getCacheKeySref();

            OSC::core('cache')->set($cache_key_sref_ip, OSC::encode(['member_id' => $member->getId()]), OSC_CACHE_TIME);
        } catch (Exception $ex) { }


        return $dest_sref_id;

    }
}
