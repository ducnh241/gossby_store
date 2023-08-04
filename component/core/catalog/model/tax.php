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
class Model_Catalog_Tax extends Abstract_Core_Model {

    protected $_table_name = 'catalog_tax';
    protected $_pk_field = 'record_id';

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['tax_value'])) {
            $data['tax_value'] = OSC::helper('catalog/common')->floatToInteger(floatval($data['tax_value']));
        }

        if (isset($data['exclude_product_type_ids'])) {
            $data['exclude_product_type_ids'] = OSC::encode(array_map('intval', $data['exclude_product_type_ids']));
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['tax_value'])) {
            $data['tax_value'] = OSC::helper('catalog/common')->integerToFloat(intval($data['tax_value']));
        }

        if (isset($data['exclude_product_type_ids'])) {
            $data['exclude_product_type_ids'] = OSC::decode($data['exclude_product_type_ids']);
        }
    }

    protected function _beforeSave() {
        parent::_beforeSave();
        $data = $this->_collectDataForSave();

        $errors = [];

        if (!isset($data['product_type_id'])) {
            $errors[] = 'Product type is empty';
        }

        if (!isset($data['departure_location_data'])) {
            $data['departure_location_data'] = '';
        }

        if (!isset($data['destination_location_data'])) {
            $errors[] = 'Destination country is empty';
        }

        if (isset($data['tax_value'])) {
            if ($data['tax_value'] < 0 || $data['tax_value'] > 100) {
                $errors[] = 'Tax value is between from 0 to 100';
            }
        }

        /*TODO Handle validate country or province have tax value*/

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
                    'destination_location_data' => 'Destination country is empty',
                    'tax_value' => 'Tax value is empty'
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

    public function getProductTypeTitle() {
        $title = '';

        try {
            $title = OSC::model('catalog/productType')->load($this->data['product_type_id'])->data['title'];
        } catch (Exception $ex) {
            //
        }

        return $title;
    }

    public function getTaxValue() {
        return $this->data['tax_value'] ?? 0;
    }

}
