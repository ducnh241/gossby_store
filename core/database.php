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
 * OSC_Framework::Database
 *
 * @package OSC_Core
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Database extends OSC_Object {

    /**
     *
     * @var array
     */
    protected $_adapters = array();

    /**
     *
     * @var boolean
     */
    protected $_static_load_collection = false;

    const BIN_READ = 'read';
    const BIN_WRITE = 'write';
    const OPERATOR_EQUAL = 'EQUAL';
    const OPERATOR_NOT_EQUAL = '!EQUAL';
    const OPERATOR_LESS_THAN = 'LESS_THAN';
    const OPERATOR_LESS_THAN_OR_EQUAL = 'LESS_THAN_OR_EQUAL';
    const OPERATOR_GREATER_THAN = 'GREATER_THAN';
    const OPERATOR_GREATER_THAN_OR_EQUAL = 'GREATER_THAN_OR_EQUAL';
    const OPERATOR_EXACT = 'EXACT';
    const OPERATOR_NOT_EXACT = '!EXACT';
    const OPERATOR_LIKE = 'LIKE';
    const OPERATOR_NOT_LIKE = '!LIKE';
    const OPERATOR_LIKE_LEFT = 'LIKE_LEFT';
    const OPERATOR_NOT_LIKE_LEFT = '!LIKE_LEFT';
    const OPERATOR_LIKE_RIGHT = 'LIKE_RIGHT';
    const OPERATOR_NOT_LIKE_RIGHT = '!LIKE_RIGHT';
    const OPERATOR_REGEXP = 'REGEXP';
    const OPERATOR_NOT_REGEXP = '!REGEXP';
    const OPERATOR_IN = 'IN';
    const OPERATOR_NOT_IN = '!IN';
    const OPERATOR_BETWEEN = 'BETWEEN';
    const OPERATOR_NOT_BETWEEN = '!BETWEEN';
    const OPERATOR_FULLTEXT = 'FULLTEXT';
    const OPERATOR_FIND_IN_SET = 'FIND_IN_SET';
    const NEGATION_MARK = '!';
    const RELATION_AND = 'AND';
    const RELATION_OR = 'OR';
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';
    const JOIN_TYPE_INNER = 'INNER';
    const JOIN_TYPE_LEFT = 'LEFT';

    public function __construct() {
        parent::__construct();

        if (OSC::DB_STATIC_LOAD_COLLECTION) {
            $this->_static_load_collection = true;
        }
    }

    /**
     * 
     * @param string $bind
     * @return OSC_Database_Adapter
     */
    public function getAdapter($bind = null) {
        $adapters = & $this->_adapters;

        if (!$bind) {
            $bind = static::BIN_READ;
        }

        $db_config = OSC::systemRegistry('database_config');

        $instance = $db_config['bind'][$bind];

        if (!isset($adapters[$instance])) {
            $config = $db_config['instance'][$instance];

            $config['prefix'] = OSC::systemRegistry('db_prefix');

            $adapters[$instance] = OSC::core('database_adapter', $instance);
            $adapters[$instance]->setConfig($config);
        }

        return $adapters[$instance];
    }

    /**
     * 
     * @return OSC_Database_Adapter
     */
    public function getReadAdapter() {
        return $this->getAdapter(static::BIN_READ);
    }

    /**
     * 
     * @return OSC_Database_Adapter
     */
    public function getWriteAdapter() {
        return $this->getAdapter(static::BIN_WRITE);
    }

    /**
     * 
     * @param string $tbl
     * @return string
     */
    public function getTableName($tbl) {
        return $this->getAdapter()->getTableName($tbl);
    }

    public function setProfiling($flag = true, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        $this->getAdapter($bind)->setProfiling($flag);

        return $this;
    }

    public function getProfiling($bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        return $this->getAdapter($bind)->getProfiling();
    }

    public function getQueryTime($bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        return $this->getAdapter($bind)->getQueryTime();
    }

    /**
     * 
     * @param string $fields
     * @param string $from
     * @param mixed $condition
     * @param string $order
     * @param integer $limit
     * @param string $key
     * @param string $bind
     * @return OSC_Database
     */
    public function select($fields, $from, $condition = null, $order = null, $limit = null, $key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        $this->getAdapter($bind)->select($fields, $from, $condition, $order, $limit, $key);

        return $this;
    }

    /**
     * 
     * @param array $parts
     * @param string $key
     * @param string $bind
     * @return OSC_Database
     */
    public function selectAdvanced($parts, $key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        $this->getAdapter($bind)->selectAdvanced($parts, $key);

        return $this;
    }

    /**
     * 
     * @param string $col
     * @param string $from
     * @param mixed $condition
     * @param string $key
     * @param string $bind
     * @return integer
     */
    public function count($col, $from, $condition = null, $key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        return $this->getAdapter($bind)->count($col, $from, $condition, $key);
    }

    /**
     * 
     * @param mixed $parts
     * @param string $key
     * @param string $bind
     * @return integer
     */
    public function countAdvanced($parts, $key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        return $this->getAdapter($bind)->countAdvanced($parts, $key);
    }

    /**
     * 
     * @param string $key
     * @param string $bind
     * @return stdClass
     */
    public function fetch($key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        return $this->getAdapter($bind)->fetch($key);
    }

    /**
     * 
     * @param string $key
     * @param string $bind
     * @return array
     */
    public function fetchAll($key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        return $this->getAdapter($bind)->fetchAll($key);
    }

    /**
     * 
     * @param string $key
     * @param string $bind
     * @return array
     */
    public function fetchNumber($key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        return $this->getAdapter($bind)->fetchNumber($key);
    }

    /**
     * 
     * @param string $key
     * @param string $bind
     * @return array
     */
    public function fetchNumberAll($key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        return $this->getAdapter($bind)->fetchNumberAll($key);
    }

    /**
     * 
     * @param string $key
     * @param string $bind
     * @return array
     */
    public function fetchArray($key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        return $this->getAdapter($bind)->fetchArray($key);
    }

    /**
     * 
     * @param string $key
     * @param string $bind
     * @return array
     */
    public function fetchArrayAll($key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        return $this->getAdapter($bind)->fetchArrayAll($key);
    }

    /**
     * 
     * @param string $key
     * @param string $bind
     * @return integer
     */
    public function rowCount($key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        return $this->getAdapter($bind)->rowCount($key);
    }

    public function getNextInsertId($tbl, $col = 'id', $key = 'default') {
        return $this->getLastId($tbl, $col, $key) + 1;
    }

    public function getLastId($tbl, $col = 'id', $key = 'default') {
        $this->execQuery($this->_qBuilder->select("MAX(`{$col}`) AS `lastId`", $tbl), $key);

        $result = $this->fetchRows($key);

        return $result['lastId'];
    }

    /**
     * 
     * @param string $tbl
     * @param array $data
     * @param string $key
     * @param string $bind
     * @return integer
     */
    public function insert($tbl, $data, $key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_WRITE;
        }

        return $this->getAdapter($bind)->insert($tbl, $data, $key);
    }

    /**
     * 
     * @param string $bind
     * @return integer
     */
    public function getInsertedId($bind = null) {
        if (!$bind) {
            $bind = static::BIN_WRITE;
        }

        return $this->getAdapter($bind)->getInsertedId();
    }

    /**
     * 
     * @param string $tbl
     * @param array $data
     * @param mixed $condition
     * @param integer $limit
     * @param string $key
     * @param string $bind
     * @return integer
     */
    public function update($tbl, $data, $condition = null, $limit = null, $key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_WRITE;
        }

        return $this->getAdapter($bind)->update($tbl, $data, $condition, $limit, $key);
    }

    /**
     * 
     * @param string $tbl
     * @param mixed $condition
     * @param integer $limit
     * @param string $key
     * @param string $bind
     * @return integer
     */
    public function delete($tbl, $condition = null, $limit = null, $key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_WRITE;
        }

        return $this->getAdapter($bind)->delete($tbl, $condition, $limit, $key);
    }

    /**
     * 
     * @param string $query
     * @param mixed $params
     * @param string $key
     * @param string $bind
     * @return OSC_Database
     */
    public function query($query, $params = array(), $key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        $this->getAdapter($bind)->query($query, $params, $key);

        return $this;
    }

    /**
     * 
     * @param string $key
     * @param string $bind
     * @return OSC_Database
     */
    public function free($key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_READ;
        }

        $this->getAdapter($bind)->free($key);

        return $this;
    }

    /**
     * Calculate relevance of a string
     *
     * @staticvar Integer $id
     * @param String $keyword
     * @return Float
     */
    public function calFulltextRelevance($keyword) {
        static $id = 0;

        $id++;

        $_prefix = $this->_prefix;

        $query = <<<EOF
CREATE TEMPORARY TABLE IF NOT EXISTS `{$_prefix}_fulltext_pre` (
  `keyword_id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword_content` varchar(255) NOT NULL,
  PRIMARY KEY (`keyword_id`),
  FULLTEXT KEY `keyword_content` (`keyword_content`)
) ENGINE=MyISAM;
EOF;

        $this->execQuery($query, 'calRelevance-' . $id);

        $query = "INSERT INTO `{$_prefix}_fulltext_pre` (`keyword_id`,`keyword_content`) VALUES (NULL, \"{$keyword}\" );";

        $this->execQuery($query, 'calRelevance-' . $id);

        $keyword_id = $this->getInsertedId();

        $query = "SELECT (MATCH(keyword_content) AGAINST (\"{$keyword}\" IN BOOLEAN MODE)) AS `score` FROM `{$_prefix}_fulltext_pre` WHERE `keyword_id` = {$keyword_id} LIMIT 1;";

        $this->execQuery($query, 'calRelevance-' . $id);

        $result = $this->fetchRows('calRelevance-' . $id);

        $this->free('calRelevance-' . $id);

        return $result['score'];
    }

    /**
     * 
     * @param string $key
     * @param string $bind
     * @return integer
     */
    public function getNumAffected($key = null, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_WRITE;
        }

        return $this->getAdapter($bind)->getNumAffected($key);
    }

    /**
     * 
     * @param string $key
     * @param string $bind
     * @return integer
     */
    public function getErrorInfo($key, $bind = null) {
        if (!$bind) {
            $bind = static::BIN_WRITE;
        }

        return $this->getAdapter($bind)->getNumAffected($key);
    }

    public function getLog() {
        $adapters = & $this->_adapters;

        $logs = array();

        foreach ($adapters as $instance => $adapter) {
            $logs[$instance] = $adapter->getQueryLog();
        }

        return $logs;
    }

    public static function registerDBInstance($key, $data) {
        $db_config = OSC::systemRegistry('database_config');

        if (!isset($db_config['instance'])) {
            $db_config['instance'] = [];
        }

        if (isset($db_config['instance'][$key])) {
            throw new Exception("Instance database_config {$key} is already declared");
        }
        $db_config['instance'][$key] = $data;

        OSC::systemRegister('database_config', $db_config);
    }

    public static function cloneDBInstance($src_key, $dest_key) {
        $db_config = OSC::systemRegistry('database_config');

        if (!isset($db_config['instance']) || !is_array($db_config['instance']) || !isset($db_config['instance'][$src_key])) {
            throw new Exception('DB instance [' . $src_key . '] is not exists');
        }

        $db_config['instance'][$dest_key] = $db_config['instance'][$src_key];

        OSC::systemRegister('database_config', $db_config);
    }

    public static function registerDBBind($bind, $instance) {
        $db_config = OSC::systemRegistry('database_config');

        if (!isset($db_config['bind'])) {
            $db_config['bind'] = array();
        }

        $db_config['bind'][$bind] = $instance;

        OSC::systemRegister('database_config', $db_config);
    }

}
