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
 * @copyright   Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
define('OSC_CORE_PATH', dirname(__FILE__));
define('OSC_ROOT_PATH', dirname(OSC_CORE_PATH));
define('OSC_RES_PATH', OSC_ROOT_PATH . '/resource');
define('OSC_VAR_PATH', OSC_SITE_PATH . '/var');
define('OSC_LOG_PATH', OSC_VAR_PATH . '/logs');
define('OSC_STORAGE_PATH', OSC_SITE_PATH . '/storage');
define('OSC_LIB_PATH', OSC_ROOT_PATH . '/library');
define('OSC_COMP_PATH', OSC_ROOT_PATH . '/component');

define('OSC_LIB_APP_AMZ', OSC_COMP_PATH . '/community/amazon/app');

$envs = [
    'OSC_CORE_CACHE_REDIS' => 0,
    'OSC_CORE_CACHE_REDIS_HOST' => 'localhost',
    'OSC_CORE_CACHE_REDIS_PORT' => 6479,
    'OSC_FS_USERNAME' => posix_getpwuid(posix_geteuid())['name'],
    'OSC_ENV' => 'production',
    'OSC_ENV_PHP_LOG' => 0,
    'OSC_ENV_DEBUG_DB' => 0,
    'OSC_ENV_ALERT_SLOW_PROCESS' => 1,
    'OSC_ENV_FORCE_SSL' => 0,
    'OSC_REDIRECT_SSL_BY_PHP' => 1,
    'OSC_ENV_INC_W3' => 0,
    'OSC_ENV_SLOW_PROCESS_TIME_FLAG' => 5  // in seconds
];

if(! in_array($envs['OSC_FS_USERNAME'], ['apache','www-data'], true)) {
    $envs['OSC_FS_USERNAME'] = 'apache';
}

foreach ($envs as $key => $val) {
    if (!defined($key)) {
        define($key, $val);
    }
}

class OSC_Core {

    public static $request_controller = null;
    public static $request_router = null;
    public static $request_action = null;
    protected static $_trapped_errors = array();
    protected static $_hash = null;
    protected static $_using_hash_flag = false;
    public static $cookie = array('domain' => '', 'path' => '/');
    protected static $_module = null;
    protected static $_module_namespaces = array();
    protected static $_system_registry = array();
    protected static $_override = array();
    protected static $_class_map = array();
    protected static $_class_map_update_flag = false;
    protected static $_in_heavy_task_flag = false;
    protected static $_cdn_enabled = false;
    public static $base_url = null;
    public static $base = null;
    public static $domain = null;
    public static $post_base_url = null;
    public static $func_base_url = null;

    /**
     *
     * @var OSC_Controller
     */
    public static $_controller_obj = null;
    public static $current_controller;
    public static $current_router;
    public static $current_action;
    public static $request_path;
    protected static $_CORE_CONTROLLER = array(
        'cron' => 'cron'
    );
    
    protected static $_CORE_CACHE = null;

    const APP_NAME = 'OSC';
    const TPL_BUILD_MODE = 0;
    const TPL_USING_COMBINE = 0;
    const VERSION = '1.0.0';
    const DYNAMIC_LOAD_MODULE = 1;
    const REWRITE_ENABLED = 1;
    const SINGLETON = 'singleton';
    const DB_STATIC_LOAD_COLLECTION = 0;
    const MULTIPLE_SITE = 0;
    /* Time default is 1 month */
    const TTL_COOKIE = 2592000;

    public static function initialize() {
        static $initialized = false;

        if ($initialized) {
            return;
        }

        $initialized = true;

        spl_autoload_register(array('OSC', 'load'));

        include_once OSC_LIB_PATH . '/vendor/autoload.php';

        if (!isset($_SERVER['SERVER_NAME']) || !$_SERVER['SERVER_NAME']) {
            if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']) {
                $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
            } else {
                $_SERVER['SERVER_NAME'] = static::core('request')->get('__SERVER__');
            }
        }

        $_SERVER['SCRIPT_NAME'] = str_replace(OSC_SITE_PATH, '', $_SERVER['SCRIPT_NAME']);

        static::$domain = $_SERVER['SERVER_NAME'];
        static::$base = preg_replace('/^(\/+(.+))?(\/+(index|cli)\.php)$/', '\\2', $_SERVER['SCRIPT_NAME']);
        static::$base_url = static::$domain . (static::$base ? '/' . static::$base : '');

        if (OSC_ENV_INC_W3) {
            if (!preg_match('/^w{3}\./i', $_SERVER['HTTP_HOST'])) {
                static::$base_url = 'http' . (static::isSSL() ? 's' : '') . '://www.' . static::$base_url;

                OSC_Controller::moveTemporary(static::core('request')->rebuildUrl(array()));
            }
        }

        if (!static::isSSL() && OSC_ENV_FORCE_SSL && OSC_REDIRECT_SSL_BY_PHP) {
            OSC_Controller::moveTemporary('https://' . static::$base_url);
        }

        static::$base_url = 'http' . (static::isSSL() ? 's' : '') . '://' . static::$base_url;
        static::$func_base_url = static::$base_url;

        foreach (static::$_CORE_CONTROLLER as $controller => $processor) {
            OSC_Controller::registerBind($controller, $processor);
        }

        static::_collectModules();

        static::$_override = static::systemRegistry('override');

        $observers = static::systemRegistry('observer');

        if (is_array($observers)) {
            foreach ($observers as $observer) {
                static::core('observer')->addObserver($observer['event'], $observer['object'], $observer['priority'], $observer['key'], $observer['call_params'], $observer['once']);
            }
        }

        static::core('debug')->initiate();

        $observer = static::core('observer');

        register_shutdown_function(array($observer, 'shutdown'));

        $observer->addObserver('shutdown', array('OSC', 'shutdown'), -9998, 'osc_shutdown');

        $observer->dispatchEvent('initialize');
    }

    public static function coreCacheGet($key, $default = null) {
        if(static::$_CORE_CACHE === null) {
            if(static::DYNAMIC_LOAD_MODULE || substr(OSC_ENV, 0, 6) == 'local-') {
                //Do nothing
            } else if(OSC_CORE_CACHE_REDIS == 1) {
                $redis = new Redis();

                try {
                    if (!$redis->connect(OSC_CORE_CACHE_REDIS_HOST, OSC_CORE_CACHE_REDIS_PORT, .5)) {
                        throw new RedisException("Cannot connect to cache server");
                    }

                    $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
                    $redis->setOption(Redis::OPT_PREFIX, OSC_SITE_KEY ? OSC_SITE_KEY. ':' : '');

                    static::$_CORE_CACHE = static::decode($redis->get('CORE_CACHE'));

                    $redis->close();
                } catch (RedisException $e) {

                }
            } else {
                $module_cache_file = OSC_VAR_PATH . '/core/cache.php';

                if(! static::isRecaching() && file_exists($module_cache_file)) {
                    include_once $module_cache_file;
                    
                    static::$_CORE_CACHE = $_CACHE;
                }
            }

            if(! is_array(static::$_CORE_CACHE)) {
                static::$_CORE_CACHE = [];                
            }
        }

        return isset(static::$_CORE_CACHE[$key]) ? static::$_CORE_CACHE[$key] : $default;
    }

    public static function coreCacheSet($key, $value) {
        if(static::$_CORE_CACHE === null) {
            throw new Exception('Unable to set core cache before load');
        }
        
        static::register('OSC_REWRITE_CORE_CACHE', 1);

        static::$_CORE_CACHE[$key] = $value;
    }

    protected static function _addCronScheduler() {
        $cron_scheduler_list = static::systemRegistry('cron_scheduler');

        if (!is_array($cron_scheduler_list)) {
            return;
        }

        /* @var $cron OSC_Cron */
        $cron = static::core('cron');

        foreach ($cron_scheduler_list as $cron_scheduler) {
            try {
                $cron->addScheduler($cron_scheduler['cron_name'], $cron_scheduler['scheduler_data'], $cron_scheduler['timer'], $cron_scheduler['options']);
            } catch (Exception $ex) {
                static::core('debug')->triggerError('Cannot register scheduler: ' . print_r($cron_scheduler, 1) . "\n\n" . $ex->getMessage());
            }
        }
    }

    public static function getModuleInfo($keys = null) {
        static $module_info_classes = null;

        if($module_info_classes === null) {
            $module_info_classes = [];

            foreach(static::$_module as $k => $v) {
                $module_info_classes[$k] = (new OSC_ModuleInfo())->setInfo($v)->lock();
            }
        }

        if (!$keys) {
            return $module_info_classes;
        }

        if (!is_array($keys)) {
            $keys = array($keys);
        }

        $modules = array();

        foreach ($keys as $key) {
            if (isset($module_info_classes[$key])) {
                $modules[$key] = $module_info_classes[$key];
            }
        }

        return $modules;
    }

    public static function isRecaching() {
        static $_flag = null;

        if($_flag === null) {
            $core_recaching_flag = isset($_REQUEST['core_recaching_flag']) ? intval($_REQUEST['core_recaching_flag']) : 0;

            if(OSC_ENV != 'production') {
                $_flag = $core_recaching_flag > 0;
            } else {
                if($core_recaching_flag < 1) {
                    $_flag = false;
                } else {
                    $flag_file_path = OSC_VAR_PATH . '/recaching_flag/.' . $core_recaching_flag . '.flag';

                    if(file_exists($flag_file_path)) {
                        @unlink($flag_file_path);

                        $_flag = true;
                    } else {
                        $_flag = false;
                    }
                }
            }
        }

        return $_flag;
    }

    protected static function _collectModules() {
        if (static::$_module) {
            return;
        }

        $_CACHE = static::coreCacheGet('module');
        
        if (! $_CACHE) {
            static::$_module_namespaces = array();

            foreach (scandir(OSC_COMP_PATH) as $namespace) {
                if ($namespace == '.' || $namespace == '..' || !is_dir(OSC_COMP_PATH . '/' . $namespace)) {
                    continue;
                }

                static::$_module_namespaces[] = $namespace;
            }

            if (file_exists(OSC_SITE_PATH . '/register.php')) {
                include OSC_SITE_PATH . '/register.php';
            }

            if (static::MULTIPLE_SITE) {
                $modules = static::_collectMultiSiteModule();
            } else {
                $modules = static::_collectOneSiteModule();
            }

            foreach ($modules as $module) {
                if (file_exists($module['code_path'] . '/register.php')) {
                    include $module['code_path'] . '/register.php';
                }

                if (static::MULTIPLE_SITE && file_exists($module['site_path'] . '/register.php')) {
                    include $module['site_path'] . '/register.php';
                }
            }

            if (!static::DYNAMIC_LOAD_MODULE) {
                static::coreCacheSet('module', [
                    'module_namespaces' => static::$_module_namespaces,
                    'module' => $modules,
                    'registry' => static::$_system_registry
                ]);
            }

            static::_addCronScheduler();

            static::$_module = $modules;
        } else {
            static::$_module_namespaces = $_CACHE['module_namespaces'];
            static::$_module = $_CACHE['module'];
            static::$_system_registry = $_CACHE['registry'];
        }
    }

    protected static function _collectOneSiteModule() {
        $modules = array();

        foreach (static::$_module_namespaces as $namespace) {
            $module_root_path = OSC_COMP_PATH . '/' . $namespace;

            $dr = opendir($module_root_path);

            while (($module_dir = readdir($dr)) !== false) {
                if ($module_dir == '.' || $module_dir == '..' || !is_dir($module_root_path . '/' . $module_dir)) {
                    continue;
                }

                $module_key = strtolower($module_dir);

                if (isset($modules[$module_key])) {
                    continue;
                }

                $module = ['key' => $module_key, 'name' => $module_key];

                $module_path = $module_root_path . '/' . $module_dir;

                if (file_exists($module_path . '/info.php')) {
                    $module_info = [];

                    include $module_path . '/info.php';

                    $module = array_merge($module, $module_info);
                }

                $module['site_path'] = $module_path;
                $module['code_path'] = $module_path;

                $modules[$module_key] = $module;
            }

            closedir($dr);
        }

        return $modules;
    }

    protected static function _collectMultiSiteModule() {
        $module_site_root_path = OSC_SITE_PATH . '/component';
        $module_code_root_path = OSC_ROOT_PATH . '/component';

//        $dr = opendir($module_site_root_path);
        $dr = opendir($module_code_root_path);

        $modules = array();

        while (($module_dir = readdir($dr)) !== false) {
//            if ($module_dir == '.' || $module_dir == '..' || !is_dir($module_site_root_path . '/' . $module_dir)) {
            if ($module_dir == '.' || $module_dir == '..' || !is_dir($module_code_root_path . '/' . $module_dir)) {
                continue;
            }

            $module_key = strtolower($module_dir);

            if (isset($modules[$module_key])) {
                continue;
            }

            $module_code_path = null;

            foreach (static::$_module_namespaces as $namespace) {
                $module_code_path = $module_code_root_path . '/' . $namespace . '/' . $module_dir;

                if (file_exists($module_code_path) && is_dir($module_code_path)) {
                    break;
                }

                $module_code_path = null;
            }

            if (!$module_code_path) {
                continue;
            }

            $module = ['key' => $module_key, 'name' => $module_key];

            $module_path = $module_root_path . '/' . $module_dir;

            if (file_exists($module_code_path . '/info.php')) {
                $module_info = [];

                include $module_code_path . '/info.php';

                $module = array_merge($module, $module_info);
            }

            $module['site_path'] = $module_site_root_path . '/' . $module_dir;
            $module['code_path'] = $module_path;

            $modules[$module_key] = $module;
        }

        closedir($dr);

        return $modules;
    }

    public static function addOverride($orig_class, $new_class) {
        $override_config = static::systemRegistry('override');

        if (!is_array($override_config)) {
            $override_config = array();
        }

        $override_config[strtolower($orig_class)] = $new_class;

        static::systemRegister('override', $override_config);
    }

    public static function callWithErrorTrap() {
        static::$_trapped_errors = array();

        set_error_handler(array(OSC, 'errorTrap'));

        $args = func_get_args();

        if (count($args) < 1) {
            throw new Exception("No callback passed to callWithErrorTrap function");
        }

        $callback = array_shift($args);

        try {
            $result = call_user_func_array($callback, $args);
        } catch (Exception $ex) {
            restore_error_handler();
            throw $ex;
        }

        restore_error_handler();
        return $result;
    }

    public static function errorTrap($errno, $errstr, $errfile, $errline) {
        static::$_trapped_errors[] = array($errno, $errstr, $errfile, $errline);
    }

    public static function hasTrappedErrors() {
        return count(static::$_trapped_errors) > 0;
    }

    public static function getTrappedErrors() {
        return static::$_trapped_errors;
    }

    public static function setCDNFlag($flag) {
        OSC::$_cdn_enabled = $flag ? true : false;
    }

    public static function getSystemProcessId() {
        return getmypid();
    }

    /**
     * 
     * @return type
     */
    public static function systemRegister() {
        if (static::$_module) {
            throw new OSC_Exception("You don't allowed to register a variable to system registry after modules loaded");
        }

        $num_args = func_num_args();

        if ($num_args == 2) {
            $key = func_get_arg(0);

            if (!is_string($key) && !is_int($key)) {
                return;
            }

            $data = array($key => func_get_arg(1));
        } else if ($num_args == 1) {
            $data = func_get_arg(0);

            if (!is_array($data)) {
                return;
            }
        } else if ($num_args % 2 == 0) {
            $data = array();

            for ($idx = 0; $idx < $num_args; $idx++) {
                $key = func_get_arg($idx);

                $idx++;

                if (!is_string($key) && !is_int($key)) {
                    continue;
                }

                $data[$key] = func_get_arg($idx);
            }
        } else {
            return;
        }

        foreach ($data as $key => $value) {
            static::$_system_registry[$key] = $value;
        }
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public static function systemRegistry($key) {
        if (!isset(static::$_system_registry[$key])) {
            return null;
        }

        return static::$_system_registry[$key];
    }

    /**
     * 
     * @param mixed $prefix if set to true, OSC_SITE_KEY + uniqid
     * @param boolean $strongest if set to true, string length is 27, otherwise is 18
     * @return string
     */
    public static function makeUniqid($prefix = false, $strongest = false) {
        return (!$prefix || (!is_string($prefix) && !is_bool($prefix) && !is_int($prefix)) ? '' : (($prefix === true ? OSC_SITE_KEY : $prefix) . '_')) . ($strongest ? str_replace('.', static::randKey(5, 7), uniqid('', true)) : (static::randKey(5, 7) . uniqid()));
    }

    /**
     * 
     * @param string $dir
     * @param octal $chmod
     * @return boolean
     */
    public static function makeDir($dir, $chmod = 0755) {
        if (!file_exists($dir)) {
            $buff = array($dir);

            while (!file_exists(end($buff))) {
                $buff[] = dirname(end($buff));
            }

            if (!mkdir($dir, $chmod, true)) {
                return false;
            }

            $buff = array_reverse($buff, true);

            foreach ($buff as $path) {
                @chmod($path, $chmod);
                @chown($path, OSC_FS_USERNAME);
                @chgrp($path, OSC_FS_USERNAME);
            }
        }

        return true;
    }

    public static function removeDir($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (strpos(preg_replace('/\/{2,}/', '/', $dir), preg_replace('/\/{2,}/', '/', OSC_SITE_PATH)) === false) {
            return false;
        }

        exec('rm -rf ' . $dir);

        return !file_exists($dir);

        $dp = opendir($dir);

        while (($file = readdir($dp)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $path = $dir . '/' . $file;

            if (is_file($path)) {
                if (unlink($path)) {
                    continue;
                }
            } else {
                if (static::removeDir($dir)) {
                    continue;
                }
            }

            closedir($dp);

            return false;
        }

        closedir($dp);

        return true;
    }

    public static function imageIsNotCorrupt($image_path) {
        $extension = OSC::core('file')->verifyImage($image_path);

        if ($extension == 'jpg') {
            $img = @imagecreatefromjpeg($image_path);
        } else if ($extension == 'png') {
            $img = @imagecreatefrompng($image_path);
        } else if ($extension == 'gif') {
            $img = @imagecreatefromgif($image_path);
        } else {
            $img = false;
        }

        if (!$img) {
            throw new Exception("Invalid Image [{$image_path}]");
        }
    }

    /**
     * 
     * @param string $message
     * @param string $file
     * @param boolean $append_flag
     * @return boolean
     */
    public static function logFile($message, $file = null, $append_flag = true) {
        if (is_string($file)) {
            $file = preg_replace('/[^a-zA-Z0-9\_]/', '', $file);

            if (!$file) {
                return false;
            }

            $append_flag = $append_flag ? true : false;
        } else {
            $file = date('Y-m-d');
            $append_flag = true;
        }

        $message = "[" . date('h:i:s') . "] " . $message . PHP_EOL;
        return static::writeToFile(OSC_LOG_PATH . '/' . $file . '.log', $message, array('append' => $append_flag));
    }

    /**
     * 
     * @param string $file_path
     * @param string $data
     * @param array $opts
     * 
     * Options:
     * - append [bool][false]
     * - limit_try_write [int][1]
     * - chmod [octet]
     */
    public static function writeToFile($file_path, $data, $opts = array()) {
        if (!is_array($opts)) {
            $opts = array();
        }

        $chmod = 0644;

        if (isset($opts['chmod'])) {
            $chmod = $opts['chmod'];
        }

        static::makeDir(dirname($file_path));

        $flag = LOCK_EX;

        if ((isset($opts['append']) && $opts['append']) || in_array('append', $opts, true)) {
            $flag |= FILE_APPEND;
        }

        $counter = 1;

        if (isset($opts['limit_try_write'])) {
            $counter = intval($opts['limit_try_write']);
        }

        if ($counter < 1) {
            $counter = 1;
        } else if ($counter > 15) {
            $counter = 15;
        }

        while (!file_put_contents($file_path, $data, $flag)) {
            $counter--;

            if ($counter < 1) {
                return false;
            }

            static::sleep(25);
        }

        @chown($file_path, OSC_FS_USERNAME);
        @chgrp($file_path, OSC_FS_USERNAME);
        @chmod($file_path, $chmod);

        return true;
    }

    /**
     * Make a random key
     *
     * Modes:
     * 1 : All of character & number,
     * 2 : only number,
     * 3 : Only character,
     * 4 : Only lower character,
     * 5 : Only upper character,
     * 6 : Only number & lower character,
     * 7 : Only number & upper character
     *
     * @param  int $len
     * @param  bool $mode
     * @param  array   $special_chars
     * @return string
     */
    public static function randKey($len = 8, $mode = 1, $special_chars = null) {
        $key = '';

        switch (intval($mode)) {
            case 2:
                $chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
                break;
            case 3:
                $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
                    'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F',
                    'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
                    'W', 'X', 'Y', 'Z');
                break;
            case 4:
                $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
                    'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
                break;
            case 5:
                $chars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
                    'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
                break;
            case 6:
                $chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f',
                    'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
                    'w', 'x', 'y', 'z');
                break;
            case 7:
                $chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F',
                    'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
                    'W', 'X', 'Y', 'Z');
                break;
            default:
                $chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f',
                    'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
                    'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L',
                    'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        }

        if (is_array($special_chars) && count($special_chars) > 0) {
            $chars = array_merge($chars, $special_chars);
        }

        $count = count($chars) - 1;

        srand((double) microtime() * 1000000);

        for ($i = 0; $i < $len; $i++) {
            $key .= $chars[rand(0, $count)];
        }

        return $key;
    }

    /**
     * 
     * @param string $core
     * @param string $instance
     * @return OSC_Object
     */
    public static function core($core, $instance = 'singleton') {
        return static::obj(static::getObjClassName('core:' . $core), $instance);
    }

    /**
     * 
     * @param string $model
     * @param mixed $instance
     * @return OSC_Database_Model
     */
    public static function model($model, $instance = null) {
//        return static::obj(static::getObjClassName('model:' . $model), $instance);
        return static::obj(static::getObjClassName('model:' . $model), $instance ? $instance : static::makeUniqid(null, true));
    }

    /**
     * 
     * @param string $helper
     * @param mixed $instance
     * @return OSC_Object
     */
    public static function helper($helper, $instance = 'singleton') {
        return static::obj(static::getObjClassName('helper:' . $helper), $instance);
    }

    /**
     * 
     * @param string $cron
     * @param mixed $instance
     * @return OSC_Cron_Abstract
     */
    public static function cron($cron, $instance = 'singleton') {
        if (strpos($cron, '/') === false) {
            return static::core('cron_cron_' . $cron, $instance);
        }

        return static::obj(static::getObjClassName('cron:' . $cron), $instance);
    }

    /**
     * 
     * @param string $helper
     * @param mixed $instance
     * @return OSC_Controller
     */
    public static function controller($controller = null, $instance = 'singleton') {
        if (!$controller) {
            return static::$_controller_obj;
        }

        return static::obj(static::getObjClassName('controller:' . $controller), $instance);
    }

    public static function isCallable($function) {
        return static::getCallable($function, false) !== false;
    }

    public static function getCallable($function, $init_class_flag = false) {
        if (is_callable($function)) {
            return $function;
        }

        if (!is_array($function) || !isset($function[0]) || !isset($function[1]) || !is_string($function[1])) {
            return false;
        }

        if (!is_string($function[0])) {
            if (!is_object($function[0])) {
                return false;
            }

            return method_exists($function[0], $function[1]) ? array($function[0], $function[1]) : false;
        }

        $function[0] = explode(':', $function[0]);

        if (count($function[0]) == 1) {
            $instance = null;

            $class = $function[0][0];
        } else {
            if (count($function[0]) == 3) {
                $instance = $function[0][2] ? $function[0][2] : null;
            } else {
                $instance = static::SINGLETON;
            }

            $class = static::getObjClassName($function[0][0] . ':' . $function[0][1]);
        }

        return method_exists($class, $function[1]) ? array($init_class_flag ? static::obj($class) : $class, $function[1]) : false;
    }

    public static function call($function) {
        $callable = static::getCallable($function);

        if (!$callable) {
            throw new Exception("The function be passed is not callable:\n" . print_r($function));
        }

        $params = func_get_args();

        array_shift($params);

        return call_user_func_array($callable, $params);
    }

    public static function getObjClassName($obj_name) {
        $obj_name = explode(':', $obj_name);

        if (count($obj_name) != 2) {
            static::core('debug')->triggerError('Obj name [' . implode(':', $obj_name) . '] is incorrect');
        }

        if ($obj_name[0] == 'core') {
            return 'OSC_' . $obj_name[1];
        }

        switch ($obj_name[0]) {
            case 'cron':
                $obj_name[0] = 'Cron';
                break;
            case 'helper':
                $obj_name[0] = 'Helper';
                break;
            case 'model':
                $obj_name[0] = 'Model';
                break;
            case 'abstract':
                $obj_name[0] = 'Abstract';
                break;
            case 'interface':
                $obj_name[0] = 'Interface';
                break;
            case 'controller':
                $obj_name[0] = 'Controller';
                break;
            default:
                static::core('debug')->triggerError('Obj type [' . $obj_name[0] . '] is not standard type');
        }

        return $obj_name[0] . '_' . str_replace('/', '_', $obj_name[1]);
    }

    /**
     * 
     * @staticvar array $instances
     * @param string $class
     * @param string $instance
     * @param mixed $params
     * @return class
     */
    public static function obj($class, $instance = null, $params = null) {
        static $instances = array();

        $class = preg_replace('/_{2,}/', '_', $class);

        $class = static::getOverride($class);

        if ($instance && isset($instances[$class . '.' . $instance])) {
            return $instances[$class . '.' . $instance];
        }

        if (!static::classExists($class, false)) {
            static::core('debug')->triggerError('Class ' . $class . ' not found');
        }

        $object = new $class($params);

        if (method_exists($object, 'setInstanceKey')) {
            $object->setInstanceKey($instance);
        }

        if ($instance) {
            $instances[$class . '.' . $instance] = $object;
        }

        return $object;
    }

    public static function getOverride($class) {
        if (isset(static::$_override[strtolower($class)])) {
            return static::getOverride(static::$_override[strtolower($class)]);
        }

        return $class;
    }

    public static function classExists($class_name, $check_override = true) {
        if ($check_override) {
            $class_name = static::getOverride($class_name);
        }

        if (class_exists($class_name, false)) {
            return true;
        }

        $file_path = static::getFilePathByClassName($class_name);

        return file_exists($file_path);
    }

    /**
     * 
     * @staticvar array $class_map
     * @param string $class_name
     * @param boolean $get_real_path
     * @return boolean
     */
    public static function getFilePathByClassName($class_name, $get_real_path = true) {
        static $class_map = null;

        if ($class_map === null) {
            $class_map = [
                'system' => [], //'system' => static::coreCacheGet('class_file_map', []),
                'register' => []
            ];
        }

        if (static::$_module) {
            $autoload_filepath = static::systemRegistry('autoload_filepath_data');

            if (is_array($autoload_filepath)) {
                $class_map['register'] = $autoload_filepath;
            }
        }

        $class_file_path = false;

        $lower_class_name = strtolower($class_name);

        if (isset($class_map['system'][$lower_class_name])) {
            $class_file_path = $class_map['system'][$lower_class_name];
        } else if (isset($class_map['register'][$lower_class_name])) {
            $class_file_path = $class_map['register'][$lower_class_name];
        } else if (preg_match('/^([a-zA-Z0-9]+)_(.+)$/s', $class_name, $matches)) {
            $dirs = '';

            switch (strtolower($matches[1])) {
                case 'osc':
                    $dirs = 'core/';
                    break;
                default:
                    $dirs = array();

                    foreach (static::$_module_namespaces as $namespace) {
                        $dirs[] = 'component/' . $namespace . '/';
                    }

                    $matches[2] = preg_replace('/^([a-zA-Z0-9]+)_/', '\\1_' . $matches[1] . '_', $matches[2]);
                    break;
            }

            $matches[2] = explode('_', $matches[2]);

            $path = array();

            foreach ($matches[2] as $v) {
                $path[] = substr_replace($v, strtolower(substr($v, 0, 1)), 0, 1);
            }

            $path = implode('/', $path);

            if (!is_array($dirs)) {
                $dirs = array($dirs);
            }

            foreach ($dirs as $dir) {
                $_class_file_path = "{$dir}{$path}.php";

                if (file_exists(OSC_ROOT_PATH . '/' . $_class_file_path)) {
                    $class_file_path = $_class_file_path;
                    break;
                }
            }

            if ($class_file_path) {
                $class_map['system'][$lower_class_name] = $class_file_path;
                static::$_class_map = &$class_map['system'];
                static::$_class_map_update_flag = true;
            }
        }

        if ($class_file_path) {
            $class_file_path = ($get_real_path ? OSC_ROOT_PATH . '/' : '') . $class_file_path;
        }

        return $class_file_path;
    }

    public static function registerAutoloadFilepathData($data) {
        if (!is_array($data)) {
            return;
        }

        $filepath_data = static::systemRegistry('autoload_filepath_data');

        if (!is_array($filepath_data)) {
            $filepath_data = array();
        }

        foreach ($data as $class_name => $filepath) {
            $filepath_data[strtolower($class_name)] = $filepath;
        }

        static::systemRegister('autoload_filepath_data', $filepath_data);
    }

    /**
     * Delay execution in milliseconds
     *
     * @param mixed $millisecond
     */
    public static function sleep($millisecond = null) {
        if (is_array($millisecond)) {
            if (count($millisecond) == 2) {
                $millisecond[0] = intval($millisecond[0]);
                $millisecond[1] = intval($millisecond[1]);

                if ($millisecond[0] < 1) {
                    $millisecond[0] = 1;
                }

                if ($millisecond[1] < 1) {
                    $millisecond[1] = 1;
                }

                if ($millisecond[0] == $millisecond[1]) {
                    $millisecond = $millisecond[0];
                } else {
                    if ($millisecond[0] > $millisecond[1]) {
                        $millisecond = rand($millisecond[1], $millisecond[0]);
                    } else {
                        $millisecond = rand($millisecond[0], $millisecond[1]);
                    }
                }
            } else {
                $buff = array();

                foreach ($millisecond as $v) {
                    $v = intval($v);

                    if ($v > 0 && !in_array($buff)) {
                        $buff[] = $v;
                    }
                }

                if (count($buff) < 1) {
                    $millisecond = 1;
                } else {
                    $millisecond = $buff[array_rand($buff)];
                }
            }
        } else if ($millisecond !== null) {
            $millisecond = intval($millisecond);
        } else {
            $millisecond = rand(1, 1000);
        }

        if ($millisecond < 1) {
            $millisecond = 1;
        }

        usleep($millisecond * 1000);
    }

    public static function shutdown() {
        if(static::$_CORE_CACHE !== null && static::registry('OSC_REWRITE_CORE_CACHE')) {
            if(OSC_CORE_CACHE_REDIS == 1) {
                $redis = new Redis();

                try {
                    if (!$redis->connect(OSC_CORE_CACHE_REDIS_HOST, OSC_CORE_CACHE_REDIS_PORT, .5)) {
                        throw new RedisException("Cannot connect to cache server");
                    }

                    $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
                    $redis->setOption(Redis::OPT_PREFIX, OSC_SITE_KEY ? OSC_SITE_KEY. ':' : '');

                    $redis->set('CORE_CACHE', static::encode(static::$_CORE_CACHE));

                    $redis->close();
                } catch (RedisException $e) {

                }
            } else {
                static::writeToFile(OSC_VAR_PATH . '/core/cache.php', static::core('string')->toPHP(static::$_CORE_CACHE, '_CACHE'), ['chmod' => 0600]);
            }
        }

        static::updateClassFileMap();

        static::core('observer')->shutdown();
    }

    public static function registry($key) {
        return static::core('object')->registry($key);
    }

    public static function register($key, $value) {
        static::core('object')->register($key, $value);
    }

    public static function updateClassFileMap() {
        if (is_array(static::$_class_map) && static::$_class_map_update_flag) {
            //static::coreCacheSet('class_file_map', static::$_class_map);
        }
    }

    public static function getRequestString() {
        $req = static::core('request');

        if ($req->get('__request_path')) {
            $request_string = $req->get('__request_path');

            $req->remove('__request_path');

            return $request_string;
        }

        if (static::isCli()) {
            $request_string = $req->get('__controller') . '/' . $req->get('__router') . '/' . $req->get('__action');

            $req->remove('__controller', '__router', '__action');
        } else {
            $request_string = urldecode(isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] ? $_SERVER['PATH_INFO'] : preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']));

            $request_string = preg_replace('/^\/+|\/$/', '', $request_string);
            $request_string = preg_replace('/\/{2,}/', '/', $request_string);

            $root_string = preg_replace('/^((\/+)?(.+))?\/+index\.php$/', '\\3/', $_SERVER['SCRIPT_NAME']);

            if (preg_match("/^" . preg_quote($root_string, '/') . "/", $request_string)) {
                $root_pos = strlen($root_string);

                if ($root_pos == 1) {
                    $root_pos = 0;
                }

                $request_string = substr($request_string, $root_pos);
            }

            $request_string = preg_replace('/^index\.php\/*(.+)?$/', '\\2', $request_string);
        }

        return $request_string;
    }

    protected static function _parseRequestString($request_string) {
        $controller = false;
        $router = false;
        $action = false;

        $request_string = explode('/', $request_string);

        $controller_binds = static::systemRegistry('controller');

        $controller = $request_string[0];

        unset($request_string[0]);

        if (!isset($controller_binds[$controller])) {
            throw new Exception('Controller is not defined', 404);
        }

        $controller_module = $controller_binds[$controller];

        if (isset(static::$_CORE_CONTROLLER[$controller])) {
            $request_string = implode('/', $request_string);

            static::core(static::$_CORE_CONTROLLER[$controller])->processController($router, $action, $request_string);

            $request_string = $request_string ? explode('/', $request_string) : array();
        } else {
            $request_string = implode('/', $request_string);

            if (static::classExists('Helper_' . $controller_module . '_Alias')) {
                static::helper($controller_module . '/alias')->process($request_string, $router, $action);
            }

            $request_string = $request_string ? explode('/', $request_string) : array();

            if (!$router) {
                $router = 'index';
                $action = 'index';

                foreach (array(0 => 'router', 1 => 'action') as $k => $v) {
                    if (!isset($request_string[$k])) {
                        break;
                    }

                    ${$v} = $request_string[$k];

                    unset($request_string[$k]);
                }
            }
        }

        if (isset(static::$_CORE_CONTROLLER[$controller])) {
            $controller_class = 'OSC_' . $controller_module . '_Controller_' . $router;
        } else {
            $controller_class = 'Controller_' . $controller_module . '_' . $router;
        }

        if (!static::classExists($controller_class)) {
            throw new Exception('Controller is not exist', 404);
        }

        if (!method_exists($controller_class, 'action' . $action)) {
            throw new Exception('Action is not exist', 404);
        }

        $request_string = implode('/', $request_string);

        if ($request_string) {
            $question_mark_pos = strpos($request_string, '?');

            if ($question_mark_pos !== false) {
                $request_string = substr($request_string, 0, $question_mark_pos);
            }

            if ($request_string) {
                static::core('request')->parseGetRequest($request_string);
            }
        }

        static::$current_controller = $controller;
        static::$current_router = $router;
        static::$current_action = $action;

        static::$request_path = static::$current_controller . '/' . static::$current_router . '/' . static::$current_action;
    }

    protected static function _cleanRequestString(&$request_string) {
        $request_string = preg_replace('/\/{2,}/', '/', $request_string);
        $request_string = preg_replace('/(^\/|\/$)/', '', $request_string);
    }

    public static function process($request_string = null) {
        if ($request_string === null || $request_string === false) {
            $request_string = static::getRequestString();
        }

        static::_cleanRequestString($request_string);

        if (!$request_string || $request_string === OSC::core('language')->getCurrentLanguageKey()) {
            $request_string = static::systemRegistry('default_request_string');
        }

        if (!$request_string) {
            static::core('debug')->triggerError('No default controller');
        }

        $process_alias_flag = false;
        try {
            if ($request_string == static::systemRegistry('default_request_string')) {
                static::_parseRequestString($request_string);
            } else {
                $process_alias_flag = true;
            }
        } catch (Exception $ex) {
            $process_alias_flag = true;
        }

        if ($process_alias_flag) {
            static::core('controller_alias')->process($request_string);
            static::_parseRequestString($request_string);
        }

        static::_callProcessor();
    }

    public static function forward($action_path) {
        $action_path = static::preActionPath($action_path);

        if (!$action_path) {
            return false;
        }

        $action_path = explode('/', $action_path);

        static::_setController($action_path[0]);
        static::_setRouter($action_path[1]);
        static::_setAction($action_path[2]);

        static::_callProcessor();
    }

    public static function getControllerModuleKey() {
        $controller_binds = static::systemRegistry('controller');

        return $controller_binds[static::$current_controller];
    }

    public static function getCurrentRouter() {
        return static::$current_router;
    }

    public static function getCurrentAction() {
        return static::$current_action;
    }

    protected static function _setController($controller) {
        static::$current_controller = $controller;
    }

    protected static function _setRouter($router) {
        static::$current_router = $router;
    }

    protected static function _setAction($action) {
        static::$current_action = $action;
    }

    protected static function _callProcessor() {
        $controller = array_search(static::$current_controller, static::systemRegistry('controller'));

        if (!$controller) {
            throw new OSC_Exception_Runtime('The controller [' . static::$current_controller . '] is not exists');
        }

        if (isset(static::$_CORE_CONTROLLER[static::$current_controller])) {
            $processor_class_name = 'OSC_' . $controller . '_Controller_' . static::$current_router;
        } else {
            $processor_class_name = 'Controller_' . $controller . '_' . static::$current_router;
        }

        static::$_controller_obj = static::obj($processor_class_name);

        $action = 'action' . static::$current_action;

        static::$_controller_obj->$action();

        die;
    }

    public static function load($class_name) {
        if (class_exists($class_name, false)) {
            return true;
        }

        $class_file_path = static::getFilePathByClassName($class_name, false);

        return $class_file_path && static::import($class_file_path);
    }

    /**
     * 
     * @staticvar array $loaded
     * @param mixed $paths
     * @param boolean $once
     * @return boolean
     */
    public static function import($paths, $once = false) {
        static $loaded = array();

        if (!is_array($paths)) {
            $paths = array($paths);
        }

        $failed = 0;

        foreach ($paths as $path) {
            $path = OSC_ROOT_PATH . '/' . $path;

            $lower_path = strtolower($path);

            if (!isset($loaded[$lower_path])) {
                if (file_exists($path)) {
                    $loaded[$lower_path] = include $path;
                } else {
                    $loaded[$lower_path] = false;
                }
            } else if (!$once && $loaded[$lower_path]) {
                include $path;
            }

            if (!$loaded[$lower_path]) {
                $failed++;
            }
        }

        return $failed < 1;
    }

    /**
     * 
     * @return Helper_Frontend_Template
     */
    public static function template() {
        return static::controller()->getTemplate();
    }

    public static function getTmpDir() {
        if (function_exists('sys_get_temp_dir')) {
            $path = sys_get_temp_dir();
        } else if ($path = getenv('TMP')) {
            
        } else if ($path = getenv('TEMP')) {
            
        } else if ($path = getenv('TMPDIR')) {
            
        } else {
            $path = tempnam(__FILE__, '');

            if (file_exists($path)) {
                unlink($path);
                $path = dirname($path);
            }
        }

        if (!$path) {
            return null;
        }

        return realpath($path);
    }

    public static function urlEncodeParams($name, $value) {
        if (!is_array($value)) {
            return $name . '=' . urlencode($value);
        }

        foreach ($value as $k => $v) {
            $value[$k] = static::urlEncodeParams($name . "[{$k}]", $v);
        }

        return implode('&', $value);
    }

    public static function touchUrl($url, $params = []) {
        $url = preg_replace('/#.+$/i', '', $url);

        if (!is_array($params)) {
            $params = [];
        }

//        $params[OSC_Controller::TOKEN_FIELD_NAME] = OSC_Controller::makeApiToken(null, false);

        foreach ($params as $k => $v) {
            $params[$k] = static::urlEncodeParams($k, $v);
        }

        $params = implode('&', $params);

        if ($params) {
            $url .= ((strpos($url, '?') === false) ? '?' : '&') . $params;
        }

        exec("curl -g -X GET " . escapeshellarg($url) . " > /dev/null 2>&1 &");

        return;

        $post_params = array();

        foreach ($params as $key => &$val) {
            if (is_array($val)) {
                $val = implode(',', $val);
            }

            $post_params[] = $key . '=' . urlencode($val);
        }

        $post_string = implode('&', $post_params);

        $parts = parse_url($url);

        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80);

        $out = "POST " . $parts['path'] . " HTTP/1.1\r\n";
        $out .= "Host: " . $parts['host'] . "\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";

        if (isset($post_string)) {
            $out .= $post_string;
        }

        fwrite($fp, $out);
        fclose($fp);
    }

    public static function encode($data) {
        return json_encode($data/* , JSON_UNESCAPED_UNICODE */);
    }

    public static function decode($data, $decode_to_array = true) {
        return json_decode($data, $decode_to_array);
    }

    public static function silentCallAction($action, $params = array(), $log_path = false) {
        if ($log_path) {
            $log_path .= '/silentCall.' . md5($action . ':' . serialize($params)) . '_' . time();
        } else {
            $log_path = '/dev/null';
        }

        exec('php -f ' . static::getCliUrl($action, $params) . ' >> ' . $log_path . ' 2>&1');
    }

    public static function getCliUrl($action, $params = array()) {
        $command = array(OSC_SITE_PATH . '/index.php');

        if (!is_array($params)) {
            $params = array();
        }

        $params['__request_path'] = $action;
        $params['__SERVER__'] = $_SERVER['SERVER_NAME'];

        foreach ($params as $k => $v) {
            $command[] = escapeshellarg($k . '=' . $v);
        }

        return implode(' ', $command);
    }

    public static function isUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function isFilePath($file_path) {
        return file_exists($file_path);
    }

    public static function isSSL() {
        $ssl_flag = null;

        if ($ssl_flag === null) {
            $ssl_flag = ((OSC_ENV_FORCE_SSL && static::isCli()) || isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['https']) && $_SERVER['https'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
        }

        return $ssl_flag;
    }

    public static function isCli() {
        $cli_flag = null;

        if ($cli_flag === null) {
            $cli_flag = false;

            if (defined('STDIN')) {
                $cli_flag = true;
            } else if (php_sapi_name() === 'cli') {
                $cli_flag = true;
            } else if (isset($_ENV['SHELL'])) {
                $cli_flag = true;
            } else if (!isset($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT'])) {
                $cli_flag = true;
            } else if (isset($_SERVER['argv']) && count($_SERVER['argv']) > 0) {
                $cli_flag = true;
            } else if (!isset($_SERVER['REQUEST_METHOD'])) {
                $cli_flag = true;
            }
        }

        return $cli_flag;
    }

    public static function isAjax() {
        $ajax_flag = null;

        if ($ajax_flag === null) {
            $ajax_flag = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
        }

        return $ajax_flag;
    }

    public static function isCrawlerRequest() {
        return static::getBrowser()->isType('bot');
    }

    public static function getBrowser() {
        static $info = null;

        if ($info === null) {
            $info = new WhichBrowser\Parser(getallheaders());
        }

        return $info;
    }

    public static function getCrawlerName() {
        
    }

    public static function getServerIp() {
        static $server_ip = null;

        if ($server_ip === null) {
            if (isset($_SERVER['SERVER_ADDR'])) {
                $server_ip = $_SERVER['SERVER_ADDR'];
            } else {
                $server_ip = gethostbyname(gethostname());
            }

            if (!preg_match('/^\d+\.\d+\.\d+\.\d+$/', $server_ip) && function_exists('exec')) {
                $get_command_line = exec('echo $(hostname -I)');

                if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $get_command_line)) {
                    $server_ip = $get_command_line;
                }
            }
        }

        return $server_ip;
    }

    /**
     * Detect IP of client OR exit program
     *
     * @return void
     */
    public static function getClientIP() {
        static $client_ip = null;

        /* Comment for split FE&BE */
        /*static $cookie_client_ip = null;*/

        if(defined('OSC_CLIENT_IP')) {
            return OSC_CLIENT_IP;
        }

        if ($client_ip === null) {
            /* Comment for split FE&BE */
            /*$cookie_client_ip = OSC::cookieGet(OSC_Controller::makeRequestChecksum('ClientIp', OSC_SITE_KEY));

            if ($cookie_client_ip) {
                $cookie_client_ip = OSC::core('encode')->decode(base64_decode($cookie_client_ip), OSC_SITE_KEY);

                if (! preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $cookie_client_ip)) {
                    $cookie_client_ip = null;
                }
            } else {
                $cookie_client_ip = null;
            }*/

            $addrs = [];

            foreach (explode(',', static::getEnv('HTTP_X_FORWARDED_FOR')) as $x_f) {
                $x_f = trim($x_f);

                if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $x_f)) {
                    $addrs[] = $x_f;
                }
            }

            $addrs[] = static::getEnv('HTTP_CLIENT_IP');
            $addrs[] = static::getEnv('HTTP_X_CLUSTER_CLIENT_IP');
            $addrs[] = static::getEnv('HTTP_PROXY_USER');
            $addrs[] = static::getEnv('REMOTE_ADDR');

            $client_ip = '';

            foreach ($addrs as $ip) {
                if (!$ip || !preg_match("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/", $ip, $matches)) {
                    continue;
                }

                $client_ip = $matches[1] . '.' . $matches[2] . '.' . $matches[3] . '.' . $matches[4];

                break;
            }
            /* Comment for split FE&BE */
            /*if(! $cookie_client_ip) {
                $cookie_client_ip = $client_ip;
            }*/
        }

        /* Comment for split FE&BE */
        /*return $skip_cookie_ip ? $client_ip : $cookie_client_ip*/
        return $client_ip;
    }

    /**
     *
     * @param string $ip 
     * @return mixed
     */
    public static function cleanIpAddress($ip) {
        $arr = explode('.', $ip);

        $ip = array();

        if (count($arr) != 4) {
            $ip = false;
        } else {
            foreach ($arr as $segment) {
                $segment = intval($segment);

                if ($segment > 0) {
                    $ip[] = $segment;
                }
            }

            if (count($ip) != 4) {
                $ip = false;
            } else {
                $ip = implode('.', $ip);
            }
        }

        return $ip;
    }

    public static function processSystemError($log_id, $error_message) {
        
    }

    public static function alertSlowProcess($log_id, $process_time) {
        
    }

    public static function isInHeavyTask() {
        return static::$_in_heavy_task_flag;
    }

    public static function setInHeavyTaskFlag($flag = true) {
        static::$_in_heavy_task_flag = $flag ? true : false;
    }

    public static function getFormatedSize($size) {
        $sizeUnit = array('kB', 'MB', 'GB', 'TB', 'PB', 'EB');

        $i = -1;

        do {
            $size = $size / 1024;
            $i++;
        } while ($size > 99);

        return round(max(array($size, 0.1)), 2) . $sizeUnit[$i];
    }

    /**
     * 
     * @param string $html
     * @return \DOMDocument
     */
    public static function makeDomFromContent($html) {
        $html = trim($html);

        if (!$html) {
            throw new Exception('HTML content is empty', 404);
        }

        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        foreach ($document->childNodes as $item) {
            if ($item->nodeType == XML_PI_NODE) {
                $document->removeChild($item);
            }
        }

        $document->encoding = 'UTF-8';

        return $document;
    }

    /**
     * Get environment variable
     *
     * @param  String $key Name of environment variable
     * @return Mixed Value of environment variable
     */
    public static function getEnv($key) {
        $return = array();

        if (is_array($_SERVER) && count($_SERVER)) {
            if (isset($_SERVER[$key])) {
                $return = $_SERVER[$key];
            }
        }

        if (!$return) {
            $return = getenv($key);
        }

        return $return;
    }

    public static function sessionGet($key) {
        if (!session_id()) {
            session_start();
        }

        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public static function sessionSet($key, $value, $ttl = null) {
        if (!session_id()) {
            session_start();
        }

        $_SESSION[$key] = $value;
    }

    public static function sessionRemove($key) {
        if (!session_id()) {
            session_start();
        }

        unset($_SESSION[$key]);
    }

    /**
     * 
     * @param string $key
     * @param string $val
     * @param integer $ttl
     */
    public static function cookieSet($key, $val, $ttl = null, $path = null, $domain = null) {
        if ($ttl === null) {
            $ttl = self::TTL_COOKIE;
        }

        setcookie($key, $val, $ttl === false ? 0 : (time() + intval($ttl)), $path ? $path : static::$cookie['path'], $domain ? $domain : static::$cookie['domain']);

        $_COOKIE[$key] = $val;
    }

    /**
     * 
     * @param string $key
     * @param string $val
     * @param integer $ttl
     */
    public static function cookieSetSiteOnly($key, $val, $ttl = null) {
        return static::cookieSet($key, $val, $ttl, '/' . static::$base, '.' . static::$domain);
    }

    public static function cookieSetCrossSite($key, $val, $ttl = null) {
        return static::cookieSet($key, $val, $ttl, '/', '.' . OSC_FRONTEND_DOMAIN);
    }

    /**
     * 
     * @param string $key
     * @return string
     */
    public static function cookieGet($key) {
        if (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        }

        return null;
    }

    /**
     * Unset a cookie variable
     *
     * @param  String $key Variable index
     * @return void
     */
    public static function cookieRemove($key) {
        setcookie($key, '', time() - (60 * 60 * 24), static::$cookie['path'], static::$cookie['domain']);

        unset($_COOKIE[$key]);
    }

    /**
     * Unset a cookie variable
     *
     * @param  String $key Variable index
     * @return void
     */
    public static function cookieRemoveSiteOnly($key) {
        setcookie($key, '', time() - (60 * 60 * 24), '/' . static::$base, '.' . static::$domain);

        unset($_COOKIE[$key]);
    }

    public static function cookieRemoveCrossSite($key) {
        setcookie($key, '', time() - (60 * 60 * 24), '/', '.' . OSC_FRONTEND_DOMAIN);

        unset($_COOKIE[$key]);
    }

    public static function getCurrentUrl() {
        return static::rebuildUrl([]);
    }

    public static function rebuildUrl($params) {
        return static::getUrl(null, array_merge(static::core('request')->getAll('get'), $params));
    }

    public static function getUrl($action_path = null, $params = [], $inc_hash = null) {
        return static::$base_url . '/' . static::getAbsoluteUrl($action_path, $params, $inc_hash);
    }

    public static function getUrlWithRewrite($rewrite_path = null, $params = [], $inc_hash = null) {
        return static::$base_url . '/' . static::_completeUrl($rewrite_path, $params, $inc_hash);
    }

    public static function preActionPath($action_path) {
        if (!is_string($action_path) || !$action_path) {
            $action_path = '*/*/*';
        }

        $segments = explode('/', $action_path);

        if (count($segments) < 3) {
            for ($i = count($segments); $i < 3; $i++) {
                array_unshift($segments, '*');
            }
        }

        $default = array('current_controller', 'current_router', 'current_action');

        foreach ($segments as $k => $segment) {
            if ($segment != '*') {
                break;
            }

            $segments[$k] = static::${$default[$k]};
        }

        $action_path = implode('/', $segments);

        if (!preg_match('/^[a-zA-Z0-9_]+\/[a-zA-Z0-9_]+\/[a-zA-Z0-9]+$/', $action_path)) {
            return '';
        }

        return $action_path;
    }

    public static function getAbsoluteUrl($action_path = null, $params = array(), $inc_hash = null) {
        $action_path = static::preActionPath($action_path);

        if (!$action_path) {
            return static::_completeUrl('');
        }

        return static::_completeUrl($action_path, $params, $inc_hash);
    }

    protected static function _completeUrl($action_path, $params = [], $inc_hash = null) {
        $url = '';

        if ($action_path) {
            $url .= $action_path;

            if (!is_array($params)) {
                $params = static::core('request')->getAll('get');
            }

            if ($inc_hash || ($inc_hash === null && static::getUsingHashFlag())) {
                if (!is_array($params)) {
                    $params = array();
                }

                $params['hash'] = static::getHash();
            }

            $request_params = static::core('request')->buildRequest($params);

            if ($request_params) {
                $url .= '/' . $request_params;
            }
        }

        //$url = static::core('language')->getCurrentLanguageKey() . '/' . $url;

        if (!static::REWRITE_ENABLED) {
            $url = 'index.php/' . $url;
        }

        return $url;
    }

    public static function setUsingHashFlag($flag = true) {
        static::$_using_hash_flag = $flag ? true : false;
    }

    public static function getUsingHashFlag() {
        return static::$_using_hash_flag;
    }

    public static function getHash() {
        return static::$_hash['key'];
    }

    public static function preHash($check_hash, $hash_failed_forward) {
        if (!static::_validateHash($check_hash)) {
//            OSC::core('observer')->dispatchEvent('hash_verify_failed');
            static::forward($hash_failed_forward ? $hash_failed_forward : static::systemRegistry('default_controller'));
        }
    }

    /**
     * 
     * @param boolean $check_hash
     * @return boolean
     */
    protected static function _validateHash($check_hash) {
        if (static::$_hash !== null) {
            return true;
        }

        static::$_hash = static::sessionGet('request_hash');

        if (!is_array(static::$_hash)) {
            static::$_hash = array(
                'key' => md5(static::makeUniqid(true, true)),
                'expire' => 0
            );
        }

        $hash_key = (string) static::core('request')->get('hash');

        if ($hash_key && static::$_hash['key'] == $hash_key) {
            if (static::$_hash['expire'] < time()) {
                $hash_key = '';
            }
        } else {
            $hash_key = '';
        }

        static::$_hash['expire'] = time() + 60 * 60;

        static::sessionSet('request_hash', static::$_hash);

        if (!$hash_key && $check_hash) {
            return false;
        }

        return true;
    }

    public static function safeString($txt) {
        return htmlspecialchars($txt, ENT_QUOTES | ENT_HTML401, "UTF-8");
//        return htmlentities($txt, ENT_COMPAT | ENT_HTML401, 'UTF-8');
    }

    public static function getObjectType($object) {
        return static::getObjectTypeByClassName(get_class($object));
    }

    public static function getObjectTypeByClassName($class) {
        if (preg_match('/^(helper|model|controller|cron)_([a-zA-Z0-9]+)_([a-zA-Z0-9].+)/i', $class, $matches)) {
            return ['type' => strtolower($matches[1]), 'name' => $matches[2] . '/' . $matches[3]];
        } else if (preg_match('/^osc_([a-zA-Z0-9].+)/i', $class, $matches)) {
            return ['type' => 'core', 'name' => $matches[1]];
        }

        return ['type' => 'class', 'name' => $class];
    }

    public static function enableCDN() {
        $CDN_CONFIG = static::systemRegistry('CDN_CONFIG');

        if (!$CDN_CONFIG ||
            !is_array($CDN_CONFIG) ||
            !isset($CDN_CONFIG['enable']) ||
            !$CDN_CONFIG['enable'] ||
            !isset($CDN_CONFIG['base_url']) ||
            !$CDN_CONFIG['base_url'] ||
            ! OSC::$_cdn_enabled
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param $files
     * @return array|mixed|string|void
     * @throws Exception
     */
    public static function wrapCDN($files) {
        $CDN_CONFIG = static::systemRegistry('CDN_CONFIG');

        if (!static::enableCDN()) {
            return $files;
        }

        if(isset($_REQUEST['test_cdn']) && $_REQUEST['test_cdn'] == 1) {
            var_dump($files);die;
        }

        $single_return = false;

        if (!is_array($files)) {
            $single_return = true;
            $files = [$files];
        }

        $s3_bucket_url = OSC::core('aws_s3')->getS3BucketUrl();
        $font_path = '/' . OSC::getStoreInfo()['store_id'] . '/storage/personalizedDesign/fonts';
        $s3_fonts_url = $s3_bucket_url . $font_path;
        foreach ($files as $idx => $file) {
            /* Load file with cdn if file is font */
            if (substr($file, 0, strlen($s3_fonts_url)) == $s3_fonts_url) {
                $files[$idx] = $CDN_CONFIG['base_url'] . $font_path . substr($file, strlen($s3_fonts_url));
                continue;
            }

            /* Load file with imagekit if file is other */
            if (substr($file, 0, strlen($s3_bucket_url)) == $s3_bucket_url) {
                $files[$idx] = $CDN_CONFIG['imagekit_url'] . substr($file, strlen($s3_bucket_url));
                continue;
            }

            if (!preg_match('/^https?:\/\/' . preg_quote(static::$domain) . '(\/.+)$/i', $file, $matches)) {
                continue;
            }

            $files[$idx] = $CDN_CONFIG['imagekit_url'] . $matches[1];
        }

        return $single_return ? $files[0] : $files;
    }

    public static function getShop() {
        return OSC::helper('shop/common')->getShop();
    }

    public static function isPrimaryStore(): bool
    {
        return OSC_PRIMARY_STORE == 1;
    }

    public static function unwrapCDN($files) {
        $CDN_CONFIG = static::systemRegistry('CDN_CONFIG');

        if (!$CDN_CONFIG || !is_array($CDN_CONFIG) || !isset($CDN_CONFIG['enable']) || !$CDN_CONFIG['enable'] || !isset($CDN_CONFIG['base_url']) || !$CDN_CONFIG['base_url'] || ! OSC::$_cdn_enabled) {
            return $files;
        }

        $single_return = false;

        if (!is_array($files)) {
            $single_return = true;
            $files = [$files];
        }

        $cdn_domain = preg_replace('/^https?:\/\//i', '', $CDN_CONFIG['base_url']);

        foreach ($files as $idx => $file) {
            if (!preg_match('/^https?:\/\/' . preg_quote($cdn_domain, '/') . '(\/.+)$/i', $file, $matches)) {
                continue;
            }

            $files[$idx] = static::$base_url . $matches[1];
        }

        return $single_return ? $files[0] : $files;
    }

}

class OSC_ModuleInfo {

    protected $_locked = false;
    protected $_key = '';
    protected $_name = '';
    protected $_description = '';
    protected $_author = '';
    protected $_author_email = '';
    protected $_offical_site_url = '';
    protected $_version = '0.0.0';
    protected $_site_path = '';
    protected $_code_path = '';

    public function lock() {
        $this->_locked = true;
        return $this;
    }

    public function setInfo($data) {
        if ($this->_locked) {
            throw new OSC_Exception_Runtime('Module just able set by core');
        }

        foreach ($data as $key => $value) {
            if ($key == 'locked') {
                continue;
            }

            if (substr($key, 0, 1) != '_') {
                $key = '_' . $key;
            }

            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        if (isset($data['version'])) {
            $version_parts = explode('.', $this->_version);

            if (count($version_parts) > 3) {
                throw new Exception('Module [' . $this->getKey() . ']: ' . $this->_version . ' is not correct version format');
            }

            foreach ($version_parts as $part) {
                if (preg_match('/[^0-9]/', $part)) {
                    throw new Exception('Module [' . $this->getKey() . ']: ' . $this->_version . ' is not correct version format');
                }
            }
        }

        return $this;
    }

    public function getKey() {
        return $this->_key;
    }

    public function getName() {
        return $this->_name;
    }

    public function getDescription() {
        return $this->_description;
    }

    public function getAuthor() {
        return $this->_author;
    }

    public function getAuthorEmail() {
        return $this->_author_email;
    }

    public function getOfficalSiteUrl() {
        return $this->_offical_site_url;
    }

    public function getVersion() {
        return $this->_version;
    }

    public function getSitePath() {
        return $this->_site_path;
    }

    public function getCodePath() {
        return $this->_code_path;
    }
    
    public function getInfo() {
        return get_object_vars($this);
    }

    public function selfSerialize() {
        $info = OSC::encode($this->getInfo());

        return <<<EOF
OSC_ModuleInfo::selfUnserialize(<<<EOS
{$info}
EOS
)
EOF;
    }

    public static function selfUnserialize($info) {
        $class = new OSC_ModuleInfo();
        return $class->setInfo(OSC::decode($info, true))->lock();
    }
}
