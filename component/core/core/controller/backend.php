<?php

class Controller_Core_Backend extends Abstract_Backend_Controller {

    public function actionUploadMedia() {
        try {
            $is_video = $this->_request->get('is_video', false);
            $uploader = new OSC_Uploader();

            $file_extension = $uploader->getExtension();

            if ($is_video) {
                $allow_types = ['mp4'];
            } else {
                $allow_types = ['png','jpg','jpeg','gif'];
            }

            if (!in_array($file_extension, $allow_types, true)) {
                throw new Exception(strtoupper($file_extension) . ' is not allowed to upload');
            }

            $tmp_file_path = 'upload_mockup/' . $this->getAccount()->getId().'.'. OSC::makeUniqid() . '.' . time() . '.' . $file_extension;
            $tmp_file_path_s3 = OSC::core('aws_s3')->getTmpFilePath($tmp_file_path);

            $tmp_file_path_saved = OSC_Storage::preDirForSaveFile($tmp_file_path);

            $uploader->save($tmp_file_path_saved, true);
            $options = [
                'overwrite' => true,
                'permission_access_file' => 'public-read'
            ];

            if ($is_video) {
                OSC_File::verifyVideo($tmp_file_path_saved);
            } else {
                OSC_File::verifyImage($tmp_file_path_saved);
            }

            $file_url = OSC::core('aws_s3')->upload($tmp_file_path_saved, $tmp_file_path_s3, $options);

            $this->_ajaxResponse($file_url);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }
}
