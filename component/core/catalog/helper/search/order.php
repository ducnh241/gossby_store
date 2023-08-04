<?php

class Helper_Catalog_Search_Order extends OSC_Object
{
    protected function _getOrderSearch(): OSC_Search_Adapter_ElasticSearch {
        return OSC::core('search')->getAdapter('catalog_order_elastic_search_index');
    }

    public function _extendGetOrderSearch() {
        return $this->_getOrderSearch();
    }

    protected $_special_field_search = ['shipping_country', 'billing_country']; //In this list must search by phrase
    public function getSearchOrder($search_data = [], $page = 1, $page_size = 10, $get_count = true) {
        try {
            $search = $this->_getOrderSearch()->getConnection();
            $index = $this->_getOrderSearch()->getIndex();
            $page = intval($page) > 0 ? intval($page) : 1;
            $page_size = intval($page_size) > 0 && intval($page_size) < 10000 ? intval($page_size) : 10000;

            $query = [
                'bool' => [
                    'must' => []
                ]
            ];

            //Check sort by both score and time or by time only
            $sort_by_time = false;
            $other_field = false;
            $search_field = ['keywords'];

            if (isset($search_data['shop_id']) && !empty($search_data['shop_id'])) {
                $query['bool']['must'][] = [
                    'match' => [
                        'shop_id' => intval($search_data['shop_id'])
                    ]
                ];
            }

            if (isset($search_data['field']) && !empty($search_data['field'])) {
                $other_field = true;
                $search_field = is_array($search_data['field']) ? $search_data['field'] : [$search_data['field']];
            }

            if (isset($search_data['added_timestamp']) && !empty($search_data['added_timestamp'])) {
                $range = [];
                if (isset($search_data['added_timestamp']['start_at']) && !empty($search_data['added_timestamp']['start_at'])) {
                    $range['gte'] = $search_data['added_timestamp']['start_at'];
                }

                if (isset($search_data['added_timestamp']['end_at']) && !empty($search_data['added_timestamp']['end_at'])) {
                    $range['lte'] = $search_data['added_timestamp']['end_at'];
                }

                if (!empty($range)) {
                    $query['bool']['must'][] = [
                        'range' => [
                            'added_timestamp' => $range
                        ]
                    ];
                }
            }

            if (isset($search_data['member_hold']) && $search_data['member_hold'] == 1) {
                $sort_by_time = true;
                $query['bool']['must_not'][] = [
                    'match' => [
                        'member_hold' => 0
                    ]
                ];
            }

            if (isset($search_data['filter_value']) && !empty($search_data['filter_value'])) {
                $sort_by_time = true;
                foreach ($search_data['filter_value'] as $filter_key => $filter_value) {
                    if (is_array($filter_value)) {
                        $params = [];
                        foreach ($filter_value as $value) {
                            $params[] = [
                                'match' => [
                                    $filter_key => $value
                                ]
                            ];
                        }

                        if (!empty($params)) {
                            $query['bool']['must'][] = [
                                'bool' => [
                                    'should' => $params
                                ]
                            ];
                        }
                    } else {
                        $query['bool']['must'][] = [
                            'match' => [
                                $filter_key => $filter_value
                            ]
                        ];
                    }
                }
            }

            if (isset($search_data['keywords']) && !empty($search_data['keywords'])) {
                //Preprocess search string
                $search_keyword = preg_replace('#[^\w0-9-_.,%?@&*!\'\"\(\)\s]#uis', '', trim(strtolower($search_data['keywords'])));
                $search_keyword = preg_replace('#[\s]{2,}#is', ' ', $search_keyword);

                //If string search contains *, remove special character and search wildcard
                //Else if string search is 1 word, search by regex in field keywords, else if phrase then search term match
                $keyword_splits = preg_split("/[\s]+/", $search_keyword);
                $number_keywords = [];
                foreach ($keyword_splits as $keyword) {
                    if (is_numeric($keyword) && strpos($keyword, '0') !== 0) {
                        $number_keywords[] = intval($keyword);
                    }
                }

                $query_string = [];
                if (strpos($search_keyword, '*') !== false) {
                    if ($other_field && count(array_intersect($this->_special_field_search, $search_field)) > 0) {
                        $query_string[] = [
                            'query_string' => [
                                'query' => $search_field[0] . ':"' . trim(implode(" ", preg_split("/[*\s]+/", $search_keyword))) . '"'
                            ]
                        ];
                    } else {
                        $query_string[] = [
                            'query_string' => [
                                'query' => trim(implode(" ", preg_split("/[\s]+/", $search_keyword))),
                                'fields' => $other_field ? $search_field : ['keywords']
                            ]
                        ];
                    }
                } else if (!empty($keyword_splits)) {
                    if (count($keyword_splits) > 1) {
                        foreach ($search_field as $field) {
                            $pattern = count(array_intersect($this->_special_field_search, $search_field)) > 0 || !$other_field ? 'match_phrase' : 'match';
                            $query_string[] = [
                                $pattern => [
                                    $field => $search_keyword
                                ]
                            ];
                        }
                    } else {
                        if ($other_field && !empty($search_field)) {
                            $subquery = [];
                            foreach ($search_field as $field) {
                                $subquery[] = [
                                    'match' => [
                                        $field => [
                                            'query' => $search_keyword,
                                        ]
                                    ]
                                ];
                            }

                            $query_string[] = [
                                'bool' => [
                                    'should' => $subquery
                                ]
                            ];
                        } else {
                            $query_string[] = [
                                'regexp' => [
                                    'keywords' => [
                                        'value' => '.*' . $search_keyword . '.*',
                                        'flags' => 'all'
                                    ]
                                ]
                            ];
                        }
                    }
                }

                if (!$other_field && !empty($number_keywords)) {
                    foreach ($number_keywords as $number_keyword) {
                        $query_string[] = [
                            'multi_match' => [
                                'query' => $number_keyword,
                                'fields' => ['master_record_id', 'shipping_phone', 'billing_phone', 'list_product_id', 'list_variant_id', 'list_design_id']
                            ]
                        ];
                    }
                }

                if (!empty($query_string)) {
                    $query['bool']['must'][] = [
                        'bool' => [
                            'should' => $query_string
                        ]
                    ];
                }
            }

            if (empty($query)) {
                return false;
            }

            $sort = [
                '_score' => ['order' => 'desc'],
                'added_timestamp' => ['order' => 'desc']
            ];

            if ($sort_by_time && !(isset($search_data['keywords']) && !empty($search_data['keywords']))) {
                $sort = [
                    'added_timestamp' => ['order' => 'desc']
                ];
            }

            $params = [
                'from' => ($page - 1) * $page_size,
                'size' => $page_size,
                'index' => $index,
                'body' => [
                    'sort' => $sort,
                    'query' => $query,
                    'fields' => ['master_record_id'],
                    '_source' => false
                ]
            ];
            $response = $search->search($params);

            $list_id = [];
            $total_item = 0;

            if (isset($response['hits']['hits']) && !empty($response['hits']['hits'])) {
                $list_id = array_map(function ($item) {
                    return $item['fields']['master_record_id'][0];
                }, $response['hits']['hits']);
            }

            if ($get_count) {
                $params = [
                    'index' => $this->_index,
                    'body' => [
                        'query' => $query
                    ]
                ];

                $response = $search->count($params);
                $total_item = $response['count'] ?? 0;
            }

            return [
                'list_id' => $list_id,
                'page' => (int)$page,
                'page_size' => (int)$page_size,
                'total_item' => (int)$total_item
            ];
        } catch (Exception $exception) {
            return [
                'list_id' => [],
                'page' => (int)$page,
                'page_size' => (int)$page_size,
                'total_item' => 0
            ];
        }
    }
}