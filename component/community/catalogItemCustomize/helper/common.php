<?php

class Helper_CatalogItemCustomize_Common {
    public function cropDesign($tmp_file_path, $design_id , $type, $extension)
    {
        // Content type
        try {
            $img_processor = new OSC_Image();

            $img_processor->setImage($tmp_file_path);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $img_processor->setImage($tmp_file_path)->resizeAndFill($width,$height, 'ffffff')->crop($type == 'front' ? 0 : $width/2 + 600,0,$type == 'front' ? $width/2 - 600 : $width,$height)->resize(600);

            $img_processor->save();

            $design_image_url = 'catalogCustomize/design/' . $design_id . '/' .  OSC::helper('user/authentication')->getMember()->getId() . '.' . OSC::makeUniqid() . '.' .time(). '.'.$extension;
            $design_image_url_s3 = OSC::core('aws_s3')->getStoragePath($design_image_url);

            $options = [
                'overwrite' => true,
                'permission_access_file' => 'public-read'
            ];
            OSC::core('aws_s3')->upload($tmp_file_path, $design_image_url_s3, $options);


            return $design_image_url;

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
            
        }
    }
}