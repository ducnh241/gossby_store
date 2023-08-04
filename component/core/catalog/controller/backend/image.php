<?php

class Controller_Catalog_Backend_Image extends Abstract_Catalog_Controller_Backend {
    public function actionPost() {
        $this->checkPermission('catalog/super|catalog/product');

        $output_html = $this->getTemplate()->build('catalog/product/uploadImage', []);

        $this->output($output_html);
    }

    public function actionUpload() {
        $this->checkPermission('catalog/super|catalog/product');

        try {
            $uploader = new OSC_Uploader();
            $original_file_name = $uploader->getName();
            $original_file_name = preg_replace('#[^0-9a-z-]+#is', '-', trim($original_file_name));
            $original_file_name = preg_replace('#[-]{2,}#is', '-', trim($original_file_name, '-'));

            $tmp_file_path = OSC::getTmpDir() . '/' . $this->getAccount()->getId() . '.' . $original_file_name . '.' . $uploader->getExtension();

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
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $file_name = OSC::makeUniqid() . '.' . rtrim($original_file_name, '-' . $extension) . '.' . $extension;

            $tmp_url = OSC::core('aws_s3')->tmpSaveFile($img_processor, $file_name);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $tmp_url = $tmp_url ?? OSC_Storage::tmpGetFileUrl($file_name);

        $this->_ajaxResponse([
            'file' => $file_name,
            'url' => $tmp_url,
            'width' => $width,
            'height' => $height,
            'extension' => $extension
        ]);
    }
}