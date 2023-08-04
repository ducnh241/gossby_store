<?php

class Helper_D2_Resource extends OSC_Object {

    /**
     * @param array $conditions
     * @return void
     * @throws OSC_Database_Model_Exception
     */
    public function saveConditions($conditions, $resource_id, $action_id) {
        foreach ($conditions as $condition) {
            OSC::model('d2/condition')->setData([
                'resource_id' => $resource_id,
                'condition_key' => trim($condition['key']),
                'condition_value' => trim($condition['value']),
                'member_id' => $action_id
            ])->save();
        }
    }
}
