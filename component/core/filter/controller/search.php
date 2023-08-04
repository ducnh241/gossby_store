<?php

class Controller_Filter_Search extends Abstract_Catalog_Controller_Backend
{

    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('filter/search|catalog/product');

        $this->getTemplate()
            ->setCurrentMenuItemKey('filter/setting_search')
            ->setPageTitle('Setting Search');
    }

    public function actionIndex()
    {
        if ($this->_request->get('save') == 1) {
            $locked_key = OSC::makeUniqid();

            try {
                $DB = OSC::core('database')->getWriteAdapter();
                $DB->begin();

                $new_setting_values = $this->_validatePostForm();

                OSC_Database_Model::lockPreLoadedModel($locked_key);

                foreach ($new_setting_values as $key => $new_setting_value) {
                    OSC::helper('core/setting')->set($key, $new_setting_value, true);
                }

                $DB->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);
                OSC::helper('core/setting')->removeCache();

                static::redirect($this->rebuildUrl(['save' => 0]));
            } catch (Exception $ex) {
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                $this->addErrorMessage($ex->getMessage());
            }
        }

        $collections = OSC::model('catalog/collection')
            ->getCollection()
            ->addField('collection_id', 'title', 'image')
            ->sort('title', OSC_Database::ORDER_ASC)
            ->load()
            ->toArray();

        $data_config_search = OSC::helper('filter/search')->getSetting();
        $popular_collections = $data_config_search['search/popular_collections'] ?? [];
        $trending_keywords_manual = $data_config_search['search/trending_keywords_manual'] ?? [];
        $suggestions = $data_config_search['search/trending_keywords'] ?? [];

        if (!empty($trending_keywords_manual['expired'])) {
            $trending_keywords_manual['expired'] = date('d/m/Y', $trending_keywords_manual['expired']);
        }

        $output_html = $this->getTemplate()->build('filter/search/postForm', [
            'form_title' => '',
            'collections' => $collections,
            'popular_collections' => $popular_collections,
            'trending_keywords_manual' => $trending_keywords_manual,
            'suggestions' => $suggestions,
        ]);

        $this->output($output_html);
    }

    /**
     * @throws Exception
     */
    protected function _validatePostForm(): array
    {
        $data = [];
        $popular_collections = $this->_request->get('popular_collections');
        $trending_keywords_manual = $this->_request->get('trending_keywords_manual', '[]');
        $trending_keywords_manual = OSC::decode($trending_keywords_manual);

        foreach ($popular_collections as $key => $collection) {
            if ($collection['collection_id']) {
                $popular_collections[$key]['collection_id'] = intval($collection['collection_id']);
            } else {
                throw new Exception('collection_id:: ' . $collection['collection_id'] . ' is invalid!');
            }

            if (!$collection['title']) {
                unset($popular_collections[$key]['title']);
            }

            if ($popular_collections[$key]['image']) {
                $popular_collections[$key]['image'] = $this->_processImage($popular_collections[$key]['image']);
            } else {
                unset($popular_collections[$key]['image']);
            }
        }

        if (empty($trending_keywords_manual['list'])) {
            $trending_keywords_manual = null;
        } else if (count($trending_keywords_manual['list']) < 3) {
            throw new Exception('You must set 3 trending keywords at a time');
        } else if (!empty($trending_keywords_manual['expired'])) {
            preg_match("/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})\s*$/", $trending_keywords_manual['expired'], $expired_matches);

            $trending_keywords_manual['expired'] = mktime(23, 59, 59, $expired_matches[2], $expired_matches[1], $expired_matches[3]);
        } else {
            throw new Exception('Expire date field for trending keywords must be set');
        }

        $data['search/popular_collections'] = $popular_collections;
        $data['search/trending_keywords_manual'] = $trending_keywords_manual;

        return $data;
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

            $file_name = 'search_popular_collection.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

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

    protected function _processImage($image)
    {
        $file_name = $image ?? '';
        $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($file_name);

        if (!$file_name || !OSC::core('aws_s3')->doesObjectExist($tmp_image_path_s3)) {
            return $file_name;
        }

        try {
            $file_name = 'search_popular_collection/' . str_replace('search_popular_collection.', '', $file_name);
            $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($file_name);

            OSC::core('aws_s3')->copy($tmp_image_path_s3, $storage_filename_s3);
            return $file_name;

        } catch (Exception $ex) {

        }
        return '';
    }
}
