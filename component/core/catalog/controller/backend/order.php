<?php

class Controller_Catalog_Backend_Order extends Abstract_Catalog_Controller_Backend {
    protected $_search_key = 'catalog/order/search';

    protected $_search_filter_field = 'search_filter_field';
    protected $_default_search_field_key = 'default_search_order_field';
    protected $_filter_field = [
        'all' => 'All field',
        'master_record_id' => 'Master record id',
        'list_product_id' => 'Product id',
        'email' => 'Email',
        'code' => 'Code',
        'cart_ukey' => 'Cart ukey',
        'shipping_full_name' => 'Shipping full name',
        'shipping_phone' => 'Shipping phone',
        'shipping_address' => 'Shipping address',
        'shipping_city' => 'Shipping city',
        'shipping_province' => 'Shipping province',
        'shipping_country' => 'Shipping country',
        'billing_full_name' => 'Billing full name',
        'billing_phone' => 'Billing phone',
        'billing_address' => 'Billing address',
        'billing_city' => 'Billing city',
        'billing_province' => 'Billing province',
        'billing_country' => 'Billing country'
    ];

    public function __construct() {
        parent::__construct();

        $this->checkPermission('catalog/super|catalog/order');

        $this->getTemplate()->setCurrentMenuItemKey('catalog_order')->resetBreadcrumb()->addBreadcrumb(array('user', 'Manage Orders'), $this->getUrl('catalog/backend_order/list'));
    }

    public function actionIndex() {
        $this->forward('*/*/list');
    }

    protected function _getFilterConfig($filter_value = null) {
        $shipping_methods = OSC::model('shipping/methods')->getCollection()->addField('shipping_name', 'shipping_key')->load();
        $shipping_method_options = [];
        foreach ($shipping_methods as $shipping_method) {
            $shipping_method_options[$shipping_method->data['shipping_key']] = $shipping_method->data['shipping_name'];
        }
        $filter_config = [
            'member_hold' => [
                'title' => 'Hold status',
                'type' => 'radio',
                'query_operator' => [
                    '0' => OSC_Database::OPERATOR_EQUAL,
                    '1' => OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL
                ],
                'data' => [
                    '1' => 'Hold',
                    '0' => 'Not hold',
                ],
                'field' => 'member_hold'
            ],
            'status' => [
                'title' => 'Status',
                'type' => 'radio',
                'data' => [
                    'open' => 'Open',
                    'archived' => 'Archived',
                    'cancelled' => 'Cancelled'
                ],
                'field' => 'order_status'
            ],
            'payment' => [
                'title' => 'Payment status',
                'type' => 'checkbox',
                'data' => [
                    'unpaid' => 'Unpaid',
                    'authorized' => 'Authorized',
                    'partially_paid' => 'Partially paid',
                    'paid' => 'Paid',
                    'partially_refunded' => 'Partially refunded',
                    'refunded' => 'Refunded'
                ],
                'field' => 'payment_status'
            ],
            'fulfillment' => [
                'title' => 'Fulfillment status',
                'type' => 'checkbox',
                'data' => [
                    'unfulfilled' => 'Unfulfilled',
                    'partially_fulfilled' => 'Partially fulfilled',
                    'fulfilled' => 'Fulfilled'
                ],
                'field' => 'fulfillment_status'
            ],
            'fraud' => [
                'title' => 'Fraud level',
                'type' => 'checkbox',
                'data' => [
                    'unknown' => 'Unknown',
                    'normal' => 'Normal risk',
                    'elevated' => 'Elevated risk',
                    'highest' => 'Highest risk'
                ],
                'field' => 'fraud_risk_level'
            ],
            'date' => [
                'title' => 'Added date',
                'type' => 'daterange',
                'field' => 'added_timestamp'
            ],
            'shipping_line' => [
                'title' => 'Shipping method',
                'type' => 'radio',
                'data' => $shipping_method_options,
                'field' => 'shipping_line'
            ],
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

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterConfig(), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet($this->_search_key, [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value,
            'filter_field' => $this->_request->get('filter_field')
        ], 60 * 60);

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    public function actionList() {
        $collection = OSC::model('catalog/order')->getCollection();
        $page = $this->_request->get('page', 1);
        $pageSize = 25;

        $search_collection = OSC::model('catalog/order')->getCollection();
        $search = OSC::sessionGet($this->_search_key);
        $is_search = $this->_request->get('search') && (!empty($search['keywords']) || !empty($search['filter_value']));
        if ($is_search) {
            $result = $this->_applySearchOrder($collection, $page, $pageSize);
            if (isset($result['list_id']) && !empty($result['list_id'])) {
                $data = OSC::model('catalog/order')->getCollection()->load($result['list_id']);
                foreach ($result['list_id'] as $id) {
                    $search_collection->addItem($data->getItemByPK($id));
                }
            }

            $search_page = $result['page'] ?? 0;
            $search_page_size = $result['page_size'] ?? 0;
            $search_total_item = $result['total_item'] ?? 0;
        }

        $this->_applyShopFilter($collection);

        $collection->sort('added_timestamp', OSC_Database::ORDER_DESC)
            ->setPageSize($pageSize)
            ->setCurrentPage($page)
            ->load();

        $this->getTemplate()->setPageTitle('Manage Orders');

        //Search order form
        $default_select_field = OSC::cookieGet($this->_default_search_field_key);
        if (empty($default_select_field)) {
            $default_select_field = array_key_first($this->_filter_field);
            OSC::cookieSet($this->_default_search_field_key, $default_select_field);
        }
        //End search order form
        $shipping_default = OSC::model('shipping/methods')->getCollection()->getShippingMethodDefault();

        $this->output(
            $this->getTemplate()->build(
                'catalog/order/list', [
                    'collection' => $is_search ? $search_collection : $collection,
                    'page' => $is_search ? $search_page : $collection->getCurrentPage(),
                    'page_size' => $is_search ? $search_page_size : $collection->getPageSize(),
                    'total_item' => $is_search ? $search_total_item : $collection->collectionLength(),
                    'search_keywords' => $collection->registry('search_keywords'),
                    'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                    'filter_config' => $this->_getFilterConfig($collection->registry('search_filter')),
                    'filter_field' => $this->_filter_field,
                    'selected_filter_field' => $collection->registry($this->_search_filter_field),
                    'default_search_field_key' => $this->_default_search_field_key,
                    'shipping_default' => $shipping_default
                ]
            )
        );
    }

    protected function _applyShopFilter(Model_Catalog_Order_Collection $collection) {
        if (OSC::getShop()->getId() > 0) {
            $collection->addCondition('shop_id', OSC::getShop()->getId(), OSC_Database::OPERATOR_EQUAL);
        }
    }

    protected function _applyListCondition(Model_Catalog_Order_Collection $collection): void {
        $search = OSC::sessionGet('catalog/order/search');

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword('id', 'master_record_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('code', 'code', OSC_Search_Analyzer::TYPE_STATE, true, false, ['validate' => function($value) {
                    return preg_match('/^[a-zA-Z0-9\-\_]*\d+[a-zA-Z0-9\-\_]*$/', $value);
                }])
                ->addKeyword('email', 'email', OSC_Search_Analyzer::TYPE_STATE, true, false, ['validate' => function($value) {
                    try {
                        OSC::core('validate')->validEmail($value);
                        return true;
                    } catch (Exception $ex) {
                        return false;
                    }
                }])
                ->addKeyword('sname', 'shipping_full_name', OSC_Search_Analyzer::TYPE_STRING, true)
                ->addKeyword('cart', 'cart_ukey', OSC_Search_Analyzer::TYPE_STRING, true)
                ->addKeyword('sadd1', 'shipping_address1', OSC_Search_Analyzer::TYPE_STRING, true)
                ->addKeyword('sadd2', 'shipping_address2', OSC_Search_Analyzer::TYPE_STRING, true)
                ->addKeyword('sphone', 'shipping_phone', OSC_Search_Analyzer::TYPE_STATE, true, false, ['validate' => function($value) {
                    return !preg_match('/[^0-9]/', $value);
                }])
                ->addKeyword('scity', 'shipping_city', OSC_Search_Analyzer::TYPE_STATE, true)
                ->addKeyword('sprovince', 'shipping_province', OSC_Search_Analyzer::TYPE_STATE, true)
                ->addKeyword('scountry', 'shipping_country', OSC_Search_Analyzer::TYPE_STATE, true)
                ->addKeyword('szip', 'shipping_zip', OSC_Search_Analyzer::TYPE_STATE, true)
                ->addKeyword('order_id', 'order_id', OSC_Search_Analyzer::TYPE_INT, true)
                ->parse($search['keywords']);

            $collection->setCondition($condition);

            $this->_applyFilter($collection, $this->_getFilterConfig(), $search['filter_value']);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    protected function _applySearchOrder(Model_Catalog_Order_Collection $collection, $page = 1, $pageSize = 25, $get_count = true) {
        $search = OSC::sessionGet($this->_search_key);

        if ($search) {
            $search_data = [];
            $keyword = $search['keywords'] ?? '';
            $filter_value = $search['filter_value'] ?? [];
            $filter_field = $search['filter_field'] ?? '';
            //$keyword = OSC::safeString($keyword);

            $search_data['shop_id'] = OSC::getShop()->getId();

            if (!empty(trim($keyword))) {
                $search_data['keywords'] = trim($keyword);
            }

            //Filter
            if (is_array($filter_value) && count($filter_value) > 0) {
                $filter_config = $this->_getFilterConfig();
                $mapping_search_data = [];

                foreach ($filter_value as $k_filter => $v_filter) {
                    if ($k_filter == 'date') {
                        preg_match('/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*\-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?\s*$/', $v_filter, $matches);
                        $start_timestamp = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);
                        if ($matches[5]) {
                            $end_timestamp = mktime(24, 0, 0, $matches[6], $matches[5], $matches[7]);
                        } else {
                            $end_timestamp = mktime(24, 0, 0, $matches[2], $matches[1], $matches[3]);
                        }
                        $added_timestamp = [
                            'start_at' => $start_timestamp,
                            'end_at' => $end_timestamp,
                        ];
                        $search_data['added_timestamp'] = $added_timestamp;
                        unset($filter_value['date']);
                        continue;
                    } elseif ($k_filter == 'member_hold' && $v_filter == 1) {
                        $search_data['member_hold'] = 1;
                        unset($filter_value['member_hold']);
                        continue;
                    }

                    if (isset($filter_config[$k_filter]['field']) && !empty($filter_config[$k_filter]['field'])) {
                        $mapping_search_data[$filter_config[$k_filter]['field']] = $search['filter_value'][$k_filter];
                    }
                }

                $search_data['filter_value'] = $mapping_search_data;
            }

            //Filter field
            if (!empty($filter_field)) {
                switch ($filter_field) {
                    case 'all':
                        $field = [];
                        break;
                    case 'shipping_address':
                        $field = ['shipping_address1', 'shipping_address2'];
                        break;
                    case 'billing_address':
                        $field = ['billing_address1', 'billing_address2'];
                        break;
                    default:
                        $field = [$filter_field];
                        break;
                }

                $search_data['field'] = $field;
            }

            $collection->register('search_keywords', $search['keywords'])
                ->register('search_filter', $search['filter_value'])
                ->register($this->_search_filter_field, $search['filter_field'])
                ->setCurrentPage($page)
                ->setPageSize($pageSize);

            return OSC::helper('catalog/search_order')->getSearchOrder($search_data, $page, $pageSize, $get_count);
        }
    }

    public function actionResyncFrequentlyBoughtTogether() {
        try {
            if (!$this->getAccount()->isRoot()) {
                throw new Exception('No permission');
            }

            OSC::core('cron')->addQueue('catalog/order_resyncFrequentlyBoughtTogether', null, ['requeue_limit' => -1, 'ukey' => 'catalog/order_resyncFrequentlyBoughtTogether']);
            $this->addMessage('Resync task has been appended to queue');
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
        }

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionResyncRedisGraph() {
        try {
            if (!$this->getAccount()->isRoot()) {
                throw new Exception('No permission');
            }

            OSC::core('cron')->addQueue('catalog/order_resyncRedisGraph', null, ['skip_realtime', 'requeue_limit' => -1, 'ukey' => 'catalog/order_resyncRedisGraph', 'estimate_time' => 60*60*5]);

            $this->addMessage("Resync Redis Graph has been appended to queue");
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
        }

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionViewHistoryCustomer() {
        try {
            $order = OSC::model('catalog/order')->load($this->_request->get('id'));
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
            static::redirect($this->getUrl('catalog/backend_order/detail', array('id' => $this->_request->get('id'))));
        }

        $condition = null;

        if ($this->_request->get('tracking_key') != '') {
            $condition = 'track_ukey="' . $this->_request->get('tracking_key') . '"';
        } elseif ($this->_request->get('ip') != '') {
            $condition = 'ip="' . $this->_request->get('ip') . '"';
        } else {
            $this->addErrorMessage('track_ukey or ip not found');
            static::redirect($this->getUrl('catalog/backend_order/detail', array('id' => $this->_request->get('id'))));
            return;
        }

        $info_list = OSC::model('frontend/tracking_footprint')->getCollection()->setCondition($condition)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $list_location = null;
        $list_client_info = null;
        $list_ip = null;
        $list_user_agent = null;

        foreach ($info_list as $model) {
            $list_ip[] = $model->data['ip'];
            $list_user_agent[] = $model->data['user_agent'];
        }

        $list_ip = array_unique($list_ip);
        $list_user_agent = array_unique($list_user_agent);

        foreach ($list_ip as $ip) {
            $location = OSC::helper('core/common')->getIPLocation($ip);
            $list_location[$ip] = ['country_name' => $location['country_name'], 'city' => $location['city']];
        }

        foreach ($list_user_agent as $user_agent) {
            $_client_info = new WhichBrowser\Parser($user_agent);
            $list_client_info[$user_agent] = ['os' => $_client_info->os->toString(), 'browser' => $_client_info->browser->toString()];
        }

        $this->getTemplate()->setCurrentMenuItemKey('catalog_order')->addBreadcrumb(array('user', 'Order #FT' . $order->getId()), $this->getUrl('catalog/backend_order/detail', array('id' => $order->getId())));

        $this->getTemplate()->setPageTitle('View history Customer');
        $output_html = $this->getTemplate()->build('catalog/order/view_history_customer', ['collection' => $info_list, 'order_id' => $order->getId(), 'list_location' => $list_location, 'list_client_info' => $list_client_info]);
        $this->output($output_html);
    }

    public function actionBrowse() {
        try {
            /* @var $collection Model_Catalog_Order_Collection */
            $collection = OSC::model('catalog/order')->getCollection();

            $this->_applyListCondition($collection, $this->_request->get('keywords'));

            $collection->setPageSize(25)
                ->setCurrentPage($this->_request->get('page'))
                ->load();

            $orders = [];

            /* @var $order Model_Catalog_Order */
            foreach ($collection as $order) {
                $orders[] = array(
                    'id' => $order->getId(),
                    'title' => $order->data['code'],
                    'email' => $order->data['email'],
                    'shipping_full_name' => $order->data['shipping_full_name'],
                    'url' => $order->getDetailUrl(),
                    'country_code' => $order->data['billing_country_code']
                );
            }

            $this->_ajaxResponse(array(
                'keywords' => [],
                'total' => $collection->collectionLength(),
                'offset' => (($collection->getCurrentPage() - 1) * $collection->getPageSize()) + $collection->length(),
                'current_page' => $collection->getCurrentPage(),
                'page_size' => $collection->getPageSize(),
                'items' => $orders
            ));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionBrowseOrderItem() {
        try {
            $order_id = $this->_request->get('order_id');
            $line_items = OSC::model('catalog/order_item')
                ->getCollection()
                ->addField('master_record_id', 'order_master_record_id', 'title', 'product_type')
                ->loadByOrderMasterRecordId($order_id);

            $item_data = [];
            foreach ($line_items as $line_item) {
                $item_data[] = [
                    'id' => $line_item->getId(),
                    'title' => $line_item->data['title'],
                    'product_type' => $line_item->data['product_type'],
                ];
            }

            $this->_ajaxResponse([
                'order_items' => $item_data,
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionResyncIndex() {
        if (!$this->getAccount()->isRoot()) {
            static::notFound();
        }

        $collection = OSC::model('catalog/order')->getCollection()->load();

        foreach ($collection as $order) {
            $order->updateIndex();
        }

        echo 'DONE';
    }
}