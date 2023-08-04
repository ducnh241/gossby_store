<?php

class Controller_Navigation_Backend extends Abstract_Backend_Controller {

    public function __construct() {
        parent::__construct();
        
        $this->checkPermission('navigation');

        $this->getTemplate()
            ->setPageTitle('Manage Navigations')
            ->setCurrentMenuItemKey('navigation');
    }

    public function actionIndex() {
        $this->forward('*/*/list');
    }

    public function actionList() {
        $collection = OSC::model('navigation/navigation')->getCollection();

        $collection->sort('title', OSC_Database::ORDER_ASC)->load();
        $this->getTemplate()
            ->addBreadcrumb(array('navigation', 'Manage Navigations'));

        $this->output($this->getTemplate()->build('navigation/list', array('collection' => $collection)));
    }

    public function actionPost() {
        $id = intval($this->_request->get('id'));
        
        $this->checkPermission('navigation/' . ($id > 0 ? 'edit' : 'add'));

        /* @var $model Model_Navigation_Navigation */
        $model = OSC::model('navigation/navigation');

        if ($id > 0) {
            $this->getTemplate()
                ->addBreadcrumb(array('navigation', 'Edit Navigation'));
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Navigation is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        } else {
            $this->getTemplate()
                ->addBreadcrumb(array('navigation', 'Create Navigation'));
        }

        if ($this->_request->get('title')) {
            $data = array();

            $data['title'] = $this->_request->get('title');
            $data['items'] = $this->_request->get('items');

            try {
                $model->setData($data)->save();

                if ($id > 0) {
                    $message = 'Navigation #' . $model->getId() . ' updated';
                } else {
                    $message = 'Navigation [#' . $model->getId() . '] "' . $model->data['title'] . '" added';
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

        $output_html = $this->getTemplate()->build('navigation/postForm', array(
            'form_title' => $model->getId() > 0 ? ('Edit navigation #' . $model->getId() . ': ' . $model->getData('title', true)) : 'Add new navigation',
            'model' => $model
        ));

        $this->output($output_html);
    }

    public function actionUploadImage()
    {
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
                    throw new Exception($this->_('core.err_data_incorrect'));
                }

                $tmp_file_path = OSC::getTmpDir() . '/' . md5($file_url);

                if (!file_exists($tmp_file_path)) {
                    $url_info = OSC::core('network')->curl($file_url, ['browser']);

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
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $file_name = 'navigation/' . date('d.m.Y') . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;


            OSC::core('aws_s3')->tmpSaveFile($img_processor, $file_name);

            $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($file_name);
            $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($file_name);
            $file_url = OSC::core('aws_s3')->getStorageUrl($file_name);

            OSC::core('aws_s3')->copy($tmp_image_path_s3, $storage_filename_s3);
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
        $this->checkPermission('navigation/delete');
        
        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            /* @var $model Model_Navigation_Navigation */
            $model = OSC::model('navigation/navigation');

            try {
                $model->load($id);
                $model->delete();
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    $this->addErrorMessage($ex->getMessage());
                    static::redirect($this->getUrl('list'));
                }
            }

            $this->addMessage('Deleted the navigation #' . $id . ' [' . $model->data['title'] . ']');
        }

        static::redirect($this->getUrl('list'));
    }

    public function actionBrowse() {
        $data = array(
            'current_page' => 1,
            'offset' => 0,
            'keywords' => array(),
            'items' => array(
                array('icon' => 'home', 'title' => 'Home page', 'url' => OSC::$base_url)
            )
        );

        OSC::core('observer')->dispatchEvent('navigation/collect_item_type', array('items' => &$data['items']));

        $data['page_size'] = $data['total'] = count($data['items']);

        $this->_ajaxResponse($data);
    }

}
