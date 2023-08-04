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
 * OSECORE Core
 *
 * @package Core_Request
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Request extends OSC_Object {

    protected $_request = array();
    protected $_raw_request = array();
    protected $_get = array();
    protected $_post = array();
    protected $_is_cli = false;
    protected $_is_ssl = false;
    protected $_headers = null;

    /**
     * In touch version ?
     *
     * @var boolean
     */
    protected $_is_touch = null;
    protected $_client_ip = null;

    /**
     *
     * @var AntiXSS 
     */
    protected $_xss_cleaner = null;

    /**
     * Initialize function. Load, clean & push $_POST, $_GET to OSCCore_Request::$_in, and to determine if there is AJAX request.
     *
     * @return Boolean
     */
    public function __construct() {
        if ($this->headerIsExists('X-Forwarded-Proto') && $this->headerGet('X-Forwarded-Proto') == 'https') {
            $this->_is_ssl = true;
        }

        $this->_xss_cleaner = new voku\helper\AntiXSS();

        if ($this->_is_touch === null) {
            $detector = new Mobile_Detect();

            $this->_is_touch = $detector->isMobile() || $detector->isTablet();
        }

        if (defined('STDIN')) {
            $_CLI_DATA = array();

            $this->_is_cli = true;
            parse_str(implode('&', array_slice($_SERVER['argv'], 1)), $_CLI_DATA);


            if (count($_CLI_DATA) > 0) {
                foreach ($_CLI_DATA as $k => $v) {
                    $_GET[$k] = $v;
                }
            }
        }

        if ($this->headerIsExists('Content-Type') && $this->headerGet('Content-Type') == 'application/json') {
            $JSON_DATA = json_decode(file_get_contents('php://input'), true);

            if (is_array($JSON_DATA) && count($JSON_DATA) > 0) {
                foreach ($JSON_DATA as $k => $v) {
                    $_GET[$k] = $v;
                }
            }
        }

        $this->_clean();
    }

    public function isSSL() {
        return $this->_is_ssl;
    }

    /**
     * Clean all input variables
     *
     */
    protected function _clean() {
        if (isset($_GET['variables']) && $_GET['variables']) {
            if (!is_array($request)) {
                $request = array();
            }

            $request = array_merge($request, $this->parseGetRequest($_GET['variables']));
        }

        if (isset($request)) {
            foreach ($request as $k => $v) {
                $_GET[$k] = $v;
            }
        }

        // Check if request backup exists, restore it

        if (isset($_GET['requestid']) && $_GET['requestid']) {
            $this->_restoreQueries($_GET['requestid']);
        }

        unset($_GET['variables']);
        unset($_GET['requestid']);

        $_GET = $this->_recursiveClean($_GET);
        $_POST = $this->_recursiveClean($_POST);

        $this->_get = $_GET;
        $this->_post = $_POST;

        $this->_request = array_merge($this->_get, $this->_post);

        $this->_raw_request = $_REQUEST;

        $_REQUEST = $this->_request;
    }

    /**
     * Recursive clean all input variables
     *
     * @param  array $data
     * @return array
     */
    protected function _recursiveClean($data) {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $v = $this->_recursiveClean($v);
            } else {
                if (isset($_REQUEST['_ajax_encode']) && $_REQUEST['_ajax_encode']) {
                    $v = $this->_decodeAjax($v);
                }

                $v = $this->_xss_cleaner->xss_clean($v);

                $v = $this->encodeValue($v);
            }

            $data[$k] = $v;
        }

        return $data;
    }

    public function decodeValue($value) {
        return $value;
        return htmlspecialchars_decode($value);
    }

    public function encodeValue($value) {
        return $value;
        return htmlspecialchars($value);
    }

    /**
     * Decode ajax variable string (UTF-8 encode)
     *
     * @param  string $txt
     * @return string
     */
    protected function _decodeAjax($txt) {
        if (!$txt) {
            return $txt;
        }

        return preg_replace_callback('/%u([0-9A-F]{1,4})/i', function($matches) {
            $int = hexdec($matches[1]);

            $return = '';

            if ($int < 0) {
                return chr(0);
            } elseif ($int <= 0x007f) {
                $return .= chr($int);
            } elseif ($int <= 0x07ff) {
                $return .= chr(0xc0 | ( $int >> 6 ));
                $return .= chr(0x80 | ( $int & 0x003f ));
            } elseif ($int <= 0xffff) {
                $return .= chr(0xe0 | ( $int >> 12 ));
                $return .= chr(0x80 | ( ( $int >> 6 ) & 0x003f ));
                $return .= chr(0x80 | ( $int & 0x003f ));
            } elseif ($int <= 0x10ffff) {
                $return .= chr(0xf0 | ( $int >> 18 ));
                $return .= chr(0x80 | ( ( $int >> 12 ) & 0x3f ));
                $return .= chr(0x80 | ( ( $int >> 6 ) & 0x3f ));
                $return .= chr(0x80 | ( $int & 0x3f ));
            } else {
                return chr(0);
            }

            return $return;
        }, utf8_encode($txt));
    }

    /**
     * Get a input variable by key
     *
     * @param  string $key
     * @return string $return
     */
    public function get($keys, $default_value = false, $method = null) {
        $method = strtolower($method);

        if ($method == 'get') {
            $pointer = & $this->_get;
        } else if ($method == 'post') {
            $pointer = & $this->_post;
        } else if ($method == 'raw') {
            $pointer = & $this->_raw_request;
        } else {
            $pointer = & $this->_request;
        }

        if (!is_array($keys)) {
            if (isset($pointer[$keys])) {
                return $pointer[$keys];
            } else if ($default_value !== false) {
                return $default_value;
            }

            return null;
        }

        $values = array();

        foreach ($keys as $key) {
            if (isset($pointer[$key])) {
                $values[$key] = $pointer[$key];
            } else if ($default_value !== false) {
                $values[$key] = $default_value;
            }
        }

        return $values;
    }

    public function getRaw($keys, $default_value = false) {
        return $this->get($keys, $default_value, 'raw');
    }

    /**
     * Get more one input variables by key array
     *
     * @param  array $keys
     * @return array $return
     */
    public function gets($keys) {
        $vars = array();

        foreach ($keys as $key) {
            $vars[] = $this->get($key);
        }

        return $vars;
    }

    public function getAll($method = null) {
        if ($method == 'get') {
            return $this->_get;
        }if ($method == 'post') {
            return $this->_post;
        } else if ($method == 'raw') {
            return $this->_raw_request;
        } else {
            return $this->_request;
        }
    }

    /**
     * Set a value to input variable by key
     *
     * @param  string $key
     * @param  mixed  $val
     * @return void
     */
    public function set($key, $val, $method = null) {
        $method = strtolower(strval($method));

        if ($method != 'get') {
            $_POST[$key] = $val;
            $this->_post[$key] = $val;
        }

        if ($method != 'post') {
            $_GET[$key] = $val;
            $this->_get[$key] = $val;
        }

        $_REQUEST[$key] = $val;

        $this->_request[$key] = $val;
        $this->_raw_request[$key] = $val;

        return $this;
    }

    /**
     * Set value to more one input variables by key array
     *
     * @param  array $arr
     * @return void
     */
    public function sets($arr, $method = null) {
        foreach ($arr as $key => $val) {
            $this->set($key, $val, $method);
        }

        return $this;
    }

    /**
     * Set value to more one input variables by key array
     *
     * @param array $data
     * @param string $method
     * @return OSC_Request
     */
    public function append($data, $method = 'get') {
        $num_args = func_num_args();

        $data = func_get_arg(0);

        if (is_array($data)) {
            $method = $num_args == 2 ? strtolower(func_get_arg(1)) : 'post';
        } else {
            if ($num_args < 2) {
                return $this;
            }

            $data = array($data => func_get_arg(1));

            $method = $num_args == 3 ? strtolower(func_get_arg(2)) : 'post';
        }

        foreach ($data as $key => $val) {
            $val = (string) $val;

            $this->_request[$key] = $val;

            if ($method == 'get') {
                $this->_post[$key] = $val;
            } else if ($method == 'post') {
                $this->_get[$key] = $val;
            }
        }

        return $this;
    }

    /**
     *
     * @return OSC_Request
     */
    public function reset() {
        $this->_request = $this->_get = $this->_post = array();
        return $this;
    }

    public function isAjax() {
        return OSC::isAjax();
    }

    public function isMobile() {
        return $this->_is_touch;
    }

    /**
     * Remove a value by index
     *
     * @param String $key
     * @return void
     */
    public function remove($key) {
        unset($this->_data[$key]);
    }

    protected function _isIPIn($ip, $net, $mask) {
        return strcmp(substr(str_pad(decbin(ip2long($net)), 32, '0', STR_PAD_LEFT), 0, $mask), substr(str_pad(decbin(ip2long($ip)), 32, '0', STR_PAD_LEFT), 0, $mask)) == 0;
    }

    protected function _isPrivateIP($ip) {
        $privates = array('127.0.0.0/24', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16');

        foreach ($privates as $k) {
            list ($net, $mask) = explode('/', $k);

            if ($this->_isIPIn($ip, $net, $mask)) {
                return true;
            }
        }

        return false;
    }

    public function getClientIp() {
        if ($this->_client_ip === null) {
            $client_ip = null;
            $ips = null;

            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ips = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ips = $_SERVER ['HTTP_CLIENT_IP'];
            }

            if ($ips) {
                $ips = preg_split('/[, ]/', $ips);

                foreach ($ips as $ip) {
                    if (preg_match('/^(\d{1,3}\.){3}\d{1,3}$/s', $ip) && !$this->_isPrivateIP($ip)) {
                        $client_ip = $ip;
                        break;
                    }
                }
            }

            if (!$client_ip) {
                $client_ip = $_SERVER['REMOTE_ADDR'];
            }

            $this->_client_ip = $client_ip;
        }

        return $this->_client_ip;
    }

    public function headerGetAll() {
        if ($this->_headers !== null) {
            return $this->_headers;
        }

        $this->_headers = array();

        if (function_exists('getallheaders')) {
            $this->_headers = getallheaders();
        } else if (is_array($_SERVER)) {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) != 'HTTP_') {
                    continue;
                }

                $this->_headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $this->_headers;
    }

    public function headerIsExists() {
        $headers = $this->headerGetAll();

        foreach (func_get_args() as $header_name) {
            $header_name = ucwords($header_name);

            if (!isset($headers[$header_name])) {
                return false;
            }
        }

        return true;
    }

    public function headerGet() {
        $headers = $this->headerGetAll();

        $total_name = func_num_args();

        if ($total_name < 1) {
            return null;
        }

        $headers_value = array();

        foreach (func_get_args() as $header_name) {
            $cleaned_header_name = ucwords($header_name);
            $headers_value[$header_name] = isset($headers[$cleaned_header_name]) ? $headers[$cleaned_header_name] : null;
        }

        return $total_name == 1 ? $headers_value[func_get_arg(0)] : $headers_value;
    }

    public function buildRequest($params) {
        if (!is_array($params)) {
            return '';
        }

        $request_string = [];

        foreach ($params as $k => $v) {
            if (is_array($v) || preg_match('/[^a-zA-Z0-9\_\-\.]/', $k . $v)) {
                continue;
            }

            unset($params[$k]);

            if ((!is_string($v) && !is_int($v) && !is_float($v)) || $v === '') {
                continue;
            }

            $request_string[] = $k . '/' . $v;
        }

        $request_string = implode('/', $request_string);

        if (count($params) > 0) {
            $request_string .= '?' . http_build_query($params);
        }

        return $request_string;
    }

    public function parseGetRequest($params) {
        $parsed = array();

        $params = trim($params);

        if ($params != '') {
            $params = preg_replace('/\/{2,}/', '/', $params);

            $segments = explode('/', $params);

            if (count($segments) > 1) {
                for ($key_idx = 0, $val_idx = 1; $val_idx < count($segments); $key_idx += 2, $val_idx += 2) {
                    if (preg_match('/[^a-zA-Z0-9\_\-\.]/', $segments[$key_idx] . $segments[$val_idx])) {
                        continue;
                    }

                    $parsed[$segments[$key_idx]] = $segments[$val_idx];
                }
            }
        }

        $this->sets($parsed, 'get');
    }

}
