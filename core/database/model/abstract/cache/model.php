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
 * @package OSC_Database_Model_Abstract_Cache_Model
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
abstract class OSC_Database_Model_Abstract_Cache_Model extends OSC_Object {

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
     * @var OSC_Database_Model_Abstract_Cache_Model_Collection 
     */
    protected $_collection_cache = null;

    /**
     * 
     * @param mixed $fields
     * @param string $key
     * @return mixed
     */
    abstract function get($fields = null, $key = null);

    /**
     * 
     * @param mixed $fields
     * @param string $key
     * @return boolean
     */
    abstract function set($fields, $key = null);

    /**
     * 
     * @param mixed $fields
     * @param string $key
     * @return boolean
     */
    abstract function delete($fields = null, $key = null);

    /**
     * 
     * @param array $keys
     * @param mixed $fields
     * @return boolean
     */
    abstract function deleteMulti($keys, $fields = null);

    /**
     * 
     * @param mixed $fields
     * @param integer $value
     * @param string $key
     * @return boolean
     */
    abstract function increment($fields, $value = 1, $key = null);

    /**
     * 
     * @param array $keys
     * @param mixed $fields
     * @return array
     */
    abstract function getMulti($keys, $fields = null);

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
     * @param array $key
     * @param integer $ttl
     * @return boolean
     */
    abstract function setMultiExpire($key, $ttl);

    /**
     * 
     * @param array $key
     * @param integer $ttl
     * @return boolean
     */
    abstract function setMultiPExpire($key, $ttl);

    /**
     *
     * @return OSC_Cache_Abstract
     */
    public function getAdapter() {
        return $this->_adapter;
    }

    /**
     * 
     * @param OSC_Cache_Abstract $adapter
     * @return OSC_Database_Model_Abstract_Cache_Model
     */
    public function setAdapter($adapter) {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * 
     * @param string $key
     * @return OSC_Database_Model_Abstract_Cache_Model
     */
    public function setKey($key) {
        $this->_key = $key;
        return $this;
    }

    /**
     *
     * @return OSC_Database_Model_Abstract_Cache_Model_Collection 
     */
    public function getCollectionCache() {
        if ($this->_collection_cache === null) {
            $this->_collection_cache = OSC::core('database_model_cache_' . $this->_adapter->getType() . '_model_collection', null)->setAdapter($this->_adapter);
        }

        return $this->_collection_cache;
    }

}
