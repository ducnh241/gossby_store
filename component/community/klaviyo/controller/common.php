<?php

class Controller_Klaviyo_Common extends Abstract_Backend_Controller{

    public function __construct() {
        parent::__construct();

        $this->getTemplate()
            ->setCurrentMenuItemKey('catalog_order/klaviyo')
            ->setPageTitle('Klaviyo List')
            ->addBreadcrumb(array('user', 'Klaviyo list'), $this->getUrl('klaviyo/common/list'));
    }

    public function actionList(){
        $collection = OSC::model('klaviyo/item')->getCollection();

        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }


        $collection->sort('added_timestamp', OSC_Database::ORDER_ASC)
            ->setPageSize(100)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $this->output($this->getTemplate()->build('klaviyo/list', array(
            'collection' => $collection,
            'search_keywords' => $collection->registry('search_keywords'),
            'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
            'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
        )));
    }

    protected function _applyListCondition(Model_Klaviyo_Item_Collection $collection): void {
        $search = OSC::sessionGet('klaviyo/list/search');

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword('id', 'record_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('queue_flag', 'queue_flag', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('error_message', 'error_message', OSC_Search_Analyzer::TYPE_STRING, true, true)
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
            'klaviyo/list/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
        ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    public function actionRecron() {
        OSC::core('cron')->addQueue('klaviyo/push', null, ['ukey' => 'klaviyo/push', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60*60]);
        $this->addMessage('Add cron queue success');
        static::redirect($this->getUrl('*/*/list'));
    }
    public function actionRequeue() {
        $DB = OSC::core('database');
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

        try {
            foreach ($ids as $id){

                $DB->query('SELECT * FROM ' . OSC::systemRegistry('db_prefix') . Helper_Klaviyo_Common::TBL_QUEUE_NAME . ' WHERE record_id = '.$id.'  LIMIT 1', null, 'fetch_queue');

                $row = $DB->fetchArray('fetch_queue');

                $DB->free('fetch_queue');

                $DB->update(Helper_Klaviyo_Common::TBL_QUEUE_NAME, ['queue_flag' => 0 , 'error_message' => ''], 'FIND_IN_SET(record_id,\'' . $id . '\')');
            }

            OSC::core('cron')->addQueue('klaviyo/push', null, ['ukey' => 'klaviyo/push', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60*60]);

        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Queues has been recroned']);
        }

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionDelete() {
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
            $this->error('No queue ID was found to delete');
        }

        /* @var $DB OSC_Database */

        try {
            OSC::core('database')->delete(Helper_Klaviyo_Common::TBL_QUEUE_NAME, 'FIND_IN_SET(record_id,\'' . implode(',', array_unique($ids)) . '\')');
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Queues has been deleted']);
        }

        static::redirect($this->getUrl('*/*/list'));
    }
}
