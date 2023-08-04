<?php

class Controller_Catalog_Backend_PrintTemplateBeta extends Abstract_Catalog_Controller_Backend {

    protected $_default_search_field_key = 'default_search_print_beta_field';
    protected $_filter_field = ['all' => "All Field", 'id' => 'ID', 'title' => 'Title', 'member_id' => 'Member ID'];

    public function actionList() {
        $this->checkPermission('catalog/super|catalog/print_template_beta/list');

        //Search print template form
        $default_select_field = OSC::cookieGet($this->_default_search_field_key);
        if (empty($default_select_field)) {
            $default_select_field = array_key_first($this->_filter_field);
            OSC::cookieSet($this->_default_search_field_key, $default_select_field);
        }
        //End search print template form
        $filter_config = $this->_getFilterConfig();

        $data = [
            'filter_config' => $filter_config,
            'default_select_field' => $this->_default_search_field_key,
            'filter_field' => $this->_filter_field
        ];

        $output_html = $this->getTemplate()->build('catalog/product/printTemplateBeta', $data);

        $this->output($output_html);
    }

    protected function _getFilterConfig() {
        $filter_config = [];

        $filter_config['date'] = [
            'title' => 'Added date',
            'type' => 'daterange',
            'field' => 'added_timestamp',
            'prefix' => 'Added date'
        ];

        $filter_config['mdate'] = [
            'title' => 'Modified date',
            'type' => 'daterange',
            'field' => 'modified_timestamp',
            'prefix' => 'Modified date'
        ];

        return $filter_config;
    }

    public function actionSave() {
        $this->checkPermission('catalog/super|catalog/product');

        try {
            $print_id = intval($this->_request->get('print_id'));
            $title = trim($this->_request->get('title'));
            $des = trim($this->_request->getRaw('description'));
            $dpi = intval(trim($this->_request->get('dpi')));
            $rotate = intval(trim($this->_request->get('rotate')));
            $print_template_beta = trim($this->_request->get('print_template'));
            $print_template_beta_thumb = trim($this->_request->get('print_url_thumb'));

            if (empty($print_template_beta)) {
                throw new Exception('Print Template not found');
            }

            $print_model = OSC::model('catalog/printTemplate_beta');

            if ($print_id > 0) {
                $print_model->load($print_id);
            }

            $print_template_data = [
                'title' => $title,
                'description' => $des
            ];

            if ($print_template_beta != $print_model->data['config']['print_file']['print_file_url']) {
                $print_dimension = $this->_request->get('print_dimension');

                $width = $print_dimension['width'];

                $height = $print_dimension['height'];

                if (!empty($print_template_beta) && (empty($width) || empty($height))) {
                    throw new Exception('Print template beta not have dimension');
                }

                $dataPrintTemplate = OSC::core('aws_s3')->getTmpFilePath($print_template_beta);
                $dataPrintTemplate_thumb = OSC::core('aws_s3')->getTmpFilePath($print_template_beta_thumb);

                if (!OSC::core('aws_s3')->doesObjectExist($dataPrintTemplate)) {
                    throw new Exception('print template beta not isset');
                } else {
                    $filename = 'print_template_beta/' . $print_template_beta;
                    $filename_thumb = 'print_template_beta/' . $print_template_beta_thumb;

                    $filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);
                    $filename_thumb_s3 = OSC::core('aws_s3')->getStoragePath($filename_thumb);

                    try {
                        OSC::core('aws_s3')->copy($dataPrintTemplate, $filename_s3);
                        OSC::core('aws_s3')->copy($dataPrintTemplate_thumb, $filename_thumb_s3);
                    } catch (Exception $ex) {
                        throw new Exception($ex->getMessage());
                    }
                }

                $print_template_data['config'] = [
                    "print_file" => [
                        'title' => $title,
                        "dimension" => [
                            "width" => $width,
                            "height" => $height
                        ],
                        "dpi" => $dpi ?: 300,
                        "rotate" => $rotate ?: 0,
                        "print_file_url" => $filename,
                        "print_file_url_thumb" => $filename_thumb
                    ]
                ];
            } else {
                $print_template_data['config'] = $print_model->data['config'];
                $print_template_data['config']['print_file']['dpi'] = $dpi;
                $print_template_data['config']['print_file']['rotate'] = $rotate;
            }


            $print_template_data['member_id'] = OSC::helper('user/authentication')->getMember()->getId();
            $print_model->setData($print_template_data)->save();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => 'success']);
    }

    public function actionGetInfo() {
        try {
            $print_id = $this->_request->get('id');

            if ($print_id > 0) {
                $print_template_beta = OSC::model('catalog/printTemplate_beta')->load($print_id);
                $print_template_beta->data['config']['print_file']['print_file_link'] = $print_template_beta->data['config']['print_file']['print_file_url'] ? OSC::core('aws_s3')->getStorageUrl($print_template_beta->data['config']['print_file']['print_file_url']) : '';
                $this->_ajaxResponse($print_template_beta->data);
            }

            $this->_ajaxResponse([]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionSearch() {
        /* @var $collection Model_Catalog_PrintTemplate_Beta_Collection */

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

            $keywords = $this->_request->get('keywords');


            $filter_field = $this->_request->get('filter_field') ?: 'all';

            $filter_config = $this->_request->get('filter');

            $list_field_config = [
                "date" => [
                    "title" => "Added date",
                    "type" => "daterange",
                    "field" => "added_timestamp",
                    "prefix" => "Added date"
                ],
                "mdate" => [
                    "title" => "Modified date",
                    "type" => "daterange",
                    "field" => "modified_timestamp",
                    "prefix" => "Modified date"
                ]
            ];

            $filter_config_data = $this->__applyFilterConfig($filter_config, $list_field_config);

            $list_field_search = [
                'title' => OSC_Database::OPERATOR_LIKE,
                'id' => OSC_Database::OPERATOR_IN,
                'member_id' => OSC_Database::OPERATOR_EQUAL
            ];

            if ($filter_field != 'all') {
                $list_field_search = [
                    $filter_field => $list_field_search[$filter_field]
                ];
            }

            $collection = OSC::model('catalog/printTemplate_beta')->getCollection();

            if ($keywords && $keywords != '') {

                $clause_idx = OSC::makeUniqid();
                $collection->addClause($clause_idx, 'OR');

                foreach ($list_field_search as $field_search => $type) {
                    if ($field_search == 'id' || $type == 'all' && $field_search == 'id') {
                        $keywords_search = explode(' ', $keywords);
                    } else {
                        $keywords_search = $keywords;
                    }

                    $collection->addCondition($field_search, $keywords_search, $type, OSC_Database::RELATION_OR, $clause_idx);
                }

            }

            if (!empty($filter_config_data)) {
                foreach ($list_field_config as $field_config => $config) {
                    if ($config['type'] == 'daterange' && count($filter_config_data[$field_config]) > 0) {
                        $collection->addCondition($config['field'], $filter_config_data[$field_config]['start_at'], OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL, OSC_Database::RELATION_AND);
                        $collection->addCondition($config['field'], $filter_config_data[$field_config]['end_at'], OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL, OSC_Database::RELATION_AND);
                    }
                }
            }

            $collection->setPageSize($page_size)->setCurrentPage($page)->sort('id', OSC_Database::ORDER_DESC)->load();

            $items = [];

            foreach ($collection as $model) {
                $model->data['config']['print_file']['print_file_link'] = $model->data['config']['print_file']['print_file_url'] ? OSC::core('aws_s3')->getStorageUrl($model->data['config']['print_file']['print_file_url']) : '';
                $model->data['config']['print_file']['print_file_url_thumb'] = $model->data['config']['print_file']['print_file_url_thumb'] ? OSC::core('aws_s3')->getStorageUrl($model->data['config']['print_file']['print_file_url_thumb']) : '';

                $item = [
                    'id' => $model->getId(),
                    'title' => $model->data['title'],
                    'description' => $model->data['description'],
                    'config' => $model->data['config'],
                ];

                $items[] = $item;
            }

            $this->_ajaxResponse([
                'keywords' => [],
                'total' => intval($collection->collectionLength()),
                'offset' => intval((($collection->getCurrentPage() - 1) * $collection->getPageSize()) + $collection->length()),
                'current_page' => intval($collection->getCurrentPage()),
                'page_size' => intval($collection->getPageSize()),
                'items' => $items
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetSupplier() {
        $supplier_model = OSC::model('catalog/supplier')->getCollection()->addField('title', 'ukey')->load();
        $this->_ajaxResponse($supplier_model->getItems());
    }

    private function __applyFilterConfig($filter_config, $list_field_config) {
        $search_data = [];

        foreach ($filter_config as $key => $value) {
            if ($list_field_config[$key]['type'] == "daterange") {
                preg_match('/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*\-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?\s*$/', $value, $matches);

                $start_timestamp = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);

                if ($matches[5]) {
                    $end_timestamp = mktime(24, 0, 0, $matches[6], $matches[5], $matches[7]);
                } else {
                    $end_timestamp = mktime(24, 0, 0, $matches[2], $matches[1], $matches[3]);
                }


                $search_data[$key] = [
                    'start_at' => $start_timestamp,
                    'end_at' => $end_timestamp,
                ];
            }
        }

        return $search_data;
    }

    public function actionDelete() {
        $this->checkPermission('catalog/super|catalog/product');

        try {
            $print_id = intval($this->_request->get('print_id'));

            if ($print_id < 1) {
                throw new Exception('Print id not found');
            }

            $print_model = OSC::model('catalog/printTemplate_beta')->load($print_id);

            $print_model->delete();

            $this->_ajaxResponse(['message' => 'success']);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionUploadTmpPrintImg() {
        $extension = '';
        $tmp_file_path = '';

        try {
            $uploader = new OSC_Uploader();

            $tmp_file_path = OSC::getTmpDir() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $uploader->getExtension();

            try {
                $uploader->save($tmp_file_path, true);
                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }

        } catch (Exception $ex) {
            if ($ex->getCode() === 500) {
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

        $file_name = '';
        $tmp_url = '';
        $width = 0;
        $height = 0;

        try {
            $img_processor = new OSC_Image();

            $img_processor->setJpgQuality(100)->setImage($tmp_file_path);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $img_processor_thumb = new OSC_Image();

            $img_processor_thumb->setJpgQuality(100)->setImage($tmp_file_path);

            if ($width > 600) {
                $img_processor_thumb->resize(600);
            } else if ($height > 600) {
                $img_processor_thumb->resize($width, 600);
            }

            $folder_key = OSC::makeUniqid();

            $file_name = 'print-template-images.' . $this->getAccount()->getId() . '.' . $folder_key. '.' . $extension;

            $file_name_thumb = 'print-template-images.' . $this->getAccount()->getId() . '.' . $folder_key . '.thumb' . '.' . $extension;

            $tmp_url = OSC::core('aws_s3')->tmpSaveFile($img_processor, $file_name);
            OSC::core('aws_s3')->tmpSaveFile($img_processor_thumb, $file_name_thumb);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse([
            'file' => $file_name,
            'file_thumb' => $file_name_thumb,
            'url' => $tmp_url,
            'width' => $width,
            'height' => $height
        ]);
    }
}