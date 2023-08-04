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
class Model_PostOffice_Subscriber extends Abstract_Core_Model {

    protected $_table_name = 'post_office_subscriber';
    protected $_pk_field = 'subscriber_id';
    protected $_ukey_field = 'token';

    public function loadByEmail($email) {
        $email = trim($email);

        OSC::core('validate')->validEmail($email);

        return $this->setCondition(['field' => 'email', 'value' => $email, 'operator' => OSC_Database::OPERATOR_EQUAL])->load();
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['email'])) {
            $data['email'] = trim($data['email']);

            if ($data['email'] === '') {
                $errors[] = 'Email is empty';
            } else {
                try {
                    OSC::core('validate')->validEmail($data['email']);
                } catch (Exception $ex) {
                    $errors[] = $ex->getMessage();
                }
            }
        }

        foreach (['newsletter'] as $k) {
            if (isset($data[$k])) {
                $data[$k] = intval($data[$k]) == 1 ? 1 : 0;
            }
        }

        foreach (['added_timestamp', 'modified_timestamp'] as $key) {
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
                    'email' => 'Email is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'newsletter' => 1,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                $data['token'] = OSC::makeUniqid(null, true);
            } else {
                unset($data['email']);
                unset($data['token']);

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
