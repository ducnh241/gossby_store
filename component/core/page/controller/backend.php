<?php

class Controller_Page_Backend extends Abstract_Backend_Controller {

    public function __construct() {
        parent::__construct();

        $this->checkPermission('page');

        $this->getTemplate()->setCurrentMenuItemKey('page/page')
            ->setPageTitle('Manage Pages');
    }

    public function actionIndex() {
        $this->forward('*/*/list');
    }

    public function actionList() {
        $collection = OSC::model('page/page')->getCollection();

        $collection->addCondition('parent_id', 0)->sort('title', OSC_Database::ORDER_ASC)->load();
        foreach ($collection as $page){
            $page->data['child_page'] = OSC::model('page/page')->getCollection()->addCondition('parent_id', $page->getId())->load();
        }

        $this->getTemplate()->addBreadcrumb(array('page', 'Manage Pages'));

        $this->output($this->getTemplate()->build('page/list', array('collection' => $collection)));
    }

    public function actionPost() {
        $id = intval($this->_request->get('id'));

        $this->checkPermission('page/' . ($id > 0 ? 'edit' : 'add'));

        /* @var $model Model_Page_Page */
        $model = OSC::model('page/page');

        if ($id > 0) {
            $this->getTemplate()->addBreadcrumb(array('page', 'Edit Pages'));
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Page is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        } else {
            $this->getTemplate()->addBreadcrumb(array('page', 'Create Pages'));
        }

        if ($this->_request->get('submit_form')) {
            $data = array();
            $data['title'] = $this->_request->get('title');
            $data['page_key'] = $this->_request->get('page_key');
            $data['type'] = $this->_request->get('type');
            $data['parent_id'] = intval($this->_request->get('parent_id'));
            $data['priority'] = intval($this->_request->get('priority'));
            $data['heading_tag'] = $this->_request->get('heading_tag');
            $data['content'] = $this->_request->getRaw('content');
            $data['image'] = $this->_request->get('image');
            $data['published_flag'] = 0;
            $data['publish_start_timestamp'] = 0;
            $data['publish_to_timestamp'] = 0;
            $data['slug'] = trim($this->_request->get('seo_slug'), '- ');
            if (!$data['slug']) {
                $data['slug'] = OSC::core('string')->cleanAliasKey($data['title']);
            }

            $additional_data = $this->_request->getRaw('additional_data', []);
            foreach ($additional_data as $key => $additional_datum) {
                if (isset($additional_datum['image']) && $additional_datum['image']) {
                    $tmp_image = OSC::core('aws_s3')->getTmpFilePath($additional_datum['image']);
                    if (OSC::core('aws_s3')->doesObjectExist($tmp_image)) {
                        $filename = 'page/' . str_replace('page.', '', $additional_datum['image']);
                        $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);

                        try {
                            OSC::core('aws_s3')->copy($tmp_image, $storage_filename_s3);
                            $additional_data[$key]['image'] = $filename;
                        } catch (Exception $ex) {
                            $additional_data[$key]['image'] = '';
                        }
                    } else {
                    }
                } else {
                    $additional_data[$key]['image'] = '';
                }
            }

            $additional_data = array_map(function ($data) {
                $data['title'] = trim($data['title']);
                $data['content'] = trim($data['content']);
                return $data;
            }, $additional_data);

            $additional_data = array_filter($additional_data, function ($data) {
                if (isset($data['title']) && $data['title'] !== '' && isset($data['content'])) {
                    return $data;
                }
            });

            $data['additional_data'] = (is_array($additional_data) && !empty($additional_data)) ?OSC::encode($additional_data) : null;

            $type_page_required_image = [Helper_Page_Common::TYPE_FAQ, Helper_Page_Common::TYPE_POLICY, Helper_Page_Common::TYPE_TERMS_OF_SERVICE];
            if (!$data['image'] && in_array($data['type'], $type_page_required_image)){
                $this->addErrorMessage('Please upload image for this page');
                static::redirect($this->getUrl(null, array('id' => $model->getId())));
            }

            $image_to_rmv = '';
            if ($data['image'] != $model->data['image']) {
                if (!$data['image']) {
                    $data['image'] = '';
                    $image_to_rmv = $model->data['image'];
                } else {
                    $tmp_image = OSC::core('aws_s3')->getTmpFilePath($data['image']);
                    if (!OSC::core('aws_s3')->doesObjectExist($tmp_image)) {
                        $data['image'] = $model->data['image'];
                    } else {
                        $filename = 'page/' . str_replace('page.', '', $data['image']);
                        $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);

                        try {
                            OSC::core('aws_s3')->copy($tmp_image, $storage_filename_s3);

                            $data['image'] = $filename;
                        } catch (Exception $ex) {
                            $data['image'] = $model->data['image'];
                        }
                    }
                }
            }

            $publish_mode = intval($this->_request->get('publish_mode'));
            // set meta seo page
            $seo_title = trim($this->_request->get('seo-title'));
            $seo_description = trim($this->_request->get('seo-description'));
            $seo_keyword = trim($this->_request->get('seo-keyword'));
            $seo_image = $this->_request->get('seo-image');

            $data['meta_tags'] = [
                'title'         => $seo_title,
                'description'   => $seo_description,
                'keywords'       => $seo_keyword,
                'image'         => $seo_image
            ];

            $meta_image =  OSC::helper('backend/backend/images/common')->saveMetaImage($data['meta_tags']['image'],$model->data['meta_tags']['image'], 'meta/page/', 'page');

            if ($meta_image['data_meta_image']){
                $data['meta_tags']['image'] = $meta_image['data_meta_image'];
            }

            if ($publish_mode === 1) {
                $data['published_flag'] = 1;
            } else if ($publish_mode === 2) {
                $date_time_data = array(
                    'publish_start_timestamp' => array('date' => $this->_request->get('publish_start_date') == null ? 0 : $this->_request->get('publish_start_date'), 'time' => $this->_request->get('publish_start_date')== null ? 0 : $this->_request->get('publish_start_date')),
                    'publish_to_timestamp' => array('date' => $this->_request->get('publish_to_date') == null ? 0 : $this->_request->get('publish_to_date'), 'time' => $this->_request->get('publish_to_date')== null ? 0 : $this->_request->get('publish_to_date'))
                );

                foreach ($date_time_data as $timestamp_key => $date_time) {
                    if($date_time['date'] == 0 &&  $date_time['time'] == 0){
                        $data[$timestamp_key] = 0;
                    }else{
                        $date_time['date'] = explode('/', $date_time['date']);
                        $date_time['time'] = explode(':', $date_time['time']);

                        for ($i = 0; $i < 3; $i ++) {
                            if (!isset($date_time['date'][$i])) {
                                $date_time['date'][$i] = 0;
                            } else {
                                $date_time['date'][$i] = abs(intval($date_time['date'][$i]));
                            }
                        }

                        for ($i = 0; $i < 2; $i ++) {
                            if (!isset($date_time['time'][$i])) {
                                $date_time['time'][$i] = 0;
                            } else {
                                $date_time['time'][$i] = abs(intval($date_time['time'][$i]));
                            }
                        }

                        $timestamp = mktime($date_time['time'][0], $date_time['time'][1], 0, $date_time['date'][1], $date_time['date'][0], $date_time['date'][2]);

                        if ($timestamp === false || $timestamp < 0) {
                            $timestamp = 0;
                        }

                        $data[$timestamp_key] = $timestamp;
                    }
                }
            }

            try {
                $model->setData($data)->save();

                $data['slug'] = OSC::helper('alias/common')->renameSlugDuplicate($seo_title, $data['slug'], $model->getId(), 'page');
                try {
                    OSC::helper('alias/common')->save($data['slug'], 'page', $model->getId());
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }

                $model->setData(['slug' => $data['slug']])->save();

                if ($image_to_rmv) {
                    unlink(OSC_Storage::getStoragePath($image_to_rmv));
                }

                if ($meta_image['image_to_rm'] != null) {
                    unlink(OSC_Storage::getStoragePath($meta_image['image_to_rm']));
                }

                if ($id > 0) {
                    $message = 'Page #' . $model->getId() . ' updated';
                } else {
                    $message = 'Page [#' . $model->getId() . '] "' . $model->data['title'] . '" added';
                }

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

        $output_html = $this->getTemplate()->build('page/postForm', array(
            'form_title' => $model->getId() > 0 ? ('Edit page #' . $model->getId() . ': ' . $model->getData('title', true)) : 'Add new page',
            'model' => $model
        ));

        $this->output($output_html);
    }

    public function actionDelete() {
        $this->checkPermission('page/delete');
        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            /* @var $model Model_Page_Page */
            $model = OSC::model('page/page');
            $alias_model = OSC::core('Controller_Alias_Model');

            try {
                $model->load($id);

                if ($model->isSystemPage()) {
                    throw new Exception('Unable to delete a system page');
                }

                try {
                    $ukey_alias_model = 'page/' . $id;
                    $alias_model->loadByUkey($ukey_alias_model)->delete();
                } catch (Exception $ex) {

                }

                $model->delete();
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    $this->addErrorMessage($ex->getMessage());
                    static::redirect($this->getUrl('list'));
                }
            }

            $this->addMessage('Deleted the page #' . $id . ' [' . $model->data['title'] . ']');
        }

        static::redirect($this->getUrl('list'));
    }

    public function actionBrowse() {
        /* @var $search OSC_Search_Adapter */
        $search = OSC::core('search')->getAdapter('backend');

        $page_size = intval($this->_request->get('page_size'));

        if ($page_size == 0) {
            $page_size = 25;
        } else if ($page_size < 5) {
            $page_size = 5;
        } else if ($page_size > 100) {
            $page_size = 100;
        }

        try {
            $search->setKeywords($this->_request->get('keywords'));
            $search->addFilter('module_key', 'page', OSC_Search::OPERATOR_EQUAL);

            $page = intval($this->_request->get('page'));

            if ($page < 1) {
                $page = 1;
            }

            $search->setOffset(($page - 1) * $page_size)->setPageSize($page_size);

            $result = $search->fetch(array('allow_no_keywords', 'auto_fix_page'));

            $pages = array();

            if (count($result['docs']) > 0) {
                $page_ids = array();

                foreach ($result['docs'] as $doc) {
                    $page_ids[] = $doc['item_id'];
                }

                $collection = OSC::model('page/page')->getCollection()->load($page_ids);

                /* @var $page Model_Page_Page */
                foreach ($collection as $page) {
                    $pages[] = array(
                        'id' => $page->getId(),
                        'title' => $page->data['title'],
                        'url' => $page->getDetailUrl(),
                        'icon' => 'file-regular'
                    );
                }
            }

            $this->_ajaxResponse(array(
                'keywords' => $result['keywords'],
                'total' => $result['total_item'],
                'offset' => $result['offset'],
                'current_page' => $result['current_page'],
                'page_size' => $result['page_size'],
                'items' => $pages
            ));
        } catch (OSC_Search_Exception_Condition $e) {
            $this->_ajaxError($e->getMessage(), $e->getCode());
        }
    }

    public function actionUploadImage() {
        $this->checkPermission('page/edit|page/add');

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
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path)->resize(3000);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $file_name = 'page.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

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

    protected function _processPostImages($page, $images) {
        $counter = 0;

        foreach ($images as $image_id => $image_alt) {
            $images[$image_id] = array(
                'position' => ++$counter,
                'id' => $image_id,
                'alt' => $image_alt
            );
        }

        foreach ($images as $image_tmp_name => $image_data) {
            $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($image_tmp_name);
            if (!OSC::core('aws_s3')->doesObjectExist($tmp_image_path_s3)) {
                continue;
            }

            $filename = 'page/' . $page->getId() . '/' . str_replace('page.', '', $image_tmp_name);
            $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);

            try {
                OSC::core('aws_s3')->copy($tmp_image_path_s3, $storage_filename_s3);
            } catch (Exception $ex) {
                continue;
            }

            $images[$image_tmp_name]['url'] = OSC::core('aws_s3')->getStorageUrl($filename);
        }

        try {
            $page->setData(['images' => OSC::encode(array_values($images))])->save();
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
        }
    }
}