<?php

class OSC_Cache_Memcache extends OSC_Cache_Abstract {

    /**
     *
     * @var Memcache 
     */
    protected $_connection = null;
    protected $_port = '';
    protected $_host = '';

    public function setConfig($config) {
        parent::setConfig($config);
        
        $this->_host = $config['host'];
        $this->_port = $config['port'];
        
        return $this;
    }

    public function getType() {
        return 'memcache';
    }

    /**
     * 
     * @return Memcache
     * @throws OSC_Exception_Runtime
     */
    public function getConnection() {
        if ($this->_connection === null) {
            $this->_connection = new Memcache();

            if (!$this->_connection->pconnect($this->_host, $this->_port, 0.5)) {
                throw new OSC_Exception_Runtime("The cache server is unable to connect");
            }
        }

        return $this->_connection;
    }
    
    public function flush() {
        $this->getConnection()->flush();
    }

    public function getRealKey($key) {
        return OSC_SITE_KEY . '__' . $key;
    }

    public function exists($key) {
        $connection = $this->getConnection();

        $result = $connection->add($this->getRealKey($key), null, false, 1);

        return !$result;
    }

    public function add($key, $value, $ttl = 0) {
        $ttl = $this->_calTtl($ttl);

        return $this->getConnection()->add($this->getRealKey($key), $value, false, $ttl);
    }

    public function set($key, $value, $ttl = 0) {
        $ttl = $this->_calTtl($ttl);

        return $this->getConnection()->set($this->getRealKey($key), $value, false, $ttl);
    }

    public function get($key) {
        return $this->getConnection()->get($this->getRealKey($key));
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

    public function getMulti() {
        $num_args = func_num_args();

        if ($num_args < 1) {
            return array();
        }

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

        $keys = array();

        foreach ($args as $key) {
            $keys[$key] = $this->getRealKey($key);
        }

        $data = $this->getConnection()->get($keys);

        $result = array();

        foreach ($keys as $key => $real_key) {
            $result[$key] = isset($data[$real_key]) ? $data[$real_key] : false;
        }

        return $result;
    }

    public function increment($key, $val = 1) {
        return $this->getConnection()->increment($this->getRealKey($key), $val);
    }

    public function decrement($key, $val = 1) {
        return $this->getConnection()->decrement($this->getRealKey($key), $val);
    }

    public function delete($key) {
        return $this->getConnection()->delete($this->getRealKey($key));
    }

}
