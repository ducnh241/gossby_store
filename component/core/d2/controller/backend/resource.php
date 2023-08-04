<?php

class Controller_D2_Backend_Resource extends Abstract_Backend_Controller {

    protected $_search_d2_resource = '/d2/resource';

    public function __construct() {
        parent::__construct();

        $this->getTemplate()
            ->setCurrentMenuItemKey('d2/resource')
            ->setPageTitle('Manage Resources');
    }

    public function actionIndex() {
        $this->forward('*/*/list');
    }

    public function actionList() {
        $this->checkPermission('d2/resource');
        $this->getTemplate()->addBreadcrumb(array('resource', 'D2'));

        $collection = OSC::model('d2/resource')->getCollection();

        $page = $this->_request->get('page', 1);
        $pageSize = 25;

        if ($this->_request->get('search')) {
            $this->_applyConditionList($collection);
        }

        $collection
            ->sort('id', OSC_Database::ORDER_DESC)
            ->setPageSize($pageSize)
            ->setCurrentPage($page)
            ->load()->preLoadCondition();

        $this->output(
            $this->getTemplate()->build('d2/resource/list',[
                'collection' => $collection,
                'search_keywords' => $collection->registry('search_keywords'),
                'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
            ])
        );
    }

    public function actionPost() {
        $this->checkPermission('d2/resource/edit');
        $id = $this->_request->get('id');
        $this->getTemplate()->addBreadcrumb(['resource', (intval($id) ? ('Edit #' . $id) : 'Create') . ' Resource']);

        $model = OSC::model('d2/resource');
        $conditions = [];

        if (intval($id)) {
            $model->load($id);
            $condition_collection = OSC::model('d2/condition')->getCollection()->addField('condition_key, condition_value')->addCondition('resource_id', $id)->load()->toArray();

            foreach ($condition_collection as $condition) {
                $conditions[] = [
                    'key' => $condition['condition_key'],
                    'value' => $condition['condition_value']
                ];
            }
        }

        if ($this->_request->get('submit_form')) {
            $conditions = $this->_request->get('conditions', []);
            $design_id = $this->_request->get('design_id', null);
            $resource_url = $this->_request->get('resource_url', null);

            if (!preg_match('/^https?:\/\/.*[a-zA-Z0-9]+\.psd/i', $resource_url)) {
                $this->addErrorMessage('Please! Enter resource url is .psd');
                static::redirect($this->getUrl(null, ['id' => $model->getId()]));
            }

            if (empty($conditions)) {
                $this->addErrorMessage('Please! Enter condition of resource');
                static::redirect($this->getUrl(null, ['id' => $model->getId()]));
            }

            if (empty($design_id) || intval($design_id) < 1) {
                $this->addErrorMessage('Please! Enter Design ID of resource');
                static::redirect($this->getUrl(null, ['id' => $model->getId()]));
            }

            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getWriteAdapter();
            $DB->begin();
            $locked_key = OSC::makeUniqid();
            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                if ( $id > 0) {

                    $model->setData([
                        'design_id' => $design_id,
                        'resource_url' => $resource_url
                    ])->save();

                    $DB->delete(OSC::model('d2/condition')->getTableName(), "resource_id = '$id'" ,null, 'delete_condition');
                    $DB->free('delete_condition');

                    OSC::helper('d2/resource')->saveConditions($conditions, $id, $this->getAccount()->getId());
                } else {
                    $resource = OSC::model('d2/resource')->setData([
                        'design_id' => $design_id,
                        'resource_url' => $resource_url,
                        'member_id' => $this->getAccount()->getId()
                    ])->save();
                    OSC::helper('d2/resource')->saveConditions($conditions, $resource->getId(), $this->getAccount()->getId());
                }

                $DB->commit();
                $this->addMessage('Resource design #' . $design_id . ' updated success!');

                static::redirect($this->getUrl('index'));

            } catch (Exception $ex) {
                $DB->rollback();
                $this->addErrorMessage($ex->getMessage());

            }
        }

        $output_html = $this->getTemplate()->build('d2/resource/postForm',
            [
                'model' => $model,
                'conditions' => $conditions
            ]
        );

        $this->output($output_html);
    }

    public function actionDelete() {
        $this->checkPermission('d2/resource/delete');
        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            try {
                /* @var $resource Model_D2_Resource */
                $resource = OSC::model('d2/resource')->load($id);

                $resource->delete();

                $this->addMessage('Deleted the D2 Resource with ID [' . $resource->getId() . ']');
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

        OSC::sessionSet($this->_search_d2_resource, [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value,
            'filter_field' => $this->_request->get('filter_field')
        ], 60 * 60);

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    protected function _applyConditionList(Model_D2_Resource_Collection $collection): void {
        $search = OSC::sessionGet($this->_search_d2_resource);

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword('design_id', 'design_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('id', 'id', OSC_Search_Analyzer::TYPE_INT, true, true)
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
        $this->checkPermission('d2/resource/delete');

        try {
            $resource_ids = $this->_request->get('resource_ids');

            $collection = OSC::model('d2/resource')->getCollection();

            if (!is_array($resource_ids)) {
                throw new Exception('Please select least a Resource to delete');
            }

            $collection->addCondition($collection->getPkFieldName(), array_unique($resource_ids), OSC_Database::OPERATOR_IN);

            $collection->load();

            if ($collection->length() < 1) {
                throw new Exception('No Resource was found to delete');
            }

            try {
                $collection->delete();
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => 'Delete success Resource selected']);
    }

    public function actionDuplicate() {
        $resource_id = $this->_request->get('id');
        if (!$resource_id) {
            $this->addErrorMessage('Resource ID is not valid');
            static::redirect($this->getUrl('index'));
        }

        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();
        $DB->begin();
        $locked_key = OSC::makeUniqid();
        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            $resource = OSC::model('d2/resource')->load($resource_id);
            $conditions = OSC::model('d2/condition')->getCollection()
                ->addField("`condition_key` as `key`, `condition_value` as `value`")
                ->addCondition('resource_id', $resource_id)
                ->load();

            $resource_duplicate = OSC::model('d2/resource')->setData([
                'design_id' => $resource->data['design_id'],
                'resource_url' => $resource->data['resource_url'],
                'member_id' => $this->getAccount()->getId()
            ])->save();


            OSC::helper('d2/resource')->saveConditions($conditions->toArray(), $resource_duplicate->getId(), $this->getAccount()->getId());

            $DB->commit();
            $this->addMessage("Resource duplicated success from resource #{$resource->getId()}!");

            static::redirect($this->getUrl('post', ['id' => $resource_duplicate->getId()]));

        } catch (Exception $ex) {
            $DB->rollback();
            $this->addErrorMessage($ex->getMessage());
            static::redirect($this->getUrl('index'));
        }
    }
}
