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
class OSC_Database_Model_Collection extends OSC_Object implements IteratorAggregate {

    /**
     *
     * @var boolean 
     */
    protected $_loaded = false;

    /**
     *
     * @var boolean 
     */
    protected $_delete_disabled = false;

    /**
     *
     * @var OSC_Database_Model
     */
    protected $_model = null;

    /**
     *
     * @var string
     */
    protected $_table_name = null;

    /**
     *
     * @var string
     */
    protected $_pk_field = null;

    /**
     *
     * @var string
     */
    protected $_ukey_field = null;

    /**
     *
     * @var array 
     */
    protected $_field_load = [];

    /**
     *
     * @var array 
     */
    protected $_addition_field_load = [];

    /**
     *
     * @var array 
     */
    protected $_condition = null;

    /**
     *
     * @var OSC_Database_Condition
     */
    protected $_condition_obj = null;

    /**
     *
     * @var array
     */
    protected $_items = array();

    /**
     *
     * @var integer 
     */
    protected $_current_page = 0;

    /**
     *
     * @var integer 
     */
    protected $_page_size = 0;

    /**
     *
     * @var array 
     */
    protected $_sort = array();

    /**
     *
     * @var array
     */
    protected $_limit = array('limit' => 0, 'offset' => 0);

    /**
     *
     * @var mixed
     */
    protected $_sort_function = null;

    /**
     *
     * @var boolean 
     */
    protected $_sort_by_id_flag = false;

    /**
     *
     * @var boolean 
     */
    protected $_sort_random_flag = false;

    /**
     *
     * @var boolean 
     */
    protected $_fresh_load_flag = false;

    /**
     * @var Array
     */
    protected $_item_index_map = array();
    protected $_item_ukey_map = array();
    protected $_page_total_item = false;
    protected $_collection_total_item = false;
    protected $_option_conf = array();
    protected $_total_page = 0;
    protected $_total_item = 0;
    protected $_all_field_loaded_flag = false;

    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    /**
     * 
     * @param array $params
     */
    public function __construct($params) {
        $this->_reset();

        $this->_model = $params['model'];
        $this->_table_name = $params['table_name'];
        $this->_pk_field = $params['pk_field'];
        $this->_ukey_field = $params['ukey_field'];
        $this->_option_conf = $params['option_conf'];

        if (!in_array($this->_pk_field, $this->_field_load)) {
            $this->_field_load[] = $this->_pk_field;
        }

        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        $this->_condition_obj = $DB->getReadAdapter()->getCondition(true);
        $this->_fresh_load_flag = $DB->static_load_collection ? false : true;
    }

    public function destruct() {
        $this->__destruct();
    }

    public function __destruct() {
        foreach ($this as $model) {
            $model->destruct();
        }

        $this->_reset();
    }

    /**
     * 
     * @return aray
     */
    public function toArray($bind_id = false) {
        $arr = array();

        foreach ($this as $model) {
            if (!$bind_id) {
                $arr[] = $model->toArray();
            } else {
                $arr[$model->getId()] = $model->toArray();
            }
        }

        return $arr;
    }

    public function getIterator() {
        return new OSC_Iterator($this->_items);
    }

    /**
     * 
     * @return OSC_Database_Model_Collection
     */
    public function addField() {
        if (func_num_args() > 0) {
            $fields = func_get_args();
            $this->_addition_field_load = array_merge($this->_addition_field_load, $fields);
        }

        return $this;
    }

    /**
     * 
     * @param boolean $set
     * @return OSC_Database_Model_Collection
     */
    public function setDistinct($set = true) {
        $this->_distinct = $set ? true : false;
        return $this;
    }

    /**
     * 
     * @param integer $page
     * @return OSC_Database_Model_Collection
     */
    public function setCurrentPage($page) {
        $page = intval($page);

        $this->_current_page = $page < 1 ? 1 : $page;

        return $this;
    }

    /**
     * 
     * @param integer $size
     * @return OSC_Database_Model_Collection
     */
    public function setPageSize($size) {
        $size = intval($size);

        $this->_page_size = $size < 1 ? 1 : $size;

        return $this;
    }

    /**
     * 
     * @param integer $limit
     * @return OSC_Database_Model_Collection
     */
    public function setLimit($limit) {
        $limit = intval($limit);

        $this->_limit['limit'] = $limit < 0 ? 0 : $limit;

        return $this;
    }

    /**
     * Get limited page in sql rows
     * 
     * @param int $cur_page
     * @param int $per_page
     * @return array
     */
    public function getPageLimit($cur_page, $per_page) {
        $return['cur'] = intval($cur_page);

        if ($return['cur'] < 1) {
            $return['cur'] = 0;
            $return['first'] = 0;
        } else {
            $return['first'] = ( $return['cur'] - 1 ) * $per_page;
        }

        return $return;
    }

    /**
     * Calculate last page of sql rows
     *
     * @param int $total
     * @param int $per_page
     * @return int
     */
    public function calLastPage($total, $per_page) {
        return ceil($total / $per_page);
    }

    /**
     * 
     * @return OSC_Database_Model_Collection
     */
    protected function _preLimit() {
        if ($this->_page_size > 0) {
            $this->_total_page = $this->calLastPage($this->collectionLength(), $this->_page_size);

            if ($this->_current_page > $this->_total_page) {
                $this->_current_page = $this->_total_page;
            }

            $pageLimit = $this->getPageLimit($this->_current_page, $this->_page_size);

            $this->setOffset($pageLimit['first'])->setLimit($this->_page_size);
        }

        return $this;
    }

    /**
     * 
     * @param integer $offset
     * @return OSC_Database_Model_Collection
     */
    public function setOffset($offset) {
        $offset = intval($offset);

        $this->_limit['offset'] = $offset < 0 ? 0 : $offset;

        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getCurrentPage() {
        return $this->_current_page;
    }

    /**
     * 
     * @return integer
     */
    public function getPageSize() {
        return $this->_page_size;
    }

    /**
     * 
     * @return integer
     */
    public function getTotalPage() {
        return $this->_total_page;
    }

    /**
     * @return mixed
     */
    protected function _getLimit() {
        $limit = null;

        if ($this->_limit['offset'] > 0 || $this->_limit['limit'] > 0) {
            if ($this->_limit['offset'] > 0) {
                $limit = array($this->_limit['offset'], $this->_limit['limit']);
            } elseif ($this->_limit['limit'] > 0) {
                $limit = $this->_limit['limit'];
            }
        }

        return $limit;
    }

    /**
     * 
     * @param string $sort
     * @param string $order
     * @return OSC_Database_Model_Collection
     */
    public function sort($sort, $order = null) {
        $order = strtoupper($order);

        if ($order != static::ORDER_DESC) {
            $order = static::ORDER_ASC;
        }

        $this->_sort[] = array($sort, $order);

        return $this;
    }

    /**
     * 
     * @param bool $flag
     * @return OSC_Database_Model_Collection
     */
    public function setRandom($flag = true) {
        $this->_sort_random_flag = $flag ? true : false;
        return $this;
    }

    /**
     * 
     * @param string $clause_idx
     * @param string $relation
     * @param string $p_clause_idx
     * @return OSC_Database_Model_Collection
     */
    public function addClause($clause_idx = '', $relation = null, $p_clause_idx = null) {
        $this->_condition_obj->addClause($clause_idx, $relation, $p_clause_idx);
        return $this;
    }

    /**
     * 
     * @param string $field
     * @param mixed $filter_value
     * @param string $operator
     * @param string $relation
     * @param string $clause_idx
     * @param string $cond_idx
     * @return OSC_Database_Model_Collection
     */
    public function addCondition($field, $filter_value, $operator = null, $relation = null, $clause_idx = null, $cond_idx = null) {
        $this->_condition_obj->addCondition($field, $filter_value, $operator, $relation, $clause_idx, $cond_idx);
        return $this;
    }

    /**
     * 
     * @param mixed $condition
     * @return OSC_Database_Model_Collection
     */
    public function setCondition($condition) {
        if ($condition instanceof OSC_Database_Condition) {
            $this->_condition_obj = $condition;
        } else {
            $this->_condition = $condition;
        }

        return $this;
    }

    /**
     *
     * @return array
     */
    protected function _getCondition() {
        if ($this->_condition) {
            $this->_condition_obj->reset();

            if (is_array($this->_condition)) {
                if (!isset($this->_condition['condition'])) {
                    $condition = $this->_condition_obj->parse($this->_condition)->getPdoData();
                } else {
                    $condition = $this->_condition;
                }
            } else {
                $condition = ['condition' => (string) $this->_condition];
            }
        } else {
            $condition = $this->_condition_obj->getPdoData();
        }

        return $condition;
    }

    public function getCondition() {
        return $this->_getCondition();
    }

    /**
     * 
     * @return integer
     */
    public function length() {
        return $this->pageLength();
    }

    /**
     * 
     * @return integer
     */
    public function pageLength() {
        if ($this->_page_total_item !== false) {
            return $this->_page_total_item;
        }

        return $this->_page_total_item;
    }

    public function collectionLength() {
        if ($this->_collection_total_item === false) {
            $DB = $this->getReadAdapter();

            $db_transaction_key = 'collection_count__' . $this->_table_name;

            $this->_collection_total_item = $DB->count($this->_pk_field, $this->_table_name, $this->_getCondition(), $db_transaction_key);

            $DB->free($db_transaction_key);
        }

        return $this->_collection_total_item;
    }

    /**
     * 
     * @param boolean $flag
     * @return OSC_Database_Model_Collection
     */
    public function setFreshLoad($flag = true) {
        $this->_fresh_load_flag = $flag;
        return $this;
    }

    /**
     * 
     * @param array $ids
     * @param array &$collection_data
     * @return array
     */
    protected function _preLoadCache($ids, &$collection_data) {
        if (!is_array($ids) || count($ids) < 1) {
            return array();
        }

        $ids = array_unique($ids);

        if (!$this->_fresh_load_flag) {
            $models = OSC_Database_Model::loadStatic($this->getModelKey(), $ids, false, true);

            $buff = array();

            foreach ($models as $id => $model) {
                $buff[] = $id;

                if ($model->getId() < 1) {
                    continue;
                }

                $collection_data[] = array(false, $model->data, $model);
            }

            $ids = array_diff($ids, $buff);
            $ids = array_values($ids);
        }

        if (!$this->usingCache()) {
            return $ids;
        }

        $cache_bind = $this->_model->getCacheObj();

        $cache_keys = array();

        foreach ($ids as $id) {
            $cache_keys[$id] = $this->_model->getCacheKey($id);
        }

        $cache_data = $cache_bind->getMulti($cache_keys);

        $ids = array();

        foreach ($cache_keys as $id => $cache_key) {
            $cache_key_data = $cache_data[$cache_key];

            if ($cache_key_data === false) {
                $ids[] = $id;
            } else {
                $collection_data[] = array(false, $cache_key_data);
            }
        }

        return $ids;
    }

    /**
     * 
     * @return string
     */
    protected function _getFieldToLoad() {
        if (count($this->_addition_field_load) < 1 || array_search('*', $this->_field_load) !== false || array_search('*', $this->_addition_field_load) !== false) {
            $this->_all_field_loaded_flag = true;
            return '*';
        }

        $this->_all_field_loaded_flag = false;

        return implode(',', array_unique(array_merge($this->_addition_field_load, $this->_field_load)));
    }

    /**
     * 
     * @param array &$collection_data
     */
    protected function _collectFromDb(&$collection_data) {
        $fields = $this->_getFieldToLoad();

        $sort = '';

        if ($this->_sort_function === null) {
            if (!is_array($this->_sort) || count($this->_sort) < 1) {
                $this->_sort[] = array($this->_pk_field, 'ASC');
            }

            if ($this->_sort_random_flag) {
                $sort = 'RAND()';
            } else {
                foreach ($this->_sort as $sort_item) {
                    if (strpos($sort_item[0], '*') !== false) {
                        $sort_item[0] = preg_replace('/[^a-zA-Z0-9_]/', '', $sort_item[0]);
                    } else if (strpos($sort_item[0], '.') === false) {
                        $sort_item[0] = $sort_item[0];
                    }

                    $sort .= ", {$sort_item[0]} {$sort_item[1]}";
                }

                $sort = preg_replace('/^\,/', '', $sort);
            }
        }

        $DB = $this->getReadAdapter();

        $db_transaction_key = 'collection_load__' . $this->_table_name;

        try {
            $DB->select($fields, $this->_table_name, $this->_getCondition(), $sort, $this->_getLimit(), $db_transaction_key);
        } catch (OSC_Exception_Database $e) {
            //OSC::logError();
        }

        if ($DB->rowCount($db_transaction_key) > 0) {
            while ($item = $DB->fetchArray($db_transaction_key)) {
                $collection_data[] = array(true, $item);
            }
        }

        $DB->free($db_transaction_key);
    }

    /**
     * 
     * @return $this
     */
    public function setNull() {
        $this->_loaded = true;

        $this->_collection_total_item = 0;
        $this->_page_total_item = 0;

        $this->_items = [];
        $this->_item_index_map = [];
        $this->_item_ukey_map = [];

        return $this;
    }

    /**
     * 
     * @return OSC_Database_Model_Collection
     */
    public function load() {
        if ($this->_loaded) {
            return $this;
        }

        $this->_preLimit();

        $collection_data = array();
        $collect_db = true;

        $model_no_cache_ids = array();

        if (func_num_args() == 1) {
            $ids = func_get_arg(0);

            $buff = array();

            foreach ($ids as $id) {
                $id = intval($id);

                if ($id > 0) {
                    $buff[] = $id;
                }
            }

            $ids = array_unique($buff);

            if ($this->_sort_by_id_flag) {
                $this->addSortFunction(array($this, 'sortDataById'), $ids);
            }

            $ids = $this->_preLoadCache($ids, $collection_data);

            $total_ids = count($ids);

            if ($total_ids > 0) {
                $this->setCondition(array('field' => $this->_pk_field, 'value' => $ids, 'operator' => 'IN'));

                if ($this->_limit['limit'] < 1) {
                    $this->setLimit($total_ids);
                }

                if ($this->usingCache()) {
                    $model_no_cache_ids = $ids;
                }
            } else {
                $collect_db = false;
            }
        }

        $_PHP_SORT_FLAG = false;

        if (count($collection_data) > 0 && $this->_sort_function === null) {
            $_PHP_SORT_FLAG = true;
        }

        $this->_items = array();
        $this->_page_total_item = 0;
        $this->_item_index_map = array();
        $this->_item_ukey_map = array();

        if ($collect_db) {
            $this->_collectFromDb($collection_data);
        }

        if (count($collection_data) > 0) {
            if ($_PHP_SORT_FLAG) {
                if ($this->_sort_random_flag) {
                    $rand_keys = array_rand($collection_data, count($collection_data));

                    $sorted_collection_data = array();

                    foreach ($rand_keys as $rand_key) {
                        $sorted_collection_data[] = $collection_data[$rand_key];
                    }

                    $collection_data = $sorted_collection_data;
                } else {
                    $sort_data = array();

                    foreach ($collection_data as $item_idx => $item) {
                        foreach ($this->_sort as $_sort) {
                            if (!isset($sort_data[$_sort[0]]) || !is_array($sort_data[$_sort[0]])) {

                                $sort_data[$_sort[0]] = array();
                            }

                            $sort_data[$_sort[0]][$item_idx] = $item[1][$_sort[0]];
                        }
                    }

                    $sort_params = array();

                    foreach ($this->_sort as $_sort) {
                        $sort_params[] = $sort_data[$_sort[0]];
                        $sort_params[] = $_sort[1] == 'ASC' ? SORT_ASC : SORT_DESC;
                    }

                    $sort_params[] = & $collection_data;

                    call_user_func_array('array_multisort', $sort_params);
                }
            } else if ($this->_sort_function !== null) {
                if (isset($this->_sort_function['params'])) {
                    $callback_params = $this->_sort_function['params'];
                } else {
                    $callback_params = array();
                }

                array_unshift($callback_params, $collection_data);

                $collection_data = call_user_func_array($this->_sort_function['callback'], $callback_params);
            }

            foreach ($collection_data as $item) {
                if (isset($item[2])) {
                    $model = $item[2];
                } else {
                    $model = $this->getNullModel();
                    $model->bind($item[1], $item[0]);
                }

                if (!$this->_all_field_loaded_flag) {
                    $model->lock();
                }

                $this->_preModel($model);

                $this->addItem($model);
            }

            if ($this->_all_field_loaded_flag && count($model_no_cache_ids) > 0) {
                foreach ($model_no_cache_ids as $id) {
                    $model = $this->getItemByKey($id);

                    if ($model) {
                        $model->setCache();
                    }
                }
            }
        }

        $this->_loaded = true;

        $this->_afterLoad();

        return $this;
    }

    public function addItem($item) {
        if ($this->_page_total_item === false) {
            $this->_loaded = true;
            $this->_items = array();
            $this->_page_total_item = 0;
            $this->_item_index_map = array();
            $this->_item_ukey_map = array();
        }

        if ($this->getItemByKey($item->getId())) {
            return $this;
        }

        $key = $item->data[$this->_pk_field];

        $this->_items[] = $item;

        $this->_page_total_item++;

        $this->_item_index_map[$key] = $this->_page_total_item - 1;

        if ($this->_ukey_field) {
            $this->_item_ukey_map[$item->data[$this->_ukey_field]] = $this->_item_index_map[$key];
        }

        return $this;
    }

    /**
     * 
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
     * @return string
     */
    public function getModelClass() {
        return $this->_model->getClassName();
    }

    /**
     * 
     * @return string
     */
    public function getModelKey() {
        return $this->_model->getModelKey();
    }

    /**
     * 
     * @param array $ukey_array
     * @return OSC_Database_Model_Collection
     */
    public function loadByUkey($ukey_array) {
        $pk_field = $this->_pk_field;
        $ukey_field = $this->_ukey_field;
        $model = $this->_model;

        foreach ($ukey_array as $idx => $ukey) {
            $ukey = OSC_Database_Model::cleanUkey($ukey);

            if (!$ukey) {
                unset($ukey_array[$idx]);
            } else {
                $ukey_array[$idx] = $ukey;
            }
        }

        $ukey_array = array_unique($ukey_array);

        if (!$this->usingCache()) {
            return $this->addCondition($ukey_field, $ukey_array, 'IN')->load();
        }

        $ukey_translate_key_array = array();

        foreach ($ukey_array as $idx => $ukey) {
            $ukey_translate_key_array[$model->getUkeyTranslateKey($ukey)] = $ukey;
        }

        $cache_adapter = $this->getCacheObj()->getAdapter();

        $ids = $cache_adapter->getMulti(array_keys($ukey_translate_key_array));

        $error_cache = array();

        foreach ($ids as $idx => $id) {
            if (!$id) {
                $error_cache[$idx] = $ukey_translate_key_array[$idx];
                unset($ids[$idx]);
            }
        }

        if (count($error_cache) > 0) {
            $db_transaction_key = 'collection_get_id_by_ukey';

            $DB = $this->getReadAdapter();

            try {
                $DB->select($pk_field . ', ' . $ukey_field, $this->_table_name, array('field' => $ukey_field, 'value' => $error_cache, 'operator' => 'IN'), null, count($error_cache), $db_transaction_key);
            } catch (OSC_Exception_Database $e) {
                //OSC::logError();
            }

            while ($row = $DB->fetchArray($db_transaction_key)) {
                $ukey_translate_key = $model->getUkeyTranslateKey($row[$ukey_field]);

                $ids[$ukey_translate_key] = $row[$pk_field];

                $cache_adapter->set($ukey_translate_key, $row[$pk_field]);
            }

            $DB->free($db_transaction_key);
        }

        return $this->load(array_values($ids));
    }

    /**
     * 
     * @param OSC_Database_Model $model
     * @return OSC_Database_Model
     */
    protected function _preModel($model) {
        return $model;
    }

    protected function _afterLoad() {
        $this->dispatchEvent('after_load', array('collection' => $this));
        OSC::core('observer')->dispatchEvent('collection__' . $this->_table_name . '__after_load', array('collection' => $this));
    }

    /**
     * 
     * @return OSC_Database_Model_Collection
     */
    public function setData() {
        $args = func_num_args();

        if ($args < 1) {
            return $this;
        }

        $data = array();

        if ($args == 2) {
            $data[func_get_arg(0)] = func_get_arg(1);
        } else if ($args == 1) {
            $data = func_get_arg(0);

            if (!is_array($data) || count($data) < 1) {
                return $this;
            }
        } else {
            return $this;
        }

        foreach ($data as $key => $value) {
            $key = strtolower($key);
            $this->_items[$key] = $value;
        }

        return $this;
    }

    /**
     *
     * @return OSC_Database_Model_Collection
     */
    public function delete() {
        if (!$this->_loaded) {
            $this->load();
        }

        if ($this->pageLength()) {
            $this->_beforeDelete();

            if (!$this->_delete_disabled) {
                foreach ($this as $model) {
                    $model->delete();
                }

                $this->_afterDelete();
            }
        }

        return $this;
    }

    protected function _beforeDelete() {
        
    }

    protected function _afterDelete() {
        
    }

    /**
     *
     * @return array
     */
    public function getItems() {
        return $this->_items;
    }

    public function getKeys() {
        return array_keys($this->_item_index_map);
    }

    public function getUkeys() {
        return array_keys($this->_item_ukey_map);
    }

    /**
     *
     * @param integer $item_idx
     * @return OSC_Database_Model
     */
    public function getItem($item_idx = 0) {
        return $this->getItemByIndex($item_idx);
    }

    /**
     *
     * @param integer $item_idx
     * @return OSC_Database_Model
     */
    public function item($item_idx = 0) {
        return $this->getItemByIndex($item_idx);
    }

    /**
     *
     * @return Model_Abstract_Model
     */
    public function first() {
        return $this->getItemByIndex(0);
    }

    /**
     *
     * @return Model_Abstract_Model
     */
    public function last() {
        return $this->getItemByIndex(count($this->_items) - 1);
    }

    /**
     *
     * @param integer $item_idx
     * @return Model_Abstract_Model
     */
    public function getItemByIndex($item_idx = 0) {
        return isset($this->_items[$item_idx]) ? $this->_items[$item_idx] : null;
    }

    /**
     *
     * @param string $item_key
     * @return Model_Abstract_Model
     */
    public function getItemByKey($item_key) {
        return isset($this->_item_index_map[$item_key]) ? $this->getItemByIndex($this->_item_index_map[$item_key]) : null;
    }

    /**
     *
     * @param string $ukey
     * @return Model_Abstract_Model
     */
    public function getItemByUkey($ukey) {
        return isset($this->_item_ukey_map[$ukey]) ? $this->getItemByIndex($this->_item_ukey_map[$ukey]) : null;
    }

    public function getItemByPK($pk_value) {
        return $this->getItemByKey($pk_value);
    }

    /**
     * 
     * @param int $item_idx
     * @return OSC_Database_Model_Collection
     */
    public function removeItem($item_idx) {
        $model = $this->getItemByIndex($item_idx);

        if (!$model) {
            return $this;
        }

        $this->_page_total_item--;
        $this->_collection_total_item = false;

        unset($this->_items[$item_idx]);
        unset($this->_item_index_map[$model->getId()]);

        if ($this->_ukey_field) {
            unset($this->_item_ukey_map[$model->getUkey()]);
        }

        $item_idx ++;

        while (isset($this->_items[$item_idx])) {
            $new_index = $item_idx - 1;

            $item = $this->_items[$item_idx];

            unset($this->_items[$item_idx]);

            $this->_items[$new_index] = $item;

            $this->_item_index_map[$item->getId()] = $new_index;

            if ($this->_ukey_field) {
                $this->_item_ukey_map[$item->getUkey()] = $new_index;
            }

            $item_idx ++;
        }

        return $this;
    }

    /**
     * 
     * @param string $item_key
     * @return OSC_Database_Model_Collection
     */
    public function removeItemByKey($item_key) {
        if (isset($this->_item_index_map[$item_key])) {
            $this->removeItem($this->_item_index_map[$item_key]);
        }

        return $this;
    }

    /**
     * 
     * @param string $ukey
     * @return Model_Abstract_Model_Collection
     */
    public function removeItemByUkey($ukey) {
        if (isset($this->_item_ukey_map[$ukey])) {
            $this->removeItem($this->_item_ukey_map[$ukey]);
        }

        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getOptions() {
        $options = array();

        foreach ($this as $item) {
            $options[] = array('value' => $item->data[$this->_option_conf['value']], 'label' => $item->data[$this->_option_conf['label']]);
        }

        return $options;
    }

    /**
     * 
     * @return OSC_Database_Adapter
     */
    public function getReadAdapter() {
        return $this->_model->getReadAdapter();
    }

    /**
     * 
     * @return OSC_Database_Adapter
     */
    public function getWriteAdapter() {
        return $this->_model->getWriteAdapter();
    }

    /**
     * 
     * @return boolean
     */
    public function usingCache() {
        return $this->_model->usingCache();
    }

    public function resetCache() {
        if ($this->usingCache()) {
            $cache_keys = array();

            foreach ($this as $model) {
                $cache_keys[] = $model->getCacheKey();
            }

            $this->getModelCacheObj()->deleteMulti($cache_keys);
        }
    }

    /**
     *
     * @return OSC_Database_Model_Abstract_Cache_Model_Collection 
     */
    public function getCacheObj() {
        return $this->getModelCacheObj()->getCollectionCache();
    }

    /**
     * 
     * @return OSC_Database_Model_Abstract_Cache_Model
     */
    public function getModelCacheObj() {
        return $this->_model->getCacheObj();
    }

    /**
     * 
     * @param boolean $inc_prefix
     * @return string
     */
    public function getTableName($inc_prefix = false) {
        return $this->_model->getTableName($inc_prefix);
    }

    /**
     * 
     * @return string
     */
    public function getPkFieldName() {
        return $this->_model->getPkFieldName();
    }

    /**
     * 
     * @param boolean $flag
     * @return OSC_Database_Model_Collection
     */
    public function setSortByIdFlag($flag = true) {
        $this->_sort_by_id_flag = $flag;
        return $this;
    }

    /**
     * 
     * @param mixed $callback
     * @return OSC_Database_Model_Collection
     */
    public function addSortFunction($callback) {
        $this->_sort_function = array('callback' => $callback);

        if (func_num_args() > 1) {
            $this->_sort_function['params'] = array();

            for ($i = 1; $i < func_num_args(); $i++) {
                $this->_sort_function['params'][] = func_get_arg($i);
            }
        }

        return $this;
    }

    /**
     * 
     * @param array $collection
     * @param array $sort_data
     * @return array
     */
    public function sortDataById($collection, $sort_data) {
        $buff = array();

        foreach ($collection as $item) {
            $buff[$item[1][$this->_pk_field]] = $item;
        }

        $collection = array();

        foreach ($sort_data as $item_id) {
            if (isset($buff[$item_id])) {
                $collection[] = $buff[$item_id];
            }
        }

        return $collection;
    }

    /**
     * 
     * @return OSC_Database_Model_Collection
     */
    public function preLoadModelData() {
        return $this;
    }

}
