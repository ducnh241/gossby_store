<?php

class Controller_Catalog_Backend_ProductTypeDescription extends Abstract_Backend_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->checkPermission('catalog/product_config/product_type_description');

        $this->getTemplate()->push('catalog/product_type.js', 'js');
        $this->getTemplate()->push('vendor/bootstrap/bootstrap-grid.min.css', 'css');
        $this->getTemplate()->setCurrentMenuItemKey('product_config/product_type_description');

        $this->getTemplate()
            ->setPageTitle('Manage Product Type Description');

    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    protected function _applyListCondition(Model_Catalog_ProductTypeDescription_Collection $collection): void
    {
        $search = OSC::sessionGet('product_config/product_type_description/search');

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword('id', 'id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('title', 'title', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('description', 'description', OSC_Search_Analyzer::TYPE_STRING, true, true)
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
            'product_config/product_type_description/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
        ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    public function actionList()
    {
        $template = $this->getTemplate()
            ->addBreadcrumb(['cog', 'Manage Product Type Description']);

        $collection = OSC::model('catalog/productTypeDescription')->getCollection();

        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }
        $collection->sort('id', OSC_Database::ORDER_DESC)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $this->output($template->build('catalog/product/productTypeDescription/list', [
            'collection' => $collection,
            'search_keywords' => $collection->registry('search_keywords'),
            'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
            'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
        ]));
    }

    public function actionPost()
    {
        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            $this->getTemplate()
                ->addBreadcrumb(['cog', 'Edit Product Type Description']);
            try {
                $model = OSC::model('catalog/productTypeDescription')->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        } else {
            $this->getTemplate()
                ->addBreadcrumb(['cog', 'Create Product Type Description']);
            $model = OSC::model('catalog/productTypeDescription');
        }

        if ($this->_request->get('title')) {
            $data = [];
            $data['title'] = trim($this->_request->get('title'));
            $data['description'] = trim($this->_request->getRaw('description'));

            try {
                if (!$data['title']) {
                    throw  new Exception('Title incorrect');
                }
                $model->setData($data)->save();

                $message = 'Product Type Description: #' . $model->getId() . ' has been ' . ($id > 0 ? 'updated' : 'created') . ' successfully by ' . OSC::helper('user/authentication')->getMember()->data['username'];
                $this->addMessage($message);

                static::redirect($this->getUrl(null, ['id' => $model->getId()]));
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $output_html = $this->getTemplate()->build('catalog/product/productTypeDescription/postForm', [
            'form_title' => $model->getId() > 0 ? ('Edit product type description #' . $model->getId()) : 'Create new product type description',
            'model' => $model,
        ]);

        $this->output($output_html);
    }

    public function actionDelete()
    {
        $this->checkPermission('catalog/product_config/product_type_description/delete');

        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            try {
                try {
                    $model = OSC::model('catalog/productTypeDescription')->load($id);
                } catch (Exception $ex) {
                    $this->addMessage($ex->getMessage());
                    static::redirect($this->getUrl('list'));
                }

                if (!$this->checkIsUsing()) {
                    $model->delete();
                } else {
                    throw new Exception('Can\'t delete this description');
                }

                $this->addMessage('Product Type Description #' . $id . ' ' . ' has been deleted by ' . OSC::helper('user/authentication')->getMember()->data['username']);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        static::redirect($this->getUrl('list'));
    }
}
