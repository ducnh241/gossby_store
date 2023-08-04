<?php

class Controller_Filter_AutoTag extends Abstract_Backend_Controller
{
    protected $_search_filter_auto_tag = '_search_filter_auto_tag';

    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('filter/auto_tag');

        $this->getTemplate()
            ->setCurrentMenuItemKey('filter/auto_tag')
            ->setPageTitle('Manage Auto Tags');
    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    public function actionList()
    {
        $this->getTemplate()->setCurrentMenuItemKey('filter/auto_tag')
            ->addBreadcrumb('Manage Auto Tags', $this->getUrl('index'));
        $this->getTemplate()->setPageTitle('Manage Auto Tags');
        
        $auto_tags = OSC::model('filter/autoTag')->getCollection();

        $page = $this->_request->get('page', 1);
        $pageSize = 25;

        if ($this->_request->get('search')) {
            $this->_applyConditionList($auto_tags);
        }

        $auto_tags
            ->sort('id', OSC_Database::ORDER_DESC)
            ->setPageSize($pageSize)
            ->setCurrentPage($page)
            ->load()->preLoadTags();

        try {
            $model = OSC::model('core/setting')->loadByUKey(Model_Filter_AutoTag::KEY_CONFIG_SETTING_FIELDS);
            $setting_fields = $model->data['setting_value'];
        } catch (Exception $ex) {}

        $output_html = $this->getTemplate()->build('filter/autoTag/list',
            [
                'collection' => $auto_tags,
                'setting_fields' => $setting_fields,
                'search_keywords' => $auto_tags->registry('search_keywords'),
                'in_search' => $auto_tags->registry('search_keywords') || (is_array($auto_tags->registry('search_filter')) && count($auto_tags->registry('search_filter')) > 0),
                'filter_config' => $this->_getFilterConfig($auto_tags->registry('search_filter'))
            ]
        );

        $this->output($output_html);
    }

    protected function _applyConditionList(Model_Filter_AutoTag_Collection $collection): void {
        $search = OSC::sessionGet($this->_search_filter_auto_tag);

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword('product_id', 'product_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('auto_tag', 'auto_tag', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('deleted_tag', 'deleted_tag', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('new_tag', 'new_tag', OSC_Search_Analyzer::TYPE_STRING, true, true)
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

        OSC::sessionSet($this->_search_filter_auto_tag, [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value,
            'filter_field' => $this->_request->get('filter_field')
        ], 60 * 60);

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    public function actionSettingFieldAutoTag() {
        $this->checkPermission('filter/auto_tag/setting_fields');
        $setting_fields = $this->_request->get('setting_fields');
        if (!$setting_fields || !is_array($setting_fields)) {
            $this->_ajaxError('Setting fields is invalid!', 400);
        }

        try {
            OSC::helper('core/setting')->set(Model_Filter_AutoTag::KEY_CONFIG_SETTING_FIELDS, $setting_fields);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), 500);
        }

        $this->_ajaxResponse();
    }
}
