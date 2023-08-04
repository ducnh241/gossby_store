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
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Model_Shipping_DeliveryTime extends OSC_Database_Model {

    /**
     *
     * @var string
     */
    protected $_table_name = 'shipping_delivery_time';

    /**
     *
     * @var string
     */
    protected $_pk_field = 'id';

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        foreach (['shipping_method_id', 'process_time', 'estimate_time'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 1) {
                    $errors[] = $key . ' is empty';
                }
            }
        }

        foreach (['group_id', 'child_group'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = trim($data[$key]);

                if ($data[$key]  == '') {
                    $errors[] = $key . ' is empty';
                }
            }
        }

        foreach (['location_data', 'location_parsed'] as $key) {
            if (isset($data[$key])) {
                if (!is_array($data[$key]) || count($data[$key]) < 1) {
                    $errors[] = $key . ' is empty';
                }
            }
        }

        foreach (['product_type_id', 'product_type_variant_id'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $errors[] = $key . ' is empty';
                }
            }
        }


        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'shipping_method_id' => 'shipping_method_id is empty',
                    'group_id' => 'group_id is empty',
                    'location_data' => 'location_data is empty',
                    'location_parsed' => 'location_parsed is empty',
                    'product_type_id' => 'product_type_id is empty',
                    'product_type_variant_id' => 'product_type_variant_id is empty',
                    'process_time' => 'process_time is empty',
                    'estimate_time' => 'estimate_time is empty',
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

        foreach (['location_data', 'location_parsed'] as $key){
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }

    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['location_data', 'location_parsed']  as $key){
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

    public function getDeliveryTimeByLocationData($product_type_ids = [], $product_type_variant_ids = [], $country_id = '', $province_id = ''){
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        /* 0 all product type */
        $product_type_ids = array_unique(array_merge($product_type_ids, [0]));
        /* 0 all product type variant */
        $product_type_variant_ids = array_unique(array_merge($product_type_variant_ids, [0]));

        $DB->select('id, shipping_method_id, location_parsed, product_type_id, product_type_variant_id, process_time, estimate_time',
            OSC::model('shipping/deliveryTime')->getTableName(),
            "`product_type_id` IN (" . implode(',', $product_type_ids) . ") AND `product_type_variant_id` IN (" . implode(',', $product_type_variant_ids) . ") AND (`location_parsed` LIKE '%\"" . $country_id . "_" . $province_id . "\"%' OR `location_parsed` LIKE '%\"" . $country_id . "_*\"%' OR `location_parsed` LIKE '%\"*_*\"%')",
            null, null, 'fetch_delivery_time');

        return $DB->fetchArrayAll('fetch_delivery_time');
    }
}
