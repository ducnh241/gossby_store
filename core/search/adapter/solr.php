<?php

class OSC_Search_Adapter_Solr extends OSC_Search_Adapter {

    /**
     *
     * @var SolrClient
     */
    protected $_connection = null;
    protected $_host = 'localhost';
    protected $_port = 8983;
    protected $_path = null;
    protected $_skip_reset_vars = array('_connection', '_host', '_port', '_path');

    /**
     * 
     * @param array $config
     * @return OSC_Search_Adapter_Solr
     */
    public function setConfig($config) {
        parent::setConfig($config);

        $map = array(
            'host' => '_host',
            'port' => '_port',
            'path' => '_path',
        );

        foreach ($map as $k => $v) {
            if (isset($config[$k])) {
                $this->$v = $config[$k];
            }
        }

        return $this;
    }

    /**
     * @return SolrClient;
     */
    public function getConnection() {
        if ($this->_connection === null) {
            $this->_connection = new SolrClient(array(
                'hostname' => $this->_host,
                'port' => $this->_port,
                'path' => $this->_path,
                'wt' => 'json'
            ));
        }

        return $this->_connection;
    }

    /**
     * 
     * @param array $doc
     * @return OSC_Search_Adapter_Solr
     * @throws OSC_Search_Exception_Condition
     */
    public function addDocument($doc) {
        return $this->addDocuments($doc);
    }

    /**
     * @params array $doc
     * @params array $_ [optional]
     * @return OSC_Search_Adapter_Solr
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
            $this->getConnection()->addDocuments($docs, true);
            $this->getConnection()->commit();
        } catch (SolrClientException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    protected function _processDelete() {
        $query = $this->_buildQuery();

        if (!$query) {
            $query = '*:*';
        }

        try {
            $this->getConnection()->deleteByQuery($query);
            $this->getConnection()->commit();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function deleteDocumentById($id) {
        return $this->deleteDocumentByIds(array($id));
    }

    public function deleteDocumentByIds($ids) {
        if (!is_array($ids) || count($ids) < 1) {
            return $this;
        }

        try {
            if (count($ids) > 1) {
                $this->getConnection()->deleteByIds($ids);
            } else {
                $this->getConnection()->deleteById(current($ids));
            }

            $this->getConnection()->commit();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return $this;
    }

    protected function _fetchProcess($keywords) {
        if ($this->_like_mode) {
            $query = array('phrase' => array());

            foreach ($keywords as $idx => $keyword) {
                if ($keyword['type'] == 'phrase') {
                    $query['phrase'][] = $keyword['modifier'] . '"' . $keyword['value'] . '"';
                } else {
                    $query[] = $keyword['modifier'] . $this->_keyword_field . ':' . $keyword['value'] . '*';
                }
            }

            if (count($query['phrase']) > 0) {
                $query['phrase'] = $this->_keyword_field . ':(' . implode(' ', $query['phrase']) . ')';
            } else {
                unset($query['phrase']);
            }

            $query = '(' . implode(' ', $query) . ')';
        } else {
            foreach ($keywords as $idx => $keyword) {
                if ($keyword['type'] == 'phrase') {
                    $keyword['value'] = '"' . $keyword['value'] . '"';
                }

                $keywords[$idx] = $keyword['modifier'] . $keyword['value'];
            }

            $query = $this->_keyword_field . ':(' . implode(' ', $keywords) . ')';
        }

        $filter_query = $this->_buildQuery();

        $query .= $filter_query ? (' AND ' . $filter_query) : '';

        $solr_query = new SolrQuery();
        $solr_query->setQuery($query);
        $solr_query->setStart($this->getOffset());
        $solr_query->setRows($this->_page_size);

        if (isset($this->_sort['random'])) {
            $solr_query->addSortField('random_' . rand(0, 1000));
        } else {
            $solr_query->addSortField('score');

            foreach ($this->_sort as $sort => $order) {
                $solr_query->addSortField($sort, $order == OSC_Search::ORDER_DESC ? SolrQuery::ORDER_DESC : SolrQuery::ORDER_ASC);
            }
        }

        $solr_query->addField('score');

        if (count($this->_fields)) {
            foreach ($this->_fields as $field) {
                $solr_query->addField($field);
            }
        } else {
            $solr_query->addField('*');
        }

        try {
            $query_response = $this->getConnection()->query($solr_query);
        } catch (SolrClientException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        $query_response->setParseMode(SolrQueryResponse::PARSE_SOLR_DOC);

        $response = $query_response->getResponse();

        $offset = intval($response['response']['start']);
        $total = intval($response['response']['numFound']);

        $data = array(
            'total_item' => $total,
            'query_time' => floatval($response->responseHeader['QTime']),
            'docs' => $response['response']['docs']
        );

        foreach ($data['docs'] as $idx => $doc) {
            $data['docs'][$idx] = (array) $doc;
        }

        return $data;
    }

    protected function _fetchFilterQuery($options, $relation = OSC_Search::_DEFAULT_RELATION) {
        $solr_query = new SolrQuery();

        $filter_array = array_map(function($a, $b) { return strval($a . ':' . $b); }, array_keys($options), array_values($options));
        $solr_query->setQuery(strval(implode($relation === OSC_Search::_DEFAULT_RELATION ? ' AND ' : ' OR ', $filter_array)));

        $solr_query->setStart($this->getOffset());
        $solr_query->setRows($this->_page_size);

        if (isset($this->_sort['random'])) {
            $solr_query->addSortField('random_' . rand(0, 1000));
        } else {
            $solr_query->addSortField('score');

            foreach ($this->_sort as $sort => $order) {
                $solr_query->addSortField($sort, $order == OSC_Search::ORDER_DESC ? SolrQuery::ORDER_DESC : SolrQuery::ORDER_ASC);
            }
        }

        $solr_query->addField('score');

        if (count($this->_fields)) {
            foreach ($this->_fields as $field) {
                $solr_query->addField($field);
            }
        } else {
            $solr_query->addField('*');
        }

        try {
            $query_response = $this->getConnection()->query($solr_query);
        } catch (SolrClientException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        $query_response->setParseMode(SolrQueryResponse::PARSE_SOLR_DOC);

        $response = $query_response->getResponse();

        $offset = intval($response['response']['start']);
        $total = intval($response['response']['numFound']);

        $data = array(
            'total_item' => $total,
            'query_time' => floatval($response->responseHeader['QTime']),
            'docs' => $response['response']['docs']
        );

        foreach ($data['docs'] as $idx => $doc) {
            $data['docs'][$idx] = (array) $doc;
        }

        return $data;
    }

    /**
     * 
     * @param string $clause_idx
     * @return string
     */
    protected function _buildQuery($clause_idx = null) {
        if (!$clause_idx) {
            $clause_idx = OSC_Search::_ROOT_CLAUSE_IDX;
        }

        $query = '';

        $counter = 0;

        foreach ($this->_condition[$clause_idx] as $condition) {
            if ($condition['type'] == 'clause') {
                if (count($this->_condition[$condition['clause_idx']]) < 1) {
                    continue;
                }

                $condition_str = $this->_buildQuery($condition['clause_idx']);

                if (!$condition_str) {
                    continue;
                }

                $condition_str = "({$condition_str})";
            } else {
                $condition_str = $this->_buildCondition($condition);

                if (!$condition_str) {
                    continue;
                }
            }

            $counter++;

            $query .= ($counter > 1 ? ' ' . $condition['relation'] . ' ' : '') . $condition_str;
        }

        return $query;
    }

    protected function _buildCondition($condition) {
        switch ($condition['operator']) {
            case OSC_Search::OPERATOR_EQUAL:
                if (preg_match('/[^a-zA-Z0-9_]/i', $condition['filter_value'])) {
                    $condition['filter_value'] = "\"{$condition['filter_value']}\"";
                }
                break;
            case OSC_Search::OPERATOR_GREATER_THAN_OR_EQUAL:
                $condition['filter_value'] = "[{$condition['filter_value']} TO *]";
                break;
            case OSC_Search::OPERATOR_LESS_THAN_OR_EQUAL:
                $condition['filter_value'] = "[* TO {$condition['filter_value']}]";
                break;
            case OSC_Search::OPERATOR_BETWEEN:
                if (!is_array($condition['filter_value'])) {
                    $condition['filter_value'] = array($condition['filter_value'], $condition['filter_value']);
                }

                $from = current($condition['filter_value']);
                $to = next($condition['filter_value']);

                if ($from > $to) {
                    $buff = $to;
                    $to = $from;
                    $from = $buff;
                }

                $condition['filter_value'] = "[" . $from . " TO " . $to . "]";
                break;
            case OSC_Search::OPERATOR_LEFT:
                $condition['filter_value'] .= '*';
                break;
            case OSC_Search::OPERATOR_RIGHT:
                $condition['filter_value'] = '*' . $condition['filter_value'];
                break;
            case OSC_Search::OPERATOR_REGEXP:
                $condition['filter_value'] = '/' . $condition['filter_value'] . '/';
                break;
            case OSC_Search::OPERATOR_IN:
                $is_int = true;

                foreach ($condition['filter_value'] as $val) {
                    if (!is_int($val)) {
                        $is_int = false;
                        break;
                    }
                }

                if ($is_int) {
                    $condition['filter_value'] = "(" . implode(" OR ", $condition['filter_value']) . ")";
                } else {
                    $condition['filter_value'] = "('" . implode("' OR '", $condition['filter_value']) . "')";
                }

                break;
        }

        return ($condition['negation'] ? '-' : '') . $condition['field'] . ':' . $condition['filter_value'];
    }

    /**
     * @return SolrClient;
     */
    public function fetchSuggest($keywords) {
        $keywords = $this->cleanKeywords($keywords);

        $keywords = array_map(function($keyword) {
            if ($keyword['modifier'] == '-') {
                return '';
            }

            return $keyword['value'];
        }, $keywords);
        $keywords = array_filter($keywords);
        $keywords = array_unique($keywords);
        $keywords = implode(' ', $keywords);

        //BUILD LOOKUP
        //Run query below by crontab hourly or set buildOnCommit => true and enable autoSoftCommit
        //http://localhost:8983/solr/osecore_shop/suggest?suggest.dictionary=suggest3&suggest.build=true
        $solr_query = new SolrQuery();
        $solr_query->setQuery($keywords);
        $solr_query->addParam('suggest.dictionary', 'suggest3');

        try {
            $this->getConnection()->setServlet(SolrClient::SEARCH_SERVLET_TYPE, "suggest");

            $query_response = $this->getConnection()->query($solr_query);

            $this->getConnection()->setServlet(SolrClient::SEARCH_SERVLET_TYPE, "select");
        } catch (SolrClientException $e) {
            $this->getConnection()->setServlet(SolrClient::SEARCH_SERVLET_TYPE, "select");

            throw new Exception($e->getMessage(), $e->getCode());
        }

        $response = (array) $query_response->getResponse()->suggest->suggest3;
        $response = (array) current($response);
        $response = $response['suggestions'];

        $suggestions = array();

        foreach ($response as $term) {
            $suggestions[] = $term['term'];
        }

        return $suggestions;
    }

}
