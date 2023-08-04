<?php

class Helper_Report_AdTracking extends OSC_Object {
    protected $_redis = null;
    protected $_queue_redis_ad_tracking = OSC_SITE_KEY . '_queue_redis_ad_tracking';
    protected $_limit_ad_tracking = 10000; // IT-8713
    protected $_collection_ads_tracking_add_to_cart = 'ads_tracking_add_to_cart';
    protected $_collection_ads_tracking_edit_cart = 'ads_tracking_edit_cart';
    protected $_collection_ads_tracking_checkout_initialize = 'ads_tracking_checkout_initialize';
    protected $_collection_ads_tracking_product_view = 'ads_tracking_product_view';
    protected $_collection_ads_tracking_purchase = 'ads_tracking_purchase';

    public function __construct() {
        if ($this->_redis === null) {
            try {
                $cache_config = OSC::systemRegistry('cache_config');
                $redis_config = isset($cache_config['instance']['redis']) ? $cache_config['instance']['redis'] : [];

                if (!is_array($redis_config) || empty($redis_config)) {
                    throw new Exception('Cannot get redis config');
                }

                $this->_redis = new Redis();
                $this->_redis->connect($redis_config['host'], $redis_config['port']);
            } catch (Exception $exception) {
                throw $exception;
            }
        }
    }

    public function getSref()
    {
        $sref_id = 0;
        $sref = OSC::registry('DLS-SALE-REF');

        if (is_array($sref)) {
            $sref_id = intval($sref['id']);
        }
        return $sref_id;
    }

    protected function _getAdTrackingData() {
        $sref_id = $this->getSref();
        if ($sref_id == 0) {
            return [];
        }

        $track_model = OSC::model('frontend/tracking');

        $cookie_key = Controller_Report_Frontend::getTrackingCookieKey();

        $track_key = OSC::cookieGet($cookie_key);

        if ($track_key) {
            try {
                $track_model->loadByUKey($track_key);
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    return [];
                }
            }
        }

        if ($track_model->getId() < 1) {
            OSC::helper('report/common')->setReferer($_SERVER['HTTP_REFERER']);

            $track_model->setData('added_timestamp', 0)->save()->register('IS_NEW_RECORD', 1);
            OSC::cookieSetCrossSite($cookie_key, $track_model->getUkey());
        }

        $track_ukey = $track_model->getUkey();

        $session_id = OSC::cookieGet('ad_session_id');

        if (!$session_id) {
            $session_id = OSC::makeUniqid();
            OSC::cookieSetCrossSite('ad_session_id', $session_id);
        }

        $data = [
            'track_ukey' => $track_ukey,
            'sref_id' => $sref_id,
            'session_id' => $session_id,
            'added_timestamp' => time()
        ];

        $tracking_keys = ['campaign_id', 'adset_id', 'ad_id', 'ad_name', 'utm_campaign', 'adset_name'/*, 'utm_source', 'utm_medium', 'utm_content' */];

        foreach ($tracking_keys as $tracking_key) {
            $data[$tracking_key] = OSC::cookieGet($tracking_key);

            if ($data[$tracking_key]) {
                $data[$tracking_key] = trim(OSC::safeString(urldecode($data[$tracking_key])));
            } else {
                $data[$tracking_key] = '';
            }
        }

        if (empty($data['campaign_id']) || empty($data['adset_id']) || empty($data['ad_id'])) {
            return [];
        }

        return $data;
    }

    public function trackProductView($product_id) {
        $data = $this->_getAdTrackingData();
        if (empty($data)) {
            return;
        }

        $data['product_id'] = $product_id;

        try {
            $this->_redis->rPush($this->_queue_redis_ad_tracking, OSC::encode([
                'collection' => $this->_collection_ads_tracking_product_view,
                'key' => md5(OSC::encode([
                    'campaign_id' => $data['campaign_id'],
                    'adset_id' => $data['adset_id'],
                    'ad_id' => $data['ad_id'],
                    'sref_id' => $data['sref_id'],
                    'track_ukey' => $data['track_ukey'],
                    'session_id' => $data['session_id'],
                    'product_id' => $data['product_id']
                ])),
                'data' => $data
            ]));
        } catch (Exception $exception) {
            OSC::helper('core/common')->writeLog('rPush ads_tracking_product_view exception: ' . $exception->getMessage());
        }
    }

    public function trackAddToCart($product_id, $quantity) {
        $data = $this->_getAdTrackingData();

        if (empty($data)) {
            return;
        }

        $data['product_id'] = $product_id;
        $data['quantity'] = $quantity;
        try {
            $this->_redis->rPush($this->_queue_redis_ad_tracking, OSC::encode([
                'collection' => $this->_collection_ads_tracking_add_to_cart,
                'key' => md5(OSC::encode([
                    'campaign_id' => $data['campaign_id'],
                    'adset_id' => $data['adset_id'],
                    'ad_id' => $data['ad_id'],
                    'sref_id' => $data['sref_id'],
                    'track_ukey' => $data['track_ukey'],
                    'session_id' => $data['session_id'],
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity']
                ])),
                'data' => $data
            ]));
        } catch (Exception $exception) {
            OSC::helper('core/common')->writeLog('rPush ads_tracking_add_to_cart exception: ' . $exception->getMessage());
        }
    }

    public function trackEditCartItem($product_id, $quantity) {
        $data = $this->_getAdTrackingData();
        if (empty($data)) {
            return;
        }

        $data['product_id'] = $product_id;
        $data['quantity'] = $quantity;
        try {
            $this->_redis->rPush($this->_queue_redis_ad_tracking, OSC::encode([
                'collection' => $this->_collection_ads_tracking_edit_cart,
                'key' => md5(OSC::encode([
                    'campaign_id' => $data['campaign_id'],
                    'adset_id' => $data['adset_id'],
                    'ad_id' => $data['ad_id'],
                    'sref_id' => $data['sref_id'],
                    'track_ukey' => $data['track_ukey'],
                    'session_id' => $data['session_id'],
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity']
                ])),
                'data' => $data
            ]));
        } catch (Exception $exception) {
            OSC::helper('core/common')->writeLog('rPush ads_tracking_edit_cart exception: ' . $exception->getMessage());
        }
    }

    protected function _updateEditCartItems(array $collection) {
        $mongodb = OSC::core('mongodb');
        if (count($collection) > 0) {
            foreach ($collection as $item) {
                $updated_quantity = $item['quantity'];
                $filter = [
                    'campaign_id' => $item['campaign_id'],
                    'adset_id' => $item['adset_id'],
                    'ad_id' => $item['ad_id'],
                    'sref_id' => $item['sref_id'],
                    'track_ukey' => $item['track_ukey'],
                    'session_id' => $item['session_id'],
                    'product_id' => $item['product_id']
                ];

                if($updated_quantity > 0) {
                    $mongodb->selectCollection('ads_tracking_add_to_cart', 'report')->updateOne($filter,[
                        '$set' => ['quantity' => $updated_quantity ],
                    ]);
                } else {
                    $mongodb->delete('ads_tracking_add_to_cart',$filter,[], 'report');
                }
            }
        }
    }

    public function trackCheckoutInitialize($cart_id) {
        $data = $this->_getAdTrackingData();

        if (empty($data)) {
            return;
        }

        $data['cart_id'] = $cart_id;

        try {
            $this->_redis->rPush($this->_queue_redis_ad_tracking, OSC::encode([
                'collection' => $this->_collection_ads_tracking_checkout_initialize,
                'key' => md5(OSC::encode([
                    'campaign_id' => $data['campaign_id'],
                    'adset_id' => $data['adset_id'],
                    'ad_id' => $data['ad_id'],
                    'sref_id' => $data['sref_id'],
                    'track_ukey' => $data['track_ukey'],
                    'session_id' => $data['session_id'],
                    'cart_id' => $cart_id
                ])),
                'data' => $data
            ]));
        } catch (Exception $exception) {
            OSC::helper('core/common')->writeLog('rPush ads_tracking_checkout_initialize exception: ' . $exception->getMessage());
        }
    }

    public function trackPurchase($event_data = []) {
        $order_id = $event_data['order_id'] ?? '';
        $data = $this->_getAdTrackingData();
        $sref_id = $this->getSref();
        if (empty($data)) {
            return;
        }

        $data['order_id'] = $order_id;
        $data['total_price'] = intval($event_data['total_price']) ?? '';
        $data['subtotal_price'] = intval($event_data['subtotal_price']) ?? '';
        $data['quantity'] = intval($event_data['quantity']) ?? 0;

        try {
            $this->_redis->rPush($this->_queue_redis_ad_tracking, OSC::encode([
                'collection' => $this->_collection_ads_tracking_purchase,
                'key' => md5(OSC::encode([
                    'order_id' => $order_id
                ])),
                'data' => $data
            ]));
        } catch (Exception $exception) {
            OSC::helper('core/common')->writeLog('rPush ads_tracking_purchase exception: ' . $exception->getMessage());
        }

        //update ad info to catalog order
        $utm_params = ['campaign_id', 'adset_id', 'ad_id', 'ad_name', 'adset_name', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content'];
        $ad_info = [];
        foreach ($utm_params as $param) {
            $ad_info[$param] = OSC::cookieGet($param);

            if ($ad_info[$param]) {
                $ad_info[$param] = trim(OSC::safeString(urldecode($ad_info[$param])));
            } else {
                $ad_info[$param] = '';
            }
        }
        $ad_info['order_id'] = $order_id;
        $ad_info['sref_id'] = $data['sref_id'];
        try {
            OSC::model('catalog/order_adInfo')->setData($ad_info)->save();
        } catch (Exception $ex) {
            //throw $th;
            OSC::helper('core/common')->writeLog('order_adInfo exception with $sref_id: ' . $sref_id . ' ,$order: ' . $order_id . ' $ad_info: ' . OSC::encode($ad_info) . ' === ' . $ex->getMessage());
        }
    }

    public function insertAdTracking() {
        try {
            $list_ad_tracking = $this->_redis->multi()
                ->lRange($this->_queue_redis_ad_tracking, 0, $this->_limit_ad_tracking)
                ->lTrim($this->_queue_redis_ad_tracking, $this->_limit_ad_tracking, -1)
                ->exec();

            $list_ads_add_to_cart = [];
            $list_ads_edit_cart = [];
            $list_ads_tracking_checkout_initialize = [];
            $list_ads_tracking_product_view = [];
            $list_ads_tracking_purchase = [];

            if (!empty($list_ad_tracking[0])) {
                foreach ($list_ad_tracking[0] as $ad_tracking) {
                    try {
                        $item = OSC::decode($ad_tracking);
                        if (isset($item['collection']) && isset($item['key'])) {
                            switch ($item['collection']) {
                                case $this->_collection_ads_tracking_product_view:
                                    $list_ads_tracking_product_view[$item['key']] = $item['data'];
                                    break;
                                case $this->_collection_ads_tracking_checkout_initialize:
                                    $list_ads_tracking_checkout_initialize[$item['key']] = $item['data'];
                                    break;
                                case $this->_collection_ads_tracking_add_to_cart:
                                    $list_ads_add_to_cart[$item['key']] = $item['data'];
                                    break;
                                case $this->_collection_ads_tracking_edit_cart:
                                    $list_ads_edit_cart[$item['key']] = $item['data'];
                                    break;
                                case $this->_collection_ads_tracking_purchase:
                                    $list_ads_tracking_purchase[$item['key']] = $item['data'];
                                    break;
                                default:
                                    break;
                            }
                        }
                    } catch (Exception $exception) { }
                }

                $mongodb = OSC::core('mongodb');
                if (!empty($list_ads_tracking_product_view)) {
                    $documents = array_values($list_ads_tracking_product_view);

                    $success_documents = [];
                    foreach ($documents as $document) {
                        try {
                            $mongodb->insert($this->_collection_ads_tracking_product_view, $document, 'report');
                            $success_documents[] = $document;
                        } catch (Exception $exception) { }
                    }

                    $this->_updateAdTrackingAnalytic('productView', $success_documents);
                }

                if (!empty($list_ads_add_to_cart)) {
                    $documents = array_values($list_ads_add_to_cart);

                    $success_documents = [];
                    foreach ($documents as $document) {
                        try {
                            $mongodb->insert($this->_collection_ads_tracking_add_to_cart, $document, 'report', ['$inc' => ['quantity' => $document['quantity']]]);
                            $success_documents[] = $document;
                        } catch (Exception $exception) { }
                    }

                    $this->_updateAdTrackingAnalytic('addToCart', $success_documents);
                }

                if (!empty($list_ads_edit_cart)) {
                    try {
                        $documents = array_values($list_ads_edit_cart);
                        $this->_updateEditCartItems($documents);
                        $this->_updateAdTrackingAnalytic('editCart', $documents);
                    } catch (Exception $exception) { }
                }

                if (!empty($list_ads_tracking_checkout_initialize)) {
                    $documents = array_values($list_ads_tracking_checkout_initialize);

                    $success_documents = [];
                    foreach ($documents as $document) {
                        try {
                            $mongodb->insert($this->_collection_ads_tracking_checkout_initialize, $document, 'report');
                            $success_documents[] = $document;
                        } catch (Exception $exception) { }
                    }

                    $this->_updateAdTrackingAnalytic('checkout', $success_documents);
                }

                if (!empty($list_ads_tracking_purchase)) {
                    $documents = array_values($list_ads_tracking_purchase);

                    $success_documents = [];
                    foreach ($documents as $document) {
                        try {
                            $mongodb->insert($this->_collection_ads_tracking_purchase, $document, 'report');
                            $success_documents[] = $document;
                        } catch (Exception $exception) { }
                    }

                    $this->_updateAdTrackingAnalytic('purchase', $success_documents);
                }
            }
        } catch (Exception $exception) {
            OSC::helper('core/common')->writeLog('insertAdTracking exception: ' . $exception->getMessage());
        }
    }

    protected function _updateAdTrackingAnalytic($type, $documents)
    {
        if (count($documents) > 0) {
            $mongodb = OSC::core('mongodb');

            foreach ($documents as $data) {
                try {
                    $update = [];
                    $additional_query = [];

                    switch ($type) {
                        case 'productView':
                            $update = ['$inc' => ['product_view' => 1]];
                            break;
                        case 'addToCart':
                            $update = ['$inc' => [
                                'add_to_cart' => 1
                            ]];
                            break;
                        case 'editCart':
                            if ($data['quantity'] === 0) {
                                $update['$inc']['add_to_cart'] = -1;
                            }
                            break;
                        case 'checkout':
                            $update = ['$inc' => ['checkout_initialize' => 1]];
                            break;
                        case 'purchase':
                            $update = [
                                '$inc' => [
                                    'purchase' => 1,
                                    'revenue' => $data['total_price'],
                                    'subtotal_revenue' =>  $data['subtotal_price'],
                                    'quantity' => $data['quantity']
                                ]
                            ];
                            break;
                    }

                    // Update the last name of campaign was recorded to show in analytics table
                    $update['$set'] = [];
                    foreach (['utm_campaign', 'ad_name', 'adset_name'] as $item) {
                        if (!empty($data[$item])) {
                            $update['$set'][$item] = $data[$item];
                        }
                    }

                    if (empty($update['$set'])) {
                        unset($update['$set']);
                    }

                    $beginOfDay = strtotime("today", time());

                    $match = array_merge([
                        'campaign_id' => $data['campaign_id'],
                        'adset_id' => $data['adset_id'],
                        'ad_id' => $data['ad_id'],
                        'sref_id' => $data['sref_id'],
                        'added_timestamp' => $beginOfDay
                    ], $additional_query);

                    $setOnInsert = array_merge($match, [
                        'ad_name' => $data['ad_name'],
                        'utm_campaign' => $data['utm_campaign'],
                        'adset_name' => $data['adset_name'],
                        'product_view' => 0,
                        'add_to_cart' => 0,
                        'checkout_initialize' => 0,
                        'purchase' => 0,
                        'subtotal_revenue' => 0,
                        'revenue' => 0,
                        'quantity' => 0
                    ]);

                    if ($update['$inc']) {
                        foreach ($update['$inc'] as $key => $v) {
                            if (isset($setOnInsert[$key])) {
                                unset($setOnInsert[$key]);
                            }
                        }
                    }

                    if ($update['$set']) {
                        foreach ($update['$set'] as $key => $v) {
                            if (isset($setOnInsert[$key])) {
                                unset($setOnInsert[$key]);
                            }
                        }
                    }

                    $mongodb->selectCollection('ads_tracking_analytic', 'report')->updateOne(
                        $match,
                        array_merge([
                            '$setOnInsert' => $setOnInsert
                        ], $update),
                        ['upsert' => true]
                    );
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage(), $ex->getCode());
                }
            }
        }
    }
}