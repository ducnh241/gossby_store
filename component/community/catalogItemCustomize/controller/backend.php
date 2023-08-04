<?php

class Controller_CatalogItemCustomize_Backend extends Abstract_Catalog_Controller_Backend {

    public function __construct() {
        parent::__construct();

        $this->checkPermission('catalog_item_customize');

        $this->getTemplate()->setCurrentMenuItemKey('catalog_product')->addBreadcrumb(array('user', 'Customize type list'), $this->getUrl('catalogItemCustomize/backend/list'));
    }

    public function actionIndex() {
        $this->forward('*/*/list');
    }

    protected function _getFilterConfig($filter_value = null) {
        $filter_config = [
            'status' => [
                'title' => 'Status',
                'type' => 'radio',
                'data' => [
                    '0' => 'Activated',
                    '1' => 'Discarded'
                ],
                'field' => 'discarded'
            ],
            'date' => [
                'title' => 'Added date',
                'type' => 'daterange',
                'field' => 'added_timestamp',
                'prefix' => 'Added date'
            ],
            'mdate' => [
                'title' => 'Modified date',
                'type' => 'daterange',
                'field' => 'modified_timestamp',
                'prefix' => 'Modified date'
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
                'catalogItemCustomize/item/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
                ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    protected function _applyListCondition(Model_CatalogItemCustomize_Item_Collection $collection): void {
        $search = OSC::sessionGet('catalogItemCustomize/item/search');

        if ($search) {
            $condition = OSC::core('search_analyzer')->addKeyword('title', 'title', OSC_Search_Analyzer::TYPE_STRING, true, true)
                    ->addKeyword('id', 'item_id', OSC_Search_Analyzer::TYPE_INT)
                    ->parse($search['keywords']);

            $collection->setCondition($condition);

            $this->_applyFilter($collection, $this->_getFilterConfig(), $search['filter_value']);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    public function actionList() {
        $collection = OSC::model('catalogItemCustomize/item')->getCollection();

        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }

        $collection->sort('title', OSC_Database::ORDER_DESC)
                ->setPageSize(25)
                ->setCurrentPage($this->_request->get('page'))
                ->load();

        $this->getTemplate()->setPageTitle('Manage customize items');

        static::setLastListUrl();

        $this->output(
                $this->getTemplate()->build(
                        'catalogItemCustomize/list', [
                    'collection' => $collection,
                    'search_keywords' => $collection->registry('search_keywords'),
                    'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                    'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
                        ]
                )
        );
    }

    protected function _getDesignFilterConfig($filter_value = null) {
        $filter_config = [
            'status' => [
                'title' => 'State',
                'type' => 'checkbox',
                'data' => [
                    '1' => 'Pending',
                    '2' => 'Processing',
                    '3' => 'Completed'
                ],
                'field' => 'state'
            ],
            'date' => [
                'title' => 'Added date',
                'type' => 'daterange',
                'field' => 'added_timestamp',
                'prefix' => 'Added date'
            ],
            'mdate' => [
                'title' => 'Modified date',
                'type' => 'daterange',
                'field' => 'modified_timestamp',
                'prefix' => 'Modified date'
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

    public function actionDesignSearch() {
        $filter_value = $this->_request->get('filter');

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getDesignFilterConfig(), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet(
                'catalogItemCustomize/design/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
                ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/designList', ['search' => 1]));
    }

    protected function _applyDesignListCondition(Model_CatalogItemCustomize_Design_Collection $collection): void {
        $search = OSC::sessionGet('catalogItemCustomize/design/search');

        if ($search) {
            $condition = OSC::core('search_analyzer')->addKeyword('ptitle', 'product_title', OSC_Search_Analyzer::TYPE_STRING, true, true)
                    ->addKeyword('cttitle', 'customize_title', OSC_Search_Analyzer::TYPE_STRING, true, true)
                    ->addKeyword('ukey', 'ukey', OSC_Search_Analyzer::TYPE_STRING, true, true)
                    ->addKeyword('id', 'record_id', OSC_Search_Analyzer::TYPE_INT, true)
                    ->addKeyword('pid', 'product_id', OSC_Search_Analyzer::TYPE_INT, true)
                    ->addKeyword('oid', 'order_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                    ->addKeyword('ctid', 'customize_id', OSC_Search_Analyzer::TYPE_INT, true)
                    ->addKeyword('member', 'member_id', OSC_Search_Analyzer::TYPE_INT, true)
                    ->parse($search['keywords']);

            $collection->setCondition($condition);

            $this->_applyFilter($collection, $this->_getDesignFilterConfig(), $search['filter_value']);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    public function actionDesignList() {
        $collection = OSC::model('catalogItemCustomize/design')->getCollection();

        if ($this->_request->get('search')) {
            $this->_applyDesignListCondition($collection);
        }

        $collection->sort('state', OSC_Database::ORDER_ASC)
                ->sort('added_timestamp', OSC_Database::ORDER_DESC)
                ->setPageSize(25)
                ->setCurrentPage($this->_request->get('page'))
                ->load();

        $this->getTemplate()->setPageTitle('Manage customize design');

        static::setLastListUrl();

        $this->output(
                $this->getTemplate()->build(
                        'catalogItemCustomize/design/list', [
                    'collection' => $collection,
                    'search_keywords' => $collection->registry('search_keywords'),
                    'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                    'filter_config' => $this->_getDesignFilterConfig($collection->registry('search_filter'))
                        ]
                )
        );
    }

    public function actionDesignExportData() {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');

            $export_condition = $this->_request->get('condition');

            $collection = OSC::model('catalogItemCustomize/design')->getCollection();

            if ($export_condition == 'search') {
                $this->_applyDesignListCondition($collection);
            } else if ($export_condition != 'all') {
                if (!is_array($export_condition)) {
                    throw new Exception('Please select least a design to export');
                }

                $export_condition = array_map(function($design_id) {
                    return intval($design_id);
                }, $export_condition);
                $export_condition = array_filter($export_condition, function($design_id) {
                    return $design_id > 0;
                });

                if (count($export_condition) < 1) {
                    throw new Exception('Please select least a design to export');
                }

                $collection->addCondition($collection->getPkFieldName(), array_unique($export_condition), OSC_Database::OPERATOR_IN);

                $export_condition = 'selected';
            }

            $collection->sort('added_timestamp', OSC_Database::ORDER_ASC)->load();

            if ($collection->length() < 1) {
                throw new Exception('No design data was found to export');
            }

            if ($collection->length() > 100) {
                $record_ids = [];

                foreach ($collection as $model) {
                    $record_ids[] = $model->getId();
                }

                OSC::core('cron')->addQueue('catalogItemCustomize/design_export', ['record_ids' => $record_ids, 'receiver' => ['email' => $this->getAccount()->data['email'], 'name' => $this->getAccount()->data['username']]], ['requeue_limit' => -1, 'skip_realtime']);

                $this->_ajaxResponse(['message' => 'Export task has sent to queue, you will received an email with download link when export completed']);
            }

            $file_url = Cron_CatalogItemCustomize_Design_Export::renderExcel($collection);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['url' => $file_url]);
    }

    public function actionDesignTake() {
        /* @var $model Model_CatalogItemCustomize_Design */

        try {

            $id = intval($this->_request->get('id'));

            if ($id < 1) {
                throw new Exception('Design ID is empty');
            }

            $model = OSC::model('catalogItemCustomize/design')->load($id);

            if (!$model->isPending()) {
                if ($model->data['member_id'] != $this->getAccount()->getId()) {
                    throw new Exception('The design is already assigned to another designer');
                }
            } else {
                $model->setData([
                    'state' => $model->getStateCodeProcessing(),
                    'member_id' => $this->getAccount()->getId()
                ])->save();
            }

            if ($this->_request->isAjax()) {
                $this->_ajaxResponse(['item' => $this->getTemplate()->build('catalogItemCustomize/design/item', ['design' => $model])]);
            }

            $this->addMessage('Design is assigned to you');
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getCode() == 404 ? 'Design is not exist' : $ex->getMessage());
        }

        static::redirectLastListUrl($this->getUrl('designList'));
    }

    public function actionDesignUpload() {
        /* @var $model Model_CatalogItemCustomize_Design */

        try {
            $id = intval($this->_request->get('id'));

            if ($id < 1) {
                throw new Exception('Design ID is empty');
            }

            $model = OSC::model('catalogItemCustomize/design')->load($id);

            if ($model->isPending()) {
                throw new Exception('Cannot update image for a pending design, please assign it to a designer before update design image');
            }

            if (!$this->getAccount()->isAdmin() && $model->data['member_id'] != $this->getAccount()->getId()) {
                throw new Exception('You unable to upload image for the design');
            }

            $uploader = new OSC_Uploader();

            $tmp_file_path = OSC::getTmpDir() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $uploader->getExtension();

            $uploader->save($tmp_file_path, true);

            $extension = OSC_File::verifyImage($tmp_file_path);

            $img_processor = new OSC_Image();
            $img_processor->setJpgQuality(65)->setImage($tmp_file_path)->resize(250)->save();

            $file_name = 'catalogCustomize/design/' . $model->getId() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;
            $file_name_s3 = OSC::core('aws_s3')->getStoragePath($file_name);

            $options = [
                'overwrite' => true,
                'permission_access_file' => 'public-read'
            ];
            OSC::core('aws_s3')->upload($tmp_file_path, $file_name_s3, $options);

            $model->setData([
                'state' => $model->getStateCodeCompleted(),
                'member_id' => $this->getAccount()->getId(),
                'design_image_url' => $file_name
            ])->save();

            $order_map_collection = OSC::model('catalogItemCustomize/orderMap')->getCollection()->addCondition('design_id', $model->getId())->load();

            $order_line_ids = [];

            foreach ($order_map_collection as $order_map) {
                $order_line_ids[] = $order_map->data['order_line_id'];
            }

            $order_line_items = OSC::model('catalog/order_item')->getCollection()->load($order_line_ids);

            foreach ($order_line_items as $order_line_item) {
                $order_line_item->setData('image_url', $model->data['design_image_url'])->save();
            }
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getCode() == 404 ? 'Design is not exist' : $ex->getMessage());
        }

        $this->_ajaxResponse(['item' => $this->getTemplate()->build('catalogItemCustomize/design/item', ['design' => $model])]);
    }

    public function actionPost() {
        $id = intval($this->_request->get('id'));

        /* @var $model Model_CatalogItemCustomize_Item */
        $model = OSC::model('catalogItemCustomize/item');

        if ($id > 0) {
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Item is not exist' : $ex->getMessage());

                static::redirectLastListUrl($this->getUrl('list'));
            }
        }

        if ($this->_request->get('title', null) !== null) {
            $data = [
                'title' => $this->_request->get('title'),
                'config' => OSC::decode($this->_request->get('config'))
            ];

            try {
                $model->setData($data)->save();

                if ($id > 0) {
                    $message = 'Customize item #' . $model->getId() . ' has been updated';
                } else {
                    $message = 'Customize item [#' . $model->getId() . '] "' . $model->data['title'] . '" added';
                }

                $this->addMessage($message);

                if (!$this->_request->get('continue')) {
                    static::redirectLastListUrl($this->getUrl('list'));
                } else {
                    static::redirect($this->getUrl(null, ['id' => $model->getId()]));
                }
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $output_html = $this->getTemplate()->build('catalogItemCustomize/postForm', [
            'form_title' => $model->getId() > 0 ? ('Edit item #' . $model->getId() . ': ' . $model->getData('title', true)) : 'Add new item',
            'model' => $model,
        ]);

        $this->output($output_html);
    }

    public function actionDuplicate() {
        /* @var $model Model_CatalogItemCustomize_Item */

        $id = intval($this->_request->get('id'));

        if ($id < 1) {
            $this->addMessage('Item ID is empty');
            static::redirectLastListUrl($this->getUrl('list'));
        }

        try {
            $model = OSC::model('catalogItemCustomize/item')->load($id);
        } catch (Exception $ex) {
            $this->addMessage($ex->getCode() == 404 ? 'Item is not exist' : $ex->getMessage());
            static::redirectLastListUrl($this->getUrl('list'));
        }

        try {
            $copy_model = $model->getNullModel();

            $model_data = $model->data;

            unset($model_data[$model->getPkFieldName()]);
            unset($model_data[$model->getUkeyFieldName()]);

            $copy_model->setData($model_data)->save();

            OSC::helper('core/common')->writeLog($copy_model->getTableName(true), "Duplicate customize #{$copy_model->getId()} from #{$model->getId()}", $copy_model->getModifiedData(true));

            static::redirect($this->getUrl('*/*/post', ['id' => $copy_model->getId()]));
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
            static::redirectLastListUrl($this->getUrl('list'));
        }
    }

    public function actionUpload() {
        try {
            $uploader = new OSC_Uploader();

            $original_file_name = $uploader->getName();

            $tmp_file_path = OSC::getTmpDir() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $uploader->getExtension();

            try {
                $uploader->save($tmp_file_path, true);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }

            try {
                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }
        } catch (Exception $ex) {
            if ($ex->getCode() == 500) {
                $this->_ajaxError($ex->getMessage());
            }

            $file_url = trim(strval($this->_request->decodeValue($this->_request->get('url'))));

            try {
                if (!$file_url) {
                    throw new Exception('No input data');
                }

                $tmp_file_path = OSC::getTmpDir() . '/' . md5($file_url);

                if (!file_exists($tmp_file_path)) {
                    $url_info = OSC::core('network')->curl($file_url, array('browser'));

                    if (!$url_info['content']) {
                        throw new Exception('Unable get response from URL');
                    }

                    if (OSC::writeToFile($tmp_file_path, $url_info['content']) === false) {
                        throw new Exception('Unable to save TMP file');
                    }
                }

                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }

            $original_file_name = preg_replace('/^.+\/([^\/]+?)([\?\#].*)?$/', '\\1', $file_url);
        }

        try {
            $img_processor = new OSC_Image();
            $img_processor->setJpgQuality(70)->setImage($tmp_file_path)->resize(150);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $img_processor->save();

            $file_name = 'catalogCustomize/images/' . date('d.m.Y') . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;
            $file_name_s3 = OSC::core('aws_s3')->getStoragePath($file_name);

            $options = [
                'overwrite' => true,
                'permission_access_file' => 'public-read'
            ];
            OSC::core('aws_s3')->upload($tmp_file_path, $file_name_s3, $options);

            $file_url = OSC::core('aws_s3')->getStorageUrl($file_name);

            $this->_ajaxResponse([
                'file' => $file_name,
                'name' => preg_replace('/^(.+)\.[^\.]*$/', '\\1', $original_file_name),
                'url' => $file_url,
                'width' => $width,
                'height' => $height
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
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
            $this->error('No customize ID was found to delete');
        } else if (count($ids) > 100) {
            $this->error('Unable to delete more than 100 customize type in a time');
        }

        try {
            OSC::model('catalogItemCustomize/item')->getCollection()->load($ids)->delete();
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Customizes has been deleted']);
        }

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionBrowse() {
        /* @var $collection Model_CatalogItemCustomize_Item_Collection */
        /* @var $model Model_CatalogItemCustomize_Item */

        try {
            $page_size = intval($this->_request->get('page_size'));

            if ($page_size == 0) {
                $page_size = 25;
            } else if ($page_size < 5) {
                $page_size = 5;
            } else if ($page_size > 100) {
                $page_size = 100;
            }

            $page = intval($this->_request->get('page'));

            if ($page < 1) {
                $page = 1;
            }

            $collection = OSC::model('catalogItemCustomize/item')->getCollection();

            $collection->setPageSize($page_size)->setCurrentPage($page)->load();

            $items = [];

            foreach ($collection as $model) {
                $items[] = [
                    'id' => $model->getId(),
                    'title' => $model->data['title'],
                    'url' => '#'
                ];
            }

            $this->_ajaxResponse([
                'keywords' => [],
                'total' => $collection->collectionLength(),
                'offset' => (($collection->getCurrentPage() - 1) * $collection->getPageSize()) + $collection->length(),
                'current_page' => $collection->getCurrentPage(),
                'page_size' => $collection->getPageSize(),
                'items' => $items
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionImportFolderDesignsUpload() {

        try {
            // if (!$this->getAccount()->isAdmin()) {
            //    throw new Exception('You unable to upload image for the design');
            // }

            $uploader = new OSC_Uploader();


            $file = pathinfo($uploader->getName());

            $ukey = $file['filename'];

            if (!in_array($uploader->getExtension(), ['jpg', 'png'])) {
                throw new Exception(strtoupper($uploader->getExtension()) . ' is not allowed to upload');
            }

            try {
                $model = OSC::model('catalogItemCustomize/design')->loadByUKey($ukey);
                if (!$model) {
                    throw new Exception('Not exist model with ukey = ' . $ukey);
                }
            } catch (Exception $e) {
                throw new Exception('Not exist model with ukey = ' . $ukey);
            }

            //save file design
            $tmp_file_path = OSC::getTmpDir() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $uploader->getExtension();
            $tmp_file_path2 = OSC::getTmpDir() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $uploader->getExtension();


            $uploader->save($tmp_file_path, true);

            $uploader->save($tmp_file_path2, true);

            $extension = OSC_File::verifyImage($tmp_file_path);

            // $printer_image_url = $model->data['printer_image_url'];
            //if ($printer_image_url === null) {
            $printer_image_url = 'catalogCustomize/design/' . $model->getId() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;
            $printer_image_url_s3 = OSC::core('aws_s3')->getStoragePath($printer_image_url);

            $options = [
                'overwrite' => true,
                'permission_access_file' => 'public-read'
            ];
            OSC::core('aws_s3')->upload($tmp_file_path, $printer_image_url_s3, $options);
            //}


            $design_image_url_front = OSC::helper('catalogItemCustomize/common')->cropDesign($tmp_file_path, $model->getId(), 'front', $extension);

            $design_image_url_behind = OSC::helper('catalogItemCustomize/common')->cropDesign($tmp_file_path2, $model->getId(), 'behind', $extension);

            try {
                $model->setData([
                    'state' => $model->getStateCodeCompleted(),
                    'member_id' => $this->getAccount()->getId(),
                    'design_image_url' => OSC::encode([$design_image_url_front, $design_image_url_behind]),
                    'printer_image_url' => $printer_image_url
                ])->save();
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            $order_map_collection = OSC::model('catalogItemCustomize/orderMap')->getCollection()->addCondition('design_id', $model->getId())->load();

            $order_line_ids = [];

            foreach ($order_map_collection as $order_map) {
                $order_line_ids[] = $order_map->data['order_line_id'];
            }

            $order_line_items = OSC::model('catalog/order_item')->getCollection()->load($order_line_ids);

            foreach ($order_line_items as $order_line_item) {
                $custom_data = [];

                foreach ($order_line_item->data['custom_data'] as $key => $value) {
                    if ($value['key'] == 'customize') {
                       $value['data']['design_image_urls'] = $model->data['design_image_url'];
                       $value['data']['printer_image_url'] = $printer_image_url;
                       $value['data']['design_key'] = $ukey;
                       $value['data']['is_mug'] = strpos(strtolower($model->getProductType()), 'mug') !== false ? 1 : 0 ;

                    }
                    $custom_data[] = $value;
                }
              
                $order_line_item->setData('custom_data', $custom_data)->save(); 
                
                OSC::core('observer')->dispatchEvent('catalog/orderUpdate', $order_line_item->data['order_id']);

            }
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
        $this->_ajaxResponse(['item' => $this->getTemplate()->build('catalogItemCustomize/design/item', ['design' => $model])]);
    }

}
