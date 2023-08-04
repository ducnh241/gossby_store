<?php

class Controller_D2_Backend_Product extends Abstract_Backend_Controller {

    protected $_d2_cron = [
        'create_record_airtable' => 'Create Record Airtable',
        'renderDesignSvgBeta' => 'Render Design Svg Beta',
        'sync_design_airtable' => 'Sync Design Airtable',
        'd2FlowReply' => 'Sync Flow',
        'retry_d2FlowReply' => 'Retry Sync Flow',
        'update_raw_airtable' => 'Update Raw Airtable'
    ];

    protected $_search_d2_product = '/d2/product';

    public function __construct() {
        parent::__construct();

        $this->getTemplate()
            ->setCurrentMenuItemKey('d2/product')
            ->setPageTitle('Manage Products');
    }

    public function actionIndex() {
        $this->forward('*/*/list');
    }

    public function actionList() {

        $this->getTemplate()->addBreadcrumb(array('product', 'D2'));

        $collection = OSC::model('d2/product')->getCollection();

        $page = $this->_request->get('page', 1);
        $pageSize = 25;

        if ($this->_request->get('search')) {
            $this->_applyConditionList($collection);
        }

        $collection
            ->sort('id', OSC_Database::ORDER_DESC)
            ->setPageSize($pageSize)
            ->setCurrentPage($page)
            ->load()->preLoadCampaignProduct();

        $this->output(
            $this->getTemplate()->build('d2/product/list',[
                'collection' => $collection,
                'search_keywords' => $collection->registry('search_keywords'),
                'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
            ])
        );
    }

    public function actionPost() {
        $product_ids = $this->_request->get('product_ids');

        $product_ids = array_filter(explode(' ', $product_ids));

        foreach ($product_ids as $key => $id) {
            if (intval($id) > 0) {
                $product_ids[$key] = intval($id);
            } else {
                unset($product_ids[$key]);
            }
        }

        $errors = [];

        foreach ($product_ids as $product_id) {

            $product = OSC::model('d2/product');

            try {
                $id = intval($this->_request->get('id'));
                if ( $id > 0) {
                    try {
                        $product->load($id);
                    } catch (Exception $ex) {
                        $error_msg = $ex->getCode() === 404 ? 'Id is not exist' : $ex->getMessage();
                        $errors[$product_id] = $error_msg;
                        continue;
                    }
                    $product->setData([
                        'product_id' => $product_id,
                        'modified_by' => $this->getAccount()->getId(),
                        'modified_timestamp' => time()
                    ])->save();
                } else {
                    $product->setData([
                        'product_id' => $product_id,
                        'added_by' => $this->getAccount()->getId(),
                        'modified_by' => $this->getAccount()->getId(),
                        'added_timestamp' => time(),
                        'modified_timestamp' => time()
                    ])->save();
                }
            } catch (Exception $ex) {
                $error_msg = strpos($ex->getMessage(), '1062 Duplicate entry') ? 'Product is exist' : $ex->getMessage();
                $errors[$product_id] = $error_msg;
            }

        }

        if (count($errors)) {
            $this->_ajaxError(OSC::encode($errors));
        }
        $this->_ajaxResponse();
    }

    public function actionDelete() {
        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            try {
                /* @var $product Model_D2_Product */
                $product = OSC::model('d2/product')->load($id);

                $product->delete();

                $this->addMessage('Deleted the D2 product with Product ID [' . $product->data['product_id'] . ']');
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionSearch() {

        $filter_value = $this->_request->get('filter');

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterConfig(), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet($this->_search_d2_product, [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value,
            'filter_field' => $this->_request->get('filter_field')
        ], 60 * 60);

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    protected function _applyConditionList(Model_D2_Product_Collection $collection): void {
        $search = OSC::sessionGet($this->_search_d2_product);

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword('product_id', 'product_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->parse($search['keywords']);

            $collection->setCondition($condition);

            $this->_applyFilter($collection, $this->_getFilterConfig(), $search['filter_value']);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    protected function _getFilterConfig($filter_value = null) {
        $filter_config = [
            'date' => [
                'title' => 'Added date',
                'type' => 'daterange',
                'field' => 'added_timestamp'
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

    public function actionBulkDelete() {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $product_ids = $this->_request->get('product_ids');

            $collection = OSC::model('d2/product')->getCollection();

            if (!is_array($product_ids)) {
                throw new Exception('Please select least a product to delete');
            }

            $collection->addCondition($collection->getPkFieldName(), array_unique($product_ids), OSC_Database::OPERATOR_IN);

            $collection->load();

            if ($collection->length() < 1) {
                throw new Exception('No product was found to delete');
            }

            try {
                $collection->delete();
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => 'Delete success product selected']);
    }

    public function actionListBulkQueue() {

        $this->getTemplate()->addBreadcrumb(array('user', 'D2 Progress Queue'));

        $collection = OSC::model('catalog/product_bulkQueue')->getCollection();

        $page = $this->_request->get('page', 1);
        $pageSize = 25;

        if ($this->_request->get('search')) {
            $this->_applyListConditionBulkQueue($collection);
        }

        $this->getTemplate()->setCurrentMenuItemKey('d2/progress_queue')->resetBreadcrumb()->addBreadcrumb(['magic-solid', 'D2 Progress Queue']);

        $collection
            ->addCondition('action', array_keys($this->_d2_cron), OSC_Database::OPERATOR_IN)
            ->sort('queue_id', OSC_Database::ORDER_DESC)
            ->setPageSize($pageSize)
            ->setCurrentPage($page)
            ->load();

        $this->output(
            $this->getTemplate()->build(
                'd2/product/bulkQueue', [
                    'collection' => $collection,
                    'search_keywords' => $collection->registry('search_keywords'),
                    'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                    'filter_config' => $this->_getFilterConfigListBulkQueue($collection->registry('search_filter'))
                ]
            )
        );
    }

    public function actionSearchListBulkQueue() {
        $filter_value = $this->_request->get('filter');

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterConfigListBulkQueue(), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet(
            'd2/queue/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
        ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/listBulkQueue', ['search' => 1]));
    }

    protected function _applyListConditionBulkQueue(Model_Catalog_Product_BulkQueue_Collection $collection): void {
        $search = OSC::sessionGet('d2/queue/search');

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword('queue_id', 'queue_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('action', 'action', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->parse($search['keywords']);

            $collection->setCondition($condition);

            $this->_applyFilter($collection, $this->_getFilterConfigListBulkQueue(), $search['filter_value']);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    protected function _getFilterConfigListBulkQueue($filter_value = null) {
        $filter_config = [
            'action' => [
                'title' => 'Action',
                'type' => 'checkbox',
                'data' => $this->_d2_cron,
                'field' => 'action'
            ],
            'status' => [
                'title' => 'Status',
                'type' => 'checkbox',
                'data' => [
                    '0' => 'Running',
                    '1' => 'Wait to run',
                    '2' => 'Error'
                ],
                'field' => 'queue_flag'
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

    public function actionDeleteCronProcessQueue() {

        $ids = $this->_request->get('queue_ids');

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $key => $id) {
            $id = intval($id);

            if ($id > 0) {
                $ids[$key] = $id;
            } else {
                unset($ids[$key]);
            }
        }

        try {
            $success = 0;
            $error_delete_queue = [];
            $collection = OSC::model('catalog/product_bulkQueue')->getCollection()->load($ids);

            /* @var Model_Catalog_Product_BulkQueue $model */
            foreach ($collection as $model) {
                if ($model->data['queue_flag'] == Model_Catalog_Product_BulkQueue::QUEUE_FLAG['error']) {
                    $model->delete();
                    $success++;
                } else {
                    $error_delete_queue[] = $model->getId();
                }
            }
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => ($success > 0 ?  "{$success} Cron has been deleted" : "") .
                (count($error_delete_queue) ? ("\n". count($error_delete_queue) ." Cron can not delete because queue is processing\nID: " . implode(",", $error_delete_queue)) : "")]);
        }
    }

    public function actionRecronProcessQueue() {

        $ids = $this->_request->get('queue_ids');

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $key => $id) {
            $id = intval($id);

            if ($id > 0) {
                $ids[$key] = $id;
            } else {
                unset($ids[$key]);
            }
        }

        try {
            $success = 0;
            $error_delete_queue = [];
            $collection = OSC::model('catalog/product_bulkQueue')->getCollection()->load($ids);

            /* @var Model_Catalog_Product_BulkQueue $model */
            foreach ($collection as $model) {
                if ($model->data['queue_flag'] == Model_Catalog_Product_BulkQueue::QUEUE_FLAG['error']) {
                    $model->setData([
                        'extra_data' => null,
                        'queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['queue'],
                        'error' => ''
                    ])->save();
                    $success++;
                } else {
                    $error_delete_queue[] = $model->getId();
                }
            }
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => ($success > 0 ?  "{$success} Cron has been ReCron" : "") .
                (count($error_delete_queue) ? ("\n". count($error_delete_queue) ." Cron can not ReCron because queue is processing\nID: " . implode(",", $error_delete_queue)) : "")]);
        }
    }
}
