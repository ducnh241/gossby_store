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
class Model_Catalog_Campaign_ImageLib_Item extends Abstract_Core_Model {

    protected $_table_name = 'catalog_2d_image_library';
    protected $_pk_field = 'item_id';

    public function getFileUrl() {
        return OSC::core('aws_s3')->getStorageUrl($this->data['filename']);
    }

    public function getFilePath() {
        return OSC_Storage::getStoragePath($this->data['filename']);
    }

    public function getFileThumbName() {
        return preg_replace('/^(.+)\.([^\.]+)$/', '\\1.thumb.\\2', $this->data['filename']);
    }

    public function getFileThumbUrl() {
        return OSC::core('aws_s3')->getStorageUrl($this->getFileThumbName());
    }

    public function getFileThumbPath() {
        return OSC_Storage::getStoragePath($this->getFileThumbName());
    }

    protected function _beforeSave() {
        if ($this->getActionFlag() != static::INSERT_FLAG) {
            $this->_error(['The model [' . $this->getModelKey() . '] is not support to modify saved data']);
            return false;
        }

        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

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
                $data['size'] = filesize(OSC_Storage::getStoragePath($data['filename']));
            }
        }

        if (isset($data['member_id'])) {
            $data['member_id'] = intval($data['member_id']);

            if ($data['member_id'] < 1) {
                $errors[] = 'Member ID is empty';
            }
        }

        if (isset($data['item_type'])) {
            $data['item_type'] = trim($data['item_type']);

            if (!in_array($data['item_type'], ['file', 'directory'], true)) {
                $errors[] = 'Item type is incorrect';
            }
        }

        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);

            if (!$data['name']) {
                $errors[] = 'Name is empty';
            }
        }

        foreach (array('added_timestamp', 'modified_timestamp') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (count($errors) < 1) {
            $require_fields = [
                'item_type' => 'Item type is empty',
                'name' => 'Name is empty',
                'member_id' => 'Member ID is empty'
            ];

            if (isset($data['item_type'])) {
                if ($data['item_type'] == 'file') {
                    $require_fields['filename'] = 'File name is empty';
                    $require_fields['extension'] = 'File extension is empty';
                    $require_fields['width'] = 'File width is empty';
                    $require_fields['height'] = 'File height is empty';
                    $require_fields['size'] = 'File size is empty';
                } else {
                    $data['filename'] = null;
                    $data['extension'] = null;
                    $data['width'] = null;
                    $data['height'] = null;
                    $data['size'] = null;
                }
            }

            foreach ($require_fields as $field_name => $err_message) {
                if (!isset($data[$field_name])) {
                    $errors[] = $err_message;
                }
            }

            $default_fields = [
                'added_timestamp' => time(),
                'modified_timestamp' => time()
            ];

            foreach ($default_fields as $field_name => $default_value) {
                if (!isset($data[$field_name])) {
                    $data[$field_name] = $default_value;
                }
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
            'url' => $this->getUrl(),
            'alt' => $this->data['alt'],
            'width' => $this->data['width'],
            'height' => $this->data['height'],
            'added_date' => date('c', $this->data['added_timestamp']),
            'modified_date' => date('c', $this->data['modified_timestamp'])
        );
    }

}
