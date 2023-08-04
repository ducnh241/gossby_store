<?php

class Model_Catalog_ProductTypeDescription extends Abstract_Core_Model {

    protected $_table_name = 'product_type_description';
    protected $_pk_field = 'id';

    protected $_allow_write_log = true;

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

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
                    'title' => 'Title is empty'
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
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    public function getImageUrl() {
        return $this->data['image'] ? OSC::core('aws_s3')->getStorageUrl($this->data['image']) : '';
    }

    public function checkIsUsing()
    {
        $id = $this->getId();
        $total_product_type_using_this_des = OSC::model('catalog/productType')->getCollection()->addCondition('description_id', $id)->load()->length();
        $total_product_type_variant_using_this_des = OSC::model('catalog/productType_variant')->getCollection()->addCondition('description_id', $id)->load()->length();
        if ($total_product_type_using_this_des > 0 || $total_product_type_variant_using_this_des > 0) {
            return true;
        }
        return false;
    }
}
