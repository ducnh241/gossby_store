<?php

class Controller_Shop_Profit extends Abstract_Backend_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->checkPermission('shop');

    }

    protected function _applyListCondition(Model_Shop_Profit_Collection $collection): void
    {
        $search = OSC::sessionGet('shop/profit/search');

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword('id', 'id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('order_id', 'order_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('order_payment_status', 'order_payment_status', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->parse($search['keywords']);

            $collection->setCondition($condition);

            $this->_applyFilter($collection, $this->_getFilterConfig(), $search['filter_value']);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    protected function _getFilterConfig($filter_value = null)
    {
        $filter_config = [
            'date' => [
                'title' => 'Added Date',
                'type' => 'daterange',
                'field' => 'added_timestamp'
            ],
            'action' => [
                'title' => 'Action',
                'type' => 'radio',
                'data' => [
                    'paid' => 'Order Paid',
                    'partially_refunded' => 'Order Partially Refunded',
                    'refunded' => 'Order Refunded',
                    'cancelled' => 'Order Cancelled'
                ],
                'field' => 'action'
            ],
            'current_tier' => [
                'title' => 'Tier',
                'type' => 'radio',
                'data' => [
                    'bronze' => 'Bronze',
                    'silver' => 'Silver',
                    'gold' => 'Gold',
                    'diamond' => 'Diamond'
                ],
                'field' => 'current_tier'
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

    public function actionIndex()
    {
        $tpl = $this->getTemplate();
        $tpl->setCurrentMenuItemKey('shop_payout/dashboard')
            ->addBreadcrumb('Payout Dashboard', $this->getUrl('*/*/*'));
        $tpl->setPageTitle('Payout Dashboard');

        $shop_id = OSC::getShop()->getId();

        $collection = OSC::model('shop/history')->getCollection();
        $collection->sort('history_id', OSC_Database::ORDER_DESC)
            ->addCondition('shop_id', $shop_id, OSC_Database::OPERATOR_EQUAL)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $shop_data = OSC::model('shop/shop')->load($shop_id);

        $payout_accounts = OSC::model('shop/account')->getCollection()
            ->addField('title', 'account_info', 'account_type', 'default_flag')
            ->addCondition('shop_id', $shop_id, OSC_Database::OPERATOR_EQUAL)
            ->addCondition('activated_flag', 1, OSC_Database::OPERATOR_EQUAL)
            ->load();

        $accounts_data = [];
        if ($payout_accounts->length() > 0) {
            foreach ($payout_accounts as $account) {
                if($account->data['default_flag'] == 1){
                    $accounts_data[$account->data['account_type']][] = $account->data['account_info']['email'];
                }
                $account_data_history[$account->getId()] = [
                    'account_type' => $account->data['account_type'],
                    'account_email' => $account->data['account_info']['email']
                ];
            }
        }
        $status_arr = OSC::model('shop/history')->getStatusArray();

        $this->output($tpl->build('shop/profit/shop_profit', [
            'collection' => $collection,
            'shop_data' => $shop_data,
            'accounts_data' => $accounts_data,
            'status_arr' => $status_arr,
            'account_data_history' => $account_data_history,
        ]));
    }

    public function actionSearch()
    {
        $filter_value = $this->_request->get('filter');

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterConfig(), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet(
            'shop/profit/search',
            [
                'keywords' => $this->_request->get('keywords'),
                'filter_value' => $filter_value
            ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    public function actionList()
    {

        $this->getTemplate()->setCurrentMenuItemKey('shop_payout/list')
            ->addBreadcrumb('Profit List', $this->getUrl('*/*/list'));
        $this->getTemplate()->setPageTitle('Profit List');

        $shop_id = OSC::getShop()->getId();

        $collection = OSC::model('shop/profit')->getCollection();

        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }

        $collection->sort('id', OSC_Database::ORDER_DESC)
            ->addCondition('shop_id', $shop_id, OSC_Database::OPERATOR_EQUAL)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $shop_data = OSC::model('shop/shop')->load($shop_id);

        $this->output($this->getTemplate()
            ->build(
            'shop/profit/list', [
            'collection' => $collection,
            'shop_data' => $shop_data,
            'filter_config' => $this->_getFilterConfig($collection->registry('search_filter')),
            'search_keywords' => $collection->registry('search_keywords'),
            'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
        ]));
    }

}