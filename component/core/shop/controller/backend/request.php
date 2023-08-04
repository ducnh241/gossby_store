<?php

class Controller_Shop_Backend_Request extends Abstract_Backend_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->getTemplate()->setCurrentMenuItemKey('request')
            ->addBreadcrumb(['account', 'PayoutHistory'], $this->getUrl('shop/backend_request/list'));
    }

    protected function _getFilterConfig($filter_value = null)
    {
        $filter_config = [
            'status' => [
                'title' => 'Status',
                'type' => 'radio',
                'data' => [
                    'Pending' => 'Pending',
                    'Processing' => 'Processing',
                    'Done' => 'Done',
                    'Cancelled' => 'Cancelled',
                ],
                'field' => 'status'
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

    public function actionSearch()
    {
        $filter_value = $this->_request->get('filter');
        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterConfig(), $filter_value);
        } else {
            $filter_value = [];
        }
        OSC::sessionSet(
            'shop/account/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
        ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    protected function _applyListCondition(Model_Shop_History_Collection $collection): void
    {
        $search = OSC::sessionGet('shop/account/search');

        if ($search) {
            $keywords = trim($search['keywords']);

            $condition = [];
            $params = [];
            if (!empty($keywords)) {
                $condition_search = [
                    "payout_account_title LIKE :keywords"
                ];

                $condition[] = '(' . implode(' OR ', $condition_search) . ')';
                $params = array_merge($params, [
                    'keywords' => '%' . $keywords . '%'
                ]);
            }
            $filter_config = $this->_getFilterConfig();
            $filter_value = $search['filter_value'];

            if (count($filter_value) > 0) {
                foreach ($filter_value as $key => $value) {
                    if (!isset($filter_config[$key])) {
                        continue;
                    }

                    if (is_array($value) && count($value) == 1) {
                        $value = $value[0];
                    }

                    if (is_array($value)) {
                        foreach ($value as $v) {
                            $condition[] = $filter_config[$key]['field'] . " = :" . $filter_config[$key]['field'];
                            $params = array_merge($params, [
                                $filter_config[$key]['field'] => $v
                            ]);
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
                    } else {
                        $condition[] = $filter_config[$key]['field'] . " = :" . $filter_config[$key]['field'];
                        $params = array_merge($params, [
                            $filter_config[$key]['field'] => $value
                        ]);
                    }
                }
            }

            $condition = [
                'condition' => implode(' AND ', $condition),
                'params' => $params
            ];

            $collection->setCondition($condition);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    public function actionList()
    {
        $collection = OSC::model('shop/history')->getCollection();

        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }

        $collection->addCondition('shop_id', OSC::getShop()->getId(), OSC_Database::OPERATOR_EQUAL)
            ->sort('history_id', OSC_Database::ORDER_DESC)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $this->getTemplate()->setPageTitle('Payout History');
        $this->output($this->getTemplate()
            ->build('payoutAccount/request/list',
                [
                    'collection' => $collection,
                    'search_keywords' => $collection->registry('search_keywords'),
                    'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                    'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
                ]
            )
        );
    }

    public function actionPost()
    {
        $id = intval($this->_request->get('id'));
        $account_id = intval($this->_request->get('account_payout'));

        /* @var $model Model_PayoutAccount_History */
        $model = OSC::model('shop/history');

        if ($id > 0) {
            try {
                $model->load($id);
                if ($model->data['status'] != 'pending') {
                    static::redirect($this->getUrl('list'));
                }
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Request is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        }

        $collection_account = OSC::model('shop/account')->getCollection();

        if ($this->_request->get('submit_form')) {
            $data = [];

            $amount = OSC::helper('catalog/common')->floatToInteger(floatval($this->_request->get('amount')));

            $data['payout_account_id'] = $this->_request->get('payout_account_id');
            $data['history_type'] = 1;
            $data['amount'] = $amount;
            $data['status'] = 'pending';
            $data['shop_id'] = OSC::getShop()->getId();

            $account_info = OSC::model('shop/account')->load($this->_request->get('payout_account_id'));
            $data['payout_account_title'] = $account_info->data['title'];

            try {

                $model->setData($data)->save();

                OSC::helper('shop/common')->updateProfit($amount, 'request_payout');

                $message = 'Request payout:Request [#' . $model->getId() . '] "' . '" added';
                $this->addMessage($message);

                static::redirect($this->getUrl('list'));

            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $list_acc = $collection_account->addCondition('activated_flag', 1, OSC_Database::OPERATOR_EQUAL)->sort('account_id', OSC_Database::ORDER_DESC)->load();

        $output_html = $this->getTemplate()->build('payoutAccount/request/postForm', array(
            'model' => $model,
            'list_acc' => $list_acc,
            'choose_acc' => $account_id,
        ));

        $this->output($output_html);
    }

    public function actionCancel()
    {
        $id = intval($this->_request->get('id'));
        /* @var $model Model_PayoutAccount_History */
        $model = OSC::model('shop/history');

        if ($id > 0) {
            try {
                $model->load($id);
                if ($model->data['status'] != 'pending') {
                    $message = 'Request payout:Request [#' . $model->getId() . '] "' . '" not Pending';
                    $this->addMessage($message);
                    static::redirect($this->getUrl('list'));
                }
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Post is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }

            $data = [];
            $data['status'] = 'cancelled';
            $data['cancel_type'] = 'From user side';
            try {

                $model->setData($data)->save();
                $amount = OSC::helper('catalog/common')->floatToInteger(floatval($this->_request->get('amount')));

                OSC::helper('shop/common')->updateProfit($amount, 'cancel_payout');

                $message = 'Request payout:Request [#' . $model->getId() . '] "' . '" Cancel';
                $this->addMessage($message);

                static::redirect($this->getUrl('list'));

            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

    }
}
