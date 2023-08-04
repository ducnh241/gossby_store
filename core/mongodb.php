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
 * OSC_Framework::MongoDB
 *
 * @package OSC_Core
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Mongodb extends OSC_Object {

    /**
     *
     * @var array
     */
    protected $_adapters = [];

    const BIN_DEFAULT = 'default';

    public function __construct() {
        parent::__construct();
    }

    /**
     *
     * @param string $bind
     * @return MongoDB\Client
     */
    public function getAdapter($bind = null) {
        $adapters = &$this->_adapters;

        if (!$bind) {
            $bind = static::BIN_DEFAULT;
        }

        $db_config = OSC::systemRegistry('mongodb_config');

        $instance = $db_config['bind'][$bind];

        if (!isset($adapters[$instance])) {
            $config = $db_config['instance'][$instance];

            $auth = '';

            if (isset($config['username']) && isset($config['password']) && $config['username'] && $config['password']) {
                $auth .= $config['username'] . ':' . $config['password'] . '@';
            }

            $retry_write = '';
            if (isset($config['env']) && $config['env'] === 'production') {
                $retry_write = '?retryWrites=false';
            }

            $auth_db = $config['dbname'];
            if (isset($config['auth_dbname']) && !empty($config['auth_dbname'])) {
                $auth_db = $config['auth_dbname'];
            }

            $use_tls = [];
            if (isset($config['tls_enable']) && $config['tls_enable']) {
                $TLS_DIR = $config['tls_dir'] ?? '';
                if (empty($TLS_DIR)) {
                    throw new Exception("Invalid TLS dir config");
                }

                $use_tls = ['tls' => 'true', 'tlsCAFile' => $TLS_DIR];
            }

            $adapters[$instance] = (new MongoDB\Client('mongodb://' . $auth . $config['host'] . ':' . $config['port'] . "/{$auth_db}{$retry_write}", $use_tls))->selectDatabase($config['dbname']);
        }

        return $adapters[$instance];
    }

    /**
     * @param $collection
     * @param null $bind
     * @return MongoDB\Collection
     * @throws Exception
     */
    public function selectCollection($collection, $bind = null) {
        return $this->getAdapter($bind)->selectCollection($collection);
    }

    /**
     * @param $collection
     * @param $document
     * @param null $bind
     * @return int
     * @throws Exception
     */
    public function insert($collection, $document, $bind = null, $options = []) {

        try {
            $result = $this->selectCollection($collection, $bind)->insertOne($document, $options)->getInsertedCount();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return $result;
    }

    /**
     * @param $collection
     * @param $documents
     * @param null $bind
     * @return int
     * @throws Exception
     */
    public function insertMulti($collection, $documents, $bind = null, $options = []) {
        try {
            $result = $this->selectCollection($collection, $bind)->insertMany($documents, $options)->getInsertedCount();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return $result;
    }

    /**
     * @param $collection
     * @param $filter
     * @param $document
     * @param null $options
     * @param null $bind
     * @return int|null
     * @throws Exception
     */
    public function update($collection, $filter, $document, $options = null, $bind = null) {
        $options = is_array($options) ? $options : [];

        try {
            $result = $this->selectCollection($collection, $bind)
                ->updateOne($filter, $document, $options)
                ->getModifiedCount();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return $result;
    }

    /**
     * @param $collection
     * @param $filter
     * @param $document
     * @param null $options
     * @param null $bind
     * @return int|null
     * @throws Exception
     */
    public function updateMany($collection, $filter, $document, $options = null, $bind = null) {
        $options = is_array($options) ? $options : [];

        try {
            $result = $this->selectCollection($collection, $bind)
                ->updateMany($filter, $document, $options)
                ->getModifiedCount();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return $result;
    }

    /**
     * @param $collection
     * @param $filter
     * @param null $options
     * @param null $bind
     * @return int
     * @throws Exception
     */
    public function delete($collection, $filter, $options = null, $bind = null) {
        $options = is_array($options) ? $options : [];

        try {
            $result = $this->selectCollection($collection, $bind)->deleteOne($filter, $options)->getDeletedCount();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return $result;
    }

    /**
     * @param $collection
     * @param $filter
     * @param null $options
     * @param null $bind
     * @return int
     * @throws Exception
     */
    public function deleteMany($collection, $filter, $options = null, $bind = null) {
        $options = is_array($options) ? $options : [];

        try {
            $result = $this->selectCollection($collection, $bind)->deleteMany($filter, $options)->getDeletedCount();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return $result;
    }

    /**
     * @param $key
     * @param $data
     * @throws Exception
     */
    public static function registerInstance($key, $data) {
        $db_config = OSC::systemRegistry('mongodb_config');

        if (!isset($db_config['instance'])) {
            $db_config['instance'] = [];
        }

        if (isset($db_config['instance'][$key])) {
            throw new Exception("Instance mongodb_config {$key} is already declared");
        }
        $db_config['instance'][$key] = $data;

        OSC::systemRegister('mongodb_config', $db_config);
    }

    /**
     * @param $src_key
     * @param $dest_key
     * @throws Exception
     */
    public static function cloneInstance($src_key, $dest_key) {
        $db_config = OSC::systemRegistry('mongodb_config');

        if (!isset($db_config['instance']) || !is_array($db_config['instance']) || !isset($db_config['instance'][$src_key])) {
            throw new Exception('MongoDB instance [' . $src_key . '] is not exists');
        }

        $db_config['instance'][$dest_key] = $db_config['instance'][$src_key];

        OSC::systemRegister('mongodb_config', $db_config);
    }

    /**
     * @param $bind
     * @param $instance
     * @throws OSC_Exception
     */
    public static function registerBind($bind, $instance) {
        $db_config = OSC::systemRegistry('mongodb_config');

        if (!isset($db_config['bind'])) {
            $db_config['bind'] = array();
        }

        $db_config['bind'][$bind] = $instance;

        OSC::systemRegister('mongodb_config', $db_config);
    }
}