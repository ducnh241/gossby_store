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
class Model_Catalog_Order_BulkQueue extends Abstract_Core_Model {
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';
    protected $_table_name = 'catalog_order_bulk_queue';
    protected $_pk_field = 'queue_id';

    /**
     *
     * @var Model_User_Member
     */
    protected $_member_model = null;

    /**
     *
     * @var Model_Catalog_Order
     */
    protected $_order_model = null;

    const QUEUE_FLAG = [
        'running' => 0,
        'queue' => 1,
        'error' => 2
    ];

    const ACTION_LABEL = [
        'fulfill' => 'Fulfill',
        'capture' => 'Capture',
        'refund' => 'Refund',
        'hold' => 'Hold',
        'fulfillment_cancel' => 'Cancel fulfillment'
    ];

    /**
     * 
     * @return Model_User_Member
     */
    public function getMember() {
        if ($this->_member_model === null) {
            $this->_member_model = static::getPreLoadedModel('user/member', $this->data['member_id']);
        }

        return $this->_member_model;
    }

    /**
     * 
     * @return Model_Catalog_Order
     */
    public function getOrder() {
        if ($this->_order_model === null) {
            $this->_order_model = static::getPreLoadedModel('catalog/order', $this->data['order_master_record_id']);
        }

        return $this->_order_model;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        foreach (['member_id' => 'Member ID', 'order_master_record_id' => 'Order Master Record ID'] as $key => $attr_name) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 1) {
                    $errors[] = $attr_name . ' is empty';
                }
            }
        }

        foreach (['error'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = trim($data[$key]);

                if ($data[$key] === '') {
                    $data[$key] = null;
                }
            }
        }

        foreach (['secondary_key'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = trim($data[$key]);
            }
        }

        foreach (['queue_flag'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);
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
                    'action' => 'Action is empty',
                    'order_master_record_id' => 'Order Master Record ID is empty',
                    'member_id' => 'Member ID is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'queue_flag' => 1,
                    'error' => null,
                    'secondary_key' => '',
                    'queue_data' => '',
                    'shop_id' => OSC::getShop()->getId(),
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            } else {
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

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['queue_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['queue_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

    public function insertMulti($items) {
        $queries = [];
        $params = [];
        $queue_data_counter = 0;
        $queue_secondary_counter = 0;
        $added_timestamp = time();

        foreach ($items as $item) {
            $item['order_master_record_id'] = intval($item['order_master_record_id']);

            if ($item['order_master_record_id'] < 1) {
                continue;
            }

            $item['member_id'] = intval($item['member_id']);

            if ($item['member_id'] < 1) {
                continue;
            }

            if (preg_match('/[^a-zA-Z0-9\_\-\/]/', $item['action'])) {
                continue;
            }

            if (isset($item['queue_data'])) {
                $queue_data_counter ++;
                $queue_data = ':queue_data_' . $queue_data_counter;

                $params['queue_data_' . $queue_data_counter] = OSC::encode($item['queue_data']);
            } else {
                $queue_data = "''";
            }

            if (isset($item['secondary_key'])) {
                $queue_secondary_counter ++;
                $secondary_key = ':secondary_key_' . $queue_secondary_counter;

                $params['secondary_key_' . $queue_secondary_counter] = trim($item['secondary_key']);
            } else {
                $secondary_key = "NULL";
            }

            $queries[] = "('{$item['member_id']}', '{$item['action']}', '" . OSC::getShop()->getId() . "', '{$item['order_master_record_id']}', {$secondary_key}, {$queue_data}, 1, {$added_timestamp})";
        }

        $queries = implode(',', $queries);

        $this->getWriteAdapter()->query("INSERT IGNORE INTO {$this->getTableName(true)} (member_id, action, shop_id, order_master_record_id, secondary_key, queue_data, queue_flag, added_timestamp) VALUES {$queries};", $params, 'insert_queues');

        return $this->getWriteAdapter()->getNumAffected('insert_queues');
    }

    public function insertMultiUpdateDuplicateKey($items) {
        $queries = [];
        $params = [];
        $queue_data_counter = 0;
        $queue_secondary_counter = 0;
        $added_timestamp = time();

        foreach ($items as $item) {
            $item['order_master_record_id'] = intval($item['order_master_record_id']);

            if ($item['order_master_record_id'] < 1) {
                continue;
            }

            $item['member_id'] = intval($item['member_id']);

            if ($item['member_id'] < 1) {
                continue;
            }

            if (preg_match('/[^a-zA-Z0-9\_\-\/]/', $item['action'])) {
                continue;
            }

            if (isset($item['queue_data'])) {
                $queue_data_counter ++;
                $queue_data = ':queue_data_' . $queue_data_counter;

                $params['queue_data_' . $queue_data_counter] = OSC::encode($item['queue_data']);
            } else {
                $queue_data = "''";
            }

            if (isset($item['secondary_key'])) {
                $queue_secondary_counter ++;
                $secondary_key = ':secondary_key_' . $queue_secondary_counter;

                $params['secondary_key_' . $queue_secondary_counter] = trim($item['secondary_key']);
            } else {
                $secondary_key = "NULL";
            }

            $queries[] = "('{$item['member_id']}', '{$item['action']}', '" . OSC::getShop()->getId() . "', '{$item['order_master_record_id']}', {$secondary_key}, {$queue_data}, 1, '', {$added_timestamp})";
        }

        $queries = implode(',', $queries);

        $this->getWriteAdapter()->query("INSERT INTO {$this->getTableName(true)} (member_id, action, shop_id, order_master_record_id, secondary_key, queue_data, queue_flag, error, added_timestamp) VALUES {$queries} ON DUPLICATE KEY UPDATE member_id = IF(queue_flag = 0, member_id, VALUES(member_id)), queue_data = IF(queue_flag = 0, queue_data, VALUES(queue_data)), queue_flag = IF(queue_flag = 0, queue_flag, VALUES(queue_flag)), error = IF(queue_flag = 0, error, VALUES(error));", $params, 'insert_queues');

        return $this->getWriteAdapter()->getNumAffected('insert_queues');
    }

    protected function _afterSave() {
        parent::_afterSave();

        if ($this->getLastActionFlag() == static::UPDATE_FLAG) {
            try {
                if (in_array($this->data['action'], ['render_design_order_beta', 'campaign_rerender_v2'])) {
                    $queue_data = $this->data['queue_data'];
                    if (isset($queue_data['design_id']) && $queue_data['design_id']) {
                        OSC::helper('personalizedDesign/common')->updateLogRerender($queue_data, $this->data['queue_flag'], $this->data['queue_flag'] == self::QUEUE_FLAG['error'] ? $this->data['error'] : '');
                    }
                }
            } catch (Exception $ex) {}
        }
    }

    protected function _afterDelete() {
        parent::_afterDelete();

        try {
            if (in_array($this->data['action'], ['render_design_order_beta', 'campaign_rerender_v2'])) {
                $queue_data = $this->data['queue_data'];
                if (isset($queue_data['design_id']) && $queue_data['design_id']) {
                    OSC::helper('personalizedDesign/common')->updateLogRerender($queue_data, Model_PersonalizedDesign_RerenderLog::STATUS['success'], '');
                }
            }
        } catch (Exception $ex) {}
    }
}
