<?php

class Model_PersonalizedDesign_Design_Draft extends Abstract_Core_Model {

    protected $_table_name = 'personalized_design_draft';
    protected $_pk_field = 'record_id';
    protected $_ukey_field = 'ukey';

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['design_data', 'meta_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['design_data', 'meta_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key]);
            }
        }
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = $this->_validateData($data);

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'design_id' => 'Design ID is empty',
                    'member_id' => 'Memberr ID is empty',
                    'design_data' => 'Design data is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                if (count($errors) < 1) {
                    $data['ukey'] = $data['member_id'] . '/' . $data['design_id'];
                }
            } else {
                $data['modified_timestamp'] = time();
                unset($data['ukey']);
                unset($data['member_id']);
                unset($data['design_id']);
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _validateData(&$data) {
        $errors = [];

        if (isset($data['design_data'])) {
            if (!is_array($data['design_data'])) {
                $errors[] = 'Design data is incorrect data';
            }
        }

        if (isset($data['design_id'])) {
            $data['design_id'] = intval($data['design_id']);

            if ($data['design_id'] < 0) {
                $errors[] = 'Design ID is incorrect';
            }
        }

        if (isset($data['member_id'])) {
            $data['member_id'] = intval($data['member_id']);

            if ($data['member_id'] < 1) {
                $errors[] = 'Member ID is incorrect';
            }
        }

        return $errors;
    }

    /**
     * @param $ukey
     * @param $params
     * @throws OSC_Database_Model_Exception
     */
    public function updateByUkey($ukey, $params) {
        $errors = $this->_validateData($params);

        if (count($errors) > 0) {
            throw new OSC_Database_Model_Exception(implode("<br/>", $errors));
        }

        $flag_insert = false;
        $params['design_data'] = OSC::encode($params['design_data']);
        $params['modified_timestamp'] = time();
        if (isset($params['meta_data'])) {
            $params['meta_data'] = OSC::encode($params['meta_data']);
        }

        $DB = OSC::core('database')->getWriteAdapter();

        $DB->select('record_id', $this->getTableName(), "ukey='{$ukey}'", 'record_id ASC', 1, 'fetch_personalized_draft');

        $row = $DB->fetchArray('fetch_personalized_draft');

        $DB->free('fetch_personalized_draft');

        if (!$row) {
            $flag_insert = true;
        }

        if ($flag_insert) {
            $params['ukey'] = $ukey;
            $params['added_timestamp'] = time();
            $DB->insert($this->getTableName(), $params, 'insert_personalized_draft');
        } else {
            $DB->update($this->getTableName(), $params, "ukey='{$ukey}'", 1, 'update_personalized_draft');
        }
    }

}
