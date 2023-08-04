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
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSC Api Abstract Controller
 *
 * @package Abstract_Api_Controller
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
abstract class Abstract_Frontend_ReactApiController extends Abstract_Core_Controller {
    /*
     *  200 OK – Trả về thành công cho những phương thức GET, PUT, PATCH hoặc DELETE.
        201 Created – Trả về khi một Resouce vừa được tạo thành công.
        204 No Content – Trả về khi Resource xoá thành công.
        304 Not Modified – Client có thể sử dụng dữ liệu cache.
        400 Bad Request – Request không hợp lệ
        401 Unauthorized – Request cần có auth.
        403 Forbidden – bị từ chối không cho phép.
        404 Not Found – Không tìm thấy resource từ URI
        405 Method Not Allowed – Phương thức không cho phép với user hiện tại.
        410 Gone – Resource không còn tồn tại, Version cũ đã không còn hỗ trợ.
        411 Length Required – Resource không đủ độ dài.
        415 Unsupported Media Type – Không hỗ trợ kiểu Resource này.
        422 Unprocessable Entity – Dữ liệu không được xác thực
        429 Too Many Requests – Request bị từ chối do bị giới hạn
     */
    const CODE_OK = 200;
    const CODE_CREATED = 201;
    const CODE_NO_CONTENT = 204;
    const CODE_MOVED_PERMANENTLY = 301;
    const CODE_NOT_MODIFIED = 404;
    const CODE_BAD_REQUEST = 400;
    const CODE_UNAUTHORIZED = 401;
    const CODE_FORBIDDEN = 403;
    const CODE_NOT_FOUND = 404;
    const CODE_METHOD_NOT_ALLOWED = 405;
    const CODE_GONE = 410;
    const CODE_LENGTH_REQUIRED = 411;
    const CODE_UNSUPPORTED_MEDIA_TYPE = 415;
    const CODE_UNPROCESSABLE_ENTITY = 422;
    const CODE_TOO_MANY_REQUEST = 429;

    //Product
    const CODE_COLLECTION_PRODUCT_MISSING_PARAM = 3001;
    const CODE_LOAD_COLLECTION_ERROR = 3002;
    const CODE_LOAD_GET_LIST_PRODUCT_BY_COLLECTION_ERROR = 3003;
    const CODE_PRODUCT_MISSING_PARAM = 3002;
    const CODE_LOAD_GET_LIST_PRODUCT_BY_SEARCH_KEYWORDS_ERROR  = 3004;
    const CODE_LOAD_GET_LIST_PRODUCT_BESTSELLING_ERROR  = 3005;
    const CODE_LOAD_GET_LIST_PRODUCT_RECENTVIEW_ERROR  = 3005;
    const CODE_LOAD_GET_ALL_PRODUCT_ERROR  = 3006;
    const CODE_LOAD_GET_PRODUCT_BY_SEO_TAGS_ERROR  = 3007;

    //Cart
    const CODE_LOAD_CART_ERROR = 4001;
    const CODE_ADD_TO_CART_ERROR = 4002;
    const CODE_UPDATE_CART_MISSING_PARAM = 4003;
    const CODE_UPDATE_CART_ERROR = 4004;
    const CODE_APPLY_DISCOUNT_CODE_ERROR = 4005;
    const CODE_REMOVE_DISCOUNT_CODE_ERROR = 4006;
    const CODE_LOAD_GET_LIST_PRODUCT_RELATED_ERROR  = 4007;
    const CODE_APPLY_DISCOUNT_CODE_BUT_NOT_FOUND = 4008;
    const CODE_APPLY_DISCOUNT_CODE_NO_INFO = 415;

    //Order
    const CODE_LOAD_ORDER_ERROR = 8001;
    const CODE_EDIT_ORDER_MISSING_PARAM = 8002;
    const CODE_EDIT_ORDER_API_ERROR = 8003;
    const CODE_EDIT_ORDER_NOT_ABLE = 8004;
    const CODE_CANCEL_ORDER_NOT_ABLE = 8005;
    const CODE_CANCEL_ORDER_ERROR = 8006;
    const CODE_CANCEL_ORDER_MISSING_PARAM = 8007;
    const CODE_CANCEL_ORDER_API_ERROR = 8008;
    const CODE_EDIT_ORDER_BY_TOKEN_ERROR = 8009;

    //Other
    const CODE_CALL_MASTER_ERROR = 10001;
    const CODE_TRACKING_REPORT_EVENTS_ERROR = 10002;

    //PersonalizedDesign
    const CODE_PERSONALIZED_MISSING_PARAM = 11001;
    const CODE_LOAD_PERSONALIZED_ERROR = 11002;
    const CODE_UPLOAD_IMAGE_ERROR = 11003;
    const CODE_EDIT_MISSING_LINE_ITEM = 11004;
    const CODE_EDIT_PERSONALIZED_ERROR = 11005;
    const CODE_SVG_ERROR = 11006;
    const CODE_MULTISVG_ERROR = 11007;
    const CODE_SVG_SEMI_ERROR = 11008;

    //Post
    const CODE_POST_MISSING_PARAM = 12001;
    const CODE_LOAD_POST_LIST_ERROR = 12002;
    const CODE_LOAD_POST_BY_ID_ERROR = 12003;
    const CODE_POST_TRACKING_MISSING_PARAM = 12004;
    const CODE_POST_TRACKING_ERROR = 12005;
    const CODE_LOAD_POST_LIST_BY_COLLECTION_ERROR = 12006;
    const CODE_LOAD_POST_LIST_RECENT_ERROR = 12007;
    const CODE_LOAD_POST_LIST_RELATED_ERROR = 12008;
    const CODE_LOAD_POST_BY_ID_NOT_PUBLISH = 12009;

    //Page
    const CODE_PAGE_MISSING_PARAM = 13001;
    const CODE_LOAD_PAGE_ERROR = 13002;

    //Subscriber
    const CODE_SEND_SUBSCRIBER_ERROR = 14001;
    const CODE_CONFIRM_SUBSCRIBER_ERROR = 14002;

    //Review
    const CODE_REVIEW_PRODUCT_SAVE_ERROR = 15001;
    const CODE_REVIEW_LOAD_ERROR = 15002;

    //Tracking Social
    const CODE_ADD_EVENT_ERROR = 16001;

    //2D Cross sell
    const CODE_LOAD_GET_PRODUCT_ERROR = 17001;
    const CODE_2DCROSSSELL_ADD_TO_CART_ERROR = 17002;
    const CODE_2DCROSSSELL_RECOMMEND_CART_ERROR = 17001;

    //Checkout
    const CHECKOUT_CODE_LOAD_CART_ERROR = 18001;
    const CHECKOUT_CODE_LOAD_ITEMS_ERROR = 18002;
    const CHECKOUT_CODE_POST_CONTACT_INFO_ERROR = 18003;

    protected $_hash = null;

    protected $_header_accept = 'json';

    public function __construct() {
        parent::__construct();

        $this->_sendCrossDomainHeader();

        $this->_header_accept = $this->_request->headerGet('accept') === 'application/msgpack' ? 'msgpack' : 'json';

        $this->_hash = trim($this->_request->get('hash'));
        $this->_request->reset();

        $request_params = [];

        $request_data = file_get_contents('php://input');

        $content_request_params = OSC::decode($request_data);

        if (!empty($content_request_params)) {
            $request_params = array_merge($request_params, $content_request_params);
        }

        if (!empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $request_query_string_params);

            if (count($request_query_string_params) > 0) {
                $request_params = array_merge($request_params, $request_query_string_params);
            }
        }

        if (count($request_params) > 0) {
            foreach ($request_params as $key => $value) {
                $this->_request->set($key, $value);
            }
        }

        try {
            if (OSC::cookieGet('customer_country_code') === null) {
                $location = OSC::helper('catalog/common')->getCustomerShippingLocation();
                OSC::cookieSetCrossSite('customer_country_code', $location['country_code']);
                OSC::cookieSetCrossSite('customer_province_code', $location['province_code']);
            }
        } catch (Exception $exception) { }

    }

    protected function getExtraData($options = []) {
        $options['extra_data'] = is_array($options['extra_data']) ? $options['extra_data'] : [];

        if (isset($options['nextjs_cache_key'])) {
            $options['extra_data']['nextjs_cache_key'] = $options['nextjs_cache_key'];
            $options['extra_data']['nextjs_cache_ttl'] = $options['nextjs_cache_ttl'];
        }

        OSC::core('observer')->dispatchEvent('reactjs_collect_extra_data', ['data' => &$options['extra_data']]);

        $commonLayout = OSC::helper('frontend/common')->getCommonLayout();

        $hashCommon = md5(OSC::encode($commonLayout));

        $options['extra_data'] = array_merge($options['extra_data'], [
            'common_layout' => $commonLayout,
            'hash' => $hashCommon
        ]);

        if ($options['sref_desc']) {
            $options['extra_data']['sref_desc'] = $options['sref_desc'];
        }

        return $options;
    }

    protected function sendSuccess($data = null, $options = []) {
        if (!isset($options['extra_data'])) {
            $options = $this->getExtraData($options);
        }

        static::__apiOutputCachingSet(['type' => 'json', 'content' => $data, 'options' => $options]);

        if (!$this->_request->get('hash') || (isset($options['extra_data']['hash']) && $this->_request->get('hash') == $options['extra_data']['hash'])) {
            unset($options['extra_data']['common_layout']);
        }

        $options['content_type'] = $this->_header_accept === 'msgpack' ? 'msgpack' : 'json';

        return $this->__ajaxResponse($data, $options);
    }

    protected function sendError($message = '', $code = 400, $options = [], $data = null) {
        $options['content_type'] = $this->_header_accept === 'msgpack' ? 'msgpack' : 'json';

        $list_acceptable_code = [100, 101, 200, 201, 202, 203, 204, 205, 206, 300, 301, 302, 303, 304, 305, 400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 500, 501, 502, 503, 504, 505];
        $code = in_array(intval($code), $list_acceptable_code, true) ? intval($code) : 400;

        return $this->__ajaxError($message, $code, $data, $options);
    }

    protected $_cache_prefix = null;
    private function __getCachePrefix($options) {
        if ($this->_cache_prefix === null) {
            $member_id = intval(OSC::cookieGet(OSC::helper('core/common')->getMemberCookieIDKey())) > 0 ? 1 : 0;
            $cache_prefix = 'REACTJS_API_CACHE_V' . intval(OSC::systemRegistry('output_cache_version')) . ':' . $member_id . ':';

            if (!in_array('ignore_location', $options, true)) {
                if (in_array('using_customer_shipping_location', $options, true)) {
                    $client_location = OSC::helper('catalog/common')->getCustomerShippingLocation();
                } else {
                    $client_location = OSC::helper('catalog/common')->getCustomerIPLocation();
                }

                $cache_prefix .= ($client_location['country_code'] ?? 'UNKNOWN') . ':' . ($client_location['province_code'] ?? 'NONE') . ':';
            }

            $this->_cache_prefix = $cache_prefix;
        }

        return $this->_cache_prefix;
    }
    
    private function __preprocessKey($key) {
        if (is_array($key)) {
            ksort($key);
            $key = implode('.', array_map(
                function ($v, $k) {
                    if (is_array($v)) {
                        return $k . '[]=' . implode('&' . $k . '[]=', $v);
                    } else {
                        return $k . '=' . $v;
                    }
                },
                $key,
                array_keys($key)
            ));
        }
        
        return trim(strval($key));
    }

    protected function apiOutputCaching($cache_key, $ttl = 0, $options = []) {
        $options = is_array($options) ? $options : [];

        if (!in_array('ignore_location', $options, true)) {
            $customer_country_code = OSC::helper('core/common')->getCustomerCountryCodeCookie();

            if (is_array($cache_key)) {
                $cache_key['customer_country_code'] = $customer_country_code ?: 'UNKNOWN';
            } else {
                $cache_key .= $customer_country_code ?: 'UNKNOWN';
            }
        }

        try {
            if (isset($options['tracking_event']) && is_array($options['tracking_event'])) {
                $event = OSC::helper('catalog/product')->getTrackingEventData($options['tracking_event']['id'], $options['tracking_event']['ukey'], $options['tracking_event']['variant_id']);
                OSC::helper('report/common')->addRecordEvent('catalog/product_view', $event);
            }

            $cache_key = implode('|', [
                $this->__getCachePrefix($options),
                debug_backtrace()[1]['class'] . '.' . debug_backtrace()[1]['function'],
                $this->__preprocessKey($cache_key)
            ]);

            $ttl = intval($ttl);
            if ($ttl < 1) {
                $ttl = OSC_CACHE_TIME;
            }

            $adapter = static::__getCacheAdapter();

            $cache = $adapter->get($cache_key);

            OSC::register('output_cache_key', ['key' => $cache_key, 'flag_set' => true, 'ttl' => $ttl]);
            if ($cache && $cache['content']) {
                OSC::register('output_cache_key', ['key' => $cache_key, 'flag_set' => false, 'ttl' => $ttl]);
                OSC::register('api_cache_key', '');

                if (isset($cache['options']['extra_data'])) {
                    $options['extra_data'] = $cache['options']['extra_data'];
                }
                $content = $this->_handleReplaceCacheContent($cache['content'], $options);

                $this->sendSuccess($content, $options);
            }

            OSC::register('api_cache_key', ['key' => $cache_key, 'ttl' => $ttl]);
        } catch (Exception $ex) {

        }
    }

    private function __apiOutputCachingSet($cache) {
        $cache_key = OSC::registry('api_cache_key');

        if (!$cache_key) {
            return;
        }

        $cache['options']['nextjs_cache_key'] = $cache_key['key'] . '.' . OSC::makeUniqid();
        $cache['options']['nextjs_cache_ttl'] = $cache_key['ttl'];

        try {
            static::__getCacheAdapter()->set($cache_key['key'], $cache, $cache_key['ttl']);
        } catch (Exception $ex) {

        }
    }

    private function __ajaxResponse($data = null, $options = []) {
        if (!is_array($options)) {
            $options = [];
        }

        $content_type_msgpack = $options['content_type'] === 'msgpack';

        if ($content_type_msgpack) {
            header("Content-type: application/msgpack");
        } else {
            header("Content-type: application/json");
        }

        if (isset($options['cache'])) {
            header("Cache-Control: public, max-age=" . ($options['cache'] <= 1 ? '2592000' : intval($options['cache'])));
            header("Expires: " . date('r', time() + ($options['cache'] <= 1 ? '2592000' : intval($options['cache']))));
        } else {
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");
        }

        OSC::core('observer')->dispatchEvent('beforeOutput', ['content' => &$data]);

        $content = [
            'result' => 'OK',
            'data' => $this->_addSref($data),
            'extra_data' => $options['extra_data'] ?? []
        ];

        $content = $content_type_msgpack ? msgpack_pack($content) : OSC::encode($content);

        if (!headers_sent()) {
            $this->outputCompression($content);
        }

        echo $content;

        die;
    }

    private function __ajaxError($message = '', $code = '', $data = null, array $options = []) {
        if (!is_array($options)) {
            $options = [];
        }

        $content_type_msgpack = $options['content_type'] === 'msgpack';

        if ($content_type_msgpack) {
            header("Content-type: application/msgpack");
        } else {
            header("Content-type: application/json");
        }

        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        $data = OSC::core('observer')->dispatchEvent('beforeOutput', ['content' => &$data]);

        $content = [
            'result' => 'ERROR',
            'message' => $message,
            'code' => $code,
            'data' => $data
        ];

        $content = $content_type_msgpack ? msgpack_pack($content) : OSC::encode($content);

        http_response_code(intval($code));

        if (!headers_sent()) {
            $this->outputCompression($content);
        }

        echo $content;

        die;
    }

    /**
     *
     * @return OSC_Cache_Abstract
     */
    private static function __getCacheAdapter() {
        return OSC::core('cache')->getAdapter();
    }

    protected function _handleReplaceCacheContent($content, $options) {
        if (OSC::helper('catalog/product')->checkCountryHasABTestPrice()) {
            switch (strtolower(OSC::getCurrentAction())) {
                case 'gethomesection':
                    $new_content = OSC::helper('catalog/product')->replaceContentApiHomePage($content);
                    break;
                case 'getproductdetail':
                    $new_content = $this->_replaceContentApiProductDetail($content, $options);
                    break;
                case 'getbestselling':
                case 'getlistproductbycollection':
                    $new_content = $this->_replaceContentApiGetListProduct($content);
                    break;
                default:
                    $new_content = $content;
                    break;
            }

            return $new_content;
        }

        return $content;
    }

    protected function _replaceContentApiGetListProduct(array $content) {
        foreach ($content['products'] as $data) {
            $product_ids[] = $data['product_id'];
        }

        $product_prices = OSC::helper('catalog/product')->getPriceDataByProductIds($product_ids, true);

        foreach ($content['products'] as $product_key => $data) {
            if (isset($product_prices[$data['product_id']]) && is_array($product_prices[$data['product_id']])) {
                $content['products'][$product_key]['price'] = $product_prices[$data['product_id']]['price'];
                $content['products'][$product_key]['compare_at_price'] = $product_prices[$data['product_id']]['compare_at_price'];
            }
        }

        return $content;
    }

    protected function _replaceContentApiProductDetail(array $content, array $options) {
        if (in_array('flag_feed', $options)) {
            return $content;
        }

        $product_id = intval($content['product']['product_id']);

        $is_semitest = $content['product']['cart_form_config']['mode'] === 'semitest';
        $cart_form_config_key = $is_semitest ? 'semitest_config' : 'campaign_config';
        $product_variants = $content['product']['cart_form_config'][$cart_form_config_key]['cart_option_config']['product_variants'];
        if ($product_id === 0 || !is_array($product_variants) || (isset($options['atp']) && $options['atp'] == 1)) {
            return $content;
        }

        $variant_prices = OSC::helper('catalog/product')->getVariantPriceDataByProductId($product_id);
        $selected_variant_id = intval($content['product']['cart_form_config'][$cart_form_config_key]['product_variant_id']);

        if ($selected_variant_id > 0 &&
            isset($variant_prices[$selected_variant_id]) &&
            is_array($variant_prices[$selected_variant_id])
        ) {
            $content['product']['price'] = $variant_prices[$selected_variant_id]['price'];
            $content['product']['compare_at_price'] = $variant_prices[$selected_variant_id]['compare_at_price'];
        }

        foreach ($product_variants as $product_variant_key => $data) {
            if (isset($variant_prices[$data['product_variant_id']]) && is_array($variant_prices[$data['product_variant_id']])) {
                $content['product']['cart_form_config'][$cart_form_config_key]['cart_option_config']['product_variants'][$product_variant_key]['price'] = $variant_prices[$data['product_variant_id']]['price'];
                $content['product']['cart_form_config'][$cart_form_config_key]['cart_option_config']['product_variants'][$product_variant_key]['compare_at_price'] = $variant_prices[$data['product_variant_id']]['compare_at_price'];
            }
        }
        return $content;
    }
}