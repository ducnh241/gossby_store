<?php

class Model_Orderdesk_QueueV2 extends Abstract_Core_Model {
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';

    protected $_table_name = 'catalog_order_orderdesk_queue_v2';
    protected $_pk_field = 'record_id';
    protected $_ukey_field = 'ukey';

    /**
     * @var Model_Catalog_Order_Item
     */
    protected $_orderItem = null;

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['queue_id'])) {
            $data['queue_id'] = trim($data['queue_id']);

            if ($data['queue_id'] == '') {
                $errors[] = 'Queue id is empty';
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

        if (isset($data['shop_id'])) {
            $data['shop_id'] = intval($data['shop_id']);

            if ($data['shop_id'] < 1) {
                $errors[] = 'shop id is empty';
            }
        }

        if (isset($data['order_id'])) {
            $data['order_id'] = intval($data['order_id']);

            if ($data['order_id'] < 1) {
                $errors[] = 'order id is empty';
            }
        }

        if (isset($data['line_items'])) {
            if (!is_array($data['line_items']) && count($data['line_items']) < 1) {
                $errors[] = 'line items is empty';
            }
        }

        if (isset($data['order_record_id'])) {
            $data['order_record_id'] = intval($data['order_record_id']);

            if ($data['order_record_id'] < 1) {
                $errors[] = 'order record id is empty';
            }
        }

        if (isset($data['queue_flag'])) {
            $data['queue_flag'] = intval($data['queue_flag']);

            if (!is_numeric($data['queue_flag'])) {
                $errors[] = 'queue flag id is empty';
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
                    'queue_id' => 'queue id is empty',
                    'member_id' => 'member id is empty',
                    'shop_id' => 'shop_id is empty',
                    'order_id' => 'order_id is empty',
                    'line_items' => 'line_items is empty',
                    'order_record_id' => 'order_record_id is empty',
                    'queue_flag' => 'queue_flag is empty',
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'error_message' => '',
                    'shipment' => '',
                    'reason' => '',
                    'order_desk_id' => 0,
                    'shipping_method' => '',
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            } else {
                unset($data['ukey']);
                unset($data['member_id']);
                unset($data['shop_id']);
                unset($data['order_id']);
                unset($data['order_record_id']);
                unset($data['line_items']);
                unset($data['queue_id']);
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

        foreach (['line_items','shipment'] as $key){
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }

    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['line_items','shipment'] as $key){
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

    public function getOrderLineItem()
    {
        if (count($this->data['line_items']) < 0) {
            return $this->_orderItem = null;
        }
        $ids = implode(',', array_keys($this->data['line_items']));

        /** @var Model_Catalog_Order_Item */
        $this->_orderItem = OSC::model('catalog/order/item')->getCollection()->addCondition('item_id', $ids, OSC_Database::OPERATOR_IN)->load();

        $data = [];
        $i = 0;
        foreach ($this->_orderItem->getItems() as $dataItem) {
            $data[$i]['id'] = $dataItem->data['item_id'];
            $data[$i]['product_name'] = $dataItem->data['title'];
            $data[$i]['product_type'] = $dataItem->data['product_type'];
            $data[$i]['image_url'] = $dataItem->data['image_url'];
            $data[$i]['qty'] = $this->data['line_items'][$dataItem->data['item_id']];
            $data[$i]['shop_id'] = $dataItem->data['shop_id'];
            $i++;
        }

        return $data;
    }
}
