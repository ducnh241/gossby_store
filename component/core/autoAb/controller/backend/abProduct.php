<?php

class Controller_AutoAb_Backend_AbProduct extends Abstract_AutoAb_Controller_Backend
{

    public function __construct()
    {
        parent::__construct();

        if (OSC::isPrimaryStore()) {
            $this->checkPermission('abProduct/super|abProduct/product');
        } else {
            static::notFound();
        }

        $this->getTemplate()
            ->setPageTitle('Manage AB Test Product')
            ->setCurrentMenuItemKey('autoAb/testProduct')
            ->addBreadcrumb('AB Test Product', $this->getUrl('autoAb/backend_abProduct/list'));
    }

    public function actionList() {
        $config_collection = OSC::model('autoAb/abProduct_config')->getCollection();

        $page = $this->_request->get('page', 1);
        $pageSize = 25;

        $config_collection->sort('id', OSC_Database::ORDER_DESC)
            ->setPageSize($pageSize)
            ->setCurrentPage($page)
            ->load();

        $this->output($this->getTemplate()->build('autoAb/abProduct/list', [
            'config_collection' => $config_collection
        ]));
    }

    public function actionViewTracking() {
        $this->checkPermission('abProduct/super|abProduct/product/view_tracking');
        $config_id = $this->_request->get('id');
        if (!$config_id) {
            static::redirect($this->getUrl('*/*/list'));
        }

        $config = OSC::model('autoAb/abProduct_config')->load($config_id);
        $product_maps = OSC::model('autoAb/abProduct_map')->getCollection()->addCondition('config_id', $config_id)->load()->toArray();
        $products = OSC::model('catalog/product')->getCollection()->addCondition('product_id', array_column($product_maps, 'product_id'), OSC_Database::OPERATOR_IN)->load();
        $product_titles = [];
        foreach ($products as $product) {
            $product_titles[$product->getId()] = $product->data['title'];
        }
        $this->output($this->getTemplate()->build('autoAb/abProduct/viewTracking', [
            'config' => $config,
            'product_maps' => $product_maps,
            'product_titles' => $product_titles
        ]));
    }

    public function actionGetPostForm() {
        $config_id = $this->_request->get('id');

        $this->checkPermission('abProduct/super|abProduct/product/' . ($config_id > 0 ? 'edit' : 'add'));

        $model = OSC::model('autoAb/abProduct_config');

        $selected_products = [];
        $default_product = [];

        if ($config_id > 0) {
            try {
                $model = $model->load($config_id);

                /* @var $collection Model_AutoAb_AbProduct_Map_Collection */
                $collection = OSC::model('autoAb/abProduct_map')->getCollection()
                    ->addCondition('config_id', $config_id, OSC_Database::OPERATOR_EQUAL)
                    ->addField('product_id,is_default')->load();
                $product_map_ids = [];
                foreach ($collection as $ab_product) {
                    $product_map_ids[$ab_product->data['product_id']] = $ab_product->data['is_default'];
                }

                if (count($collection) > 0) {

                    $collection_product = OSC::model('catalog/product')->getCollection()->addField('title')->load(array_keys($product_map_ids));

                    foreach ($collection_product as $product) {
                        $selected_products[] = [
                            'product_id' => $product->getId(),
                            'title' => $product->data['title']
                        ];

                        if ($product_map_ids[$product->getId()]) {
                            $default_product = [
                                'product_id' => $product->getId(),
                                'title' => $product->data['title']
                            ];
                        }
                    }
                }
            } catch (Exception $ex) {
                $this->addMessage($ex->getMessage());
                static::redirect($this->getUrl('*/*/list'));
            }
        }

        $this->output($this->getTemplate()->build('autoAb/abProduct/postForm', [
            'model' => $model,
            'selected_products' => $selected_products,
            'default_product' => $default_product
        ]));
    }


    /**
     * @throws OSC_Exception_Runtime
     */
    public function actionGetListProducts() {
        $keyword = $this->_request->get('keyword');

        $products = OSC::model('catalog/product')
            ->getCollection()
            ->addField('product_id', 'title')
            ->addCondition('product_id', $keyword, OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_OR)
            ->addCondition('title', $keyword, OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_OR)
            ->setLimit(50)
            ->load()
            ->toArray();

        $this->_ajaxResponse([
            'result' => $products
        ]);
    }

    public function actionPost() {
        $config_id = $this->_request->get('id');

        $this->checkPermission('abProduct/super|abProduct/product/' . ($config_id > 0 ? 'edit' : 'add'));


        $title = trim($this->_request->get('title'));

        if (($title) == '') {
            $this->_ajaxError('Title not found');
        }

        $begin_at = trim($this->_request->get('begin_at'));

        if (($begin_at) == '') {
            $this->_ajaxError('Begin time not found');
        }

        $finish_at = trim($this->_request->get('finish_at'));

        if (($finish_at) == '') {
            $this->_ajaxError('Finish time not found');
        }

        if ($finish_at < $begin_at) {
            $this->_ajaxError('Finish time need greater than or equal Begin time');
        }

        $product_ids = !empty($this->_request->get('products')) ?
            array_map('intval', $this->_request->get('products')) :
            [];

        if (count($product_ids) < 2) {
            $this->_ajaxError('Product need greater than or equal 2');
        }

        $default_product_id = intval($this->_request->get('default_product_id'));

        $data['title'] = $title;
        $data['begin_time'] = strtotime(str_replace('/', '-', $begin_at) . " 00:00:00");
        $data['finish_time'] = strtotime(str_replace('/', '-', $finish_at) . " 23:59:59");

        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            $model = OSC::model('autoAb/abProduct_config');

            try {
                $model = OSC::model('autoAb/abProduct_config')->load($config_id);
            } catch (Exception $ex) {
                //
            }

            $status_request = $this->_request->get('status');
            $status = $model->data['status'];

            if ($config_id > 0) {
                $_status = null;

                if ($status == Model_AutoAb_AbProduct_Config::STATUS_CREATED && $status_request == Model_AutoAb_AbProduct_Config::STATUS_IN_PROGRESS) {
                    $_status = Model_AutoAb_AbProduct_Config::STATUS_IN_PROGRESS;
                } else if ($status == Model_AutoAb_AbProduct_Config::STATUS_IN_PROGRESS && $status_request == Model_AutoAb_AbProduct_Config::STATUS_CREATED) {
                    $_status = Model_AutoAb_AbProduct_Config::STATUS_ON_HOLD;
                } else if ($status == Model_AutoAb_AbProduct_Config::STATUS_ON_HOLD && $status_request == Model_AutoAb_AbProduct_Config::STATUS_IN_PROGRESS) {
                    $_status = Model_AutoAb_AbProduct_Config::STATUS_IN_PROGRESS;
                }

                if ($_status == null) {
                    $data['status'] = $status;
                } else {
                    $data['status'] = $_status;
                }
            } else {
                $data['status'] = $this->_request->get('status');
            }

            if ($status != Model_AutoAb_AbProduct_Config::STATUS_CREATED) {
                unset($data['title']);
                unset($data['begin_time']);
                unset($data['finish_time']);
            }

            $model->setData($data)->save();

            if (!$default_product_id) {
                $default_product_id = $product_ids[0];
            }

            if ($config_id < 1 || $status == Model_AutoAb_AbProduct_Config::STATUS_CREATED) {
                $_config_id = $model->getId();

                OSC::model('autoAb/abProduct_map')->getCollection()
                    ->addCondition('config_id', $_config_id)
                    ->load()
                    ->delete();

                foreach ($product_ids as $product_id) {
                    OSC::model('autoAb/abProduct_map')->setData([
                        'config_id' => $_config_id,
                        'product_id' => $product_id,
                        'acquisition' => 0,
                        'is_default' => $default_product_id == $product_id ? Model_AutoAb_AbProduct_Map::IS_DEFAULT['ENABLE'] : Model_AutoAb_AbProduct_Map::IS_DEFAULT['DISABLE'],
                        'added_timestamp' => time(),
                        'modified_timestamp' => time()
                    ])->save();
                }
            } else {
                /* @var $map Model_AutoAb_AbProduct_Map */
                $product_maps = OSC::model('autoAb/abProduct_map')->getCollection()
                    ->addCondition('config_id', $model->getId())
                    ->load();

                foreach ($product_maps as $map) {

                    $is_default = Model_AutoAb_AbProduct_Map::IS_DEFAULT['DISABLE'];
                    if ($map->data['product_id'] == $default_product_id) {
                        $is_default = Model_AutoAb_AbProduct_Map::IS_DEFAULT['ENABLE'];
                    }

                    $map->setData([
                        'is_default' => $is_default
                    ])->save();
                }

            }

            if ($config_id > 0) {
                $log_content = 'AB Test Product: Edit#' . $model->data['title'];
            } else {
                $log_content = 'AB Test Product: Added [#' . $model->getId() . '] "' . $model->data['title'] . '"';
            }

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse([
            'result' => 200,
            'redirect' => $this->getUrl('*/*/list'),
            'hub_url' => $model->getHubUrl(),
            'message' => $log_content
        ]);
    }

    public function actionDelete() {
        $this->checkPermission('abProduct/super|abProduct/product/delete');

        $id = intval($this->_request->get('id'));

        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            $model = OSC::model('autoAb/abProduct_config')->load($id);
            $title = $model->data['title'];
            $model->delete();

            $this->addMessage('Deleted the config AB Test Product #' . $title);

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            $this->addErrorMessage($ex->getMessage());
        }

        OSC_Database_Model::unlockPreLoadedModel($locked_key);

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionDuplicate() {
        $this->checkPermission('abProduct/super|abProduct/product/add|abProduct/product/edit');

        $id = intval($this->_request->get('id'));

        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            $model = OSC::model('autoAb/abProduct_config')->load($id);

            $model_config_new = OSC::model('autoAb/abProduct_config')->setData(
                [
                    'title' => 'Duplicate - ' . $model->data['title'],
                    'begin_time' => $model->data['begin_time'],
                    'finish_time' => $model->data['finish_time'],
                    'status' => Model_AutoAb_AbProduct_Config::STATUS_CREATED,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ]
            )->save();

            $id_config_new = $model_config_new->getId();

            $collection = OSC::model('autoAb/abProduct_map')->getCollection()
                ->addCondition('config_id', $id)
                ->load();

            foreach ($collection as $model_map) {
                OSC::model('autoAb/abProduct_map')->setData([
                    'config_id' => $id_config_new,
                    'product_id' => $model_map->data['product_id'],
                    'acquisition' => 0,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ])->save();
            }

            $this->addMessage('AB Test Product: Duplicate the config #' . $id .' to #' . $id_config_new);

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            $this->addErrorMessage($ex->getMessage());
        }

        OSC_Database_Model::unlockPreLoadedModel($locked_key);

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionGetTrackingRange() {

        $this->checkPermission('abProduct/super|abProduct/product/view_tracking');

        $config_id = $this->_request->get('id');
        $timestamp_range = OSC::helper('autoAb/common')->fetchTrackingListData($this->_request->get('range'));

        $data = [];

        if ($config_id > 0) {

            $condition = ['config_id = ' . $config_id];

            if ($timestamp_range['time'][0] > 0) {
                $condition[] = 'date >= ' . date('Ymd', $timestamp_range['time'][0]);
            }

            if ($timestamp_range['time'][1] > 0) {
                $condition[] = 'date <= ' . date('Ymd', $timestamp_range['time'][1]);
            }

            $condition = implode(' AND ', $condition);
            try {
                $model = OSC::model('autoAb/abProduct_config')->load($config_id);

                $title = $model->data['title'];

                /* @var $DB OSC_Database */
                $DB = OSC::core('database');
                $DB->query("SELECT product_id, SUM(unique_visitor) AS 'unique_visitor', SUM(page_view) AS 'page_view', SUM(total_order) AS 'total_order', SUM(quantity) AS 'quantity', SUM(revenue) AS 'revenue' FROM `osc_auto_ab_product_report` WHERE {$condition} GROUP BY product_id", null, 'fetch_ab_tracking');

                $data = $DB->fetchArrayAll('fetch_ab_tracking');
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getCode() == 404 ? 'AB Test Product is not exist' : $ex->getMessage());
            }
        }

        $this->_ajaxResponse([
            'data' => $data,
            'range' => $timestamp_range['range'],
            'name' => $title
        ]);
    }
}
