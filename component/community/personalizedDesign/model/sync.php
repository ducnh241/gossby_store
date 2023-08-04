<?php

class Model_PersonalizedDesign_Sync extends Abstract_Core_Model {

    protected $_table_name = 'personalized_design_sync';
    protected $_pk_field = 'record_id';

    public function getSyncFlagCode()
    {
        $code_map = [
            0 => 'Queue',
            1 => 'Running',
            2 => 'Error'
        ];

        return $code_map[$this->data['syncing_flag']];
    }
    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['sync_data'])) {
            $data['sync_data'] = OSC::encode($data['sync_data']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['sync_data'])) {
            $data['sync_data'] = OSC::decode($data['sync_data'], true);
        }
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['sync_type'])) {
            $data['sync_type'] = trim($data['sync_type']);

            if (!$data['sync_type']) {
                $errors[] = 'Sync type is incorrect';
            }
        }

        if (isset($data['syncing_flag'])) {
            $data['syncing_flag'] = intval($data['syncing_flag']) == 1 ? 1 : 0;
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
                $require_fields = array(
                    'sync_type' => 'Sync type is empty',
                    'sync_data' => 'Sync data is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'ukey' => null,
                    'syncing_flag' => 0,
                    'sync_error' => null,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            } else {
                unset($data['ukey']);
                unset($data['sync_type']);
                unset($data['sync_data']);
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

}
