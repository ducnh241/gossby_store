<?php

class OSC_Search_Adapter_Database extends OSC_Search_Adapter {

    protected $_read_adapter = null;
    protected $_write_adapter = null;
    protected $_tblname = null;
    protected $_skip_reset_vars = array('_read_adapter', '_write_adapter', '_tblname');

    /**
     *
     * @var OSC_Database_Condition 
     */
    protected $_condition_obj;

    /**
     * 
     * @param array $config
     * @return OSC_Search_Adapter_Database
     */
    public function setConfig($config) {
        parent::setConfig($config);

        $map = array(
            'read_adapter' => '_read_adapter',
            'write_adapter' => '_write_adapter',
            'tblname' => '_tblname'
        );

        foreach ($map as $k => $v) {
            if (isset($config[$k])) {
                $this->$v = $config[$k];
            }
        }

        return $this;
    }

    /**
     * @return OSC_Database_Adapter;
     */
    protected function _getAdapter($bind = null) {
        if ($bind === null) {
            $bind = $this->_read_adapter;
        }

        return OSC::core('database')->getAdapter($bind);
    }

    protected function _processDelete() {
        try {
            $DB = $this->_getAdapter();
            $DB->delete($this->_tblname, $this->_getDBConditionObj(), 'delete_search_entry');
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function deleteDocumentById($id) {
        return $this->deleteDocumentByIds(array($id));
    }

    public function deleteDocumentByIds($ids) {
        if (!is_array($ids)) {
            return $this;
        }

        return $this;
    }

    /**
     * 
     * @param array $doc
     * @return OSC_Search_Adapter_Database
     * @throws OSC_Search_Exception_Condition
     */
    public function addDocument($doc) {
        return $this->addDocuments($doc);
    }

    /**
     * @params array $doc
     * @params array $_ [optional]
     * @return OSC_Search_Adapter_Database
     * @throws OSC_Search_Exception_Condition
     */
    public function addDocuments($doc) {
        $docs = array();

        foreach (func_get_args() as $data) {
            if (!is_array($data)) {
                continue;
            }

            $doc = new SolrInputDocument();

            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $_v) {
                        $doc->addField($k, $_v);
                    }
                } else {
                    $doc->addField($k, $v);
                }
            }

            $docs[] = $doc;
        }

        try {
            $this->getConnection()->addDocuments($docs);
        } catch (SolrClientException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    protected function _fetchProcess($keywords) {
        if (count($this->_fields)) {
            $fields = implode(',', $this->_fields);
        } else {
            $fields = '*';
        }

        $fields = 'SQL_CALC_FOUND_ROWS ' . $fields;

        $condition_array = $this->_getDBConditionObj()->getArray();

        $condition_obj = $this->_getDBConditionObj(true);

        $fulltext_condition_flag = false;

        if (count($keywords) > 0) {
            $condition_obj->setFulltextKeywordMinLength(OSC_Search::KEYWORD_MIN_LENGTH);

            $words = array();

            $or_like_clause_idx = OSC::makeUniqid();

            $condition_obj->addClause($or_like_clause_idx, OSC_Database::RELATION_AND);

            $wildcard = $this->_like_mode ? '*' : '';

            foreach ($keywords as $keyword) {
                if ($keyword['type'] == 'word') {
                    $words[] = $keyword['modifier'] . $keyword['value'] . $wildcard;
                    continue;
                }

                $condition_obj->addCondition($this->_keyword_field, $keyword['value'], $keyword['modifier'] == '-' ? OSC_Database::OPERATOR_NOT_LIKE : OSC_Database::OPERATOR_LIKE, !$keyword['modifier'] ? OSC_Database::RELATION_OR : OSC_Database::RELATION_AND, !$keyword['modifier'] ? $or_like_clause_idx : null);
            }

            $words = implode(' ', $words);

            if ($words !== '') {
                $fulltext_condition_flag = true;
                $condition_obj->addCondition($this->_keyword_field, $words, OSC_Database::OPERATOR_FULLTEXT, OSC_Database::RELATION_OR, $or_like_clause_idx);
            }
        }

        $clause_idx = OSC::makeUniqid();

        $condition_obj->addClause($clause_idx, OSC_Database::RELATION_AND)->parse($condition_array, $clause_idx);

        if ($fulltext_condition_flag) {
            $condition = $condition_obj->makeConditionItem($this->_keyword_field, $words, OSC_Database::OPERATOR_FULLTEXT);
            $fields .= ", {$condition_obj->buildCondition($condition)} AS relevancy";
        }

        if (isset($this->_sort['random'])) {
            $orders = 'RAND()';
        } else {
            if ($fulltext_condition_flag) {
                $orders = array('relevancy DESC');
            }

            foreach ($this->_sort as $sort => $order) {
                $order = $order == OSC_Database::ORDER_ASC ? $order : OSC_Database::ORDER_DESC;
                $orders[] = "{$sort} {$order}";
            }

            $orders = implode(',', $orders);
        }

        $DB = $this->_getAdapter();

        $DB->setProfiling(true);
        $DB->select($fields, $this->_tblname, $this->_getDBConditionObj(), $orders, array($this->getOffset(), $this->_page_size), 'fetch_search_result');
        $query_time = $DB->getQueryTime();
        $DB->setProfiling(false);

        $total = 0;
        $docs = array();

        if ($DB->rowCount('fetch_search_result') > 0) {
            while ($doc = $DB->fetchArray('fetch_search_result')) {
                $docs[] = $doc;
            }

            $DB->query('SELECT FOUND_ROWS() AS total', null, 'fetch_search_result_total');
            $total = $DB->fetchArray('fetch_search_result_total');
            $total = $total['total'];

            $DB->free('fetch_search_result_total');
        }

        $DB->free('fetch_search_result');

        $data = array(
            'total_item' => $total,
            'docs' => $docs,
            'query_time' => $query_time
        );

        return $data;
    }

    protected function _fetchFilterQuery($options, $relation = OSC_Search::_DEFAULT_RELATION) {}

    /**
     * 
     * @param boolean $reset
     * @return OSC_Database_Condition
     */
    protected function _getDBConditionObj($reset = false) {
        if ($this->_condition_obj === null || $reset) {
            $this->_condition_obj = $this->_getAdapter()->getCondition(true);
        }

        return $this->_condition_obj;
    }

    /**
     * 
     * @param string $field
     * @param mixed $filter_value
     * @param string $operator
     * @param string $relation
     * @param string $clause_idx
     * @param string $cond_idx
     * @return OSC_Search_Adapter_Database
     * @throws OSC_Search_Exception_Condition
     */
    public function addFilter($field, $filter_value, $operator = null, $relation = null, $clause_idx = null, $cond_idx = null) {
        if (!$operator) {
            $operator = OSC_Search::_DEFAULT_OPERATOR;
        }

        if (!$this->operatorSupported($operator)) {
            throw new Exception("The operator [{$operator}] is not supported");
        }

        switch ($operator) {
            case OSC_Search::OPERATOR_LEFT:
                $operator = OSC_Database::OPERATOR_LIKE_LEFT;
                break;
            case OSC_Search::OPERATOR_RIGHT:
                $operator = OSC_Database::OPERATOR_LIKE_RIGHT;
                break;
            case OSC_Search::OPERATOR_CONTAINS:
                $operator = OSC_Database::OPERATOR_LIKE;
                break;
        }

        try {
            $this->_getDBConditionObj()->addCondition($field, $filter_value, $operator, $relation, $clause_idx, $cond_idx);
        } catch (OSC_Exception_Runtime $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 
     * @param string $clause_idx
     * @param string $relation
     * @param string $p_clause_idx
     * @return OSC_Search_Adapter_Database
     * @throws OSC_Search_Exception_Condition
     */
    public function addFilterClause($clause_idx = '', $relation = null, $p_clause_idx = null) {
        try {
            $this->_getDBConditionObj()->addClause($clause_idx, $relation, $p_clause_idx);
        } catch (OSC_Exception_Runtime $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        return $this;
    }

}
