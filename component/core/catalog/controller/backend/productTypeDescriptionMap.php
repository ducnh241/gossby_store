<?php

class Controller_Catalog_Backend_ProductTypeDescriptionMap extends Abstract_Backend_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->checkPermission('catalog/product_config/product_type_description/map');

        $this->getTemplate()
            ->setCurrentMenuItemKey('product_config/product_type_description_map')
            ->setPageTitle('Manage Product Type Description Map')
            ->push('catalog/product_type.js', 'js')
            ->push('vendor/bootstrap/bootstrap-grid.min.css', 'css')
            ->push('catalog/product_type.scss', 'css');
    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    protected function _applyListCondition(Model_Catalog_ProductType_Collection $collection): void
    {
        $search = OSC::sessionGet('product_config/product_type_description_map/search');

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer
                ->addKeyword($collection->getPkFieldName(), $collection->getPkFieldName(), OSC_Search_Analyzer::TYPE_INT, true, true)
                ->addKeyword('group_name', 'group_name', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('tab_name', 'tab_name', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('title', 'title', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('ukey', 'ukey', OSC_Search_Analyzer::TYPE_STRING, true, true)
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
            'product_config/product_type_description_map/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
        ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    public function actionList()
    {
        $template = $this->getTemplate()
            ->addBreadcrumb(['cog', 'Manage Product Type Description Map']);

        $collection = OSC::model('catalog/productType')->getCollection();

        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }
        $collection->sort('id', OSC_Database::ORDER_DESC)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $this->output($template->build('catalog/product/productTypeDescriptionMap/list', [
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
                ->addBreadcrumb(['cog', 'Edit Product Type Description Map']);
            try {
                $model = OSC::model('catalog/productType')->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        } else {
            $this->getTemplate()
                ->addBreadcrumb(['cog', 'Create Product Type Description Map']);
            $model = OSC::model('catalog/productType');
        }

        $product_type_description_id = intval($this->_request->get('product_type_description_id'));
        $is_submit = intval($this->_request->get('is_submit'));
        $product_type_descriptions = $this->_request->get('product_type_descriptions');

        if ($is_submit) {
            $size_guide_allow = intval($this->_request->get('size_guide_allow', 0));
            $size_guide_image = $this->_request->get('size_guide_image', '');
            $size_guide_input = $this->_request->get('size_guide_input', '');

            try {
                $model->setData([
                    'description_id' => $product_type_description_id,
                    'size_guide_data' => [
                        'allow' => $size_guide_allow,
                        'image' => $this->_processImage($size_guide_image, $model),
                        'data' => OSC::decode($size_guide_input)
                    ]
                ])->save();
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
                static::redirect($this->getUrl('*/*/*', array('id' => $model->getId())));
            }

            foreach ($product_type_descriptions as $product_type_variant_id => $product_type_description) {
                $product_type_variant_id = intval($product_type_variant_id);
                try {
                    $product_type_variant = OSC::model('catalog/productType_variant')->load($product_type_variant_id);
                    $product_type_variant->setData(
                        ['description_id' => $product_type_description]
                    )->save();
                } catch (Exception $ex) {

                }
            }

            $message = $id > 0 ? 'Your update has been saved successfully.' : 'Product Type Description Map has been saved successfully.';
            $this->addMessage($message);

            static::redirect($this->getUrl(null, ['id' => $model->getId()]));

        }
        $product_type_variants = OSC::model('catalog/productType_variant')->getCollection()
            ->addCondition('product_type_id', $id)->sort('title')->load();

        $product_type_descriptions = OSC::model('catalog/productTypeDescription')->getCollection()
            ->addField('id', 'title')->sort('title')->load();

        $output_html = $this->getTemplate()->build('catalog/product/productTypeDescriptionMap/postForm', [
            'form_title' => $model->getId() > 0 ? ('Map product type description #' . $model->getId()) : '',
            'model' => $model,
            'product_type_variants' => $product_type_variants,
            'product_type_descriptions' => $product_type_descriptions
        ]);

        $this->output($output_html);
    }

    public function actionUploadImage()
    {
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
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path)->resize(700);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $file_name = 'productType.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

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

    protected function _processImage($file_name, $model)
    {
        $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($file_name);

        if (!$file_name || !OSC::core('aws_s3')->doesObjectExist($tmp_image_path_s3)) {
            return $model->data['size_guide_data']['image'] ?? '';
        }

        try {
            $file_name = 'productType/' . str_replace('productType.', '', $file_name);
            $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($file_name);

            OSC::core('aws_s3')->copy($tmp_image_path_s3, $storage_filename_s3);
            return $file_name;

        } catch (Exception $ex) {
        }
        return '';
    }
}
