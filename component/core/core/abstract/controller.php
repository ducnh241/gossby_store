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
 * OSC Core Abstract Controller
 *
 * @package Abstract_Core_Controller
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
abstract class Abstract_Core_Controller extends OSC_Controller {

    public function __construct() {
        parent::__construct();
        
        static::_antiCrawlerPersonalizedDesign();

        if ($this->_request->get('show_debug_info') && $this->checkPermission('developer', false)) {
            OSC::register('SHOW_DEBUG_INFO', 1);
        }

        if ($this->_request->get('cdn_enable')) {
            OSC::setCDNFlag(true);
        }

        OSC::core('language')->load('core/common', 'user/common', 'payment/common');
    }

    protected static function _antiCrawlerPersonalizedDesign() {
        $timestamp = time();

        $prefix = $timestamp - ($timestamp % (60 * 60 * 24));
        $timestamp = $timestamp - ($timestamp % (60 * 15));

        $md5 = md5($prefix . $timestamp);

        OSC::cookieSetSiteOnly($md5, md5($timestamp), ($timestamp + (60 * 15)) - time());
    }

    /**
     * 
     * @return OSC_Cache_Abstract
     */
    private static function __getCacheAdapter() {
        return OSC::core('cache')->getAdapter('controller');
    }
    
    public function cachingOutput($html) {
        parent::output($html);
    }
    
    public function cachingResponseAjax($data) {
        parent::_ajaxResponse($data);
    }

    public static function outputCaching($cache_key, $ttl = 0, $options = []) {
        $options = is_array($options) ? $options : [];

        try {
            $price_rate = OSC::helper('catalog/common')->priceExchangeRate();

            if (in_array('using_customer_shipping_location', $options, true)) {
                $client_location = OSC::helper('catalog/common')->getCustomerShippingLocation();
            } else {
                $client_location = OSC::helper('catalog/common')->getCustomerIPLocation();
            }

            $member_id = OSC::helper('user/authentication')->getMember()->getId();

            $cache_key_prefix = 'OUTPUT_CACHE_V1' .
                intval(OSC::systemRegistry('output_cache_version')) . ':' .
                $member_id . ':' .
                (isset($client_location['country_code']) ? $client_location['country_code'] : 'UNKNOWN') . ':' .
                (isset($client_location['province_code']) ? $client_location['province_code'] : 'NONE') . ':' .
                $price_rate['currency_code'];

            if(OSC::core('request')->get('dev_speed_test')) {
                $cache_key_prefix .= ':speed_test';
            }

            $cache_key = $cache_key_prefix . '|' . $cache_key;

            $adapter = static::__getCacheAdapter();

            $cache = $adapter->get($cache_key);

            if ($cache && ! OSC::core('request')->get('dev_reset_output_caching')) {
                OSC::register('controller_cache_key', '');

                if ($cache['type'] == 'ajax') {
                    OSC::controller()->cachingResponseAjax($cache['content']);
                }

                OSC::controller()->cachingOutput($cache['content']);
            } else {

            }

            $ttl = intval($ttl);

            if ($ttl < 1) {
                $ttl = 60 * 15;
            }

            OSC::register('controller_cache_key', ['key' => $cache_key, 'ttl' => $ttl]);
        } catch (Exception $ex) {

        }
    }

    private function __outputCachingSet($cache) {
        $cache_key = OSC::registry('controller_cache_key');

        if (!$cache_key) {
            return;
        }

        try {
            static::__getCacheAdapter()->set($cache_key['key'], $cache, $cache_key['ttl']);
        } catch (Exception $ex) {

        }
    }

//    public static function caching($cache_key, $ttl = 0) {
//        try {
//            $cache_key_prefix = intval(OSC::systemRegistry('controller_caching_prefix'));
//
//            if ($cache_key_prefix < 1) {
//                return;
//            }
//
//            $cache_key = $cache_key_prefix . ':' . $cache_key;
//
//            $adapter = static::__getCacheAdapter();
//
//            $cache = $adapter->get($cache_key);
//
//            if ($cache) {
//                OSC::register('controller_cache_key', '');
//
//                if ($cache['type'] == 'ajax') {
//                    OSC::controller()->cachingResponseAjax($cache['content']);
//                }
//
//                OSC::controller()->cachingOutput($cache['content']);
//            }
//
//            $ttl = intval($ttl);
//
//            if ($ttl < 1) {
//                $ttl = 3600;
//            }
//
//            OSC::register('controller_cache_key', ['key' => $cache_key, 'ttl' => time() + $ttl]);
//        } catch (Exception $ex) {
//
//        }
//    }

//    private static $__cache_mapping = [];
//
//    public static function setCacheMapping(Abstract_Core_Model $model) {
//        $cache_key = OSC::registry('controller_cache_key');
//
//        if (!$cache_key) {
//            return;
//        }
//
//        $model_key = strtolower($model->getModelKey());
//
//        static::$__cache_mapping[$cache_key['key']] = $model_key . ':' . $model->getId();
//    }
//
//    private function __setCache($cache) {
//        $cache_key = OSC::registry('controller_cache_key');
//
//        if (!$cache_key) {
//            return;
//        }
//
//        try {
//            static::__getCacheAdapter()->set($cache_key['key'], $cache, $cache_key['ttl']);
//        } catch (Exception $ex) {
//
//        }
//    }

    public function output($html) {
        static::__outputCachingSet(['type' => 'html', 'content' => $html]);
//        static::__setCache(['type' => 'html', 'content' => $html]);
        parent::output($html);
    }

    protected function _ajaxResponse($data = null, $options = []) {
        static::__outputCachingSet(['type' => 'ajax', 'content' => $data, 'options' => $options]);
        parent::_ajaxResponse($data, $options);
    }

    /**
     * Check if access permission exist
     *
     * @param  string  $perm_key
     * @param  boolean $auto_break
     * @return boolean
     */
    public function checkPermission($perm_key = false, $auto_break = true) {
        return static::_checkPermission($perm_key, $auto_break);
    }

    public function checkHavePermissions($perm_keys, $auto_break = true) {
        if (!is_array($perm_keys)) {
            $perm_keys = [$perm_keys];
        }

        return static::_checkPermission(implode('&', $perm_keys), $auto_break);
    }

    public function checkInPermissions($perm_keys, $auto_break = true) {
        if (!is_array($perm_keys)) {
            $perm_keys = [$perm_keys];
        }

        return static::_checkPermission(implode('|', $perm_keys), $auto_break);
    }

    /**
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function setting(string $key, $default = null) {
        return OSC::helper('core/setting')->get($key, $default);
    }

    /**
     * Check if access permission exist
     *
     * @param  string  $perm_key
     * @param  boolean $auto_break
     * @return boolean
     */
    protected static function _checkPermission($perm_key = false, $auto_break = true) {
        if (!$perm_key) {
            if (!OSC::helper('user/authentication')->getMember()->isAdmin()) {
                if ($auto_break) {
                    static::notFound('You don\'t have permission to view the page');
                }

                return false;
            }

            return true;
        }

        $permission_data = static::getPermissionData();

        if ($permission_data === true) {
            return true;
        }

        $check_flag = false;

        if (preg_match('/[|&]/', $perm_key)) {
            $perm_key = preg_replace('/([^a-zA-Z0-9\/_.\-:()|&])/', '', $perm_key);
            $perm_key = preg_replace('/(([^|&])([|&])([|&]+))/', '\\2\\3', $perm_key);
            $perm_key = preg_replace_callback('/([a-zA-Z0-9\/_.\-:]+)/', function($matches) use($permission_data) {
                return in_array($matches[1], $permission_data) ? '+' : '*';
            }, $perm_key);
            $perm_key = str_replace(array('+', '*', '|', '&'), array(' true ', ' false ', ' || ', ' && '), $perm_key);

            eval('$check_flag = ' . $perm_key . ';');
        } else {
            $check_flag = in_array($perm_key, $permission_data);
        }

        if (!$check_flag && $auto_break) {
            static::notFound('You don\'t have permission to view the page');
        }

        return $check_flag;
    }

    public static function getPermissionData() {
        static $_permission_data = null;

        if ($_permission_data !== null) {
            return $_permission_data;
        }

        if (OSC::helper('user/authentication')->getMember()->getGroup()->isAdmin()) {
            $_permission_data = true;
        } else {
            $_permission_data = array();

            if (OSC::helper('user/authentication')->getMember()->getId() > 0) {
                $perm_mask_ids = array_merge(OSC::helper('user/authentication')->getMember()->getGroup()->data['perm_mask_ids'], OSC::helper('user/authentication')->getMember()->data['perm_mask_ids']);

                $collection = OSC::model('user/permissionMask')->getCollection()->load($perm_mask_ids);

                $_permission_data = array();

                foreach ($collection as $perm_mask) {
                    $_permission_data = array_merge($_permission_data, $perm_mask->data['permission_data']);
                }

                $_permission_data = array_unique($_permission_data);
            }
        }

        return $_permission_data;
    }

    /**
     * 
     * @return Model_User_Member
     */
    public function getAccount() {
        return OSC::helper('user/authentication')->getMember();
    }

}
