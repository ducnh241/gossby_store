<?php

class Model_Catalog_Product_Pack extends Abstract_Core_Model {
    protected $_table_name = 'catalog_product_pack';
    protected $_pk_field = 'id';

    protected $_product_type_model = null;

    /* If modify this const, please review common.js (Price pack of product detail) */
    const PERCENTAGE = 0;
    const FIXED_AMOUNT = 1;

    const STATE_PACK_AUTO = [
        'ON' => 1,
        'OFF' => 0
    ];

    /**
     *
     * @return Model_Catalog_ProductType|OSC_Database_Model
     */
    public function getProductType() {
        if ($this->_product_type_model === null) {
            $this->_product_type_model = static::getPreLoadedModel('catalog/productType', $this->data['product_type_id']);
        }

        return $this->_product_type_model;
    }

    /**
     *
     * @param Model_Catalog_ProductType $product_type
     * @return $this
     */
    public function setProductType(Model_Catalog_ProductType $product_type) {
        $this->_product_type_model = $product_type;

        return $this;
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['discount_value'])) {
            $data['discount_value'] = OSC::helper('catalog/common')->floatToInteger(floatval($data['discount_value']));
        }

        if (isset($data['marketing_point_rate'])) {
            $data['marketing_point_rate'] = OSC::helper('catalog/common')->floatToInteger(floatval($data['marketing_point_rate']));
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['discount_value'])) {
            $data['discount_value'] = OSC::helper('catalog/common')->integerToFloat(intval($data['discount_value']));
        }

        if (isset($data['marketing_point_rate'])) {
            $data['marketing_point_rate'] = OSC::helper('catalog/common')->integerToFloat(intval($data['marketing_point_rate']));
        }
    }

    protected function _beforeSave() {
        parent::_beforeSave();
        $data = $this->_collectDataForSave();

        $errors = [];

        if (!isset($data['product_type_id'])) {
            $errors[] = 'Product type is not found';
        }

        if (!isset($data['discount_type'])) {
            $errors[] = 'Discount type is empty';
        }

        if (!isset($data['discount_value'])) {
            $errors[] = 'Discount value is empty';
        }

        if ($data['discount_type'] !== static::PERCENTAGE && $data['discount_type'] !== static::FIXED_AMOUNT) {
            $errors[] = 'Discount type is not found';
        }

        if ($data['discount_type'] === static::PERCENTAGE && $data['discount_value'] < 0 || $data['discount_value'] > 100) {
            $errors[] = 'Discount value is between from 0 to 100';
        }

        if ($data['marketing_point_rate'] < 0 || $data['marketing_point_rate'] > 100) {
            $errors[] = 'Marketing point rate is between from 0 to 100';
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
                    'product_type_id' => 'Product type is empty',
                    'discount_type' => 'Discount type is empty',
                    'discount_value' => 'Discount value is empty',
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

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
                unset($data['added_timestamp']);
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    /**
     * Check this table has record
     * @return bool
     */
    public function hasRecord() {
        $DB = OSC::core('database');

        $DB->query("SELECT count(*) as count FROM {$this->getTableName(true)}");

        $result = 0;
        while ($row = $DB->fetchArray()) {
            $result = intval($row['count']);
        }

        return $result > 0;
    }

    public function getDistinctProductTypeId() {
        $db_transaction_key = 'get_distinct_product_type_id';
        $database = OSC::core('database')->getAdapter();
        $database->select(
            'distinct product_type_id',
            $this->getTableName(),
            null,
            null,
            null,
            $db_transaction_key
        );

        $distinct_product_type_ids = $database->fetchArrayAll($db_transaction_key);
        $database->free($db_transaction_key);

        return array_map(
            function ($distinct_product_type_id) {
                return intval($distinct_product_type_id);
            },
            array_column($distinct_product_type_ids, 'product_type_id')
        );
    }
}
