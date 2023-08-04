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
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSC_Framework::Cache_Abstract
 *
 * @package OSC_Cache_Abstract
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
abstract class OSC_Cache_Abstract extends OSC_Object {
    
    public function setConfig($config) {
        return $this;
    }

    /**
     * 
     * @return string Cache type
     */
    abstract public function getType();

    /**
     * 
     * @param string $key
     * @return boolean
     */
    abstract public function exists($key);

    /**
     * Set value to cache if the key doesn't already exist
     * 
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return boolean
     */
    abstract public function add($key, $value, $ttl = 0);

    /**
     * Set value to cache if the key doesn't already exist
     * 
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return boolean
     */
    public function setnx($key, $value, $ttl = 0) {
        return $this->add($key, $value, $ttl);
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return boolean
     */
    abstract public function set($key, $value, $ttl = 0);

    /**
     * 
     * @param string $key
     * @param int $ttl
     * @return boolean
     */
    abstract public function setTtl($key, $ttl = 0);

    /**
     * 
     * @param string $key
     * @return int Time to live [Timestamp]
     */
    abstract public function getTtl($key);

    /**
     * 
     * @param string $key
     * @return mixed
     */
    abstract public function get($key);

    /**
     * @params mixed $key
     * @params mixed $_ [optional]
     * @return array
     */
    abstract public function getMulti();

    /**
     * 
     * @param string $key
     * @param int $val
     * @return int new value
     */
    abstract public function increment($key, $val = 1);

    /**
     * 
     * @param string $key
     * @param int $val
     * @return int new value
     */
    abstract public function decrement($key, $val = 1);

    /**
     * 
     * @param string $key
     * @return boolean
     */
    abstract public function delete($key);
    
    abstract public function flush();

    /**
     * 
     * @param int $ttl
     * @return int
     */
    protected function _getTtlTimestamp($ttl) {
        return time() + ($ttl < 1 ? 60 * 60 * 24 * 365 : $ttl);
    }

    protected function _calTtl($ttl) {
        if ($ttl === true) {
            return 0;
        }

        if ($ttl < 1) {
            $ttl = 60 * 60 * 24 * 10;
        }

        return $ttl;
    }

    /**
     * 
     * @param string $value
     * @return mixed
     */
    public function decode($value) {
        if (is_string($value) && strpos($value, 'osc_seril:') === 0) {
            return OSC::decode(substr($value, 10), true);
        }

        return $value;
    }

    /**
     * 
     * @param mixed $value
     * @return string
     */
    public function encode($value) {
        if (is_array($value) || is_object($value)) {
            return 'osc_seril:' . OSC::encode($value);
        }

        return $value;
    }

}
