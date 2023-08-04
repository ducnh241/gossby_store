<?php

class Controller_Catalog_Backend_Collection extends Abstract_Catalog_Controller_Backend {
    protected $_search_filter_field = 'search_filter_field';
    protected $_default_search_field_key = 'default_search_product_field';

    protected $_filter_field = [
        'all' => 'All field',
        'product_id' => 'ID',
        'sku' => 'SKU',
        'product_title' => 'Title',
    ];

    public function __construct() {
        parent::__construct();

        $this->checkPermission('catalog/super|catalog/collection');
        $this->getTemplate()->setPageTitle('Manage Collections');

        $this->getTemplate()->setCurrentMenuItemKey('catalog/collection');
    }

    protected function _getFilterConfig($filter_value = null)
    {
        $filter_config = [
            'status' => [
                'title' => 'Allow index',
                'type' => 'radio',
                'data' => [
                    '1' => 'Yes',
                    '0' => 'No'
                ],
                'field' => 'allow_index'
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
            'catalog/collection/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
        ], 60 * 60);

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    protected function _applyListCondition(Model_Catalog_Collection_Collection $collection): void
    {
        $search = OSC::sessionGet('catalog/collection/search');

        if ($search) {
            $keywords = trim($search['keywords']);

            $condition = [];
            $params = [];
            if (!empty($keywords)) {
                $condition_search = [
                    "title LIKE :keywords",
                    "custom_title LIKE :keywords",
                    "meta_tags LIKE :keywords",
                    "slug LIKE :keywords",
                    "collection_id LIKE :keywords",
                ];

                $condition[] = '(' . implode(' OR ', $condition_search) . ')';
                $params = array_merge($params, [
                    'keywords' => '%' . $keywords . '%'
                ]);
            }
            $filter_config = $this->_getFilterConfig();
            $filter_value = $search['filter_value'];

            if (count($filter_value) > 0) {
                foreach ($filter_value as $key => $value) {
                    if (!isset($filter_config[$key])) {
                        continue;
                    }

                    if (is_array($value) && count($value) == 1) {
                        $value = $value[0];
                    }

                    if (is_array($value)) {
                        foreach ($value as $v) {
                            $condition[] = $filter_config[$key]['field'] . " = :" . $filter_config[$key]['field'];
                            $params = array_merge($params, [
                                $filter_config[$key]['field'] => $v
                            ]);
                        }
                    } else {
                        $condition[] = $filter_config[$key]['field'] . " = :" . $filter_config[$key]['field'];
                        $params = array_merge($params, [
                            $filter_config[$key]['field'] => $value
                        ]);
                    }
                }
            }

            $condition = [
                'condition' => implode(' AND ', $condition),
                'params' => $params
            ];

            $collection->setCondition($condition);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    public function actionList()
    {
        $this->getTemplate()->addBreadcrumb('Manage Collections');
        $collection = OSC::model('catalog/collection')->getCollection();
        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }
        $collection->sort('collection_id', OSC_Database::ORDER_DESC)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $this->output($this->getTemplate()->build('catalog/collection/list',
            [
                'collection' => $collection,
                'search_keywords' => $collection->registry('search_keywords'),
                'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
            ]
        )
        );
    }

    public function actionUpdateProducts() {
        $this->checkPermission('catalog/super|catalog/collection/edit');

        $id_data = array(
            'add' => $this->_request->get('add'),
            'remove' => $this->_request->get('remove')
        );

        foreach ($id_data as $key => $ids) {
            if (!is_array($ids)) {
                $ids = array($ids);
            }

            $ids = array_map(function($id) {
                return intval($id);
            }, $ids);

            $ids = array_filter($ids, function($id) {
                return $id > 0;
            });

            $ids = array_unique($ids);

            $id_data[$key] = $ids;
        }

        $id_data['remove'] = array_diff($id_data['remove'], $id_data['add']);

        if (count($id_data['add']) > 0 || count($id_data['remove']) > 0) {
            try {
                $collection = OSC::model('catalog/collection')->load($this->_request->get('id'));

                $collection_id = $collection->getId();

                $queries = array();
                $select_condition = array();

                $product_collection = OSC::model('catalog/product')->getCollection();

                $product_table_name = $product_collection->getTableName(true);

                if (count($id_data['add']) > 0) {
                    $select_condition[] = "(product_id IN (" . implode(',', $id_data['add']) . ") AND (collection_ids IS NULL OR collection_ids NOT LIKE '%,{$collection_id},%'))";
                    $queries[] = "UPDATE {$product_table_name} SET collection_ids = CONCAT(COALESCE(collection_ids,''), IF(LOCATE(',{$collection_id},', collection_ids) > 0, '', IF(collection_ids IS NULL OR collection_ids = '', ',{$collection_id},','{$collection_id},'))) WHERE product_id IN (" . implode(',', $id_data['add']) . ") LIMIT " . count($id_data['add']);
                }

                if (count($id_data['remove']) > 0) {
                    $select_condition[] = "(product_id IN (" . implode(',', $id_data['remove']) . ") AND collection_ids LIKE '%,{$collection_id},%')";
                    $queries[] = "UPDATE {$product_table_name} SET collection_ids = REPLACE(collection_ids, ',{$collection_id},', IF(collection_ids = ',{$collection_id},', '',',')) WHERE product_id IN (" . implode(',', $id_data['remove']) . ") LIMIT " . count($id_data['remove']);
                }

                $select_condition = implode(' OR ', $select_condition);
                $queries = implode(";\n", $queries);

                $queries = "SELECT product_id FROM {$product_table_name} WHERE {$select_condition} LIMIT " . (count($id_data['add']) + count($id_data['remove'])) . ";\n" . $queries;

                $DB = OSC::core('database');

                $DB->query($queries);

                $product_ids = array();

                while ($row = $DB->fetchArray()) {
                    $product_ids[] = $row['product_id'];
                }

                if (count($product_ids) > 0) {
                    try {
                        $product_collection->load($product_ids)->resetCache();
                    } catch (Exception $ex) {

                    }
                }

                OSC::helper('catalog/common')->reloadFileFeedFlag();

                $this->_ajaxResponse();
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }
        }
    }

    public function actionProductList() {
        $this->checkPermission('catalog/super|catalog/collection/edit');

        try {
            $collection_id = intval($this->_request->get('id'));

            if ($collection_id < 1) {
                throw new Exception('Collection ID is incorrect');
            }

            /* @var $collection Model_Catalog_Collection */
            $collection = OSC::model('catalog/collection')->load($collection_id);

            if ($collection->data['collect_method'] != Model_Catalog_Collection::COLLECT_MANUAL) {
                throw new Exception('The collection type is auto collection');
            }

            $collection->loadProducts(array(
                'page_size' => 15,
                'page' => $this->_request->get('page')
            ));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $products = array();

        /* @var $product Model_Catalog_Product */
        foreach ($collection->getProducts() as $product) {
            $products[] = array(
                'id' => $product->getId(),
                'title' => $product->getProductTitle(),
                'image_url' => $product->getFeaturedImageUrl()
            );
        }

        $this->_ajaxResponse(array(
            'products' => $products,
            'collection_length' => $collection->collectionLength(),
            'page_size' => $collection->getPageSize(),
            'current_page' => $collection->getCurrentPage()
        ));
    }

    public function actionUploadImage() {
        $this->checkPermission('catalog/super|catalog/collection/edit|catalog/collection/add');

        try {
            $uploader = new OSC_Uploader();

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

            $image_url = trim(strval($this->_request->decodeValue($this->_request->get('image_url'))));

            try {
                if (!$image_url) {
                    throw new Exception($this->_('core.err_data_incorrect'));
                }

                $tmp_file_path = OSC::getTmpDir() . '/' . md5($image_url);

                if (!file_exists($tmp_file_path)) {
                    $url_info = OSC::core('network')->curl($image_url, array('browser'));

                    if (!$url_info['content']) {
                        throw new Exception($this->_('core.err_data_incorrect'));
                    }

                    if (OSC::writeToFile($tmp_file_path, $url_info['content']) === false) {
                        throw new Exception($this->_('core.err_tmp_save_failed'));
                    }
                }

                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }
        }

        try {
            $img_processor = new OSC_Image();
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path)->resize(1920);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $file_name = 'collection.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

            $tmp_url = OSC::core('aws_s3')->tmpSaveFile($img_processor, $file_name);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $tmp_url = $tmp_url ?? OSC_Storage::tmpGetFileUrl($file_name);

        $this->_ajaxResponse([
            'file' => $file_name,
            'url' => $tmp_url,
            'width' => $width,
            'height' => $height
        ]);
    }

    public function actionPost() {
        $id = intval($this->_request->get('id'));

        $this->checkPermission('catalog/super|catalog/collection/' . ($id > 0 ? 'edit' : 'add'));

        /* @var $model Model_Catalog_Collection */
        $model = OSC::model('catalog/collection');

        if ($id > 0) {
            $this->getTemplate()->addBreadcrumb(array('user', 'Edit Collection'));
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Collection is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        } else {
            $this->getTemplate()->addBreadcrumb(array('user', 'Create Collection'));
        }

        if (key_exists('title', $this->_request->getAll('post'))) {
            $data = array();

            $data['title'] = $this->_request->get('title');
            $data['slug'] = trim($this->_request->get('seo_slug'), '- ');
            $data['custom_title'] = $this->_request->get('custom_title');
            $data['description'] = $this->_request->getRaw('description');
            $data['image'] = $this->_request->get('image');
            $data['collect_method'] = $this->_request->get('collect_method');
            $data['sort_option'] = $this->_request->get('sort_option');
            $data['allow_index'] = intval($this->_request->get('allow_index'));
            $data['auto_conditions'] = $this->_request->get('condition');
            $data['show_review_mode'] = intval($this->_request->get('show_review_mode')) == 1 ? 1 : 0 ;
            $data['top'] = $this->_request->get('top') ? intval($this->_request->get('top')) : null;

            $date_type = $this->_request->get('date_type');
            $date_range = $this->_request->get('date_range');
            $relative_range = intval($this->_request->get('relative_range')) > 0 ? intval($this->_request->get('relative_range')) : null;

            if ($data['sort_option'] === 'solds') {
                if ($date_type == Model_Catalog_Collection::DATE_TYPE_ABSOLUTE && $date_range) {
                    // not all time
                    preg_match($this->_date_range_pattern, $date_range, $matches);

                    $start_timestamp = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);

                    if ($matches[5]) {
                        $end_timestamp = mktime(23, 59, 59, $matches[6], $matches[5], $matches[7]);
                    } else {
                        $end_timestamp = mktime(23, 59, 59, $matches[2], $matches[1], $matches[3]);
                    }

                    $data['best_selling_start'] = $start_timestamp;
                    $data['best_selling_end'] = $end_timestamp;
                    $data['relative_range'] = null;
                } else if ($date_type == Model_Catalog_Collection::DATE_TYPE_RELATIVE && $relative_range) {
                    $data['relative_range'] = $relative_range;
                    $data['best_selling_start'] = null;
                    $data['best_selling_end'] = null;
                }
            } else {
                $data['relative_range'] = null;
                $data['best_selling_start'] = null;
                $data['best_selling_end'] = null;
            }

            // get SEO Collection to save meta_data
            $seo_title = trim($this->_request->get('seo-title'));
            $seo_description = trim($this->_request->get('seo-description'));
            $seo_keyword = trim($this->_request->get('seo-keyword'));
            $seo_image = $this->_request->get('seo-image');
            $banner = $this->_request->get('banner');

            if (isset($banner['pc'])) {
                $banner['pc'] = OSC::helper('catalog/frontend')->saveCollectionBannerOnS3($banner['pc'], $model->data['meta_tags']['banner']['pc']);
            }

            if (isset($banner['mobile'])) {
                $banner['mobile'] = OSC::helper('catalog/frontend')->saveCollectionBannerOnS3($banner['mobile'], $model->data['meta_tags']['banner']['mobile']);
            }

            $data['meta_tags'] = [
                'title' => $seo_title,
                'description' => $seo_description,
                'keywords' => $seo_keyword,
                'image' => $seo_image
            ];
            $data['meta_tags']['banner'] = $banner;

            if (!$data['slug']) {
                $data['slug'] = OSC::core('string')->cleanAliasKey($data['title']);
            }

            if ($data['image'] != $model->data['image']) {

                if (!$data['image']) {
                    $data['image'] = '';
                    $image_to_rmv = $model->data['image'];
                } else {
                    $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($data['image']);
                    if (!OSC::core('aws_s3')->doesObjectExist($tmp_image_path_s3)) {
                        $data['image'] = $model->data['image'];
                    } else {
                        $filename = 'collection/' . str_replace('collection.', '', $data['image']);
                        $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);

                        try {
                            OSC::core('aws_s3')->copy($tmp_image_path_s3, $storage_filename_s3);

                            $data['image'] = $filename;
                        } catch (Exception $ex) {
                            $data['image'] = $model->data['image'];
                        }
                    }
                }
            }

            $meta_image =  OSC::helper('backend/backend/images/common')->saveMetaImage($data['meta_tags']['image'],$model->data['meta_tags']['image'], 'meta/collection/', 'collection');

            if ($meta_image['data_meta_image']) {
                $data['meta_tags']['image'] = $meta_image['data_meta_image'];
            }

            try {
                $old_seo_tag_title =  $model->data['custom_title'] ?: $model->data['title'];

                $model->setData($data)->save();

                $data['slug'] = OSC::helper('alias/common')->renameSlugDuplicate($seo_title, $data['slug'], $model->getId(), 'collection');
                try {
                    OSC::helper('alias/common')->save($data['slug'], 'catalog_collection', $model->getId());
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }

                $model->setData(['slug' => $data['slug']])->save();

                try {
                    OSC::helper('catalog/product')->updateSeoTags($model->data['collection_id'], $old_seo_tag_title, $model->data['custom_title'] ?: $model->data['title']);
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }

                if ($meta_image['image_to_rm'] != null) {
                    unlink(OSC_Storage::getStoragePath($meta_image['image_to_rm']));
                }

                if ($image_to_rmv) {
                    unlink(OSC_Storage::getStoragePath($image_to_rmv));
                }

                if ($id > 0) {
                    $message = 'Your update has been saved successfully.';
                } else {
                    $message = 'Collection has been saved successfully.';
                }

                if ($banner['option'] === 'current' && (!isset($banner['pc']) || !isset($banner['mobile']))) {
                    $this->addErrorMessage('Please upload Collection images.');
                    static::redirect($this->getUrl(null, ['id' => $model->getId()]));
                }

                OSC::helper('catalog/common')->reloadFileFeedFlag();

                $this->addMessage($message);

                if (!$this->_request->get('continue')) {
                    static::redirect($this->getUrl('list'));
                } else {
                    static::redirect($this->getUrl(null, array('id' => $model->getId())));
                }
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        //Search catalog collection form
        $default_select_field = OSC::cookieGet($this->_default_search_field_key);
        if (empty($default_select_field)) {
            $default_select_field = array_key_first($this->_filter_field);
            OSC::cookieSet($this->_default_search_field_key, $default_select_field);
        }
        //End search catalog collection form

        $output_html = $this->getTemplate()->build('catalog/collection/postForm', array(
            'form_title' => $model->getId() > 0 ? ('Edit collection #' . $model->getId() . ': ' . $model->getData('title', true)) : 'Add new collection',
            'model' => $model,
            'filter_config' => [],
            'filter_field' => $this->_filter_field,
            'selected_filter_field' => $model->registry($this->_search_filter_field),
            'default_search_field_key' => $this->_default_search_field_key,
        ));

        $this->output($output_html);
    }

    public function actionExportDataSEO() {
        $this->checkPermission('catalog/super|catalog/collection/full|catalog/collection/exportDataSEO');

        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');

            $export_condition = $this->_request->get('export_condition');
            $colums = $this->_request->get('columns');

            if (!in_array($export_condition, ['all', 'search', 'selected'])) {
                throw new Exception('Please select condition to export');
            }

            $collections = OSC::model('catalog/collection')->getCollection();

            if ($export_condition == 'selected') {
                $selected_ids = $this->_request->get('selected_ids');

                if (!is_array($selected_ids)) {
                    throw new Exception('Please select least a collection to export');
                }

                $selected_ids = array_map(function($collection_id) {
                    return intval($collection_id);
                }, $selected_ids);
                $selected_ids = array_filter($selected_ids, function($collection_id) {
                    return $collection_id > 0;
                });

                if (count($selected_ids) < 1) {
                    throw new Exception('Please collection least a collection to export');
                }

                $collections->addCondition($collections->getPkFieldName(), array_unique($selected_ids), OSC_Database::OPERATOR_FIND_IN_SET);
            } else if ($export_condition == 'search') {
                $this->_applyListCondition($collections);
            }

            $collections->sort('collection_id', OSC_Database::ORDER_ASC)->load();

            if ($collections->length() < 1) {
                throw new Exception('No collection was found to export');
            }

            $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers_column = [];
            foreach ($colums as $key => $item) {
                foreach ($item as $key2 => $value) {
                    $headers_column[$key2] = $value;
                }
            }

            $headers = array_values($headers_column);

            foreach ($headers as $i => $title) {
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . 1, $title);
            }

            $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . 1)->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('89B7E5');
            $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . 1)->getFont()->getColor()->setARGB('FFFFFF');
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setWidth(20);

            /* @var $collection Model_Catalog_Collection */

            $sheet_row_index = 2;

            foreach ($collections as $collection) {

                $data_insert = $this->_getDataExport($collection);
                $row_data = array_values(array_intersect_key($data_insert, $headers_column));
                foreach ($row_data as $i => $value) {
                    if (!empty($value)) {
                        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . $sheet_row_index, $value);
                    }
                }

                if (array_intersect_key($data_insert, $headers_column)['variant_id']) {
                    $sheet_row_index++;
                }

                if (!array_intersect_key($data_insert, $headers_column)['variant_id']) {
                    $sheet_row_index++;
                }
            }

            $file_name = 'export/catalog/collection/' . $export_condition . '.' . OSC::makeUniqid() . '.' . date('d-m-Y') . '.xlsx';
            $file_path = OSC_Storage::preDirForSaveFile($file_name);

            $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $writer->save($file_path);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['url' => OSC_Storage::tmpGetFileUrl($file_name)]);
    }
    private function _getDataExport( $collection){
        $data_insert = [
            "collection_id" =>$collection->getId() ?: '',
            "url" => $collection->getDetailUrl() ?: '',
            "title" =>  $collection->data['title'] ?: '',
            "custom_title" => $collection->data['custom_title'] ?: '',
            "description" =>  $collection->data['description'] ?: '',
            "meta_title" =>  $collection->data['meta_tags']['title'] ?: '',
            "meta_slug" =>  $collection->data['slug'] ?: '',
            "meta_keywords" =>  $collection->data['meta_tags']['keywords'] ?: '',
            "meta_description" =>   $collection->data['meta_tags']['description'] ?: '',
        ];

        return $data_insert;
    }

    public function actionImportDataSEO() {
        $this->checkPermission('catalog/super|catalog/collection/full|catalog/collection/importDataSEO');

        try {
            $uploader = new OSC_Uploader();

            if ($uploader->getExtension() != 'xlsx') {
                throw new Exception(strtoupper($uploader->getExtension()) . ' is not allowed to upload');
            }

            $file_name = 'import/catalog/collection/.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . time() . '.' . $uploader->getExtension();

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

            $collections = [];
            $collection_id = null;

            $sheet_header_column = ['collection_id', 'url', 'title', 'custom_title', 'description', 'meta_title', 'meta_slug', 'meta_keywords', 'meta_description'];
            $sheet_header_invalid = array_filter(array_diff(array_keys($map_idx), $sheet_header_column));

            if (count($sheet_header_invalid) > 0) {
                throw new Exception('Invalid columns:'. implode(", ", $sheet_header_invalid));
            }

            $sheet_row_slug = array_column($sheet_data, $map_idx['meta_slug']);
            $sheet_row_slug_duplicate =  array_filter(array_unique(array_diff_assoc($sheet_row_slug, array_unique($sheet_row_slug ))));

            if (count($sheet_row_slug_duplicate) > 0) {
                throw new Exception('Slug is duplicate:'. implode(", ", $sheet_row_slug_duplicate));
            }

            foreach ($sheet_data as $sheet_row) {

                foreach ($sheet_row as $idx => $value) {
                    $sheet_row[$idx] = trim(strval($value));
                }

                if ($sheet_row[$map_idx['collection_id']]) {
                    $collection_id = $sheet_row[$map_idx['collection_id']] ? $sheet_row[$map_idx['collection_id']] : OSC::makeUniqid('new');

                    $collections[$collection_id] = [
                        'data' => [],
                    ];

                    foreach (['title', 'custom_title', 'description',] as $key) {
                        if ($sheet_row[$map_idx[$key]] !== '') {
                            $collections[$collection_id]['data'][$key] = $sheet_row[$map_idx[$key]];
                        }
                    }

                    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $sheet_row[$map_idx['meta_slug']['slug']]) && trim($sheet_row[$map_idx['meta_slug']['slug']]) != '' ) {
                        throw new Exception('Slug is invalid: '.$sheet_row[$map_idx['meta_slug']['slug']]);
                    }

                    foreach (['meta_title','meta_slug','meta_keywords','meta_description'] as $key) {
                        if ($sheet_row[$map_idx[$key]] !== '') {
                            $collections[$collection_id]['data']['meta_tags'][substr($key, 5)] = $sheet_row[$map_idx[$key]];
                        }
                    }
                }

                if (!$collection_id) {
                    continue;
                }
            }

            if (count($collections) < 1) {
                throw new Exception('No collection was found to import');
            }

            if (!OSC::writeToFile($file_path, OSC::encode($collections))) {
                throw new Exception('Cannot write collection data to file');
            }

            $this->_ajaxResponse(['file' => $file_name]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionImportProcessDataSEO() {
        $this->checkPermission('catalog/super|catalog/collection/full|catalog/collection/importDataSEO');

        try {
            $file = $this->_request->get('file');

            $tmp_file_path = OSC_Storage::tmpGetFilePath($file);

            if (!$tmp_file_path) {
                throw new Exception('File is not exists or removed');
            }

            $JSON = OSC::decode(file_get_contents($tmp_file_path), true);

            $errors = [];
            $success = 0;

            foreach ($JSON as $collection_id => $collection_info) {
                $queue_data = [];

                $collecion = OSC::model('catalog/collection');

                if ($collection_id < 1) {
                    throw new Exception('Collection is not exist');
                }

                try {
                    $collecion->load($collection_id);

                    $collection_info['data']['collection_id'] = $collecion->getId();
                } catch (Exception $ex) {
                    if ($ex->getCode() != 404) {
                        throw new Exception($ex->getMessage());
                    }

                    $collection_info['data']['collection_id'] = 0;
                }

                $queue_data['collection'] = $collection_info['data'];

                try {

                    OSC::model('catalog/collection_bulkQueue')->setData([
                        'ukey' => 'import/' . md5(OSC::encode($queue_data)),
                        'member_id' => $this->getAccount()->getId(),
                        'action' => 'importDataSEO',
                        'queue_data' => $queue_data
                    ])->save();

                    $success++;
                } catch (Exception $ex) {
                    if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                        $success++;
                    } else {
                        $errors[] = $collecion->getId();
                    }
                }
            }

            if ($success < 1) {
                throw new Exception('Cannot add collection to import queue');
            }

            OSC::core('cron')->addQueue('catalog/collection_bulk_importDataSEO', null, ['requeue_limit' => -1]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        @unlink($tmp_file_path);

        $this->_ajaxResponse(['message' => 'Bulk import task has appended to queue' . (count($errors) < 1 ? '' : (' with ' . $success . ' collection and ' . count($errors) . " errors. Collection below cannot add to import queue:\n" . implode(', ', $errors)))]);
    }

    public function actionDelete() {
        $this->checkPermission('catalog/super|catalog/collection/delete');

        $ids = $this->_request->get('id');
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $result = [];
        foreach ($ids as $id) {
            if (intval($id) > 0) {
                $result[] = intval($id);
            }
        }
        $ids = $result;

        if (count($ids) < 1) {
            $this->error('No customize ID was found to delete');
        } else if (count($ids) > 100) {
            $this->error('Unable to delete more than 100 customize type in a time');
        }

        try {
            OSC::model('catalog/collection')->getCollection()->load($ids)->delete();
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        OSC::helper('catalog/common')->reloadFileFeedFlag();

        $this->addMessage('Successfully deleted the collection.');

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Successfully deleted the collection.']);
        }
        static::redirect($this->getUrl('list'));
    }

    public function actionBrowse() {
        try {
            /* @var $collection Model_Catalog_Collection_Collection */
            $collection = OSC::model('catalog/collection')->getCollection();

            $type = trim($this->_request->get('filter_type'));

            if (in_array($type, [Model_Catalog_Collection::COLLECT_AUTO, Model_Catalog_Collection::COLLECT_MANUAL], true)) {
                $collection->addCondition('collect_method', $type);
            }

            $collection->load();

            $catalog_collections = [];

            /* @var $catalog_collection Model_Catalog_Collection */
            foreach ($collection as $catalog_collection) {
                $catalog_collections[] = array(
                    'id' => $catalog_collection->getId(),
                    'title' => $catalog_collection->data['title'],
                    'url' => $catalog_collection->getDetailUrl(),
                    'image' => $catalog_collection->getImageUrl()
                );
            }

            $this->_ajaxResponse(array(
                'keywords' => [],
                'total' => count($catalog_collections),
                'offset' => 0,
                'current_page' => 1,
                'page_size' => count($catalog_collections),
                'items' => $catalog_collections
            ));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetCollectionInfo(){
        $id = intval($this->_request->get('id'));

        try {
            /* @var $model Model_Catalog_Collection */
            $model = OSC::model('catalog/collection');

            if ($id < 1) {
                throw new Exception('Collection ID is incorrect');
            }

            try {
                $model->load($id);
            } catch (Exception $ex) {
                throw new Exception($ex->getCode() == 404 ? 'Collection is not exist' : $ex->getMessage());
            }

            $model->data['meta_tags']['meta_image_url'] = $model->getMetaImageUrl();
            $this->_ajaxResponse($model->data);
        } catch (Exception $ex){
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionQuickEditCollection() {
        $id = intval($this->_request->get('id'));

        /* @var $model Model_Catalog_Collection */
        $model = OSC::model('catalog/collection');

        if ($id > 0) {
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getCode() == 404 ? 'Collection is not exist' : $ex->getMessage());
            }

        }

        try {
            $data['title'] = $this->_request->get('title');
            $data['slug'] = trim($this->_request->get('seo_slug'), '- ');
            $data['custom_title'] = $this->_request->get('custom_title');
            $data['allow_index'] = intval($this->_request->get('allow_index'));
            $data['description'] = $this->_request->getRaw('description');
            // get SEO PRODUCT to save meta_data
            $seo_title = trim($this->_request->get('seo_title'));
            $seo_description = trim($this->_request->get('seo_description'));
            $seo_keyword = trim($this->_request->get('seo_keyword'));
            $seo_image = $this->_request->get('seo-image');
            $banner = $this->_request->get('banner');

            if (isset($banner['pc'])) {
                $banner['pc'] =  OSC::helper('catalog/frontend')->saveCollectionBannerOnS3($banner['pc'], $model->data['meta_tags']['banner']['pc']);
            }

            if (isset($banner['mobile'])) {
                $banner['mobile'] = OSC::helper('catalog/frontend')->saveCollectionBannerOnS3($banner['mobile'], $model->data['meta_tags']['banner']['mobile']);
            }

            $data['meta_tags'] = [
                'title' => $seo_title,
                'description' => $seo_description,
                'keywords' => $seo_keyword,
                'image' => $seo_image
            ];

            $data['meta_tags']['banner'] = $banner;

            if (!$data['slug']) {
                $data['slug'] = OSC::core('string')->cleanAliasKey($data['title']);
            }

            $meta_image = OSC::helper('backend/backend/images/common')->saveMetaImage($data['meta_tags']['image'], $model->data['meta_tags']['image'], 'meta/collection/', 'collection');

            if ($meta_image['data_meta_image']) {
                $data['meta_tags']['image'] = $meta_image['data_meta_image'];
            }

            try {
                $old_seo_tag_title =  $model->data['custom_title'] ?: $model->data['title'];

                $model->setData($data)->save();

                try {
                    OSC::helper('catalog/product')->updateSeoTags($model->data['collection_id'], $old_seo_tag_title, $model->data['custom_title'] ?: $model->data['title']);
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }

                $data['slug'] = OSC::helper('alias/common')->renameSlugDuplicate($seo_title, $data['slug'], $model->getId(), 'collection');

                try {
                    OSC::helper('alias/common')->save($data['slug'], 'catalog_collection', $model->getId());
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }

                $model->setData(['slug' => $data['slug']])->save();

            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            $this->_ajaxResponse($model->data);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionGetListCollectionByIds() {
        $collection_ids = $this->_request->get('collection_ids', []);
        if (count($collection_ids) < 1) {
            $this->_ajaxResponse([]);
        }

        try {
            $collections = OSC::model('catalog/collection')->getCollection()->addField('title')->addCondition('collection_id', $collection_ids, OSC_Database::OPERATOR_IN)->load();
            $result = [];
            foreach ($collections as $collection) {
                $result[$collection->getId()] = $collection->data['title'];
            }
            $this->_ajaxResponse($result);
        } catch (Exception $ex) {
            $this->_ajaxError('Load collection error');
        }

    }
}
