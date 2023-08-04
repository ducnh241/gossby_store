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
class OSC_Database_Model extends OSC_Object {

    /**
     *
     * @var string
     */
    protected $_model_key = null;

    /**
     *
     * @var string
     */
    protected $_table_name = '';

    /**
     *
     * @var string
     */
    protected $_pk_field = '';

    /**
     *
     * @var string
     */
    protected $_ukey_field = 'ukey';

    /**
     *
     * @var boolean
     */
    protected $_ukey_editable = false;

    /**
     *
     * @var string
     */
    protected $_temp_ukey = null;

    /**
     *
     * @var string
     */
    protected $_ukey_prefix = '';

    /**
     *
     * @var string
     */
    protected $_ukey_condition_operator = 'EQUAL';

    /**
     *
     * @var mixed
     */
    protected $_condition = null;

    /**
     *
     * @var string
     */
    protected $_action_flag = null;

    /**
     *
     * @var string
     */
    protected $_last_action_flag = null;

    /**
     *
     * @var boolean
     */
    protected $_error_flag = false;

    /**
     *
     * @var array
     */
    protected $_error_info = array('type' => '', 'message' => '', 'data' => '');

    /**
     *
     * @var boolean
     */
    protected $_collection_locked = false;

    /**
     *
     * @var OSC_Database_Model_Collection
     */
    protected $_collection = null;
    protected $_model_locked_flag = false;

    /**
     *
     * @var boolean
     */
    protected $_cache_enable = false;

    /**
     *
     * @var string
     */
    protected $_cache_key = null;

    /**
     *
     * @var type 
     */
    protected $_cache_adapter = null;

    /**
     *
     * @var string
     */
    protected $_cache_bind = null;

    /**
     *
     * @var string
     */
    private $__default_cache_bind = 'model';

    /**
     *
     * @var array
     */
    public $data = array();

    /**
     *
     * @var array
     */
    protected $_orig_data = array();

    /**
     *
     * @var array
     */
    protected $_data_modified_map = array();

    /**
     *
     * @var array
     */
    protected $_option_conf = array('value' => 'id', 'label' => 'title');

    /**
     *
     * @var array
     */
    protected static $_preloaded_model = array();
    protected static $_preloaded_model_locked = null;
    protected static $__DESCRIBE = null;

    const _DB_BIN_READ = 'read';
    const _DB_BIN_WRITE = 'write';
    const INSERT_FLAG = 'INSERT';
    const UPDATE_FLAG = 'UPDATE';
    const DELETE_FLAG = 'DELETE';
    const ERROR_TYPE_SQL = 'SQL';
    const ERROR_TYPE_CONDITION = 'CONDITION';
    const __DESCRIBE = [];

    protected function _encode($data) {
        return OSC::encode($data);
    }

    protected function _decode($data) {
        return OSC::decode($data, true);
    }

    /**
     * 
     */
    public function __construct() {
        parent::__construct();

        $this->_table_name = strtolower($this->_table_name);

        if (!$this->_model_key) {
            $class = get_class($this);
            preg_match('/^[^_]+_([^_]+)_(.+)$/', $class, $matches);
            $this->_model_key = lcfirst($matches[1]) . '/' . preg_replace_callback('/_([a-z])/', function($match) {
                        return '_' . lcfirst($match[1]);
                    }, lcfirst($matches[2]));
        }
    }

    public static function fetchTableDescribe($table_name, $_bind = null) {
        static $__DESCRIBE = null;
        $db_config = OSC::systemRegistry('database_config');

        if (!is_array($__DESCRIBE)) {
            $__DESCRIBE = OSC::coreCacheGet('db_describe');

            if (!is_array($__DESCRIBE)) {
                $__DESCRIBE = [];

                $db_config['bind'] = $db_config['bind'] ?? [];
                $db_config['instance'] = $db_config['instance'] ?? [];
                $instances = array_keys($db_config['instance']);

                if (count($db_config['bind']) > 0) {
                    foreach ($db_config['bind'] as $bind => $instance) {
                        if (!in_array($instance, $instances) || isset($__DESCRIBE[$instance])) {
                            continue;
                        }

                        $DB = OSC::core('database')->getAdapter($bind);

                        $tables = [];

                        $DB->query("SELECT LOWER(TABLE_NAME) as `Table`,COLUMN_NAME as `Field`,COLUMN_TYPE as `Type`,IS_NULLABLE as `Null`,COLUMN_KEY as `Key`,COLUMN_DEFAULT as `Default`,EXTRA as `Extra` FROM information_schema.columns WHERE table_schema = '{$db_config['instance'][$instance]['database']}'", null, 'fetch_table_list');

                        while($row = $DB->fetchArray('fetch_table_list')) {
                            if(! isset($tables[$row['Table']])) {
                                $tables[$row['Table']] = [];
                            }

                            $tables[$row['Table']][] = $row;
                        }

                        foreach($tables as $table_name => $columns) {
                            $__DESCRIBE[$instance][$table_name] = static::_processTableDescribeInfo($columns);                            
                        }
                    }
                }

                OSC::coreCacheSet('db_describe', $__DESCRIBE);
            }
        }

        $table_name = strtolower($table_name);
        $_instance = $db_config['bind'][$_bind];
        if (!isset($__DESCRIBE[$_instance][$table_name])) {
            $__DESCRIBE[$_instance][$table_name] = [];
        }

        return $__DESCRIBE[$_instance][$table_name];
    }

    protected static function _processTableDescribeInfo($columns) {
        $describe = [];
        $default_length = [
            'varchar' => 255,
            'int' => 11
        ];

        foreach ($columns as $column) {
            preg_match('/^([0-9a-zA-Z\_]+)(\((.+)\))?(\s+(unsigned))?(\s+(zerofill))?$/', $column['Type'], $matches);

            $column_describe = [
                'name' => $column['Field'],
                'type' => $matches[1],
                'null' => $column['Null'] == 'NO',
                'default' => $column['Default']
            ];

            if (in_array($column_describe['type'], ['tinyint', 'smallint', 'int', 'mediumint', 'bigint', 'decimal', 'numeric', 'float', 'double'])) {
                $column_describe['unsigned'] = isset($matches[5]) && $matches[5] !== '';
            }

            if ($column_describe['type'] == 'enum') {
                $enum_values = [];

                $matches[3] = explode(',', $matches[3]);

                foreach ($matches[3] as $enum_value) {
                    $enum_value = trim($enum_value);
                    $enum_values[] = substr($enum_value, 1, -1);
                }

                $column_describe['enum_values'] = $enum_values;
            } else if ($matches[3]) {
                $column_describe['max_length'] = $matches[3];
            } else if (isset($default_length[$column_describe['type']])) {
                $column_describe['max_length'] = $default_length[$column_describe['type']];
            }

            $describe[$column_describe['name']] = $column_describe;
        }

        return $describe;
    }

    protected function _getDBDescribe($bind = null) {
        if (is_array(static::__DESCRIBE) && count(static::__DESCRIBE) > 0) {
            return static::__DESCRIBE;
        }

        return OSC_Database_Model::fetchTableDescribe($this->getTableName(true), $bind);
    }

    public function lock() {
        $this->_model_locked_flag = true;
        return $this;
    }

    public function unlock() {
        $this->_model_locked_flag = false;
        return $this;
    }

    /**
     * Property and method call overloading
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments) {
        if (preg_match('/^(get|set)([A-Z])(.*)$/', $name, $matches)) {
            $action = $matches[1];
            $variable = strtolower($matches[2]) . $matches[3];

            $using_orig_data = false;

            if ($action == 'get') {
                if (count($arguments) == 1 && $arguments[0]) {
                    $using_orig_data = true;
                }
            } else {
                if (count($arguments) == 2 && $arguments[1]) {
                    $using_orig_data = true;
                }
            }

            if ($using_orig_data ? isset($this->_orig_data) && is_array($this->_orig_data) : isset($this->data) && is_array($this->data)) {
                $_variable = preg_replace_callback('/([A-Z])/', function($match) {
                    return '_' . strtolower($match[1]);
                }, $variable);

                if ($action == 'set') {
                    return $this->setData($_variable, $arguments[0], $using_orig_data);
                } else {
                    if ($using_orig_data) {
                        if (isset($this->_orig_data[$_variable])) {
                            return $this->_orig_data[$_variable];
                        }
                    } else if (isset($this->data[$_variable])) {
                        return $this->data[$_variable];
                    }
                }
            }

            return false;
        }

        throw new OSC_Exception_Runtime("The function [" . get_class($this) . ":{$name}] is not exists", 1000);
    }

    /**
     * 
     * @return array
     */
    public function __toArray() {
        return $this->data;
    }

    /**
     *
     * @return array
     */
    public function toArray() {
        return $this->__toArray();
    }

    /**
     * 
     * @return string
     */
    protected function _getCollectionClassName() {
        return get_class($this) . '_Collection';
    }

    /**
     *
     * @return OSC_Database_Model_Collection
     */
    public function getCollection() {
        if ($this->_collection_locked) {
            throw new OSC_Exception_Runtime($this->getModelKey() . ': Collection disabled');
        }

        if ($this->_collection === null) {
            $collection_class = $this->_getCollectionClassName();

            $params = array();

            $this->_beforeGetCollection($params);

            $this->_collection = OSC::obj($collection_class, null, $params);

            if (!is_object($this->_collection) || !($this->_collection instanceof $collection_class)) {
                throw new OSC_Exception_Runtime($this->getModelKey() . ': Collection is not exists [' . $collection_class . ']');
            }
        }

        return $this->_collection;
    }

    /**
     * 
     * @param array $params
     */
    protected function _beforeGetCollection(&$params) {
        $params['model'] = $this;
        $params['table_name'] = $this->_table_name;
        $params['pk_field'] = $this->_pk_field;
        $params['ukey_field'] = $this->_ukey_field;
        $params['option_conf'] = $this->_option_conf;
    }

    /**
     * 
     * @param boolean $orig
     * @return integer
     */
    public function getId($orig = false) {
        if ($orig) {
            $pointer = & $this->_orig_data;
        } else {
            $pointer = & $this->data;
        }

        if (!isset($pointer[$this->_pk_field])) {
            return 0;
        }

        return $pointer[$this->_pk_field];
    }

    /**
     * 
     * @param boolean $orig
     * @return string
     */
    public function getUkey($orig = false) {
        if (!$this->_ukey_field) {
            return null;
        }

        if ($orig) {
            $pointer = & $this->_orig_data;
        } else {
            $pointer = & $this->data;
        }

        if (!isset($pointer[$this->_ukey_field])) {
            return null;
        }

        return $pointer[$this->_ukey_field];
    }

    /**
     *
     * @return OSC_Database_Model
     */
    public function setId($value, $orig = false) {
        return $this->setData($this->_pk_field, $value, $orig);
    }

    /**
     * 
     * @param string $key
     * @param boolean $get_orig
     * @return mixed
     */
    public function getData($key, $get_orig = false) {
        if ($get_orig) {
            $pointer = & $this->_orig_data;
        } else {
            $pointer = & $this->data;
        }

        if (!isset($pointer[$key])) {
            return false;
        }

        return $pointer[$key];
    }

    /**
     * @example $this->setData('field', 'value'); // set field value
     * @example $this->setData('field', 'value', true); // set original field value
     * @example $this->setData(array('field1' => 'value1', 'field2' => 'value2')); // set multi field value
     * @example $this->setData(array('field1' => 'value1', 'field2' => 'value2'), true); // set multi original field value
     * 
     * @return OSC_Database_Model
     */
    public function setData() {
        $args = func_num_args();

        if ($args < 1) {
            return $this;
        }

        $data = array();

        $data = func_get_arg(0);

        if (is_array($data)) {
            if (count($data) < 1) {
                return $this;
            }

            $update_orig_data = $args == 2 && func_get_arg(1);
        } else {
            if ($data) {
                $data = array($data => func_get_arg(1));
            }

            $update_orig_data = $args == 3 && func_get_arg(2);
        }

        if ($update_orig_data) {
            $pointer = & $this->_orig_data;
        } else {
            $pointer = & $this->data;
        }

        if (!is_array($data)) {
            if (!$update_orig_data) {
                foreach ($pointer as $k => $v) {
                    $this->_data_modified_map[$k] = 1;
                }
            }

            $pointer = array();

            return $this;
        }

        foreach ($data as $k => $v) {
            $k = strtolower($k);

            if ($v === false) {
                unset($pointer[$k]);
            } else {
                $pointer[$k] = $v;
            }

            if (!$update_orig_data) {
                $this->_data_modified_map[$k] = 1;
            }
        }

        return $this;
    }

    public function revert() {
        $this->resetDataModifiedMap();

        $this->data = $this->_orig_data;

        return $this;
    }

    /**
     * 
     * @return OSC_Database_Model
     */
    public function resetDataModifiedMap() {
        $this->_data_modified_map = array();
        return $this;
    }

    /**
     * 
     * @param boolean $get_bak
     * @return array
     */
    public function getModifiedData() {
        $modified_data = array();

        $data = & $this->data;

        foreach ($this->_data_modified_map as $k => $v) {
            if ($v == 1) {
                $modified_data[$k] = isset($data[$k]) ? $data[$k] : null;
            }
        }

        return $modified_data;
    }

    /**
     * @return boolean
     */
    public function isModified() {
        if (func_num_args() < 1) {
            foreach ($this->_data_modified_map as $k => $v) {
                if ($v == 1) {
                    return true;
                }
            }

            return false;
        }

        $keys = array();

        foreach (func_get_args() as $k) {
            if (is_array($k)) {
                foreach ($k as $_k) {
                    $keys[] = $_k;
                }
            } else {
                $keys[] = $k;
            }
        }

        $keys = array_unique($keys);

        foreach ($keys as $k) {
            if (!isset($this->_data_modified_map[$k]) || $this->_data_modified_map[$k] != 1) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * @return array
     */
    protected function _collectDataForSave() {
        return $this->getModifiedData();
    }

    /**
     * 
     * @return boolean
     */
    public function isExists() {
        return isset($this->_orig_data[$this->_pk_field]);
    }

    /**
     * 
     * @throws Exception
     */
    public function reload() {
        if ($this->getId() < 1) {
            throw new Exception('Model need loaded before call the function');
        }

        $id = $this->getId();

        $this->_reset();

        try {
            $this->load($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return $this;
    }

    /**
     *
     * @param integer $id
     * @return OSC_Database_Model
     */
    public function load($id = 0) {
        $condition = $this->_getCondition($id);

        if (!$condition) {
            throw new OSC_Database_Model_Exception("Cannot detect model [{$this->getModelKey()}] load condition.".$_SERVER['REQUEST_URI']);
        }

        $this->_beforeLoad();

        $data = null;
        $cache = null;
        $cache_key = null;

        if ($this->usingCache() && $this->_cache_key) {
            $cache_key = $this->_cache_key;

            $cache_obj = $this->getCacheObj();

            $cache_obj->setKey($cache_key);

            $data = $cache_obj->get();
        }

        $this->_cache_key = null;

        if (!$data) {
            $DB = $this->getReadAdapter();

            $db_transaction_key = 'model_load__' . $this->_table_name;

            try {
                $DB->select('*', $this->_table_name, $condition, null, 1, $db_transaction_key);
            } catch (OSC_Exception_Database $ex) {
                throw new OSC_Database_Model_Exception($ex->getMessage());
            }

            $data = $DB->fetchArray($db_transaction_key);

            $DB->free($db_transaction_key);

            if (!$data) {
                throw new OSC_Database_Model_Exception("Model load failed: " . $this->getModelKey() . ':' . $id, 404);
            }

            $data_decoded = $data;

            $this->_preDataForUsing($data_decoded);

            foreach ($data as $k => $v) {
                if (isset($data_decoded[$k])) {
                    $data[$k] = $data_decoded[$k];
                }
            }

            if ($cache_key) {
                $cache_obj->set($data);
            }
        }

        $this->bind($data, false, false);

        $this->_afterLoad();

        $this->resetDataModifiedMap();

        return $this;
    }

    public function bind($data, $raw_data = true, $reset_modified_map_flag = true) {
        $this->_collection_locked = true;

        if ($raw_data) {
            $data_decoded = $data;

            $this->_preDataForUsing($data_decoded);

            foreach ($data as $k => $v) {
                if (isset($data_decoded[$k])) {
                    $data[$k] = $data_decoded[$k];
                }
            }
        }

        $this->_orig_data = count($this->_orig_data) > 0 ? $this->data : $data;

        $this->data = $data;

        if ($reset_modified_map_flag) {
            $this->resetDataModifiedMap();
        }

        $this->_condition = null;

        return $this;
    }

    /**
     * 
     * @param array &$data
     */
    protected function _preDataForUsing(&$data) {
        if ($this->_table_name) {
            $describe = $this->_getDBDescribe(static::_DB_BIN_READ);

            foreach ($data as $field_name => $field_value) {
                if (!isset($describe[$field_name])) {
                    continue;
                }

                $field_config = $describe[$field_name];

                if (in_array($field_config['type'], ['tinyint', 'smallint', 'int', 'mediumint', 'bigint', 'decimal', 'numeric', 'float', 'double'], true)) {
                    if (in_array($field_config['type'], ['tinyint', 'smallint', 'int', 'mediumint', 'bigint'], true)) {
                        $field_value = intval($field_value);
                    } else {
                        $field_value = floatval($field_value);
                    }
                }

                $data[$field_name] = $field_value;
            }
        }
    }

    protected function _beforeLoad() {
        
    }

    protected function _afterLoad() {
        
    }

    /**
     * 
     * @param mixed $condition
     * @return $this
     */
    public function setCondition($condition) {
        $this->_condition = $condition;

        return $this;
    }

    /**
     * 
     * @param string $key
     * @return OSC_Database_Model
     */
    public function loadByUKey($key) {
        $key = static::cleanUkey($key);

        if (!$key) {
            throw new Exception('Ukey is empty');
        }

        if ($this->usingCache()) {
            return $this->load($this->translateUkey($key));
        }

        return $this->setCondition(['field' => $this->_ukey_field, 'value' => $key, 'operator' => $this->_ukey_condition_operator])->load();
    }

    /**
     * 
     * @param boolean $use_temp
     * @return string
     */
    protected function _makeUkey($use_temp = false, $uniqid = null) {
        if ($use_temp) {
            if (!$this->_temp_ukey) {
                $this->_temp_ukey = $this->_makeUkey();
            }

            return $this->_temp_ukey;
        }

        return $this->_ukey_prefix . ($uniqid ? md5($uniqid) : OSC::makeUniqid());
    }

    /**
     * 
     * @param string $key
     * @return integer
     */
    public function translateUkey($key) {
        if ($this->usingCache()) {
            $translate_key = $this->getUkeyTranslateKey($key);

            if ($translate_key) {
                $id = $this->getCacheObj()->getAdapter()->get($translate_key);

                if ($id) {
                    return $id;
                }
            }
        }

        $pk_field = $this->_pk_field;

        $db_transaction_key = 'translate_ukey';

        $DB = $this->getReadAdapter();

        $id = 0;

        $DB->query("SELECT {$pk_field} FROM `{$this->getTableName(true)}` WHERE `{$this->_ukey_field}` = :ukey LIMIT 1", array('ukey' => $key), $db_transaction_key);

        if ($row = $DB->fetchArray($db_transaction_key)) {
            if ($this->usingCache()) {
                if (!$translate_key) {
                    $translate_key = $this->getUkeyTranslateKey($key);
                }

                $this->getCacheObj()->getAdapter()->set($translate_key, $row[$pk_field]);
            }

            $id = $row[$pk_field];
        }

        $DB->free($db_transaction_key);

        if ($id < 1) {
            throw new Exception('Record not found', 404);
        }

        return $id;
    }

    /**
     * 
     * @param string $flag
     * @param boolean $skip_count_data
     * @return \OSC_Database_Model
     */
    public function save($flag = null, $skip_count_data = null) {
        if ((!is_array($this->data) || count($this->data) < 1 || !$this->isModified()) && !$skip_count_data) {
            return $this;
        }

        if (!isset($this->_orig_data[$this->_pk_field])) {
            $id = false;
        } else {
            $id = $this->_orig_data[$this->_pk_field];
        }

        if ($flag) {
            switch ($flag) {
                case static::INSERT_FLAG:
                    $this->setId(null)->setId(null, true);
                    break;
                case static::UPDATE_FLAG:
                    if (!$id) {
                        throw new OSC_Database_Model_Exception('Try update a empty model');
                    }
                    break;
                default:
                    $flag = null;
            }
        }

        if (!$flag) {
            $flag = $id ? static::UPDATE_FLAG : static::INSERT_FLAG;
        }

        if ($this->_model_locked_flag) {
            throw new OSC_Database_Model_Exception('Cannot update a locked model');
        }

        $this->_action_flag = $flag;
        $this->_beforeSave();
        $result = 0;

        if (in_array($this->_action_flag, array(self::UPDATE_FLAG, self::INSERT_FLAG))) {
            $data_to_save = $this->_collectDataForSave();
            $data_to_save_encoded = $data_to_save;

            $this->_preDataForSave($data_to_save_encoded);

            foreach ($data_to_save as $k => $v) {
                if (isset($data_to_save_encoded[$k])) {
                    $data_to_save[$k] = $data_to_save_encoded[$k];
                }
            }

            $DB = $this->getWriteAdapter();

            $db_transaction_key = 'model_save_row';

            $error_info = null;

            try {
                switch ($this->_action_flag) {
                    case static::UPDATE_FLAG:
                        $result = $DB->update($this->_table_name, $data_to_save, $this->_getCondition($id), 1, $db_transaction_key);
                        break;
                    case static::INSERT_FLAG:
                        $result = $DB->insert($this->_table_name, $data_to_save, $db_transaction_key);

                        if ($result) {
                            if (!isset($data_to_save[$this->_pk_field])) {
                                $data_to_save[$this->_pk_field] = $DB->getInsertedId();
                            }

                            $this->setId($data_to_save[$this->_pk_field]);
                        }
                        break;
                    default:
                }
            } catch (OSC_Exception_Database $ex) {
                $result = -1;
                $error_info = $DB->getErrorInfo($db_transaction_key);
            }

            $DB->free($db_transaction_key);
        }

        $this->_last_action_flag = $this->_action_flag;

        $this->_action_flag = null;

        if ($result < 1) {
            if ($result < 0) {
                $this->_error = true;
                $this->_error_info = array(
                    'type' => static::ERROR_TYPE_SQL,
                    'message' => $error_info['message'],
                    'data' => $error_info
                );
            } else {
                if ($this->_last_action_flag == static::UPDATE_FLAG) {
                    $this->resetDataModifiedMap();
                    return $this;
                }
            }

            $this->dispatchEvent('save_error', array('model' => $this, 'error_info' => $this->_error_info));
            OSC::core('observer')->dispatchEvent('model__' . $this->_table_name . '__save_error', array('model' => $this));
            throw new OSC_Database_Model_Exception(is_array($this->_error_info['message']) ? implode("<br />", $this->_error_info['message']) : $this->_error_info['message']);
        }

        $data_decoded = $data_to_save;

        $this->_preDataForUsing($data_decoded);

        foreach ($data_to_save as $k => $v) {
            if (isset($data_decoded[$k])) {
                $data_to_save[$k] = $data_decoded[$k];
            }
        }

        $this->bind(array_merge($this->_orig_data, $data_to_save), false, false);

        $this->_afterSave();

        $this->resetDataModifiedMap();

        return $this;
    }

    protected function _beforeSave() {
        $data = $this->_collectDataForSave();


        if ($this->getActionFlag() != static::INSERT_FLAG) {
            if (isset($data[$this->_pk_field])) {
                unset($data[$this->_pk_field]);
            }

            if ($this->_ukey_field && isset($data[$this->_ukey_field])) {
                $old_ukey = $this->getData($this->_ukey_field, true);

                if (!$old_ukey || !$this->_ukey_editable) {
                    unset($data[$this->_ukey_field]);
                } else {
                    $this->register('__OLD_UKEY', $old_ukey);
                }
            }
        }
        if ($this->_table_name) {
            $describe = $this->_getDBDescribe(static::_DB_BIN_READ);

            foreach ($data as $field_name => $field_value) {
                if (!isset($describe[$field_name])) {
                    OSC::core('debug')->triggerError('Table ' . $this->getTableName() . ' dont have field ' . $field_name);
                }

                $field_config = $describe[$field_name];

                if ($field_config['type'] == 'enum') {

                } else if (in_array($field_config['type'], ['tinyint', 'smallint', 'int', 'mediumint', 'bigint', 'decimal', 'numeric', 'float', 'double'], true)) {
                    if (in_array($field_config['type'], ['tinyint', 'smallint', 'int', 'mediumint', 'bigint'], true)) {
                        $field_value = intval($field_value);
                    } else {
                        $field_value = floatval($field_value);
                    }

                    if ($field_config['unsigned'] && $field_value < 0) {
                        OSC::core('debug')->triggerError('Field ' . $this->getTableName() . '.' . $field_name . ' is unsigned, ' . $field_value . ' is passed');
                    }
                }
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        $this->dispatchEvent('before_save', array('model' => $this));
        OSC::core('observer')->dispatchEvent('model__' . $this->_table_name . '__before_save', array('model' => $this));

        return true;
    }

    protected function _afterSave() {
        $this->dispatchEvent('after_save', array('model' => $this));
        OSC::core('observer')->dispatchEvent('model__' . $this->_table_name . '__after_save', array('model' => $this));

        if ($this->usingCache()) {
            if ($this->getLastActionFlag() == static::INSERT_FLAG) {
                $this->getCacheObj()->set($this->data);
            } else {
                if ($this->_ukey_field && $this->isModified($this->_ukey_field)) {
                    $old_ukey = $this->registry('__OLD_UKEY');

                    if ($old_ukey) {
                        $this->register('__OLD_UKEY', null);
                        $this->getCacheObj()->getAdapter()->delete($this->getUkeyTranslateKey($old_ukey));
                    }
                }

                $this->getCacheObj()->delete();
            }
        }
    }

    /**
     * 
     * @param array &$data
     */
    protected function _preDataForSave(&$data) {
        
    }

    public function setCache() {
        if ($this->_model_locked_flag || !$this->usingCache()) {
            throw new OSC_Database_Model_Exception("The model is locked, cannot set cache for model");
        }

        $this->getCacheObj()->set($this->data);

        return $this;
    }

    /**
     * 
     * @return OSC_Database_Model
     */
    public function delete() {
        if ($this->_model_locked_flag) {
            throw new OSC_Database_Model_Exception('Cannot delete a locked model');
        }

        $id = $this->getId(true);

        if ($id < 1) {
            throw new OSC_Database_Model_Exception("The model [{$this->getModelKey()}] have to load before exec delete command");
        }

        $condition = $this->_getCondition($id);

        if (!$condition) {
            throw new OSC_Database_Model_Exception("The model cannot detect delete condition");
        }

        $this->_action_flag = static::DELETE_FLAG;

        $this->_beforeDelete();

        $db_transaction_key = 'model_delete';

        $DB = $this->getWriteAdapter();

        $result = 0;

        if ($this->_action_flag == static::DELETE_FLAG) {
            try {
                $result = $DB->delete($this->_table_name, $condition, 1, $db_transaction_key);
            } catch (OSC_Exception_Database $e) {
                $result = -1;
                $error_info = $DB->getErrorInfo($db_transaction_key);
            }
        }

        $DB->free($db_transaction_key);

        $this->_last_action_flag = $this->_action_flag;

        $this->_action_flag = null;

        if ($result < 1) {
            if ($result < 0) {
                $this->_error = true;
                $this->_error_info = array(
                    'type' => static::ERROR_TYPE_SQL,
                    'message' => $error_info['message'],
                    'data' => $error_info
                );
            }

            $this->dispatchEvent('delete_error', array('model' => $this, 'error_info' => $this->_error_info));
            OSC::core('observer')->dispatchEvent('model__' . $this->getTableName() . '__delete_error', array('model' => $this));
            throw new OSC_Database_Model_Exception(is_array($this->_error_info['message']) ? implode("<br />", $this->_error_info['message']) : $this->_error_info['message']);
        }

        $this->setId(null, true);

        $this->_afterDelete();

        $this->dispatchEvent('deleted', array('model' => $this));
        OSC::core('observer')->dispatchEvent('model__' . $this->getTableName() . '__row_deleted', array('model' => $this));

        return $this;
    }

    protected function _beforeDelete() {
        
    }

    protected function _afterDelete() {
        if ($this->usingCache()) {
            $this->getCacheObj()->delete();
        }
    }

    /**
     * 
     * @param integer $id
     * @return mixed
     */
    protected function _getCondition($id) {
        if (!$this->_condition) {
            $id = intval($id);

            if ($id < 1) {
                return false;
            }

            $this->_cache_key = $this->getCacheKey($id);

            $condition = OSC::core('database')->getReadAdapter()
                    ->getCondition()
                    ->reset()
                    ->parse(array('field' => $this->_pk_field, 'value' => $id))
                    ->getPdoData();
        } else {
            if (is_array($this->_condition)) {
                if (!isset($this->_condition['condition'])) {
                    $condition = OSC::core('database')->getReadAdapter()
                            ->getCondition()
                            ->reset()
                            ->parse($this->_condition)
                            ->getPdoData();
                } else {
                    $condition = $this->_condition;
                }
            } else if ($this->_condition instanceof OSC_Database_Condition) {
                $condition = $this->_condition->getPdoData();
            } else {
                $condition = array('condition' => (string) $this->_condition, 'params' => null);
            }
        }

        if (!$condition) {
            return false;
        }

        return $condition;
    }

    /**
     * 
     * @return boolean
     */
    public function usingCache() {
        return OSC::systemRegistry('cache_model') && $this->_cache_enable;
    }

    /**
     * 
     * @param integer $id
     * @return string
     */
    public function getCacheKey($id = null) {
        $id = intval($id);

        return strtolower(str_replace('/', '.', $this->_model_key) . '__' . ($id > 0 ? $id : $this->getId()));
    }

    /**
     * 
     * @param string $ukey
     * @return string
     */
    public function getUkeyTranslateKey($ukey) {
        return 'ukey_2_id_' . strtolower($this->getTableName()) . '_' . $ukey;
    }

    /**
     *
     * @return OSC_Database_Model_Abstract_Cache_Model 
     */
    public function getCacheObj() {
        if (!$this->usingCache()) {
            throw new OSC_Exception_Runtime('Model [' . $this->getModelKey() . '] is not support cache');
        }

        if ($this->_cache_adapter === null) {
            $cache_adapter = OSC::core('cache')->getAdapter($this->_cache_bind ? $this->_cache_bind : $this->__default_cache_bind);

            if (!$cache_adapter) {
                $cache_adapter = OSC::core('cache')->getAdapter($this->__default_cache_bind);
            }

            $this->_cache_adapter = OSC::core('database_model_cache_' . $cache_adapter->getType() . '_model', null)->setAdapter($cache_adapter)->setKey($this->getCacheKey());
        }

        return $this->_cache_adapter;
    }

    /**
     * 
     * @param integer $id
     * @return OSC_Database_Model
     */
    public function resetCache($id = null) {
        if ($this->usingCache()) {
            $id = intval($id);

            if ($id < 1) {
                $id = $this->getId();
            }

            if ($id > 0) {
                $this->getCacheObj()->delete(null, $this->getCacheKey($id));
            }
        }

        return $this;
    }

    /**
     * 
     * @return OSC_Database_Adapter
     */
    public function getReadAdapter() {
        return OSC::core('database')->getAdapter(static::_DB_BIN_READ);
    }

    /**
     * 
     * @return OSC_Database_Adapter
     */
    public function getWriteAdapter() {
        return OSC::core('database')->getAdapter(static::_DB_BIN_WRITE);
    }

    /**
     * 
     * @return string
     */
    public function getModelKey() {
        return $this->_model_key;
    }

    /**
     * @return OSC_Database_Model
     */
    public function getNullModel() {
        return OSC::model($this->getModelKey());
    }

    /**
     * 
     * @return OSC_Database_Model_Collection
     */
    public function getNullCollection() {
        return $this->getNullModel()->getCollection();
    }

    /**
     * 
     * @param boolean $inc_prefix
     * @return string
     */
    public function getTableName($inc_prefix = false) {
        return ($inc_prefix ? OSC::systemRegistry('db_prefix') : '') . $this->_table_name;
    }

    /**
     * 
     * @return string
     */
    public function getPkFieldName() {
        return $this->_pk_field;
    }

    /**
     * 
     * @return string
     */
    public function getUkeyFieldName() {
        return $this->_ukey_field;
    }

    /**
     * 
     * @return string
     */
    public function getActionFlag() {
        return $this->_action_flag;
    }

    /**
     * 
     * @return string
     */
    public function getLastActionFlag() {
        return $this->_last_action_flag;
    }

    /**
     * 
     * @return string
     */
    public function getErrorMessage() {
        return $this->_error_info['message'];
    }

    /**
     * 
     * @return string
     */
    public function getErrorType() {
        return $this->_error_info['type'];
    }

    /**
     * 
     * @return mixed
     */
    public function getErrorData() {
        return $this->_error_info['data'];
    }

    /**
     * 
     * @return array
     */
    public function getErrorInfo() {
        return $this->_error_info;
    }

    /**
     * 
     * @param string $message
     * @param mixed $data
     * @param boolean $block_action
     * @param string $type
     * @return OSC_Database_Model
     */
    protected function _error($message = '', $data = array(), $block_action = true, $type = null) {
        if (!$type) {
            $type = static::ERROR_TYPE_CONDITION;
        }

        if ($block_action) {
            $this->_action_flag = null;
        }

        $this->_error_info = array(
            'type' => $type,
            'message' => $message,
            'data' => $data
        );

        return $this;
    }

    public function increment($field_name, $value = 1, $modified_field = null) {
        if ($this->getId() < 1) {
            throw new OSC_Database_Model_Exception("Model cần được load trước khi thực thi tác vụ increment");
        }

        $value = intval($value);

        $DB = $this->getWriteAdapter();

        $db_transaction_key = 'increment_field_value';

        try {
            if ($modified_field) {
                $modified_field = ",`{$modified_field}`=" . time();
            } else {
                $modified_field = '';
            }

            $DB->query("UPDATE `{$this->getTableName(true)}` SET `{$field_name}` = (`{$field_name}` + {$value}){$modified_field} WHERE `{$this->_pk_field}` = {$this->getId()} LIMIT 1", null, $db_transaction_key);
        } catch (OSC_Exception_Database $ex) {
            throw new OSC_Database_Model_Exception($ex->getMessage());
        }

        if ($DB->getNumAffected($db_transaction_key) < 1) {
            throw new OSC_Database_Model_Exception("Không thể cập nhật database");
        }

        $new_value = false;

        if ($this->usingCache()) {
            $new_value = $this->getCacheObj()->increment($field_name, $value);
        }

        if ($new_value === false) {
            $DB->select($field_name, $this->_table_name, array('condition' => "{$this->_pk_field} = {$this->getId()}"), null, 1, $db_transaction_key);

            $row = $DB->fetchArray($db_transaction_key);

            if (!$row) {
                $new_value = $this->data[$field_name] + $value;
            }

            $new_value = $row[$field_name];
        }

        $DB->free($db_transaction_key);

        $this->data[$field_name] = $new_value;
        $this->_orig_data[$field_name] = $new_value;

        unset($this->_data_modified_map[$field_name]);

        return $new_value;
    }

    public static function cleanUkey($ukey) {
        $ukey = preg_replace('/[^a-zA-Z0-9\.\/\-\_\:]/', '', $ukey);
        $ukey = preg_replace('/(^[\.\/\-\_\:]+|[\.\/\-\_\:]+$)/', '', $ukey);
        $ukey = preg_replace('/\.{2,}/', '.', $ukey);
        $ukey = preg_replace('/\/{2,}/', '/', $ukey);
        $ukey = preg_replace('/\-{2,}/', '-', $ukey);
        $ukey = preg_replace('/\_{2,}/', '_', $ukey);
        $ukey = preg_replace('/\:{2,}/', ':', $ukey);

        return $ukey;
    }

    /**
     * 
     * @param string $model_key
     * @param mixed $pk_value
     * @param boolean $using_ukey
     * @param boolean $only_get_if_exists
     * @return OSC_Database_Model
     */
    public static function loadStatic($model_key, $pk_value = 0, $using_ukey = false, $only_get_if_exists = false) {
        static $_models = array();

        $return_array_flag = true;

        if (!is_array($pk_value)) {
            $return_array_flag = false;
            $pk_value = array($pk_value);
        }

        if (!$using_ukey) {
            foreach ($pk_value as $k => $v) {
                $v = intval($v);

                if ($v < 1) {
                    $v = 0;
                }

                $pk_value[$k] = $v;
            }
        } else {
            foreach ($pk_value as $k => $v) {
                $v = trim($v);

                if ($v != '') {
                    $pk_value[$k] = $v;
                } else {
                    unset($pk_value[$k]);
                }
            }
        }

        if (count($pk_value) < 1) {
            return $return_array_flag ? array() : null;
        }

        $pk_value = array_unique($pk_value);

        $instance_key_prefix = strtolower($model_key) . ($using_ukey ? ':ukey' : '') . ':';
        $other_instance_key_prefix = strtolower($model_key) . ($using_ukey ? '' : ':ukey') . ':';

        $models = array();

        foreach ($pk_value as $k => $value) {
            $instance_key = $instance_key_prefix . $value;

            if (!isset($_models[$instance_key])) {
                continue;
            }

            unset($pk_value[$k]);

            $models[$value] = $_models[$instance_key];
        }

        if ($only_get_if_exists) {
            return $models;
        }

        $total_to_load = count($pk_value);

        if ($total_to_load > 0) {
            if ($total_to_load == 1) {
                $pk_value = reset($pk_value);
                $model = OSC::model($model_key);

                if ($using_ukey) {
                    $model->loadByUkey($pk_value);
                } else if ($pk_value > 0) {
                    $model->load($pk_value);
                }

                $instance_key = $instance_key_prefix . $pk_value;

                $_models[$instance_key] = $model;

                if ($model->getId() > 0) {
                    $other_key = $using_ukey ? $model->getId() : $model->getUkey();

                    if ($other_key) {
                        $_models[$other_instance_key_prefix . $other_key] = $model;
                    }
                }

                $models[$pk_value] = $model;
            } else {
                $collection = OSC::model($model_key)->getCollection()->setFreshLoad();

                if ($using_ukey) {
                    $collection->loadByUkey($pk_value);
                    $key_field = $collection->ukey_field;
                } else {
                    $collection->load($pk_value);
                    $key_field = $collection->pk_field;
                }

                foreach ($collection as $model) {
                    $key_value = $model->data[$key_field];
                    unset($pk_value[array_search($key_value, $pk_value)]);
                    $_models[$instance_key_prefix . $key_value] = $model;

                    $other_key = $using_ukey ? $model->getId() : $model->getUkey();

                    if ($other_key) {
                        $_models[$other_instance_key_prefix . $other_key] = $model;
                    }

                    $models[$key_value] = $model;
                }

                foreach ($pk_value as $value) {
                    $model = OSC::model($model_key);
                    $_models[$instance_key_prefix . $value] = $model;
                    $models[$value] = $model;
                }
            }
        }

        if (!$return_array_flag) {
            return current($models);
        }

        return $models;
    }

    public static function getPreLoadedModelLockKey() {
        return OSC_Database_Model::$_preloaded_model_locked;
    }

    /**
     * 
     * @param string $lock_key
     * @throws Exception
     */
    public static function lockPreLoadedModel(string $lock_key) {
        if (OSC_Database_Model::$_preloaded_model_locked) {
            throw new Exception('Pre Loaded Model already locked');
        }

        OSC_Database_Model::$_preloaded_model_locked = $lock_key;
    }

    /**
     * 
     * @param string $lock_key
     * @throws Exception
     */
    public static function unlockPreLoadedModel(string $lock_key) {
        if (OSC_Database_Model::$_preloaded_model_locked && OSC_Database_Model::$_preloaded_model_locked != $lock_key) {
            throw new Exception('Pre Loaded Model lock key is not match');
        }

        OSC_Database_Model::$_preloaded_model_locked = null;
    }

    /**
     * 
     * @param string $model_key
     * @param integer $id
     * @return OSC_Database_Model
     */
    public static function getPreLoadedModel($model_key, $id) {
        $model_key = trim($model_key);
        $group_key = strtolower($model_key);

        $id = intval($id);

        if ($id < 1) {
            return null;
        }

        if (OSC_Database_Model::$_preloaded_model_locked) {
            try {
                return OSC::model($model_key)->load($id);
            } catch (Exception $ex) {
                return null;
            }
        }

        if (!isset(OSC_Database_Model::$_preloaded_model[$group_key]) || !isset(OSC_Database_Model::$_preloaded_model[$group_key][$id])) {
            static::preLoadModelData($model_key, $id);
        }

        return OSC_Database_Model::$_preloaded_model[$group_key][$id];
    }

    /**
     * 
     * @param string $model_key
     * @param OSC_Database_Model $model
     */
    public static function setPreLoadedModel($model_key, $model) {
        if (!($model instanceof OSC_Database_Model) || $model->getId() < 1) {
            return;
        }

        $model_key = trim($model_key);
        $group_key = strtolower($model_key);

        if (!isset(OSC_Database_Model::$_preloaded_model[$group_key])) {
            OSC_Database_Model::$_preloaded_model[$group_key] = [];
        }

        OSC_Database_Model::$_preloaded_model[$group_key][$model->getId()] = $model;

        $model->addObserver('saved', 'OSC_Database_Model::removePreLoadedModel', null, null, $model_key, true);
        $model->addObserver('deleted', 'OSC_Database_Model::removePreLoadedModel', null, null, $model_key, true);
    }

    /**
     * 
     * @param OSC_Database_Model $model
     * @param string $model_key
     */
    public static function removePreLoadedModel($model, $model_key) {
        $model_key = trim($model_key);
        $model_key = strtolower($model_key);

        if (isset(OSC_Database_Model::$_preloaded_model[$model_key])) {
            unset(OSC_Database_Model::$_preloaded_model[$model_key][$model->getId()]);
        }
    }

    /**
     * 
     * @param OSC_Database_Model $model
     * @param string $model_key
     */
    public static function resetPreLoadedModel($model_key = '') {
        $model_key = trim($model_key);
        $model_key = strtolower($model_key);

        if ($model_key) {
            if (isset(OSC_Database_Model::$_preloaded_model[$model_key])) {
                OSC_Database_Model::$_preloaded_model[$model_key] = [];
            }
        } else {
            OSC_Database_Model::$_preloaded_model = [];
        }
    }

    /**
     * 
     * @param string $model_key
     * @param mixed $ids
     */
    public static function preLoadModelData($model_key, $ids) {
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        $model_key = trim($model_key);
        $group_key = strtolower($model_key);

        if (!$model_key) {
            return;
        }

        $ids = array_map(function($id) {
            return intval($id);
        }, $ids);
        $ids = array_filter($ids, function($id) {
            return $id > 0;
        });

        if (isset(OSC_Database_Model::$_preloaded_model[$group_key])) {
            $_ids = array();

            foreach ($ids as $k => $id) {
                if (!isset(OSC_Database_Model::$_preloaded_model[$group_key][$id])) {
                    $_ids[] = $id;
                }
            }

            $ids = $_ids;
        }

        if (count($ids) < 1) {
            return;
        }

        try {
            if (count($ids) > 1) {
                $collection = OSC::model($model_key)->getCollection()->load($ids);

                foreach ($collection as $model) {
                    static::setPreLoadedModel($model_key, $model);
                }
            } else {
                static::setPreLoadedModel($model_key, OSC::model($model_key)->load($ids[0]));
            }
        } catch (Exception $ex) {}
    }

    public function loadByListUKey($keys) {
        if (empty($keys)) {
            throw new Exception('Ukey is empty');
        }

        foreach ($keys as $key => $value) {
            $keys[$key] = static::cleanUkey($value);
        }

        return $this->getCollection()->setCondition(array('field' => $this->_ukey_field, 'value' => $keys, 'operator' => OSC_Database::OPERATOR_IN))->load();
    }
}
