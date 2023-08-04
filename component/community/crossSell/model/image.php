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
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Model_CrossSell_Image extends OSC_Database_Model {

    /**
     *
     * @var string
     */
    protected $_table_name = 'cross_sell_image';

    /**
     *
     * @var string
     */
    protected $_pk_field = 'id';
    protected $_ukey_field = 'ukey';

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['ukey'])) {
            $data['ukey'] = trim($data['ukey']);

            if ($data['ukey']  == '') {
                $errors[] = 'ukey is error';
            }
        }

        if (isset($data['design_id'])) {
            $data['design_id'] = intval($data['design_id']);

            if ($data['design_id'] < 1) {
                $errors[] = 'design_id is error';
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
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'ukey' => 'ukey is empty',
                    'design_id' => 'design_id is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
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
            if ($this->data['is_default_mockup'] != 1) {
                OSC::core('storage')->delete($this->data['filename']);
            }
        } catch (Exception $ex) {

        }
    }

    protected function _afterSave() {
        parent::_afterSave();

        try {
            if (isset($this->data['filename'])) {
                $printer_image_url_s3 = OSC::core('aws_s3')->getStoragePath($this->data['filename']);

                $options = [
                    'overwrite' => true,
                    'permission_access_file' => 'public-read'
                ];

                OSC::core('aws_s3')->upload(OSC_Storage::getStoragePath($this->data['filename']), $printer_image_url_s3, $options);

                $this->setData([
                    'filename_s3' => OSC::core('aws_s3')->getStorageUrl($this->data['filename'])
                ])->save();
            }
        } catch (Exception $ex) {
        }
    }
}
