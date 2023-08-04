<?php

ini_set('display_errors', 'Off');
error_reporting(E_ERROR | E_PARSE);

include_once dirname(__FILE__) . '/library/vendor/autoload.php';

function getIpAddress()
{
    $ipAddress = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // to get shared ISP IP address
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // check for IPs passing through proxy servers
        // check if multiple IP addresses are set and take the first one
        $ipAddressList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($ipAddressList as $ip) {
            if (!empty($ip)) {
                // if you prefer, you can check for valid IP address here
                $ipAddress = $ip;
                break;
            }
        }
    } else if (!empty($_SERVER['HTTP_X_FORWARDED'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if (!empty($_SERVER['HTTP_FORWARDED'])) {
        $ipAddress = $_SERVER['HTTP_FORWARDED'];
    } else if (!empty($_SERVER['REMOTE_ADDR'])) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }
    return $ipAddress;
}

function headerGetAll()
{
    static $_headers = null;

    if ($_headers !== null) {
        return $_headers;
    }

    $_headers = [];

    if (function_exists('getallheaders')) {
        $_headers = getallheaders();
    } else if (is_array($_SERVER)) {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) != 'HTTP_') {
                continue;
            }

            $_headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }

    return $_headers;
}

function headerIsExists()
{
    $headers = headerGetAll();

    foreach (func_get_args() as $header_name) {
        $header_name = ucwords($header_name);

        if (!isset($headers[$header_name])) {
            return false;
        }
    }

    return true;
}

function headerGet()
{
    $headers = headerGetAll();

    $total_name = func_num_args();

    if ($total_name < 1) {
        return null;
    }

    $headers_value = array();

    foreach (func_get_args() as $header_name) {
        $cleaned_header_name = ucwords($header_name);
        $headers_value[$header_name] = isset($headers[$cleaned_header_name]) ? $headers[$cleaned_header_name] : null;
    }

    return $total_name == 1 ? $headers_value[func_get_arg(0)] : $headers_value;
}

if (headerIsExists('Content-Type') && headerGet('Content-Type') == 'application/json') {
    $JSON_DATA = json_decode(file_get_contents('php://input'), true);

    if (is_array($JSON_DATA) && count($JSON_DATA) > 0) {
        foreach ($JSON_DATA as $k => $v) {
            $_GET[$k] = $v;
            $_REQUEST[$k] = $v;
        }
    }
}

class OSC_Mongodb
{

    /**
     *
     * @var MongoDB\Client
     */
    protected $_adapter = null;

    /**
     *
     * @var array
     */
    protected $_config = null;

    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    /**
     *
     * @return MongoDB\Client
     */
    public function getAdapter()
    {
        if ($this->_adapter === null) {
            $auth = '';

            if (isset($this->_config['username']) && isset($this->_config['password']) && $this->_config['username'] && $this->_config['password']) {
                $auth .= $this->_config['username'] . ':' . $this->_config['password'] . '@';
            }

            $retry_write = '';

            if (isset($this->_config['env']) && $this->_config['env'] === 'production') {
                $retry_write = '?retryWrites=false';
            }

            $auth_db = $this->_config['dbname'];

            if (isset($this->_config['auth_dbname']) && !empty($this->_config['auth_dbname'])) {
                $auth_db = $this->_config['auth_dbname'];
            }

            $this->_adapter = (new MongoDB\Client('mongodb://' . $auth . $this->_config['host'] . ':' . $this->_config['port'] . "/{$auth_db}{$retry_write}"))->selectDatabase($this->_config['dbname']);
        }

        return $this->_adapter;
    }

    /**
     * @param $collection
     * @return MongoDB\Collection
     */
    public function selectCollection($collection)
    {
        return $this->getAdapter()->selectCollection($collection);
    }

    /**
     * @param $collection
     * @param $document
     * @return int
     */
    public function insert($collection, $document)
    {
        return $this->selectCollection($collection)->insertOne($document)->getInsertedCount();
    }

    /**
     * @param $collection
     * @param $documents
     * @return int
     */
    public function insertMulti($collection, $documents)
    {
        return $this->selectCollection($collection)->insertMany($documents)->getInsertedCount();
    }

    /**
     * @param $collection
     * @param $filter
     * @param $document
     * @param null $options
     * @return mixed
     */
    public function update($collection, $filter, $document, $options = null)
    {
        $options = is_array($options) ? $options : [];

        return $this->selectCollection($collection)->updateOne($filter, $document, $options)->getModifiedCount();
    }

    /**
     * @param $collection
     * @param $filter
     * @param $document
     * @param null $options
     * @return mixed
     */
    public function updateMany($collection, $filter, $document, $options = null)
    {
        $options = is_array($options) ? $options : [];

        return $this->selectCollection($collection)->updateMany($filter, $document, $options)->getModifiedCount();
    }
}

function makeRequestChecksum($request_string, $secret_key)
{
    if (!is_string($secret_key) || $secret_key === '') {
        throw new Exception('Secret key is empty');
    }

    $hmac = hash_hmac('sha256', $request_string, $secret_key);

    return md5($hmac);
}

if (!isset($_SERVER['SERVER_NAME']) || !$_SERVER['SERVER_NAME']) {
    if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']) {
        $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
    }
}
