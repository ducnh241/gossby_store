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
class Helper_Catalog_Search_Product extends OSC_Object
{
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

        if (!empty($product->data['addon_service_data']['addon_services'])) {
            $keywords[] = 'has_addon_service';
        }

        return trim(implode(" ", $keywords));
    }

    /**
     *
     * @return $this
     */
    public function resync($is_resync = false) {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');

            $search = $this->_getSearch();

            $client = $search->getConnection();
            $index = $search->getIndex();

            if ($is_resync) {
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
                                'member_id' => ['type' => 'text'],
                                'slug' => ['type' => 'text'],
                                'title' => ['type' => 'text'],
                                'topic' => ['type' => 'text'],
                                'product_title' => [
                                    'type' => 'text',
                                    'analyzer' => 'lower_analyzer',
                                    'fields' => [
                                        'shingle' => [
                                            'type' => 'text',
                                            'analyzer' => 'autocomplete'
                                        ]
                                    ]
                                ],
                                'description' => ['type' => 'text'],
                                'content' => ['type' => 'text'],
                                'product_type' => ['type' => 'text'],
                                'selling_type' => ['type' => 'text'],
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
                                'type_flag' => ['type' => 'integer'],
                                'keywords' => ['type' => 'text', 'analyzer' => 'keyword_analyzer'],
                                'added_timestamp' => ['type' => 'integer'],
                                'modified_timestamp' => ['type' => 'integer']
                            ]
                        ]
                    ]
                ];

                $client->indices()->create($params);
            }

            $collection = OSC::model('catalog/product')
                ->getCollection()
                ->addField('product_id, upc, sku, member_id, slug, title, topic, description, content, product_type, selling_type, vendor, price, discarded, listing, seo_status, solds, tags, meta_tags, seo_tags, meta_data, supply_location, added_timestamp, modified_timestamp, master_lock_flag', 'type_flag', 'addon_service_data')
                ->load();

            $list_product = [];

            foreach ($collection as $product) {
                try {
                    $list_product[$product->getId()] = $this->_makeDataProductIndex($product);
                } catch (Exception $exception) { }
            }

            $search->addDocuments($list_product);

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
        $this->_getSearch()->addDocument($this->_makeDataProductIndex($product));

        try {
            OSC::core('observer')->dispatchEvent('catalog/algoliaSyncProduct',
                [
                    'product_id' => $product->getId(),
                    'sync_type' => Helper_Catalog_Algolia_Product::SYNC_TYPE_UPDATE_PRODUCT
                ]
            );
        } catch (Exception $ex) {}

        return $this;
    }

    /**
     *
     * @param Model_Catalog_Product $product
     * @return $this
     */
    public function deleteProduct(Model_Catalog_Product $product) {
        try {
            $this->_getSearch()->deleteDocumentById($product->getId());

            OSC::core('observer')->dispatchEvent('catalog/algoliaSyncProduct',
                [
                    'product_id' => $product->getId(),
                    'sync_type' => Helper_Catalog_Algolia_Product::SYNC_TYPE_DELETE_PRODUCT
                ]
            );

        } catch (Exception $exception) {

        }

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

    public function fetchSuggest(string $keyword, int $page_size = 10) {
        try {
            $client = $this->_getSearch()->getConnection();
            $index = $this->_getSearch()->getIndex();

            $params = [
                'index' => $index,
                'sort' => '_score:desc',
                'body' => [
                    'suggest' => [
                        'text' => $keyword,
                        'phrase_suggester' => [
                            'phrase' => [
                                'field' => 'product_title.shingle',
                                'size' => $page_size,
                                'confidence' => '0.0'
                            ]
                        ]
                    ]
                ]
            ];

            $response = $client->search($params);

            $result = [];
            if (isset($response['suggest']['phrase_suggester'][0]['options']) && !empty($response['suggest']['phrase_suggester'][0]['options'])) {
                $result = array_map(function ($item) {
                    return $item['text'];
                }, $response['suggest']['phrase_suggester'][0]['options']);
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
            $flag_filter = isset($options['flag_filter']) && $options['flag_filter'];

            $sort = $options['sort'] ?? 'default';
            if (!in_array($sort, OSC::helper('filter/search')->getSortOptions(true))) {
                $sort = 'default';
            }

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

            if ($flag_filter) {
                $product_ids = (isset($options['product_ids']) && !empty($options['product_ids']) && is_array($options['product_ids'])) ? $options['product_ids'] : [0];
                $query['bool']['must'][] = [
                    'terms' => [
                        'id' => $product_ids
                    ]
                ];
            }

            if ($keywords) {
                $query['bool']['must'][] = [
                    'bool' => [
                        'should' => [
                            [
                                'match' => [
                                    'title' => $keywords
                                ]
                            ],
                            [
                                'match' => [
                                    'product_title' => $keywords
                                ]
                            ]
                        ]
                    ]
                ];
            }

            if (isset($options['location_code']) && !empty($options['location_code'])) {
                $countries_render_beta_products = OSC::helper('core/setting')->get('catalog/product_listing/country_render_beta_product');
                $shipping_location = OSC::helper('catalog/common')->getCustomerShippingLocation();
                if (!in_array($shipping_location['country_code'], $countries_render_beta_products)) {
                    $query['bool']['must'][] = [
                        'match' => [
                            'supply_location' => $options['location_code']
                        ]
                    ];
                } else {
                    $query['bool']['must'][] = [
                        'bool' => [
                            'should' => [
                                [
                                    'match' => [
                                        'supply_location' => $options['location_code']
                                    ]
                                ],
                                [
                                    'match' => [
                                        'selling_type' => Model_Catalog_Product::TYPE_SEMITEST
                                    ]
                                ]
                            ]
                        ]
                    ];
                }
            }

            switch ($sort) {
                case 'solds':
                    $sort_params = [
                        'solds' => [
                            'order' => 'desc'
                        ]
                    ];
                    break;
                case 'newest':
                    $sort_params = [
                        'added_timestamp' => [
                            'order' => 'desc'
                        ]
                    ];
                    break;
                default:
                    $sort_params = [
                        '_score' => ['order' => 'desc'],
                        'added_timestamp' => ['order' => 'desc']
                    ];
            }

            $params = [
                'from' => ($page - 1) * $page_size,
                'size' => $page_size,
                'index' => $index,
                'body' => [
                    'sort'  => $sort_params,
                    'query' => $query,
                    'fields' => ['product_id'],
                    '_source' => false
                ]
            ];

            $list_id = [];

            if ($options['debug'] ?? 0) {
                dump(OSC::encode($params));
            }
            $response = $search->search($params);

            if (isset($response['hits']['hits']) && !empty($response['hits']['hits'])) {
                $list_id = array_map(function ($item) {
                    return $item['_id'];
                }, $response['hits']['hits']);
            }

            $total_item = $response['hits']['total']['value'] ?? 0;

            return [
                'list_id' => $list_id,
                'page' => $page,
                'page_size' => $page_size,
                'total_item' => (int) $total_item
            ];
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     *
     * Search product in backend
     * */
    protected $_special_field_search = ['addon_services']; //In this list must search by phrase
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
            $sort_by_score = false;
            $other_field = false;
            $search_field = ['keywords', 'addon_services'];

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

                if (isset($search_data['solds']['product_id']) && !empty($search_data['solds']['product_id'])) {
                    $query['bool']['filter'] = [
                        'terms' => [
                            'product_id' => $search_data['solds']['product_id']
                        ]
                    ];
                }

                if (!empty($range)) {
                    $query['bool']['must'][] = [
                        'range' => [
                            'solds' => $range
                        ]
                    ];
                }
            }

            if (isset($search_data['member']) && !empty($search_data['member'])) {
                $member = [];
                if (isset($search_data['member']['member_id']) && !empty($search_data['member']['member_id'])) {
                    $member[] = [
                        'match' => [
                            'member_id' => is_array($search_data['member']['member_id']) ? implode(" ", array_unique($search_data['member']['member_id'])) : $search_data['member']['member_id']
                        ]
                    ];
                }

                if (isset($search_data['member']['vendor']) && !empty($search_data['member']['vendor'])) {
                    $member[] = [
                        'match' => [
                            'vendor' => is_array($search_data['member']['vendor']) ? implode(" ", array_unique($search_data['member']['vendor'])) : $search_data['member']['vendor']
                        ]
                    ];
                }

                $query['bool']['must'][] = [
                    'bool' => [
                        'should' => $member
                    ]
                ];
            }

            if (isset($search_data['flag_type'])) {
                $query['bool']['must'][] = [
                    'match' => [
                        'type_flag' => $search_data['flag_type']
                    ]
                ];
            }

            if (isset($search_data['filter_value']) && isset($search_data['filter_value']['has_addon_service'])) {
                if ($search_data['filter_value']['has_addon_service'] == 1) {
                    $query['bool']['must'][] = [
                        'match' => [
                            'keywords' => 'has_addon_service'
                        ]
                    ];
                } else {
                    $query['bool']['must'][] = [
                        'bool' => [
                            'must_not' => [
                                'term' => [
                                    'keywords' => 'has_addon_service'
                                ]
                            ]
                        ]
                    ];
                }

                unset($search_data['filter_value']['has_addon_service']);
            }

            if (isset($search_data['filter_value']) && !empty($search_data['filter_value'])) {
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
                if (count(array_intersect($search_field, ['product_title', 'keywords', 'addon_services'])) > 0) {
                    $sort_by_score = true;
                }

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
                            foreach ($search_field as $field) {
                                $query_string[] = [
                                    'match' => [
                                        $field => [
                                            'query' => $search_keyword
                                        ]
                                    ]
                                ];
                            }
                        } else {
                            $query_string[] = [
                                'regexp' => [
                                    'keywords' => [
                                        'value' => ".*{$search_keyword}.*",
                                        'flags' => 'ALL'
                                    ]
                                ]
                            ];
                            $query_string[] = [
                                'match_phrase' => [
                                    'addon_services' => [
                                        'query' => $search_keyword
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
                                'fields' => ['product_id', 'list_design_id', 'addon_services']
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

            if (isset($_GET['mode']) && $_GET['mode'] === 'debug') {
                if (isset($query['bool'])) {
//                    dump($query['bool']);
                }
            }

            if (empty($query)) {
                return false;
            }

            $sort = [
                'added_timestamp' => ['order' => 'desc']
            ];

            if ($sort_by_score) {
                $sort = [
                    '_score' => ['order' => 'desc'],
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

            if (isset($response['hits']['hits']) && !empty($response['hits']['hits'])) {
                $list_id = array_map(function ($item) {
                    return $item['fields']['product_id'][0];
                }, $response['hits']['hits']);
            }

            $total_item = $response['hits']['total']['value'] ?? 0;

            // 512 Mb = 512 * 1024 * 1024
            $log_memory_flag = 536870912;

            // TODO: Check memory
            $current_used_memory_usage = memory_get_usage();
            $last_time_used_memory_usage = $current_used_memory_usage;
            $current_allocated_memory_usage = memory_get_usage(true);
            $last_time_allocated_memory_usage = $current_allocated_memory_usage;

            if ($current_allocated_memory_usage >= $log_memory_flag) {
                OSC::helper('core/common')->insertHighMemoryLog(
                    $last_time_used_memory_usage,
                    $last_time_allocated_memory_usage,
                    $current_used_memory_usage,
                    $current_allocated_memory_usage,
                    'Line: ' . __LINE__ . ', after query elasticsearch'
                );
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

    protected function _makeDataProductIndex(Model_Catalog_Product $product): array
    {
        $product_id = $product->getId();
        $meta_data = OSC::encode($product->data['meta_data']);

        $addon_services = '';
        if (!empty($product->data['addon_service_data']['addon_services'])) {
            foreach ($product->data['addon_service_data']['addon_services'] as $service) {
                $addon_services .= ' ' . $service['addon_service_id'] . ' ' . $service['title'];
            }
        }
        return [
            'id' => $product_id,
            'product_id' => strval($product_id),
            'upc' => $product->data['upc'] ?? '',
            'sku' => $product->data['sku'] ?? '',
            'member_id' => $product->data['member_id'],
            'slug' => $product->data['slug'] ?? '',
            'title' => $product->data['title'] ?? '',
            'topic' => $product->data['topic'] ?? '',
            'product_title' => $product->getProductTitle(true, false),
            'autocomplete' => $product->getProductTitle(true, false),
            'description' => $product->data['description'] ?? '',
            'content' => $product->data['content'] ?? '',
            'product_type' => $product->data['product_type'] ?? '',
            'selling_type' => $product->data['selling_type'] ?? Model_Catalog_Product::TYPE_CAMPAIGN,
            'vendor' => $product->data['vendor'] ?? '',
            'price' => (int)$product->data['price'],
            'discarded' => (int)$product->data['discarded'],
            'listing' => (int)$product->data['listing'],
            'seo_status' => (int)$product->data['seo_status'],
            'solds' => (int)$product->data['solds'],
            'tags' => implode(', ', count($product->data['tags']) > 0 ? $product->data['tags'] : []),
            'meta_tags' => implode(', ', count($product->data['tags']) > 0 ? $product->data['tags'] : []),
            'seo_tags' => implode(', ', count($product->data['tags']) > 0 ? $product->data['tags'] : []),
            'meta_data' => $meta_data,
            'supply_location' => $product->data['supply_location'],
            'master_lock_flag' => (int)$product->data['master_lock_flag'],
            'type_flag' => (int)$product->data['type_flag'],
            'keywords' => $this->_fetchProductKeywords($product),
            'addon_services' => $addon_services,
            'added_timestamp' => (int)$product->data['added_timestamp'],
            //'filter_tag_ids' => OSC::helper('filter/search')->getTagByProductId($product_id)
        ];
    }
}