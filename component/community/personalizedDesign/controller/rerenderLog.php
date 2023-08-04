<?php

class Controller_PersonalizedDesign_RerenderLog extends Abstract_Catalog_Controller_Backend
{

    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('personalized_design/rerender');

        $this->getTemplate()->resetBreadcrumb()
            ->addBreadcrumb(array('magic-solid', 'Personalized Design'), $this->getUrl('personalizedDesign/backend/list'))
            ->addBreadcrumb(array('magic-solid', 'Rerender Design Log'))
            ->setCurrentMenuItemKey('personalized_design/rerender_log');
    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    public function actionList()
    {
        $collection = OSC::model('personalizedDesign/rerenderLog')->getCollection();
        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }

        $collection->sort('added_timestamp', OSC_Database::ORDER_DESC)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $this->getTemplate()->setPageTitle('Design rerender log list');

        $this->output($this->getTemplate()->build('personalizedDesign/rerenderLog',
            [
                'collection' => $collection,
                'search_keywords' => $collection->registry('search_keywords'),
                'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
            ]
        ));
    }

    protected function _getFilterConfig($filter_value = null)
    {
        $filter_config = [
            'status' => [
                'title' => 'Rerender Status',
                'type' => 'checkbox',
                'data' => [
                    '0' => 'Running',
                    '1' => 'waiting to run',
                    '2' => 'Error',
                    '3' => 'Success'
                ],
                'field' => 'status'
            ],
            'date' => [
                'title' => 'Added date',
                'type' => 'daterange',
                'field' => 'added_timestamp',
                'prefix' => 'Added date'
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
            'personalizedDesign/rerenderLog/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
        ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    protected function _applyListCondition(Model_PersonalizedDesign_RerenderLog_Collection $collection): void
    {
        $search = OSC::sessionGet('personalizedDesign/rerenderLog/search');

        if ($search) {
            $condition = OSC::core('search_analyzer')->addKeyword('message', 'message', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('design_id', 'design_id', OSC_Search_Analyzer::TYPE_INT, true)
                ->parse($search['keywords']);

            $collection->setCondition($condition);

            $this->_applyFilter($collection, $this->_getFilterConfig(), $search['filter_value']);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    public function actionDelete()
    {
        $this->checkPermission('personalized_design/rerender/delete_log');

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
            $this->error('No rerender log was found to delete');
        } else if (count($ids) > 100) {
            $this->error('Unable to delete more than 100 records in a time');
        }

        try {
            $collection = OSC::model('personalizedDesign/rerenderLog')->getCollection()->load($ids);

            foreach ($collection as $model) {
                $model->delete();
            }
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Personalized design rerender log has been deleted']);
        }

        static::redirect($this->getUrl('*/*/list'));
    }
}
