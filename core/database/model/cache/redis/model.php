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
 * @package OSC_Database_Model_Cache_Redis_Model
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Database_Model_Cache_Redis_Model extends OSC_Database_Model_Abstract_Cache_Model {

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
            $this->_connection = $this->_adapter->getConnection();
        }

        return $this->_connection;
    }

    /**
     * 
     * @param mixed $fields
     * @return array
     */
    protected function _parseFields($fields) {
        if (is_array($fields)) {
            $buff = array();

            foreach ($fields as $field) {
                $field = trim((string) $field);

                if ($field != '' && !in_array($field, $buff)) {
                    $buff[] = $field;
                }
            }

            $fields = $buff;
        } else if ($fields !== null) {
            $fields = trim((string) $fields);

            if ($fields == '') {
                return false;
            }

            $fields = array($fields);
        }

        return $fields;
    }

    /**
     * 
     * @param mixed $fields
     * @param string $key
     * @return mixed
     */
    public function get($fields = null, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        $fields = $this->_parseFields($fields);

        if ($fields === false) {
            return false;
        }

        if ($fields === null || count($fields) < 1) {
            $fields = $this->getConnection()->hGetAll($key);
        } else {
            $fields = $this->getConnection()->hMGet($key, $fields);
        }

        if (count($fields) < 1) {
            return false;
        }

        $adapter = $this->getAdapter();

        foreach ($fields as $idx => $val) {
            $fields[$idx] = $adapter->decode($val);
        }

        $this->setExpire(60 * 60 * 24 * 5, $key);

        return $fields;
    }

    /**
     * 
     * @param mixed $fields
     * @param string $key
     * @return boolean
     */
    public function set($fields, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        if (!is_array($fields) || count($fields) < 1) {
            return false;
        }

        $adapter = $this->getAdapter();

        foreach ($fields as $k => $val) {
            $fields[$k] = $adapter->encode($val);
        }

        $response = $this->getConnection()->hMSet($key, $fields);

        $this->setExpire(60 * 60 * 24 * 5, $key);

        return $response;
    }

    /**
     * 
     * @param mixed $fields
     * @param string $key
     * @return boolean
     */
    public function delete($fields = null, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        $fields = $this->_parseFields($fields);

        if ($fields === false) {
            return false;
        }

        if ($fields === null || count($fields) < 1) {
            return $this->getConnection()->del($key);
        }

        array_unshift($fields, $key);

        return call_user_func_array(array($this->getConnection(), 'hdel'), $fields);
    }

    /**
     * 
     * @param array $keys
     * @param mixed $fields
     * @return boolean
     */
    public function deleteMulti($keys, $fields = null) {
        if (!is_array($keys)) {
            return false;
        }

        $keys = array_map(function($key) {
            return trim((string) $key);
        }, $keys);
        $keys = array_filter($keys, function($key) {
            return $key != '';
        });
        $keys = array_unique($keys);

        if (count($keys) < 1) {
            return false;
        }

        $fields = $this->_parseFields($fields);

        if ($fields === false) {
            return false;
        }

        $connection = $this->getConnection();

        $connection->multi(REDIS::PIPELINE);

        if ($fields === null || count($fields) < 1) {
            foreach ($keys as $key) {
                $this->getConnection()->del($key);
            }
        } else {
            foreach ($keys as $key) {
                $_fields = $fields;

                array_unshift($_fields, $key);

                call_user_func_array(array($this->getConnection(), 'hdel'), $_fields);
            }
        }

        return $connection->exec();
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

    /**
     * 
     * @param mixed $fields
     * @param integer $value
     * @param string $key
     * @return boolean
     */
    public function increment($fields, $value = 1, $key = null) {
        if (!$key) {
            $key = $this->_key;
        }

        if (!$key) {
            return false;
        }

        if (!$this->exists($key)) {
            return false;
        }

        $fields = $this->_parseFields($fields);

        if (!is_array($fields)) {
            return false;
        }

        $total_field = count($fields);

        if ($total_field < 1) {
            return false;
        }

        $value = intval($value);

        if ($value == 0) {
            return false;
        }

        if ($total_field == 1) {
            return $this->getConnection()->hIncrBy($key, $fields[0], $value);
        }

        $connection = $this->getConnection();

        $connection->multi(REDIS::PIPELINE);

        foreach ($fields as $field) {
            $connection->hIncrBy($key, $field, $value);
        }

        $response = $connection->exec();

        $this->setExpire(60 * 60 * 24 * 5, $key);

        return $response;
    }

    /**
     * 
     * @param array $keys
     * @param mixed $fields
     * @return array
     */
    public function getMulti($keys, $fields = null) {
        if (!is_array($keys)) {
            return array();
        }

        $buff = array();

        foreach ($keys as $key) {
            $key = trim((string) $key);

            if ($key != '' && !in_array($key, $buff)) {
                $buff[] = $key;
            }
        }

        if (count($buff) < 1) {
            return array();
        }

        $keys = $buff;

        $fields = $this->_parseFields($fields);

        if ($fields === false) {
            return array();
        }

        $get_all = false;

        if ($fields === null || count($fields) < 1) {
            $get_all = true;
        }

        $connection = $this->getConnection();

        $connection->multi(REDIS::PIPELINE);

        foreach ($keys as $key) {
            if ($get_all) {
                $connection->hGetAll($key);
            } else {
                $connection->hMGet($key, $fields);
            }
        }

        $buff = $connection->exec();

        $return = array();

        $idx = 0;

        $adapter = $this->getAdapter();

        foreach ($keys as $key) {
            if (is_array($buff[$idx])) {
                if (count($buff[$idx]) < 1) {
                    $buff[$idx] = false;
                } else {
                    foreach ($buff[$idx] as $k => $v) {
                        $buff[$idx][$k] = $adapter->decode($v);
                    }
                }
            }

            $return[$key] = $buff[$idx];

            $idx++;
        }

        $this->setMultiExpire(60 * 60 * 24 * 5, $keys);

        return $return;
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
     * @param array $key
     * @param integer $ttl
     * @return boolean
     */
    public function setMultiExpire($keys, $ttl) {
        if (!is_array($keys)) {
            return array();
        }

        $buff = array();

        foreach ($keys as $key) {
            $key = trim((string) $key);

            if ($key != '' && !in_array($key, $buff)) {
                $buff[] = $key;
            }
        }

        if (count($buff) < 1) {
            return array();
        }

        $keys = $buff;

        $connection = $this->getConnection();

        $connection->multi(REDIS::PIPELINE);

        foreach ($keys as $key) {
            $connection->expire($key, $ttl);
        }

        return $connection->exec();
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
     * @param array $key
     * @param integer $ttl
     * @return boolean
     */
    public function setMultiPExpire($keys, $ttl) {
        if (!is_array($keys)) {
            return array();
        }

        $buff = array();

        foreach ($keys as $key) {
            $key = trim((string) $key);

            if ($key != '' && !in_array($key, $buff)) {
                $buff[] = $key;
            }
        }

        if (count($buff) < 1) {
            return array();
        }

        $keys = $buff;

        $connection = $this->getConnection();

        $connection->multi(REDIS::PIPELINE);

        foreach ($keys as $key) {
            $connection->pexpire($key, $ttl);
        }

        return $connection->exec();
    }

}
