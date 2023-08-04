<?php

class Controller_Catalog_Backend_ProcessOrder extends Abstract_Catalog_Controller_Backend{

    public function __construct() {
        parent::__construct();

        $this->checkPermission('catalog/super|catalog/order');

        $this->getTemplate()->setCurrentMenuItemKey('catalog_order')->addBreadcrumb(array('user', 'Process list'), $this->getUrl('catalog/backend_processOrder/list'));
    }

    public function actionList() {
        $collection = OSC::model('catalog/order_processV2')->getCollection();

        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }


        $collection->sort('queue_flag', OSC_Database::ORDER_ASC)
            ->sort('added_timestamp', OSC_Database::ORDER_DESC)
            ->setPageSize(100)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $this->output($this->getTemplate()->build('catalog/order/list_process', array(
            'collection' => $collection,
            'search_keywords' => $collection->registry('search_keywords'),
            'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
            'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
        )));
    }

    protected function _applyListCondition(Model_Catalog_Order_ProcessV2_Collection $collection): void {
        $search = OSC::sessionGet('process_order/list/search');

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword('id', 'record_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('ukey', 'ukey', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('member_id', 'member_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('service', 'service', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('order_id', 'order_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('line_items', 'line_items', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('queue_flag', 'queue_flag', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('error_message', 'error_message', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('quantity', 'quantity', OSC_Search_Analyzer::TYPE_INT, true, true)
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

    public function actionSearch() {
        $filter_value = $this->_request->get('filter');

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterConfig(), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet(
            'process_order/list/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
        ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    public function actionRequeue() {
        $ids = $this->_request->get('id');

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $ids = array_map(function($id) {
            return intval($id);
        }, $ids);
        $ids = array_filter($ids, function($id) {
            return $id > 0;
        });

        if (count($ids) < 1) {
            $this->error('No queue ID was found to requeue');
        }

        /* @var $DB OSC_Database */

        try {
            OSC::core('database')->getAdapter('db_master')->update(
                'catalog_order_process_v2',
                [
                    'queue_flag' => 0,
                    'error_message' => ''
                ],
                'FIND_IN_SET(record_id,\'' . implode(',', array_unique($ids)) . '\')'
            );
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Queues has been recroned']);
        }

        static::redirect($this->getUrl('*/*/list'));
    }
}
