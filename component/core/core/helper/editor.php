<?php

class Helper_Core_Editor {

    /**
     * @param $url
     * @return mixed
     * @throws Exception
     */
    public static function imageUrlProcessor($url) {
        if (OSC::core('aws_s3')->tmpIsUrl($url)) {
            $tmp_filename = OSC::core('aws_s3')->tmpGetFileNameFromUrl($url);
            $tmp_filename_s3 = OSC::core('aws_s3')->getTmpFilePath($tmp_filename);
            $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($tmp_filename);

            if (!OSC::core('aws_s3')->doesObjectExist($tmp_filename_s3)) {
                throw new Exception(OSC::core('language')->get('core.err_tmp_expired'));
            }

            try {
                $options = [
                    'overwrite' => true,
                    'permission_access_file' => 'public-read'
                ];
                OSC::core('aws_s3')->copy($tmp_filename_s3, $storage_filename_s3, $options);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            return OSC::core('aws_s3')->getStorageUrl($tmp_filename);
        } else {
            return $url;
        }
    }

}
