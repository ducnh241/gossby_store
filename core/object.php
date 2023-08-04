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
class OSC_Object {

    protected $_default_value = null;
    protected $_default_var_data = null;
    protected $_events = array();
    protected $_skip_reset_vars = array();
    protected $_registry = array();
    protected $_instance_key = null;
    protected $_registry_updated_flag = false;

    /**
     *
     * @var OSC_Language
     */
    protected $_language = null;

    /**
     *
     * @param string $instance_key
     * @return \OSC_Object
     */
    public function setInstanceKey($instance_key) {
        if (!$this->_instance_key) {
            if (!$instance_key) {
                $instance_key = OSC::makeUniqid();
            }

            $this->_instance_key = $instance_key;
        }

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getInstanceKey() {
        return $this->_instance_key;
    }

    public static function getInstance() {
        static $_instances = array();

        $class = strtolower(get_called_class());

        if (!isset($_instances[$class])) {
            $_instances[$class] = new $class();
        }

        return $_instances[$class];
    }

    /**
     *
     */
    public function __construct() {
        $this->_reset();
    }

    /**
     *
     * @param string $key
     * @return string
     */
    public function _($key) {
        return func_num_args() > 1 ? call_user_func_array(array($this->getLanguage(), 'build'), func_get_args()) : $this->getLanguage()->get($key);
    }

    /**
     *
     * @return OSC_Language
     */
    public function getLanguage() {
        if ($this->_language === null) {
            $this->_language = $this instanceof OSC_Language ? $this : OSC::core('language');
        }

        return $this->_language;
    }

    /**
     *
     */
    public function destruct() {
        $this->__destruct();
    }

    /**
     *
     */
    public function __destruct() {
        $this->_reset();
    }

    public function __get($name) {
        $name = '_' . $name;

        if (!isset($this->$name)) {
            return $this->_default_value;
        }

        return $this->$name;
    }

    public function __set($name, $value) {
        $name = '_' . $name;

        if (!isset($this->$name)) {
            return;
        }

        throw new OSC_Exception_Runtime('The variable is not writeable');
    }

    public function __call($name, $arguments) {

    }

    protected function _reset() {
        if ($this->_default_var_data === null) {
            $this->_default_var_data = array();

            foreach (get_object_vars($this) as $key => $value) {
                if ($key == '_default_var_data' || $key == '_skip_reset_vars' || in_array($key, $this->_skip_reset_vars)) {
                    continue;
                }

                $this->_default_var_data[$key] = $value;
            }
        } else {
            foreach ($this->_default_var_data as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     *
     * @param type $event
     * @param type $object
     * @param int $priority
     * @param type $key
     * @param type $call_params
     * @param type $once
     */
    public function addObserver($event, $object, $priority = null, $key = null, $call_params = null, $once = false) {
        if (!isset($this->_events[$event])) {
            $this->_events[$event] = array();
        }

        $priority = intval($priority);

        $pointer = & $this->_events[$event];

        if (!isset($pointer[$priority])) {
            $pointer[$priority] = array();
            krsort($pointer);
        }

        if (!$key) {
            $pointer[$priority][] = array('object' => $object, 'params' => $call_params, 'once' => $once);
        } else {
            $pointer[$priority][$key] = array('object' => $object, 'params' => $call_params, 'once' => $once);
        }

        return $this;
    }

    public function removeObserver($event, $key = null) {
        if (!isset($this->_events[$event])) {
            return $this;
        }

        if (!$key) {
            unset($this->_events[$event]);
        } else if (isset($this->_events[$event][$key])) {
            unset($this->_events[$event][$key]);
        }

        return $this;
    }

    public function dispatchEvent($event, $params = null, $broadcast = true) {
        $response = $broadcast ? [] : null;

        if (!isset($this->_events[$event])) {
            return $response;
        }

        foreach ($this->_events[$event] as $priority => $observers) {
            foreach ($observers as $key => $observer) {
                if ($observer['once']) {
                    unset($this->_events[$event][$priority][$key]);
                }

                if (is_array($observer['object']) && is_string($observer['object'][0])) {
                    $observer['object'][0] = explode(':', $observer['object'][0]);

                    if (count($observer['object'][0]) == 1) {
                        $observer['object'][0] = new $observer['object'][0][0];
                    } else {
                        if (count($observer['object'][0]) == 3) {
                            if (!$observer['object'][0][2]) {
                                $instance = null;
                            } else {
                                $instance = $observer['object'][0][2];
                            }
                        } else {
                            $instance = OSC::SINGLETON;
                        }

                        if ($observer['object'][0][0] == 'helper') {
                            $observer['object'][0] = OSC::helper($observer['object'][0][1], $instance);
                        } else if ($observer['object'][0][0] == 'core') {
                            $observer['object'][0] = OSC::core($observer['object'][0][1], $instance);
                        } else {
                            continue;
                        }
                    }
                }

                $callback_return = call_user_func($observer['object'], $params, $observer['params']);

                if ($broadcast) {
                    $response[] = $callback_return;
                } else {
                    if ($callback_return) {
                        return $callback_return;
                    }
                }
            }
        }

        return $response;
    }

    protected function _preMethodOpts($method_caller_opts, $opts) {
        if (is_array($method_caller_opts) && count($method_caller_opts) > 0) {
            foreach ($method_caller_opts as $key => $val) {
                $opts[$key] = $val;
            }
        }

        return $opts;
    }

    /**
     *
     * @param string $key
     * @param mixed  $value
     * @return OSC_Object
     */
    public function register($key, $value) {
        if ($value === null) {
            if (!isset($this->_registry[$key])) {
                unset($this->_registry[$key]);
                $this->_registry_updated_flag = true;
            }
        } else {
            if (!isset($this->_registry[$key]) || $this->_registry[$key] != $value) {
                $this->_registry[$key] = $value;
                $this->_registry_updated_flag = true;
            }
        }

        return $this;
    }

    /**
     *
     * @param string $key
     * @return mixed
     */
    public function registry($key) {
        if (!isset($this->_registry[$key])) {
            return null;
        }

        return $this->_registry[$key];
    }

    public function getAllRegistry() {
        return $this->_registry;
    }

    public function setAllRegistry($registry, $skip_updated_flag = false) {
        if (!is_array($registry)) {
            $registry = array();
        }

        $saved_updated_flag = $this->_registry_updated_flag;

        foreach ($registry as $key => $val) {
            $this->register($key, $val);
        }

        if ($skip_updated_flag) {
            $this->_registry_updated_flag = $saved_updated_flag;
        }

        return $this;
    }

    public function registryIsUpdated() {
        return $this->_registry_updated_flag == true;
    }

    public function getClassName() {
        return get_class($this);
    }

    public static function getOSCObjectType() {
        return OSC::getObjectTypeByClassName(get_called_class());
    }

    public function helper($helper, $instance = 'singleton') {
        return OSC::helper($helper, $instance);
    }

    public function core($core, $instance = 'singleton') {
        return OSC::core($core, $instance);
    }
}
