<?php

class Controller_PersonalizedDesign_Frontend extends Abstract_Frontend_Controller {

    public function actionGetFrmConfig() {
        /* @var $model Model_PersonalizedDesign_Design */

        $id = intval($this->_request->get('id'));

        if ($id < 1) {
            $this->error('Design ID is incorrect');
        }

        try {
            $model = OSC::model('personalizedDesign/design')->load($id);
            $form_data = $model->extractPersonalizedFormData();
            $this->_ajaxResponse([
                'id' => $id, 
                'document_type' => $model->data['design_data']['document'], 
                'components' => $form_data['components'],
                'image_data' => $form_data['image_data']
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getCode() == 404 ? 'Design is not exist' : $ex->getMessage());
        }
    }

    public function actionGetMultiFrmConfig() {
        die(); //Remove legacy code
    }

    public function actionUploadImage() {
        try {
            $uploader = new OSC_Uploader();

            $original_file_name = $uploader->getName();

            $file_size = $uploader->getSize();

            $tmp_file_path = OSC::getTmpDir() . '/' . OSC::makeUniqid() . '.' . $uploader->getExtension();

            $uploader->save($tmp_file_path, true);

            $extension = OSC_File::verifyImage($tmp_file_path);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        try {
            $file_name = 'personalizedDesign/customer_uploaded/' . date('Ymd') . '/' . OSC::makeUniqid() . '.' . $extension;

            OSC::core('aws_s3')->tmpSaveFile($tmp_file_path, $file_name);
            $tmp_file_path = OSC_Storage::preDirForSaveFile($file_name);
            $tmp_thumb_file_path = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.thumb.\\2', $tmp_file_path);

            $image = new Imagick($tmp_file_path);

            $width = $image->getImageWidth();
            $height = $image->getImageHeight();

            if ($image->getImageHeight() > 400) {
                $image->scaleImage(0, 400);
            }

            $image->writeImage($tmp_thumb_file_path);

            $image->clear();
            $image->destroy();

            $file_name_s3 = OSC::core('aws_s3')->getStoragePath($file_name);
            $options = [
                'overwrite' => true,
                'permission_access_file' => 'public-read'
            ];
            $file_url = OSC::core('aws_s3')->upload($tmp_file_path, $file_name_s3, $options);

            if (!$file_url) {
                throw new Exception('Unable to write file to thumb storage');
            }

            try {
                OSC::model('personalizedDesign/sync')->setData([
                    'ukey' => 'image/' . md5($file_name),
                    'sync_type' => 'image',
                    'sync_data' => $file_name
                ])->save();
            } catch (Exception $ex) {
                if (strpos($ex->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') === false) {
                    throw new Exception($ex->getMessage());
                }
            }

            $data = [
                'file' => $file_name,
                'name' => $original_file_name,
                'url' => $file_url,
                'size' => $file_size,
                'width' => $width,
                'height' => $height
            ];

            $data['token'] = OSC::helper('personalizedDesign/common')->imageUploaderGetDataToken($data);

            $this->_ajaxResponse($data);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionEdit() {
        die(); //Remove legacy code
    }

    public function actionEditSemiTest() {
        die(); //Remove legacy code
    }
}