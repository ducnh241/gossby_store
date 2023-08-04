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
 * @copyright	Copyright (C) 2011 by SNETSER JSC (http://www.snetser.com). All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Le Tuan Sang - batsatla@gmail.com
 */
class Helper_Catalog_ProductIndex extends OSC_Object {
    /**
     * 
     * @return OSC_Search_Adapter_ElasticSearch
     */
    protected function _getSearch(): OSC_Search_Adapter_ElasticSearch {
        return OSC::core('search')->getAdapter('catalog_product_elastic_search_index');
    }

    /**
     * 
     * @param Model_Catalog_Product $product
     * @return string
     */
    protected function _fetchProductKeywords(Model_Catalog_Product $product): string {
        $keywords = [];

        foreach (['product_type', 'title', 'topic', 'vendor', 'tags', 'meta_tags', 'seo_tags'] as $key) {
            $keywords[] = (is_array($product->data[$key]) ? implode(' ', $product->data[$key]) : trim($product->data[$key]));
        }

        $keywords[] = $product->getProductTitle(true, false);

        return trim(implode(" ", $keywords));
    }

    /**
     * 
     * @return $this
     */
    public function resync() {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');

            //$this->_getSearch()->delete();
            $client = $this->_getSearch()->getConnection();
            $index = $this->_getSearch()->getIndex();

            try {
                //Delete index
                $client->indices()->delete([
                    'index' => $index
                ]);
            } catch (Exception $exception) { }

            //Create index
            $params = [
                'index' => $index,
                'body' => [
                    'settings' => [
                        'max_result_window' => 50000,
                        'index' => [
                            'number_of_shards' => 1,
                            'analysis' => [
                                'analyzer' => [
                                    'keyword_analyzer' => [
                                        'type' => 'custom',
                                        'tokenizer' => 'my_tokenizer',
                                        'filter' => ['lowercase']
                                    ],
                                    'autocomplete' => [
                                        'filter' => ['lowercase', 'shingle_filter'],
                                        'char_filter' => ['html_strip'],
                                        'type' => 'custom',
                                        'tokenizer' => 'standard'
                                    ],
                                    'default' => [
                                        'filter' => ['lowercase', 'stopwords_filter', 'stemmer_filter'],
                                        'char_filter' => ['html_strip'],
                                        'type' => 'custom',
                                        'tokenizer' => 'standard'
                                    ],
                                    'lower_analyzer' => [
                                        'filter' => ['lowercase'],
                                        'char_filter' => ['html_strip'],
                                        'type' => 'custom',
                                        'tokenizer' => 'standard'
                                    ]
                                ],
                                'filter' => [
                                    'shingle_filter' => [
                                        'type' => 'shingle',
                                        'min_shingle_size' => 2,
                                        'max_shingle_size' => 3
                                    ],
                                    'stemmer_filter' => [
                                        'type' => 'stemmer',
                                        'language' => 'english'
                                    ],
                                    'stopwords_filter' => [
                                        'type' => 'stop',
                                        'stopwords' => ['_english_']
                                    ]
                                ],
                                'tokenizer' => [
                                    'my_tokenizer' => [
                                        'type' => 'char_group',
                                        'tokenize_on_chars' => ['whitespace']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'mappings' => [
                        'properties' => [
                            'autocomplete' => ['type' => 'completion', 'analyzer' => 'autocomplete'],
                            'product_id' => ['type' => 'text'],
                            'upc' => ['type' => 'text'],
                            'sku' => ['type' => 'text'],
                            'member_id' => ['type' => 'integer'],
                            'slug' => ['type' => 'text'],
                            'title' => ['type' => 'text'],
                            'topic' => ['type' => 'text'],
                            'product_title' => ['type' => 'text', 'analyzer' => 'lower_analyzer'],
                            'description' => ['type' => 'text'],
                            'content' => ['type' => 'text'],
                            'product_type' => ['type' => 'text'],
                            'vendor' => ['type' => 'text', 'analyzer' => 'lower_analyzer'],
                            'price' => ['type' => 'integer'],
                            'discarded' => ['type' => 'integer'],
                            'listing' => ['type' => 'integer'],
                            'seo_status' => ['type' => 'integer'],
                            'solds' => ['type' => 'integer'],
                            'tags' => ['type' => 'text'],
                            'meta_tags' => ['type' => 'text'],
                            'seo_tags' => ['type' => 'text'],
                            'meta_data' => ['type' => 'text'],
                            'supply_location' => ['type' => 'text'],
                            'master_lock_flag' => ['type' => 'integer'],
                            'keywords' => ['type' => 'text', 'analyzer' => 'keyword_analyzer'],
                            'added_timestamp' => ['type' => 'integer']
                        ]
                    ]
                ]
            ];

            $client->indices()->create($params);

            $collection = OSC::model('catalog/product')
                ->getCollection()
                ->addField('product_id, upc, sku, member_id, slug, title, topic, description, content, product_type, vendor, price, discarded, listing, seo_status, solds, tags, meta_tags, seo_tags, meta_data, supply_location, added_timestamp, modified_timestamp, master_lock_flag')
                ->load();

            $list_product = [];

            foreach ($collection as $product) {
                try {
                    $product_id = (int) $product->getId();
                    $list_product[] = [
                        'id' => $product_id,
                        'product_id' => strval($product_id),
                        'upc' => $product->data['upc'] ?? '',
                        'sku' => $product->data['sku'] ?? '',
                        'member_id' => (int) $product->data['member_id'],
                        'slug' => $product->data['slug'] ?? '',
                        'title' => $product->data['title'] ?? '',
                        'topic' => $product->data['topic'] ?? '',
                        'product_title' => $product->getProductTitle(true, false),
                        'description' => $product->data['description'] ?? '',
                        'content' => $product->data['content'] ?? '',
                        'product_type' => $product->data['product_type'] ?? '',
                        'vendor' => $product->data['vendor'] ?? '',
                        'price' => (int) $product->data['price'],
                        'discarded' => (int) $product->data['discarded'],
                        'listing' => (int) $product->data['listing'],
                        'seo_status' => (int) $product->data['seo_status'],
                        'solds' => (int) $product->data['solds'],
                        'tags' => implode(', ', count($product->data['tags']) > 0 ? $product->data['tags'] : []),
                        'meta_tags' => implode(', ', count($product->data['tags']) > 0 ? $product->data['tags'] : []),
                        'seo_tags' => implode(', ', count($product->data['tags']) > 0 ? $product->data['tags'] : []),
                        'meta_data' => $product->data['meta_data'],
                        'supply_location' => $product->data['supply_location'],
                        'master_lock_flag' => (int) $product->data['master_lock_flag'],
                        'keywords' => $this->_fetchProductKeywords($product),
                        'added_timestamp' => (int) $product->data['added_timestamp']
                    ];
                } catch (Exception $exception) { }
            }

            $this->_getSearch()->addDocuments($list_product);

            return $this;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 
     * @param Model_Catalog_Product $product
     * @return $this
     */
    public function addProduct(Model_Catalog_Product $product) {
        $product_id = $product->getId();

        $meta_data = OSC::encode($product->data['meta_data']);

        $this->_getSearch()->addDocument([
            'id' => $product_id,
            'product_id' => strval($product_id),
            'upc' => $product->data['upc'] ?? '',
            'sku' => $product->data['sku'] ?? '',
            'member_id' => (int) $product->data['member_id'],
            'slug' => $product->data['slug'] ?? '',
            'title' => $product->data['title'] ?? '',
            'topic' => $product->data['topic'] ?? '',
            'product_title' => $product->getProductTitle(true, false),
            'description' => $product->data['description'] ?? '',
            'content' => $product->data['content'] ?? '',
            'product_type' => $product->data['product_type'] ?? '',
            'vendor' => $product->data['vendor'] ?? '',
            'price' => (int) $product->data['price'],
            'discarded' => (int) $product->data['discarded'],
            'listing' => (int) $product->data['listing'],
            'seo_status' => (int) $product->data['seo_status'],
            'solds' => (int) $product->data['solds'],
            'tags' => implode(', ', count($product->data['tags']) > 0 ? $product->data['tags'] : []),
            'meta_tags' => implode(', ', count($product->data['tags']) > 0 ? $product->data['tags'] : []),
            'seo_tags' => implode(', ', count($product->data['tags']) > 0 ? $product->data['tags'] : []),
            'meta_data' => $meta_data,
            'supply_location' => $product->data['supply_location'],
            'master_lock_flag' => (int) $product->data['master_lock_flag'],
            'keywords' => $this->_fetchProductKeywords($product),
            'added_timestamp' => (int) $product->data['added_timestamp']
        ]);

        return $this;
    }

    /**
     * 
     * @param Model_Catalog_Product $product
     * @return $this
     */
    public function deleteProduct(Model_Catalog_Product $product) {
        $this->_getSearch()->deleteDocumentById($this->_makeDocKey($product));

        return $this;
    }

    /**
     *
     * @param string $keywords
     * @param int $page_size
     * @param int $page
     * @param int $suggestion_if_less_than
     * @param array $skip_ids
     * @return array
     */
    public function fetch(string $keywords, int $page_size = 5, int $page = 1, int $suggestion_if_less_than = 0, array $skip_ids = []): array {
        $search_result = $this->_fetch($keywords, $page_size, $page, $skip_ids);

        return [
            'hits' => $search_result['hits'],
            'offset' => $search_result['offset'],
            'page_size' => $search_result['page_size'],
            'total_item' => $search_result['total_item'],
            'total_page' => $search_result['total_page'],
            'current_page' => $search_result['current_page']
        ];
    }

    /**
     * 
     * @param string $keywords
     * @param int $page_size
     * @param int $page
     * @param array $skip_ids
     * @return array
     */
    protected function _fetch(string $keywords, int $page_size = 25, int $page = 1, array $skip_ids = []): array {
        $search = $this->_getSearch();

        if ($page_size < 1 || $page_size > 25) {
            $page_size = 25;
        }

        $search->setPageSize($page_size)
                ->setCurrentPage($page)
                ->addField('id', 'data')
                ->addSort('added_timestamp', OSC_Search::ORDER_DESC);

        if (count($skip_ids) > 0) {
            $skip_ids = array_map(function($id) {
                return intval($id);
            }, $skip_ids);

            $skip_ids = array_filter($skip_ids, function($id) {
                return $id > 0;
            });

            if (count($skip_ids) > 0) {
                $search->addFilter('id', array_unique($skip_ids), 'NOT_' . OSC_Search::OPERATOR_IN);
            }
        }

        $search_result = $search->fetchFilter(['title' => '"' . $keywords . '"']);
        //$search->setKeywords($keywords);
        //$search_result = $search->fetch(['allow_no_keywords', 'auto_fix_page']);

        return $search_result;
    }

    public function fetchSuggest(string $keyword) {
        try {
            $client = $this->_getSearch()->getConnection();
            $index = $this->_getSearch()->getIndex();
            $page_size = 5;

            $params = [
                'index' => $index,
                'sort' => '_score:desc',
                'body' => [
                    'suggest' => [
                        'text' => $keyword,
                        'simple_phrase' => [
                            'phrase' => [
                                'field' => 'title',
                                'size' => $page_size,
                                'gram_size' => $page_size,
                                'direct_generator' => [
                                    [
                                        'field' => 'title',
                                        'suggest_mode' => 'always'
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            ];

            $response = $client->search($params);

            $result = [];
            if (isset($response['suggest']['simple_phrase'][0]['options']) && !empty($response['suggest']['simple_phrase'][0]['options'])) {
                $result = array_map(function ($item) {
                    return $item['text'];
                }, $response['suggest']['simple_phrase'][0]['options']);
            }

            return $result;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function fetchAutocomplete(string $keyword) {
        try {
            $client = $this->_getSearch()->getConnection();
            $index = $this->_getSearch()->getIndex();
            $page_size = 5;

            $params = [
                'index' => $index,
                'sort' => '_score:desc',
                'body' => [
                    'suggest' => [
                        'suggestion' => [
                            'prefix' => $keyword,
                            'completion' => [
                                'field' => 'autocomplete',
                                'size' => $page_size,
                                'skip_duplicates' => true,
                                'fuzzy' => ['fuzziness' => 2]
                            ]
                        ]
                    ]
                ]
            ];

            $response = $client->search($params);

            $result = [];
            if (isset($response['suggest']['suggestion'][0]['options']) && !empty($response['suggest']['suggestion'][0]['options'])) {
                $result = array_map(function ($item) {
                    return $item['text'];
                }, $response['suggest']['suggestion'][0]['options']);
            }

            return $result;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     *
     * Search product in frontend
     * @param string $keywords
     * @param int $page_size
     * @param int $page
     * @param int $suggestion_if_less_than
     * @param array $skip_ids
     * @return array
     */
    public function searchProduct(string $keywords, array $options = [], int $page = 1, int $page_size = 5, $get_count = true): array {
        try {
            $search = $this->_getSearch()->getConnection();
            $index = $this->_getSearch()->getIndex();

            $keywords = strtolower($keywords);

            $query = [
                'bool' => [
                    'must' => [
                        [
                            'match' => [
                                'discarded' => 0,
                            ],
                        ],
                        [
                            'match' => [
                                'listing' => 1
                            ]
                        ]
                    ],
                ]
            ];

            if (isset($options['skip_ids']) && !empty($options['skip_ids']) && is_array($options['skip_ids'])) {
                $query['bool']['must_not'] = [
                    'terms' => [
                        'id' => $options['skip_ids']
                    ]
                ];
            }

            $query['bool']['must'][] = [
                'match' => [
                    'product_title' => $keywords
                ]
            ];

            if (isset($options['location_code']) && !empty($options['location_code'])) {
                $query['bool']['must'][] = [
                    'match' => [
                        'supply_location' => $options['location_code']
                    ]
                ];
            }

            $params = [
                'from' => ($page - 1) * $page_size,
                'size' => $page_size,
                'index' => $index,
                'body' => [
                    'sort'  => [
                        '_score' => ['order' => 'desc'],
                        'added_timestamp' => ['order' => 'desc']
                    ],
                    'query' => $query,
                    'fields' => ['product_id'],
                    '_source' => false
                ]
            ];

            $list_id = [];
            $total_item = 0;

            $response = $search->search($params);

            if (isset($response['hits']['hits']) && !empty($response['hits']['hits'])) {
                $list_id = array_map(function ($item) {
                    return $item['_id'];
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
                'page' => $page,
                'page_size' => $page_size,
                'total_item' => (int)$total_item
            ];
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     *
     * Search product in backend
     * */
    protected $_special_field_search = []; //In this list must search by phrase
    public function getFilterProduct($search_data = [], $page = 1, $page_size = 10, $get_count = true) {
        try {
            $search = $this->_getSearch()->getConnection();
            $index = $this->_getSearch()->getIndex();
            $page = intval($page) > 0 ? intval($page) : 1;
            $page_size = intval($page_size) > 0 && intval($page_size) < 10000 ? intval($page_size) : 10000;

            $query = [
                'bool' => [
                    'must' => [],
                ]
            ];

            //Check sort by both score and time or by time only
            $sort_by_time = false;
            $other_field = false;
            $search_field = ['keywords'];

            if (isset($search_data['field']) && !empty($search_data['field'])) {
                $other_field = true;
                $search_field = is_array($search_data['field']) ? $search_data['field'] : [$search_data['field']];
            }

            //Build range added_timestamp and modified_timestamp
            $time_array = ['added_timestamp', 'modified_timestamp'];
            foreach ($time_array as $time_item) {
                if (isset($search_data[$time_item]) && !empty($search_data[$time_item])) {
                    $range = [];
                    if (isset($search_data[$time_item]['start_at']) && !empty($search_data[$time_item]['start_at'])) {
                        $range['gte'] = $search_data[$time_item]['start_at'];
                    }

                    if (isset($search_data[$time_item]['end_at']) && !empty($search_data[$time_item]['end_at'])) {
                        $range['lte'] = $search_data[$time_item]['end_at'];
                    }

                    if (!empty($range)) {
                        $query['bool']['must'][] = [
                            'range' => [
                                $time_item => $range
                            ]
                        ];
                    }
                }
            }

            //Build range min max solds
            if (isset($search_data['solds']) && !empty($search_data['solds'])) {
                $range = [];
                if (isset($search_data['solds']['min']) && !empty($search_data['solds']['min'])) {
                    $range['gte'] = $search_data['solds']['min'];
                }

                if (isset($search_data['solds']['max']) && !empty($search_data['solds']['max'])) {
                    $range['lte'] = $search_data['solds']['max'];
                }

                if (!empty($range)) {
                    $query['bool']['must'][] = [
                        'range' => [
                            'solds' => $range
                        ]
                    ];
                }
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
                $search_data['keywords'] = trim(strtolower($search_data['keywords']));
                //If string search contains *, remove special character and search wildcard
                //Else if string search is 1 word, search by regex in field keywords, else if phrase then search term match
                $keyword_splits = preg_split("/[\s]+/", $search_data['keywords']);
                $number_keywords = [];
                foreach ($keyword_splits as $keyword) {
                    if (is_numeric($keyword) && strpos($keyword, '0') !== 0) {
                        $number_keywords[] = intval($keyword);
                    }
                }

                $query_string = [];
                if (strpos($search_data['keywords'], '*') !== false) {
                    if ($other_field && count(array_intersect($this->_special_field_search, $search_field)) > 0) {
                        $query_string[] = [
                            'query_string' => [
                                'query' => $search_field[0] . ':"' . trim(implode(" ", preg_split("/[*\s]+/", $search_data['keywords']))) . '"'
                            ]
                        ];
                    } else {
                        $query_string[] = [
                            'query_string' => [
                                'query' => trim(implode(" ", preg_split("/[\s]+/", $search_data['keywords']))),
                                'fields' => $other_field ? $search_field : ['keywords']
                            ]
                        ];
                    }
                } else if (!empty($keyword_splits)) {
                    if (count($keyword_splits) > 1) {
                        $search_keyword = preg_replace('#[^a-z0-9-_.,%?@&*!\'\"\s]#is', '', $search_data['keywords']);
                        $search_keyword = preg_replace('#[\s]{2,}#is', ' ', $search_keyword);
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
                                            'query' => $search_data['keywords'],
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
                                        'value' => '.*' . $search_data['keywords'] . '.*',
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
                                'fields' => ['product_id', 'list_design_id']
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

            dump($query);
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
                    'fields' => ['product_id'],
                    '_source' => false
                ]
            ];

            $response = $search->search($params);

            $list_id = [];
            $total_item = 0;

            if (isset($response['hits']['hits']) && !empty($response['hits']['hits'])) {
                $list_id = array_map(function ($item) {
                    return $item['fields']['product_id'][0];
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