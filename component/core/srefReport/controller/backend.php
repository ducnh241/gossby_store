<?php

class Controller_SrefReport_Backend extends Abstract_Backend_Controller
{
    protected $_search_key = 'adTracking/search';

    protected $_search_filter_field = 'search_filter_field';
    protected $_default_search_field_key = 'default_search_adtracking_field';
    protected $_filter_field = [
        'all' => 'All field',
        'utm_campaign' => 'Campaign Name',
        'campaign_id' => 'Campaign ID'
    ];

    public function __construct()
    {
        parent::__construct();

        if (!$this->checkPermission('srefReport', false) && !$this->checkPermission('report|power_bi', false)) {
            static::notFound('You don\'t have permission to view the page');
        }

        $this->getTemplate()->setCurrentMenuItemKey('srefReport/dashboard')->addBreadcrumb(array('report', 'Analytics'), $this->getUrl('srefReport/backend/index'));
    }

    protected function _getABTest() {
        $ab_test_key = $this->_request->get('ab_test_key');

        if (!$ab_test_key) {
            return null;
        }

        $ab_test_value = $this->_request->get('ab_test_value');

        if ($ab_test_value === '') {
            return null;
        }

        return ['key' => $ab_test_key, 'value' => $ab_test_value];
    }


    public function actionIndex()
    {
        $sref_member_id = intval($this->_request->get('sref_member_id'));

        $sref_group_id = intval($this->_request->get('sref_group_id'));

        $is_sref_report = ($sref_member_id < 1 && $sref_member_id < 1) ? 0 : 1;

        $action = 'index';
        $selectors = [];
        try {
            $selectors = OSC::helper('user/decentralization/common')->getSelectorsByLeader($action, $sref_member_id, $sref_group_id, $this->_request->get('range', 'today'));
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
            static::redirect($this->getUrl('*/*/*'));
        }
        $member_ids = [];
        $get_data_sref = 0;
        if ($sref_group_id) {
            try {
                $member_ids = OSC::helper('adminGroup/common')->getMembersByGroup($sref_group_id);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
                static::redirect($this->getUrl('*/*/*'));
            }
            $get_data_sref = 1;
        } else if ($sref_member_id) {
            $list_member_mkt = OSC::helper('report/common')->getListMemberActiveAnalytic();

            if (!count($list_member_mkt)) {
                $this->addErrorMessage('List member marketing is empty!');
                static::redirect($this->getUrl('*/*/*'));
            }
            $member_ids = [$sref_member_id];
            $get_data_sref = 1;
        } else {
            $list_member_mkt = OSC::helper('report/common')->getListMemberActiveAnalytic();
            $member_ids = array_keys($list_member_mkt);
        }

        $datas = [
            'get_data_sref' => $get_data_sref,
            'is_sref_report' => $is_sref_report,
            'action' => $action,
            'selectors' => $selectors
        ];

        if ($is_sref_report == 0 && $get_data_sref == 0 && $this->checkPermission('report', false)) {
            $fetched_data = OSC::helper('report/process')->fetchDashboardData($this->_request->get('range'), $this->_getABTest());
            $template = 'report/dashboard';
            $datas = array_merge($fetched_data, $datas);
        } else {
            $fetched_data = OSC::helper('srefReport/process')->fetchDashboardDataBySref($this->_request->get('range'), $member_ids);
            $template = 'srefReport/dashboard';
            $datas['meta_data'] = $fetched_data;
        }
        $page_title = $this->getPageTitle($selectors, $sref_member_id, $sref_group_id);
        $this->getTemplate()->setPageTitle('Analytics :: ' . $fetched_data['title'] . ' / ' . '<span class="page-title-sref">'.$page_title.'</span>');
        $this->output($this->getTemplate()->build($template, $datas));
    }

    public function actionProductList()
    {
        $sref_member_id = intval($this->_request->get('sref_member_id'));

        $sref_group_id = intval($this->_request->get('sref_group_id'));

        $is_sref_report = ($sref_member_id < 1 && $sref_member_id < 1) ? 0 : 1;

        $selectors = [];
        $action = 'productList';
        try {
            $selectors = OSC::helper('user/decentralization/common')->getSelectorsByLeader($action, $sref_member_id, $sref_group_id, $this->_request->get('range', 'today'));
        } catch (Exception $e) {
            static::redirect($this->getUrl('*/*/*'));
        }
        $member_ids = [];
        $get_data_sref = 0;
        if ($sref_group_id) {
            try {
                $member_ids = OSC::helper('adminGroup/common')->getMembersByGroup($sref_group_id);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
                static::redirect($this->getUrl('*/*/*'));
            }
            $get_data_sref = 1;
        } else if ($sref_member_id) {
            $list_member_mkt = OSC::helper('report/common')->getListMemberActiveAnalytic();

            if (!count($list_member_mkt)) {
                $this->addErrorMessage('List member marketing is empty!');
                static::redirect($this->getUrl('*/*/*'));
            }
            $member_ids = [$sref_member_id];
            $get_data_sref = 1;
        }  else {
            $list_member_mkt = OSC::helper('report/common')->getListMemberActiveAnalytic();
            $member_ids = array_keys($list_member_mkt);
        }

        $datas = [
            'get_data_sref' => $get_data_sref,
            'is_sref_report' => $is_sref_report,
            'sref_member_id' => $sref_member_id,
            'sref_group_id' => $sref_group_id,
            'action' => $action,
            'selectors' => $selectors
        ];

        if ($is_sref_report == 0 && $get_data_sref == 0 && $this->checkPermission('report', false)) {
            $fetched_data = OSC::helper('report/process')->fetchProductListData($this->_request->get('range'), $this->_request->get('page'));
            $template = 'report/product/list';
            $datas = array_merge($fetched_data, $datas);
        } else {
            $fetched_data = OSC::helper('srefReport/process')->fetchProductListDataBySerf($this->_request->get('range'), $this->_request->get('page'), $member_ids);
            $template = 'srefReport/product/list';
            $datas['meta_data'] = $fetched_data;
        }

        $page_title = $this->getPageTitle($selectors, $sref_member_id, $sref_group_id);

        $this->getTemplate()
            ->addBreadcrumb('Product List')
            ->setCurrentMenuItemKey('srefReport/product')
            ->setPageTitle('Product List :: ' . $fetched_data['title'] . ' / ' . '<span class="page-title-sref">'.$page_title.'</span>');
        $this->output($this->getTemplate()->build($template, $datas));
    }

    public function actionProductDetail()
    {
        $sref_member_id = $this->_request->get('sref_member_id');
        $sref_group_id = $this->_request->get('sref_group_id');
        $date_range = $this->_request->get('range', 'today');
        $product_id = $this->_request->get('id');
        $product_page = intval($this->_request->get('product_page', 0));

        $user_selector = [];
        $member_ids = [];
        $action = 'productList';
        $redirect_url = $this->getUrl(
            '*/*/productList',
            [
                'sref_member_id' => $sref_member_id,
                'sref_group_id' => $sref_group_id,
                'range' => $date_range
            ]
        );

        if ($product_page) {
            $redirect_url = $this->getUrl(
                'catalog/backend_product/list',[]
            );
        }

        // get user selector
        try {
            $user_selector = OSC::helper('user/decentralization/common')->getSelectorsByLeader(
                $action,
                $sref_member_id,
                $sref_group_id,
                $date_range,
                ['product_page' => $product_page]
            );
        } catch (Exception $e) {
            static::redirect($redirect_url);
        }

        // get member ids
        if ($sref_group_id) {
            try {
                $member_ids = OSC::helper('adminGroup/common')->getMembersByGroup($sref_group_id);
            } catch (Exception $exception) {
                $this->addErrorMessage($exception->getMessage());
                static::redirect($redirect_url);
            }
        } else if ($sref_member_id) {
            try {
                $marketing_user_list = OSC::helper('report/common')->getListMemberActiveAnalytic();

                if (!count($marketing_user_list)) {
                    $this->addErrorMessage('List member marketing is empty!');
                    static::redirect($redirect_url);
                }

                $member_ids = [$sref_member_id];
            } catch (Exception $exception) {
                $this->addErrorMessage($exception->getMessage());
                static::redirect($redirect_url);
            }
        } else {
            $marketing_user_list = OSC::helper('report/common')->getListMemberActiveAnalytic(['product_page' => $product_page]);
            $member_ids = array_keys($marketing_user_list);
        }

        // get product detail data by sref
        try {
            $fetched_data = OSC::helper('srefReport/process')->fetchProductDetailDataBySref(
                $product_id,
                $date_range,
                $member_ids
            );

            if (is_null($fetched_data)) {
                static::redirect($redirect_url);
            }
        } catch (Exception $exception) {
            static::redirect($redirect_url);
        }

        // get date range
        if (!empty($fetched_data['range'])) {
            $date_range = $fetched_data['range'];

            if (is_array($date_range)) {
                if (count($date_range) > 1 && $date_range[0] === $date_range[1]) {
                    $date_range = $date_range[0];
                } else {
                    $date_range = implode('-', $date_range);
                }
            }
        }

        $page_title = $this->getPageTitle($user_selector, $sref_member_id, $sref_group_id);
        $date_title = $fetched_data['title'] ?? '';

        // set breadcrumb and title
        $this->getTemplate()
            ->addBreadcrumb('Product', $this->getUrl('*/*/productList', ['range' => $date_range]))
            ->addBreadcrumb('Product detail :: ' . $date_title)
            ->setPageTitle('Product detail :: ' . $date_title . ' / ' . '<span class="page-title-sref">' . $page_title . '</span>');

        $this->output($this->getTemplate()->build(
            'srefReport/product/detail',
            array(
                'meta_data' => $fetched_data ?? [],
                'selectors' => $user_selector,
                'action' => $action,
                'product_page' => $product_page
            )
        ));
    }

    private function getPageTitle($selectors, $sref_member_id = null, $sref_group_id = null)
    {
        $page_title = $selectors ? 'All Results' : OSC::helper('user/authentication')->getMember()->getUsernameWithFormat();
        if ($sref_member_id) {
            $ref_member = OSC::model('user/member')->load($sref_member_id);

            $group = $ref_member->getGroup();

            $flag_show_group = false;
            if ($this->getAccount()->isAdmin()) {
                if ($group->data['lock_flag'] == 0) {
                    $flag_show_group = true;
                }
            } else {
                $adminGroups = OSC::model('adminGroup/memberGroupsAdmin')->getCollection()->load();
                foreach ($adminGroups as $adminGroup) {
                    if (in_array($group->getId(), $adminGroup->data['group_ids'])) {
                        $flag_show_group = true;
                        break;
                    }
                }
            }

            $page_title = ($flag_show_group ? ('<svg data-icon="osc-group-members-white" viewBox="0 0 15 9" width="20px" ata-insert-cb="configOSCIcon"><use xlink:href="#osc-group-members-white"></use></svg>  ' . $ref_member->getGroup()->data['title'] . ' / ' )  : '') .  $ref_member->data['username'];
        } elseif ($sref_group_id) {
            $ref_group = OSC::model('user/group')->load($sref_group_id);
            $page_title = '<svg data-icon="osc-group-members-white" viewBox="0 0 15 9" width="20px" ata-insert-cb="configOSCIcon"><use xlink:href="#osc-group-members-white"></use></svg>  ' . $ref_group->data['title'];
        }
        return $page_title;
    }

    protected function _getFilterConfig($filter_value = null) {
        $filter_config = [
            'operator' => [
                'title' => 'Filter Options',
                'type' => 'operator',
                'field' => 'operator',
                'data' => [
                    'filter_type' => [
                        'viewed_product' => 'Viewed Product',
                        'added_to_cat' => 'Added To Cart',
                        'check_out' => 'Check out',
                        'purchased' => 'Purchased'
                    ],
                    'comparison' => [
                        OSC_Database::OPERATOR_GREATER_THAN => '>',
                        OSC_Database::OPERATOR_LESS_THAN => '<',
                        OSC_Database::OPERATOR_EQUAL => '=',
                        OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL => '>=',
                        OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL => '<='
                    ],
                    'default_filter_value' => 0
                ]
            ]
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

    /**
     * @return array Campaign ids by filter
     */
    protected function _applySearch() {
        $search = OSC::sessionGet($this->_search_key);
        $keyword = $search['keywords'] ?? '';
        $filter_value = $search['filter_value'] ?? [];
        $filter_field = ($search['search_field'] !== "") ? $search['search_field'] : 'all';

        $filter = [];

        $this->register('search_keywords', $search['keywords'])
            ->register('search_filter', $search['filter_value'])
            ->register($this->_search_filter_field, $filter_field);

        if ($keyword) {
            $keyword = addslashes($keyword);

            if ($filter_field === 'all') {
                $or = [];
                foreach ($this->_filter_field as $key => $_filter_field) {
                    if ($key === $filter_field) continue;
                    $or[] = [
                        $key => [
                            '$regex' =>  $keyword,
                            '$options' => 'i'
                        ]
                    ];
                }
                $filter['keyword'] = [
                    '$match' => [
                        '$or' => $or
                    ]
                ];
            } else {
                $filter['keyword'] = [
                    '$match'  => [
                        $filter_field =>  [
                            '$regex' =>  $keyword,
                            '$options' => 'i',
                        ]
                    ]
                ];
            }
        }

        $is_filter = false;
        $operator = [];
        if (is_array($filter_value) && count($filter_value) > 0) {
            $is_filter = true;
            foreach ($filter_value as $k_filter => $v_filter) {
                if ($k_filter == 'operator') {
                    $operator = $v_filter;
                    unset($filter_value['operator']);
                    continue;
                }
            }

            $compare_field = '';
            switch ($operator['type']) {
                case 'viewed_product':
                    $compare_field = 'product_view_count';
                    break;

                case 'added_to_cat':
                    $compare_field = 'add_to_cart_count';
                    break;

                case 'check_out':
                    $compare_field = 'checkout_initialize_count';
                    break;

                case 'purchased':
                    $compare_field = 'purchase_count';
                    break;

                case 'revenue':
                    $compare_field = 'revenue';
                    break;

                case 'subtotal_revenue':
                    $compare_field = 'subtotal_revenue';
                    break;
            }
        }

        if ($is_filter) {
            $filter['filter']['$match'] = $this->_comparison($compare_field, intval($operator['value']), $operator['comparison']);
        }

        return $filter;
    }

    /**
     * @param $a
     * @param $b
     * @param $operator
     * @return bool
     * $comparison = [
            OSC_Database::OPERATOR_GREATER_THAN => '$gt',
            OSC_Database::OPERATOR_LESS_THAN => '$lt',
            OSC_Database::OPERATOR_EQUAL => '$eq',
            OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL => '$gte',
            OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL => '$lte'
            ];
     */
    protected function _comparison($a, $b, $operator) {
        if ($operator === OSC_Database::OPERATOR_GREATER_THAN) {
            return [$a => ['$gt' => $b]];
        }

        if ($operator === OSC_Database::OPERATOR_LESS_THAN) {
            return [$a => ['$lt' => $b]];
        }

        if ($operator === OSC_Database::OPERATOR_EQUAL) {
            return [$a => ['$eq' => $b]];
        }

        if ($operator === OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL) {
            return [$a => ['$gte' => $b]];
        }

        if ($operator === OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL) {
            return [$a => ['$lte' => $b]];
        }

        return false;
    }

    public function actionSearch() {
        $range = $this->_request->get('range');
        $sref_member_id = intval($this->_request->get('sref_member_id'));
        $sref_group_id = intval($this->_request->get('sref_group_id'));
        $filter = $this->_request->get('filter');
        if (is_array($filter) && count($filter) > 0) {
            $filter = $this->_processFilterValue($this->_getFilterConfig(), $filter);
        } else {
            $filter = [];
        }

        OSC::sessionSet($this->_search_key, [
            'keywords' => trim($this->_request->get('keywords')),
            'filter_value' => $filter,
            'search_field' => $this->_request->get('filter_field')
        ], 60 * 60);

        static::redirect($this->getUrl('*/*/adTracking', ['search' => 1, 'range' => $range, 'sref_group_id' => $sref_group_id, 'sref_member_id' => $sref_member_id]));
    }

    protected function _parseDateRage($date_range)
    {
        if (preg_match('/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*\-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?\s*$/', $date_range, $matches)) {
            for ($i = 1; $i <= 7; $i++) {
                if ($i == 4) {
                    continue;
                }

                $matches[$i] = intval($matches[$i]);
            }

            if (!checkdate($matches[2], $matches[1], $matches[3]) || ($matches[5] && !checkdate($matches[6], $matches[5], $matches[7]))) {
                $date_range = null;
            } else {
                if ($matches[5]) {
                    $begin_date = str_pad($matches[1], 2, 0, STR_PAD_LEFT) . '/' . str_pad($matches[2], 2, 0, STR_PAD_LEFT) . '/' . str_pad($matches[3], 4, 0, STR_PAD_LEFT);
                    $end_date = str_pad($matches[5], 2, 0, STR_PAD_LEFT) . '/' . str_pad($matches[6], 2, 0, STR_PAD_LEFT) . '/' . str_pad($matches[7], 4, 0, STR_PAD_LEFT);
                } else {
                    $begin_date = $end_date = str_pad($matches[1], 2, 0, STR_PAD_LEFT) . '/' . str_pad($matches[2], 2, 0, STR_PAD_LEFT) . '/' . str_pad($matches[3], 4, 0, STR_PAD_LEFT);
                }

                $date_range = [$begin_date, $end_date];
            }
        }

        return $date_range;
    }

    public function actionAdTracking()
    {
        $range = $this->_request->get('range', 'today');
        $sref_member_id = intval($this->_request->get('sref_member_id'));
        $sref_group_id = intval($this->_request->get('sref_group_id'));
        $is_sref_report = ($sref_member_id < 1 && $sref_member_id < 1) ? 0 : 1;
        $sort_by = $this->_request->get('sort_by') ?? 'added_timestamp';
        $sort_order = $this->_request->get('sort_order') ?? -1;

        $search = OSC::sessionGet($this->_search_key);
        $is_search = $this->_request->get('search') && (!empty(trim($search['keywords'])) || !empty($search['filter_value']));

        $applySearch = [];
        if ($is_search) {
            $applySearch = $this->_applySearch();
        }

        $sort = [
            'key' => $sort_by,
            'order' => ($sort_order < 1) ? -1 : 1
        ];
        
        $selectors = [];
        $action = 'adTracking';

        try {
            $selectors = OSC::helper('user/decentralization/common')->getSelectorsByLeader($action, $sref_member_id, $sref_group_id, $this->_request->get('range', 'today'), $sort);
        } catch (Exception $e) {
            static::redirect($this->getUrl('*/*/*'));
        }

        $member_ids = [];
        $get_data_sref = 0;
        if ($sref_group_id) {
            try {
                $member_ids = OSC::helper('adminGroup/common')->getMembersByGroup($sref_group_id);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
                static::redirect($this->getUrl('*/*/*'));
            }
            $get_data_sref = 1;
        }
        else if ($sref_member_id) {
            $list_member_mkt = OSC::helper('report/common')->getListMemberActiveAnalytic();

            if (!count($list_member_mkt)) {
                $this->addErrorMessage('List member marketing is empty!');
                static::redirect($this->getUrl('*/*/*'));
            }
            $member_ids = [$sref_member_id];
            $get_data_sref = 1;
        }
        else {
            $list_member_mkt = OSC::helper('report/common')->getListMemberActiveAnalytic();
            $member_ids = array_keys($list_member_mkt);
        }

        $datas = [
            'range' => $this->_parseDateRage($range),
            'get_data_sref' => $get_data_sref,
            'is_sref_report' => $is_sref_report,
            'sref_member_id' => $sref_member_id,
            'sref_group_id' => $sref_group_id,
            'action' => $action,
            'selectors' => $selectors,
            'sort' => $sort,
            'is_search' => $is_search ? 1 : 0,
            'search_keywords' => $this->registry('search_keywords'),
            'filter_config' => $this->_getFilterConfig($this->registry('search_filter')),
            'filter_field' => $this->_filter_field,
            'selected_filter_field' => $this->registry($this->_search_filter_field),
            'default_search_field_key' => $this->_default_search_field_key
        ];

        $is_get_all = $is_sref_report == 0 && $get_data_sref == 0 && $this->checkPermission('report', false);

        $filter_options = [
            'date_range' => $range
        ];

        if (count($applySearch) > 0) {
            $filter_options['apply_search'] = $applySearch;
        }
        $fetched_data = OSC::helper('report/common')->fetchAdTrackingData(
            'campaign',
            $filter_options,
            $this->_request->get('page') ?? 1,
            $is_get_all ? []: $member_ids,
            $sort
        );

        $template = 'report/adTracking/campaignList';
        $datas = array_merge($fetched_data, $datas);

        $page_title = $this->getPageTitle($selectors, $sref_member_id, $sref_group_id);

        $this->getTemplate()
            ->addBreadcrumb('Ad Tracking')
            ->setCurrentMenuItemKey('srefReport/adTracking')
            ->setPageTitle('Ad Tracking :: ' . $fetched_data['title'] . ' / ' . '<span class="page-title-sref">'.$page_title.'</span>');
        $this->output($this->getTemplate()->build($template, $datas));
    }

    public function actionGetPowerBi()
    {
        if (OSC::helper('srefReport/common')->currentUserHasPowerBiPerm() === false || !$this->checkPermission('power_bi', false)) {
            static::notFound('You don\'t have permission to view the page');
        }

        $this->getTemplate()
            ->resetBreadcrumb()
            ->addBreadcrumb('Power Bi')
            ->setCurrentMenuItemKey('srefReport/powerBi');
        $name = $this->_request->get('name');
        $power_bi_manage = OSC::helper('core/setting')->get('power_bi_manage');
        $power_bis = [];

        foreach ($power_bi_manage as $item) {
            if ($this->getAccount()->isAdmin() || (is_array($item['viewer']) && in_array($this->getAccount()->getId(), $item['viewer']))) {
                $item['activated'] = $name == $item['name'];
                $power_bis[] = $item;
            }
        }

        if ($name === null && count($power_bis) > 0) {
            $name = $power_bis[0]['name'];
            $power_bis[0]['activated'] = true;
        }

        $output_html = $this->getTemplate()->build('user/power_bi/block', ['name' => $name, 'power_bis' => $power_bis]);

        $this->output($output_html);
    }

    public function actionGetPowerBiUrl()
    {
        $token = $this->_request->get('t');
        $name = $this->_request->get('name');
        $secret_string = base64_decode($token);
        $secret_array = explode('_', $secret_string);
        if (count($secret_array) != 3) {
            static::notFound('You don\'t have permission to view the page');
        }
        $time_payload = $secret_array[0];
        $_site_key_payload = $secret_array[1];
        $_name = $secret_array[2];
        if ($time_payload + 5 < time() || $_site_key_payload != OSC_SITE_KEY || $_name != $name) {
            static::notFound('You don\'t have permission to view the page');
        }
        $power_bi_manage = OSC::helper('core/setting')->get('power_bi_manage');
        $url = null;
        foreach ($power_bi_manage as $item) {
            if ($_name == $item['name']) {
                $url = $item['url'];
                break;
            }
        }
        if ($url === null) {
            static::notFound('You don\'t have permission to view the page');
        }
        static::redirect($url);
    }

    public function actionAdTrackingData() {
        $data_type = $this->_request->get('data_type') ?? 'campaign';
        $range = $this->_request->get('range') ?? 'alltime';
        $campaign_ids = $this->_request->get('campaign_ids') ?? [];
        $adset_ids = $this->_request->get('adset_id') ?? [];
        $page = intval($this->_request->get('page')) ?? 1;
        $sref_member_id = intval($this->_request->get('sref_member_id'));
        $sref_group_id = intval($this->_request->get('sref_group_id'));
        $filter = $this->_request->get('filter');
        $is_sref_report = ($sref_member_id < 1 && $sref_member_id < 1) ? 0 : 1;

        $sort_by = $this->_request->get('sort_by') ?? 'added_timestamp';
        $sort_order = $this->_request->get('sort_order') ?? -1;

        $search = OSC::sessionGet($this->_search_key);
        $is_search = intval($this->_request->get('is_search')) === 1 && (!empty(trim($search['keywords'])) || !empty($search['filter_value']));

        $applySearch = [];
        if (count($campaign_ids) === 0) {
            if ($is_search) {
                $applySearch = $this->_applySearch();
            }
        }

        $sort = [
            'key' => $sort_by,
            'order' => ($sort_order < 1) ? -1 : 1
        ];

        $action = 'adTracking';

        $selectors = [];

        if ($data_type == 'ads') {
            $action = 'adTrackingAdsetDetail';
        } elseif ($data_type == 'adsets') {
            $action = 'adTrackingCampaignDetail';
        }

        $params = array_merge([
            'campaign_id' => $campaign_ids,
            'adset_id' => $adset_ids
        ], $sort);

        try {
            $selectors = OSC::helper('user/decentralization/common')->getSelectorsByLeader(
                $action,
                $sref_member_id,
                $sref_group_id,
                $this->_request->get('range', 'today'),
                $params
            );
        } catch (Exception $e) {
            static::redirect($this->getUrl('*/*/*'));
        }

        $member_ids = [];
        $get_data_sref = 0;
        if ($sref_group_id)
        {
            try {
                $member_ids = OSC::helper('adminGroup/common')->getMembersByGroup($sref_group_id);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
                static::redirect($this->getUrl('*/*/*'));
            }
            $get_data_sref = 1;
        }
        else if ($sref_member_id)
        {
            $list_member_mkt = OSC::helper('report/common')->getListMemberActiveAnalytic();

            if (!count($list_member_mkt)) {
                $this->addErrorMessage('List member marketing is empty!');
                static::redirect($this->getUrl('*/*/*'));
            }
            $member_ids = [$sref_member_id];
            $get_data_sref = 1;
        }
        else {
            $list_member_mkt = OSC::helper('report/common')->getListMemberActiveAnalytic();
            $member_ids = array_keys($list_member_mkt);
        }

        $is_get_all = $is_sref_report == 0 && $get_data_sref == 0 && $this->checkPermission('report', false);

        $datas = [
            'get_data_sref' => $get_data_sref,
            'is_sref_report' => $is_sref_report,
            'sref_member_id' => $sref_member_id,
            'sref_group_id' => $sref_group_id,
            'action' => $action,
            'selectors' => $selectors,
            'campaign_ids' => $campaign_ids,
            'adset_id' => $adset_ids,
            'filter_config' => $this->_getFilterConfig(),
            'filter_field' => $this->_filter_field,
            'selected_filter_field' => $this->_search_filter_field,
            'default_search_field_key' => $this->_default_search_field_key
        ];

        $filter_params = [
            'date_range' => $range,
            'filter' => $filter
        ];

        if (count($campaign_ids) > 0) {
            $filter_params['campaign_ids'] = $campaign_ids;
        } else {
            if (count($applySearch) > 0) {
                $filter_params['apply_search'] = $applySearch;
            }
        }

        if (count($adset_ids) > 0) {
            $filter_params['adset_ids'] = $adset_ids;
        }

        $fetched_data = OSC::helper('report/common')->fetchAdTrackingData(
            $data_type,
            $filter_params,
            $page,
            $is_get_all ? []: $member_ids,
            $sort
        );

        $datas = array_merge($fetched_data, $datas);

        return $this->_ajaxResponse($datas);

    }

    public function actionMarketingPoint()
    {
        $range = $this->_request->get('range', 'today');
        $page = $this->_request->get('page') ? intval($this->_request->get('page')) : 1;
        $sort = $this->_request->get('sort', 'total_point');
        $order = $this->_request->get('order', 'desc');
        $sref_member_id = intval($this->_request->get('sref_member_id'));
        $sref_group_id = intval($this->_request->get('sref_group_id'));
        $is_sref_report = ($sref_member_id < 1 && $sref_member_id < 1) ? 0 : 1;
        $view_mode = $this->_request->get('view_mode', 'total');

        $page_size = 15;

        $selectors = [];
        $action = 'marketingPoint';

        try {
            $selectors = OSC::helper('user/decentralization/common')->getSelectorsByLeader($action, $sref_member_id, $sref_group_id, $range);
        } catch (Exception $e) {
            static::redirect($this->getUrl('*/*/*'));
        }

        $member_ids = [];
        $get_data_sref = 0;

        if ($sref_group_id) {
            try {
                $member_ids = OSC::helper('adminGroup/common')->getMembersByGroup($sref_group_id);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
                static::redirect($this->getUrl('*/*/*'));
            }
            $get_data_sref = 1;

        } else if ($sref_member_id) {
            $list_member_mkt = OSC::helper('report/common')->getListMemberActiveAnalytic();
            $selector_data = OSC::helper('marketing/common')->getSelectorData($selectors, $member_ids, $action, $range);

            if (!count($list_member_mkt) && !in_array($sref_member_id, $selector_data['member_ids'])) {
                $this->addErrorMessage('List member marketing is empty or you are not permission to view marketing point of member #' . $sref_member_id . '.');
                static::redirect($this->getUrl('*/*/*'));
            }

            $member_ids = [$sref_member_id];
            $get_data_sref = 1;

        } else {
            $list_member_mkt = OSC::helper('report/common')->getListMemberActiveAnalytic();
            $member_ids = array_keys($list_member_mkt);

            // Update $selectors for leader of a group have a vendor but the vendor is not a sref
            $selector_data = OSC::helper('marketing/common')->getSelectorData($selectors, $member_ids, $action, $range);
            $member_ids = $selector_data['member_ids'];
            $selectors = $selector_data['selectors'];
        }

        $data = [
            'get_data_sref' => $get_data_sref,
            'is_sref_report' => $is_sref_report,
            'sref_member_id' => $sref_member_id,
            'sref_group_id' => $sref_group_id,
            'action' => $action,
            'selectors' => $selectors,
            'range' => $range,
            'view_mode' => $view_mode,
            'page' => $page,
            'page_size' => $page_size
        ];

        $template = 'report/marketingPoint/list';
        $fetched_data_title = null;
        $options = [
            'sort' => $sort,
            'order' => $order,
            'page_size' => $page_size
        ];

        if ($this->checkPermission('srefReport|srefReport/marketingPoint', false) && count($member_ids) > 0) {
            if ($view_mode == 'total') {
                $fetched_data = OSC::helper('marketing/common')->getReportGroupByUser($member_ids, $range, $page, $options);
            } else { // For view all items in mkt point table, currently not active this case
                $fetched_data = OSC::helper('marketing/common')->getReportDetail($member_ids, $range, $page);
            }

            $fetched_data_title = !empty($fetched_data) ? $fetched_data['title'] . ' / ' : null;

            $data['options'] = $options;
            $data = array_merge($fetched_data, $data);
        }

        $page_title = $this->getPageTitle($selectors, $sref_member_id, $sref_group_id);

        $this->getTemplate()
            ->addBreadcrumb('Marketing Point Analytics')
            ->setCurrentMenuItemKey('srefReport/marketingPoint')
            ->setPageTitle('Marketing Point :: ' . $fetched_data_title . '<span class="page-title-sref">' . $page_title . '</span>');
        $this->output($this->getTemplate()->build($template, $data));
    }
}
