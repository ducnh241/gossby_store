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
class Model_Catalog_Order_Log extends Abstract_Core_Model {
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';
    protected $_table_name = 'catalog_order_log';
    protected $_pk_field = 'record_id';

    /**
     *
     * @var Model_User_Member
     */
    protected $_member_model = null;

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
    public function loadByOrderRecordId(int $order_record_id) {
        if ($order_record_id < 1) {
            throw new Exception('Order Record ID is empty');
        }

        return $this->setCondition(['condition' => '`order_masster_record_id` = :order_record_id', 'params' => ['order_record_id' => $order_record_id]])->load();
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
//            }
//        }

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

        if (isset($data['member_id'])) {
            $data['member_id'] = intval($data['member_id']);

            if ($data['member_id'] < 1) {
                $data['member_id'] = 0;
            } else {
                $member = $this->getPreLoadedModel('user/member', $data['member_id']);

                if (!$member) {
                    $errors[] = 'Member with ID #' . $data['member_id'] . ' is not exist';
                }
            }
        }

        if (isset($data['action_key'])) {
            if (preg_match('/([^a-zA-Z0-9_]|_{2,}|^_|_$)/', $data['action_key'])) {
                $errors[] = 'Action key is incorrect';
            } else if (strlen($data['action_key']) < 3) {
                $errors[] = 'Action key length should be at least 3 characters';
            }
        }

        if (isset($data['action_title'])) {
            $data['action_title'] = trim($data['action_title']);

            if (strlen($data['action_title']) < 3) {
                $errors[] = 'Action title length should be at least 3 characters';
            }
        }

        foreach (array('added_timestamp') as $key) {
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
//                'order_id' => 'Order ID is empty',
                'member_id' => 'Member ID is empty',
                'action_key' => 'Action key is empty',
                'action_title' => 'Action title is empty'
            ];

            foreach ($require_fields as $field_name => $err_message) {
                if (!isset($data[$field_name])) {
                    $errors[] = $err_message;
                }
            }

            $default_fields = [
                'shop_id' => OSC::getShop()->getId(),
                'content' => '',
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

        foreach (['content'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['content'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

    public function getMember() {
        if ($this->_member_model === null) {
            $this->_member_model = static::getPreLoadedModel('user/member', $this->data['member_id']);
        }

        return $this->_member_model;
    }

}
