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

/**
 * OSC_Framework::Controller
 *
 * @package OSC_Core
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
abstract class OSC_Controller extends OSC_Object {

    /**
     *
     * @var OSC_Request
     */
    protected $_request = null;
    protected $_response_callback = array();
    protected $_cross_domain_flag = true;
    private static $__token_key = null;
    private static $__token_data = array();
    private static $__api_flag = null;
    private static $__token_verify_error = false;

    const TOKEN_FIELD_NAME = '__TOKEN_KEY__';
    const CHECKSUM_SECRET_KEY = '';

    /**
     *
     * @var OSC_Template
     */
    protected $_template = null;

    /**
     *
     * @var boolean 
     */
    protected $_check_hash = false;

    /**
     *
     * @var string 
     */
    protected $_hash_failed_forward = false;

    protected $_enable_pre_hash = true;

    /**
     *
     * @var string 
     */
    protected $_hash = '';

    public function __construct() {        
        $this->getTemplate()->resetBreadcrumb();

        $this->_request = OSC::core('request');

        if (OSC_Controller::getVerifyTokenError() !== false) {
            $this->_ajaxError(OSC_Controller::getVerifyTokenError());
        }

        if ($this->_enable_pre_hash) {
            OSC::preHash($this->_check_hash, $this->_hash_failed_forward);
        }
    }

    public static function makeRequestChecksum($request_string, $secret_key = null) {
        if ($secret_key === null) {
            $secret_key = static::CHECKSUM_SECRET_KEY;
        }

        if (!$secret_key) {
            throw new Exception('Secret key is empty');
        }

        $hmac = hash_hmac('sha256', $request_string, $secret_key);

        return md5($hmac);
    }

    protected function _verifyRequestByChecksum() {
        if (!static::CHECKSUM_SECRET_KEY) {
            throw new Exception('Checksum secret key is not set');
        }

        $request_params = $this->_request->getAll('raw');

        $checksum = $request_params['checksum'];
        unset($request_params['checksum']);

        try {
            if (static::makeRequestChecksum(isset($request_params['checksum_signature']) ? $request_params['checksum_signature'] : http_build_query($request_params)) !== $checksum) {
                throw new Exception('HMAC validation failed with params: ' . print_r($request_params, 1));
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function verifyToken() {
        if (static::$__api_flag !== null) {
            return;
        }

        static::$__token_key = null;
        static::$__token_data = array();
        static::$__api_flag = null;

        $token_key = trim(OSC::core('request')->get(static::TOKEN_FIELD_NAME));

        if ($token_key) {
            try {
                static::$__token_data = $this->__verifyApiTokenData($token_key);
                static::$__token_key = $token_key;
                static::$__api_flag = true;
            } catch (Exception $ex) {
                static::$__token_verify_error = 'TOKEN KEY không hợp lệ';
            }
        }
    }

    public static function isApiCall() {
        return static::$__api_flag;
    }

    public static function getApiTokenKey() {
        return static::$__token_key;
    }

    public static function getApiTokenData() {
        return static::$__token_data;
    }

    public static function getVerifyTokenError() {
        return static::$__token_verify_error;
    }

    /**
     * 
     * @param string $api_path
     * @param mixed $api_data
     * @param boolean $auth_with_current_member
     * @return mixed
     * @throws OSC_Exception
     */
    public static function callApi($api_path, $api_data = array(), $auth_with_current_member = true) {
        return static::makeApiCall($api_path, $api_data, $auth_with_current_member);
    }

    /**
     * 
     * @param string $api_path
     * @param array $files
     * @param mixed $api_data
     * @param boolean $auth_with_current_member
     * @return mixed
     * @throws OSC_Exception
     */
    public static function callApiWithFile($api_path, $files, $api_data = array(), $auth_with_current_member = true) {
        return static::makeApiCall($api_path, $api_data, $auth_with_current_member, array('files' => $files));
    }

    /**
     * 
     * @param string $api_path
     * @param mixed $api_data
     * @param boolean $auth_with_current_member
     * @param array $curl_opts
     * @return mixed
     * @throws OSC_Exception
     */
    public static function makeApiCall($api_path, $api_data, $auth_with_current_member, $curl_opts = array()) {
        if (!is_array($curl_opts)) {
            $curl_opts = array();
        }

        if (!preg_match('/^https?:\/\//i', $api_path)) {
            $api_path = OSC::$base_url . '/' . $api_path;
        }

        if (!isset($curl_opts['request_params'])) {
            $curl_opts['request_params'] = array();
        }

        $curl_opts['request_params'][static::TOKEN_FIELD_NAME] = static::makeApiToken($api_data, $auth_with_current_member ? true : false);

        try {
            $response = OSC::core('network')->curl($api_path, $curl_opts);
        } catch (Exception $ex) {
            throw new OSC_Exception($ex->getMessage());
        }

        $server_response = $response['content'];

        if (!is_array($server_response) || !isset($server_response['result'])) {
            throw new OSC_Exception('Khong the connect toi api server');
        }

        return $server_response;
    }

    /**
     * 
     * @param mixed $data
     * @param mixed $auth_member_id
     * @return string
     * @throws Exception
     */
    public static function makeApiToken($data, $auth_member_id = null) {
        $token_key = OSC::makeUniqid('API_TOKEN', true);

        if (is_bool($auth_member_id) && $auth_member_id) {
            $auth_member_id = Linkhay::user()->id;
        } else if (is_int($auth_member_id) && $auth_member_id > 0) {
            $auth_member_id = intval($auth_member_id);
        } else {
            $auth_member_id = 0;
        }

        try {
            if (!Linkhay::getRedis()->set($token_key, OSC::encode(array('DATA' => $data, 'AUTH_MEMBER_ID' => $auth_member_id)), array('nx', 'ex' => 60 * 5))) {
                throw new Exception("Cannot generate token key");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return $token_key;
    }

    private function __verifyApiTokenData($token_key) {
        try {
            $token_data = Linkhay::getRedis()->get($token_key);

            if ($token_data === false) {
                throw new Exception('Token key is not exists');
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        $token = OSC::decode($token_data, true);

        if ($token['AUTH_MEMBER_ID'] > 0) {
            try {
                Linkhay::reLogin($token['AUTH_MEMBER_ID']);
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }
        }

        return $token['DATA'];
    }

    /**
     * 
     * @param array $additional_params
     * @return string
     */
    public function rebuildUrl($additional_params) {
        return OSC::rebuildUrl($additional_params);
    }

    /**
     * 
     * @return string
     */
    public function getCurrentUrl() {
        return OSC::getCurrentUrl();
    }

    /**
     * 
     * @param string $action_path
     * @param mixed $params
     * @param bool $inc_hash
     * @return string
     */
    public function getUrl($action_path = null, $params = array(), $inc_hash = null) {
        return OSC::getUrl($action_path, $params, $inc_hash);
    }

    /**
     * 
     * @param string $action
     * @return OSC_Controller
     */
    public function forward($action) {
        OSC::forward($action);
        return $this;
    }

    /**
     * @param null $data
     * @param array $options
     */
    protected function _ajaxResponse($data = null, array $options = []) {
        $response_callback = array_pop($this->_response_callback);

        if (!is_array($options)) {
            $options = [];
        }

        $content_type_msgpack = $options['content_type'] === 'msgpack';

        if ($response_callback) {
            $this->_sendCrossDomainHeader();

            call_user_func($response_callback, [
                'result' => 'OK',
                'data' => $data,
                'extra_data' => $options['extra_data'] ?? []
            ]);
        } else {
            $this->_sendCrossDomainHeader();

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
        }
        die;
    }

    /**
     * @param string $message
     * @param string $code
     * @param null $data
     * @param array $options
     */
    protected function _ajaxError($message = '', $code = '', $data = null, array $options = []) {
        $response_callback = array_pop($this->_response_callback);

        if (!is_array($options)) {
            $options = [];
        }

        $content_type_msgpack = $options['content_type'] === 'msgpack';

        if ($response_callback) {
            $this->_sendCrossDomainHeader();

            call_user_func($response_callback, [
                'result' => 'ERROR',
                'message' => $message,
                'code' => $code,
                'data' => $data
            ]);
        } else {
            $this->_sendCrossDomainHeader();

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
        }

        die;
    }

    public function output($html) {
        if (OSC::registry('SHOW_DEBUG_INFO')) {
            OSC::core('debug')->showInfo();
        }

        OSC::core('observer')->dispatchEvent('beforeOutput', ['content' => &$html]);

        $html = $this->_addSref($html);

        if (!headers_sent()) {
            $this->outputCompression($html);
        }

        echo $html;
        die;
    }

    protected function _addSref($content) {        
        $sref = OSC::registry('DLS-SALE-REF');

        if(! $sref || OSC::registry('DLS-ADD-SREF-URL') != 1) {
            return $content;
        }

        if(is_array($content)) {
            foreach($content as $k => $v) {
                $content[$k] = $this->_addSref($v);
            }

            return $content;
        } else if(! is_string($content)) {
            return $content;
        }

        return preg_replace_callback('/(\s+href\s*=\s*[\'"])(https?:\/\/[^\'"]+)([\'"])/i', function($matched) use ($sref) {
            if(! preg_match('/[\?\&]sref=[0-9]+(\&|$|\#)/i', $matched[2])) {
                $matched[2] = explode('#', $matched[2]);

                if(preg_match('/^https?:\/\/[^\/]+$/i', $matched[2][0])) {
                    $matched[2][0] .= '/';
                }

                $matched[2][0] .= (strpos($matched[2][0], '?') === false ? '?' : '&') . 'sref=' . $sref['id'];

                $matched[2] = implode('#', $matched[2]);
            } else {
                $matched[2] = preg_replace('/([\?\&])sref=[0-9]+(\&|$|\#)/', '\\1sref=' . $sref['id'] . '\\2', $matched[2]);
            }

            return $matched[1] . $matched[2] . $matched[3];
        }, $content);
    }

    public function error($message = '', $code = '', $data = null) {
        if ($this->_request->isAjax()) {
            $this->_ajaxError($message, $code, $data);
        }

        static::notFound($message);
    }

    public static function notFound() {
        header("HTTP/1.0 404 Not Found");
        echo OSC::core('template')->build('static/404');
        die;
    }

    public static function redirect($url) {
        static::moveTemporary($url);
    }

    /**
     * Moved permanently
     * 
     * @param string $new_url
     */
    public static function movePermanently($new_url) {
        static::move301($new_url);
    }

    /**
     * Moved permanently
     * 
     * @param string $new_url
     */
    public static function move301($new_url) {
        header('Location: ' . $new_url, true, 301);
        die;
    }

    /**
     * Moved Temporarily
     * 
     * @param string $new_url
     */
    public static function moveTemporary($new_url) {
        static::move302($new_url);
    }

    /**
     * Moved Temporarily
     * 
     * @param string $new_url
     */
    public static function move302($new_url) {
        header('Location: ' . $new_url, true, 302);
        die;
    }

    /**
     * 
     * @return OSC_Template
     */
    public function getTemplate() {
        return OSC::core('template');
    }

    public function addMessage($message) {
        $this->getTemplate()->addMessage($message, OSC_Template::MESSAGE_TYPE_MESSAGE);
    }

    public function addErrorMessage($message) {
        $this->getTemplate()->addMessage($message, OSC_Template::MESSAGE_TYPE_ERROR);
    }

    public function isCrossRequest() {
        return $this->registry('CROSS_REQUEST_FLAG');
    }

    public function setCrossRequest($flag = true) {
        return $this->register('CROSS_REQUEST_FLAG', $flag ? 1 : 0);
    }

    public function crossControllerListener($response) {
        if ($response['result'] != 'OK') {
            $this->_ajaxError($response['message'], $response['code'], $response['data']);
        }

        $this->_ajaxResponse($response['data']);
    }

    /**
     * 
     * @param mixed $callback
     * @return \Abstract_Core_Controller
     */
    public function setResponseCallback($callback = null) {
        $this->_response_callback[] = $callback;

        return $this;
    }

    protected function _response($data = null) {
        if ($this->_request->isAjax()) {
            $this->_ajaxResponse($data);
        }
    }

    public function setCrossDomainFlag($flag = false) {
        $this->_cross_domain_flag = $flag;
        return $this;
    }

    protected function _sendCrossDomainHeader() {
        if (!$this->_cross_domain_flag || !$this->_request->headerIsExists('Origin')) {
            return;
        }

        $origin = $this->_request->headerGet('Origin');

        header("Access-Control-Allow-Origin: " . $origin);
        header("Access-Control-Allow-Credentials: true"); //Include cookies for cross domain request
        header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT");
        header("Access-Control-Allow-Headers: Content-Type, Depth, User-Agent, Authorization, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control, X-OSC-Cross-Request");
    }

    public function outputCompression($data, $headers = array()) {
        if (!is_array($headers)) {
            $headers = array();
        }

        if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) {
            $encoding = 'x-gzip';
        } else if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
            $encoding = 'gzip';
        } else {
            $encoding = false;
        }

        if ($encoding !== false) {
            ob_start();
            ob_implicit_flush(0);
            echo $data;
            $buffer = ob_get_contents();
            ob_end_clean();

            $headers[] = "Content-Encoding: " . $encoding;
            $headers[] = "Vary: Accept-Encoding";

            $this->_sendHeaders($headers);

            echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";

            $size = strlen($buffer);
            $crc = crc32($buffer);

            $buffer = gzcompress($buffer, 9);
            $buffer = substr($buffer, 0, strlen($buffer) - 4);

            echo $buffer;

            $this->_gzipPrintFourChars($crc);
            $this->_gzipPrintFourChars($size);
        } else {
            $this->_sendHeaders($headers);
            echo $data;
        }

        die;
    }

    protected function _gzipPrintFourChars($val) {
        for ($i = 0; $i < 4; $i++) {
            echo chr($val % 256);
            $val = floor($val / 256);
        }
    }

    protected function _sendHeaders($headers = array(), $break = false) {
        if (count($headers)) {
            foreach ($headers as $header) {
                header($header, true);
            }
        }

        if ($break === true) {
            die;
        }
    }

    public static function registerBind($controller_key, $module_key) {
        $controller_config = OSC::systemRegistry('controller');

        if (!isset($controller_config)) {
            $controller_config = array();
        } else if (isset($controller_config[$controller_key]) || isset($controller_config[$module_key])) {
            echo 'Controller key conflict';
            die;
        }

        $controller_config[$controller_key] = $module_key;
        $controller_config[$module_key] = $module_key;

        OSC::systemRegister('controller', $controller_config);
    }

    public static function registerDefaultRequestString($request_string) {
        OSC::systemRegister('default_request_string', $request_string);
    }

}
