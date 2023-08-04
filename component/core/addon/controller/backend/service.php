<?php

class Controller_Addon_Backend_Service extends Abstract_Backend_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('addon_service');
        $this->getTemplate()->setPageTitle('Manage Add-on Services');

        $this->getTemplate()->setCurrentMenuItemKey('product_config/addon_service');
    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    public function actionList()
    {
        $addon_services = OSC::model('addon/service')->getCollection();
        $this->getTemplate()->addBreadcrumb('Add-on Services');
        $addon_services->sort('id', OSC_Database::ORDER_DESC)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $this->output($this->getTemplate()->build('addon/service/list',
            [
                'addon_services' => $addon_services
            ])
        );
    }

    public function actionUploadImage()
    {
        $this->checkPermission('addon_service/edit|addon_service/add');

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
                    $url_info = OSC::core('network')->curl($image_url, ['browser']);

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

            $file_name = 'addon_service.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

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

    public function actionPost()
    {
        $id = intval($this->_request->get('id'));
        $mode = $this->_request->get('mode', '');

        if ($id > 0) {
            $this->checkPermission('addon_service/view|addon_service/edit');
        } else {
            $this->checkPermission('addon_service/add');
        }

        /* @var $model Model_Addon_Service */
        $model = OSC::model('addon/service');

        $this->getTemplate()->addBreadcrumb('Add-on Services', $this->getUrl('list'));

        if ($id > 0) {
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Add-on service is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        }
        
        $max_video_size = OSC::helper('core/setting')->get('catalog/video_config/max_file_size');
        $max_video_size = $max_video_size ?? 0;

        $response = [];

        if (key_exists('title', $this->_request->getAll('post'))) {
            $this->checkPermission('addon_service/' . ($id > 0 ? 'edit' : 'add'));
            $data = [];

            try {
                $data = $this->_validatePostForm($data, $model);

                $versions = $data['versions'];
                unset($data['versions']);

                $model->setData($data)->save();

                $this->_processPostVersions($model, $versions);

                if ($id > 0) {
                    $message = 'Your update has been saved successfully.';
                } else {
                    $message = 'Add-on service has been saved successfully.';
                }

                $this->addMessage($message);

                if (!$this->_request->get('continue')) {
                    static::redirect($this->getUrl('list'));
                } else {
                    static::redirect($this->getUrl(null, ['id' => $model->getId()]));
                }
            } catch (Exception $ex) {
                $response = $this->_request->getAll();
                $response['version_data'] = OSC::decode($response['version_data']);
                foreach (['type', 'status', 'ab_test_enable', 'continue', 'id'] as $key) {
                    if (isset($response[$key])) {
                        $response[$key] = intval($response[$key]);
                    }
                }
                $response['product_type_id'] = intval(explode('/', $this->_request->get('product_type_info', 0))[0]);
                $response['auto_apply_for_product_type_variants'] = $this->_getAddonSelectedProductTypeVariants();

                $this->addErrorMessage($ex->getMessage());
            }
        } else {
            $response = $model->data;
            $response['version_data'] = OSC::helper('addon/service')->getAllVersions($id);
            $response['ab_test_time'] = OSC::helper('addon/service')->formatDateRange($model->data['ab_test_start_timestamp'], $model->data['ab_test_end_timestamp']);
            $response['active_time'] = OSC::helper('addon/service')->formatDateRange($model->data['start_timestamp'], $model->data['end_timestamp']);
        }

        $product_type_variants = OSC::helper('catalog/productType')->getAllVariants();
        $version_data = OSC::helper('addon/service')->getAllVersions($id);

        $this->getTemplate()->setPageTitle($model->getId() > 0
            ? $mode === 'view'
                ? 'View Add-on Service Config'
                : 'Edit Add-on Service'
            : 'Create New Add-on Service');

        $output_html = $this->getTemplate()->build('addon/service/postForm', [
            'form_title' => $model->getId() > 0 ? 'Edit Add-on Service' : 'Create New Add-on Service',
            'model' => $model,
            'mode' => $mode,
            'max_video_size' => $max_video_size,
            'product_type_variants' => $product_type_variants,
            'product_types' => OSC::helper('addon/service')->getProductTypeData(),
            'version_data' => $version_data,
            'response' => $response,
        ]);

        $this->output($output_html);
    }

    protected function _getAddonSelectedProductTypeVariants() {
        $auto_apply_for_product_type_variants = $this->_request->get('auto_apply_for_product_type_variants');

        if ($auto_apply_for_product_type_variants === '*') {
            $auto_apply_for_product_type_variants = ['*'];
        } else {
            $auto_apply_for_product_type_variants = OSC::decode($auto_apply_for_product_type_variants);

            if (is_array($auto_apply_for_product_type_variants)) {
                foreach ($auto_apply_for_product_type_variants as $key => $variant_ids) {
                    if ($variant_ids === '*') {
                        $auto_apply_for_product_type_variants[$key] = ['*'];
                    } else if (!is_array($variant_ids)) {
                        unset($auto_apply_for_product_type_variants[$key]);
                    }
                }
            } else {
                $auto_apply_for_product_type_variants = null;
            }
        }

        return $auto_apply_for_product_type_variants;
    }

    /**
     * @throws Exception
     */
    protected function _validatePostForm($data, Model_Addon_Service $model)
    {
        $data['title'] = trim($this->_request->get('title'));

        if (!$data['title']) {
            throw new Exception('Add-on Title is required');
        }

        $data['status'] = intval($this->_request->get('status'));
        $data['type'] = intval($this->_request->get('type'));
        $data['ab_test_enable'] = intval($this->_request->get('ab_test_enable'));

        if ($data['ab_test_enable']) {
            $ab_test_time_range = $this->_convertDateRange($this->_request->get('ab_test_time'));

            if (!$ab_test_time_range) {
                throw new Exception('A/B test time is required.');
            }

            $data['ab_test_start_timestamp'] = $ab_test_time_range['start_timestamp'];
            $data['ab_test_end_timestamp'] = $ab_test_time_range['end_timestamp'];

            $conflict_time_addon = OSC::helper('addon/service')->getConflictAbTestTimeAddon($model->getId(), $data['ab_test_start_timestamp'], $data['ab_test_end_timestamp']);

            if ($conflict_time_addon) {
                $conflict_addon_id = $conflict_time_addon->getId();
                $conflict_start_date = date('d/m/y', $conflict_time_addon->data['ab_test_start_timestamp']);
                $conflict_end_date = date('d/m/y', $conflict_time_addon->data['ab_test_end_timestamp']);

                throw new Exception("A/B test time of addon service ID($conflict_addon_id) is set from $conflict_start_date to $conflict_end_date. Can not set A/B test for other addon service in this time.");
            }
        }

        if ($data['type'] === Model_Addon_Service::TYPE_ADDON) {
            // Validate auto apply for product type
            $data['auto_apply_for_product_type_variants'] = $this->_getAddonSelectedProductTypeVariants();

            // Validate start + end date when using auto apply for product type
            if ($data['auto_apply_for_product_type_variants']) {
                $active_time = $this->_request->get('active_time');

                if (!$active_time) {
                    throw new Exception('Active Time is required when you set `Auto apply for product types`.');
                }

                $active_time = $this->_convertDateRange($active_time);

                if (!$active_time) {
                    throw new Exception('Active Time is invalid.');
                }

                $data['start_timestamp'] = $active_time['start_timestamp'];
                $data['end_timestamp'] = $active_time['end_timestamp'];
            }
        } else if ($data['type'] === Model_Addon_Service::TYPE_VARIANT) {
            $data['product_type_id'] = intval(explode('/', $this->_request->get('product_type_info', 0))[0]);
        }

        $version_data = $this->_request->get('version_data');

        $data['versions'] = $this->_validateVersions(OSC::decode($version_data), $data['type']);

        return $data;
    }

    protected function _validateVersions($version_data, $addon_type) {
        foreach ($version_data as $version_id => $version) {
            $data = [];

            if ($addon_type === Model_Addon_Service::TYPE_ADDON) {
                $data = [
                    'title' => $version['title'],
                    'display_area' => intval($version['display_area']),
                    'is_default_version' => intval($version['is_default_version']),
                    'is_hide' => intval($version['is_hide']),
                    'images' => $version['images'],
                    'videos' => $version['videos'],
                    'data' => [
                        'service_title' => $version['service_title'],
                        'group_price' => OSC::helper('catalog/common')->floatToInteger(doubleval($version['group_price'])),
                        'show_message' => intval($version['show_message']),
                        'placeholder' => $version['placeholder'],
                        'description' => $version['description'],
                        'enable_same_price' => intval($version['enable_same_price']),
                        'auto_select' => intval($version['auto_select']),
                        'max_version_index' => intval($version['max_version_index']),
                    ]
                ];
            } else if ($addon_type === Model_Addon_Service::TYPE_VARIANT) {
                $data = [
                    'title' => $version['title'],
                    'display_area' => intval($version['display_area']),
                    'is_default_version' => intval($version['is_default_version']),
                    'is_hide' => intval($version['is_hide']),
                    'images' => $version['images'],
                    'videos' => $version['videos'],
                    'data' => [
                        'service_title' => $version['service_title'],
                        'max_version_index' => intval($version['max_version_index']),
                    ]
                ];
            }

            $data['data']['options'] = $this->_validateOptions($version['options'], $data['data']['group_price']);

            if (
                $data['display_area'] != Model_Addon_Service::DISPLAY_CART_ONLY &&
                $data['display_area'] != Model_Addon_Service::DISPLAY_CART_AND_PRODUCT
            ) {
                $data['display_area'] = Model_Addon_Service::DISPLAY_CART_ONLY;
            }

            $version_data[$version_id] = $data;
        }

        return $version_data;
    }

    protected function _validateOptions($options, $same_price) {
        if (!is_array($options) || count($options) < 1) {
            throw new Exception('You must add at least 1 option!');
        }

        foreach ($options as $option_id => $option) {
            $data = [
                'id' => $option['id'],
                'title' => $option['title'],
                'price' => $same_price ?: OSC::helper('catalog/common')->floatToInteger(doubleval($option['price'])),
                'image' => $option['image'],
                'is_default' => intval($option['is_default']),
            ];

            $options[$option_id] = $data;
        }

        return $options;
    }

    protected function _convertDateRange($date_range) {
        if (!$date_range) return false;

        $date_range = explode(' - ', $date_range);

        preg_match("/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})\s*$/", $date_range[0], $start_date);
        preg_match("/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})\s*$/", $date_range[1], $end_date);

        if (count($start_date) < 4 || count($end_date) < 4) {
            return false;
        }

        return [
            'start_timestamp' => mktime(0, 0, 1, $start_date[2], $start_date[1], $start_date[3]),
            'end_timestamp' => mktime(23, 59, 59, $end_date[2], $end_date[1], $end_date[3]),
        ];
    }

    protected function _processPostVersions($addon, $versions) {
        $old_versions = OSC::model('addon/version')->getCollection()->addCondition('addon_id', $addon->getId(), OSC_Database::OPERATOR_EQUAL)->load()->toArray();
        $old_version_ids = array_column($old_versions, 'id');

        foreach ($old_version_ids as $idx => $old_version_id) {
            $old_version_ids[$idx] = (string)$old_version_id;
        }

        $new_version_ids = array_keys($versions);
        $remove_version_ids = array_diff($old_version_ids, $new_version_ids);
        $added_version_ids = array_diff($new_version_ids, $old_version_ids);

        $upload_image_mapping = [];
        $upload_video_mapping = [];

        foreach ($versions as $version_id => $version_data) {
            $version_model = OSC::model('addon/version');

            try {
                $version_model->load($version_id);
            } catch (Exception $ex) {
            }

            $version_data['addon_id'] = $addon->getId();
            $version_data['images'] = $this->_processPostImages($addon, $version_data['images'], $upload_image_mapping);
            $version_data['videos'] = $this->_processPostVideos($addon, $version_data['videos'], $upload_video_mapping);

            if (count($added_version_ids)) {
                $version_data['traffic'] = 0;
            }

            foreach ($version_data['data']['options'] as $option_key => $option) {
                if ($option['image']) {
                    $image_path_s3 = OSC::core('aws_s3')->getStoragePath($option['image']);
                    $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($option['image']);

                    if (OSC::core('aws_s3')->doesObjectExist($image_path_s3)) {
                        continue;
                    } else if (OSC::core('aws_s3')->doesObjectExist($tmp_image_path_s3)) {
                        $filename = 'addon_service/' . str_replace('addon_service.', '', $option['image']);
                        $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);

                        try {
                            OSC::core('aws_s3')->copy($tmp_image_path_s3, $storage_filename_s3);
                            $option['image'] = $filename;
                        } catch (Exception $ex) {
                            $option['image'] = '';
                        }
                    } else {
                        $option['image'] = '';
                    }

                    $version_data['data']['options'][$option_key]['image'] = $option['image'];
                }
            }

            $version_model->setData($version_data)->save();
        }

        if (count($remove_version_ids)) {
            OSC::model('addon/version')->getCollection()->addCondition('id', $remove_version_ids, OSC_Database::OPERATOR_IN)->delete();
        }
    }

    protected function _processPostImages($addon, $images, &$upload_image_mapping) {
        $s3_storage_dir_url = OSC::core('aws_s3')->getStorageDirUrl();

        $result = [];

        foreach ($images as $image) {
            if ($image['fileId'] && $upload_image_mapping[$image['fileId']]) {
                $image['id'] = $upload_image_mapping[$image['fileId']]['id'];
                $image['url'] = $upload_image_mapping[$image['fileId']]['url'];
            } else if ($image['fileId']) {
                if (!OSC_Storage::tmpUrlIsExists($image['url'])) {
                    $this->_ajaxError('Image url error ' . $image['url']);
                }

                $image['id'] = OSC::makeUniqid();

                $image_file_extension = preg_replace('/^.*(\.[a-zA-Z0-9]+)$/', '\\1', $image['url']);
                $image_file_extension = strtolower($image_file_extension);

                $image_file_name = 'addon_service/mockup_image/' . $addon->getId() . '/' . md5($image['url']) . $image_file_extension;
                $image_file_name_s3 = OSC::core('aws_s3')->getStoragePath($image_file_name);

                $image_tmp_file_name = OSC::core('aws_s3')->tmpGetFileNameFromUrl($image['url']);
                $image_tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($image_tmp_file_name);

                $image['url'] = $image_file_name;

                $upload_image_mapping[$image['fileId']] = $image;

                if (!OSC::core('aws_s3')->doesObjectExist($image_file_name_s3)) {
                    OSC::core('aws_s3')->copy($image_tmp_file_name_s3, $image_file_name_s3);
                }
            } else {
                $image['url'] = str_replace($s3_storage_dir_url . '/', '', $image['url']);
            }

            $result[] = [
                'id' => $image['id'],
                'url' => $image['url'],
                'position' => $image['position'],
            ];
        }

        return $result;
    }

    protected function _processPostVideos($addon, $videos, &$upload_video_mapping) {
        $s3_storage_dir_url = OSC::core('aws_s3')->getStorageDirUrl();

        $result = [];

        foreach ($videos as $video) {
            if ($video['fileId'] && $upload_video_mapping[$video['fileId']]) {
                $video['id'] = $upload_video_mapping[$video['fileId']]['id'];
                $video['url'] = $upload_video_mapping[$video['fileId']]['url'];
            } else if ($video['fileId']) {
                if (!OSC_Storage::tmpUrlIsExists($video['url'])) {
                    $this->_ajaxError('Image url error ' . $video['url']);
                }

                $video['id'] = OSC::makeUniqid();

                $video_file_extension = preg_replace('/^.*(\.[a-zA-Z0-9]+)$/', '\\1', $video['url']);
                $video_file_extension = strtolower($video_file_extension);

                $video_file_name = 'addon_service/mockup_video/' . $addon->getId() . '/' . md5($video['url']) . $video_file_extension;
                $video_file_name_s3 = OSC::core('aws_s3')->getStoragePath($video_file_name);

                $video_tmp_file_name = OSC::core('aws_s3')->tmpGetFileNameFromUrl($video['url']);
                $video_tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($video_tmp_file_name);

                $video['url'] = $video_file_name;

                $upload_video_mapping[$video['fileId']] = $video;

                if (!OSC::core('aws_s3')->doesObjectExist($video_file_name_s3)) {
                    OSC::core('aws_s3')->copy($video_tmp_file_name_s3, $video_file_name_s3);
                }
            } else {
                $video['url'] = str_replace($s3_storage_dir_url . '/', '', $video['url']);
            }

            $thumbnail_url = str_replace($s3_storage_dir_url . '/', '', $video['thumbnail']);

            if (OSC::core('aws_s3')->doesStorageObjectExist($thumbnail_url)) {
                $video['thumbnail'] = $thumbnail_url;
            } else {
                $thumbnail_file_name = 'addon_service/mockup_thumbnail/' . $addon->getId() . '/' . md5($video['thumbnail']) . '.png';
                $thumbnail_file_name_s3 = OSC::core('aws_s3')->getStoragePath($thumbnail_file_name);

                $thumbnail_tmp_file_name = OSC::core('aws_s3')->tmpGetFileNameFromUrl($video['thumbnail']);
                $thumbnail_tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($thumbnail_tmp_file_name);

                $video['thumbnail'] = $thumbnail_file_name;

                if (!OSC::core('aws_s3')->doesObjectExist($thumbnail_file_name_s3)) {
                    OSC::core('aws_s3')->copy($thumbnail_tmp_file_name_s3, $thumbnail_file_name_s3);
                }
            }

            $result[] = [
                'id' => $video['id'],
                'url' => $video['url'],
                'thumbnail' => $video['thumbnail'],
                'position' => $video['position'] ?? 0,
                'duration' => $video['duration'] ?? 0,
            ];
        }

        return $result;
    }

    protected function _getModifiedLog($new_data, $old_data)
    {
        $log = [];

        foreach ([
                     'title',
                     'product_type_id',
                     'data/show_message',
                     'data/placeholder',
                     'data/description',
                     'data/enable_same_price',
                     'data/group_price',
                     'data/service_title',
                 ] as $key) {
            $key_split = explode('/', $key);
            $old_value = $old_data[$key_split[0]];
            $new_value = $new_data[$key_split[0]];

            if (count($key_split) > 1) {
                $old_value = $old_value[$key_split[1]];
                $new_value = $new_value[$key_split[1]];
            }

            if ($old_value != $new_value) {
                $log[$key] = [
                    "old" => $old_value,
                    "new" => $new_value,
                ];
            }
        }

        foreach ($old_data['data']['options'] as $key => $old_option) {
            $new_options = $new_data['data']['options'][$key];
            if (empty($new_options)) {
                $log["delete option ${key}"] = $old_option;
                continue;
            }

            foreach (['title', 'price', 'show_message', 'placeholder', 'description', 'image'] as $option_key) {
                if ($old_option[$option_key] != $new_options[$option_key]) {
                    $log["data/options/${key}/${option_key}"] = [
                        'old' => $old_option[$option_key],
                        'new' => $new_options[$option_key],
                    ];
                }
            }

            unset($new_data['data']['options'][$key]);
        }

        if (count($new_data['data']['options'])) {
            foreach ($new_data['data']['options'] as $key => $new_option) {
                $log["add option ${key}"] = $new_option;
            }
        }

        return $log;
    }

    public function actionDelete()
    {
        $this->checkPermission('addon_service/delete');

        $id = intval($this->_request->get('id'));

        if ($id < 1) {
            $this->error('Addon service ID is invalid!');
        }

        try {
            OSC::helper('addon/service')->checkAddonServiceIsUsing($id);
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
            static::redirect($this->getUrl('list'));
        }

        try {
            OSC::model('addon/service')->load($id)->delete();
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        $this->addMessage('Successfully deleted the add-on service.');

        static::redirect($this->getUrl('list'));
    }

}

