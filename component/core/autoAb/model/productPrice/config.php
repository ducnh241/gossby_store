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

class Model_AutoAb_ProductPrice_Config extends Abstract_Core_Model {

    protected $_table_name = 'auto_ab_product_price_config';
    protected $_pk_field = 'id';

    protected $_allow_write_log = true;

    const CONDITION_CAMPAIGN = 0;
    const CONDITION_STORE = 1;
    const CONDITION_FIXED_CAMPAIGN = 2;

    const CONFIG_TYPE_CAMPAIGN = 0;
    const CONFIG_TYPE_SEMITEST = 1;

    const STATUS_ALLOW = 1;
    const STATUS_OFF = 2;

    const STOP_OPTIONS_DO_SOME_THING = 1;
    const STOP_OPTIONS_APPLY_THE_BEST_RESULT = 2;
    const STOP_OPTIONS_CHOOSE_MANUALLY = 3;

    const CONDITION_TYPES = [
        0 => 'Set for Campaign',
        1 => 'Set for Store'
    ];
    const JSON_COLUMNS = [
        'location_data',
        'variant_data',
        'fixed_product_ids',
        'price_range'
    ];

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['price_range'])) {
            foreach ($data['price_range'] as $key => $value) {
                $data['price_range'][$key] = OSC::helper('catalog/common')->floatToInteger(floatval($value));
            }
        }

        foreach (self::JSON_COLUMNS as $column) {
            $data[$column] = OSC::encode($data[$column]);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (self::JSON_COLUMNS as $column) {
            $data[$column] = OSC::decode($data[$column]);
        }

        if (isset($data['price_range'])) {
            foreach ($data['price_range'] as $key => $value) {
                $data['price_range'][$key] = OSC::helper('catalog/common')->integerToFloat(intval($value));
            }
        }

    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['title']) && mb_strlen($data['title']) < 1) {
            $errors[] = 'Title is empty!';
        }

        if (isset($data['location_data']) && (count($data['location_data']) === 0 || $data['location_data'] === 'null')) {
            $errors[] = 'Country is empty!';
        }

        if (isset($data['variant_data']) &&
            (count($data['variant_data']) === 0) &&
            $data['config_type'] === self::CONFIG_TYPE_CAMPAIGN
        ) {
            $errors[] = 'Variant data is empty!';
        }

        if ($data['config_type'] === self::CONFIG_TYPE_SEMITEST && empty($data['fixed_product_ids'])) {
            $errors[] = 'Fixed products data is empty!';
        }

        if (isset($data['fee']) && mb_strlen($data['fee']) < 1) {
            $errors[] = 'Fee is empty!';
        }

        if (isset($data['begin_at']) && mb_strlen($data['begin_at']) < 1) {
            $errors[] = 'Condition begin empty!';
        }

        if (isset($data['finish_at']) && mb_strlen($data['finish_at']) < 1) {
            $errors[] = 'Condition finish empty!';
        }

        if (isset($data['price_range'])) {
            if (!is_array($data['price_range']) || count($data['price_range']) === 0) {
                $errors[] = 'Price range is empty!';
            }

            foreach ($data['price_range'] as $price) {
                if ($price === '') {
                    $errors[] = 'Price range is empty!';
                    break;
                }
            }
        }

        if (isset($data['variant_data']) && count($data['variant_data']) > 0) {
            foreach ($data['variant_data'] as $key => $value) {
                $data['variant_data'][$key]['product_type_id'] = intval($value['product_type_id']);

                foreach ($value['variants'] as $k => $product_type_variant_id) {
                    $value['variants'][$k] = intval($product_type_variant_id);
                }
                $data['variant_data'][$key]['variants'] = is_array($value['variants']) ? $value['variants'] : [];
            }
        }

        /*$product_type_variant_ids = OSC::helper('core/common')->parseProductTypeVariantIds($data['variant_data']);
        $base_cost_configs = OSC::helper('catalog/product')->getBaseCostConfig($data['variant_data'], $data['location_data']);
        foreach ($product_type_variant_ids as $value) {
            if (!isset($base_cost_configs[$value . '_1'])) {
                $product_type_variant_title = '';

                try {
                    $product_type_variant_title = OSC::model('catalog/productType_variant')->load($value)->data['title'];
                } catch (Exception $ex) {

                }

                $errors[] = 'Base cost of ' . $product_type_variant_title . ' has not been set';
            }
        }*/

        foreach (['fee', 'begin_at', 'finish_at'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'title' => 'Title is empty!',
                    'location_data' => 'Country is empty!',
                    'variant_data' => 'Variant data is empty!',
                    'fee' => 'Fee is empty!',
                    'begin_at' => 'Condition begin empty!',
                    'finish_at' => 'Condition finish empty!',
                    'price_range' => 'Price range is empty!'
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

    protected function _afterDelete() {
        parent::_afterDelete();

        OSC::model('autoAb/productPrice')->getCollection()
            ->addCondition('config_id', $this->getId())
            ->load()
            ->delete();

        OSC::model('autoAb/productPrice_tracking')->getCollection()
            ->addCondition('config_id', $this->getId())
            ->load()
            ->delete();
    }


    public function isBegin($product_id) {
        $quantity_begin = intval($this->data['begin_at']);
        $quantity_sold = $this->_getQuantitySoldOfConfig($product_id);

        return $quantity_sold >= $quantity_begin;
    }

    public function isFinish($product_id) {
        $quantity_finish = intval($this->data['finish_at']);
        $quantity_sold = $this->_getQuantitySoldOfConfig($product_id);

        return $quantity_sold >= $quantity_finish;
    }

    protected function _getQuantitySoldOfConfig($product_id) {
        $quantity = 0;

        try {
            $tracking_data = OSC::helper('autoAb/productPrice')->getTrackingOfConfigABTestPrice($this->getId());

            if (intval($this->data['condition_type']) === self::CONDITION_CAMPAIGN) {
                foreach ($tracking_data as $data) {
                    if ($product_id === $data['product_id']) {
                        $quantity += $data['quantity'];
                    }
                }
            } else {
                foreach ($tracking_data as $data) {
                    $quantity += $data['quantity'];
                }
            }
        } catch (Exception $ex) {}

        return $quantity;
    }

    /**
     * @throws OSC_Database_Model_Exception
     * @throws OSC_Exception_Runtime
     */
    public function saveFlattenData() {
        OSC::model('autoAb/productPrice')->getCollection()
            ->addCondition('config_id', $this->getId())
            ->load()
            ->delete();

        $product_type_variant_ids = OSC::helper('core/common')->parseProductTypeVariantIds($this->data['variant_data']);
        $country_codes = $this->_parseCountryCodes();

        $exists_data = [];
        if (!empty($this->data['fixed_product_ids'])) {
            $fixed_product_type_variant_ids = OSC::model('catalog/product_variant')
                ->getCollection()
                ->addCondition('product_id', $this->data['fixed_product_ids'], OSC_Database::OPERATOR_IN)
                ->addField('product_type_variant_id')
                ->load()
                ->toArray();

            $fixed_product_type_variant_ids = is_array($fixed_product_type_variant_ids) && !empty($fixed_product_type_variant_ids) ?
                array_unique(array_column($fixed_product_type_variant_ids, 'product_type_variant_id')) :
                [];

            if (is_array($fixed_product_type_variant_ids) &&
                !empty($fixed_product_type_variant_ids) &&
                is_array($country_codes) &&
                !empty($country_codes)
            ) {
                OSC::model('autoAb/productPrice')->getCollection()
                    ->addCondition('product_type_variant_id', $fixed_product_type_variant_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                    ->addCondition('country_code', $country_codes, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                    ->load()
                    ->delete();
            }
        } else {
            $ab_test_exists = OSC::model('autoAb/productPrice')->getCollection()
                ->addCondition('product_type_variant_id', $product_type_variant_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                ->addCondition('country_code', $country_codes, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                ->load();

            foreach ($ab_test_exists as $ab_test_exist) {
                $item = $ab_test_exist->data['country_code'] . '_' . $ab_test_exist->data['product_type_variant_id'];
                $exists_data[$item] = $item;
            }
        }

        $data = [];
        foreach ($country_codes as $country_code) {
            foreach ($product_type_variant_ids as $product_type_variant_id) {
                $key = $country_code . '_' . $product_type_variant_id;
                if (in_array($key, $exists_data)) {
                    continue;
                }

                $data[] = [
                    'country_code' => $country_code,
                    'product_type_variant_id' => $product_type_variant_id,
                    'config_id' => $this->getId(),
                ];
            }
        }

        foreach ($data as $params) {
            OSC::model('autoAb/productPrice')->setData($params)->save();
        }
    }

    /**
     * Get list country code by location selector
     * @return array $country_codes
     */
    protected function _parseCountryCodes() {
        return $this->data['location_data'];
    }

    /**
     * Check config ab test apply for only some products
     * @return bool
     */
    public function isFixedForAnyProducts() {
        return !empty($this->data['fixed_product_ids']);
    }

    public function isSemitestConfig() {
        return $this->data['config_type'] === self::CONFIG_TYPE_SEMITEST;
    }

}
