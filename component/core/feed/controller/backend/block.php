<?php

class Controller_Feed_Backend_Block extends Abstract_Backend_Controller {

    protected $_search_feed_block = '_search_feed_block';
    protected $_search_feed_bulk_queue_block = '_search_feed_bulk_queue_block';

    public function __construct() {
        parent::__construct();

        $this->getTemplate()
            ->setCurrentMenuItemKey('feed/block')
            ->setPageTitle('Manage feed block');
    }

    public function actionIndex() {
        $this->forward('*/*/google');
    }

    public function actionGoogle() {
        $this->getTemplate()->addBreadcrumb(['block', 'Block']);
        $this->checkPermission('feed/block');

        $collection = OSC::model('feed/block')->getCollection();

        $page = $this->_request->get('page', 1);
        $pageSize = 25;

        if ($this->_request->get('search')) {
            $this->_applyConditionList($collection, 'google');
        }

        $collection
            ->addCondition('category', 'google')
            ->addField('distinct country_code')
            ->setPageSize($pageSize)
            ->setCurrentPage($page)
            ->load();

        $this->output(
            $this->getTemplate()->build('feed/block/list',[
                'block' => $collection,
                'search_keywords' => $collection->registry('search_keywords_google'),
                'in_search' => $collection->registry('search_keywords_google') || (is_array($collection->registry('search_filter_google')) && count($collection->registry('search_filter_google')) > 0),
                'filter_config' => $this->_getFilterConfig($collection->registry('search_filter_google'), 'google'),
                'category' => 'google'
            ])
        );
    }

    public function actionBing() {
        $this->getTemplate()->addBreadcrumb(['block', 'Block']);
        $this->checkPermission('feed/block');

        $collection = OSC::model('feed/block')->getCollection();

        $page = $this->_request->get('page', 1);
        $pageSize = 25;

        if ($this->_request->get('search')) {
            $this->_applyConditionList($collection, 'bing');
        }

        $collection
            ->addCondition('category', 'bing')
            ->addField('distinct country_code')
            ->setPageSize($pageSize)
            ->setCurrentPage($page)
            ->load();

        $this->output(
            $this->getTemplate()->build('feed/block/list',[
                'block' => $collection,
                'search_keywords' => $collection->registry('search_keywords_bing'),
                'in_search' => $collection->registry('search_keywords_bing') || (is_array($collection->registry('search_filter_bing')) && count($collection->registry('search_filter_bing')) > 0),
                'filter_config' => $this->_getFilterConfig($collection->registry('search_filter_bing'), 'bing'),
                'category' => 'bing'
            ])
        );
    }

    public function actionSearchGoogle() {
        $this->checkPermission('feed/block');

        $filter_value = $this->_request->get('filter');

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterConfig(null, 'google'), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet($this->_search_feed_block . 'google', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value,
            'filter_field' => $this->_request->get('filter_field')
        ], 60 * 60);

        static::redirect($this->getUrl('*/*/google', ['search' => 1]));
    }

    public function actionSearchBing() {
        $this->checkPermission('feed/block');

        $filter_value = $this->_request->get('filter');

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterConfig(null, 'bing'), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet($this->_search_feed_block . 'bing', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value,
            'filter_field' => $this->_request->get('filter_field')
        ], 60 * 60);

        static::redirect($this->getUrl('*/*/bing', ['search' => 1]));
    }

    protected function _applyConditionList(Model_Feed_Block_Collection $collection, $category): void {
        $search = OSC::sessionGet($this->_search_feed_block . $category);

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword('product_id', 'product_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('sku', 'sku', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->parse($search['keywords']);

            $collection->setCondition($condition);

            $this->_applyFilter($collection, $this->_getFilterConfig(null, $category), $search['filter_value']);

            $collection->register('search_keywords_' . $category, $search['keywords'])->register('search_filter_' . $category, $search['filter_value']);
        }
    }

    protected function _getFilterConfig($filter_value = null, $category) {
        $filter_config = [];

        $countries = OSC::model('feed/block')
            ->getCollection()
            ->addCondition('category', $category)
            ->addField('distinct country_code')
            ->load()->toArray();

        $country_array = [];

        if (!empty($countries)) {
            $country_collection = OSC::model('core/country_country')->getCollection()
                ->addCondition('country_code', array_column($countries, 'country_code'), OSC_Database::OPERATOR_IN)->load()->toArray();

            foreach (array_column($countries, 'country_code') as $country_code) {
                if ($country_code != '*') {
                    $country = array_filter($country_collection, function ($item) use($country_code) {
                        return $item['country_code'] == $country_code;
                    });
                    $country_name = array_values($country)[0]['country_name'];
                }
                $country_array[$country_code] = $country_code == '*' ? 'All country' : $country_name . " ({$country_code})" ?? '';
            }

        }

        $filter_config['country_code'] =[
            'title' => 'Country',
            'type' => 'checkbox',
            'field' => 'country_code',
            'data' => $country_array
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

    public function actionPost() {
        $this->checkPermission('feed/block/add|feed/block/edit|feed/block');

        $country_code = $this->_request->get('country_code');
        $is_edit = $this->_request->get('is_edit');
        $category = $this->_request->get('category');

        $this->getTemplate()->addBreadcrumb(['block', ($is_edit ? 'Edit' : 'View') . ' Block']);

        $model = OSC::model('feed/block');

        if (!empty($country_code)) {
            try {
                $model = $model->getCollection()->addCondition('category', $category)->addCondition('country_code', $country_code)->load()->first();
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Country is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        }

        $country_exist = OSC::model('feed/block')->getCollection()->addCondition('category', $category)->addField('distinct country_code')->load()->toArray();
        $country_exist = array_column($country_exist, 'country_code');

        $countries['*'] = 'All Country';
        $countries = $countries + OSC::helper('core/country')->getCountries();
        $countries = array_filter($countries, function ($country_code) use ($country_exist){
            return !in_array($country_code, $country_exist);
        }, ARRAY_FILTER_USE_KEY);

        $this->output(
            $this->getTemplate()->build('feed/block/postForm',[
                'model' => $model,
                'countries' => $countries,
                'is_edit' => $is_edit,
                'category' => $category
            ])
        );

    }

    public function actionBulkBlock() {
        $this->checkPermission('feed/block/add');

        try {
            $uploader = new OSC_Uploader();

            if ($uploader->getExtension() != 'xlsx') {
                throw new Exception(strtoupper($uploader->getExtension()) . ' is not allowed to upload');
            }

            $file_name = 'feed/block/product/.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . time() . '.' . $uploader->getExtension();
            $file_path = OSC_Storage::preDirForSaveFile($file_name);

            $uploader->save($file_path, true);

            $sheet_data = PhpOffice\PhpSpreadsheet\IOFactory::load($file_path)->getActiveSheet()->toArray(null, true, true, true);

            $header = array_shift($sheet_data);
            $header = array_map(function($title) {;
                return preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($title));
            }, $header);
            $map_idx = [];

            foreach ($header as $column => $title) {
                $map_idx[$title] = $column;
            }

            $products = [];

            $sheet_header_column = ['collection_id', 'sku', 'country_code', 'category'];
            $sheet_header_invalid = array_filter(array_diff(array_keys($map_idx), $sheet_header_column));

            if(count($sheet_header_invalid) > 0) {
                throw new Exception('Invalid columns:'. implode(", ", $sheet_header_invalid));
            }

            foreach ($sheet_data as $index => $sheet_row) {

                foreach ($sheet_row as $idx => $value) {
                    $sheet_row[$idx] = trim(strval($value));
                }

                $category = explode(',', $sheet_row[$map_idx['category']]);

                foreach ($category as $sub) {
                    $products[] = [
                        'category' => strtolower(trim($sub)),
                        'collection_id' => $sheet_row[$map_idx['collection_id']],
                        'sku' => strtoupper($sheet_row[$map_idx['sku']]),
                        'country_code' => strtoupper($sheet_row[$map_idx['country_code']]),
                        'member_id' => $this->getAccount()->getId()
                    ];
                }

            }

            if (count($products) < 1) {
                throw new Exception('No product was found to block');
            }

            if (!OSC::writeToFile($file_path, OSC::encode($products))) {
                throw new Exception('Cannot write product data to file');
            }

            $this->_ajaxResponse(['file' => $file_name]);

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

    }

    public function actionProcessBulkBlock() {
        $this->checkPermission('feed/block/add');
        try {
            $file = $this->_request->get('file');

            $tmp_file_path = OSC_Storage::tmpGetFilePath($file);

            if (!$tmp_file_path) {
                throw new Exception('File is not exists or removed');
            }

            $products = OSC::decode(file_get_contents($tmp_file_path), true);

            foreach ($products as $product_info) {
                $queue_data = [];
                $queue_data['block'] = $product_info;


                try {
                    $model = OSC::model('catalog/product_bulkQueue')->loadByUKey('block/' . md5(OSC::encode($queue_data)));
                    $model->delete();
                } catch (Exception $ex) {
                    if ($ex->getCode() != 404) {
                        throw new Exception($ex->getMessage());
                    }
                }

                OSC::model('catalog/product_bulkQueue')->setData([
                    'ukey' => 'block/' . md5(OSC::encode($queue_data)),
                    'member_id' => $this->getAccount()->getId(),
                    'action' => 'feedBulkBlock',
                    'queue_data' => $queue_data
                ])->save();

            }

            OSC::core('cron')->addQueue('feed/bulkBlock', null, ['ukey' => 'feed/bulkBlock', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 5]);

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        @unlink($tmp_file_path);

        $this->_ajaxResponse(['message' => 'Bulk block task has appended to queue']);
    }

    public function actionSaveBlock() {
        if (!$this->checkPermission('feed/block/add|feed/block/edit', false)) {
            $this->_ajaxError('permission denied!');
        }
        $country_code = $this->_request->get('country_code');
        $collection_data = $this->_request->get('data');
        $category = $this->_request->get('category', 'google');
        $category = strtolower($category);

        if (!empty($collection_data) && !is_array($collection_data)) {
            $this->_ajaxError('Collection invalid');
        }

        if ($country_code !== '*') {
            try {
                $country = OSC::helper('core/country')->getCountry($country_code);

                if ($country->getId() < 1) {
                    throw new Exception('Not found country');
                }
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }
        }

        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();
        $DB->begin();
        $locked_key = OSC::makeUniqid();
        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            $DB->delete(OSC::model('feed/block')->getTableName(), "country_code = '{$country_code}' AND category = '{$category}'" ,null, 'delete_block');
            $DB->free('delete_block');
            $block_list = [];
            foreach ($collection_data as $collection) {
                try {
                    $product = OSC::model('catalog/product')->loadByUKey($collection['sku']);
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage() . ':' .$collection['sku']);
                }
                $block_list[] = [
                    'product_id' => $product->getId(),
                    'sku' => $collection['sku'],
                    'collection_id' => $collection['collection_id'],
                    'country_code' => $country_code,
                    'member_id' => $this->getAccount()->getId(),
                    'category' => $category
                ];
            }

            OSC::model('feed/block')->insertMulti($block_list);
            $DB->commit();
            $this->_ajaxResponse();
        } catch (Exception $ex) {
            $DB->rollback();
            $this->_ajaxError($ex->getMessage());
        }

    }

    public function actionValidateSKU() {
        if (!$this->checkPermission('feed/block/add|feed/block/edit', false)) {
            $this->_ajaxError('permission denied!');
        }
        $skus = $this->_request->get('skus');

        if (!empty($skus) && !is_array($skus)) {
            $this->_ajaxError('SKU list is can not empty');
        }

        $products = OSC::model('catalog/product')->getCollection()
            ->addField('sku')
            ->addCondition('type_flag', Model_Catalog_Product::TYPE_PRODUCT_DEFAULT)
            ->addCondition('sku', $skus, OSC_Database::OPERATOR_IN)
            ->load()->toArray();

        if (empty($products)) {
            $this->_ajaxError('Product is not exist with sku');
        }

        $product_sku_exist = array_column($products, 'sku');

        $sku_not_exist = array_diff($skus, $product_sku_exist);
        if (!empty($sku_not_exist)) {
            $this->_ajaxError('Product is empty with skus: ' . implode(',', $sku_not_exist));
        }
        $this->_ajaxResponse();
    }

    public function actionGetProductBySku() {
        $this->checkPermission('feed/block/add|feed/block/edit|feed/block');
        $skus = $this->_request->get('skus');
        if (!empty($skus) && !is_array($skus)) {
            $this->_ajaxError('SKU list is can not empty');
        }

        $result = [];

        foreach ($skus as $sku) {
            $product = OSC::helper('catalog/product')->getProduct(['ukey' => $sku], true);
            $result[$sku] = $product->getID() > 0 ? $product->data['title'] : '';
        }

        $this->_ajaxResponse($result);
    }

    public function actionDelete() {
        $this->checkPermission('feed/block/delete');
        $country_code = $this->_request->get('country_code');
        $category = $this->_request->get('category');

        if (!empty($country_code) && !empty($category)) {
            try {
                $country_title = $country_code == '*' ? 'All Country' : OSC::helper('core/country')->getCountryTitle($country_code) . " ({$country_code})";
                /* @var $DB OSC_Database_Adapter */
                $DB = OSC::core('database')->getWriteAdapter();
                $DB->delete(OSC::model('feed/block')->getTableName(), "country_code = '{$country_code}' AND category = '{$category}'" , null, 'delete_block');
                $DB->free('delete_block');
                $this->addMessage('Deleted successful country [' . $country_title . '], category [' . $category . ']');
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        static::redirect($this->getUrl("*/*/{$category}"));
    }

    public function actionBulkDelete() {
        $this->checkPermission('feed/block/delete');
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $country_codes = $this->_request->get('country_codes');
            $category = $this->_request->get('category');
            if (!is_array($country_codes)) {
                throw new Exception('Please select least a country to delete');
            }

            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getWriteAdapter();
            $DB->delete(OSC::model('feed/block')->getTableName(), "country_code IN (" . implode(',', $country_codes) .") AND category = '{$category}'" , null, 'delete_block');
            $DB->free('delete_block');

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => 'Delete successful country selected']);
    }

    public function actionBulkBlockLog() {
        $this->checkPermission('feed/block/add');

        $this->getTemplate()
            ->setCurrentMenuItemKey('feed/bulkBlockLog')
            ->setPageTitle('Bulk Block Log');

        $this->getTemplate()->addBreadcrumb(array('user', 'Bulk Block Log'));

        $collection = OSC::model('catalog/product_bulkQueue')->getCollection();

        $page = $this->_request->get('page', 1);
        $pageSize = 25;

        if ($this->_request->get('search')) {
            $this->_applyListBulkBlockLog($collection);
        }

        $collection
            ->addCondition('action', 'feedBulkBlock')
            ->sort('queue_id', OSC_Database::ORDER_ASC)
            ->setPageSize($pageSize)
            ->setCurrentPage($page)
            ->load();

        $this->output(
            $this->getTemplate()->build(
                'feed/block/bulkLog', [
                    'collection' => $collection,
                    'search_keywords' => $collection->registry('search_keywords'),
                    'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                    'filter_config' => $this->_getFilterListBulkBlockLog($collection->registry('search_filter'))
                ]
            )
        );
    }

    protected function _applyListBulkBlockLog($collection): void {
        $search = OSC::sessionGet($this->_search_feed_bulk_queue_block);

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword('queue_id', 'queue_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                ->parse($search['keywords']);

            $collection->setCondition($condition);

            $this->_applyFilter($collection, $this->_getFilterListBulkBlockLog(), $search['filter_value']);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    protected function _getFilterListBulkBlockLog($filter_value = null) {
        $filter_config = [
            'date' => [
                'title' => 'Added date',
                'type' => 'daterange',
                'field' => 'added_timestamp'
            ],
            'flag' => [
                'title' => 'Flag Status',
                'type' => 'checkbox',
                'data' => [
                    '0' => 'Running',
                    '1' => 'Wait to run',
                    '2' => 'Error',
                    '3' => 'Success'
                ],
                'field' => 'queue_flag'
            ],
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

    public function actionSearchBulkBlockLog() {
        $this->checkPermission('feed/block/add');
        $filter_value = $this->_request->get('filter');

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterListBulkBlockLog(), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet($this->_search_feed_bulk_queue_block, [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
        ], 60 * 60);

        static::redirect($this->getUrl('*/*/bulkBlockLog', ['search' => 1]));
    }

    public function actionDeleteBulkBlock() {

        if (!$this->checkPermission('feed/block/add', false)) {
            $this->_ajaxError('permission denied!');
        }

        $queue_ids = $this->_request->get('queue_ids', []);

        if(!is_array($queue_ids)) {
            $queue_ids = [$queue_ids];
        }

        try {
            $collection = OSC::model('catalog/product_bulkQueue')
                ->getCollection()
                ->addCondition('action', 'feedBulkBlock')
                ->load($queue_ids);

            $collection->delete();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Bulk block queue has been deleted']);
        }

    }
    
}
