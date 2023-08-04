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
class RedisMonitor {

    private $__redis = null;
    protected $_multi_flag = false;

    public function __construct() {
        $this->__redis = new Redis();
    }

    public function __call($name, $arguments) {
        if (!$this->_multi_flag) {
            OSC::core('debug')->startProcess('Redis.Command', strtoupper($name) . (count($arguments) > 0 ? (' :: ' . $arguments[0]) : ''));
        }

        if (strtolower($name) === 'multi') {
            $this->_multi_flag = true;
        }

        $response = call_user_func_array(array($this->__redis, $name), $arguments);

        if (!$this->_multi_flag || strtolower($name) == 'exec') {
            $this->_multi_flag = false;
            OSC::core('debug')->endProcess('Redis.Command');
        }

        return $response;
    }

}

/**
 * OSC_Framework::Cache_Redis
 *
 * @package OSC_Cache_Redis
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Cache_Redis extends OSC_Cache_Abstract {

    /**
     *
     * @var Redis 
     */
    protected $_connection = null;

    /**
     *
     * @var int
     */
    protected $_port = 6379;

    /**
     *
     * @var string
     */
    protected $_host = 'localhost';

    /**
     * 
     * @param array $config
     */
    public function setConfig($config) {
        parent::setConfig($config);

        if (isset($config['host'])) {
            $this->_host = $config['host'];
        }

        if (isset($config['port'])) {
            $this->_port = $config['port'];
        }

        return $this;
    }

    /**
     * 
     * @return string Cache type
     */
    public function getType() {
        return 'redis';
    }

    /**
     * 
     * @return Redis
     * @throws OSC_Exception_Runtime
     */
    public function getConnection() {
        if ($this->_connection === null) {
            $this->_connection = OSC_ENV != 'production' ? new RedisMonitor() : new Redis();

            $retry_counter = 0;

            while ($retry_counter <= 5) {
                try {
                    if (!$this->_connection->connect($this->_host, $this->_port, .5)) {
                        throw new RedisException("Cannot connect to cache server");
                    }

                    $this->_connection->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
                    $this->_connection->setOption(Redis::OPT_PREFIX, OSC_SITE_KEY ? OSC_SITE_KEY. ':' : '');

                    break;
                } catch (RedisException $e) {
                    $retry_counter ++;

                    usleep(1000);

                    if ($retry_counter > 5) {
                        throw new OSC_Exception_Runtime($e->getMessage(), $e->getCode());
                    }
                }
            }
        }

        return $this->_connection;
    }

    public function flush() {
        $this->getConnection()->flushAll();
    }

    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function exists($key) {
        return $this->getConnection()->exists($key);
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return boolean
     */
    public function add($key, $value, $ttl = 0) {
        $ttl = $this->_calTtl($ttl);

        $opts = array('nx');

        if ($ttl > 0) {
            $opts['ex'] = $ttl;
        }

        return $this->getConnection()->set($key, $this->encode($value), $opts);

///////// Redis version is smaller than 2.6.12 /////////////
//        if ($this->getConnection()->setnx($key, $this->encode($value))) {
//            if($this->setTtl($key, $ttl)) {
//                return true;
//            }
//            
//            $this->delete($key);
//            
//            return false;
//        }
//
//        return false;
////////////////////////////////////////////////////////////
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return boolean
     */
    public function set($key, $value, $ttl = 0) {
        $ttl = $this->_calTtl($ttl);

        if ($ttl > 0) {
            return $this->getConnection()->setex($key, $ttl, $this->encode($value));
        }

        return $this->getConnection()->set($key, $this->encode($value));
    }

    /**
     * 
     * @param string $key
     * @param int $ttl
     * @return boolean
     */
    public function setTtl($key, $ttl = 0) {
        return true;
    }

    /**
     * 
     * @param string $key
     * @return int Time to live [Timestamp]
     */
    public function getTtl($key) {
        return true;
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key) {
        return $this->decode($this->getConnection()->get($key));
    }

    /**
     * @params mixed $key
     * @params mixed $_ [optional]
     * @return array
     */
    public function getMulti() {
        $cache_arr = array();

        $num_args = func_num_args();

        if ($num_args > 0) {
            $args = array();

            if ($num_args == 1) {
                $first_arg = func_get_arg(0);

                if (is_array($first_arg)) {
                    if (count($first_arg) > 0) {
                        $args = $first_arg;
                    }
                } else {
                    $args[] = $first_arg;
                }
            } else {
                $args = func_get_args();
            }

            $getted = $this->getConnection()->mget($args);

            $idx = 0;

            foreach ($args as $key) {
                $cache_arr[$key] = $this->decode($getted[$idx]);
                $idx++;
            }
        }

        return $cache_arr;
    }

    /**
     * 
     * @param string $key
     * @param int $val
     * @return int new value
     */
    public function increment($key, $val = 1) {
        return $this->getConnection()->incrby($key, $val);
    }

    /**
     * 
     * @param string $key
     * @param int $val
     * @return int new value
     */
    public function decrement($key, $val = 1) {
        return $this->getConnection()->decrby($key, $val);
    }

    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function delete($key) {
        return $this->getConnection()->del($key);
    }

}
