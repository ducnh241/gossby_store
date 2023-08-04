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
class Model_Catalog_Discount_Code_Usage extends Abstract_Core_Model {

    protected $_table_name = 'catalog_discount_code_usage';
    protected $_pk_field = 'usage_id';

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['discount_code'])) {
            unset($data['discount_code']);
        }

        if (isset($data['discount_code_id'])) {
            $data['discount_code_id'] = intval($data['discount_code_id']);

            if ($data['discount_code_id'] < 1) {
                $errors[] = 'Discount code ID is empty';
            } else {
                try {
                    $discount_code = OSC::model('catalog/discount_code')->load($data['discount_code_id']);
                    $data['discount_code'] = $discount_code->data['discount_code'];
                } catch (Exception $ex) {
                    $errors[] = $ex->getMessage();
                }
            }
        }

//        if (isset($data['order_id'])) {
//            $data['order_id'] = intval($data['order_id']);
//
//            if ($data['order_id'] < 1) {
//                $errors[] = 'Order ID is empty';
//            } else {
//                try {
//                    $order = OSC::model('catalog/order')->load($data['order_id']);
//                    $data['order_email'] = $order->data['email'];
//                } catch (Exception $ex) {
//                    $errors[] = $ex->getMessage();
//                }
//            }
//        }

        foreach (array('added_timestamp') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        foreach (array('code_auto_generated') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]) == 1 ? 1 : 0;
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
//                    'order_id' => 'Order ID is empty',
                    'order_email' => 'Order email is empty',
                    'discount_code_id' => 'Discount code ID is empty',
                    'discount_code' => 'Discount code is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'code_auto_generated' => 0,
                    'added_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            } else {
                $errors[] = 'The model not support update action';
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

}
