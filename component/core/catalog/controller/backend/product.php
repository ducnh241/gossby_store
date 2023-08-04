<?php

class Controller_Catalog_Backend_Product extends Abstract_Catalog_Controller_Backend {
    protected $_search_product_key = 'catalog/product/search';
    protected $_search_product_sold_key = 'catalog/product/search/sold';

    protected $_search_filter_field = 'search_filter_field';
    protected $_default_search_field_key = 'default_search_product_field';
    protected $_filter_field = [
        'all' => 'All field',
        'product_id' => 'ID',
        'product_title' => 'Title',
        'vendor' => 'Vendor',
        'product_type' => 'Product Type',
        'sku' => 'SKU',
        'addon_services' => 'Add-on Service'
    ];

    public function __construct() {
        parent::__construct();

        $this->checkPermission('catalog/super|catalog/product');

        $this->getTemplate()
            ->setCurrentMenuItemKey('catalog/product')
            ->setPageTitle('Manage Products');
    }

    public function actionIndex() {
        $this->forward('*/*/list');
    }

    protected function _getFilterConfig($filter_value = null) {
        $filter_config = [];

        $filter_config['status'] = [
            'title' => 'Status',
            'type' => 'radio',
            'data' => [
                '0' => 'Activated',
                '1' => 'Discarded'
            ],
            'field' => 'discarded'
        ];

        if (!$this->getAccount()->isAdmin()) {
            $members = OSC::helper('adminGroup/common')->getMembersGroup($this->getAccount()->getId());

            if (count($members) > 1) {
                $members_array = [];
                foreach ($members as $member) {
                    $members_array[$member->data['member_id']] = $member->getGroup()->data['title'] .' - '. $member->data['username'];
                }
                $filter_config['member'] =  [
                    'title' => 'Member',
                    'type' => 'checkbox',
                    'data' => $members_array,
                    'field' => 'member_id'
                ];
            }
        }

        $filter_config['listing'] = [
            'title' => 'Listing',
            'type' => 'radio',
            'data' => [
                '1' => 'Yes',
                '0' => 'No'
            ],
            'field' => 'listing'
        ];

        $filter_config['solds'] = [
            'title' => 'Sold',
            'type' => 'range',
            'field' => 'solds',
            'prefix' => 'Sold',
            'data' => [
                'min' => 'Min',
                'max' => 'Max',
                'time' => 'Time'
            ]
        ];

        $filter_config['status_seo'] = [
            'title' => 'SEO status',
            'type' => 'radio',
            'data' => [
                '1' => 'Optimized',
                '0' => 'UnOptimized'
            ],
            'field' => 'seo_status',
            'prefix' => 'SEO status'
        ];

        $filter_config['addon_service'] = [
            'title' => 'Add-on service',
            'type' => 'radio',
            'data' => [
                '1' => 'Add-on',
                '0' => 'No Add-on'
            ],
            'field' => 'has_addon_service'
        ];

        $filter_config['date'] = [
            'title' => 'Added date',
            'type' => 'daterange',
            'field' => 'added_timestamp',
            'prefix' => 'Added date'
        ];

        $filter_config['mdate'] = [
            'title' => 'Modified date',
            'type' => 'daterange',
            'field' => 'modified_timestamp',
            'prefix' => 'Modified date'
        ];

        if ($filter_value !== null) {
            if (!is_array($filter_value)) {
                $filter_value = [];
            }

            foreach ($filter_config as $k => $v) {
                unset($v['field']);

                if (isset($filter_value[$k])) {
                    $v['value'] = $filter_value[$k];
                }

                $filter_config[$k] = $v;
            }
        }

        return $filter_config;
    }

    public function actionSearch() {
        $filter_value = $this->_request->get('filter');

        $path_list = '*/*/list';

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterConfig(), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet($this->_search_product_key, [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value,
            'filter_field' => $this->_request->get('filter_field')
        ], 60 * 60);

        static::redirect($this->getUrl($path_list, ['search' => 1]));
    }

    protected function _applyListCondition(Model_Catalog_Product_Collection $collection): void {
        $search = OSC::sessionGet($this->_search_product_key);
        $filter_config = $this->_getFilterConfig();
        $filter_value = $search['filter_value'];
        if ($search) {
            OSC::sessionRemove($this->_search_product_sold_key);
            $keywords = trim($search['keywords']);
            $ids = explode(' ', $keywords);

            foreach ($ids as $key => $id) {
                if (intval($id) > 0) {
                    $ids[$key] = intval($id);
                } else {
                    unset($ids[$key]);
                }
            }

            $ids = implode(',', $ids);

            $list_product_identifires = OSC::helper('catalog/product')->getListProductIdentifiers();

            $arr_keywords = explode('-', $keywords);

            if (in_array(trim(end($arr_keywords)), $list_product_identifires) && count($arr_keywords) > 1) {
                unset($arr_keywords[array_key_last($arr_keywords)]);
                $keywords = trim(implode('-', $arr_keywords));
            }

            $condition = [];
            $params = [];
            if (!empty($keywords)) {
                $condition_search = [];
                $arr_field_search = ['product_id', 'title', 'product_type', 'vendor'];
                $filter_value['search_in'] = isset($filter_value['search_in']) ? $filter_value['search_in'] : $arr_field_search;

                if(isset($filter_value['search_in'])){
                    foreach ($filter_value['search_in'] as $key => $value){
                        if($value != 'product_id'){
                            $condition_search[] = $value." LIKE :keywords";
                        }
                    }

                    if(in_array('title',$filter_value['search_in'])){
                        $condition_search[] = "CONCAT(TRIM(topic), ' - ', TRIM(title)) LIKE :keywords";
                    }

                    $params = array_merge($params, [
                        'keywords' => '%' . $keywords . '%'
                    ]);

                    if ($ids && in_array('product_id',$filter_value['search_in'])) {
                        $condition_search[] = "product_id IN " . '(' . $ids . ')';
                    }
                }

                if($condition_search) {
                    $condition[] = '(' . implode(' OR ', $condition_search) . ')';
                }
            }

            if (count($filter_value) > 0) {
                foreach ($filter_value as $key => $value) {
                    if (isset($filter_config[$key]) && $key == "search_in") {
                        continue;
                    }

                    if (!isset($filter_config[$key]) || $key == 'member') {
                        continue;
                    }

                    if (is_array($value) && count($value) == 1 && $filter_config[$key]['type'] != 'range') {
                        $value = $value[0];
                    }

                    if (is_array($value)) {
                        if ($filter_config[$key]['type'] == 'range' && $key == "solds") {
                            $productIds = [];
                            if (isset($value['time']) && !empty($value['time'])) {
                                preg_match('/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*\-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?\s*$/', $value['time'], $matches);

                                $start_timestamp = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);

                                if ($matches[5]) {
                                    $end_timestamp = mktime(24, 0, 0, $matches[6], $matches[5], $matches[7]);
                                } else {
                                    $end_timestamp = mktime(24, 0, 0, $matches[2], $matches[1], $matches[3]);
                                }

                                $DB = OSC::core('database')->getAdapter('db_master');
                                $query = "SELECT COUNT(product_id) as sold, product_id FROM osc_catalog_order_item WHERE added_timestamp >= {$start_timestamp} AND added_timestamp <= {$end_timestamp} GROUP BY product_id ORDER BY sold DESC";

                                try {
                                    $DB->query($query, null, 'fetch_productBySold');
                                    while ($row = $DB->fetchArray('fetch_productBySold')) {
                                        $productIds[$row['product_id']] = $row['sold'];
                                    }
                                    $DB->free('fetch_productBySold');
                                } catch (Exception $ex) { }

                                if (isset($value['min']) && !empty($value['min']) && isset($value['max']) && !empty($value['max'])) {
                                    foreach ($productIds as $k => $v) {
                                        if ($v < $value['min'] || $v > $value['max']) {
                                            unset($productIds[$k]);
                                        }
                                    }
                                } else {
                                    if (isset($value['min']) && !empty($value['min'])) {
                                        foreach ($productIds as $k => $v) {
                                            if ($v < $value['min']) {
                                                unset($productIds[$k]);
                                            }
                                        }
                                    }
                                    if (isset($value['max']) && !empty($value['max'])) {
                                        foreach ($productIds as $k => $v) {
                                            if ($v > $value['max']) {
                                                unset($productIds[$k]);
                                            }
                                        }
                                    }
                                }

                                if (count($productIds) > 0) {
                                    OSC::sessionSet($this->_search_product_sold_key, $productIds, 60 * 60);
                                    $condition[] = 'product_id in ('. implode(',', array_keys($productIds)).')';
                                } else {
                                    /** No product to return */
                                    $condition[] = 'product_id = 0';
                                }
                            } else {
                                if (isset($value['min']) && !empty($value['min']) && isset($value['max']) && !empty($value['max'])) {
                                    $condition[] = "(" . $filter_config[$key]['field'] . " >= :min_sold AND " . $filter_config[$key]['field'] . " <= :max_sold)";
                                    $params = array_merge($params, [
                                        "min_sold" => $value['min'],
                                        "max_sold" => $value['max'],
                                    ]);
                                } else {
                                    if (isset($value['min']) && !empty($value['min'])) {
                                        $condition[] = "(" . $filter_config[$key]['field'] . " >= :min_sold)";
                                        $params = array_merge($params, [
                                            "min_sold" => $value['min']
                                        ]);
                                    }
                                    if (isset($value['max']) && !empty($value['max'])) {
                                        $condition[] = "(" . $filter_config[$key]['field'] . " <= :max_sold)";
                                        $params = array_merge($params, [
                                            "max_sold" => $value['max']
                                        ]);
                                    }
                                }
                            }
                        } else {
                            foreach ($value as $v) {
                                $condition[] = $filter_config[$key]['field'] . " = :" . $filter_config[$key]['field'];
                                $params = array_merge($params, [
                                    $filter_config[$key]['field'] => $v
                                ]);
                            }
                        }

                    } else if ($filter_config[$key]['type'] == 'daterange') {
                        preg_match('/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*\-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?\s*$/', $value, $matches);

                        $start_timestamp = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);

                        if ($matches[5]) {
                            $end_timestamp = mktime(24, 0, 0, $matches[6], $matches[5], $matches[7]);
                        } else {
                            $end_timestamp = mktime(24, 0, 0, $matches[2], $matches[1], $matches[3]);
                        }

                        $condition[] = "(" . $filter_config[$key]['field'] . " >= :start_timestamp AND " . $filter_config[$key]['field'] . " <= :end_timestamp)";
                        $params = array_merge($params, [
                            "start_timestamp" => $start_timestamp,
                            "end_timestamp" => $end_timestamp,
                        ]);
                    } else if($filter_config[$key]['type'] == 'number') {
                        $condition[] = $filter_config[$key]['field'] . ($filter_config[$key]['operator'] ?? ' = ') . ":" . $filter_config[$key]['field'];
                        $params = array_merge($params, [
                            $filter_config[$key]['field'] => $value
                        ]);
                    } else {
                        $condition[] = $filter_config[$key]['field'] . " = :" . $filter_config[$key]['field'];
                        $params = array_merge($params, [
                            $filter_config[$key]['field'] => $value
                        ]);
                    }
                }
            }
            $condition_extra = [];
            if (!$this->getAccount()->isAdmin() && !$this->checkPermission('catalog/product/view_all|catalog/product/full', false)) {
                if (!isset($filter_value['member'])) {
                    $members = OSC::helper('adminGroup/common')->getMembersGroup($this->getAccount()->getId(), false);
                } else {
                    $members  = OSC::model('user/member')->getCollection()->load($filter_value['member']);
                }
                $members_ids = [];
                $members_names = [];
                foreach ($members as $member) {
                    $members_ids[] = $member->getId();
                    $members_names[] = $member->data['username'];
                }
                if (count($members_names) > 0) {
                    $condition_extra[] = 'vendor in ("'. implode('","', $members_names).'")';
                }

                if ($this->checkPermission('catalog/product/view_group', false)) {
                    $members_ids = array_merge($members_ids, OSC::helper('adminGroup/common')->getMembersByGroup($this->getAccount()->getGroup()->getId(), true));
                }

                $condition_extra[] = 'member_id in ('. implode(',', $members_ids).')';
            }
            $condition = [
                'condition' => implode(' AND ', $condition),
                'params' => $params
            ];

            if (count($condition_extra) > 0) {
                $condition['condition'] = ($condition['condition'] != '' ? $condition['condition']. ' AND ' : ' '). '(' . implode(' OR ', $condition_extra).')';
            }

            $collection->setCondition($condition);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    protected function _applySearchProduct(Model_Catalog_Product_Collection $collection, $page = 1, $pageSize = 25, $get_count = true, $options = []) {
        // 512 Mb = 512 * 1024 * 1024
        $log_memory_flag = 536870912;

        $search = OSC::sessionGet($this->_search_product_key);

        if ($search) {
            $search_data = [];
            $keywords = trim(strtolower($search['keywords']));
            $filter_value = $search['filter_value'] ?? [];
            $filter_field = $search['filter_field'] ?? '';

            $ids = explode(' ', $keywords);

            foreach ($ids as $key => $id) {
                if (intval($id) > 0) {
                    $ids[$key] = intval($id);
                } else {
                    unset($ids[$key]);
                }
            }

            $mapping_search_data = [];

            if (!empty($keywords)) {
                $search_data['keywords'] = trim($keywords);
            }

            if (is_array($filter_value) && count($filter_value) > 0) {
                $filter_config = $this->_getFilterConfig();

                foreach ($filter_value as $key => $value) {
                    if (!isset($filter_config[$key]) || $key == 'member') {
                        continue;
                    }

                    if (is_array($value) && count($value) == 1 && $filter_config[$key]['type'] != 'range') {
                        $value = $value[0];
                    }

                    if (is_array($value)) {
                        if ($filter_config[$key]['type'] == 'range' && $key == 'solds') {
                            $productIds = [];
                            if (isset($value['time']) && !empty($value['time'])) {
                                preg_match('/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*\-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?\s*$/', $value['time'], $matches);

                                $start_timestamp = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);

                                if ($matches[5]) {
                                    $end_timestamp = mktime(24, 0, 0, $matches[6], $matches[5], $matches[7]);
                                } else {
                                    $end_timestamp = mktime(24, 0, 0, $matches[2], $matches[1], $matches[3]);
                                }

                                $DB = OSC::core('database')->getAdapter('db_master');
                                $query = "SELECT COUNT(product_id) as sold, product_id FROM osc_catalog_order_item WHERE added_timestamp >= {$start_timestamp} AND added_timestamp <= {$end_timestamp} GROUP BY product_id ORDER BY sold DESC";

                                try {
                                    $DB->query($query, null, 'fetch_productBySold');
                                    while ($row = $DB->fetchArray('fetch_productBySold')) {
                                        $productIds[$row['product_id']] = $row['sold'];
                                    }
                                    $DB->free('fetch_productBySold');
                                } catch (Exception $ex) { }

                                if (isset($value['min']) && !empty($value['min']) && isset($value['max']) && !empty($value['max'])) {
                                    foreach ($productIds as $k => $v) {
                                        if ($v < $value['min'] || $v > $value['max']) {
                                            unset($productIds[$k]);
                                        }
                                    }
                                } else {
                                    if (isset($value['min']) && !empty($value['min'])) {
                                        foreach ($productIds as $k => $v) {
                                            if ($v < $value['min']) {
                                                unset($productIds[$k]);
                                            }
                                        }
                                    }
                                    if (isset($value['max']) && !empty($value['max'])) {
                                        foreach ($productIds as $k => $v) {
                                            if ($v > $value['max']) {
                                                unset($productIds[$k]);
                                            }
                                        }
                                    }
                                }

                                if (count($productIds) > 0) {
                                    OSC::sessionSet($this->_search_product_sold_key, $productIds, 60 * 60);
                                    $search_data[$filter_config[$key]['field']]['product_id'] = array_keys($productIds);
                                } else {
                                    /** No product to return */
                                    $search_data[$filter_config[$key]['field']]['product_id'] = [0];
                                }
                            } else {
                                if (isset($value['min']) && !empty($value['min'])) {
                                    $search_data[$filter_config[$key]['field']]['min'] = $value['min'];
                                }

                                if (isset($value['max']) && !empty($value['max'])) {
                                    $search_data[$filter_config[$key]['field']]['max'] = $value['max'];
                                }
                            }
                        } else {
                            foreach ($value as $v) {
                                $mapping_search_data[$filter_config[$key]['field']] = $v;
                            }
                        }
                    } else if ($filter_config[$key]['type'] == 'daterange') {
                        preg_match('/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*\-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?\s*$/', $value, $matches);

                        $start_timestamp = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);

                        if ($matches[5]) {
                            $end_timestamp = mktime(24, 0, 0, $matches[6], $matches[5], $matches[7]);
                        } else {
                            $end_timestamp = mktime(24, 0, 0, $matches[2], $matches[1], $matches[3]);
                        }

                        $search_data[$filter_config[$key]['field']] =  [
                            'start_at' => $start_timestamp,
                            'end_at' => $end_timestamp,
                        ];
                    } else {
                        $mapping_search_data[$filter_config[$key]['field']] =  $value;
                    }
                }
            }

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
                    'Line: ' . __LINE__ . ', after check filter config'
                );
            }

            if (!$this->getAccount()->isAdmin() && isset($filter_value['member'])) {
                $members  = OSC::model('user/member')->getCollection()->load($filter_value['member']);

                $members_ids = [];
                $members_names = [];
                foreach ($members as $member) {
                    $members_ids[] = $member->getId();
                    $members_names[] = $member->data['username'];
                }

                if (count($members_names) > 0) {
                    $search_data['member']['vendor'] = $members_names;
                }

                if ($this->checkPermission('catalog/product/view_group', false)) {
                    $members_ids = array_merge($members_ids, OSC::helper('adminGroup/common')->getMembersByGroup($this->getAccount()->getGroup()->getId(), true));
                    if (!in_array($this->getAccount()->getId(), $filter_value['member'])) {
                        $members_ids = array_filter($members_ids, function ($members_id) {
                            return $this->getAccount()->getId() != $members_id;
                        });
                    }
                }

                $search_data['member']['member_id'] = array_unique($members_ids);
            }

            $search_data['filter_value'] = $mapping_search_data;

            if (in_array('search_by_amazon' , $options)) {
                $search_data['flag_type'] = 1;
            } else {
                $search_data['flag_type'] = 0;
            }

            //Filter field
            if (!empty($filter_field)) {
                switch ($filter_field) {
                    case 'all':
                        $field = [];
                        break;
                    default:
                        $field = [$filter_field];
                        break;
                }

                $search_data['field'] = $field;
            }

            $collection
                ->register('search_keywords', $search['keywords'])
                ->register($this->_search_filter_field, $search['filter_field'])
                ->register('search_filter', $search['filter_value']);

            // TODO: Check memory
            $current_used_memory_usage = memory_get_usage();
            $current_allocated_memory_usage = memory_get_usage(true);

            if ($current_allocated_memory_usage >= $log_memory_flag) {
                OSC::helper('core/common')->insertHighMemoryLog(
                    $last_time_used_memory_usage,
                    $last_time_allocated_memory_usage,
                    $current_used_memory_usage,
                    $current_allocated_memory_usage,
                    'Line: ' . __LINE__ . ', before OSC::helper(\'catalog/search_product\')->getFilterProduct'
                );
            }

            return OSC::helper('catalog/search_product')->getFilterProduct($search_data, $page, $pageSize, $get_count);
        }
    }

    public function actionList() {
        // 512 Mb = 512 * 1024 * 1024
        $log_memory_flag = 536870912;

        $this->getTemplate()->addBreadcrumb(array('user', 'Manage Products'));

        $collection = OSC::model('catalog/product')->getCollection();
        $page = $this->_request->get('page', 1);
        $pageSize = 25;

        $search_collection = OSC::model('catalog/product')->getCollection();

        $search = OSC::sessionGet($this->_search_product_key);
        $is_search = $this->_request->get('search') && (!empty($search['keywords']) || !empty($search['filter_value']));

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
                'Line: ' . __LINE__ . ', after $is_search definition, before check $is_search'
            );
        }

        if ($is_search) {
            $result = $this->_applySearchProduct($collection, $page, $pageSize, true, ['search_by_default']);

            // TODO: Check memory
            $last_time_used_memory_usage = $current_used_memory_usage;
            $last_time_allocated_memory_usage = $current_allocated_memory_usage;
            $current_used_memory_usage = memory_get_usage();
            $current_allocated_memory_usage = memory_get_usage(true);

            if ($current_allocated_memory_usage >= $log_memory_flag) {
                OSC::helper('core/common')->insertHighMemoryLog(
                    $last_time_used_memory_usage,
                    $last_time_allocated_memory_usage,
                    $current_used_memory_usage,
                    $current_allocated_memory_usage,
                    'Line: ' . __LINE__ . ', after $this->_applySearchProduct, before check $result[\'list_id\']'
                );
            }

            if (isset($result['list_id']) && !empty($result['list_id'])) {
                $data = OSC::model('catalog/product')->getCollection()->load($result['list_id']);

                foreach ($result['list_id'] as $id) {
                    // TODO: Check memory
                    $last_time_used_memory_usage = $current_used_memory_usage;
                    $last_time_allocated_memory_usage = $current_allocated_memory_usage;
                    $current_used_memory_usage = memory_get_usage();
                    $current_allocated_memory_usage = memory_get_usage(true);

                    if ($current_allocated_memory_usage >= $log_memory_flag) {
                        OSC::helper('core/common')->insertHighMemoryLog(
                            $last_time_used_memory_usage,
                            $last_time_allocated_memory_usage,
                            $current_used_memory_usage,
                            $current_allocated_memory_usage,
                            'Line: ' . __LINE__ . ', after foreach $result[\'list_id\'], before $data->getItemByPK($id)'
                        );
                    }

                    try {
                        $item = $data->getItemByPK($id);
                        if (!empty($item)) {
                            $search_collection->addItem($data->getItemByPK($id));

                            // TODO: Check memory
                            $last_time_used_memory_usage = $current_used_memory_usage;
                            $last_time_allocated_memory_usage = $current_allocated_memory_usage;
                            $current_used_memory_usage = memory_get_usage();
                            $current_allocated_memory_usage = memory_get_usage(true);

                            if ($current_allocated_memory_usage >= $log_memory_flag) {
                                OSC::helper('core/common')->insertHighMemoryLog(
                                    $last_time_used_memory_usage,
                                    $last_time_allocated_memory_usage,
                                    $current_used_memory_usage,
                                    $current_allocated_memory_usage,
                                    'Line: ' . __LINE__ . ', after addItem to $search_collection'
                                );
                            }
                        }
                    } catch (Exception $exception) { }
                }
            }

            $search_page = $result['page'] ?? 0;
            $search_page_size = $result['page_size'] ?? 0;
            $search_total_item = $result['total_item'] ?? 0;
        }

        $collection
            ->addCondition('type_flag' , Model_Catalog_Product::TYPE_PRODUCT_DEFAULT, OSC_Database::OPERATOR_EQUAL)
            ->sort('product_id', OSC_Database::ORDER_DESC)
            ->setPageSize($pageSize)
            ->setCurrentPage($page);

        // TODO: Check memory
        $last_time_used_memory_usage = $current_used_memory_usage;
        $last_time_allocated_memory_usage = $current_allocated_memory_usage;
        $current_used_memory_usage = memory_get_usage();
        $current_allocated_memory_usage = memory_get_usage(true);

        if ($current_allocated_memory_usage >= $log_memory_flag) {
            OSC::helper('core/common')->insertHighMemoryLog(
                $last_time_used_memory_usage,
                $last_time_allocated_memory_usage,
                $current_used_memory_usage,
                $current_allocated_memory_usage,
                'Line: ' . __LINE__ . ', after check $is_search and add $collection condition'
            );
        }

        if (!$this->getAccount()->isAdmin() && !$is_search && !$this->checkPermission('catalog/product/view_all|catalog/product/full', false)) {
            $members_groups = OSC::helper('adminGroup/common')->getMembersGroup($this->getAccount()->getId(), false);
            $members_ids = [];
            $members_names = [];
            foreach ($members_groups as $member) {
                $members_ids[] = $member->getId();
                $members_names[] = $member->data['username'];
            }
            if ($this->checkPermission('catalog/product/view_group', false)) {
                $members_ids = array_merge($members_ids, OSC::helper('adminGroup/common')->getMembersByGroup($this->getAccount()->getGroup()->getId(), true));
            }
            $collection->addCondition('member_id', $members_ids, OSC_Database::OPERATOR_IN);
            $collection->addCondition('vendor', $members_names , OSC_Database::OPERATOR_IN, OSC_Database::RELATION_OR);
        }

        // TODO: Check memory
        $last_time_used_memory_usage = $current_used_memory_usage;
        $last_time_allocated_memory_usage = $current_allocated_memory_usage;
        $current_used_memory_usage = memory_get_usage();
        $current_allocated_memory_usage = memory_get_usage(true);

        if ($current_allocated_memory_usage >= $log_memory_flag) {
            OSC::helper('core/common')->insertHighMemoryLog(
                $last_time_used_memory_usage,
                $last_time_allocated_memory_usage,
                $current_used_memory_usage,
                $current_allocated_memory_usage,
                'Line: ' . __LINE__ . ', before pre load member $collection'
            );
        }

        $collection->load()->preLoadMember();

        // TODO: Check memory
        $last_time_used_memory_usage = $current_used_memory_usage;
        $last_time_allocated_memory_usage = $current_allocated_memory_usage;
        $current_used_memory_usage = memory_get_usage();
        $current_allocated_memory_usage = memory_get_usage(true);

        if ($current_allocated_memory_usage >= $log_memory_flag) {
            OSC::helper('core/common')->insertHighMemoryLog(
                $last_time_used_memory_usage,
                $last_time_allocated_memory_usage,
                $current_used_memory_usage,
                $current_allocated_memory_usage,
                'Line: ' . __LINE__ . ', after pre load member $collection'
            );
        }

        static::setLastListUrl();

        //Search product form
        $default_select_field = OSC::cookieGet($this->_default_search_field_key);
        if (empty($default_select_field)) {
            $default_select_field = array_key_first($this->_filter_field);
            OSC::cookieSet($this->_default_search_field_key, $default_select_field);
        }
        //End search product form

        $product_collection = $is_search ? $search_collection : $collection;

        $design_ids = $product_collection->getDesignIdByProductList();

        // TODO: Check memory
        $last_time_used_memory_usage = $current_used_memory_usage;
        $last_time_allocated_memory_usage = $current_allocated_memory_usage;
        $current_used_memory_usage = memory_get_usage();
        $current_allocated_memory_usage = memory_get_usage(true);

        if ($current_allocated_memory_usage >= $log_memory_flag) {
            OSC::helper('core/common')->insertHighMemoryLog(
                $last_time_used_memory_usage,
                $last_time_allocated_memory_usage,
                $current_used_memory_usage,
                $current_allocated_memory_usage,
                'Line: ' . __LINE__ . ', after $product_collection->getDesignIdByProductList()'
            );
        }

        $this->output(
            $this->getTemplate()->build(
                'catalog/product/list', [
                    'collection' => $product_collection,
                    'page' => $is_search ? $search_page : $collection->getCurrentPage(),
                    'page_size' => $is_search ? $search_page_size : $collection->getPageSize(),
                    'total_item' => $is_search ? $search_total_item : $collection->collectionLength(),
                    'search_keywords' => $collection->registry('search_keywords'),
                    'data_sold' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0) ? OSC::sessionGet($this->_search_product_sold_key) : null,
                    'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                    'filter_config' => $this->_getFilterConfig($collection->registry('search_filter')),
                    'filter_field' => $this->_filter_field,
                    'selected_filter_field' => $collection->registry($this->_search_filter_field),
                    'default_search_field_key' => $this->_default_search_field_key,
                    'campaign_type_flag' => 'default',
                    'design_ids' => $design_ids
                ]
            )
        );
    }

    public function actionExport() {
        die(); //Remove legacy code
    }

    public function actionImport() {
        die(); //Remove legacy code
    }

    public function actionExportDataSEO() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/exportDataSEO');

        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');

            $export_condition = $this->_request->get('export_condition');
            $colums = $this->_request->get('columns');

            if (!in_array($export_condition, ['all', 'search', 'selected'])) {
                throw new Exception('Please select condition to export');
            }

            $collection = OSC::model('catalog/product')->getCollection();

            if ($export_condition == 'selected') {
                $selected_ids = $this->_request->get('selected_ids');

                if (!is_array($selected_ids)) {
                    throw new Exception('Please select least a product to export');
                }

                $selected_ids = array_map(function($product_id) {
                    return intval($product_id);
                }, $selected_ids);
                $selected_ids = array_filter($selected_ids, function($product_id) {
                    return $product_id > 0;
                });

                if (count($selected_ids) < 1) {
                    throw new Exception('Please select least a product to export');
                }

                $collection->addCondition($collection->getPkFieldName(), array_unique($selected_ids), OSC_Database::OPERATOR_FIND_IN_SET);
            } else if ($export_condition == 'search') {
                $this->_applyListCondition($collection);
            }

            $collection->sort('product_id', OSC_Database::ORDER_ASC)->load();

            if ($collection->length() < 1) {
                throw new Exception('No product was found to export');
            }

            $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers_column = [];
            foreach ($colums as $key => $item) {
                foreach ($item as $key2 => $value) {
                    $headers_column[$key2] = $value;
                }
            }

            $headers = array_values($headers_column);

            foreach ($headers as $i => $title) {
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . 1, $title);
            }

            $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . 1)->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('89B7E5');
            $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . 1)->getFont()->getColor()->setARGB('FFFFFF');
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setWidth(20);

            /* @var $product Model_Catalog_Product */
            /* @var $variant Model_Catalog_Product_Variant */
            /* @var $image Model_Catalog_Product_Image */

            $sheet_row_index = 2;

            foreach ($collection as $product) {
                $push_product_info = true;

                $row_index_seo_tags = 2;

                $arr_seo_tags = [];

                if($product->data['seo_tags']) {
                    foreach ($product->data['seo_tags'] as $key => $tag) {
                        $data_insert = $this->_getDataExport($push_product_info, $product, $tag);
                        $row_data = array_values(array_intersect_key ($data_insert, $headers_column));

                        foreach ($row_data as $i => $value) {
                            if(!empty($value)){
                                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . $sheet_row_index, $value);
                            }
                        }

                        $push_product_info = false;

                        if(count($product->data['seo_tags']) > 0 && $row_index_seo_tags <= count($product->data['seo_tags'])){
                            $row_index_seo_tags++;
                            $sheet_row_index++;
                        }

                    }
                } else {
                    $data_insert = $this->_getDataExport($push_product_info, $product);
                    $row_data = array_values(array_intersect_key ($data_insert, $headers_column));
                    foreach ($row_data as $i => $value) {
                        if(!empty($value)){
                            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . $sheet_row_index, $value);
                        }
                    }

                    $push_product_info = false;
                }

                if(array_intersect_key ($data_insert, $headers_column)['variant_id']){
                    $sheet_row_index++;
                }

                if(!array_intersect_key ($data_insert, $headers_column)['variant_id']){
                    $sheet_row_index++;
                }
            }

            $file_name = 'export/catalog/product/' . $export_condition . '.' . OSC::makeUniqid() . '.' . date('d-m-Y') . '.xlsx';
            $file_path = OSC_Storage::preDirForSaveFile($file_name);

            $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $writer->save($file_path);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['url' => OSC_Storage::tmpGetFileUrl($file_name)]);
    }

    public function actionImportDataSEO() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/importDataSEO');

        try {
            $uploader = new OSC_Uploader();

            if ($uploader->getExtension() != 'xlsx') {
                throw new Exception(strtoupper($uploader->getExtension()) . ' is not allowed to upload');
            }

            $file_name = 'import/catalog/product/.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . time() . '.' . $uploader->getExtension();

            $file_path = OSC_Storage::preDirForSaveFile($file_name);

            $uploader->save($file_path, true);

            $sheet_data = PhpOffice\PhpSpreadsheet\IOFactory::load($file_path)->getActiveSheet()->toArray(null, true, true, true);

            $header = array_shift($sheet_data);
            $header = array_map(function($title) {;
                return preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($title));
            }, $header);
            $map_idx = [];

            foreach ($header as $column => $title) {
                $map_idx[$title] = $column;
            }

            $products = [];
            $product_id = null;

            $sheet_header_column = ['product_id', 'url', 'topic', 'title', 'description', 'meta_title', 'meta_slug', 'meta_description', 'seo_tag_collection_id', 'seo_tag_collection_title', 'seo_status'];
            $sheet_header_invalid = array_filter(array_diff(array_keys($map_idx), $sheet_header_column));

            if(count($sheet_header_invalid) > 0) {
                throw new Exception('Invalid columns:'. implode(", ", $sheet_header_invalid));
            }

            foreach ($sheet_data as $sheet_row) {
                foreach ($sheet_row as $idx => $value) {
                    $sheet_row[$idx] = trim(strval($value));
                }

                if ($sheet_row[$map_idx['product_id']]) {
                    $product_id = $sheet_row[$map_idx['product_id']] ? $sheet_row[$map_idx['product_id']] : OSC::makeUniqid('new');

                    $products[$product_id] = [
                        'data' => [],
                    ];

                    foreach (['title','topic', 'description',] as $key) {
                        if ($sheet_row[$map_idx[$key]] !== '') {
                            $products[$product_id]['data'][$key] = $sheet_row[$map_idx[$key]];
                        }
                    }

                    $sheet_row[$map_idx['tags']] = explode(',', $sheet_row[$map_idx['tags']]);
                    $sheet_row[$map_idx['tags']] = array_map(function($tag) {
                        return trim($tag);
                    }, $sheet_row[$map_idx['tags']]);
                    $sheet_row[$map_idx['tags']] = array_filter($sheet_row[$map_idx['tags']], function($tag) {
                        return $tag !== '';
                    });

                    if (count($sheet_row[$map_idx['tags']]) > 0) {
                        $products[$product_id]['data']['tags'] = $sheet_row[$map_idx['tags']];
                    }

                    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $sheet_row[$map_idx['meta_slug']['slug']]) && trim($sheet_row[$map_idx['meta_slug']['slug']]) != '') {
                        throw new Exception('Slug is invalid: '.$sheet_row[$map_idx['meta_slug']['slug']]);
                    }

                    foreach (['meta_title', 'meta_slug', 'meta_keywords', 'meta_description'] as $key) {
                        if ($sheet_row[$map_idx[$key]] !== '') {
                            $products[$product_id]['data']['meta_tags'][substr($key, 5)] = $sheet_row[$map_idx[$key]];
                        }
                    }

                    if (!is_numeric($sheet_row[$map_idx['seo_status']]) || intval($sheet_row[$map_idx['seo_status']]) < 0 || intval($sheet_row[$map_idx['seo_status']]) > 1){
                        throw new Exception('SEO Status is invalid: '.$sheet_row[$map_idx['seo_status']].' (Valid value are 0 or 1)');
                    }

                    $products[$product_id]['data']['seo_status'] = $sheet_row[$map_idx['seo_status']];

                }

                if (!$product_id) {
                    continue;
                }

                $seo_tags = [];
                foreach (['seo_tag_collection_id'=>'0', 'seo_tag_collection_title'=>''] as $key => $item) {
                    $seo_tags[substr($key, 8)] = $sheet_row[$map_idx[$key]] ?: $item;
                }

                $seo_tags['collection_slug'] =  $seo_tags['collection_title'] ? $this->__createSlug($seo_tags['collection_title']) : '';

                $products[$product_id]['data']['seo_tags'][]=$seo_tags;
            }

            if (count($products) < 1) {
                throw new Exception('No product was found to import');
            }

            if (!OSC::writeToFile($file_path, OSC::encode($products))) {
                throw new Exception('Cannot write product data to file');
            }

            $this->_ajaxResponse(['file' => $file_name]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    private function __createSlug($str, $delimiter = '-') {
        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;
    }

    public function actionImportProcessDataSEO() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/importDataSEO');

        try {
            $file = $this->_request->get('file');

            $tmp_file_path = OSC_Storage::tmpGetFilePath($file);

            if (!$tmp_file_path) {
                throw new Exception('File is not exists or removed');
            }

            $JSON = OSC::decode(file_get_contents($tmp_file_path), true);

            $errors = [];
            $success = 0;

            foreach ($JSON as $product_id => $product_info) {
                $queue_data = [];

                $product = OSC::model('catalog/product');

                if ($product_id < 1) {
                    throw new Exception('Product '.$product_id.' is not exist');
                }

                try {
                    $product->load($product_id);

                    $product_info['data']['product_id'] = $product->getId();
                } catch (Exception $ex) {
                    if ($ex->getCode() != 404) {
                        throw new Exception($ex->getMessage());
                    }
                }

                $queue_data['product'] = $product_info['data'];

                try {
                    OSC::model('catalog/product_bulkQueue')->setData([
                        'ukey' => 'import/' . md5(OSC::encode($queue_data)),
                        'member_id' => $this->getAccount()->getId(),
                        'action' => 'importDataSEO',
                        'queue_data' => $queue_data
                    ])->save();

                    $success++;
                } catch (Exception $ex) {
                    if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                        $success++;
                    } else {
                        $errors[] = $product->getId();
                    }
                }
            }

            if ($success < 1) {
                throw new Exception('Cannot add products to import queue');
            }

            OSC::core('cron')->addQueue('catalog/product_bulk_importDataSEO', null, ['requeue_limit' => -1]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        @unlink($tmp_file_path);

        $this->_ajaxResponse(['message' => 'Bulk import task has appended to queue' . (count($errors) < 1 ? '' : (' with ' . $success . ' products and ' . count($errors) . " errors. Products below cannot add to import queue:\n" . implode(', ', $errors)))]);
    }

    private function _getDataExport($push_product_info, $product, $seo_tag = []){
        $data_insert = [
            "product_id" => $push_product_info ? $product->getId() : '',
            "url" => $push_product_info ? $product->getDetailUrl() : '',
            "topic" => $push_product_info ? $product->data['topic'] : '',
            "title" => $push_product_info ?  $product->data['title'] : '',
            "product_type" => $push_product_info ? $product->data['product_type'] : '',
            "vendor" => $push_product_info ? $product->data['vendor'] : '',
            "description" => $push_product_info ? $product->data['description'] : '',
            "content" => $push_product_info ? $product->data['content'] : '',
            "tags" => $push_product_info ? implode(', ', $product->data['tags']) : '',
            "meta_title" => $push_product_info ? $product->data['meta_tags']['title']: '',
            "meta_slug" => $push_product_info ? $product->data['slug'] : '',
            "meta_keywords" => $push_product_info ? $product->data['meta_tags']['keywords'] : '',
            "meta_description" => $push_product_info ? $product->data['meta_tags']['description'] : '',
            "seo_tag_collection_id" => 0,
            "seo_tag_collection_title" => '',
            "seo_status" => $push_product_info ? intval($product->data['seo_status']) : '',
        ];

        if($product->data['seo_tags']){
            $data_insert['seo_tag_collection_id'] = $seo_tag['collection_id'] ? $seo_tag['collection_id'] : 0;
            $data_insert['seo_tag_collection_title'] = $seo_tag['collection_title'] ? $seo_tag['collection_title'] : '';
        }

        return $data_insert;
    }

    public function actionInventoryImportUpload() {
        die(); //Remove legacy code
    }

    public function actionImageUpload() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/edit|catalog/product/add');

        try {
            $uploader = new OSC_Uploader();

            $tmp_file_path = OSC::getTmpDir() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $uploader->getExtension();

            try {
                $uploader->save($tmp_file_path, true);
                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }
        } catch (Exception $ex) {
            if ($ex->getCode() === 500) {
                $this->_ajaxError($ex->getMessage());
            }

            $image_url = trim(strval($this->_request->decodeValue($this->_request->get('image_url'))));

            try {
                if (!$image_url) {
                    throw new Exception($this->_('core.err_data_incorrect'));
                }

                $tmp_file_path = OSC::getTmpDir() . '/' . md5($image_url);

                if (!file_exists($tmp_file_path)) {
                    $url_info = OSC::core('network')->curl($image_url, array('browser'));

                    if (!$url_info['content']) {
                        throw new Exception($this->_('core.err_data_incorrect'));
                    }

                    if (OSC::writeToFile($tmp_file_path, $url_info['content']) === false) {
                        throw new Exception($this->_('core.err_tmp_save_failed'));
                    }
                }

                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }
        }

        $file_name = '';
        $tmp_url = '';
        $width = 0;
        $height = 0;

        try {
            $img_processor = new OSC_Image();
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path)->resize(3000);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $file_name = 'product.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

            $tmp_url = OSC::core('aws_s3')->tmpSaveFile($img_processor, $file_name);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse([
            'file' => $file_name,
            'url' => $tmp_url,
            'width' => $width,
            'height' => $height
        ]);
    }

    /**
     *
     * @param Model_Catalog_Product $product
     * @param array $images
     * @return array
     * @throws OSC_Exception_Runtime
     */
    protected function _processPostImages($product, $images) {
        $counter = 0;

        foreach ($images as $image_id => $image_alt) {
            $images[$image_id] = [
                'position' => ++$counter,
                'alt' => $image_alt
            ];
        }

        $image_collection = $product->getImages();

        $image_map = array();

        foreach ($image_collection as $image_model) {
            $image_id = $image_model->getId();

            if (!isset($images[$image_id])) {
                try {
                    $image_collection->removeItemByKey($image_id);
                    $image_model->delete();
                } catch (Exception $ex) {
                    $this->addErrorMessage($ex->getMessage());
                }
            } else {
                $image_new_data = $images[$image_id];

                if ($image_model->data['alt'] != $image_new_data['alt'] ||
                    $image_model->data['position'] != $image_new_data['position']
                ) {
                    try {
                        $image_model->setData([
                            'alt' => $image_new_data['alt'],
                            'position' => $image_new_data['position']
                        ])->save();
                    } catch (Exception $ex) {
                        $this->addErrorMessage($ex->getMessage());
                    }
                }

                $image_map[(string) $image_id] = $image_id;
                unset($images[$image_id]);
            }
        }

        foreach ($images as $image_tmp_name => $image_data) {
            $image_tmp_name_s3 = OSC::core('aws_s3')->getTmpFilePath($image_tmp_name);
            if (!OSC::core('aws_s3')->doesObjectExist($image_tmp_name_s3)) {
                continue;
            }

            $filename = 'product/' . $product->getId() . '/' . str_replace('product.', '', $image_tmp_name);
            $filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);

            try {
                OSC::core('aws_s3')->copy($image_tmp_name_s3, $filename_s3);
            } catch (Exception $ex) {
                continue;
            }

            $image_model = $image_collection->getNullModel();

            try {
                $image_model->setData([
                    'product_id' => $product->getId(),
                    'alt' => $image_data['alt'],
                    'position' => $image_data['position'],
                    'filename' => $filename
                ])->save();

                $image_collection->addItem($image_model);

                $image_map[$image_tmp_name] = $image_model->getId();
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        return $image_map;
    }

    protected function _processPostVideos($product, $videos, &$variants) {
        $post_videos = OSC::decode($videos);
        $new_video_ids = [];
        $old_videos = $product->getVideos();
        $old_video_keys = $old_videos->getKeys();

        foreach ($post_videos as $key => $video) {
            $video_id = intval($video['video_id']);

            if (!$video_id) {
                if (!OSC_Storage::tmpUrlIsExists($video['url'])) {
                    $this->_ajaxError('Video url error ' . $video['url']);
                }

                if ($video['thumbnail'] && !OSC_Storage::tmpUrlIsExists($video['thumbnail'])) {
                    $this->_ajaxError('Video thumbnail url error ' . $video['thumbnail']);
                }

                $video_file_extension = preg_replace('/^.*(\.[a-zA-Z0-9]+)$/', '\\1', $video['url']);
                $video_file_extension = strtolower($video_file_extension);

                $video_file_name = 'catalog/mockup_video/' . $product->getId() . '/' . md5($video['url']) . $video_file_extension;
                $video_file_name_s3 = OSC::core('aws_s3')->getStoragePath($video_file_name);

                $video_tmp_file_name = OSC::core('aws_s3')->tmpGetFileNameFromUrl($video['url']);
                $video_tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($video_tmp_file_name);

                if (!OSC::core('aws_s3')->doesObjectExist($video_file_name_s3)) {
                    OSC::core('aws_s3')->copy($video_tmp_file_name_s3, $video_file_name_s3);
                }

                $thumbnail_file_name = '';

                if ($video['thumbnail']) {
                    $thumbnail_file_name = 'catalog/mockup_thumbnail/' . $product->getId() . '/' . md5($video['thumbnail']) . '.png';
                    $thumbnail_file_name_s3 = OSC::core('aws_s3')->getStoragePath($thumbnail_file_name);

                    $thumbnail_tmp_file_name = OSC::core('aws_s3')->tmpGetFileNameFromUrl($video['thumbnail']);
                    $thumbnail_tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($thumbnail_tmp_file_name);

                    if (!OSC::core('aws_s3')->doesObjectExist($thumbnail_file_name_s3)) {
                        OSC::core('aws_s3')->copy($thumbnail_tmp_file_name_s3, $thumbnail_file_name_s3);
                    }
                }

                try {
                    $video_ukey = $product->getId() . '_' . md5($video_file_name);
                    $model = OSC::model('catalog/product_image')->loadByUKey($video_ukey);
                } catch (Exception $ex) {
                    if ($ex->getCode() == 400) {
                        $this->_ajaxError($ex->getMessage());
                    }

                    $model = OSC::model('catalog/product_image')->setData([
                        'product_id' => $product->getId(),
                        'width' => 0,
                        'height' => 0,
                        'duration' => intval($video['duration']),
                        'ukey' => $video_ukey,
                        'position' => intval($key),
                        'flag_main' => 0,
                        'alt' => '',
                        'filename' => $video_file_name,
                        'thumbnail' => $thumbnail_file_name,
                        'is_static_mockup' => 3,
                    ])->save();
                }

                $video_id = $model->getId();
            } else {
                // Check & Update thumbnail for existing video
                try {
                    $model = OSC::model('catalog/product_image')->load($video_id);

                    $old_thumbnail = $model->getS3Thumbnail();

                    if ($old_thumbnail != $video['thumbnail']) {
                        // Update new video thumbnail
                        $thumbnail_file_name = '';

                        if ($video['thumbnail']) {
                            try {
                                if (!OSC_Storage::tmpUrlIsExists($video['thumbnail'])) {
                                    throw new Exception('Video thumbnail url error ' . $video['thumbnail']);
                                }

                                $thumbnail_file_name = 'catalog/mockup_thumbnail/' . $product->getId() . '/' . md5($video['thumbnail']) . '.png';
                                $thumbnail_file_name_s3 = OSC::core('aws_s3')->getStoragePath($thumbnail_file_name);

                                $thumbnail_tmp_file_name = OSC::core('aws_s3')->tmpGetFileNameFromUrl($video['thumbnail']);
                                $thumbnail_tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($thumbnail_tmp_file_name);

                                if (!OSC::core('aws_s3')->doesObjectExist($thumbnail_file_name_s3)) {
                                    OSC::core('aws_s3')->copy($thumbnail_tmp_file_name_s3, $thumbnail_file_name_s3);
                                }
                            } catch (Exception $ex) {
                                throw new Exception($ex->getMessage());
                            }
                        }

                        $model->setData([
                            'thumbnail' => $thumbnail_file_name,
                        ])->save();
                    }
                } catch (Exception $ex) {
                    $this->_ajaxError($ex->getMessage());
                }
            }

            $new_video_ids[] = $video_id;

            // update video ids in variants
            if ($video['file_id'] && !intval($video['file_id'])) {
                foreach ($variants as $variant_idx => $variant) {
                    $variant_video_ids = is_array($variant['video_id'])
                        ? $variant['video_id']
                        : explode(',', $variant['video_id'] ?? '');
                    foreach ($variant_video_ids as $idx => $variant_video_id) {
                        if ($variant_video_id === $video['file_id']) {
                            $variant_video_ids[$idx] = $video_id;
                        }
                    }

                    $variants[$variant_idx]['video_id'] = $variant_video_ids;
                }
            }
        }

        // update video postions in variants
        foreach ($variants as $variant_idx => $variant) {
            $variant_video_ids = is_array($variant['video_id'])
                ? $variant['video_id']
                : explode(',', $variant['video_id'] ?? '');
            $variant_video_positions = is_array($variant['video_position'])
                ? $variant['video_position']
                : explode(',', $variant['video_position'] ?? '');

            if (!is_array($variants[$variant_idx]['video_position'])) {
                $variants[$variant_idx]['video_position'] = [];
            }
            foreach ($variant_video_ids as $idx => $variant_video_id) {
                if (!empty($variant_video_id)) {
                    $variants[$variant_idx]['video_position'][$variant_video_id] = $variant_video_positions[$idx];
                }
            }
        }

        $remove_video_ids = array_diff($old_video_keys, $new_video_ids);

        if (count($remove_video_ids)) {
            $video_collection = OSC::model('catalog/product_image')->getCollection()->load($remove_video_ids);

            foreach ($video_collection as $video) {
                $video->delete();
            }
        }
    }

    public function actionPost() {
        $id = intval($this->_request->get('id'));

        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/' . ($id > 0 ? 'edit' : 'add'));

        /* @var $model Model_Catalog_Product */
        $model = OSC::model('catalog/product');

        if ($id > 0) {
            $this->getTemplate()->addBreadcrumb(array('user', 'Edit Product'));
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Product is not exist' : $ex->getMessage());

                static::redirectLastListUrl($this->getUrl('list'));
            }

            $is_reset_cache = intval($this->_request->get('is_reset_cache'));

            if ($is_reset_cache) {
                try {
                    OSC::helper('core/cache')->deleteByPattern([$model->getUkey()]);
                    $this->addMessage('Reset cache '. 'product #' . $model->getId() . ' success');
                } catch (Exception $ex) { }
            }

            if ($model->isCampaignMode()) {
                static::redirect($this->getUrl('catalog/backend_campaign/post', ['id' => $model->getId()]));
            }
        } else {
            $this->getTemplate()->addBreadcrumb(array('user', 'Create Product'));
        }

        if ($model->getId() > 0 && $model->checkMasterLock()) {
            $this->addErrorMessage('You do not have the right to perform this function');
            static::redirect($this->getUrl('*/*/list'));
        }

        $list_product_tags = OSC::helper('filter/common')->getListTagSettingProduct();

        $product_tag_selected =  $model->getProductTagSelected();

        $list_member_display_live_preview = OSC::helper('core/setting')->get('catalog/product/live_preview_members_vendor');
        $list_member_display_live_preview = OSC::model('user/member')
            ->getCollection()
            ->addField('username')
            ->addCondition('member_id', $list_member_display_live_preview, OSC_Database::OPERATOR_IN)
            ->load()
            ->toArray();

        if ($this->_request->get('action', null) == 'post_form' ) {
            try {
                $variants = $this->_request->get('variants');

                if (!is_array($variants) || count($variants) < 1) {
                    throw new Exception('Bạn phải tạo options, make variants trước khi submit');
                }
                $data = [];

                $title = $this->_request->get('title');
                $data['title'] = trim($title);
                $data['description'] = $this->_request->getRaw('description');
                $data['position_index'] = $this->_request->get('position_index');
                $data['vendor'] = $this->_request->get('vendor');
                $data['options'] = $this->_request->get('options');


                $data['topic'] = $this->_request->get('topic', null) ?? Model_Catalog_Product::TOPIC_SEMITEST_DEFAULT;

                // get SEO PRODUCT to save meta_data
                $seo_title = trim($this->_request->get('seo-title'));
                $seo_slug = trim($this->_request->get('seo_slug'), '- ');
                $seo_description = trim($this->_request->get('seo-description'));
                $seo_keyword = trim($this->_request->get('seo-keyword'));
                $seo_image = $this->_request->get('seo-image');

                $data['meta_tags'] = [
                    'title' => $seo_title,
                    'description' => $seo_description,
                    'keywords' => $seo_keyword,
                    'image' => $seo_image
                ];
                if (!$seo_slug){
                    $seo_slug = OSC::core('string')->cleanAliasKey($title);
                }

                if (isset($model->data['meta_tags']['is_clone']) && intval($model->data['meta_tags']['is_clone']) > 0) {
                    $data['meta_tags']['is_clone'] = $model->data['meta_tags']['is_clone'];
                    if (($data['topic'] != $model->data['topic']) || ($data['title'] != $model->data['title'])) {
                        $seo_slug = OSC::core('string')->cleanAliasKey($data['title']);
                        unset($data['meta_tags']['is_clone']);
                    }
                }

                $data['slug'] = $seo_slug;

                $meta_image = OSC::helper('backend/backend/images/common')->saveMetaImage($data['meta_tags']['image'], $model->data['meta_tags']['image'], 'meta/product/', 'product');

                if ($meta_image['data_meta_image']) {
                    $data['meta_tags']['image'] = $meta_image['data_meta_image'];
                }

                $data['meta_data'] = ($id > 0 && is_array($model->data['meta_data'])) ? $model->data['meta_data'] : [];

                $data['meta_data']['is_disable_preview'] = intval($this->_request->get('is_disable_preview', 0));

                $images = $this->_request->get('images');

                if (!is_array($images)) {
                    $images = [];
                }

                $data['personalized_form_detail'] = 'default';
                if (in_array($data['vendor'], array_column($list_member_display_live_preview, 'username'))) {
                    $data['personalized_form_detail'] = intval($this->_request->get('show_product_detail_type', 0)) == 1 ? 'live_preview' : 'default';
                }

                if ($id < 1) {
                    $data['member_id'] = OSC::helper('user/authentication')->getMember()->getId();
                    $data['selling_type'] = Model_Catalog_Product::TYPE_SEMITEST;
                }

                if($id >= 1){
                    $data['seo_status'] = intval($this->_request->get('seo_status')) === 1 ? 1 : 0;
                }

                $product_tag_ids = $this->_request->get('product_tags');

                $data['addon_service_data'] = OSC::helper('addon/service')->verifyAddonServiceData(
                    $this->_request->get('addon_service_data'),
                    (bool)($this->_request->get('addon_service_enable'))
                );

                $model->setData($data);

                OSC::helper('filter/common')->verifyTagProductRel($product_tag_ids);

                $product_tag_selected = $this->_getProductTagSelected($product_tag_ids);

                OSC::core('observer')->dispatchEvent('catalog/product/postFrmSaveData', ['model' => $model]);

                $model->save();

                OSC::helper('filter/tagProductRel')->saveTagProductRel($product_tag_ids, $model, $this->getAccount()->getId());

                OSC::core('cron')->addQueue('catalog/product_updateBetaOrder', ['product_id' => $model->getId()], ['ukey' => 'catalog/product_updateBetaOrder', 'requeue_limit' => -1, 'estimate_time' => 60]);

                if ($meta_image['image_to_rm'] != null) {
                    $meta_image_path_to_rm_s3 = OSC::core('aws_s3')->getStoragePath($meta_image['image_to_rm']);
                    OSC::core('aws_s3')->delete($meta_image_path_to_rm_s3);
                }
                $image_map = $this->_processPostImages($model, $images);
                $this->_processPostVideos($model, $this->_request->get('videos'), $variants);

                $model->reload();

                // Update, Create variant when created product update product and not have options to created variants
                try {
                    OSC::helper('catalog/product')->processPostVariants($model, $variants, $image_map);
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }

                if ($id > 0) {
                    $message = 'Product #' . $model->getId() . ' đã được cập nhật';
                } else {
                    $message = 'Product [#' . $model->getId() . '] "' . $model->getProductTitle() . '" added';
                }

                $default_variant = $model->getVariants(true)->getItem();

                if ($default_variant && ($default_variant->data['price'] != $model->data['price'] || $default_variant->data['compare_at_price'] != $model->data['compare_at_price'])) {
                    try {
                        $model->setData([
                            'price' => $default_variant->data['price'],
                            'compare_at_price' => $default_variant->data['compare_at_price']
                        ])->save();
                    } catch (Exception $ex) {

                    }
                }

                $this->addMessage($message);

                if (!$this->_request->get('continue')) {
                    static::redirectLastListUrl($this->getUrl('list'));
                } else {
                    static::redirect($this->getUrl(null, ['id' => $model->getId()]));
                }
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }
        $post_form_columns = [
            'main' => [],
            'sidebar' => []
        ];

        OSC::core('observer')->dispatchEvent('catalog/product/postFrmRender', [
            'columns' => &$post_form_columns,
            'model' => $model
        ]);

        if (!is_array($post_form_columns)) {
            $post_form_columns = [];
        }

        if (!isset($post_form_columns['main']) || !is_array($post_form_columns['main'])) {
            $post_form_columns['main'] = [];
        }

        if (!isset($post_form_columns['sidebar']) || !is_array($post_form_columns['sidebar'])) {
            $post_form_columns['sidebar'] = [];
        }

        $list_vendors = OSC::model('user/member')->getListMemberIdeaResearch();

        if (!($list_vendors instanceof Model_User_Member_Collection)) {
            $list_vendors = [];
        }

        $data_variants = $model->getDataVariantsSemitest();

        $print_beta_ids = [];
        $design_ids = [];

        foreach ($data_variants as $key => $variant) {
            foreach ($variant['meta_data']['variant_config'] as $variant_key => $config) {
                foreach ($config['print_template_config'] as $config_key => $print_config) {
                    $print_beta_ids[$variant['id']][] = $print_config['print_template_beta_id'];
                    foreach ($data_variants[$key]['meta_data']['variant_config'][$variant_key]['print_template_config'][$config_key]['segments']['source'] as $source_key => $source) {
                        $design_ids[$source['design_id']] = $source['design_id'];
                    }
                }
            }
        }

        $print_template_beta_data = $design_data = [];

        foreach ($print_beta_ids as $variant_id => $print_ids) {
            $print_template_beta = OSC::model('catalog/printTemplate_beta')->getCollection()->load($print_ids);
            foreach ($print_template_beta as $key => $print) {
                $print_template_beta_data[$print->data['id']] = [
                    "id" => $print->data['id'],
                    "img" => OSC::core('aws_s3')->getStorageUrl($print->data['config']['print_file']['print_file_url_thumb']),
                    "dimension" => $print->data['config']['print_file']['dimension']
                ];
            }
        }

        $design_beta = OSC::model('personalizedDesign/design')->getCollection()->load($design_ids);

        foreach ($design_beta as $design) {
            $design_data[$design->data['design_id']] = $design;
        }

        foreach ($data_variants as $key => $variant) {
            foreach ($variant['meta_data']['variant_config'] as $variant_key => $config) {
                foreach ($config['print_template_config'] as $config_key => $print_config) {
                    if ($print_template_beta_data[$print_config['print_template_beta_id']]) {
                        foreach ($data_variants[$key]['meta_data']['variant_config'][$variant_key]['print_template_config'][$config_key]['segments']['source'] as $source_key => $source) {
                            $data_variants[$key]['meta_data']['variant_config'][$variant_key]['print_template_config'][$config_key]['segments']['source'][$source_key]['svg_content'] = OSC::helper('personalizedDesign/common')->renderSvg($design_data[$source['design_id']], [], ['original_render', 'render_design']);
                        }
                        $data_variants[$key]['meta_data']['variant_config'][$variant_key]['print_template_config'][$config_key]['print_template_beta'] = $print_template_beta_data[$print_config['print_template_beta_id']];
                        unset($data_variants[$key]['meta_data']['variant_config'][$variant_key]['print_template_config'][$config_key]['print_template_beta_id']);
                    }
                }
            }
        }

        $videos = array_map(function($video) {
            return [
                'id' => $video['id'],
                'url' => $video['url'],
                'thumbnail' => $video['thumbnail'],
            ];
        }, $model->getVideos()->toArray());

        $max_video_size = OSC::helper('core/setting')->get('catalog/video_config/max_file_size');

        $addon_list = OSC::helper('addon/service')->getAddonServiceList([]);

        $addon_list = array_filter($addon_list, function($addon) {
            return !$addon['auto_apply_for_product_type_variants'];
        });

        $addon_services = OSC::helper('addon/service')->formatData($model->data['addon_service_data']);

        $available_addon_ids = array_column($addon_list, 'id');

        $addon_services = array_map(function($addon) use ($available_addon_ids) {
            if (!in_array($addon['addon_service_id'], $available_addon_ids)) {
                $addon['not_available'] = 1;
            }
            return $addon;
        }, $addon_services);

        $output_html = $this->getTemplate()->build('catalog/product/postForm', [
            'form_title' => $model->getId() > 0 ? ('Edit product #' . $model->getId() . ': ' . $model->getData('title', true)) : 'Add new product',
            'model' => $model,
            'videos' => $videos,
            'max_video_size' => $max_video_size ?? 0,
            'data_variants' => $data_variants,
            'list_vendors' => $list_vendors,
            'list_member_display_live_preview' => $list_member_display_live_preview,
            'post_form_columns' => $post_form_columns,
            'data_request_images' => $this->_request->get('images'),
            'data_request_variants' => $this->_request->get('variants') ?? [],
            'option_types' => OSC::helper('catalog/product')->collectOptionTypes(),
            'addon_list' => $addon_list,
            'addon_services' => $addon_services,
            'list_product_tags' => $list_product_tags,
            'list_product_tags_selected' => $product_tag_selected
        ]);

        $this->output($output_html);
    }

    public function actionGetSupplierForPrintConfigBeta() {
        $supplier_model = OSC::model('catalog/supplier')->getCollection()->addField('title', 'ukey')->load();

        $role_edit_supplier = $this->checkPermission('catalog/super|catalog/product/full|catalog/product/semitest/print_template_config/edit_supplier', false);

        $this->_ajaxResponse(
            [
                'supplier' => $supplier_model->getItems(),
                'role_edit_supplier' => $role_edit_supplier
            ]
        );
    }

    private function _getProductTagSelected($product_tag_ids) {
        $product_tag_selected = [];

        foreach ($product_tag_ids as $key => $product_tag_id) {
            $product_tag_selected[$product_tag_id] = $product_tag_id;
        }

        return $product_tag_selected;
    }

    public function actionGetProductTypes() {
        $collection = OSC::model('catalog/product')->getCollection();

        try {
            $collection->load();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $product_types = array();

        foreach ($collection as $model) {
            $product_type = explode(',', $model->data['product_type']);

            foreach ($product_type as $entry) {
                $entry = trim($entry);

                if (strlen($entry) < 1) {
                    continue;
                }

                if (!isset($product_types[$entry])) {
                    $product_types[$entry] = 0;
                }

                $product_types[$entry]++;
            }
        }

        $this->_ajaxResponse(array_keys($product_types));
    }

    public function actionGetProductVendors() {
        $collection = OSC::model('catalog/product')->getCollection();

        try {
            $collection->load();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $vendors = array();

        foreach ($collection as $model) {
            $vendor = trim($model->data['vendor']);

            if (strlen($vendor) < 1) {
                continue;
            }

            if (!isset($vendors[$vendor])) {
                $vendors[$vendor] = 0;
            }

            $vendors[$vendor]++;
        }

        $this->_ajaxResponse(array_keys($vendors));
    }

    public function actionGetProductTags() {
        $collection = OSC::model('catalog/product')->getCollection();

        try {
            $collection->load();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $tags = array();

        foreach ($collection as $model) {
            foreach ($model->data['tags'] as $tag) {
                if (!isset($tags[$tag])) {
                    $tags[$tag] = 0;
                }

                $tags[$tag]++;
            }
        }

        $this->_ajaxResponse(array_keys($tags));
    }

    public function actionGetProductSEOTags(){
        $collection = OSC::model('catalog/collection')->getCollection();

        try {
            $collection->load();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $tags = array();
        foreach ($collection->getItems() as $tag) {
            $tag = $tag->data;

            if (!isset($tags[$tag['collection_id']])) {
                $tags[$tag['collection_id']] = [
                    'id' => $tag['collection_id'],
                    'value' => $tag['custom_title'] ?: $tag['title']
                ];
            }
            $tags[$tag]++;

        }

        $this->_ajaxResponse([['type' => 'include_id'], ['data' => array_values($tags)]]);
    }

    public function actionDuplicate() {
        /* @var $source_product Model_Catalog_Product */
        /* @var $new_product Model_Catalog_Product */
        /* @var $source_image Model_Catalog_Product_Image */
        /* @var $new_image Model_Catalog_Product_Image */
        /* @var $source_variant Model_Catalog_Product_Variant */
        /* @var $new_variant Model_Catalog_Product_Variant */

        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/add');

        $path_list = '*/*/list';

        try {
            $source_product = OSC::model('catalog/product')->load($this->_request->get('id'));
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getCode() === 404 ? 'Product is not exists' : $ex->getMessage());
            static::redirect($this->getUrl($path_list));
        }

        if ($source_product->checkMasterLock()) {
            $this->addErrorMessage('You do not have the right to perform this function');
            static::redirect($this->getUrl($path_list));
        }

        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        $new_product_image_dir = '';

        try {
            $new_product = $source_product->getNullModel();

            $product_data = $source_product->data;
            $product_type_ids = [];
            $product_type_ukeys = [];
            $is_product_type_deactive = false;
            $product_type_variant_ids = [];
            $product_types = explode(', ', $product_data['product_type']);

            $product_type_deactive = OSC::helper('catalog/productType')->getProductTypeDeactive($product_types);

            foreach ($product_type_deactive as $key => $product_type) {
                $product_type_ids[] = $product_type->data['id'];
                $product_type_ukeys[$product_type->data['ukey']] = $product_type->data['title'];
            }

            if (count($product_type_ids) > 1) {
                $product_type_variant_collection = OSC::model('catalog/productType_variant')->getCollection()->addField('title', 'product_type_id')->addCondition('product_type_id', $product_type_ids, OSC_Database::OPERATOR_IN)->load();

                foreach ($product_type_variant_collection as $key => $product_type_variant) {
                    $product_type_variant_ids[] = $product_type_variant->data['id'];
                }
            }

            if (count($product_type_deactive) >= 1) {
                foreach ($product_types as $key => $ukey) {
                    if (in_array($ukey, array_keys($product_type_ukeys))) {
                        if (count($product_data['meta_data']['campaign_config']['print_template_config']) == 1) {
                            $this->addErrorMessage('Cannot duplicate campaign because the product type of ' . $product_type_ukeys[$ukey] . ' is no longer activated');
                            static::redirect($this->getUrl($path_list));
                        }
                        $is_product_type_deactive = true;
                        unset($product_types[$key]);
                    }
                }

                $product_data['product_type'] = implode(', ', $product_types);
            }

            if (isset($product_data['meta_data']['sref']) && !empty($product_data['meta_data']['sref'])) {
                unset($product_data['meta_data']['sref']);
            }

            if ($is_product_type_deactive && isset($product_data['meta_data']['campaign_config']['print_template_config']) && count($product_data['meta_data']['campaign_config']['print_template_config']) > 0) {
                $print_template_data_deactive = OSC::helper('catalog/product')->clearPrintTemplateDeactiveByDuplicate($product_data['meta_data']['campaign_config']['print_template_config']);
                $product_data['meta_data']['campaign_config']['print_template_config'] = $print_template_data_deactive['print_template_configs'];
            }

            unset($product_data[$new_product->getPkFieldName()]);
            unset($product_data['sku']);
            unset($product_data['upc']);
            unset($product_data['solds']);
            unset($product_data['listing']);
            unset($product_data['added_timestamp']);
            unset($product_data['modified_timestamp']);
            unset($product_data['meta_tags']);
            unset($product_data['seo_tags']);
            unset($product_data['seo_status']);
            unset($product_data['product_amz_id']);

            $product_data['price'] = $source_product->getFloatPrice();
            $product_data['compare_at_price'] = $source_product->getFloatCompareAtPrice();
            $product_data['member_id'] = $this->getAccount()->getId();
            $product_data['meta_tags']['is_clone'] = $source_product->getId();

            $new_product->setData($product_data)->save();

            $image_map = [];
            $video_map = [];

            // duplicate images
            if (!$source_product->isCampaignMode()) {
                $new_product_image_dir = 'product/' . $new_product->getId() . '/';

                foreach ($source_product->getImages() as $source_image) {
                    $new_image = $source_image->getNullModel();
                    $image_data = $source_image->data;

                    unset($image_data[$new_image->getPkFieldName()]);
                    unset($image_data['added_timestamp']);
                    unset($image_data['modified_timestamp']);

                    $image_data['product_id'] = $new_product->getId();
                    $image_data['filename'] = $new_product_image_dir .
                        $new_product->data['member_id'] .
                        '.' . OSC::makeUniqid(false, true) .
                        '.' . $image_data['extension'];

                    /* Copy image from source product to duplicate product */
                    try {
                        $source_product_image_s3_path = OSC::core('aws_s3')->getStoragePath($source_image->data['filename']);
                        $duplicate_product_image_s3_path = OSC::core('aws_s3')->getStoragePath($image_data['filename']);

                        OSC::core('aws_s3')->copy($source_product_image_s3_path, $duplicate_product_image_s3_path);
                    } catch (Exception $ex) {

                    }

                    $new_image->setData($image_data)->save();

                    $image_map[$source_image->getId()] = $new_image->getId();
                }
            }

            // duplicate videos
            $new_video_dir = $source_product->isCampaignMode()
                ? 'campaign/' . $new_product->getId() . '/'
                : 'product/' . $new_product->getId() . '/';

            foreach ($source_product->getVideos() as $source_video) {
                $new_video = $source_video->getNullModel();
                $video_data = $source_video->data;

                unset($video_data[$new_video->getPkFieldName()]);
                unset($video_data['added_timestamp']);
                unset($video_data['modified_timestamp']);

                $video_data['product_id'] = $new_product->getId();
                $video_data['filename'] = $new_video_dir .
                    $new_product->data['member_id'] .
                    '.' . OSC::makeUniqid(false, true) .
                    '.' . $video_data['extension'];
                $video_data['ukey'] = $new_product->getId() . '_' . md5($video_data['filename']);

                /* Copy video from source product to duplicate product */
                try {
                    $source_video_s3_path = OSC::core('aws_s3')->getStoragePath($source_video->data['filename']);
                    $duplicate_video_s3_path = OSC::core('aws_s3')->getStoragePath($video_data['filename']);

                    OSC::core('aws_s3')->copy($source_video_s3_path, $duplicate_video_s3_path);

                    if ($source_video->data['thumbnail']) {
                        $source_thumbnail_s3_path = OSC::core('aws_s3')->getStoragePath($source_video->data['thumbnail']);
                        $duplicate_thumbnail_s3_path = OSC::core('aws_s3')->getStoragePath($video_data['thumbnail']);

                        OSC::core('aws_s3')->copy($source_thumbnail_s3_path, $duplicate_thumbnail_s3_path);
                    }
                } catch (Exception $ex) {

                }

                $new_video->setData($video_data)->save();

                $video_map[$source_video->getId()] = $new_video->getId();
            }

            /**
             * Variant images
             */

            $images_keys = [];
            $images_customer_upload_keys = [];
            $images_ids = [];

            foreach ($source_product->getVariants() as $source_variant) {
                $variant_data = $source_variant->data;
                if (is_array($variant_data['meta_data']['campaign_config']['image_ids']) &&
                    count($variant_data['meta_data']['campaign_config']['image_ids']) > 0
                ) {
                    foreach ($variant_data['meta_data']['campaign_config']['image_ids'] as $images_list) {
                        if (isset($print_template_data_deactive['print_template_deactive_ids']) && in_array($images_list['print_template_id'], $print_template_data_deactive['print_template_deactive_ids'])) {
                            continue;
                        }
                        $images_ids = array_unique(array_merge($images_ids, $images_list['image_ids']));
                    }
                }
            }

            if (count($images_ids) > 0) {
                $images_arr = OSC::model('catalog/product_image')->getCollection()
                    ->addCondition('image_id', $images_ids, OSC_Database::OPERATOR_IN)
                    ->load()
                    ->toArray();

                $version = time();

                foreach ($images_arr as $image) {
                    $old_ukey = explode('_', $image['ukey']);

                    if ($image['is_static_mockup'] == 2) {
                        $ukey = implode('_', str_replace($old_ukey[0], $new_product->getId(), $old_ukey, $i));
                    } else {
                        $ukey = implode('_', str_replace([$old_ukey[0], end($old_ukey)], [$new_product->getId(), $version], $old_ukey, $i));
                    }

                    if ($image['is_static_mockup'] == 1) {
                        $image_url = $image['filename'];

                    } else {
                        /**
                         * Copy image customer mockup
                         */
                        $path_info = pathinfo($image['filename']);

                        $image_url = str_replace(
                            [$path_info['filename'], '/' . $source_product->getId() . '/'],
                            [$ukey, '/' . $new_product->getId() . '/'],
                            $image['filename']
                        );

                        $source_image_s3_path = OSC::core('aws_s3')->getStoragePath($image['filename']);
                        $duplicate_image_s3_path = OSC::core('aws_s3')->getStoragePath($image_url);
                        $options = [
                            'overwrite' => true,
                            'permission_access_file' => 'public-read'
                        ];

                        OSC::core('aws_s3')->copy($source_image_s3_path, $duplicate_image_s3_path, $options);
                    }

                    /**
                     * Insert to image table
                     */

                    try {
                        $image_model = OSC::model('catalog/product_image')->loadByUKey($ukey);
                    } catch (Exception $ex) {
                        if ($ex->getCode() != 404) {
                            throw new Exception($ex->getMessage());
                        }

                        $image_model = OSC::model('catalog/product_image')->setData([
                            'product_id' => $new_product->getId(),
                            'ukey' => $ukey,
                            'position' => $image['position'] ?: 0,
                            'flag_main' => $image['flag_main'] ?: 0,
                            'extension' => $image['extension'],
                            'width' => $image['width'],
                            'height' => $image['height'],
                            'alt' => $image['alt'],
                            'filename' => $image_url,
                            'is_static_mockup' => $image['is_static_mockup'],
                            'is_upload_mockup_amazon' => $image['is_upload_mockup_amazon'],
                            'is_show_product_type_variant_image' => $image['is_show_product_type_variant_image']
                        ])->save();
                    }

                    /**
                     * Push array to replace
                     */
                    $images_keys[$image['id']] = $image_model->getId();

                    if ($image['is_static_mockup'] == 2) {
                        $images_customer_upload_keys[$image['id']] = $image_model->getId();
                    }
                }
            }

            /**
             * Update variant meta_data
             */
            foreach ($source_product->getVariants() as $source_variant) {
                if (in_array($source_variant->data['product_type_variant_id'], $product_type_variant_ids)) {
                    continue;
                }

                $new_variant = $source_variant->getNullModel();
                $variant_data = $source_variant->data;

                $variant_data['price'] = $source_variant->getFloatPrice();
                $variant_data['compare_at_price'] = $source_variant->getFloatCompareAtPrice();
                $variant_data['cost'] = $source_variant->getFloatCost();
                $variant_data['weight'] = $source_variant->getFloatWeight();

	            if (is_array($variant_data['meta_data']['semitest_config'])) {
		            foreach ($variant_data['meta_data']['semitest_config'] as $key => $semitest_config) {
			            if (!in_array($key, ['shipping_price', 'shipping_plus_price'])) {
			            	continue;
			            }
			            $variant_data['meta_data']['semitest_config'][$key] = OSC::helper('catalog/common')->floatToInteger(floatval($semitest_config));
		            }
	            }

                if (is_array($variant_data['meta_data']['campaign_config']['image_ids'])) {
                    foreach ($variant_data['meta_data']['campaign_config']['image_ids'] as $key => $images_list) {
                        foreach ($images_list['image_ids'] as $k => $item) {
                            if (in_array($item, array_keys($images_keys))) {
                                $variant_data['meta_data']['campaign_config']['image_ids'][$key]['image_ids'][$k] = $images_keys[$item];
                            }
                            if (in_array($item, array_keys($images_customer_upload_keys))) {
                                $variant_data['meta_data']['campaign_config']['image_ids'][$key]['image_ids_customer'][$k] = $images_customer_upload_keys[$item];
                            }
                        }
                    }
                }

                if (is_array($variant_data['meta_data']['video_config']['position'])) {
                    foreach ($variant_data['meta_data']['video_config']['position'] as $video_id => $position) {
                        $variant_data['meta_data']['video_config']['position'][$video_map[$video_id]] = $position;
                        unset($variant_data['meta_data']['video_config']['position'][$video_id]);
                    }
                }

                unset($variant_data[$new_variant->getPkFieldName()]);
                unset($variant_data['added_timestamp']);
                unset($variant_data['modified_timestamp']);
                unset($variant_data['sku']);
                unset($variant_data['best_price_data']);
                
                $variant_data['product_id'] = $new_product->getId();

                if (count($variant_data['image_id']) > 0) {
                    foreach ($variant_data['image_id'] as $variant_data_image_id) {
                        $variant_data['image_id'][] = $image_map[$variant_data_image_id];
                    }
                }

                $variant_data_video_ids = [];
                if (count($variant_data['video_id']) > 0) {
                    foreach ($variant_data['video_id'] as $variant_data_video_id) {
                        $variant_data_video_ids[] = $video_map[$variant_data_video_id];
                    }
                    $variant_data['video_id'] = $variant_data_video_ids;
                }

                $new_variant->setData($variant_data)->save();
            }

            $new_product->getVariants(true);

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            if ($new_product_image_dir) {
                try {
                    $new_product_image_dir_s3 = OSC::core('aws_s3')->getStoragePath($new_product_image_dir);
                    OSC::core('aws_s3')->delete($new_product_image_dir_s3);
                } catch (Exception $ex) {

                }
            }

            $this->addErrorMessage($ex->getMessage());

            static::redirect($this->getUrl($path_list));
        }

        OSC_Database_Model::unlockPreLoadedModel($locked_key);

        $this->addMessage('Duplicate product successfully!');

        static::redirect($this->getUrl('*/*/post', ['id' => $new_product->getId()]));
    }

    public function actionDelete() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/delete');

        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            try {
                /* @var $model Model_Catalog_Product */
                $model = OSC::model('catalog/product')->load($id);

                if ($model->checkMasterLock()) {
                    $this->addErrorMessage('You do not have the right to perform this function');
                    static::redirect($this->getUrl('*/*/list'));
                }

                $model->delete();

                $this->addMessage('Deleted the product #' . $id . ' [' . $model->getProductTitle() . ']');
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        static::redirectLastListUrl($this->getUrl('list'));
    }

    public function actionAddToCollection() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/edit');

        try {
            $product = OSC::model('catalog/product')->load($this->_request->get('id'));

            $collection_ids = $product->collectionAdd($this->_request->get('collection_id'));

            OSC::helper('core/common')->writeLog('Product', "Catalog :: Add product [{$product->getId()}] \"{$product->getProductTitle()}\" to collection [" . implode(',', $collection_ids) . "]");

            $this->_ajaxResponse();
        } catch (Exception $ex) {
            if ($ex->getCode() === 404) {
                $this->_ajaxError('Product is not exists');
            }

            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionRemoveFromCollection() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/edit');

        try {
            $product = OSC::model('catalog/product')->load($this->_request->get('id'));

            $collection_ids = $product->collectionRemove($this->_request->get('collection_id'));

            OSC::helper('core/common')->writeLog('Product', "Catalog :: Remove product [{$product->getId()}] \"{$product->getProductTitle()}\" from collection [" . implode(',', $collection_ids) . "]");

            $this->_ajaxResponse();
        } catch (Exception $ex) {
            if ($ex->getCode() === 404) {
                $this->_ajaxError('Product is not exists');
            }

            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionBrowse() {
        /* @var $search OSC_Search_Adapter */
        $search = OSC::core('search')->getAdapter('backend');

        $page_size = intval($this->_request->get('page_size'));

        if ($page_size == 0) {
            $page_size = 25;
        } else if ($page_size < 5) {
            $page_size = 5;
        } else if ($page_size > 100) {
            $page_size = 100;
        }

        try {
            $search->setKeywords($this->_request->get('keywords'));
            $search->addFilter('module_key', 'catalog', OSC_Search::OPERATOR_EQUAL)
                    ->addFilter('item_group', 'product', OSC_Search::OPERATOR_EQUAL);

            $page = intval($this->_request->get('page'));

            if ($page < 1) {
                $page = 1;
            }

            $search->setLikeMode(true)->setOffset(($page - 1) * $page_size)->setPageSize($page_size);

            $result = $search->fetch(array('allow_no_keywords', 'auto_fix_page'));

            $products = array();

            if (count($result['docs']) > 0) {
                $product_ids = array();

                foreach ($result['docs'] as $doc) {
                    $product_ids[] = $doc['item_id'];
                }

                /* @var $collection Model_Catalog_Product_Collection */
                $collection = OSC::model('catalog/product')->getCollection()->load($product_ids);

                if ($this->_request->get('variant')) {
                    $collection->countVariant();
                }

                /* @var $product Model_Catalog_Product */
                foreach ($collection as $product) {
                    $products[] = array(
                        'id' => intval($product->getId()),
                        'title' => $product->getProductTitle(),
                        'url' => $product->getDetailUrl(),
                        'type' => $product->data['product_type'],
                        'vendor' => $product->data['vendor'],
                        'price' => intval($product->data['price']),
                        'total_variant' => ($this->_request->get('variant') && !$product->hasOnlyDefaultVariant()) ? $product->getTotalVariant() : -1,
                        'options' => $product->getOrderedOptions(true),
                        'collection_ids' => $product->data['collection_ids'],
                        'image' => $product->getFeaturedImageUrl()
                    );
                }
            }

            $this->_ajaxResponse(array(
                'keywords' => $result['keywords'],
                'total' => $result['total_item'],
                'offset' => $result['offset'],
                'current_page' => $result['current_page'],
                'page_size' => $result['page_size'],
                'items' => $products
            ));
        } catch (OSC_Search_Exception_Condition $e) {
            $this->_ajaxError($e->getMessage(), $e->getCode());
        }
    }

    public function actionBrowseDB() {
        $page_size = intval($this->_request->get('page_size'));
        $except = $this->_request->get('except') ?? [];
        if ($page_size == 0) {
            $page_size = 25;
        } else if ($page_size < 5) {
            $page_size = 5;
        } else if ($page_size > 100) {
            $page_size = 100;
        }

        try {
            $page = intval($this->_request->get('page'));

            if ($page < 1) {
                $page = 1;
            }

            $products = [];

            $keyword = trim($this->_request->get('keywords'));

            /* @var $collection Model_Catalog_Product_Collection */
            $collection = OSC::model('catalog/product')->getCollection();
            $collection->setPageSize($page_size)
                ->setOffset(($page - 1) * $page_size)
                ->setCurrentPage($page)
                ->addCondition('title',"%{$keyword}%",OSC_Database::OPERATOR_LIKE);

            if (count($except)) {
                $collection->addCondition('product_id', $except, OSC_Database::OPERATOR_NOT_IN);
            }

            $collection->load();

            /* @var $product Model_Catalog_Product */
            foreach ($collection as $product) {
                $products[] = array(
                    'id' => intval($product->getId()),
                    'title' => $product->getProductTitle(),
                    'url' => $product->getDetailUrl(),
                    'type' => $product->data['product_type'],
                    'vendor' => $product->data['vendor'],
                    'price' => intval($product->data['price']),
                    'total_variant' => -1,
                    'options' => $product->getOrderedOptions(true),
                    'collection_ids' => $product->data['collection_ids'],
                    'image' => $product->getFeaturedImageUrl()
                );
            }

            $this->_ajaxResponse(array(
                'keywords' => [],
                'total' => $collection->collectionLength(),
                'offset' => (($collection->getCurrentPage() - 1) * $collection->getPageSize()) + $collection->length(),
                'current_page' => $collection->getCurrentPage(),
                'page_size' => $collection->getPageSize(),
                'items' => $products
            ));
        } catch (Exception $e) {
            $this->_ajaxError($e->getMessage(), $e->getCode());
        }
    }

    public function actionBrowseEs()
    {
        $page_size = intval($this->_request->get('page_size'));

        if ($page_size == 0) {
            $page_size = 25;
        } else if ($page_size < 5) {
            $page_size = 5;
        } else if ($page_size > 100) {
            $page_size = 100;
        }

        try {
            $page = intval($this->_request->get('page'));

            if ($page < 1) {
                $page = 1;
            }

            OSC::sessionSet($this->_search_product_key, [
                'keywords' => trim($this->_request->get('keywords')),
                'filter_value' => [],
                'flag_type' => 0,
                'filter_field' => trim($this->_request->get('filter_field')),
            ], 60 * 60);

            $collection = OSC::model('catalog/product')->getCollection();

            $products = [];

            $result = $this->_applySearchProduct($collection, $page, $page_size, true, ['search_by_default']);
            if (isset($result['list_id']) && !empty($result['list_id'])) {
                $data = OSC::model('catalog/product')->getCollection()->load($result['list_id']);
                foreach ($result['list_id'] as $id) {
                    try {
                        $product = $data->getItemByPK($id);
                        if (!empty($product)) {
                            $products[] = [
                                'id' => intval($product->getId()),
                                'title' => $product->getProductTitle(),
                                'url' => $product->getDetailUrl(),
                                'type' => $product->data['product_type'],
                                'vendor' => $product->data['vendor'],
                                'price' => intval($product->data['price']),
                                'total_variant' => -1,
                                'options' => $product->getOrderedOptions(true),
                                'collection_ids' => $product->data['collection_ids'],
                                'image' => $product->getFeaturedImageUrl()
                            ];
                        }
                    } catch (Exception $exception) {
                    }
                }
            }

            $search_page = $result['page'] ?? 0;
            $search_page_size = $result['page_size'] ?? 0;
            $search_total_item = $result['total_item'] ?? 0;

            $this->_ajaxResponse(array(
                'keywords' => [],
                'total' => $search_total_item,
                'offset' => (($search_page - 1) * $search_page_size) + count($products),
                'current_page' => $search_page,
                'page_size' => $search_page_size,
                'items' => $products
            ));
        } catch (Exception $e) {
            $this->_ajaxError($e->getMessage());
        }
    }


    public function actionBrowseVariant() {
        $product_id = intval($this->_request->get('product'));

        if ($product_id < 1) {
            $this->_ajaxError('Product ID is empty');
        }

        try {
            /* @var $product Model_Catalog_Product */
            /* @var $variant Model_Catalog_Product_Variant */

            $product = OSC::model('catalog/product')->load($product_id);

            $variants = [];

            foreach ($product->getVariants() as $variant) {
                $variants[] = [
                    'id' => intval($variant->getId()),
                    'option1' => $variant->data['option1'],
                    'option2' => $variant->data['option2'],
                    'option3' => $variant->data['option3'],
                    'price' => intval($variant->data['price']),
                    'compare_at_price' => intval($variant->data['compare_at_price'])
                ];
            }

            $this->_ajaxResponse(['variants' => $variants]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionResyncSearchIndex() {
        try {
            if (!$this->getAccount()->isRoot()) {
                throw new Exception('No permission');
            }

            OSC::core('cron')->addQueue('catalog/product_resyncSearchIndex', null, ['requeue_limit' => -1, 'ukey' => 'catalog/product_resyncSearchIndex', 'estimate_time' => 60 * 2]);
            $this->addMessage('Resync task has been appended to queue');
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
        }

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionBulkDiscard() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/edit/bulk');

        try {
            $condition = $this->_request->get('condition');

            $collection = OSC::model('catalog/product')->getCollection();

            if ($condition == 'search') {
                $this->_applyListCondition($collection);
            } else if ($condition != 'all') {
                if (!is_array($condition)) {
                    throw new Exception('Please select least a product to discard');
                }

                $condition = array_map(function($id) {
                    return intval($id);
                }, $condition);
                $condition = array_filter($condition, function($id) {
                    return $id > 0;
                });

                if (count($condition) < 1) {
                    throw new Exception('Please select least a product to discard');
                }

                $collection->addCondition($collection->getPkFieldName(), array_unique($condition), OSC_Database::OPERATOR_FIND_IN_SET);

                $condition = 'selected';
            }

            $collection->addCondition('discarded', 0, OSC_Database::RELATION_AND);

            $collection->sort('product_id', OSC_Database::ORDER_ASC)->load();

            if ($collection->length() < 1) {
                throw new Exception('No product was found to discard');
            }

            $ids = [];

            foreach ($collection as $order) {
                $ids[] = $order->getId();
            }

            OSC::core('cron')->addQueue('catalog/product_bulkDiscard', ['ids' => $ids], array('requeue_limit' => -1, 'ukey' => 'catalog/product_bulkDiscard:' . md5(OSC::encode($ids)), 'estimate_time' => 60*5));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => 'Bulk discard task has appended to queue']);
    }

    public function actionBulkSetListing() {
        try {
            if (OSC::helper('core/setting')->get('catalog/product_default/listing_admin') == 1 && $this->checkPermission('catalog/super|catalog/product/full|catalog/product/listing/bulk',false) == false) {
                $this->_ajaxError('You unable to perform this task');
            }

            $listing_mode = intval($this->_request->get('mode')) == 1 ? 1 : 0;

            $condition = $this->_request->get('condition');

            $collection = OSC::model('catalog/product')->getCollection();

            if ($condition == 'search') {
                $this->_applyListCondition($collection);
            } else if ($condition != 'all') {
                if (!is_array($condition)) {
                    throw new Exception('Please select least a product to set listing mode');
                }

                $condition = array_map(function($id) {
                    return intval($id);
                }, $condition);
                $condition = array_filter($condition, function($id) {
                    return $id > 0;
                });

                if (count($condition) < 1) {
                    throw new Exception('Please select least a product to set listing mode');
                }

                $collection->addCondition($collection->getPkFieldName(), array_unique($condition), OSC_Database::OPERATOR_FIND_IN_SET);

                $condition = 'selected';
            }

            $collection->addCondition('listing', $listing_mode == 1 ? 0 : 1, OSC_Database::RELATION_AND);

            $collection->sort('product_id', OSC_Database::ORDER_ASC)->load();

            if ($collection->length() < 1) {
                throw new Exception('No product was found to set listing mode');
            }

            $ids = [];

            foreach ($collection as $order) {
                $ids[] = $order->getId();
            }

            OSC::core('cron')->addQueue('catalog/product_bulkSetListing', ['ids' => $ids, 'mode' => $listing_mode], array('requeue_limit' => -1, 'ukey' => 'catalog/product_bulkSetListing:' . md5(OSC::encode($ids)), 'estimate_time' => 60*5));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => 'Bulk set listing mode task has appended to queue']);
    }

    public function actionBulkSetTag() {
        try {
            if (OSC::helper('core/setting')->get('catalog/product_default/listing_admin') == 1 && !$this->getAccount()->isAdmin()) {
                $this->_ajaxError('You unable to perform this task');
            }

            $export_condition = $this->_request->get('condition');

            if (!in_array($export_condition, ['all', 'search', 'select'])) {
                throw new Exception('Please select condition to Bull Tag');
            }

            $collection = OSC::model('catalog/product')->getCollection();

            if ($export_condition == 'select') {
                $selected_ids = $this->_request->get('selected_ids');

                if (!is_array($selected_ids)) {
                    throw new Exception('Please select least a product to edit tag');
                }

                $selected_ids = array_map(function($product_id) {
                    return intval($product_id);
                }, $selected_ids);

                $selected_ids = array_filter($selected_ids, function($product_id) {
                    return $product_id > 0;
                });

                if (count($selected_ids) < 1) {
                    throw new Exception('Please select least a product to edit tag');
                }

                $collection->addCondition($collection->getPkFieldName(), array_unique($selected_ids), OSC_Database::OPERATOR_FIND_IN_SET);
            } else if ($export_condition == 'search') {
                $this->_applyListCondition($collection);
            }

            $collection->sort('product_id', OSC_Database::ORDER_ASC)->load();

            if ($this->_request->get('get_data') == 'true'){
                $tags = array();

                foreach ($collection as $model) {
                    foreach ($model->data['tags'] as $tag) {
                        if (!isset($tags[$tag])) {
                            $tags[$tag] = 0;
                        }

                        $tags[$tag]++;
                    }
                }

                $this->_ajaxResponse(array_keys($tags));
            }

            $list_tags = $this->_request->get('list_tag');

            if (!is_array($list_tags)) {
                throw new Exception('list tag not found');
            }

            $list_tags = array_map(function($tag) {
                return trim($tag);
            }, $list_tags);

            $list_tags = array_filter($list_tags, function($tag) {
                return $tag != '';
            });

            if (!in_array(trim($this->_request->get('method')),['add_tag','remove_tag'])){
                throw new Exception('method in add_tag or remove_tag');
            }

            if ($collection->length() < 1) {
                throw new Exception('No product was found to set listing mode');
            }

            $ids = [];

            foreach ($collection as $product) {
                $ids[] = $product->getId();
            }

            OSC::core('cron')->addQueue('catalog/product_bulkSetTag', ['ids' => $ids, 'list_tags' => $list_tags, 'method' => $this->_request->get('method')], array('requeue_limit' => -1, 'skip_realtime','ukey' => 'catalog/product_bulkSetTag:' . md5(OSC::encode($ids)),'estimate_time' => 60*5));

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => 'Bulk set Tag mode task has appended to queue']);
    }

    public function actionBulkSetCollection() {
        try {
            if (OSC::helper('core/setting')->get('catalog/product_default/listing_admin') == 1 && !$this->getAccount()->isAdmin()) {
                $this->_ajaxError('You unable to perform this task');
            }

            $export_condition = $this->_request->get('condition');

            if (!in_array($export_condition, ['all', 'search', 'select'])) {
                throw new Exception('Please select condition to Bull Set Collection');
            }

            $collection_product = OSC::model('catalog/product')->getCollection();

            if ($export_condition == 'select') {
                $selected_ids = $this->_request->get('selected_ids');

                if (!is_array($selected_ids)) {
                    throw new Exception('Please select least a product to Bull Set Collection');
                }

                $selected_ids = array_map(function($product_id) {
                    return intval($product_id);
                }, $selected_ids);

                $selected_ids = array_filter($selected_ids, function($product_id) {
                    return $product_id > 0;
                });

                if (count($selected_ids) < 1) {
                    throw new Exception('Please select least a product to Bull Set Collection');
                }

                $collection_product->addCondition($collection_product->getPkFieldName(), array_unique($selected_ids), OSC_Database::OPERATOR_FIND_IN_SET);
            } else if ($export_condition == 'search') {
                $this->_applyListCondition($collection_product);
            }

            $collection_product->sort('product_id', OSC_Database::ORDER_ASC)->load();

            $method = $this->_request->get('method');

            if (!in_array(trim($method),['add_collection','remove_collection'])){
                throw new Exception('method in add_collection or remove_collection');
            }

            if ($this->_request->get('get_data') == 'true'){
                $collection_data = array();
                $collection_collection = null;

                if ($method == 'add_collection'){
                    $collection_collection =  OSC::model('catalog/collection')->getCollection()->addCondition('collect_method', Model_Catalog_Collection::COLLECT_MANUAL,OSC_Database::OPERATOR_EQUAL)->load();

                }elseif ($method == 'remove_collection'){
                    $collections = array();

                    foreach ($collection_product as $model) {
                        foreach ($model->data['collection_ids'] as $collection_id) {
                            if (!isset($collections[$collection_id])) {
                                $collections[$collection_id] = 0;
                            }

                            $collections[$collection_id]++;
                        }
                    }

                    if (count($collections) < 1){
                        throw new Exception('remove collection data manual is null');
                    }

                    $collection_collection = OSC::model('catalog/collection')->getCollection()->load(array_keys($collections));
                }

                if (!isset($collection_collection)){
                    throw new Exception('Collection manual is null');
                }

                foreach ($collection_collection as $collection) {
                    $collection_data[] = array(
                        'id' => $collection->getId(),
                        'title' => $collection->data['title'],
                    );
                }

                if (!isset($collection_data) && count($collection_data) < 1 ){
                    throw new Exception('collection data manual is null');
                }

                $this->_ajaxResponse($collection_data);
            }

            $list_collections = $this->_request->get('list_collection');

            if (!is_array($list_collections)) {
                throw new Exception('list collection not found');
            }

            $list_collections = array_map(function($collection) {
                return trim($collection);
            }, $list_collections);

            $list_collections = array_filter($list_collections, function($collection) {
                return $collection > 0;
            });

            $list_collections = array_unique($list_collections);

            if (count($list_collections) < 1) {
                throw new Exception('Collection ID is empty');
            }

            if ($collection_product->length() < 1) {
                throw new Exception('No product was found to set bulk collection');
            }

            $ids = [];

            foreach ($collection_product as $product) {
                $ids[] = $product->getId();
            }

            OSC::core('cron')->addQueue('catalog/product_bulkSetCollection', ['ids' => $ids, 'list_collections' => $list_collections, 'method' => $method], array('requeue_limit' => -1, 'skip_realtime','ukey' => 'catalog/product_bulkSetCollection:' . md5(OSC::encode($ids)),'estimate_time' => 60*5));

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => 'Bulk set Tag mode task has appended to queue']);
    }

    public function actionBulkActive() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/edit/bulk');

        try {
            $condition = $this->_request->get('condition');

            $collection = OSC::model('catalog/product')->getCollection();

            if ($condition == 'search') {
                $this->_applyListCondition($collection);
            } else if ($condition != 'all') {
                if (!is_array($condition)) {
                    throw new Exception('Please select least a product to active');
                }

                $condition = array_map(function($id) {
                    return intval($id);
                }, $condition);
                $condition = array_filter($condition, function($id) {
                    return $id > 0;
                });

                if (count($condition) < 1) {
                    throw new Exception('Please select least a product to active');
                }

                $collection->addCondition($collection->getPkFieldName(), array_unique($condition), OSC_Database::OPERATOR_FIND_IN_SET);

                $condition = 'selected';
            }

            $collection->addCondition('discarded', 1, OSC_Database::RELATION_AND);

            $collection->sort('product_id', OSC_Database::ORDER_ASC)->load();

            if ($collection->length() < 1) {
                throw new Exception('No product was found to active');
            }

            $ids = [];

            foreach ($collection as $order) {
                $ids[] = $order->getId();
            }

            OSC::core('cron')->addQueue('catalog/product_bulkActive', ['ids' => $ids], array('requeue_limit' => -1, 'ukey' => 'catalog/product_bulkActive:' . md5(OSC::encode($ids)), 'estimate_time' => 60*5));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => 'Bulk active task has appended to queue']);
    }

    public function actionBulkDelete() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/delete/bulk');

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $condition = $this->_request->get('condition');

            $collection = OSC::model('catalog/product')->getCollection();

            if ($condition == 'search') {
                $this->_applyListCondition($collection);
            } else {
                if (!is_array($condition)) {
                    throw new Exception('Please select least a product to delete');
                }

                $condition = array_map(function($id) {
                    return intval($id);
                }, $condition);
                $condition = array_filter($condition, function($id) {
                    return $id > 0;
                });

                if (count($condition) < 1) {
                    throw new Exception('Please select least a product to delete');
                }

                $collection->addCondition($collection->getPkFieldName(), array_unique($condition), OSC_Database::OPERATOR_FIND_IN_SET);
            }

            $collection->sort('product_id', OSC_Database::ORDER_ASC)->load();

            if ($collection->length() < 1) {
                throw new Exception('No product was found to delete');
            }

            $flag_add_queue = false;
            if ($collection->length() > 20) {
                $flag_add_queue = true;
            }

            $errors = [];
            $success = 0;

            foreach ($collection as $product) {
                try {
                    if ($flag_add_queue) {
                        $product_bulkqueue_delete = OSC::model('catalog/product_bulkQueue')->getCollection()->addCondition('ukey', 'delete/' . $product->getId())->load()->first();

                        if ($product_bulkqueue_delete) {
                            $product_bulkqueue_delete->setData([
                                'queue_flag' => 1,
                                'error' => null
                            ])->save();
                        } else {
                            OSC::model('catalog/product_bulkQueue')->setData([
                                'ukey' => 'delete/' . $product->getId(),
                                'member_id' => $this->getAccount()->getId(),
                                'action' => 'delete',
                                'queue_data' => $product->getId()
                            ])->save();
                        }
                    } else {
                        OSC::helper('catalog/product')->delete($product->getId());
                    }

                    $success++;
                } catch (Exception $ex) {
                    if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                        $success++;
                    } else {
                        $errors[] = $product->getId();
                    }
                }
            }

            if ($success < 1) {
                throw new Exception('Cannot add products to delete queue');
            }

            OSC::core('cron')->addQueue('catalog/product_bulk_delete', null, ['requeue_limit' => -1, 'estimate_time' => 60*30]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => $flag_add_queue ? 'Bulk delete task has appended to queue' : 'Successfully delete products' . (count($errors) < 1 ? '' : (' with ' . $success . ' products and ' . count($errors) . " errors. Products below cannot add to delete queue:\n" . implode(', ', $errors)))]);
    }

    public function actionEditPrice() {
        $this->output($this->getTemplate()->build('catalog/product/editPrice', [
            'product_id' => intval($this->_request->get('id'))
        ]));
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    public function actionListFixedCampaignPrice() {
        $product_id = intval($this->_request->get('product_id'));
        $data = [];
        $product_types = [];
        $product_variants_data = [];
        $product_type_variant_ids = [];

        $product_variants = OSC::model('catalog/product_variant')
            ->getCollection()
            ->addField('id', 'product_type_variant_id', 'best_price_data')
            ->addCondition('product_id', $product_id)
            ->load();

        foreach ($product_variants as $product_variant) {
            $product_variants_data[$product_variant->data['product_type_variant_id']] = [
                'product_variant_id' => $product_variant->getId(),
                'fixed_price_data' => $product_variant->getFixedPriceData(),
            ];
            $product_type_variant_ids[] = $product_variant->data['product_type_variant_id'];
        }


        $product_type_variants = OSC::model('catalog/productType_variant')
            ->getCollection()
            ->addField('id', 'product_type_id', 'title', 'price', 'compare_at_price')
            ->addCondition('id', $product_type_variant_ids, OSC_Database::OPERATOR_IN)
            ->load();

        $product_type_ids = array_unique(array_column($product_type_variants->toArray(), 'product_type_id'));
        $product_type_collection = OSC::model('catalog/productType')
            ->getCollection()
            ->addField('id', 'title')
            ->addCondition('id', $product_type_ids, OSC_Database::OPERATOR_IN)
            ->load();
        foreach ($product_type_collection as $product_type) {
            $product_types[$product_type->getId()] = $product_type->data['title'];
        }

        foreach ($product_type_variants as $product_type_variant) {
            $product_type_id = $product_type_variant->data['product_type_id'];
            $data[$product_type_id]['product_type_name'] = $product_types[$product_type_id];

            $price = $product_type_variant->data['price'];
            $compare_at_price = $product_type_variant->data['compare_at_price'];
            $plus_price = 0;
            $fixed_price_data = $product_variants_data[$product_type_variant->getId()]['fixed_price_data'];

            if (!empty($fixed_price_data)) {
                $price = intval($fixed_price_data['price']) > 0 ? $fixed_price_data['price'] : $price;
                $compare_at_price = intval($fixed_price_data['compare_at_price']) ?
                    $fixed_price_data['compare_at_price'] :
                    $compare_at_price;
                $plus_price = intval($fixed_price_data['plus_price']);
            }

            $data[$product_type_id]['variants'][] = [
                'id' => $product_type_variant->getId(),
                'product_variant_id' => $product_variants_data[$product_type_variant->getId()]['product_variant_id'],
                'title' => $product_type_variant->data['title'],
                'price' => OSC::helper('catalog/common')->integerToFloat($price),
                'comparePrice' => OSC::helper('catalog/common')->integerToFloat($compare_at_price),
                'plusPrice' => OSC::helper('catalog/common')->integerToFloat($plus_price),
            ];
        }

        $this->_ajaxResponse([
            'result' => 200,
            'data' => $data
        ]);
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    public function actionPostFixedCampaignPrice() {
        $product_variant_ids = $this->_request->get('product_variant_ids');
        $price = floatval($this->_request->get('price'));
        $compare_at_price = floatval($this->_request->get('compare_at_price'));
        $plus_price = floatval($this->_request->get('plus_price'));

        $product_variants = OSC::model('catalog/product_variant')
            ->getCollection()
            ->addCondition('id', $product_variant_ids, OSC_Database::OPERATOR_IN)
            ->load();

        $DB = OSC::core('database')->getWriteAdapter();
        $DB->begin();

        $product_id = 0;
        try {
            foreach ($product_variants as $product_variant) {
                $product_id = $product_variant->data['product_id'];

                $best_price_data = $product_variant->data['best_price_data'];
                $best_price_data['fixed_price_data'] = [
                    'price' => OSC::helper('catalog/common')->floatToInteger($price),
                    'compare_at_price' => OSC::helper('catalog/common')->floatToInteger($compare_at_price),
                    'plus_price' => OSC::helper('catalog/common')->floatToInteger($plus_price)
                ];

                $product_variant->setData('best_price_data', $best_price_data)->save();
            }

            $log_content = 'Update fixed price of campaign: [#' . $product_id . '] by' . OSC::helper('user/authentication')->getMember()->data['username'];
            OSC::helper('core/common')->writeLog('Fixed price', $log_content);

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse([
            'result' => 200,
            'message' => 'Update fixed price of campaign: [#' . $product_id . '] successfully'
        ]);
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    public function actionDeleteFixedCampaignPrice() {
        $product_variant_ids = $this->_request->get('product_variant_ids');

        $product_variants = OSC::model('catalog/product_variant')
            ->getCollection()
            ->addCondition('id', $product_variant_ids, OSC_Database::OPERATOR_IN)
            ->load();

        $DB = OSC::core('database')->getWriteAdapter();
        $DB->begin();

        $product_id = 0;
        try {
            foreach ($product_variants as $product_variant) {
                $product_id = $product_variant->data['product_id'];
                $best_price_data = $product_variant->data['best_price_data'];
                $best_price_data['fixed_price_data'] = [];
                $product_variant->setData('best_price_data', $best_price_data)->save();
            }

            $log_content = 'Delete fixed price of campaign: [#' . $product_id . '] by' . OSC::helper('user/authentication')->getMember()->data['username'];
            OSC::helper('core/common')->writeLog('Delete fixed price', $log_content);

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse([
            'result' => 200,
            'message' => 'Delete fixed price of campaign: [#' . $product_id . '] successfully'
        ]);
    }

    public function actionPostSrefSourceDest () {
        $id = intval($this->_request->get('id'));
        $sref_source = intval($this->_request->get('sref_source'));
        $sref_dest = intval($this->_request->get('sref_dest'));

        try {
            if($sref_source > 0 && $sref_dest == $sref_source) {
                throw new Exception('Sref Source and Sref Dest are the same. Please click button reset');
            }
            $product = OSC::model('catalog/product')->load($id);

            $meta_data = is_array($product->data['meta_data']) ? $product->data['meta_data'] : [];
            $meta_data['sref']['sref_source'] = $sref_source > 0 ? $sref_source : 0;
            $meta_data['sref']['sref_dest'] = $sref_dest > 0 ? $sref_dest : 0;

            $product->setData(['meta_data' => $meta_data])->save();
        } catch (Exception $ex) {
            $message = $ex->getCode() == 404 ? ('Not found product #' . $id) : $ex->getMessage();
            $this->_ajaxError('Error.'. $message);
        }

        $this->_ajaxResponse([
            'result' => 200,
            'message' => 'Success'
        ]);
    }

    public function actionImportTagUpload() {
        $this->checkPermission('catalog/super|catalog/product/full|filter/tag/list');

        try {
            $uploader = new OSC_Uploader();

            if ($uploader->getExtension() != 'xlsx') {
                throw new Exception(strtoupper($uploader->getExtension()) . ' is not allowed to upload');
            }

            $file_name = 'import/catalog/product/tag/.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . time() . '.' . $uploader->getExtension();

            $file_path = OSC_Storage::preDirForSaveFile($file_name);

            $uploader->save($file_path, true);

            $sheet_data = PhpOffice\PhpSpreadsheet\IOFactory::load($file_path)->getActiveSheet()->toArray(null, true, true, true);

            $header = array_shift($sheet_data);

            $header = array_map(function($title) {;
                return preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($title));
            }, $header);

            $map_idx = [];

            foreach ($header as $column => $title) {
                if ($title == '') {
                    continue;
                }
                if (!in_array($title, ['product_id', 'tags'])) {
                    throw new Exception('Header file upload is incorrect format');
                }
                $map_idx[] = $title;
            }

            $data = [];

            foreach ($sheet_data as $row_idx => $sheet_row) {
                $row = [];

                $i = 0;

                foreach ($sheet_row as $idx => $cell) {
                    $row[$map_idx[$i]] = trim($cell);
                    $i++;
                }

                if ($row['product_id'] == '' || $row['tags'] == '') {
                    continue;
                }

                $data[$row['product_id']] = $row['tags'];
            }
            if (count($data) < 1) {
                throw new Exception('Data is incorrect. Please Check again');
            }

            if (!OSC::writeToFile($file_path, OSC::encode($data))) {
                throw new Exception('Cannot write data map fulfillment to file');
            }


            $this->_ajaxResponse(['file' => $file_name]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionImportTags() {
        $this->checkPermission('catalog/super|catalog/product/full|filter/tag/list');

        $errors = [];

        $success = 0;

        $file = $this->_request->get('file');
        $tmp_file_path = OSC_Storage::tmpGetFilePath($file);

        try {
            if (!$tmp_file_path) {
                throw new Exception('File is not exists or removed');
            }

            $JSON = OSC::decode(file_get_contents($tmp_file_path), true);

            $product_ids = array_keys($JSON);

            $product_collection = OSC::model('catalog/product')->getCollection()->addField('product_id')->load($product_ids);

            foreach ($JSON as $product_id => $tags) {
                $queue_data = [];

                if ($product_id < 1) {
                    throw new Exception('Product ' . $product_id . ' is not exist');
                }

                $product = $product_collection->getItemByPK($product_id);

                if (!($product instanceof Model_Catalog_Product)) {
                    throw new Exception('Product ' . $product_id . ' is not exist');
                }

                $tags = explode(',', $tags);

                $queue_data['product_id'] = $product_id;
                $queue_data['tags'] = $tags;

                try {
                    OSC::model('catalog/product_bulkQueue')->setData([
                        'ukey' => 'import_tag/'. $product_id . '_' . md5(OSC::encode($queue_data)),
                        'member_id' => $this->getAccount()->getId(),
                        'action' => 'importTags',
                        'queue_data' => $queue_data
                    ])->save();

                    $success++;
                } catch (Exception $ex) {
                    if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                        $success++;
                    } else {
                        $errors[] = $product->getId();
                    }
                }
            }

            if ($success < 1) {
                throw new Exception('Cannot add products to import queue');
            }

            OSC::core('cron')->addQueue('filter/importTag', null, ['ukey' => 'filter/importTag', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        @unlink($tmp_file_path);

        $this->_ajaxResponse(['message' => 'Bulk import task has appended to queue' . (count($errors) < 1 ? '' : (' with ' . $success . ' products and ' . count($errors) . " errors. Products below cannot add to import queue:\n" . implode(', ', $errors)))]);
    }

    public function actionBulkProductBetaUpload() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/add');

        try {
            $uploader = new OSC_Uploader();

            if ($uploader->getExtension() != 'xlsx') {
                throw new Exception(strtoupper($uploader->getExtension()) . ' is not allowed to upload');
            }

            $file_name = 'import/catalog/bulk_upload_product/.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . time() . '.' . $uploader->getExtension();

            $file_path = OSC_Storage::preDirForSaveFile($file_name);

            $uploader->save($file_path, true);

            $sheet_data = PhpOffice\PhpSpreadsheet\IOFactory::load($file_path)->getActiveSheet()->toArray(null, true, true, true);

            $header = array_shift($sheet_data);

            $header = array_map(function($title) {;
                return preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($title));
            }, $header);

            $map_idx = [];

            foreach ($header as $column => $title) {
                if ($title == '') {
                    continue;
                }

                if (!in_array($title, ['no', 'topic', 'quote', 'description', 'image', 'vendor', 'option_title', 'option_type', 'option_value', 'price', 'compared_price', 'shipping_price', 'shipping_plus_price', 'design'])) {
                    throw new Exception('Header ' . $title . ' file upload is incorrect format');
                }

                $map_idx[] = $title;
            }

            $data = [];

            $list_vendors = OSC::model('user/member')->getListMemberIdeaResearch();

            $vendors = [];

            foreach ($list_vendors as $vendor) {
                $vendors[] = strtolower($vendor->data['username']);
            }

            $list_member_display_live_preview = OSC::helper('core/setting')->get('catalog/product/live_preview_members_vendor');
            $list_member_display_live_preview = OSC::model('user/member')
                ->getCollection()
                ->addField('username')
                ->addCondition('member_id', $list_member_display_live_preview, OSC_Database::OPERATOR_IN)
                ->load()
                ->toArray();

            foreach ($sheet_data as $row_idx => $sheet_row) {
                $row = [];

                $i = 0;

                foreach ($sheet_row as $idx => $cell) {
                    $row[$map_idx[$i]] = trim($cell);
                    $i++;
                }

                foreach (['topic', 'quote', 'description', 'image', 'vendor', 'option_title', 'option_type', 'option_value', 'price', 'shipping_price', 'design'] as $value) {
                    if ($row[$value] == '') {
                        throw new Exception('[Excel Error] ' . $value . ' is empty in the row #' . ($row_idx + 1));
                    }
                }

                $row['design_id'] = $row['design'];
                $row['compare_at_price'] = $row['compared_price'];

                $product_beta = [];

                $product_beta['title'] = $row['quote'];
                $product_beta['topic'] = $row['topic'];
                $product_beta['description'] = $row['description'];
                $product_beta['position_index'] = null;

                $product_beta['listing'] = 0;
                $product_beta['discarded'] = 1;

                $product_beta['meta_tags'] = [
                    'title' => '',
                    'description' => '',
                    'keywords' => '',
                    'image' => null
                ];

                $seo_slug = null;

                if (!$seo_slug){
                    $seo_slug = OSC::core('string')->cleanAliasKey($product_beta['title']);
                }

                $product_beta['slug'] = $seo_slug;

                $product_beta['meta_data'] = [];

                $product_beta['meta_data']['is_disable_preview'] = intval($this->_request->get('is_disable_preview', 0));

                $product_beta['personalized_form_detail'] = 'default';
                if (in_array($product_beta['vendor'], array_column($list_member_display_live_preview, 'username'))) {
                    $product_beta['personalized_form_detail'] = intval($this->_request->get('show_product_detail_type', 0)) == 1 ? 'live_preview' : 'default';
                }

                $product_beta['member_id'] = OSC::helper('user/authentication')->getMember()->getId();
                $product_beta['selling_type'] = Model_Catalog_Product::TYPE_SEMITEST;


                $product_beta['addon_service_data'] = [
                    'addon_services' => [],
                    'addon_service_ids' => '',
                    'enable' => false
                ];

                $row['vendor'] = strtolower($row['vendor']);

                if (!in_array($row['vendor'], $vendors)) {
                    throw new Exception('Vendor is incorrect in the row #' . ($row_idx + 1));
                }

                $product_beta['vendor'] = $row['vendor'];

                $options = [];

                $option_type_system = ['default', 'button', 'clothing_size', 'product_type', 'poster_size', 'color_size'];

                $option_title = explode(',', $row['option_title']);
                $option_type = explode(',', $row['option_type']);

                if (!is_array($option_title) || count($option_title) < 1 || count($option_title) > 3) {
                    throw new Exception('option_title is incorrect in the row #' . ($row_idx + 1));
                }

                if (!is_array($option_type) || count($option_type) < 1 || count($option_type) > 3 || count($option_type) != count($option_title)) {
                    throw new Exception('option_type is incorrect in the row #' . ($row_idx + 1));
                }

                foreach (['option_value', 'design_id', 'image'] as $key) {
                    if (!preg_match('/^(\[\s*([^\[\]]*\s*,\s*)*[^\[\]]*\s*\]\s*)+$/', trim($row[$key]))) {
                        throw new Exception($key . ' wrong format [abc,..,abc] in the row #' . ($row_idx + 1));
                    }
//                    if (!preg_match("/^(\[[^\]]+\],)*\[[^\]]+\]$/", $row[$key])) {

//                    }

                    $row[$key] = str_replace(['[', "\n", "\t", "\r"], '', $row[$key]);

                    $row[$key] = explode(']', $row[$key]);

                    $row[$key] = array_filter($row[$key], function ($value) {
                        return $value !== '';
                    });
                }

                if (!is_array($row['option_value']) || count($row['option_value']) < 1 || count($row['option_value']) > 3 || count($row['option_value']) != count($row['option_value'])) {
                    throw new Exception('$option_value is incorrect in the row #' . ($row_idx + 1));
                }

                foreach ($option_title as $key => $title) {
                    $options['option' . ($key + 1)] = [
                        'title' => trim($title),
                        'position' => $key + 1
                    ];
                }

                foreach ($option_type as $key => $type) {
                    $type = preg_replace('/[^a-zA-Z0-9]+/', '_', trim(strtolower($type)));

                    if (!in_array(trim($type), $option_type_system)) {
                        $type = 'default';
                    }

                    $options['option' . ($key + 1)]['type'] = trim(strtolower($type));
                }

                $option_make_variant = [];

                foreach ($row['option_value'] as $key => $values) {
                    $values = explode(',', $values);

                    $values = array_map('trim', $values);

                    $option_make_variant['option' . ($key + 1)] = $values;

                    $options['option' . ($key + 1)]['values'] = $values;
                }

                $product_beta['options'] = $options;

                $variants = [[]];

                foreach ($option_make_variant as $key => $values) {
                    $append = [];
                    foreach ($variants as $product) {
                        foreach ($values as $item) {
                            $product[$key] = $item;
                            $append[] = $product;
                        }
                    }

                    $variants = $append;
                }

                foreach (['price', 'compare_at_price', 'shipping_price', 'shipping_plus_price'] as $key) {
                    $row[$key] = explode(',', $row[$key]);

                    $buff = [];

                    foreach ($row[$key] as $value) {
                        if (trim($value) == '') {
                            continue;
                        }

                        if (!is_numeric($value)) {
                            throw new Exception($key . ' must be numeric in the row #' . ($row_idx + 1));
                        }

                        if (!is_int($value)) {
                            $value = number_format(floor($value * 100) / 100, 2, '.', '');
                        }

                        $buff[] = $value;
                    }

                    $row[$key] = $buff;
                }

                $_variants = [];

                foreach ($variants as $variant_key => $variant) {
                    foreach (['option1', 'option2', 'option3'] as $key) {
                        if (!isset($variant[$key])) {
                            $variant[$key] = '';
                        }
                    }

                    $variant['image_id'] = '';
                    $variant['video_id'] = '';
                    $variant['price'] = '';
                    $variant['position'] = $variant_key + 1;

                    foreach (['price', 'compare_at_price', 'shipping_price', 'shipping_plus_price', 'design_id'] as $key) {
                        if (count($row[$key]) == 1) {
                            $variant[$key] = $row[$key][0];
                        } else if (count($row[$key]) > 1) {
                            $variant[$key] = $row[$key][$variant_key];
                        }

                        if ($key == 'design_id' && trim($variant[$key]) != '') {
                            $variant[$key] = explode(',', $variant[$key]);
                            $variant[$key] = array_map('trim', $variant[$key]);

                            $variant[$key] = array_filter($variant[$key], function ($value) {
                                return $value !== '';
                            });

                            $variant[$key] = array_values($variant[$key]);
                        }

                        if ($variant['price'] > 0 || $variant['shipping_plus_price'] > 0) {
                            $variant['meta_data'] = OSC::encode([
                                'video_config' => [
                                    'position' => []
                                ],
                                'semitest_config' => [
                                    'shipping_price' => $variant['price'],
                                    'shipping_plus_price' => $variant['shipping_plus_price']
                                ],
                                'variant_config' => []
                            ]);
                        }
                    }

                    $_variants['new.' . OSC::makeUniqid()] = $variant;
                }

                foreach ($row['image'] as $images) {
                    $images = explode(',', $images);
                    $images = array_map('trim', $images);

                    foreach ($images as $image) {
                        if (!OSC::isUrl($image)) {
                            throw new Exception('wrong image url format in the row #' . ($row_idx + 1));
                        }

                        $filename_from_url = parse_url($image);

                        $ext = pathinfo($filename_from_url['path'], PATHINFO_EXTENSION);

                        if (!in_array($ext, ['png','jpg','jpeg', 'mp4'], true)) {
                            throw new Exception(strtoupper($ext) . ' wrong image format in the row #' . ($row_idx + 1));
                        }

//                        $tmp_file_name = 'bulk_upload_product/mockup/' . md5($image) . '.' . $ext;
//
//                        if (!OSC_Storage::tmpFileExists($tmp_file_name)) {
//                            OSC_Storage::tmpSaveFile($image, $tmp_file_name);
//                        }
//
//                        try {
//                            OSC::imageIsNotCorrupt(OSC_Storage::tmpGetFilePath($tmp_file_name));
//                        } catch (Exception $ex) {
//                            @unlink(OSC_Storage::tmpGetFilePath($tmp_file_name));
//                            throw new Exception($ex->getMessage() . ' in the row #' . $row_idx);
//                        }
                    }
                }

                $data[] = [
                    'product_data' => $product_beta,
                    'variants' => $_variants,
                    'images' => $row['image'],
                    'type' => 'beta',
                    'member_id' => $this->getAccount()->getId()
                ];
            }

            if (count($data) < 1) {
                throw new Exception('Data is incorrect. Please Check again');
            }

            if (!OSC::writeToFile($file_path, OSC::encode($data))) {
                throw new Exception('Cannot write data map fulfillment to file');
            }


            $this->_ajaxResponse(['file' => $file_name]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionBulkProductCampaignUpload() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/add');

        try {
            $uploader = new OSC_Uploader();

            if ($uploader->getExtension() != 'xlsx') {
                throw new Exception(strtoupper($uploader->getExtension()) . ' is not allowed to upload');
            }

            $file_name = 'import/catalog/bulk_upload_product/.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . time() . '.' . $uploader->getExtension();

            $file_path = OSC_Storage::preDirForSaveFile($file_name);

            $uploader->save($file_path, true);

            $sheet_data = PhpOffice\PhpSpreadsheet\IOFactory::load($file_path)->getActiveSheet()->toArray(null, true, true, true);

            $header = array_shift($sheet_data);

            $header = array_map(function($title) {;
                return preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($title));
            }, $header);

            $map_idx = [];

            foreach ($header as $column => $title) {
                if ($title == '') {
                    continue;
                }

                if (!in_array($title, ['no', 'product_type', 'product_type_variant', 'topic', 'quote', 'image', 'vendor',
                    'design', 'collection'
                ])) {
                    throw new Exception('Header ' . $title . ' file upload is incorrect format');
                }
                $map_idx[] = $title;
            }

            $data = [];

            $list_vendors = OSC::model('user/member')->getListMemberIdeaResearch();

            $vendors = [];

            foreach ($list_vendors as $vendor) {
                $vendors[] = strtolower($vendor->data['username']);
            }

            $list_member_display_live_preview = OSC::helper('core/setting')->get('catalog/product/live_preview_members_vendor');
            $list_member_display_live_preview = OSC::model('user/member')
                ->getCollection()
                ->addField('username')
                ->addCondition('member_id', $list_member_display_live_preview, OSC_Database::OPERATOR_IN)
                ->load()
                ->toArray();

            foreach ($sheet_data as $row_idx => $sheet_row) {
                $row = [];

                $i = 0;

                foreach ($sheet_row as $idx => $cell) {
                    $row[$map_idx[$i]] = trim($cell);
                    $i++;
                }

                foreach (['topic', 'quote', 'vendor', 'design', 'product_type', 'product_type_variant'] as $value) {
                    if ($row[$value] == '') {
                        throw new Exception('[Excel Error] ' . $value . ' is empty in the row #' . ($row_idx + 1));
                    }
                }

                $row['design_id'] = $row['design'];

                $product_data = [];

                $product_data['title'] = $row['quote'];
                $product_data['topic'] = $row['topic'];
                $product_data['position_index'] = '';
                $product_data['content'] = '';

                $product_data['upc'] = null;

                $product_data['meta_tags'] = [
                    'title' => '',
                    'description' => '',
                    'keywords' => '',
                    'image' => null
                ];

                $seo_slug = null;

                if (!$seo_slug){
                    $seo_slug = OSC::core('string')->cleanAliasKey($product_data['topic'] . '-' . $product_data['title']);
                }

                $product_data['slug'] = $seo_slug;

                $product_data['meta_data'] = [];

                $product_data['personalized_form_detail'] = 'default';
                if (in_array($product_data['vendor'], array_column($list_member_display_live_preview, 'username'))) {
                    $product_data['personalized_form_detail'] = intval($this->_request->get('show_product_detail_type', 0)) == 1 ? 'live_preview' : 'default';
                }

                $product_data['member_id'] = OSC::helper('user/authentication')->getMember()->getId();
                $product_data['selling_type'] = Model_Catalog_Product::TYPE_CAMPAIGN;

                $product_data['addon_service_data'] = [
                    'addon_services' => [],
                    'addon_service_ids' => '',
                    'enable' => false
                ];

                $product_data['tags'] = [];
                $product_data['listing'] = 0;
                $product_data['discarded'] = 1;

                $row['vendor'] = strtolower($row['vendor']);

                if (!in_array($row['vendor'], $vendors)) {
                    throw new Exception('Vendor is incorrect in the row #' . ($row_idx + 1));
                }

                $product_data['vendor'] = $row['vendor'];


                foreach (['product_type', 'product_type_variant', 'collection'] as $key) {
                    $row[$key] = explode(',', $row[$key]);

                    $row[$key] = array_map('trim', $row[$key]);

                    $row[$key] = array_filter($row[$key], function ($value) {
                        return $value !== '';
                    });
                }

                if (count($row['product_type']) < 1) {
                    throw new Exception('product type is incorrect in the row #' . ($row_idx + 1));
                }

                $product_type_collection = OSC::model('catalog/productType')->getCollection()
                    ->addCondition('status', 1, OSC_Database::OPERATOR_EQUAL)
                    ->addField('ukey')->load($row['product_type']);

                $product_type_ukey = [];

                if ($product_type_collection->length() != count($row['product_type'])) {
                    throw new Exception('product type id is incorrect in the row #' . ($row_idx + 1));
                }

                foreach ($product_type_collection as $product_type) {
                    $product_type_ukey[] = $product_type->data['ukey'];
                }

                if (count($row['product_type_variant']) < 1) {
                    throw new Exception('product_type_variant is incorrect in the row #' . ($row_idx + 1));
                }

                $product_type_variant_collection = OSC::model('catalog/productType_variant')->getCollection()
                    ->addCondition('status', 1, OSC_Database::OPERATOR_EQUAL)
                    ->addField('id', 'product_type_id')->load($row['product_type_variant']);

                if ($product_type_variant_collection->length() != count($row['product_type_variant'])) {
                    throw new Exception('product_type_variant id is incorrect in the row #' . ($row_idx + 1));
                }

                $product_type_ids_by_excel = [];
                foreach ($product_type_variant_collection as $product_type_variant) {
                    $product_type_ids_by_excel[] = $product_type_variant->data['product_type_id'];
                }

                if ($product_type_collection->length() != count(array_unique($product_type_ids_by_excel))) {
                    throw new Exception('product type id not enough corresponds to variants in the row #' . ($row_idx + 1));
                }

                $product_collection = [];

                if (count($row['collection']) > 0) {
                    $catalog_collection = OSC::model('catalog/collection')->getCollection()->addField('collection_id')->load($row['collection']);

                    if ($catalog_collection->length() != count($row['collection'])) {
                        throw new Exception('collection id is incorrect in the row #' . ($row_idx + 1));
                    }

                    foreach ($catalog_collection as $collection) {
                        $product_collection[] = $collection->data['collection_id'];
                    }

                    $product_data['collection_ids'] = $product_collection;
                }

                foreach (['image', 'design_id'] as $key) {
                    $row[$key] = trim($row[$key]);

                    if ($key == 'image' && $row[$key] == '') {
                        continue;
                    }

                    if (!preg_match('/^(\[\s*([^\[\]]*\s*,\s*)*[^\[\]]*\s*\]\s*)+$/', trim($row[$key]))) {
                        throw new Exception($key . ' wrong format [abc,..,abc] in the row #' . ($row_idx + 1));
                    }


                    $row[$key] = str_replace(['[', "\n", "\t", "\r"], '', $row[$key]);

                    $row[$key] = explode(']', $row[$key]);

                    $row[$key] = array_filter($row[$key], function ($value) {
                        return $value !== '';
                    });
                }

                if (count($row['design_id']) != 1) {
                    throw new Exception('design id is incorrect in the row #' . ($row_idx + 1));
                }

                $row['design_id'] = explode(',', $row['design_id'][0]);
                $row['design_id'] = array_map('trim', $row['design_id']);

                $row['design_id'] = array_filter($row['design_id'], function ($value) {
                    return $value !== '';
                });

                $row['design_id'] = array_values($row['design_id']);

                $design_collection = OSC::model('personalizedDesign/design')->getCollection()->addField('design_id')->load($row['design_id']);

                if ($design_collection->length() != count(array_unique($row['design_id']))) {
                    throw new Exception('design_ids is incorrect in the row #' . ($row_idx + 1));
                }

                if (count($row['image']) > 0) {
                    foreach ($row['image'] as $images) {
                        $images = explode(',', $images);
                        $images = array_map('trim', $images);

                        foreach ($images as $image) {
                            if (!OSC::isUrl($image)) {
                                throw new Exception('wrong image url format in the row #' . ($row_idx + 1));
                            }

                            $filename_from_url = parse_url($image);

                            $ext = pathinfo($filename_from_url['path'], PATHINFO_EXTENSION);

                            if (!in_array($ext, ['png','jpg','jpeg', 'mp4'], true)) {
                                throw new Exception(strtoupper($ext) . ' wrong image format in the row #' . ($row_idx + 1));
                            }

//                        $tmp_file_name = 'bulk_upload_product/mockup/' . md5($image) . '.' . $ext;
//
//                        if (!OSC_Storage::tmpFileExists($tmp_file_name)) {
//                            OSC_Storage::tmpSaveFile($image, $tmp_file_name);
//                        }
//
//                        try {
//                            OSC::imageIsNotCorrupt(OSC_Storage::tmpGetFilePath($tmp_file_name));
//                        } catch (Exception $ex) {
//                            @unlink(OSC_Storage::tmpGetFilePath($tmp_file_name));
//                            throw new Exception($ex->getMessage() . ' in the row #' . $row_idx);
//                        }
                        }
                    }
                }

                $data[] = [
                    'product_data' => $product_data,
                    'design_ids' => $row['design_id'],
                    'product_type' => $product_type_ukey,
                    'product_type_variant' => $row['product_type_variant'],
                    'images' => $row['image'],
                    'type' => 'campaign',
                    'member_id' => $this->getAccount()->getId()
                ];
            }

            if (count($data) < 1) {
                throw new Exception('Data is incorrect. Please Check again');
            }

            if (!OSC::writeToFile($file_path, OSC::encode($data))) {
                throw new Exception('Cannot write data map fulfillment to file');
            }


            $this->_ajaxResponse(['file' => $file_name]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionBulkProduct() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/add');

        $errors = [];

        $success = 0;

        $file = $this->_request->get('file');
        $tmp_file_path = OSC_Storage::tmpGetFilePath($file);

        try {
            if (!$tmp_file_path) {
                throw new Exception('File is not exists or removed');
            }

            $JSON = OSC::decode(file_get_contents($tmp_file_path), true);

            foreach ($JSON as $data) {
                try {
                    OSC::model('catalog/product_bulkQueue')->setData([
                        'ukey' => 'bulk_upload_product/' . '_' . md5(OSC::encode($data)),
                        'member_id' => $this->getAccount()->getId(),
                        'action' => 'bulkUploadProduct',
                        'queue_data' => $data
                    ])->save();

                    $success++;
                } catch (Exception $ex) {
                    if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                        $success++;
                    } else {
                        $errors[] = $data['product_data']['quote'];
                    }
                }
            }

            if ($success < 1) {
                throw new Exception('Cannot add bulk upload to queue');
            }
            OSC::core('cron')->addQueue('catalog/product_bulkUploadProduct', null, ['ukey' => 'catalog/product_bulkUploadProduct', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        @unlink($tmp_file_path);

        $this->_ajaxResponse(['message' => 'Bulk import task has appended to queue' . (count($errors) < 1 ? '' : (' with ' . $success . ' products and ' . count($errors) . " errors. Products below cannot add to import queue:\n" . implode(', ', $errors)))]);
    }

    public function actionExportProductType() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/add');

        try {
            $product_type_collection = OSC::model('catalog/productType')->getCollection()
                ->addCondition('status', 1, OSC_Database::OPERATOR_EQUAL)
                ->addField('title')
                ->load();

            $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet_product_type = $spreadsheet->getActiveSheet();

            $sheet_product_type->setTitle('Product Type');
            $sheet_product_type->getColumnDimension('B')->setWidth(35);

            $headers_tab_product_type = [
                'ID',
                'Title'
            ];

            foreach ($headers_tab_product_type as $i => $title) {
                $sheet_product_type->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . 1, $title);
            }

            $sheet_row_index = 2;

            $data_product_type_ids = [];

            foreach ($product_type_collection as $model) {
                $data_product_type_ids[] = $model->getId();

                $row_data = [
                    $model->getId(),
                    $model->data['title'],
                ];

                foreach ($row_data as $i => $value) {
                    $sheet_product_type->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . $sheet_row_index, $value);
                }

                $sheet_row_index++;
            }

            $product_type_variant_collection = OSC::model('catalog/productType_variant')->getCollection()
                ->addCondition('status', 1, OSC_Database::OPERATOR_EQUAL)
                ->addField('id', 'product_type_id', 'title')
                ->load();

            $sheet_product_type_variant = $spreadsheet->createSheet();

            $sheet_product_type_variant->setTitle('Product Type Variant');

            $sheet_product_type_variant->getColumnDimension('B')->setWidth(35);
            $sheet_product_type_variant->getColumnDimension('C')->setWidth(15);

            $headers_tab_product_type_variant = [
                'ID',
                'Title',
                'Product Type'
            ];

            foreach ($headers_tab_product_type_variant as $i => $title) {
                $sheet_product_type_variant->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . 1, $title);
            }

            $sheet_row_index = 2;

            foreach ($product_type_variant_collection as $model) {
                if (!in_array($model->data['product_type_id'], $data_product_type_ids)){
                    continue;
                }

                $row_data = [
                    $model->getId(),
                    $model->data['title'],
                    $model->data['product_type_id']
                ];

                foreach ($row_data as $i => $value) {
                    $sheet_product_type_variant->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . $sheet_row_index, $value);
                }

                $sheet_row_index++;
            }

            $catalog_collection = OSC::model('catalog/collection')->getCollection()
                ->addField('collection_id', 'title')
                ->load();

            $sheet_collection = $spreadsheet->createSheet();

            $sheet_collection->setTitle('Collection');

            $sheet_collection->getColumnDimension('B')->setWidth(100);

            $headers_collection = [
                'ID',
                'Title'
            ];

            foreach ($headers_collection as $i => $title) {
                $sheet_collection->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . 1, $title);
            }

            $sheet_row_index = 2;

            foreach ($catalog_collection as $model) {
                $row_data = [
                    $model->getId(),
                    $model->data['title']
                ];

                foreach ($row_data as $i => $value) {
                    $sheet_collection->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . $sheet_row_index, $value);
                }

                $sheet_row_index++;
            }

            $list_vendors = OSC::model('user/member')->getListMemberIdeaResearch();

            $sheet_vendor = $spreadsheet->createSheet();

            $sheet_vendor->setTitle('Vendor');

            $sheet_vendor->getColumnDimension('A')->setWidth(35);

            $headers_vendor = [
                'Title'
            ];

            foreach ($headers_vendor as $i => $title) {
                $sheet_vendor->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . 1, $title);
            }

            $sheet_row_index = 2;

            foreach ($list_vendors as $model) {
                $row_data = [
                    $model->data['username']
                ];

                foreach ($row_data as $i => $value) {
                    $sheet_vendor->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . $sheet_row_index, $value);
                }

                $sheet_row_index++;
            }

            $file_name = 'export/product_type/ ' . OSC::makeUniqid() . '.' . date('d-m-Y') . '.xlsx';

            $file_path = OSC_Storage::preDirForSaveFile($file_name);

            $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $writer->save($file_path);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['url' => OSC_Storage::tmpGetFileUrl($file_name)]);
    }
}
