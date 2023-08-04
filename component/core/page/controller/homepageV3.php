<?php

class Controller_Page_HomepageV3 extends Abstract_Backend_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('page/home_page/update');

        $this->getTemplate()->setCurrentMenuItemKey('page/homepage_v3')
            ->setPageTitle('Homepage V3');
    }

    public function actionIndex()
    {
        $this->forward('*/*/post');
    }

    public function actionClearCache() {
        try {
            OSC::helper('core/cache')->deleteByPattern(['actionGetHomeSection']);
            $this->addMessage('Clear cached Homepage success!');
            static::redirect($this->getUrl('*/*/post'));
        } catch (Exception $ex) {
            $this->addErrorMessage('Clear cached Homepage Fail. Please try again!');
            static::redirect($this->getUrl('*/*/post'));
        }
    }

    public function actionPost()
    {
        $this->checkPermission('page/home_page/update');

        $homepage_v3_data = OSC::helper('core/setting')->get('frontend/homepage_v3', []);

        $catalog_collections = OSC::model('catalog/collection')->getCollection()
            ->addField('collection_id', 'title')
            ->sort('title')
            ->load();

        if ($this->_request->get('submit_form')) {
            try {
                $homepage_v3 = $this->_request->get('homepage_v3');

                if (!is_array($homepage_v3) || count($homepage_v3) < 1) {
                    throw new Exception('Data config incorrect');
                }

                $homepage_v3_post_form = [];
                foreach ($homepage_v3 as $section => $homepage_v3_item) {
                    switch ($section) {
                        case 'community':
                        case 'gift_finder':
                        case 'popular_categories':
                        case 'popular_collection':
                            $homepage_v3_post_form[$section] = $homepage_v3_item;
                            foreach ($homepage_v3_item['items'] as $position => $item_value) {
                                foreach ($item_value as $input => $value) {
                                    if ($input == 'images') {
                                        $homepage_v3_post_form[$section]['items'][$position]['images']['pc'] = $this->_processImage($value, $section, $position, 'pc', true);
                                        $homepage_v3_post_form[$section]['items'][$position]['images']['mobile'] = $this->_processImage($value, $section, $position, 'mobile', true);
                                    } else {
                                        $homepage_v3_post_form[$section]['items'][$position][$input] = trim($value);
                                    }
                                }
                            }

                            break;
                        case 'sales_campaign':
                            foreach ($homepage_v3_item as $item_type => $item_value) {
                                if ($item_type == 'images') {
                                    $homepage_v3_post_form[$section]['images']['pc'] = $this->_processImage($item_value, $section, 0, 'pc', 'images');
                                    $homepage_v3_post_form[$section]['images']['mobile'] = $this->_processImage($item_value, $section, 0, 'mobile', 'images');
                                } else {
                                    $homepage_v3_post_form[$section][$item_type]= trim($item_value);
                                }
                            }

                            break;
                        case 'customer_review':
                        case 'preview_3d':
                            $homepage_v3_post_form[$section] = trim($homepage_v3_item);
                            break;
                        case 'collection':
                        case 'ab_test':
                            $homepage_v3_post_form[$section] = $homepage_v3_item;
                            break;
                        default:
                            foreach ($homepage_v3_item as $position => $item_value) {
                                foreach ($item_value as $input => $value) {
                                    if ($input == 'images') {
                                        $homepage_v3_post_form[$section][$position]['images']['pc'] = $this->_processImage($value, $section, $position);
                                        $homepage_v3_post_form[$section][$position]['images']['mobile'] = $this->_processImage($value, $section, $position, 'mobile');
                                    } else {
                                        $homepage_v3_post_form[$section][$position][$input] = trim($value);
                                    }
                                }
                            }
                            break;
                    }
                }

                OSC::helper('core/setting')->set('frontend/homepage_v3', $homepage_v3_post_form);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
                static::redirect($this->getUrl(null));
            }
            $this->addMessage('Configure success!');
            static::redirect($this->getUrl(null));
        }

        $output_html = $this->getTemplate()->build('page/homepage_v3', [
            'homepage_v3_data' => $homepage_v3_data,
            'catalog_collections' => $catalog_collections
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
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path)->resize(3000);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $file_name = 'homepage_v3.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

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

    protected function _processImage($image, $section, $position, $type = 'pc', $group_items = false)
    {
        $file_name = $image[$type] ?? '';
        $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($file_name);
        if (!$file_name || !OSC::core('aws_s3')->doesObjectExist($tmp_image_path_s3)) {
            $homepage_v3_data = OSC::helper('core/setting')->get('frontend/homepage_v3', []);
            if (!$group_items) {
                return $homepage_v3_data[$section][$position]['images'][$type] ?? '';
            } else {
                if ($group_items === true) {
                    return $homepage_v3_data[$section]['items'][$position]['images'][$type] ?? '';
                } else {
                    return $homepage_v3_data[$section][$group_items][$type] ?? ''; // $group_items = string
                }
            }

        }

        try {
            $file_name = 'homepage_v3/' . str_replace('homepage_v3.', '', $file_name);
            $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($file_name);

            OSC::core('aws_s3')->copy($tmp_image_path_s3, $storage_filename_s3);
            return $file_name;

        } catch (Exception $ex) {

        }
        return '';
    }
}