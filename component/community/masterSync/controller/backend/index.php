<?php

class Controller_MasterSync_Backend_Index extends Abstract_Backend_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (OSC::isPrimaryStore()) {
            $this->checkPermission('developer/master_sync');
        } else {
            if (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) != 1) {
                static::notFound();
            }
        }
    }

    protected function _applyListCondition(Model_MasterSync_Queue_Collection $collection): void
    {
        $search = OSC::sessionGet('masterSync/queue/search');

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword('sync_key', 'sync_key', OSC_Search_Analyzer::TYPE_STRING, true, true)
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

    public function actionSearch()
    {
        $filter_value = $this->_request->get('filter');

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterConfig(), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet(
            'masterSync/queue/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
        ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    public function actionList()
    {
        $this->getTemplate()
            ->setCurrentMenuItemKey('developer/developer')
            ->resetBreadcrumb()
            ->addBreadcrumb('Master Sync', $this->getUrl('*/*/*'))
            ->setPageTitle('Developer');

        $collection = OSC::model('masterSync/queue')->getCollection();

        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }
        $collection->sort('added_timestamp', OSC_Database::ORDER_DESC)
            ->setPageSize(50)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $this->output($this->getTemplate()->build('masterSync/queue/list', array(
            'collection' => $collection,
            'search_keywords' => $collection->registry('search_keywords'),
            'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
            'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
        )));
    }

    public function actionInfo()
    {
        $id = intval($this->_request->get('id'));

        if ($id < 1) {
            echo 'Not found';
            die;
        }

        try {
            /* @var $model Model_MasterSync_Queue */
            $model = OSC::model('masterSync/queue')->load($id);
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }

        echo "<pre>";
        var_dump($model->data);
    }

    public function actionDelete()
    {
        if (OSC::isPrimaryStore()) {
            if (!$this->checkPermission('developer/master_sync/delete', false)) {
                $this->error('You don\'t have permission to view the page');
            }
        } else {
            if (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) != 1) {
                static::notFound();
            }
        }

        $ids = $this->_request->get('id');

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $ids = array_map(function ($id) {
            return intval($id);
        }, $ids);

        $ids = array_filter($ids, function ($id) {
            return $id > 0;
        });

        if (count($ids) < 1) {
            $this->error('No rerender queue was found to delete');
        } else if (count($ids) > 100) {
            $this->error('Unable to delete more than 100 records in a time');
        }

        try {
            OSC::core('database')->delete('masterSync/queue', 'queue_id IN (' . implode(',', array_unique($ids)) . ')');
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Master Sync queue has been deleted']);
        }

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionRequeue()
    {
        if (OSC::isPrimaryStore()) {
            if (!$this->checkPermission('developer/master_sync/requeue', false)) {
                $this->error('You don\'t have permission to view the page');
            }
        } else {
            if (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) != 1) {
                static::notFound();
            }
        }

        $ids = $this->_request->get('id');

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $ids = array_map(function ($id) {
            return intval($id);
        }, $ids);
        $ids = array_filter($ids, function ($id) {
            return $id > 0;
        });

        if (count($ids) < 1) {
            $this->error('No queue ID was found to requeue');
        }

        /* @var $DB OSC_Database */

        try {
            OSC::core('database')->update(
                'masterSync/queue',
                [
                    'syncing_flag' => 0,
                    'error_message' => '',
                    'modified_timestamp' => time()
                ],
                'queue_id IN (' . implode(',', array_unique($ids)) . ')'
            );
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Queues has been reset']);
        }

        static::redirect($this->getUrl('*/*/list'));
    }
}