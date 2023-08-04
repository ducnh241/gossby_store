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
class Model_Shipping_Methods extends OSC_Database_Model {

    /**
     *
     * @var string
     */
    protected $_table_name = 'shipping_methods';

    /**
     *
     * @var string
     */
    protected $_pk_field = 'id';
    protected $_ukey_field = 'key';

    const STATUS_SHIPPING_METHOD_ON = 1;
    const STATUS_SHIPPING_METHOD_OFF = 0;

    const STATUS_SHIPPING_METHOD_DEFAULT = 1;

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['shipping_key'])) {
            $data['shipping_key'] = trim($data['shipping_key']);

            if ($data['shipping_key']  == '') {
                $errors[] = 'Shipping key is empty';
            }
        }

        if (isset($data['shipping_name'])) {
            $data['shipping_name'] = trim($data['shipping_name']);

            if ($data['shipping_name']  == '') {
                $errors[] = 'Shipping name is empty';
            }
        }

        if (isset($data['shipping_status'])) {
            $data['shipping_status'] = intval($data['shipping_status']);

            if (!in_array($data['shipping_status'], [0, 1])) {
                $errors[] = 'Shipping status is error';
            }
        }

        if (isset($data['is_default'])) {
            $data['is_default'] = intval($data['is_default']);

            if (!in_array($data['is_default'], [0, 1])) {
                $errors[] = 'is_default is error';
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
                    'shipping_key' => 'Key is empty',
                    'shipping_name' => 'Name is empty',
                    'shipping_status' => 'Status data is empty',
                    'is_default' => 'Shipping default data is empty'
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

    public function isShippingMethodDefault() {
        if ($this->data['is_default'] == $this::STATUS_SHIPPING_METHOD_DEFAULT) {
            return true;
        }

        return false;
    }

    public function checkStatusShippingMethod() {
        if ($this->data['shipping_status'] == $this::STATUS_SHIPPING_METHOD_ON) {
            return true;
        }

        return false;
    }
}
