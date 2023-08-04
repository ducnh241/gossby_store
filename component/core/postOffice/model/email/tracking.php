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
class Model_PostOffice_Email_Tracking extends Abstract_Core_Model {

    protected $_table_name = 'post_office_email_tracking';
    protected $_pk_field = 'record_id';

    /**
     *
     * @var Model_PostOffice_Email
     */
    protected $_email = null;

    /**
     * 
     * @param Model_PostOffice_Email $email
     * @return $this
     */
    public function setEmail(Model_PostOffice_Email $email) {
        $this->_email = $email;

        return $this;
    }

    /**
     * 
     * @param bool $reset
     * @return \Model_PostOffice_Email
     * @throws Exception
     */
    public function getEmail(bool $reset = false): Model_PostOffice_Email {
        if ($this->_email === null || $reset) {
            if (!$reset) {
                $this->_email = $this->getPreLoadedModel('postOffice/email', $this->data['email_id']);
            } else {
                $this->_email = OSC::model('postOffice/email')->load($this->data['email_id']);
            }

            if (!$this->_email) {
                throw new Exception('Cannot load email');
            }
        }

        return $this->_email;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        foreach (['email_id'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $errors[] = $k . ' is empty';
                }
            }
        }

        foreach (['added_timestamp'] as $key) {
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
                    'event' => 'Event is empty',
                    'email_id' => 'Email ID is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'referer' => null,
                    'event_data' => null,
                    'added_timestamp' => time()
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            } else {
                $errors[] = 'The model don\t support update method';
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

}
