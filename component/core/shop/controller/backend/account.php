<?php

class Controller_Shop_Backend_Account extends Abstract_Backend_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('shop');

        $this->getTemplate()->setCurrentMenuItemKey('shop_payout/accounts')
            ->addBreadcrumb(['account', 'Manage Payout Accounts'], $this->getUrl('shop/backend_account/list'));
    }

    protected function _getFilterConfig($filter_value = null)
    {
        $filter_config = [
            'active' => [
                'title' => 'Active Flag',
                'type' => 'radio',
                'data' => [
                    '1' => 'Yes',
                    '0' => 'No'
                ],
                'field' => 'activated_flag'
            ],
            'defaultflg' => [
                'title' => 'Default Flag',
                'type' => 'radio',
                'data' => [
                    '1' => 'Yes',
                    '0' => 'No'
                ],
                'field' => 'default_flag'
            ],
            'account_type' => [
                'title' => 'Payment Provider',
                'type' => 'radio',
                'data' => [
                    'payoneer' => 'Payoneer',
                    'pingpong' => 'Pingpong'
                ],
                'field' => 'account_type'
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

    protected function _applyListCondition(Model_Shop_Account_Collection $collection): void
    {
        $search = OSC::sessionGet('shop/account/search');

        if ($search) {

            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword('title', 'title', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('account_info', 'account_info', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->parse($search['keywords']);

            $collection->setCondition($condition);

            $this->_applyFilter($collection, $this->_getFilterConfig(), $search['filter_value']);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    public function actionList()
    {

        $collection = OSC::model('shop/account')->getCollection();

        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }

        $collection->addCondition('shop_id', OSC::getShop()->getId(), OSC_Database::OPERATOR_EQUAL)
            ->sort('account_id', OSC_Database::ORDER_DESC)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $this->getTemplate()->setPageTitle('Manage Payout Accounts');
        $this->output($this->getTemplate()
            ->build('payoutAccount/list',
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

        $this->checkPermission('shop/account/' . ($id > 0 ? 'edit' : 'add'));

        /* @var $model Model_PayoutAccount_Account */
        $model = OSC::model('shop/account');

        if ($id > 0) {
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Post is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        }

        if ($this->_request->get('submit_form')) {
            $error = false;
            $data = [];

            $data['title'] = $this->_request->get('title');
            $data['account_type'] = $this->_request->get('account_type');
            $data['activated_flag'] = !empty($this->_request->get('activated_flag')) ? $this->_request->get('activated_flag') : 0;
            $data['default_flag'] = !empty($this->_request->get('default_flag')) ? $this->_request->get('default_flag') : 0;
            $data['shop_id'] = OSC::getShop()->getId();
            $data['account_info'] = [];
            if ($this->_request->get('email')) {
                $data['account_info']['email'] = $this->_request->get('email');
            }

            try {
                if (!$error) {
                    $model->setData($data)->save();

                    if ($id > 0) {
                        $message = 'Profit Payout: Account "' . $model->data['title'] . '" updated';
                    } else {
                        $message = 'Profit Payout: Account "' . $model->data['title'] . '" added';
                    }

                    $this->addMessage($message);

                    if (!$this->_request->get('continue')) {
                        static::redirect($this->getUrl('list'));
                    } else {
                        static::redirect($this->getUrl(null, array('id' => $model->getId())));
                    }
                }
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $output_html = $this->getTemplate()->build('payoutAccount/postForm', array(
            'model' => $model,
        ));

        $this->output($output_html);
    }

    public function actionDelete()
    {
        $this->checkPermission('shop/account/delete');
        $id = intval($this->_request->get('id'));
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        if ($id > 0) {
            /* @var $model Model_PayoutAccount_Account */
            $model = OSC::model('shop/account');

            $DB->begin();
            $locked_key = OSC::makeUniqid();
            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                $model->load($id);
                $model->delete();

                $DB->commit();
                OSC_Database_Model::unlockPreLoadedModel($locked_key);
            } catch (Exception $ex) {
                $DB->rollback();
                OSC_Database_Model::unlockPreLoadedModel($locked_key);
                if ($ex->getCode() != 404) {
                    $this->addErrorMessage($ex->getMessage());
                    static::redirect($this->getUrl('list'));
                }
            }

            $this->addMessage('Profit Payout: Deleted the account "' . $model->data['title'] . '"');
        }

        static::redirect($this->getUrl('list'));
    }

    public function actionBulkDelete()
    {
        $this->checkPermission('shop/account/delete/bulk');

        $ids = $this->_request->get('ids');
        if (!is_array($ids) || !$ids) {
            $ids = [];
        }
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();
        $locked_key = OSC::makeUniqid();
        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            $posts = OSC::model('shop/account')->getCollection();

            if (count($ids) < 1) {
                throw new Exception('Please select least a account to delete');
            }

            $ids = array_map(function ($id) {
                return intval($id);
            }, $ids);

            $ids = array_filter($ids, function ($id) {
                return $id > 0;
            });

            $posts->load($ids)->delete();

            $DB->commit();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);
        } catch (Exception $ex) {
            $DB->rollback();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => 'Delete success account selected']);
    }

    public function actionGetListAccount()
    {
        $type = $this->_request->get('type');

        $list_acc = OSC::model('shop/account')->getCollection()
            ->addCondition('shop_id', OSC::getShop()->getId(), OSC_Database::OPERATOR_EQUAL)
            ->addCondition('account_type', $type, OSC_Database::OPERATOR_EQUAL)
            ->addCondition('activated_flag', 1, OSC_Database::OPERATOR_EQUAL)
            ->load();
        $response_list_account = [];
        foreach ($list_acc as $account) {
            array_push($response_list_account, $account);
        }
        $this->_ajaxResponse(['list_account' => $response_list_account]);
    }

}
