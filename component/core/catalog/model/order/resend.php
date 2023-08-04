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
class Model_Catalog_Order_Resend extends Abstract_Core_Model {
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';
    protected $_table_name = 'catalog_order_resend_queue';
    protected $_pk_field = 'record_id';
    const TBL_RESEND_QUEUE = 'catalog_order_resend_queue';

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

        if (isset($data['order_id'])) {
            $data['order_id'] = intval($data['order_id']);

            if ($data['order_id'] < 1) {
                $errors[] = 'order_id is not exists';
            }
        }

        if (isset($data['data'])) {
            if (!is_array($data['data']) || count($data['data']) < 1) {
                $errors[] = 'data is empty';
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
                    'order_master_record_id' => 'order master record id is empty',
                    'member_id' => 'Member ID is empty',
                    'order_id' => 'order_id empty',
                    'data' => 'data empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'shop_id' => OSC::getShop()->getId(),
                    'error_message' => '',
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

    /**
     *
     * @param int $order_record_id
     * @return $this
     * @throws Exception
     */
    public function loadByOrderRecordId(int $order_master_record_id) {
        if ($order_master_record_id < 1) {
            throw new Exception('Order Record ID is empty');
        }

        return $this->setCondition(['condition' => '`order_master_record_id` = :order_master_record_id', 'params' => ['order_master_record_id' => $order_master_record_id]])->load();
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

}
