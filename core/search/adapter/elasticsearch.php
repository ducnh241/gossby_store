<?php

use Elasticsearch\ClientBuilder;

class OSC_Search_Adapter_ElasticSearch extends OSC_Search_Adapter {

    protected $_connection = null;
    protected $_host = ['http://localhost:9200'];
    protected $_index = null;
    protected $_type = null;
    protected $_prefix = null;
    protected $_skip_reset_vars = ['_connection', '_host', '_index'];

    public function setConfig($config) {
        parent::setConfig($config);

        $map = [
            'host'  => '_host',
            'index' => '_index',
            'prefix' => '_prefix',
        ];

        foreach ($map as $k => $v) {
            if (isset($config[$k])) {
                $this->$v = $config[$k];
            }
        }

        if ($this->_index !== null) {
            $this->_index = (!empty($this->_prefix) ? $this->_prefix : strtolower(OSC_SITE_KEY)) . '_' . $this->_index;
        }

        return $this;
    }

    public function getConnection() {
        if ($this->_connection === null) {
            $this->_connection = ClientBuilder::create()
                ->setHosts($this->_host)
                ->build();
        }

        return $this->_connection;
    }

    public function getIndex() {
        return $this->_index;
    }

    public function addDocument($doc)
    {
        $params = [
            'index'     => $this->_index,
            'id'        => $doc['id'] ?? 0,
            'body'      => is_array($doc) ? $doc : [$doc]
        ];

        try {
            $this->getConnection()->index($params);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    public function addDocuments($data = [])
    {
        if (!is_array($data)) {
            return $this;
        }

        $params = [
            'index' => $this->_index,
            'body' => []
        ];

        foreach ($data as $k => $v) {
            $params['body'][] = [
                'index' => [
                    '_index' => $this->_index,
                    '_id' => $v['id'] ?? 0
                ]
            ];
            $params['body'][] = $v;
        }

        try {
            $this->getConnection()->bulk($params);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    protected function _processDelete()
    {
        $query = $this->_buildQuery();

        if (empty($query)) {
            $query = [];
        }

        $params = [
            'index' => $this->_index,
            'body' => [
                'query' => $query
            ]
        ];

        try {
            $this->getConnection()->deleteByQuery($params);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function deleteDocumentById($id)
    {
        try {
            $params = [
                'index' => $this->_index,
                'id' => $id
            ];

            $this->getConnection()->delete($params);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    public function deleteDocumentByIds($ids)
    {
        if (!is_array($ids) || count($ids) < 1) {
            return $this;
        }

        try {
            $params = [
                'index' => $this->_index,
                'body' => []
            ];

            foreach ($ids as $id) {
                $params ['body'][] = [
                    'delete' => [
                        '_index' => $this->_index,
                        '_id' => $id
                    ]
                ];
            }

            $this->getConnection()->bulk($params);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return $this;
    }

    protected function _fetchProcess($keywords)
    {
        $query = $this->_buildQuery();

        $field = $this->_keyword_field;
        foreach ($keywords as $idx => $keyword) {
            if ($keyword['type'] == 'phrase') {
                $keyword['value'] = '"' . $keyword['value'] . '"';
            }

            $keywords[$idx] = $keyword['modifier'] . $keyword['value'];
        }

        $filter_array = array_map(function($keyword) use ($field) {
            return [
                'match' => [
                    $field => $keyword
                ]
            ];
        }, $keywords);

        $query['bool']['must'][] = $filter_array[0];

        $params = [
            'index' => $this->_index,
            'sort' => '_score:desc',
            'from' => $this->getOffset(),
            'size' => $this->_page_size,
            'body' => [
                'query' => $query
            ]
        ];

        $response = $this->getConnection()->search($params);

        $data = [
            'total_item' => intval($response['hits']['total']['value']),
            'took' => floatval($response['took']),
            'keywords' => $this->_keywords,
            'hits' => []
        ];

        foreach ($response['hits']['hits'] as $hit) {
            $data['hits'][] = array_merge(['_id' => $hit['_id']], $hit['_source']);
        }

        return $data;
    }

    protected function _fetchFilterQuery($options, $relation)
    {
        $query = $this->_buildQuery();

        $filter_array = array_map(function($a, $b) {
            return [
                'match' => [
                    $a => $b
                ]
            ];
        }, array_keys($options), array_values($options));

        foreach ($filter_array as $filter) {
            if ($relation === OSC_Search::_DEFAULT_RELATION) {
                $query['bool']['must'][] = $filter;
            } else {
                $query['bool']['should'][] = $filter;
            }
        }

        $params = [
            'index' => $this->_index,
            'sort' => '_score:desc',
            'from' => $this->getOffset(),
            'size' => $this->_page_size,
            'body' => [
                'query' => $query
            ]
        ];

        $response = $this->getConnection()->search($params);

        $data = [
            'total_item' => intval($response['hits']['total']['value']),
            'took' => floatval($response['took']),
            'keywords' => $this->_keywords,
            'hits' => []
        ];

        foreach ($response['hits']['hits'] as $hit) {
            $data['hits'][] = array_merge(['_id' => $hit['_id']], $hit['_source']);
        }

        return $data;
    }

    protected function _buildQuery($clause_idx = null) {
        if (!$clause_idx) {
            $clause_idx = OSC_Search::_ROOT_CLAUSE_IDX;
        }

        $query = [
            'bool' => [
                'must' => [],
                'should' => [],
                'must_not' => [],
                'filter' => []
            ]
        ];

        $counter = 0;

        foreach ($this->_condition[$clause_idx] as $condition) {
            if ($condition['type'] == 'clause') {
                if (count($this->_condition[$condition['clause_idx']]) < 1) {
                    continue;
                }

                $condition_arr = $this->_buildQuery($condition['clause_idx']);

                if (empty($condition_arr)) {
                    continue;
                }
            } else {
                $condition_arr = $this->_buildCondition($condition);

                if (!$condition_arr) {
                    continue;
                }
            }

            $counter++;
            if ($counter > 1) {
                if ($condition['relation'] === OSC_Search::_DEFAULT_RELATION) {
                    if ($condition['negation']) {
                        $query['bool']['must_not'][] = $condition_arr;
                    } else {
                        $query['bool']['must'][] = $condition_arr;
                    }
                } else {
                    $query['bool']['should'][] = $condition_arr;
                }
            } else {
                $query['bool']['must'][] = $condition_arr;
            }
        }

        return $query;
    }

    protected function _buildCondition($condition) {
        $result = [
            'match' => [
                $condition['field'] => $condition['filter_value']
            ]
        ];

        switch ($condition['operator']) {
            case OSC_Search::OPERATOR_EQUAL:
                if (preg_match('/[^a-zA-Z0-9_]/i', $condition['filter_value'])) {
                    $result = [
                        'match' => [
                            $condition['field'] => $condition['filter_value']
                        ]
                    ];
                }
                break;
            case OSC_Search::OPERATOR_GREATER_THAN_OR_EQUAL:
                $result = [
                    'range' => [
                        $condition['field'] => [
                            'gte' => $condition['filter_value']
                        ]
                    ]
                ];
                break;
            case OSC_Search::OPERATOR_LESS_THAN_OR_EQUAL:
                $result = [
                    'range' => [
                        $condition['field'] => [
                            'lte' => $condition['filter_value']
                        ]
                    ]
                ];
                break;
            case OSC_Search::OPERATOR_BETWEEN:
                if (!is_array($condition['filter_value'])) {
                    $condition['filter_value'] = [$condition['filter_value'], $condition['filter_value']];
                }

                $from = current($condition['filter_value']);
                $to = next($condition['filter_value']);

                if ($from > $to) {
                    $buff = $to;
                    $to = $from;
                    $from = $buff;
                }

                $result = [
                    'range' => [
                        $condition['field'] => [
                            'gte' => $from,
                            'lte' => $to,
                        ]
                    ]
                ];
                break;
            case OSC_Search::OPERATOR_LEFT:
                $result = [
                    'wildcard' => [
                        $condition['field'] => $condition['filter_value'] . '*'
                    ]
                ];
                break;
            case OSC_Search::OPERATOR_RIGHT:
                $result = [
                    'wildcard' => [
                        $condition['field'] => '*' . $condition['filter_value']
                    ]
                ];
                break;
            case OSC_Search::OPERATOR_REGEXP:
                $result = [
                    'regexp' => [
                        $condition['field'] => [
                            'value' => $condition['filter_value'],
                            'flags' => 'ALL',
                            'case_insensitive' => true
                        ]
                    ]
                ];
                break;
            case OSC_Search::OPERATOR_IN:
                $result = [
                    $condition['field'] . 's' => [
                        'values' => is_array($condition['filter_value']) ? $condition['filter_value'] : [$condition['filter_value']]
                    ],
                ];
                break;
            default:
                break;
        }

        return $result;
    }
}