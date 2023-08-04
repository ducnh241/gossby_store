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
class Model_Catalog_Order_EditDesignChangeProductType extends Abstract_Core_Model {
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';

    protected $_table_name = 'catalog_order_edit_design_change_product_type';
    protected $_pk_field = 'record_id';

    const WAIT_CONFIRM = 0;
    const WAIT_PAYMENT = 1;
    const EDIT_SUCCESS = 2;
    const EDIT_ERROR = 3;
    const PAYMENT_SUCCESS = 4;
    const RENDER_SUCCESS = 5;
    /**
     *
     * @var Model_Catalog_Order
     */
    protected $_order = null;

    /**
     *
     * @return $this->_order
     */
    public function getOrder() {
        if ($this->_order === null) {
            $this->_order = $this->getPreLoadedModel('catalog/order', $this->data['order_master_record_id']);
        }

        return $this->_order;
    }

    /**
     *
     * @param Model_Catalog_Order $order
     * @return $this
     */
    public function setOrder($order) {
        $this->_order = ($order instanceof Model_Catalog_Order) ? $order : null;
        return $this;
    }


    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['member_id_edit_design'])) {
            $data['member_id_edit_design'] = intval($data['member_id_edit_design']);

            if ($data['member_id_edit_design'] < 1) {
                $errors[] = 'Member edit design is not exists';
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

        if (isset($data['shop_id'])) {
            $data['shop_id'] = intval($data['shop_id']);

            if ($data['shop_id'] < 1) {
                $errors[] = 'Shop ID is empty';
            }
        }

        if (isset($data['order_master_record_id'])) {
            $data['order_master_record_id'] = intval($data['order_master_record_id']);

            if ($data['order_master_record_id'] < 1) {
                $errors[] = 'Order Master Record ID is empty';
            }
        }

        if (isset($data['order_id'])) {
            $data['order_id'] = intval($data['order_id']);

            if ($data['order_id'] < 1) {
                $errors[] = 'Order ID is empty';
            }
        }

        if (isset($data['order_master_item_id'])) {
            $data['order_master_item_id'] = intval($data['order_master_item_id']);

            if ($data['order_master_item_id'] < 1) {
                $errors[] = 'order master item id is empty';
            }
        }

        if (isset($data['quantity'])) {
            $data['quantity'] = intval($data['quantity']);

            if ($data['quantity'] < 1) {
                $errors[] = 'quantity is empty';
            }
        }

        if (isset($data['order_code'])) {
            $data['order_code'] = trim($data['order_code']);

            if ($data['order_code']  == '') {
                $errors[] = 'order code is empty';
            }
        }

        if (isset($data['print_template_id_new'])) {
            $data['print_template_id_new'] = intval($data['print_template_id_new']);

            if ($data['print_template_id_new'] < 1) {
                $errors[] = 'print_template_id_new is empty';
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'shop_id' => 'Shop ID is empty',
                    'order_master_record_id' => 'Order Master Record ID is empty',
                    'order_id' => 'Order ID is empty',
                    'member_id_edit_design' => 'Member ID edit design is empty',
                    'order_master_item_id' => 'Order master item id is empty',
                    'item_id' => 'Item id is empty',
                    'quantity' => 'Quantity is empty',
                    'variant_id_new' => 'Variant item new empty',
                    'print_template_id_new' => 'Print Template id new empty',
                    'order_code' => 'Order code empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'other_quantity' => 1,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

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

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['config_option','additional_data','payment_method', 'payment_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['config_option','additional_data','payment_method', 'payment_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

    public function getUnitPriceWithDiscount() {
        return intval((100 - $this->data['discount_code_percent']) * ($this->data['price_base_cost_new'] - $this->data['price_base_cost_old']) / 100);
    }

    public function getShippingCompare() {
        return $this->data['price_shipping_new'] - $this->data['price_shipping_old'];
    }

    public function getSubTotalCompare() {
        return ($this->data['price_base_cost_new'] - $this->data['price_base_cost_old']) * $this->data['quantity'];
    }

    public function getTaxCompare() {
        return intval(round(($this->getSubTotalCompare() + $this->getShippingCompare()) * intval($this->data['tax_value_new']) / 100 - $this->data['discount_code_percent'] * $this->getSubTotalCompare() * intval($this->data['tax_value_new']) / 10000));
    }

    public function getTotalCompare() {
        return $this->getSubTotalCompare() + $this->getShippingCompare() + $this->getTaxCompare() - $this->getDiscountCodeSubTotal();
    }

    public function getDiscountCodeSubTotal() {
        return intval($this->data['discount_code_percent'] * $this->getSubTotalCompare() / 100);
    }
}
