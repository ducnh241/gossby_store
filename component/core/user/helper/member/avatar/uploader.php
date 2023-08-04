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

/**
 * OSC_User
 * 
 * @package Helper_User_Member_Avatar_Uploader
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Helper_User_Member_Avatar_Uploader extends Abstract_Core_Controller {

    /**
     *
     * @var Model_User_Member
     */
    protected $_model = null;

    const AVA_MIN_DIM = 60;
    const AVA_MAX_DIM = 600;

    public function __construct() {
        parent::__construct();
    }

    public function setModel($model) {
        $this->_model = $model;
        return $this;
    }

    public function error($error_message, $error_code = 0) {
        $this->_ajaxError($error_message, $error_code);
    }

    public function processUpload() {
        $this->removeTemporary();

        try {
            $uploader = OSC::core('uploader');
        } catch (OSC_Exception_Upload $e) {
            $this->error($e->getMessage());
        }

        $ext = $uploader->getExtension();

        if (!in_array($ext, array('png', 'gif', 'jpeg', 'jpg'))) {
            $this->error('Bạn không được phép upload dạng file này');
        }

        $timestamp = mktime();

        $temp_id = 'ava.' . $this->_model->getId() . '.' . $ext;

        try {
            OSC::core('filesystem_temporary')->saveFile($uploader, $temp_id);
        } catch (OSC_Exception $e) {
            $this->error('TMP: ' . $e->getMessage());
        }

        $temp_path = OSC::core('filesystem_temporary')->getPath($temp_id);

        list($w, $h) = getimagesize($temp_path);

        if ($w < self::AVA_MIN_DIM || $h < self::AVA_MIN_DIM) {
            $this->error('Ảnh cần có chiều cao và chiều rộng lớn hơn ' . self::AVA_MIN_DIM . 'px');
        }

        if ($w > self::AVA_MAX_DIM && $h > self::AVA_MAX_DIM) {
            $img_processor = OSC::core('image', 'avatar')->setImage($temp_path)->setJpgQuality(100);

            if ($w > $h) {
                $img_processor->resize(0, self::AVA_MAX_DIM);
            } else {
                $img_processor->resize(self::AVA_MAX_DIM);
            }

            $w = $img_processor->getWidth();
            $h = $img_processor->getHeight();

            $img_processor->save()->destroy();
        }

        $img_processor = OSC::core('image', 'avatar')->setImage($temp_path)->setJpgQuality(100);

        $storage = OSC::core('storage');

        if ($w / $h != 1) {
            $storage_tmp_path = $storage->getTempDir() . '/ava.' . $this->_model->getId() . '_' . time() . '.' . $ext;

            $old_ava_temp_file = OSC::sessionGet('_ava_temp');

            if (isset($old_ava_temp_file)) {
                $storage->delete($old_ava_temp_file['path']);
                OSC::sessionSet('_ava_temp', null);
            }

            try {
                $storage->sendFile($temp_path, $storage_tmp_path, true);
            } catch (OSC_Exception $e) {
                $this->error($e->getMessage());
            }

            OSC::core('filesystem_temporary')->delete($temp_id);

            $ratio_dim = $w > $h ? $h : $w;

            OSC::sessionSet('_ava_temp', array('path' => $storage_tmp_path, 'ratio_dim' => $ratio_dim, 'w' => $w, 'h' => $h, 'ext' => $ext));

            $this->_ajaxResponse(array('cropper' => 1, 'url' => $storage->getUrl($storage_tmp_path), 'ratio_dim' => $ratio_dim, 'min_dim' => self::AVA_MIN_DIM, 'max_dim' => self::AVA_MAX_DIM));
        }

        $this->_save($ext, $temp_id);
    }

    public function removeTemporary() {
        $ava_tmp = OSC::sessionGet('_ava_temp');

        if (is_array($ava_tmp)) {
            OSC::core('aws_s3')->deleteStorageFile($ava_tmp['path']);
            OSC::sessionSet('_ava_temp', null);
        }
    }

    public function crop() {
        $ava_tmp = OSC::sessionGet('_ava_temp');

        $storage = OSC::core('storage');

        if (!is_array($ava_tmp) || !$storage->exists($ava_tmp['path'])) {
            OSC::sessionSet('_ava_temp', null);
            $this->error('File temporary không tồn tại', 1);
        }

        $x1 = intval($this->_request->get('x1'));
        $y1 = intval($this->_request->get('y1'));
        $x2 = intval($this->_request->get('x2'));
        $y2 = intval($this->_request->get('y2'));

        if ($x1 < 0 || $y1 < 0 || $x2 <= $x1 || $y2 <= $y1) {
            $this->error('Tỷ lệ cắt không chính xác', 2);
        }

        $new_w = $x2 - $x1;
        $new_h = $y2 - $y1;

        if ($new_w < $new_h) {
            $y2 = $y1 + $new_w;

            if ($y2 > $ava_tmp['h']) {
                if ($ava_tmp['h'] - $new_w >= 0) {
                    $y2 = $ava_tmp['h'];
                    $y1 = $ava_tmp['h'] - $new_w;
                    $new_h = $new_w;
                }
            } else {
                $new_h = $new_w;
            }
        }

        if ($new_w != $new_h) {
            $x2 = $x1 + $new_h;

            if ($x2 > $ava_tmp['w']) {
                if ($ava_tmp['w'] - $new_h < 0) {
                    $this->error('Tỷ lệ cắt không chính xác', 2);
                }

                $x2 = $ava_tmp['w'];
                $x1 = $ava_tmp['w'] - $new_h;
                $new_w = $new_h;
            } else {
                $new_w = $new_h;
            }
        }

        if ($new_w < self::AVA_MIN_DIM || $new_w > $ava_tmp['w'] || $new_w > $ava_tmp['h']) {
            $this->error('Tỷ lệ cắt không chính xác', 2);
        }

        $temp_id = 'ava.' . $this->_model->getId() . '.' . $ava_tmp['ext'];

        $temporary = OSC::core('filesystem_temporary');

        try {
            $temporary->saveFile($storage->getUrl($ava_tmp['path']), $temp_id, OSC_Filesystem_Temporary::SAVE_FROM_URL);
        } catch (OSC_Exception $e) {
            $this->error($e->getMessage(), 2);
        }

        $storage->delete($ava_tmp['path']);

        OSC::sessionSet('_ava_temp', null);

        $temp_path = $temporary->getPath($temp_id);

        OSC::core('image', 'avatar')->setImage($temp_path)->setJpgQuality(100)->crop($x1, $y1, $x2, $y2)->save()->destroy();

        $this->_save($ava_tmp['ext'], $temp_id);
    }

    public function remove() {
        $this->_save();
    }

    /**
     *
     * @param string $ext
     * @param string $temp_id 
     */
    protected function _save($ext = false, $temp_id = false) {
        $storage = OSC::core('storage');

        $sizes = array(Model_User_Member::AVA_EXTRA_SIZE, Model_User_Member::AVA_LARGE_SIZE, Model_User_Member::AVA_SMALL_SIZE, Model_User_Member::AVA_TINY_SIZE);

        if ($ext !== false) {
            if ($this->_model->getData('avatar_extension', true) != '') {
                foreach ($sizes as $size) {
                    try {
                        $storage->delete($this->_model->getAvatarUrl($size, true));
                    } catch (OSC_Exception $e) {
                        
                    }
                }
            }

            $this->_model->setData('avatar_extension', $ext);

            $temp_path = OSC::core('filesystem_temporary')->getPath($temp_id);

            $buff_path = preg_replace('/^(.+)\.([^\.]+)$/', '\\1.buff.\\2', $temp_path);

            if (copy($temp_path, $buff_path)) {
                foreach ($sizes as $size) {
                    OSC::core('image', null)->setImage($buff_path)->setJpgQuality(100)->trimAndResize($size, $size)->save()->destroy();

                    try {
                        $storage->sendFile($buff_path, $this->_model->getAvatarUrl($size, true));
                    } catch (OSC_Exception $e) {
                        
                    }
                }

                unlink($buff_path);
            }

            OSC::core('filesystem_temporary')->delete($temp_id);
        } else {
            if ($this->_model->data['avatar_extension']) {
                foreach ($sizes as $size) {
                    try {
                        $storage->delete($this->_model->getAvatarUrl($size, true));
                    } catch (OSC_Exception $e) {
                        
                    }
                }
            }

            $this->_model->setData('avatar_extension', '');
        }

        $this->_model->save();

        $this->_ajaxResponse($this->_model->getAvatarUrl(Model_User_Member::AVA_EXTRA_SIZE));
    }

}
