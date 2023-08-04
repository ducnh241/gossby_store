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
 * @package OSC_Core
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Database_Adapter extends OSC_Object {

    /**
     *
     * @var array 
     */
    protected $_config;

    /**
     *
     * @var string 
     */
    protected $_prefix;

    /**
     *
     * @var null 
     */
    protected $_transaction_null = null;

    /**
     *
     * @var array 
     */
    protected $_transaction = array();

    /**
     *
     * @var array 
     */
    protected $_transaction_logs = array();

    /**
     *
     * @var string 
     */
    protected $_default_trans_key = 'default';

    /**
     *
     * @var int
     */
    protected $_profiling_flag = 0;

    /**
     *
     * @var PDO
     */
    protected $_dbh = null;

    /**
     *
     * @var OSC_Database_Condition
     */
    protected $_cond = null;

    /**
     *
     * @var int
     */
    protected $_auto_lock_select_query = 0;

    public function setConfig($config) {
        $this->_prefix = isset($config['prefix']) && $config['prefix'] ? $config['prefix'] : '';
        $this->_config = $config;
    }

    public function __destruct() {
        $this->disconnect();
    }

    public function disconnect() {
        if (!isset($this->_config['persistent']) || !$this->_config['persistent']) {
            $this->_dbh = null;
        }
    }

    /**
     * 
     * @param boolean $new_instance
     * @return OSC_Database_Condition
     */
    public function getCondition($new_instance = false) {
        if ($this->_cond === null || $new_instance) {
            $cond = OSC::core('database_condition', null);

            if ($new_instance) {
                return $cond;
            }

            $this->_cond = $cond;
        }

        return $this->_cond;
    }

    /**
     * 
     * @param mixed $condition
     * @return array
     */
    public function parseCondition($condition) {
        if (is_array($condition)) {
            if (!isset($condition['condition'])) {
                $condition = $this->getCondition()->reset()->parse($condition)->getPdoData();
                $this->getCondition()->reset();
            }
        } else if (is_object($condition)) {
            if (method_exists($condition, 'getPdoData')) {
                $condition = $condition->getPdoData();
            } else {
                $condition = false;
            }
        } else {
            $condition = array('condition' => $condition, 'params' => null);
        }

        if ($condition && (!is_array($condition) || !isset($condition['condition']) || !$condition['condition'])) {
            $condition = false;
        }

        return $condition;
    }

    /**
     * 
     * @return PDO
     * @throws OSC_Exception_Database
     */
    public function getDbh() {
        if ($this->_dbh === null) {
            $_conf = & $this->_config;

            if (!isset($_conf['port'])) {
                $_conf['port'] = 3306;
            }

            $opts = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
            );

            if (isset($_conf['persistent']) && $_conf['persistent']) {
                $opts[PDO::ATTR_PERSISTENT] = true;
            }

            try {
                $this->_dbh = new PDO('mysql:host=' . $_conf['host'] . ';port=' . $_conf['port'] . ';dbname=' . $_conf['database'], $_conf['user'], $_conf['pass'], $opts);
            } catch (PDOException $ex) {
                throw new OSC_Exception_Database($ex->getMessage(), $ex->getCode(), $ex);
            }

            if (OSC_ENV_DEBUG_DB > 0) {
                $this->setProfiling(true);
            }

            //$this->query("SET time_zone = '+00:00'");
        }

        return $this->_dbh;
    }

    /**
     * 
     * @param string $key
     * @return string
     */
    protected function _getTransactionKey($key) {
        return $key ? $key : $this->_default_trans_key;
    }

    /**
     * 
     * @param string $key
     * @return &array
     */
    protected function &_getTransaction($key) {
        $key = $this->_getTransactionKey($key);

        if (!isset($this->_transaction[$key])) {
            return $this->_transaction_null;
        }

        return $this->_transaction[$key];
    }

    public function setProfiling($flag = true) {
        if (OSC_ENV_DEBUG_DB > 0 && (!$flag || $this->_profiling_flag)) {
            return $this;
        }

        $this->_profiling_flag = $flag ? 1 : 0;

        try {
            if ($this->_profiling_flag > 0) {
                $this->getDbh()->prepare('SET @@profiling_history_size = 1')->execute();
            }

            $this->getDbh()->prepare('SET @@profiling = ' . $this->_profiling_flag)->execute();
        } catch (PDOException $ext) {
            $this->_profiling_flag = 0;
        }

        return $this;
    }

    public function getProfiling() {
        if ($this->_profiling_flag < 1) {
            return false;
        }

        try {
            $stmt = $this->getDbh()->prepare('SHOW profiles');
            $stmt->execute();
        } catch (PDOException $ext) {
            return false;
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getQueryTime() {
        $profiling = $this->getProfiling();

        if (!$profiling) {
            return 0;
        }

        return floatval($profiling['Duration']) * 1000;
    }

    /**
     * 
     * @param string $fields
     * @param mixed $tbl
     * @param mixed $condition
     * @param string $order
     * @param mixed $limit
     * @param string $key
     * @return OSC_Database_Adapter
     */
    public function select($fields, $tbl, $condition = null, $order = null, $limit = null, $key = null) {
        if ($this->_auto_lock_select_query == 1) {
            return $this->selectForUpdate($fields, $tbl, $condition, $order, $limit, $key);
        } else if ($this->_auto_lock_select_query == 2) {
            return $this->selectForUpdateInShareMode($fields, $tbl, $condition, $order, $limit, $key);
        }

        $query_data = $this->_buildSelectQuery($tbl, $fields, $condition, $order, $limit);

        $this->_transaction[$this->_getTransactionKey($key)] = array(
            'query' => $query_data['query'],
            'params' => $query_data['params'],
            'error' => null,
            'file' => $this->_getCallFromFile()
        );

        $this->exec($key);

        return $this;
    }

    public function selectForUpdate($fields, $tbl, $condition = null, $order = null, $limit = null, $key = null) {
        $query_data = $this->_buildSelectQuery($tbl, $fields, $condition, $order, $limit);

        $query_data['query'] .= ' FOR UPDATE';

        $this->_transaction[$this->_getTransactionKey($key)] = array(
            'query' => $query_data['query'],
            'params' => $query_data['params'],
            'error' => null,
            'file' => $this->_getCallFromFile()
        );

        $this->exec($key);

        return $this;
    }

    public function selectForUpdateInShareMode($fields, $tbl, $condition = null, $order = null, $limit = null, $key = null) {
        $query_data = $this->_buildSelectQuery($tbl, $fields, $condition, $order, $limit);

        $query_data['query'] .= ' LOCK IN SHARE MODE';

        $this->_transaction[$this->_getTransactionKey($key)] = array(
            'query' => $query_data['query'],
            'params' => $query_data['params'],
            'error' => null,
            'file' => $this->_getCallFromFile()
        );

        $this->exec($key);

        return $this;
    }

    protected function _buildSelectQuery($tbl, $fields, $condition, $order, $limit) {
        if (is_array($tbl)) {
            $as = key($tbl);
            $_tbl = trim($tbl[$as]);
            $as = trim($as);

            if (strpos($_tbl, '/') !== false) {
                $_tbl = OSC::model($_tbl)->getTableName();
            }

            $tbl = "`" . $this->_prefix . "{$_tbl}` AS `{$as}`";

            unset($_tbl);
            unset($as);
        } else {
            if (strpos($tbl, '/') !== false) {
                $tbl = OSC::model($tbl)->getTableName();
            }

            $tbl = $this->_prefix . trim($tbl);
        }

        $query = "SELECT " . $fields . " FROM " . $tbl;

        $params = null;

        if ($condition) {
            $condition = $this->parseCondition($condition);

            if ($condition) {
                $query .= " WHERE " . $condition['condition'];
                $params = $condition['params'];
            }
        }

        if ($order) {
            $query .= " ORDER BY " . $order;
        }

        if ($limit !== null) {
            if (!is_array($limit)) {
                $limit = intval($limit);

                if ($limit > 0) {
                    $query .= " LIMIT " . $limit;
                }
            } else if (count($limit) == 2) {
                $query .= " LIMIT " . $limit[0] . "," . $limit[1];
            }
        }

        return ['query' => $query, 'params' => $params];
    }

    protected function _buildAdvancedSelectTransaction($parts) {
        if (is_array($parts['from'])) {
            $as = key($parts['from']);
            $_tbl = trim($parts['from'][$as]);
            $as = trim($as);

            if (strpos($_tbl, '/') !== false) {
                $_tbl = OSC::model($_tbl)->getTableName();
            }

            $parts['from'] = "`" . $this->_prefix . "{$_tbl}` AS `{$as}`";
        } else {
            if (strpos($parts['from'], '/') !== false) {
                $parts['from'] = OSC::model($parts['from'])->getTableName();
            }

            $parts['from'] = $this->_prefix . trim($parts['from']);
        }

        $query = "SELECT " . $parts['select'] . " FROM " . $parts['from'];

        if (isset($parts['join']) && is_array($parts['join']) && count($parts['join']) > 0) {
            foreach ($parts['join'] as $idx => $join) {
                if (!isset($join['type']) || !in_array($join['type'], array(OSC_Database::JOIN_TYPE_INNER, OSC_Database::JOIN_TYPE_LEFT))) {
                    continue;
                }

                if (!isset($join['table'])) {
                    continue;
                }

                if (!is_array($join['table'])) {
                    continue;
                }

                $as = key($join['table']);
                $_tbl = trim($join['table'][$as]);
                $as = trim($as);

                if (strpos($_tbl, '/') !== false) {
                    $_tbl = OSC::model($_tbl)->getTableName();
                }

                $join['table'] = "`" . $this->_prefix . "{$_tbl}` AS `{$as}`";

                if (!isset($join['condition'])) {
                    continue;
                }

                $query .= ' ' . ($join['type'] == OSC_Database::JOIN_TYPE_INNER ? 'INNER JOIN' : 'LEFT JOIN') . ' ' . $join['table'] . ' ON ' . $join['condition'];
            }
        }

        $params = null;

        if (isset($parts['condition']) && $parts['condition']) {
            $parts['condition'] = $this->parseCondition($parts['condition']);

            if ($parts['condition']) {
                $query .= " WHERE " . $parts['condition']['condition'];
                $params = $parts['condition']['params'];
            }
        }

        if (isset($parts['group']) && $parts['group']) {
            $query .= " GROUP BY " . $parts['group'];
        }

        if (isset($parts['having']) && $parts['having']) {
            $parts['having'] = $this->parseCondition($parts['having']);

            if ($parts['having']) {
                $query .= " HAVING " . $parts['having']['condition'];

                if (is_array($parts['having']['params'])) {
                    if (!is_array($params)) {
                        $params = array();
                    }

                    $params = array_merge($params, $parts['having']['params']);
                }
            }
        }

        if (isset($parts['order']) && $parts['order']) {
            $query .= " ORDER BY " . $parts['order'];
        }

        if (isset($parts['limit']) && $parts['limit'] !== null) {
            if (!is_array($parts['limit'])) {
                $parts['limit'] = intval($parts['limit']);

                if ($parts['limit'] > 0) {
                    $query .= " LIMIT " . $parts['limit'];
                }
            } else if (count($parts['limit']) == 2) {
                $query .= " LIMIT " . $parts['limit'][0] . "," . $parts['limit'][1];
            }
        }

        $transaction = array(
            'query' => $query,
            'params' => $params,
            'error' => null,
            'file' => $this->_getCallFromFile()
        );

        return $transaction;
    }

    protected function _getCallFromFile() {
        if (OSC_ENV_DEBUG_DB < 1) {
            return '';
        }

        $backtrace = debug_backtrace(false, 10);

        $files = [];

        foreach ($backtrace as $step) {
            if (!isset($step['class']) || (strtolower($step['class']) != 'osc_database_adapter' && !is_subclass_of($step['class'], 'OSC_Database_Adapter'))) {
                $files[] = $step['file'] . '[' . $step['line'] . ']';
            }
        }

        return $files;
    }

    /**
     * 
     * @param array $parts
     * @param string $key
     * @return OSC_Database_Adapter
     */
    public function selectAdvanced($parts, $key = null) {
        $this->_transaction[$this->_getTransactionKey($key)] = $this->_buildAdvancedSelectTransaction($parts);

        $this->exec($key);

        return $this;
    }

    /**
     * 
     * @param string $tbl
     * @param array $data
     * @param string $key
     * @return integer
     */
    public function insert($tbl, $data, $key = null) {
        $tbl = trim($tbl);

        if (strpos($tbl, '/') !== false) {
            $tbl = OSC::model($tbl)->getTableName();
        }

        $data = $this->_compileInsertString($data);

        $query = "INSERT INTO {$this->_prefix}{$tbl} (" . $data['column'] . ") VALUES (" . $data['anchor'] . ");";

        $this->_transaction[$this->_getTransactionKey($key)] = array(
            'query' => $query,
            'params' => $data['params'],
            'error' => null,
            'file' => $this->_getCallFromFile()
        );

        $this->exec($key);

        return $this->getNumAffected($key);
    }

    /**
     * Compile insert data (array) to string
     *
     * @param  array $data
     * @return array
     */
    protected function _compileInsertString($data) {
        $f = array('column' => '',
            'anchor' => '',
            'params' => array());

        foreach ($data as $k => $v) {
            $f['column'] .= "`{$k}`,";
            $f['anchor'] .= ":{$k},";
            $f['params'][$k] = $v === false ? '' : $v;
        }

        $f['column'] = preg_replace("/,$/", "", $f['column']);
        $f['anchor'] = preg_replace("/,$/", "", $f['anchor']);

        return $f;
    }

    /**
     * 
     * @param string $tbl
     * @param array $data
     * @param mixed $condition
     * @param integer $limit
     * @param string $key
     * @return integer
     */
    public function update($tbl, $data, $condition = null, $limit = null, $key = null) {
        $tbl = trim($tbl);

        if (strpos($tbl, '/') !== false) {
            $tbl = OSC::model($tbl)->getTableName();
        }

        $data = $this->_compileUpdateString($data);

        $query = "UPDATE `{$this->_prefix}{$tbl}` SET {$data['anchor']}";

        if ($condition) {
            $condition = $this->parseCondition($condition);

            if ($condition) {
                $query .= " WHERE " . $condition['condition'];

                if (is_array($condition['params'])) {
                    $data['params'] = array_merge($data['params'], $condition['params']);
                }
            }
        }

        if ($limit > 0) {
            $query .= " LIMIT " . $limit;
        }

        $this->_transaction[$this->_getTransactionKey($key)] = array(
            'query' => $query,
            'params' => $data['params'],
            'error' => null,
            'file' => $this->_getCallFromFile()
        );

        $this->exec($key);

        return $this->getNumAffected($key);
    }

    /**
     * 
     * @param array $data
     * @return array
     */
    protected function _compileUpdateString($data) {
        $f = array('anchor' => '', 'params' => array());

        foreach ($data as $k => $v) {
            $anchor = 's___' . $k;
            $f['anchor'] .= "`{$k}`=:{$anchor},";
            $f['params'][$anchor] = $v;
        }

        $f['anchor'] = preg_replace("/,$/", "", $f['anchor']);

        return $f;
    }

    /**
     * 
     * @param string $tbl
     * @param mixed $condition
     * @param integer $limit
     * @param string $key
     * @return integer
     */
    public function delete($tbl, $condition = null, $limit = null, $key = null) {
        $tbl = trim($tbl);

        if (strpos($tbl, '/') !== false) {
            $tbl = OSC::model($tbl)->getTableName();
        }

        $query = "DELETE FROM `{$this->_prefix}{$tbl}`";

        $params = null;

        if ($condition) {
            $condition = $this->parseCondition($condition);

            if ($condition) {
                $query .= " WHERE " . $condition['condition'];
                $params = $condition['params'];
            }
        }

        if ($limit > 0) {
            $query .= " LIMIT " . $limit;
        }

        $this->_transaction[$this->_getTransactionKey($key)] = array(
            'query' => $query,
            'params' => $params,
            'error' => null,
            'file' => $this->_getCallFromFile()
        );

        $this->exec($key);

        return $this->getNumAffected($key);
    }

    /**
     * 
     * @param string $query
     * @param array $params
     * @param string $key
     * @return OSC_Database_Adapter
     */
    public function query($query, $params = array(), $key = null) {
        $this->_transaction[$this->_getTransactionKey($key)] = array(
            'query' => $query,
            'params' => $params,
            'error' => null,
            'file' => $this->_getCallFromFile()
        );

        return $this->exec($key);
    }

    /**
     * 
     * @param string $key
     * @return stdClass
     */
    public function fetch($key = null) {
        $transaction = & $this->_getTransaction($key);

        if ($transaction && isset($transaction['stmt'])) {
            if ($transaction['stmt'] instanceof PDOStatement) {
                return $transaction['stmt']->fetch(PDO::FETCH_OBJ);
            }
        }

        return false;
    }

    /**
     * 
     * @param string $key
     * @return array
     */
    public function fetchAll($key = null) {
        $transaction = & $this->_getTransaction($key);

        if ($transaction && isset($transaction['stmt'])) {
            if ($transaction['stmt'] instanceof PDOStatement) {
                return $transaction['stmt']->fetchAll(PDO::FETCH_OBJ);
            }
        }

        return array();
    }

    /**
     * 
     * @param string $key
     * @return array
     */
    public function fetchNumber($key = null) {
        $transaction = & $this->_getTransaction($key);

        if ($transaction && isset($transaction['stmt'])) {
            if ($transaction['stmt'] instanceof PDOStatement) {
                return $transaction['stmt']->fetch(PDO::FETCH_NUM);
            }
        }

        return false;
    }

    /**
     * 
     * @param string $key
     * @return array
     */
    public function fetchNumberAll($key = null) {
        $transaction = & $this->_getTransaction($key);

        if ($transaction && isset($transaction['stmt'])) {
            if ($transaction['stmt'] instanceof PDOStatement) {
                return $transaction['stmt']->fetchAll(PDO::FETCH_NUM);
            }
        }

        return array();
    }

    /**
     * 
     * @param string $key
     * @return array
     */
    public function fetchArray($key = null) {
        $transaction = & $this->_getTransaction($key);

        if ($transaction && isset($transaction['stmt'])) {
            if ($transaction['stmt'] instanceof PDOStatement) {
                return $transaction['stmt']->fetch(PDO::FETCH_ASSOC);
            }
        }

        return false;
    }

    /**
     * 
     * @param string $key
     * @return array
     */
    public function fetchArrayAll($key = null) {
        $transaction = & $this->_getTransaction($key);

        if ($transaction && isset($transaction['stmt'])) {
            if ($transaction['stmt'] instanceof PDOStatement) {
                return $transaction['stmt']->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return array();
    }

    /**
     * 
     * @param string $key
     * @return integer
     */
    public function rowCount($key = null) {
        return $this->getNumAffected($key);
    }

    /**
     * 
     * @param string $col
     * @param string $from
     * @param mixed $condition
     * @param string $key
     * @return integer
     */
    public function count($col, $from, $condition = null, $key = null) {
        return $this->select("COUNT('{$col}') AS `total`", $from, $condition, null, null, $key)->fetch($key)->total;
    }

    /**
     * 
     * @param array $parts
     * @param string $key
     * @return integer
     */
    public function countAdvanced($parts = null, $key = null) {
        if (isset($parts['group'])) {
            $parts['select'] = "{$parts['count']}";
            $transaction = $this->_buildAdvancedSelectTransaction($parts);
            $transaction['query'] = "SELECT COUNT(*) AS `total` FROM ({$transaction['query']}) AS counter_tmp_tbl";

            $this->_transaction[$this->_getTransactionKey($key)] = $transaction;

            $this->exec($key);
        } else {
            $parts['select'] = "COUNT({$parts['count']}) AS `total`";
            $this->selectAdvanced($parts, $key);
        }

        $this->exec($key);

        return $this->fetch($key)->total;
    }

    /**
     * 
     * @return integer
     */
    public function getInsertedId() {
        if (OSC_ENV_DEBUG_DB < 1) {
            return $this->getDbh()->lastInsertId();
        }

        try {
            return $this->query("SELECT LAST_INSERT_ID() AS id", null, 'getLastInsertId')->fetch('getLastInsertId')->id;
        } catch (Exception $ex) {
            return $this->getDbh()->lastInsertId();
        }
    }

    /**
     * 
     * @param string $key
     * @return integer
     */
    public function getNumAffected($key = null) {
        $transaction = & $this->_getTransaction($key);

        if ($transaction && isset($transaction['stmt'])) {
            if ($transaction['stmt'] instanceof PDOStatement) {
                return $transaction['stmt']->rowCount();
            }
        }

        return 0;
    }

    public function begin(int $auto_lock_select_query = 0) {
        $this->getDbh()->beginTransaction();

        if ($auto_lock_select_query > 0) {
            $this->_auto_lock_select_query = $auto_lock_select_query;
        }

        return $this;
    }

    public function rollback() {
        $this->_auto_lock_select_query = 0;

        $this->getDbh()->rollBack();

        return $this;
    }

    public function commit() {
        $this->_auto_lock_select_query = 0;

        $this->getDbh()->commit();

        return $this;
    }

    /**
     * 
     * @param sring $key
     * @return OSC_Database_Adapter
     */
    public function free($key = null) {
        $transaction = & $this->_getTransaction($key);

        if ($transaction && isset($transaction['stmt'])) {
            if ($transaction['stmt'] instanceof PDOStatement) {
                $transaction['stmt']->closeCursor();
            }

            $transaction = null;
            unset($this->_transaction[$this->_getTransactionKey($key)]);
        }

        return $this;
    }

    /**
     * 
     * @param string $key
     * @return OSC_Database_Adapter
     * @throws OSC_Exception_Database
     */
    public function exec($key = null) {
        $transaction = & $this->_getTransaction($key);

        if (!$transaction) {
            return $this;
        }

        OSC::core('debug')->startProcess('OSC.QueryDB', $transaction['query']);

        try {
            if (OSC_ENV_DEBUG_DB) {
                $transaction['query'] = trim($transaction['query']);
                $transaction['query'] = preg_replace('/;+$/', '', $transaction['query']);
                $transaction['query'] .= " /*" . preg_replace('/[^a-zA-Z0-9\-\_\.\/\[\]]/', '', $transaction['file'][0]) . "*/;";
            }

            $stmt = $this->getDbh()->prepare($transaction['query']);

            if (isset($transaction['params']) && is_array($transaction['params']) && count($transaction['params']) > 0) {
                $stmt->execute($transaction['params']);
            } else {
                $transaction['params'] = null;
                $stmt->execute();
            }

            $transaction['stmt'] = $stmt;
        } catch (PDOException $e) {
            $table_name = '';

            if (preg_match('/^.+?[^a-z0-9\_\-]+(osc_[a-zA-Z0-9\_\-]+)[^a-zA-Z0-9\_\-].*$/', $transaction['query'], $matches)) {
                $table_name = $matches[1];
            }

            $transaction['error'] = array(
                'key' => $key,
                'query' => $transaction['query'],
                'params' => $transaction['params'],
                'file' => $transaction['file'],
                'error' => $e->getCode(),
                'message' => ($table_name ? ('[' . $table_name . '] ') : '') . $e->getMessage()
            );

            $profiling = $this->getProfiling();

            if ($profiling) {
                if (is_array($transaction['error']['params']) && count($transaction['error']['params'])) {
                    $transaction['error']['query_parsed'] = $profiling['Query'];
                }

                $transaction['error']['query_time'] = (floatval($profiling['Duration']) * 1000) . 'ms';
            }

            if (OSC_ENV_DEBUG_DB > 0) {
                $this->_transaction_logs[] = $transaction['error'];
            }

            OSC::core('debug')->endProcess('OSC.QueryDB');

            throw new OSC_Exception_Database('ERROR [' . $e->getCode() . '] :: ' . ($table_name ? ('[' . $table_name . '] ') : '') . $e->getMessage());
        }

        if (OSC_ENV_DEBUG_DB > 0) {
            $transaction_log = array(
                'key' => $key,
                'query' => $transaction['query'],
                'params' => $transaction['params'],
                'affected' => $this->getNumAffected($key),
                'file' => $transaction['file']
            );

            $profiling = $this->getProfiling();

            if ($profiling) {
                if (is_array($transaction_log['params']) && count($transaction_log['params'])) {
                    $transaction_log['query_parsed'] = $profiling['Query'];
                }

                $transaction_log['query_time'] = (floatval($profiling['Duration']) * 1000) . 'ms';
            }

            $this->_transaction_logs[] = $transaction_log;
        }

        OSC::core('debug')->endProcess('OSC.QueryDB');

        return $this;
    }

    public function getErrorInfo($key = null) {
        $transaction = & $this->_getTransaction($key);

        if (!$transaction) {
            return null;
        }

        return $transaction['error'];
    }

    public function getQueryLog() {
        return $this->_transaction_logs;
    }

    /**
     * 
     * @param string $tbl
     * @return string
     */
    public function getTableName($tbl) {
        return $this->prefix . $tbl;
    }

}
