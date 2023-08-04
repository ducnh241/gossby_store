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
 * @copyright	Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
declare(strict_types=1);

$headers = [];

if (function_exists('getallheaders')) {
    $headers = getallheaders();
} else if (is_array($_SERVER)) {
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) != 'HTTP_') {
            continue;
        }

        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
    }
}

if (isset($headers['Access-Control-Request-Headers']) || $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (!isset($headers['Origin'])) {
        die;
    }

//    if (!preg_match('/^https?\:\/\/([^\/]+\.)?linkhay\.com$/i', $headers['Origin'])) {
//        die;
//    }

    header("Access-Control-Allow-Origin: " . $headers['Origin']);
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT");
    header("Access-Control-Allow-Headers: Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control, X-OSC-Cross-Request");
    die;
}

include dirname(__FILE__) . '/core/compatible.php';
include dirname(__FILE__) . '/core/osecore.php';

class OSC extends OSC_Core {

    protected static $_AB_Test = [];
    protected static $_cdn_enabled = true;

    const APP_NAME = 'OSC';
    const DYNAMIC_LOAD_MODULE = 0;

    const AB_VER4_TAB_PRODUCT = ['key' => 'ab_test_ver_4_tab_product', 'value' => ['has_tab', 'not_have_tab']];

    const AB_TEST = [
        'ab_test_ver_4_tab_product' => [
            'enable' => false,
            'key' => 'ab_test_ver_4_tab_product',
            'values' => ['has_tab', 'not_have_tab']
        ],
        'ab_test_autocomplete_address_countries_range' => [
            'enable' => true,
            'key' => 'ab_test_autocomplete_address_countries_range',
            'values' => ['us', 'all_countries']
        ],
    ];

    public static function getServiceDomainSuffix() {
        return OSC_ENV == 'production' ? '' : '-' . OSC_ENV;
    }
    
    public static function getServiceUrlPersonalizedDesign() {
        if (preg_match('/^local-([a-z0-9]+)$/i', OSC_ENV, $matches)) {
            return 'http://personalizeddesign.' . strtolower($matches[1]) . '.com';
        }
        
        return 'https://personalizeddesign' . OSC::getServiceDomainSuffix() . '.9prints.com';
    }

    public static function getServiceUrlCrm() {
        switch (OSC_ENV) {
            case "production":
                $subdomain = '';
                break;
            case "beta":
                $subdomain = '-beta';
                break;
            default:
                $subdomain = '-dev';
        }
        return 'https://crm'. $subdomain .'.9prints.com';
    }

    public static function getServiceUrlCrossSell() {
        switch (OSC_ENV) {
            case "production":
                $subdomain = '';
                break;
            case "beta":
                $subdomain = '-beta';
                break;
            default:
                $subdomain = '-dev';
        }
        return 'https://2dcrosssell'. $subdomain .'.9prints.com';
    }

    public static function getABTestKey($return_string = false) {
        static $initialized = false;

        if (static::registry('OSC_SKIP_AB_TEST')) {
            return $return_string ? '' : [];
        }

        if (!$initialized) {
            $initialized = true;

            $list_ab_test_data = OSC::core('observer')->dispatchEvent('collect_ab_test_data');

            if (is_array($list_ab_test_data) && !empty($list_ab_test_data)) {
                foreach ($list_ab_test_data as $ab_test_data) {
                    if (!is_array($ab_test_data) || empty($ab_test_data)) {
                        continue;
                    }

                    foreach ($ab_test_data as $key_ab_test => $list_value_ab_test) {
                        $key_ab_test = trim($key_ab_test);

                        if (!$key_ab_test || !is_array($list_value_ab_test) || empty($list_value_ab_test)) {
                            continue;
                        }

                        $cookie_key = '_a' . OSC_Controller::makeRequestChecksum($key_ab_test, OSC_SITE_KEY) . 'B_';

                        $cookie_value = OSC::cookieGet($cookie_key);

                        if ($cookie_value !== null && in_array($cookie_value, $list_value_ab_test)) {
                            static::$_AB_Test[$key_ab_test] = $cookie_value;
                        }
                    }
                }
            }
        }

        if ($return_string) {
            $keys = [];

            foreach (static::$_AB_Test as $k => $v) {
                $keys[] = $k . ':' . $v;
            }

            return implode('|', $keys);
        }

        return static::$_AB_Test;
    }

    public static function getABTestValueWithSkipCondition(array $ab_test_pairs, array $skip_conditions = []) {
        if (static::registry('OSC_SKIP_AB_TEST')) {
            return null;
        }

        foreach($skip_conditions as $key) {
            $key = strval($key);

            $cookie_key = '_a' . OSC_Controller::makeRequestChecksum($key, OSC_SITE_KEY) . 'B_';

            if(OSC::cookieGet($cookie_key) !== null) {
                return null;
            }
        }

        foreach($ab_test_pairs as $key => $values) {
            $key = strval($key);

            $cookie_key = '_a' . OSC_Controller::makeRequestChecksum($key, OSC_SITE_KEY) . 'B_';

            $cookie_value = OSC::cookieGet($cookie_key);

            if ($cookie_value !== null) {
                static::$_AB_Test[$key] = $cookie_value;

                return ['key' => $key, 'value' => $cookie_value];
            }
        }

        $key = array_rand($ab_test_pairs);

        return static::getABTestValue($key, $ab_test_pairs[$key]);
    }

    public static function getABTestValue($key, array $arr = []) {
        if (static::registry('OSC_SKIP_AB_TEST')) {
            return null;
        }

        if (empty($arr) && isset(OSC::AB_TEST[$key]['values']) && is_array(OSC::AB_TEST[$key]['values'])) {
            $arr = OSC::AB_TEST[$key]['values'];
        }

        if (!isset(static::$_AB_Test[$key])) {
            $cookie_key = '_a' . OSC_Controller::makeRequestChecksum($key, OSC_SITE_KEY) . 'B_';

            $cookie_value = OSC::cookieGet($cookie_key);

            if ($cookie_value !== null) {
                static::$_AB_Test[$key] = $cookie_value;
            } else {
                $client_ip = OSC::getClientIP();

                try {
                    $ab_test_client_data = OSC::core('cache')->get('ABTestClientData');
                    $ab_test_client_data = OSC::decode($ab_test_client_data);

                    $client_data = $ab_test_client_data[$client_ip] ?? [];
                } catch (Exception $ex) {
                    $ab_test_client_data = [];
                    $client_data = [];
                }

                if (!empty($client_data) &&
                    !empty($client_data['request_time']) &&
                    $client_data['request_time'] === time()) {
                    static::$_AB_Test[$key] = $client_data['cookie_value'];
                } else {
                    try {
                        $value = OSC::core('cache')->increment('ABTest.' . $key, 'cache');
                        $value = intval($value);
                    } catch (Exception $ex) {
                        $value = 1;
                    }

                    $cookie_value = $arr[($value - 1) % count($arr)];

                    OSC::cookieSetCrossSite($cookie_key, $cookie_value);

                    OSC::core('observer')->dispatchEvent('increment_visit_ab', ['ab_key' => $key, 'ab_value' => $cookie_value]);

                    static::$_AB_Test[$key] = $cookie_value;

                    $ab_test_client_data[$client_ip] = [
                        'request_time' => time(),
                        'cookie_value' => $cookie_value
                    ];

                    try {
                        OSC::core('cache')->set('ABTestClientData', OSC::encode($ab_test_client_data));
                    } catch (Exception $ex) {}
                }
            }
        }

        return ['key' => $key, 'value' => static::$_AB_Test[$key]];
    }

    public static function setABTestValue($key, $value) {
        if (static::registry('OSC_SKIP_AB_TEST')) {
            return null;
        }

        $cookie_key = '_a' . OSC_Controller::makeRequestChecksum($key, OSC_SITE_KEY) . 'B_';

        OSC::cookieSetSiteOnly($cookie_key, $value);

        static::$_AB_Test[$key] = $value;
    }

    public static function getStoreInfo() {
        $store_id_file_path = OSC_SITE_PATH . '/.store_id';

        if (!file_exists($store_id_file_path)) {
            throw new Exception('No STORE_ID file was found');
        }

        $store_id = file_get_contents($store_id_file_path);
        $store_id = explode('|', $store_id, 4);

        if (count($store_id) != 4) {
            throw new Exception('STORE_ID file content is incorrect');
        }

        return [
            'id' => $store_id[0],
            'secret_key' => $store_id[1],
            'master_store_url' => $store_id[2],
            'store_id' => intval($store_id[3])
        ];
    }
}

OSC::initialize();

