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
 * OSECORE Core
 *
 * @package OSC_Database_Model_Abstract_Cache_Model_Collection
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
abstract class OSC_Database_Model_Abstract_Cache_Model_Collection extends OSC_Object {

    /**
     *
     * @var string 
     */
    protected $_key = null;

    /**
     *
     * @var OSC_Cache_Abstract 
     */
    protected $_adapter = null;

    /**
     * 
     * @param string $key
     * @return integer
     */
    abstract function getLength($key = null);

    /**
     * 
     * @param integer $start
     * @param integer $stop
     * @param string $key
     * @return array
     */
    abstract function getRange($start = null, $stop = null, $key = null);

    /**
     * 
     * @param mixed $value
     * @param integer $count
     * @param string $key
     * @return boolean
     */
    abstract function remove($value, $count = 0, $key = null);

    /**
     * 
     * @param array $values
     * @param int $count
     * @param string $key
     * @return boolean
     */
    abstract function multiRemove($values, $count = 0, $key = null);

    /**
     * 
     * @param mixed $value
     * @param string $key
     * @return boolean
     */
    abstract function push($value, $key = null);

    /**
     * 
     * @param array $values
     * @param string $key
     * @return boolean
     */
    abstract function multiPush($values, $key = null);

    /**
     * 
     * @param mixed $value
     * @param string $key
     * @return boolean
     */
    abstract function unshift($value, $key = null);

    /**
     * 
     * @param array $values
     * @param string $key
     * @return boolean
     */
    abstract function multiUnshift($values, $key = null);

    /**
     * 
     * @param mixed $value
     * @param mixed $pivot
     * @param string $key
     * @return boolean
     */
    abstract function insertBefore($value, $pivot, $key = null);

    /**
     * 
     * @param mixed $value
     * @param mixed $pivot
     * @param string $key
     * @return boolean
     */
    abstract function insertAfter($value, $pivot, $key = null);

    /**
     * 
     * @param integer $offset
     * @param integer $length
     * @param string $key
     * @return boolean
     */
    abstract function trim($offset, $length, $key = null);

    /**
     * 
     * @param string $key
     * @return mixed
     */
    abstract function pop($key = null);

    /**
     * 
     * @param string $key
     * @return mixed
     */
    abstract function shift($key = null);

    /**
     * 
     * @param integer $index
     * @param string $key
     * @return mixed
     */
    abstract function getByIndex($index, $key = null);

    /**
     * 
     * @param string $key
     * @return boolean
     */
    abstract function delete($key = null);

    /**
     * 
     * @param integer $ttl
     * @param string $key
     * @return boolean
     */
    abstract function setExpire($ttl, $key = null);

    /**
     * 
     * @param integer $ttl
     * @param string $key
     * @return boolean
     */
    abstract function setPExpire($ttl, $key = null);

    /**
     * 
     * @param string $key
     * @return boolean
     */
    abstract function exists($key = null);

    /**
     *
     * @return OSC_Cache_Abstract
     */
    public function getAdapter() {
        return $this->_adapter;
    }

    /**
     * 
     * @param string $key
     * @return OSC_Database_Model_Abstract_Cache_Model_Collection
     */
    public function setKey($key) {
        $this->_key = $key;
        return $this;
    }

    /**
     * 
     * @param OSC_Cache_Abstract $adapter
     * @return OSC_Database_Model_Abstract_Cache_Model_Collection
     */
    public function setAdapter($adapter) {
        $this->_adapter = $adapter;
        return $this;
    }

}
