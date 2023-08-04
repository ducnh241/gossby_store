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
class Model_Catalog_Order_Fulfillment extends Abstract_Core_Model {
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';
    protected $_table_name = 'catalog_order_fulfillment';
    protected $_pk_field = 'record_id';

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

    /**
     *
     * @param int $order_record_id
     * @return $this
     * @throws Exception
     */
    public function loadByOrderRecordId(int $order_master_record_id) {
        if ($order_master_record_id < 1) {
            throw new Exception('order_master_record_id is empty');
        }

        return $this->setCondition(['condition' => '`order_master_record_id` = :order_master_record_id', 'params' => ['order_master_record_id' => $order_master_record_id]])->load();
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['order_master_record_id'])) {
            $data['order_master_record_id'] = intval($data['order_master_record_id']);

            if ($data['order_master_record_id'] < 1) {
                $errors[] = 'order master record id is empty';
            } else {
                $order = $this->getOrder();

                if (!$order || !$order->getId()) {
                    $errors[] = 'Order is not exists';
                }
            }
        }

        if (isset($data['member_id'])) {
            $data['member_id'] = intval($data['member_id']);

            if ($data['member_id'] < 1) {
                $data['member_id'] = 0;
            } else {
                $member = $this->getPreLoadedModel('user/member', $data['member_id']);

                if (!$member || $member->getId() < 1) {
                    $errors[] = 'Member is not exists';
                }
            }
        }

        if (isset($data['quantity'])) {
            unset($data['quantity']);
        }

        if (isset($data['line_items'])) {
            if (!is_array($data['line_items']) || count($data['line_items']) < 1) {
                $errors[] = 'Fulfillment items is empty';
            } else {
                $line_items = [];
                $data['quantity'] = 0;

                foreach ($data['line_items'] as $line_item_id => $line_item) {
                    if (!isset($line_item['fulfill_quantity']) || !isset($line_item['before_quantity'])) {
                        
                    }

                    $line_item_id = intval($line_item_id);
                    $line_item['fulfill_quantity'] = intval($line_item['fulfill_quantity']);
                    $line_item['before_quantity'] = intval($line_item['before_quantity']);

                    if ($line_item_id < 1) {
                        $errors[] = 'Line items data is incorrect';
                        break;
                    }

                    if ($line_item['fulfill_quantity'] < 1) {
                        $errors[] = 'Line items data is incorrect';
                        break;
                    }

                    if ($line_item['fulfill_quantity'] > $line_item['before_quantity']) {
                        $errors[] = 'Line items data is incorrect';
                        break;
                    }

                    if (isset($line_items[$line_item_id])) {
                        $errors[] = 'Line items data is incorrect';
                        break;
                    } else {
                        $line_items[$line_item_id] = $line_item;

                        $data['quantity'] += $line_item['fulfill_quantity'];
                    }
                }

                $data['line_items'] = $line_items;
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

        if (isset($data['order_master_record_id'])) {
            $data['order_master_record_id'] = intval($data['order_master_record_id']);

            if ($data['order_master_record_id'] < 1) {
                $errors[] = 'Order Master Record ID is empty';
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'order_master_record_id' => 'Order Master Record ID is empty',
//                    'order_id' => 'Order ID is empty',
                    'member_id' => 'Member ID is empty',
                    'line_items' => 'Line items empty',
                    'quantity' => 'Quantity is empty',
                    'tracking_number' => 'Tracking number is empty',
                    'shipping_carrier' => 'Shipping carrier is empty',
                    'tracking_url' => 'Tracking URL is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'shop_id' => OSC::getShop()->getId(),
                    'additional_data' => [],
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            } else {
                unset($data['added_timestamp']);
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

        foreach (['line_items', 'additional_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['line_items', 'additional_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

    protected function _afterSave() {
        parent::_afterSave();

        if ($this->getLastActionFlag() == static::INSERT_FLAG) {
            OSC::core('observer')->dispatchEvent('catalog/order_fulfillment/create', $this);
        } else {
            OSC::core('observer')->dispatchEvent('catalog/order_fulfillment/update', $this);
        }
    }

}
