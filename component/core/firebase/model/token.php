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
class Model_Firebase_Token extends Abstract_Core_Model {

    protected $_table_name = 'firebase_token';
    protected $_pk_field = 'token_id';
    protected $_ukey_field = 'token';

    public static function cleanUkey($ukey) {
        $ukey = preg_replace('/[^a-zA-Z0-9\.\/\-\_\:]/', '', $ukey);
        $ukey = preg_replace('/(^[\.\/\-\_\:]+|[\.\/\-\_\:]+$)/', '', $ukey);

        return $ukey;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['token'])) {
            $data['token'] = trim($data['token']);

            if (!$data['token']) {
                $errors[] = 'Token is empty';
            }
        }

        if (isset($data['member_id'])) {
            $data['member_id'] = intval($data['member_id']);

            if ($data['member_id'] < 1) {
                $errors[] = 'Member ID is less than 0';
            }
        }

        foreach (array('added_timestamp', 'updated_timestamp') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 1) {
                    $data[$key] = 0;
                }
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'token' => 'Token is empty',
                    'member_id' => 'Member ID is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'client_ip' => OSC::core('request')->getClientIp(),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'added_timestamp' => time(),
                    'updated_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            } else {
                unset($data['token']);
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _afterSave() {
        parent::_afterSave();

        if ($this->getLastActionFlag() == static::INSERT_FLAG) {
            try {
                OSC::helper('firebase/common')->memberAddToken($this->data['member_id'], $this->data['token']);
            } catch (Exception $ex) {
                
            }
        }
    }

    protected function _afterDelete() {
        parent::_afterDelete();
        
        try {
            OSC::helper('firebase/common')->memberRemoveToken($this->data['member_id'], $this->data['token']);
        } catch (Exception $ex) {
            
        }
    }

}
