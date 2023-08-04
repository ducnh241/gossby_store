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
 * @package OSC_Database_Model_Cache_Redis_Model_Collection
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Database_Model_Cache_Redis_Model_Collection extends OSC_Database_Model_Abstract_Cache_Model_Collection {

    /**
     *
     * @var Redis 
     */
    protected $_connection = null;

    /**
     *
     * @return Redis 
     */
    public function getConnection() {
        if ($this->_connection === null) {
            $this->_connection = $this->getAdapter()->getConnection();
        }

        return $this->_connection;
    }

    /**
     * 
     * @param string $key
     * @return integer
     */
    public function getLength($key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        return $this->getConnection()->lLen($key);
    }

    /**
     * 
     * @param integer $start
     * @param integer $stop
     * @param string $key
     * @return array
     */
    public function getRange($start = null, $stop = null, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        $connection = $this->getConnection();
        $adapter = $this->getAdapter();

        $collection = $connection->lRange($key, $start, $stop);

        foreach ($collection as $idx => $item) {
            $collection[$idx] = $adapter->decode($item);
        }

        $this->setExpire(60 * 60 * 24 * 30, $key);

        return $collection;
    }

    /**
     * 
     * @param mixed $value
     * @param integer $count
     * @param string $key
     * @return boolean
     */
    public function remove($value, $count = 0, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        return $this->getConnection()->lRem($key, $this->getAdapter()->encode($value), $count);
    }

    /**
     * 
     * @param mixed $value
     * @param integer $count
     * @param string $key
     * @return array
     */
    public function multiRemove($values, $count = 0, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        $values = array_unique($values);

        $connection = $this->getConnection();

        $connection->multi(REDIS::PIPELINE);

        $adapter = $this->getAdapter();

        foreach ($values as $v) {
            $this->getConnection()->lRem($key, $adapter->encode($v), $count);
        }

        return $connection->exec();
    }

    /**
     * 
     * @param mixed $value
     * @param string $key
     * @return boolean
     */
    public function push($value, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        $response = $this->getConnection()->rPush($key, $this->getAdapter()->encode($value));

        $this->setExpire(60 * 60 * 24 * 30, $key);

        return $response;
    }

    /**
     * 
     * @param array $values
     * @param string $key
     * @return boolean
     */
    public function multiPush($values, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        $connection = $this->getConnection();

        $connection->multi(REDIS::PIPELINE);

        $adapter = $this->getAdapter();

        foreach ($values as $value) {
            $connection->rpush($key, $adapter->encode($value));
        }

        $response = $connection->exec();

        $this->setExpire(60 * 60 * 24 * 30, $key);

        return $response;
    }

    /**
     * 
     * @param mixed $value
     * @param string $key
     * @return boolean
     */
    public function unshift($value, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        $response = $this->getConnection()->lPush($key, $this->getAdapter()->encode($value));

        $this->setExpire(60 * 60 * 24 * 30, $key);

        return $response;
    }

    /**
     * 
     * @param array $values
     * @param string $key
     * @return boolean
     */
    public function multiUnshift($values, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        $connection = $this->getConnection();

        $connection->multi(REDIS::PIPELINE);

        $adapter = $this->getAdapter();

        $values = array_reverse($values);

        foreach ($values as $value) {
            $connection->lpush($key, $adapter->encode($value));
        }

        $response = $connection->exec();

        $this->setExpire(60 * 60 * 24 * 30, $key);

        return $response;
    }

    /**
     * 
     * @param mixed $value
     * @param mixed $pivot
     * @param string $key
     * @return boolean
     */
    public function insertBefore($value, $pivot, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        $response = $this->getConnection()->lInsert($key, REDIS::BEFORE, $this->getAdapter()->encode($pivot), $this->getAdapter()->encode($value));

        $this->setExpire(60 * 60 * 24 * 30, $key);

        return $response;
    }

    /**
     * 
     * @param mixed $value
     * @param mixed $pivot
     * @param string $key
     * @return boolean
     */
    public function insertAfter($value, $pivot, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        $response = $this->getConnection()->lInsert($key, REDIS::AFTER, $this->getAdapter()->encode($pivot), $this->getAdapter()->encode($value));

        $this->setExpire(60 * 60 * 24 * 30, $key);

        return $response;
    }

    /**
     * 
     * @param integer $offset
     * @param integer $length
     * @param string $key
     * @return boolean
     */
    public function trim($offset, $length, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        return $this->getConnection()->lTrim($key, $offset, $offset + $length - 1);
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function pop($key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        return $this->getAdapter()->decode($this->getConnection()->rPop($key));
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function shift($key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        return $this->getAdapter()->decode($this->getConnection()->lPop($key));
    }

    /**
     * 
     * @param integer $index
     * @param string $key
     * @return mixed
     */
    public function getByIndex($index, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        $response = $this->getAdapter()->decode($this->getConnection()->lIndex($key, $index));

        $this->setExpire(60 * 60 * 24 * 30, $key);

        return $response;
    }

    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function delete($key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        return $this->getConnection()->del($key);
    }

    /**
     * 
     * @param integer $ttl
     * @param string $key
     * @return boolean
     */
    public function setExpire($ttl, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        return $this->getConnection()->expire($key, $ttl);
    }

    /**
     * 
     * @param integer $ttl
     * @param string $key
     * @return boolean
     */
    public function setPExpire($ttl, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        return $this->getConnection()->pExpire($key, $ttl);
    }

    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function exists($key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        return $this->getConnection()->exists($key);
    }

}
