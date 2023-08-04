<?php

class Controller_PersonalizedDesign_Sync extends Abstract_Catalog_Controller_Backend
{
    public function __construct()
    {
        parent::__construct();
        $this->checkPermission('personalized_design/sync_queue');
        $this->getTemplate()
            ->resetBreadcrumb()
            ->addBreadcrumb(array('magic-solid', 'Personalized Design'), $this->getUrl('personalizedDesign/backend/list'))
            ->addBreadcrumb(array('magic-solid', 'Personalized Design Sync Queue List'), $this->getCurrentUrl())
            ->setCurrentMenuItemKey('personalized_design/sync_queue')
            ->setPageTitle('Personalized Design Sync Queue List');
    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    public function actionList(){
        $collection = OSC::model('personalizedDesign/sync')->getCollection();

        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }

        $collection->setPageSize(25)->setCurrentPage($this->_request->get('page'))->load();


        $this->output($this->getTemplate()->build('personalizedDesign/syncList',
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
                'title' => 'Sync Flag code',
                'type' => 'checkbox',
                'data' => [
                    '0' => 'Queue',
                    '1' => 'Running',
                    '2' => 'Error'
                ],
                'field' => 'syncing_flag'
            ],
            'sync_type' => [
                'title' => 'Type',
                'type' => 'checkbox',
                'data' => [
                    'v2campaigndesign' => 'V2campaigndesign',
                    'image' => 'Image',
                    'font' => 'Font',
                    'renderDesignImage' => 'RenderDesignImage',
                    'imagelib' => 'Imagelib',
                    'removeDesign' => 'RemoveDesign',
                    'design' => 'Design'
                ],
                'field' => 'sync_type'
            ],
            'date' => [
                'title' => 'Added date',
                'type' => 'daterange',
                'field' => 'added_timestamp',
                'prefix' => 'Added date'
            ],
            'rdate' => [
                'title' => 'Modified date',
                'type' => 'daterange',
                'field' => 'modified_timestamp',
                'prefix' => 'Running date'
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
            'personalizedDesign/sync/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
        ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    protected function _applyListCondition(Model_PersonalizedDesign_Sync_Collection $collection): void
    {
        $search = OSC::sessionGet('personalizedDesign/sync/search');

        if ($search) {
            $condition = OSC::core('search_analyzer')->addKeyword('sync_error', 'sync_error', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('ukey', 'ukey', OSC_Search_Analyzer::TYPE_STRING, true)
                ->addKeyword('record_id', 'record_id', OSC_Search_Analyzer::TYPE_INT, true)
                ->parse($search['keywords']);

            $collection->setCondition($condition);

            $this->_applyFilter($collection, $this->_getFilterConfig(), $search['filter_value']);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    public function actionDelete()
    {
        $this->checkPermission('personalized_design/sync_queue/delete');
        $ids = $this->_request->get('id');

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $ids = OSC::helper('personalizedDesign/common')->clearData($ids);

        if (count($ids) < 1) {
            $this->error('No rerender queue was found to delete');
        } else if (count($ids) > 100) {
            $this->error('Unable to delete more than 100 records in a time');
        }

        try {
            $collection = OSC::model('personalizedDesign/sync')->getCollection()->load($ids);

            foreach ($collection as $model) {
                $model->delete();
            }
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Personalized sync has been deleted']);
        }

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionReQueue()
    {
        $this->checkPermission('personalized_design/sync_queue/requeue');

        $ids = $this->_request->get('id');

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $ids = OSC::helper('personalizedDesign/common')->clearData($ids);

        if (count($ids) < 1) {
            $this->error('No rerender queue was found to delete');
        } else if (count($ids) > 100) {
            $this->error('Unable to recron more than 100 records in a time');
        }

        try {
            OSC::core('database')->update(
                'personalizedDesign/sync',
                [
                    'syncing_flag' => 0,
                    'sync_error' => '',
                    'requeue' => 0,
                    'next_timestamp' => 0
                ],
                'FIND_IN_SET(record_id,\'' . implode(',', $ids) . '\')'
            );
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Personalized design sync has been recroned']);
        }

        static::redirect($this->getUrl('*/*/list'));
    }


}