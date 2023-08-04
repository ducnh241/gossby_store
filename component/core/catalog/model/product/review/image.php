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
class Model_Catalog_Product_Review_Image extends Abstract_Core_Model {

    protected $_table_name = 'catalog_product_review_image';
    protected $_pk_field = 'image_id';

    /**
     *
     * @var Model_Catalog_Product_Review
     */
    protected $_review_model = null;

    /**
     * 
     * @param mixed $product
     * @return $this
     */
    public function setReview($review) {
        $this->_review_model = $review;
        return $this;
    }

    /**
     * 
     * @return Model_Catalog_Product
     * @throws Exception
     */
    public function getReview() {
        if ($this->_review_model === null || ($this->_review_model->getId() > 0 && $this->_review_model->getId() != $this->data['review_id'])) {
            try {
                $this->_review_model = OSC::model('catalog/product_review');

                $review_id = intval($this->data['review_id']);

                if ($review_id > 0) {
                    $this->_review_model->load($review_id);
                }
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }
        }

        return $this->_review_model;
    }

    public function getUrl() {
        return OSC::core('aws_s3')->getStorageUrl($this->data['filename']);
    }

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

        if (isset($data['review_id'])) {
            $data['review_id'] = intval($data['review_id']);

            if ($data['review_id'] < 1) {
                $errors[] = 'Review ID is empty';
            } else {
                try {
                    OSC::model('catalog/product_review')->load($data['review_id']);
                } catch (Exception $ex) {
                    $errors[] = 'Cannot verify review id';
                }
            }
        }

        foreach (array('added_timestamp', 'modified_timestamp', 'position') as $key) {
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
                    'review_id' => 'Review ID is empty'
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
                unset($data['review_id']);
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
            OSC::core('aws_s3')->deleteStorageFile($this->data['filename']);
        } catch (Exception $ex) {
            
        }
    }

    public function __toArray() {
        return array(
            'id' => $this->getId(),
            'review_id' => $this->data['review_id'],
            'url' => $this->getUrl(),
            'filename' => $this->data['filename'],
            'alt' => $this->data['alt'],
            'width' => $this->data['width'],
            'height' => $this->data['height'],
            'added_date' => date('c', $this->data['added_timestamp']),
            'modified_date' => date('c', $this->data['modified_timestamp'])
        );
    }

}