<?php

/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Model_Catalog_Product_Image extends Abstract_Core_Model {

    protected $_table_name = 'catalog_product_image';
    protected $_pk_field = 'image_id';
    protected $_ukey_field = 'ukey';

    const ENABLE_AMAZON_MOCKUP_UPLOAD_STATUS = 1;
    const DISABLE_AMAZON_MOCKUP_UPLOAD_STATUS = 0;

    /**
     *
     * @var Model_Catalog_Product
     */
    protected $_product_model = null;

    /**
     * 
     * @param mixed $product
     * @return $this
     */
    public function setProduct($product) {
        $this->_product_model = $product;
        return $this;
    }

    /**
     * 
     * @return Model_Catalog_Product
     * @throws Exception
     */
    public function getProduct() {
        if ($this->_product_model === null || ($this->_product_model->getId() > 0 && $this->_product_model->getId() != $this->data['product_id'])) {
            try {
                $this->_product_model = OSC::model('catalog/product');

                $product_id = intval($this->data['product_id']);

                if ($product_id > 0) {
                    $this->_product_model->load($product_id);
                }
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }
        }

        return $this->_product_model;
    }

    public function getS3Thumbnail() {
        return OSC::core('aws_s3')->getStorageDirUrl() . '/' . $this->data['thumbnail'];
    }

    protected $_s3_url = null;

    public function getS3ImageUrl() {
        if ($this->_s3_url) {
            return $this->_s3_url;
        }

        $this->_s3_url = OSC::core('aws_s3')->getStorageDirUrl() . '/' . $this->data['filename'];

        return $this->_s3_url;
    }

    public function getUrl() {
        return $this->getS3ImageUrl();
    }

    /* Old function, remove when feature s3 working normally */
//    public function getUrl($skip_cdn = false) {
//        $storage_url = OSC::core('aws_s3')->getStorageUrl($this->data['filename']);
//
//        return $skip_cdn ? $storage_url : OSC::wrapCDN($storage_url);
//    }

    public function getFilePath() {
        return OSC_Storage::getStoragePath($this->data['filename']);
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['filename'])) {
            $data['filename'] = trim($data['filename']);

            if (!$data['filename']) {
                $errors[] = 'Image file path is empty';
            } else if (!OSC::core('aws_s3')->doesStorageObjectExist($data['filename'])) {
                $errors[] = 'Image file is not exists';
            } else if ($data['thumbnail'] && !OSC::core('aws_s3')->doesStorageObjectExist($data['thumbnail'])) {
                $errors[] = 'Video thumbnail file is not exists';
            } else if ($data['is_static_mockup'] === 3) {
                $data['width'] = 0;
                $data['height'] = 0;
                $data['extension'] = preg_replace('/^.+\.([^\.]+)$/i', '\\1', $data['filename']);
            } else {
                list($width, $height) = getimagesize(OSC::core('aws_s3')->getStorageUrl($data['filename']));

                $data['extension'] = preg_replace('/^.+\.([^\.]+)$/i', '\\1', $data['filename']);
                $data['width'] = $width;
                $data['height'] = $height;
            }
        }

        if (isset($data['alt'])) {
            $data['alt'] = trim($data['alt']);
        }

        if (isset($data['product_id'])) {
            $data['product_id'] = intval($data['product_id']);

            if ($data['product_id'] < 1) {
                $errors[] = 'Product ID is empty';
            } else {
                try {
                    OSC::model('catalog/product')->load($data['product_id']);
                } catch (Exception $ex) {
                    $errors[] = 'Cannot verify product id';
                }
            }
        }

        foreach (['added_timestamp', 'modified_timestamp', 'is_static_mockup'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'filename' => 'Image file path is empty',
                    'product_id' => 'Product ID is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'alt' => '',
                    'added_timestamp' => time(),
                    'modified_timestamp' => time(),
                    'position' => 0
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            } else {
                unset($data['product_id']);
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _afterDelete() {
        parent::_afterDelete();

        try {
            if ($this->data['is_static_mockup'] != 1) {
                OSC::core('aws_s3')->deleteStorageFile($this->data['filename']);
            }
        } catch (Exception $ex) {
            
        }
    }

    public function __toArray() {
        return [
            'id' => $this->getId(),
            'ukey' => $this->data['ukey'],
            'position' => $this->data['position'],
            'flag_main' => $this->data['flag_main'],
            'url' => $this->getUrl(),
            'thumbnail' => $this->getS3Thumbnail(),
            'alt' => $this->data['alt'],
            'width' => $this->data['width'],
            'height' => $this->data['height'],
            'duration' => $this->data['duration'],
            'filename' => $this->data['filename'],
            'is_static_mockup' => $this->data['is_static_mockup'],
            'is_upload_mockup_amazon' => $this->data['is_upload_mockup_amazon'],
            'is_show_product_type_variant_image' => $this->data['is_show_product_type_variant_image'],
            'added_date' => date('c', $this->data['added_timestamp']),
            'modified_date' => date('c', $this->data['modified_timestamp'])
        ];
    }

}
