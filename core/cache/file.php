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
 * OSC_Framework::Cache_File
 *
 * @package OSC_Cache_File
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Cache_File extends OSC_Cache_Abstract {

    protected $_storage_path;

    /**
     * 
     * @param array $config
     * @throws OSC_Exception_Runtime
     */
    public function setConfig($config) {
        parent::setConfig($config);
        
        $this->_storage_path = VAR_PATH . 'cache/' . urlencode($config['dirname']) . '/';

        if (!file_exists($this->_storage_path)) {
            if (!OSC::makeDir($this->_storage_path)) {
                throw new OSC_Exception_Runtime("The cache storage directory is not exists");
            }
        } else if (!is_dir($this->_storage_path)) {
            throw new OSC_Exception_Runtime("The cache storage path is not directory");
        } else if (!is_writable($this->_storage_path)) {
            throw new OSC_Exception_Runtime("The cache storage directory is not writeable");
        }
        
        return $this;
    }
    
    public function flush() {
        
    }

    /**
     * 
     * @return string Cache type
     */
    public function getType() {
        return 'file';
    }

    /**
     * 
     * @param string $key
     * @return string
     */
    protected function _getCacheFile($key) {
        return $this->_storage_path . DS . $key . '.cache.php';
    }

    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function exists($key) {
        $file_path = $this->_getCacheFile($key);
        return file_exists($file_path) && filemtime($file_path) > time();
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

        if (!$this->exists($key)) {
            return $this->set($key, $value, $ttl);
        }

        return false;
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

        $file_path = $this->_getCacheFile($key);

        if (OSC::writeToFile($file_path, OSC::core('string')->toPHP($this->_preDataForSave($value), 'cache_data'))) {
            return @touch($file_path, $this->_getTtlTimestamp($ttl));
        }

        return false;
    }

    /**
     * 
     * @param string $key
     * @param int $ttl
     * @return boolean
     */
    public function setTtl($key, $ttl = 0) {
        $ttl = $this->_calTtl($ttl);

        $file_path = $this->_getCacheFile($key);

        if (!$this->exists($key)) {
            return false;
        }

        return @touch($file_path, $this->_getTtlTimestamp($ttl));
    }

    /**
     * 
     * @param string $key
     * @return int Time to live [Timestamp]
     */
    public function getTtl($key) {
        $file_path = $this->_getCacheFile($key);

        if (!$this->exists($key)) {
            return 0;
        }

        return filemtime($file_path);
    }

    /**
     * 
     * @param mixed $data
     * @return array
     */
    protected function _preDataForSave($data) {
        $is_serialize = false;

        if (is_object($data) || is_array($data)) {
            $is_serialize = true;
            $data = json_encode($data);
        }

        return array('serialize' => $is_serialize, 'data' => $data);
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key) {
        if (!$this->exists($key)) {
            return false;
        }

        include $this->_getCacheFile($key);

        if (!isset($cache_data)) {
            return false;
        }

        return $cache_data['serialize'] ? json_decode($cache_data['data'], true) : $cache_data['data'];
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

            foreach ($args as $key) {
                $cache_arr[$key] = $this->get($key);
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
        
    }

    /**
     * 
     * @param string $key
     * @param int $val
     * @return int new value
     */
    public function decrement($key, $val = 1) {
        
    }

    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function delete($key) {
        return @unlink($this->_getCacheFile($key));
    }

}
