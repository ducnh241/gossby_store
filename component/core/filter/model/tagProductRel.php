<?php

class Model_Filter_TagProductRel extends Abstract_Core_Model {
    protected $_table_name = 'filter_tag_product_rel';
    protected $_pk_field = 'id';

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['tag_id'])) {
            $data['tag_id'] = intval($data['tag_id']);
            if ($data['tag_id'] < 1) {
                $errors[] = 'Product tag id not found';
            }
        }

        if (isset($data['product_id'])) {
            $data['product_id'] = intval($data['product_id']);
            if ($data['product_id'] < 1) {
                $errors[] = 'Product id not found';
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == self::INSERT_FLAG) {

                if (!isset($data['added_timestamp'])) {
                    $data['added_timestamp'] = time();
                }

                if (!isset($data['modified_timestamp'])) {
                    $data['modified_timestamp'] = 0;
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

}