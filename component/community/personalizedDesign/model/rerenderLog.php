<?php

class Model_PersonalizedDesign_RerenderLog extends Abstract_Core_Model {

    protected $_table_name = 'personalized_design_rerender_log';
    protected $_pk_field = 'id';

    const STATUS = [
        'running' => 0,
        'waiting to run' => 1,
        'error' => 2,
        'success' => 3
    ];

    public function insertMultiUpdateDuplicateKey($items) {
        $queries = [];
        $added_timestamp = time();

        foreach ($items as $item) {
            $item['member_id'] = intval($item['member_id']);

            if ($item['member_id'] < 1) {
                continue;
            }

            $item['order_id'] = intval($item['order_master_record_id']);

            if ($item['order_id'] < 1) {
                continue;
            }

            $item['order_item_id'] = intval($item['item_master_record_id']);

            if ($item['order_item_id'] < 1) {
                continue;
            }

            $item['design_id'] = intval($item['design_id']);

            if ($item['design_id'] < 1) {
                continue;
            }

            $queries[] = "('{$item['member_id']}', '{$item['order_id']}', '{$item['order_item_id']}', '{$item['design_id']}', 1, '', {$added_timestamp}, {$added_timestamp})";
        }

        $queries = implode(',', $queries);

        $this->getWriteAdapter()->query("INSERT INTO {$this->getTableName(true)} (member_id, order_id, order_item_id, design_id, status, message, added_timestamp, modified_timestamp) VALUES {$queries} ON DUPLICATE KEY UPDATE member_id = IF(status = 0, member_id, VALUES(member_id)), status = IF(status = 0, status, VALUES(status)), message = IF(status = 0, message, VALUES(message)), modified_timestamp = IF(status = 0, modified_timestamp, VALUES(modified_timestamp)), added_timestamp = IF(status = 0, added_timestamp, VALUES(added_timestamp));", null, 'insert_queues');

        return $this->getWriteAdapter()->getNumAffected('insert_queues');
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        if ($this->getActionFlag() == static::INSERT_FLAG) {

            $default_fields = [
                'added_timestamp' => time(),
                'modified_timestamp' => time()
            ];

            foreach ($default_fields as $field_name => $default_value) {
                if (!isset($data[$field_name])) {
                    $data[$field_name] = $default_value;
                }
            }
        } else {
            $data['modified_timestamp'] = time();
        }

        $this->resetDataModifiedMap()->setData($data);

    }

}
