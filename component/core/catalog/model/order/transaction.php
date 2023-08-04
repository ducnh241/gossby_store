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
class Model_Catalog_Order_Transaction extends Abstract_Core_Model {
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';
    protected $_table_name = 'catalog_order_transaction';
    protected $_pk_field = 'record_id';

    /**
     *
     * @var Model_Catalog_Order
     */
    protected $_order = null;

    /**
     *
     * @var Model_User_Member
     */
    protected $_member = null;

    const TRANSACTION_TYPES = [
        'authorize' => 'Authorize',
        'capture' => 'Capture',
        'payment' => 'Payment',
        'refund' => 'Refund',
        'cancel' => 'Cancel'
    ];
    const TRANSACTION_TYPE_AUTHORIZE = 'authorize';
    const TRANSACTION_TYPE_CAPTURE = 'capture';
    const TRANSACTION_TYPE_PAYMENT = 'payment';
    const TRANSACTION_TYPE_REFUND = 'refund';
    const TRANSACTION_TYPE_CANCEL = 'cancel';

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
     * @param mixed $member
     * @return $this
     */
    public function setMember($member = null) {
        $this->_customer = $member instanceof Model_User_Member ? $member : null;
        return $this;
    }

    /**
     * 
     * @param bool $reload
     * @return \Model_User_Member
     * @throws Exception
     */
    public function getMember(bool $reload = false): Model_User_Member {
        if ($this->_member === null || $reload) {
            if ($this->data['member_id'] < 1) {
                $this->_member = OSC::model('user/member')->setGuestData();
            } else {
                $this->_member = $this->getPreLoadedModel('user/member', $this->data['member_id']);

                if (!$this->_member) {
                    throw new Exception('Cannot load member');
                }
            }
        }

        return $this->_member;
    }

    /**
     * 
     * @param int $order_record_id
     * @return $this
     * @throws Exception
     */
    public function loadByOrderRecordId(int $order_record_id) {
        if ($order_record_id < 1) {
            throw new Exception('Order Record ID is empty');
        }

        return $this->setCondition(['condition' => '`order_record_id` = :order_record_id', 'params' => ['order_record_id' => $order_record_id]])->load();
    }

    protected function _beforeSave() {
        if ($this->getActionFlag() != static::INSERT_FLAG) {
            throw new Exception('Model not allowed to update');
        }

        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

//        if (isset($data['order_id'])) {
//            $data['order_id'] = intval($data['order_id']);
//
//            if ($data['order_id'] < 1) {
//                $errors[] = 'Order ID is empty';
//            } else {
//                $order = $this->getOrder();
//
//                if (!$order || !$order->getId()) {
//                    $errors[] = 'Order is not exists';
//                }
//            }
//        }

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

        if (isset($data['amount'])) {
            $data['amount'] = intval($data['amount']);

            if ($data['amount'] == 0) {
                $errors[] = 'Transaction amount is incorrect';
            }
        }

        if (isset($data['transaction_type'])) {
            $data['transaction_type'] = trim($data['transaction_type']);

            if (!$data['transaction_type']) {
                $errors[] = 'Transaction type is empty';
            } else if (!isset(static::TRANSACTION_TYPES[$data['transaction_type']])) {
                $errors[] = 'Transaction type is incorrect';
            }
        }

        if (isset($data['note'])) {
            $data['note'] = trim($data['note']);
        }

        if (isset($data['order_master_record_id'])) {
            $data['order_master_record_id'] = intval($data['order_master_record_id']);

            if ($data['order_master_record_id'] < 1) {
                $errors[] = 'Order Master Record ID is empty';
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
            $require_fields = [
                'order_master_record_id' => 'Order Master Record ID is empty',
                'member_id' => 'Member ID is empty',
                'amount' => 'Amount is empty',
                'transaction_type' => 'Transaction type is empty'
            ];

            foreach ($require_fields as $field_name => $err_message) {
                if (!isset($data[$field_name])) {
                    $errors[] = $err_message;
                }
            }

            $default_fields = [
                'shop_id' => OSC::getShop()->getId(),
                'note' => '',
                'transaction_data' => [],
                'added_timestamp' => time()
            ];

            foreach ($default_fields as $field_name => $default_value) {
                if (!isset($data[$field_name])) {
                    $data[$field_name] = $default_value;
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

        foreach (['transaction_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['transaction_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

    protected function _afterSave() {
        parent::_afterSave();

        if ($this->getLastActionFlag() == static::INSERT_FLAG) {
            OSC::core('observer')->dispatchEvent('catalog/order_transaction/create', $this);
        }
    }

}
