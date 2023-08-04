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
class Model_Shop_Shop extends Abstract_Core_Model {
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';
    protected $_table_name = 'shop';
    protected $_pk_field = 'shop_id';
    protected $_ukey_field = 'shop_key';

    public static function cleanUkey($ukey) {
        $ukey = preg_replace('/[^a-zA-Z0-9\.\/\-\_\:]/', '', $ukey);
        $ukey = preg_replace('/(^[\.\/\-\_\:]+|[\.\/\-\_\:]+$)/', '', $ukey);
        $ukey = preg_replace('/\.{2,}/', '.', $ukey);
        $ukey = preg_replace('/\/{2,}/', '/', $ukey);
        $ukey = preg_replace('/\-{2,}/', '-', $ukey);
        $ukey = preg_replace('/\_{3,}/', '__', $ukey);
        $ukey = preg_replace('/\:{2,}/', ':', $ukey);

        return $ukey;
    }

    public function getStoreUrl() {
        if(preg_match('/^local-([a-z0-9]+)$/i', OSC_ENV)) {
            return 'http://' . $this->data['shop_domain'];
        }
        
        return 'https://' . $this->data['shop_domain'];
    }

    public function getStorageUrl($file) {
        return $this->getStoreUrl() . '/storage/' . $file;
    }

    public function getLogo($flag_email = true) {
        $logo = $this->setting('theme/logo');
        if ($flag_email) {
            $logo_email = $this->setting('theme/logo/email');
            if (is_array($logo_email) && isset($logo_email['file'])) {
                $logo = $logo_email;
            }
        }

        if (is_array($logo)) {
            $logo['file'] = $this->getStorageUrl($logo['file']);
        } else {
            $logo = [
                'file' => OSC::helper('backend/template')->getImage('logo.svg')
            ];
        }

        $return = new stdClass();

        $return->url = $logo['file'];
        $return->alt = $logo['alt'];

        return $return;
    }

    public function setting($setting_key) {
        return isset($this->data['setting'][$setting_key]) ? $this->data['setting'][$setting_key] : null;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['shop_name'])) {
            $data['shop_name'] = trim($data['shop_name']);

            if (!$data['shop_name']) {
                $errors[] = 'Must input shop name';
            }
        }

        if (isset($data['shop_domain'])) {
            $data['shop_domain'] = trim($data['shop_domain']);

            if (!OSC::core('validate')->validUrl($data['shop_domain'])) {
                $errors[] = 'Domain is not right';
            }
        }

        if (isset($data['setting']) && !is_array($data['setting'])) {
            $data['setting'] = [];
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == self::INSERT_FLAG) {
                $require_fields = [
                    'shop_name' => 'Shop name is empty',
                    'shop_domain' => 'Domain is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'group_id' => 0,
                    'status' => 1,
                    'metadata' => [],
                    'setting' => [],
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                $data['secret_key'] = OSC::makeUniqid(null, true);
                $data['shop_key'] = preg_replace('/[^\.a-zA-Z0-9]/', '_', $data['shop_domain']);
                $data['shop_key'] = preg_replace('/_{2,}/', '_', $data['shop_key']);
                $data['shop_key'] = preg_replace('/\./', '__', $data['shop_key']);
            } else {
                unset($data['shop_key']);
                $data['modified_timestamp'] = time();
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['metadata'])) {
            $data['metadata'] = OSC::encode($data['metadata']);
        }

        if (isset($data['setting'])) {
            $data['setting'] = OSC::encode($data['setting']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['metadata'])) {
            $data['metadata'] = OSC::decode($data['metadata']);
        }

        if (isset($data['setting'])) {
            $data['setting'] = OSC::decode($data['setting']);
        }
    }

}
