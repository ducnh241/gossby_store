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
 * @package Core_Database
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Cache extends OSC_Object {

    protected $_default_bind = 'cache';
    protected $_adapters = array();

    /**
     * 
     * @staticvar array $adapters
     * @param string $bind
     * @return OSC_Cache_Abstract
     */
    public function getAdapter($bind = null) {
        static $adapters = null;

        if ($adapters === null) {
            $adapters = & $this->_adapters;
        }

        $cache_config = OSC::systemRegistry('cache_config');

        if (!$bind) {
            $bind = $this->_default_bind;
        }

        $instance = $cache_config['bind'][$bind];

        if (!$instance) {
            throw new OSC_Exception_Runtime("Cache bind [{$bind}] not found");
        }

        if (!isset($adapters[$instance])) {
            $config = $cache_config['instance'][$instance];
            $adapters[$instance] = OSC::core('cache_' . $config['adapter'], $instance)->setConfig($config);
        }

        return $adapters[$instance];
    }

    public function add($key, $value, $ttl = 0, $bind = null) {
        return $this->getAdapter($bind)->add($key, $value, $ttl);
    }

    public function set($key, $value, $ttl = 0, $bind = null) {
        return $this->getAdapter($bind)->set($key, $value, $ttl);
    }

    public function setTtl($key, $ttl = 0, $bind = null) {
        return $this->getAdapter($bind)->setTtl($key, $ttl);
    }

    public function increment($key, $bind = null) {
        return $this->getAdapter($bind)->increment($key);
    }

    public function decrement($key, $bind = null) {
        return $this->getAdapter($bind)->decrement($key);
    }

    public function get($key, $bind = null) {
        return $this->getAdapter($bind)->get($key);
    }

    public function delete($key, $bind = null) {
        return $this->getAdapter($bind)->delete($key);
    }

    public function exists($key, $bind = null) {
        return $this->getAdapter($bind)->exists($key);
    }

    public function getMulti($keys, $bind = null) {
        return call_user_func_array([$this->getAdapter($bind), 'getMulti'], $keys);
    }

    public static function registerCacheInstance($key, $data) {
        $cache_config = OSC::systemRegistry('cache_config');

        if (!isset($cache_config['instance'])) {
            $cache_config['instance'] = array();
        }

        $cache_config['instance'][$key] = $data;

        OSC::systemRegister('cache_config', $cache_config);
    }

    /**
     * 
     * @param string $bind
     * @param string $instance
     */
    public static function registerCacheBind($bind, $instance) {
        $cache_config = OSC::systemRegistry('cache_config');

        if (!isset($cache_config['bind'])) {
            $cache_config['bind'] = array();
        }

        $cache_config['bind'][$bind] = $instance;

        OSC::systemRegister('cache_config', $cache_config);
    }

}
