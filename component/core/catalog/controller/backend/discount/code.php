<?php

class Controller_Catalog_Backend_Discount_Code extends Abstract_Catalog_Controller_Backend {

    public function __construct() {
        parent::__construct();
        
        $this->checkPermission('catalog/super|catalog/discount');

        $this->getTemplate()
            ->setCurrentMenuItemKey('catalog_discount')
            ->resetBreadcrumb()
            ->setPageTitle('Manage Discount Codes');
        $this->getTemplate()->push('catalog/discount.js', 'js');
    }

    public function actionIndex() {
        $this->forward('*/*/list');
    }

    public function actionList() {
        $collection = OSC::model('catalog/discount_code')->getCollection();
        $this->getTemplate()->addBreadcrumb(array('badget-percent-solid', 'Discounts'));

        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }

        $collection->setPageSize(25)
                ->setCurrentPage($this->_request->get('page'))
                ->addCondition('auto_generated', 0)
                ->sort('deactive_timestamp = 0', OSC_Database::ORDER_DESC)
                ->sort('deactive_timestamp', OSC_Database::ORDER_DESC)
                ->sort('added_timestamp', OSC_Database::ORDER_DESC)
                ->load();

        $this->output($this->getTemplate()->build('catalog/discount/code/list', [
            'collection' => $collection,
            'search_keywords' => $collection->registry('search_keywords'),
            'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
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
            'discount_code/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
        ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    protected function _applyListCondition(Model_Catalog_Discount_Code_Collection $collection): void
    {
        $search = OSC::sessionGet('discount_code/search');

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');
            $condition = $search_analyzer->addKeyword('discount_code', 'discount_code', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->parse($search['keywords']);

            $collection->setCondition($condition);

            $filter_config = $this->_getFilterConfig();
            $filter_value = $search['filter_value'];

            $status = '';
            if (isset($search['filter_value']['status'])) {
                if ($search['filter_value']['status'] === 'expired') {
                    $and_clause_idx = OSC::makeUniqid();
                    $collection->addClause($and_clause_idx, OSC_Database::RELATION_AND, 'search_analyze_clause_0')
                        ->addCondition('deactive_timestamp', 0, OSC_Database::OPERATOR_GREATER_THAN,OSC_Database::RELATION_AND, $and_clause_idx)
                        ->addCondition('deactive_timestamp', time(), OSC_Database::OPERATOR_LESS_THAN, OSC_Database::RELATION_AND, $and_clause_idx);
                } elseif ($search['filter_value']['status'] === 'scheduled') {
                    $and_clause_idx = OSC::makeUniqid();
                    $collection->addClause($and_clause_idx, OSC_Database::RELATION_AND, 'search_analyze_clause_0')
                        ->addCondition('active_timestamp', time(), OSC_Database::OPERATOR_GREATER_THAN);
                } elseif ($search['filter_value']['status'] === 'active') {
                    $and_clause_idx = OSC::makeUniqid();
                    $or_clause_idx = OSC::makeUniqid();
                    $collection->addClause($and_clause_idx, OSC_Database::RELATION_AND, 'search_analyze_clause_0')
                        ->addCondition('deactive_timestamp', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND, $and_clause_idx)
                        ->addClause($or_clause_idx, OSC_Database::RELATION_OR, $and_clause_idx)
                        ->addCondition('active_timestamp', time(), OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL, OSC_Database::RELATION_AND, $or_clause_idx)
                        ->addCondition('deactive_timestamp', time(), OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL, OSC_Database::RELATION_AND, $or_clause_idx);
                }
                $status = $search['filter_value']['status'];
                unset($search['filter_value']['status']);
            }

            unset($filter_config['status']);

            if (count($filter_value) > 0) {
                foreach ($filter_value as $key => $value) {
                    if (is_array($value)) {
                        if ($filter_config[$key]['type'] == 'range' && $key == 'percent') {
                            $and_clause_idx = OSC::makeUniqid();
                            if (isset($value['time']) && !empty($value['time'])) {
                                preg_match('/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*\-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?\s*$/', $value['time'], $matches);

                                $start_timestamp = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);

                                if ($matches[5]) {
                                    $end_timestamp = mktime(24, 0, 0, $matches[6], $matches[5], $matches[7]);
                                } else {
                                    $end_timestamp = mktime(24, 0, 0, $matches[2], $matches[1], $matches[3]);
                                }

                                $collection->addClause($and_clause_idx, OSC_Database::RELATION_AND, 'search_analyze_clause_0')
                                    ->addCondition('added_timestamp', $start_timestamp, OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL,OSC_Database::RELATION_AND);
                                $collection->addClause($and_clause_idx, OSC_Database::RELATION_AND, 'search_analyze_clause_0')
                                    ->addCondition('added_timestamp', $end_timestamp, OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL,OSC_Database::RELATION_AND);
                            } else {
                                if (isset($value['min']) && !empty($value['min'])) {
                                    $collection->addClause($and_clause_idx, OSC_Database::RELATION_AND, 'search_analyze_clause_0')
                                        ->addCondition($filter_config[$key]['field'], $value['min'], OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL,OSC_Database::RELATION_AND);
                                }
                                if (isset($value['max']) && !empty($value['max'])) {
                                    $collection->addClause($and_clause_idx, OSC_Database::RELATION_AND, 'search_analyze_clause_0')
                                        ->addCondition($filter_config[$key]['field'], $value['max'], OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL,OSC_Database::RELATION_AND);
                                }
                            }
                        }
                    }
                }
            }
            unset($filter_config['percent']);

            $this->_applyFilter($collection, $filter_config, $search['filter_value']);

            if ($status) {
                $search['filter_value']['status'] = $status;
            }
            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    protected function _getFilterConfig($filter_value = null)
    {
        $filter_config = [
            'status' => [
                'title' => 'Status',
                'type' => 'radio',
                'data' => [
                    'active' => 'Active',
                    'expired' => 'Expired',
                    'scheduled' => 'Scheduled',
                ],
                'field' => 'status'
            ],
            'percent' => [
                'title' => 'Percent',
                'type' => 'range',
                'field' => 'discount_value',
                'data' => [
                    'min' => 'Min',
                    'max' => 'Max',
                ]
            ],
            'date' => [
                'title' => 'Added date',
                'type' => 'daterange',
                'field' => 'added_timestamp',
                'prefix' => 'Added date'
            ],
            'adate' => [
                'title' => 'Start date',
                'type' => 'daterange',
                'field' => 'active_timestamp',
                'prefix' => 'Start date'
            ],
            'ddate' => [
                'title' => 'End date',
                'type' => 'daterange',
                'field' => 'deactive_timestamp',
                'prefix' => 'End date'
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

    public function actionPost() {
        $id = intval($this->_request->get('id'));
        
        $this->checkPermission('catalog/super|catalog/discount/' . ($id > 0 ? 'edit' : 'add'));

        /* @var $model Model_Catalog_Discount_Code */
        $model = OSC::model('catalog/discount_code');

        if ($id > 0) {
            $this->getTemplate()->addBreadcrumb(array('badget-percent-solid', 'Edit Discount'));
            try {
                $model->load($id);

                if ($model->data['auto_generated'] == 1) {
                    throw new Exception('You unable to edit auto generated discount code');
                }
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Discount code is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        } else {
            $this->getTemplate()->addBreadcrumb(array('badget-percent-solid', 'Create Discount'));
        }

        if ($this->_request->get('discount_code')) {
            $data = ['auto_generated' => 0];

            $data['prerequisite_type'] = $this->_request->get('prerequisite_type');
            if ($id == 0) {
                $data['discount_code'] = $this->_request->get('discount_code');
            }
            $data['discount_type'] = $this->_request->get('discount_type', '');
            $data['maximum_amount'] = $this->_request->get('maximum_amount', 0);
            $data['usage_limit'] = $this->_request->get('usage_limit', 0);
            $data['once_per_customer'] = $this->_request->get('once_per_customer', 0);
            $data['auto_apply'] = $this->_request->get('auto_apply', 0);
            $data['prerequisite_customer_id'] = $this->_request->get('prerequisite_customer_id', []);
            $data['prerequisite_customer_group'] = $this->_request->get('prerequisite_customer_group', []);

            if (in_array($data['discount_type'], ['percent', 'fixed_amount'], true)) {
                $data['discount_value'] = $this->_request->get('discount_value', 0);
            }

            if (in_array($data['discount_type'], ['bxgy'], true)) {
                $data['entitled_product_id'] = $this->_request->get('entitled_product_id', []);
                $data['entitled_variant_id'] = $this->_request->get('entitled_variant_id', []);
                $data['entitled_collection_id'] = $this->_request->get('entitled_collection_id', []);
                $data['bxgy_prerequisite_quantity'] = $this->_request->get('bxgy_prerequisite_quantity', 1);
                $data['bxgy_entitled_quantity'] = $this->_request->get('bxgy_entitled_quantity', 1);
                $data['bxgy_discount_rate'] = $this->_request->get('bxgy_discount_rate', 100);
                $data['bxgy_allocation_limit'] = $this->_request->get('bxgy_allocation_limit', 0);
            }

            if (in_array($data['discount_type'], ['bxgy', 'percent', 'fixed_amount'], true)) {
                $data['prerequisite_product_id'] = $this->_request->get('prerequisite_product_id', []);
                $data['prerequisite_variant_id'] = $this->_request->get('prerequisite_variant_id', []);
                $data['prerequisite_collection_id'] = $this->_request->get('prerequisite_collection_id', []);
            }

            if (in_array($data['discount_type'], ['free_shipping', 'percent', 'fixed_amount'], true)) {
                $data['prerequisite_subtotal'] = $this->_request->get('prerequisite_subtotal', 0);
                $data['prerequisite_quantity'] = $this->_request->get('prerequisite_quantity', 0);
            }

            if (in_array($data['discount_type'], ['free_shipping'], true)) {
                $data['prerequisite_country_code'] = $this->_request->get('prerequisite_country_code', []);
                $data['prerequisite_shipping_rate'] = $this->_request->get('prerequisite_shipping_rate', 0);
            }

            if (in_array($data['discount_type'], [ 'fixed_amount'], true)) {
                $data['max_item_allow'] = $this->_request->get('max_item_allow');
                $data['prerequisite_shipping'] = $this->_request->get('prerequisite_shipping');

            }

            $date_time_data = [
                'active_timestamp' => [
                    'date' => $this->_request->get('active_date') == null ? 0 : $this->_request->get('active_date'),
                    'time' => $this->_request->get('active_time') == null ? 0 : $this->_request->get('active_time')],
                'deactive_timestamp' => [
                    'date' => $this->_request->get('deactive_date') == null ? 0 : $this->_request->get('deactive_date'),
                    'time' => $this->_request->get('deactive_time')== null ? 0 : $this->_request->get('deactive_time')
                ]
            ];



            foreach ($date_time_data as $timestamp_key => $date_time) {
                if ( $date_time['date'] == 0 &&  $date_time['time'] == 0) {
                    $data[$timestamp_key] = 0;

                }else{
                    $date_time['date'] = explode('/', $date_time['date']);
                    $date_time['time'] = explode(':', $date_time['time']);

                    for ($i = 0; $i < 3; $i ++) {
                        if (!isset($date_time['date'][$i])) {
                            $date_time['date'][$i] = 0;
                        } else {
                            $date_time['date'][$i] = abs(intval($date_time['date'][$i]));
                        }
                    }

                    for ($i = 0; $i < 2; $i ++) {
                        if (!isset($date_time['time'][$i])) {
                            $date_time['time'][$i] = 0;
                        } else {
                            $date_time['time'][$i] = abs(intval($date_time['time'][$i]));
                        }
                    }

                    $timestamp = mktime($date_time['time'][0], $date_time['time'][1], 0, $date_time['date'][1], $date_time['date'][0], $date_time['date'][2]);

                    if ($timestamp === false || $timestamp < 0) {
                        $timestamp = 0;
                    }

                    $data[$timestamp_key] = $timestamp;
                }
            }

            try {
                if ($id <= 0) {
                    $data['member_id'] = $this->getAccount()->getId();
                }
                $model->setData($data)->save();

                if ($id > 0) {
                    $message = 'Discount code #' . $model->getId() . ' updated';
                } else {
                    $message = 'Discount code [#' . $model->getId() . '] "' . $model->data['discount_code'] . '" added';
                }

                $this->addMessage($message);

                if (!$this->_request->get('continue')) {
                    static::redirect($this->getUrl('list'));
                } else {
                    static::redirect($this->getUrl(null, array('id' => $model->getId())));
                }
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $output_html = $this->getTemplate()->build('catalog/discount/code/postForm', array(
            'form_title' => $model->getId() > 0 ? ('Edit discount code #' . $model->getId() . ': ' . $model->getData('discount_code', true)) : 'Add new discount code',
            'model' => $model,
            'shipping_methods' => OSC::model('shipping/methods')->getCollection()->addCondition('shipping_status', Model_Shipping_Methods::STATUS_SHIPPING_METHOD_ON)->load()->toArray(),
        ));

        $this->output($output_html);
    }

    public function actionDelete() {
        $id = intval($this->_request->get('id'));
        
        $this->checkPermission('catalog/super|catalog/discount/delete');

        if ($id > 0) {
            /* @var $model Model_Catalog_Discount_Code */
            $model = OSC::model('catalog/discount_code');

            try {
                $model->load($id);
                $model->delete();
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    $this->addErrorMessage($ex->getMessage());
                    static::redirect($this->getUrl('list'));
                }
            }

            $this->addMessage('Deleted the discount code "' . $model->data['discount_code'] . '"');
        }

        static::redirect($this->getUrl('list'));
    }

}
