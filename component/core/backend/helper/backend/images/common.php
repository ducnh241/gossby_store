<?php

class Helper_Backend_Backend_Images_Common extends OSC_Object {
    public function __construct(){
        parent::__construct();
    }

    /**
     * @param $dataMetaImage (string)
     * @param $modelCheck (model data is check string)
     * @param $savePath (string)
     * @param $nameReplace (string)
     */

    public function saveMetaImage($dataMetaImage, $modelCheck ,$savePath, $nameReplace) {
        if ($dataMetaImage != $modelCheck) {
            if (!$dataMetaImage) {
                $dataMetaImage = '';
                $meta_image_to_rmv = $modelCheck;

                return [
                    'image_to_rm' => $meta_image_to_rmv,
                    'data_meta_image' => $dataMetaImage
                ];
            } else {
                $dataMetaImageS3 = OSC::core('aws_s3')->getTmpFilePath($dataMetaImage);
                if (!OSC::core('aws_s3')->doesObjectExist($dataMetaImageS3)) {
                    $dataMetaImage = $modelCheck;

                    return [
                        'image_to_rm' => null,
                        'data_meta_image' => $dataMetaImage
                    ];
                } else {
                    $filename = $savePath . str_replace($nameReplace. '.', '', $dataMetaImage);
                    $filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);

                    try {
                        OSC::core('aws_s3')->copy($dataMetaImageS3, $filename_s3);
                        $dataMetaImage = $filename;

                        return [
                            'image_to_rm' => null,
                            'data_meta_image' => $dataMetaImage
                        ];

                    } catch (Exception $ex) {
                        $dataMetaImage = $modelCheck;
                    }
                }
            }
        }
    }
}