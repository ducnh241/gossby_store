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
class Model_PostOffice_Email_Queue extends Abstract_Core_Model {

    protected $_table_name = 'post_office_email_queue';
    protected $_pk_field = 'queue_id';

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['email_callback']) && is_array($data['email_callback'])) {
            $data['email_callback'] = OSC::encode($data['email_callback']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['email_callback']) && $data['email_callback']) {
            $data['email_callback'] = OSC::decode($data['email_callback']);
        }
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['member_id'])) {
            $data['member_id'] = intval($data['member_id']);

            if ($data['member_id'] < 1) {
                $data['member_id'] = null;
            } else {
                try {
                    $member = OSC::model('user/member')->load($data['member_id']);
                } catch (Exception $ex) {
                    $errors[] = $ex->getCode() == 404 ? "Member with ID [{$data['member_id']}] is not exists" : $ex->getMessage();
                }
            }
        }

        if (isset($data['email_callback'])) {
            if (is_array($data['email_callback'])) {
                foreach ($data['email_callback'] as $key => $value) {
                    if (!in_array($key, ['validate'], true)) {
                        unset($data['email_callback'][$key]);
                    }
                }

                if (count($data['email_callback']) < 1) {
                    $data['email_callback'] = null;
                }
            } else {
                $data['email_callback'] = null;
            }
        }

        foreach (['html_content', 'text_content', 'note', 'error_message', 'email_key'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = trim($data[$key]);

                if ($data[$key] === '') {
                    $data[$key] = null;
                }
            }
        }

        foreach (['subject' => 'Subject is empty', 'receiver_name' => 'Receiver name is empty', 'sender_name' => 'Sender name is empty'] as $key => $error_message) {
            if (isset($data[$key])) {
                $data[$key] = trim($data[$key]);

                if ($data[$key] === '') {
                    $errors[] = $error_message;
                }
            }
        }

        foreach (['receiver_email' => 'Receiver email', 'sender_email' => 'Sender email'] as $key => $attr_name) {
            if (isset($data[$key])) {
                $data[$key] = trim($data[$key]);

                if ($data[$key] === '') {
                    $errors[] = $attr_name . ' is empty';
                } else {
                    try {
                        OSC::core('validate')->validEmail($data[$key]);
                    } catch (Exception $ex) {
                        $errors[] = $attr_name . ' :: ' . $ex->getMessage();
                    }
                }
            }
        }

        if (isset($data['priority'])) {
            $data['priority'] = intval($data['priority']);
        }

        foreach (['added_timestamp', 'modified_timestamp', 'running_timestamp'] as $key) {
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
                    'subject' => 'Email subject is empty',
                    'receiver_email' => 'Receiver email is empty',
                    'receiver_name' => 'Receiver name is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'email_key' => null,
                    'member_id' => null,
                    'sender_email' => OSC::helper('core/setting')->get('theme/contact/email'),
                    'sender_name' => OSC::helper('core/setting')->get('theme/site_name'),
                    'note' => null,
                    'priority' => 0,
                    'email_callback' => null,
                    'html_content' => null,
                    'text_content' => null,
                    'state' => 'queue',
                    'error_message' => null,
                    'running_timestamp' => time(),
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                if ($data['html_content'] === null && $data['text_content'] === null) {
                    $errors[] = 'Email content is empty';
                }

                $data['token'] = OSC::makeUniqid(false, true);

                $token_marker = Helper_PostOffice_Email::getTokenMarker();

                foreach (['html_content', 'text_content'] as $key) {
                    if ($data[$key]) {
                        $data[$key] = str_replace($token_marker, $data['token'], $data[$key]);
                    }
                }
            } else {
                foreach (array_keys($data) as $k) {
                    if (!in_array($k, ['modified_timestamp', 'error_message', 'state'], true)) {
                        unset($data[$k]);
                    }
                }

                if (!isset($data['modified_timestamp'])) {
                    $data['modified_timestamp'] = time();
                }
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

}
