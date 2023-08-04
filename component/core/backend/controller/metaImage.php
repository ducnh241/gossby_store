<?php

class Controller_Backend_MetaImage extends Abstract_Backend_Controller {

    public function actionUploadMetaImage() {
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
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path)->resize(1920);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $file_name =  'meta-images.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

            $tmp_url = OSC::core('aws_s3')->tmpSaveFile($img_processor, $file_name);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse([
            'file' => $file_name,
            'url' => $tmp_url,
            'width' => $width,
            'height' => $height
        ]);
    }
}