<?php

class Helper_Catalog_Frontend extends OSC_Object
{
    public function applyPriceRules(&$price, &$compare_at_price, $options = null) {
        if (!is_array($options)) {
            $options = [];
        }

        if (!(OSC::controller() instanceof Abstract_Frontend_Controller) && !isset($options['force']) && !$options['force']) {
            return;
        }

        $price = max($price, 0);

        if (OSC::helper('core/setting')->get('catalog/product/disable_compare_at_price')) {
            $compare_at_price = 0;
        }
    }

    public function saveCollectionBannerOnS3($data_image, $model_image) {
        if ($data_image != $model_image) {
            if (!$data_image) {
                $data_image = '';
            } else {
                $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($data_image);
                if (!OSC::core('aws_s3')->doesObjectExist($tmp_image_path_s3)) {
                    $data_image = $model_image;
                } else {
                    $filename = 'collection/' . str_replace('post.', '', $data_image);
                    $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);
                    try {
                        OSC::core('aws_s3')->copy($tmp_image_path_s3, $storage_filename_s3);
                        $data_image = $filename;
                    } catch (Exception $ex) {
                        $data_image = $model_image;
                    }
                }
            }
        }

        return $data_image;
    }
}