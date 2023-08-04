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
class Model_Catalog_Discount_Code extends Abstract_Core_Model {

    protected $_table_name = 'catalog_discount_code';
    protected $_pk_field = 'discount_code_id';
    protected $_ukey_field = 'discount_code';

    protected $_allow_write_log = true;
    protected $_member = null;

    const DISCOUNT_TYPES = [
        'percent' => 'Percentage',
        'fixed_amount' => 'Fixed amount',
    ];
    const APPLY_TYPES = [
        'entire_order' => 'Entire order',
        'entire_order_include_shipping' => 'Entire order include shipping',
        'shipping' => 'Shipping',
        'collection' => 'Specific collections',
        'product' => 'Specific products'
    ];
    const FIXED_AMOUNT_APPLY_TYPES = [
        'entire_order' => 'All Products',
        'collection' => 'Specific collections',
        'product' => 'Specific products'
    ];
    const CONDITION_TYPES = [
        'none' => 'None',
        'subtotal' => 'Minimum purchase amount',
        'quantity' => 'Minimum quantity of items'
    ];
    const FIXED_AMOUNT_CONDITION_TYPES = [
        'shipping' => 'Specific shipping'
    ];
    const CUSTOMER_LIMIT_TYPES = [
        'none' => 'Everyone',
//        'group' => 'Specific groups of customers',
        'customer' => 'Specific customers'
    ];

    const DISCOUNT_CODE_ERROR = 5002;

    public static function cleanUkey($ukey) {
        try {
            return static::cleanCode($ukey);
        } catch (Exception $ex) {
            return '';
        }
    }

    public static function cleanCode($code) {
        $code = preg_replace('/[^a-zA-Z0-9]/', '', $code);

        if (strlen($code) < 4 || strlen($code) > 20) {
            throw new Exception('Discount code need have from 4 to 20 characters');
        }

        return strtoupper($code);
    }

    /**
     * @return null|Model_User_Member
     * @throws OSC_Database_Model_Exception
     */
    public function getMember() {
        if ($this->_member === null) {
            try {
                if (isset($this->data['member_id']) && $this->data['member_id']) {
                    $this->_member = OSC::model('user/member')->load($this->data['member_id']);
                }
            } catch (Exception $ex) {

            }
        }

        return $this->_member;
    }

    public function getInfo($shorten = false) {
        $info = [];

        if ($this->data['discount_type'] == 'bxgy') {
            $info[] = 'Buy ' . $this->data['bxgy_prerequisite_quantity'] . ' item' . ($this->data['bxgy_prerequisite_quantity'] > 1 ? 's' : '') . ', get ' . $this->data['bxgy_entitled_quantity'] . ' item' . ($this->data['bxgy_entitled_quantity'] > 1 ? 's' : '') . ($this->data['bxgy_discount_rate'] == 100 ? ' free' : (' at ' . $this->data['bxgy_discount_rate'] . '% off'));

            if (!$shorten && $this->data['bxgy_allocation_limit'] > 0) {
                $info[] = $this->data['bxgy_allocation_limit'] . ' use' . ($this->data['bxgy_allocation_limit'] > 1 ? 's' : '') . ' per order';
            }
        } else if ($this->data['discount_type'] == 'free_shipping') {
            $info[] = 'Free shipping on entire order';

            if (count($this->data['prerequisite_country_code']) > 1) {
                $info[] = 'For ' . count($this->data['prerequisite_country_code']) . ' countries';
            } else if (count($this->data['prerequisite_country_code']) > 0) {
                $info[] = 'For ' . OSC::helper('core/country')->getCountryTitle($this->data['prerequisite_country_code'][0]);
            } else {
                $info[] = 'For all countries';
            }

            if ($this->data['prerequisite_shipping_rate'] > 0) {
                $info[] = 'Applies to shipping rates under ' . OSC::helper('catalog/common')->formatPrice($this->getFloatFreeShippingRateLimit(), 'email_without_currency');
            }
        } else {
            if ($this->data['discount_type'] == 'fixed_amount') {
                $prefix = OSC::helper('catalog/common')->formatPriceByInteger(intval($this->data['discount_value']), 'email_without_currency');
            } else {
                $prefix = $this->data['discount_value'] . '%';
            }

            if (count($this->data['prerequisite_product_id']) > 0) {
                if (count($this->data['prerequisite_product_id']) > 1) {
                    $info[] = $prefix . ' off ' . count($this->data['prerequisite_product_id']) . ' products';
                } else {
                    try {
                        /* @var $product Model_Catalog_Product */
                        $product = OSC::model('catalog/product')->load($this->data['prerequisite_product_id'][0]);

                        if (count($this->data['prerequisite_variant_id']) > 0) {
                            if (count($this->data['prerequisite_variant_id']) > 1) {
                                $info[] = $prefix . ' off ' . $product->getProductTitle() . ' (' . count($this->data['prerequisite_variant_id']) . ' variants)';
                            } else {
                                try {
                                    /* @var $variant Model_Catalog_Product_Variant */
                                    $variant = OSC::model('catalog/product_variant')->load($this->data['prerequisite_variant_id'][0]);
                                    $variant->setProduct($product);

                                    $info[] = $prefix . ' off ' . $product->getProductTitle() . ' (' . $variant->getTitle() . ')';
                                } catch (Exception $ex) {
                                    $info[] = $prefix . ' off ' . $product->getProductTitle() . ' (1 variant)';
                                }
                            }
                        } else {
                            $info[] = $prefix . ' off ' . $product->getProductTitle();
                        }
                    } catch (Exception $ex) {
                        $info[] = $prefix . ' off product id #' . $this->data['prerequisite_product_id'][0] . (count($this->data['prerequisite_variant_id']) > 0 ? (' (' . count($this->data['prerequisite_variant_id']) . ' variant' . (count($this->data['prerequisite_variant_id']) > 1 ? 's' : '') . ')') : '');
                    }
                }
            }
            if (count($this->data['prerequisite_collection_id']) > 0) {
                if (count($this->data['prerequisite_collection_id']) > 1) {
                    $info[] = $prefix . ' off ' . count($this->data['prerequisite_collection_id']) . ' collections';
                } else {
                    try {
                        $catalog_collection = OSC::model('catalog/collection')->load($this->data['prerequisite_collection_id'][0]);
                        $info[] = $prefix . ' off collection ' . $catalog_collection->data['title'];
                    } catch (Exception $ex) {
                        $info[] = $prefix . ' off collection id #' . $this->data['prerequisite_collection_id'][0];
                    }
                }
            }
            if ($this->data['prerequisite_shipping']) {
                $info[] = 'For ' . ucwords(str_replace('_', ' ', $this->data['prerequisite_shipping']));
            }
            if ($this->data['prerequisite_type'] === 'entire_order_include_shipping') {
                $info[] = $prefix . ' off entire order include shipping';
            } else if ($this->data['prerequisite_type'] === 'shipping') {
                $info[] = $prefix . ' off shipping';
            } else if ($this->data['prerequisite_type'] === 'entire_order') {
                $info[] = $prefix . ' off entire order';
            }
        }

        if ($this->data['prerequisite_subtotal'] > 0) {
            $info[] = 'Minimum purchase of ' . OSC::helper('catalog/common')->formatPrice($this->getFloatPrerequisiteSubtotal(), 'email_without_currency');
        } else if ($this->data['prerequisite_quantity'] > 0) {
            $info[] = 'Minimum purchase of ' . $this->data['prerequisite_quantity'] . ' item' . ($this->data['prerequisite_quantity'] > 1 ? 's' : '');
        }

        if (count($this->data['prerequisite_customer_id']) > 0) {
            if (count($this->data['prerequisite_customer_id']) > 1) {
                $info[] = 'For ' . count($this->data['prerequisite_customer_id']) . ' customers';
            } else {
                try {
                    $customer = OSC::helper('account/customer')->get(['customer_id' => $this->data['prerequisite_customer_id'][0]]);
                    $info[] = 'For ' . $customer['name'];
                } catch (Exception $ex) {
                    $info[] = 'For customer id #' . $this->data['prerequisite_customer_id'][0];
                }
            }
        } else if (count($this->data['prerequisite_customer_group']) > 0) {
            if (count($this->data['prerequisite_customer_group']) > 1) {
                $info[] = 'For ' . count($this->data['prerequisite_customer_group']) . ' groups of customers';
            } else {
                $group_name = $this->data['prerequisite_customer_group'][0];

                foreach (OSC::helper('catalog/common')->collectCustomerGroup() as $group_key => $group_data) {
                    if ($group_key == $group_name) {
                        $group_name = $group_data['title'];
                        break;
                    }
                }

                $info[] = 'For group ' . $group_name;
            }
        }

        if (!$shorten && $this->data['usage_limit'] > 0) {
            $info[] = 'Limit of ' . $this->data['usage_limit'] . ' use' . ($this->data['usage_limit'] > 1 ? 's' : '');
        }

        if ($this->data['once_per_customer'] == 1) {
            $info[] = 'One use per customer';
        }

        if ($this->data['auto_apply'] == 1) {
            $info[] = 'Auto apply';
        }

        return $info;
    }

    public function getFloatPrerequisiteSubtotal() {
        return OSC::helper('catalog/common')->integerToFloat(intval($this->data['prerequisite_subtotal']));
    }

    public function getFloatFreeShippingRateLimit() {
        return OSC::helper('catalog/common')->integerToFloat(intval($this->data['prerequisite_shipping_rate']));
    }

    public function verifyProduct($data, $map) {
        $_data = [];

        if (isset($data[$map['collection']]) && is_array($data[$map['collection']]) && count($data[$map['collection']]) > 0) {
            $data[$map['collection']] = array_map(function($collection_id) {
                return intval($collection_id);
            }, $data[$map['collection']]);
            $data[$map['collection']] = array_filter($data[$map['collection']], function($collection_id) {
                return $collection_id > 0;
            });

            if (count($data[$map['collection']]) > 0) {
                $data[$map['collection']] = array_unique($data[$map['collection']]);

                $buff = [];

                foreach (OSC::model('catalog/collection')->getCollection()->load($data[$map['collection']]) as $catalog_collection) {
                    $buff[] = $catalog_collection->getId();
                }

                if (count($buff) > 0) {
                    $_data[$map['collection']] = $buff;
                }
            }
        }
        if (isset($data[$map['product']]) && is_array($data[$map['product']]) && count($data[$map['product']]) > 0) {
            $variant_ids = [];
            $product_ids = [];

            foreach ($data[$map['product']] as $id) {
                $id = explode(':', $id, 2);

                foreach ($id as $k => $v) {
                    $id[$k] = intval($v);

                    if ($id[$k] < 1) {
                        goto skip;
                    }
                }

                if (count($id) == 2) {
                    $variant_ids[$id[1]] = $id[0];
                } else {
                    $product_ids[$id[0]] = 1;
                }

                skip:
            }

            foreach ($variant_ids as $product_id) {
                unset($product_ids[$product_id]);
            }

            $variant_ids = array_keys($variant_ids);

            if (count($variant_ids) > 0) {
                $buff = [];

                foreach (OSC::model('catalog/product_variant')->getCollection()->load($variant_ids) as $variant) {
                    unset($product_ids[$variant->data['product_id']]);
                    $buff[$variant->data['product_id'] . ':' . $variant->getId()] = $variant->data['product_id'];
                }

                $variant_ids = $buff;
            }

            $product_ids = array_keys($product_ids);

            if (count($product_ids) > 0) {
                $buff = [];

                foreach (OSC::model('catalog/product')->getCollection()->load($product_ids) as $product) {
                    $buff[] = $product->getId();
                }

                $product_ids = $buff;
            }

            foreach ($variant_ids as $product_id) {
                $product_ids[] = $product_id;
            }

            $product_ids = array_unique($product_ids);
            $variant_ids = array_keys($variant_ids);

            if (count($product_ids) > 0) {
                $_data[$map['product']] = $product_ids;
            }

            if (count($variant_ids) > 0) {
                $_data[$map['variant']] = $variant_ids;
            }
        }

        return $_data;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        $discount_type_default_data = [
            'discount_value' => 0,
            'prerequisite_type' => 'entire_order',
            'prerequisite_product_id' => [],
            'prerequisite_variant_id' => [],
            'prerequisite_collection_id' => [],
            'prerequisite_country_code' => [],
            'prerequisite_shipping_rate' => 0,
            'prerequisite_subtotal' => 0,
            'prerequisite_quantity' => 0,
            'entitled_product_id' => [],
            'entitled_variant_id' => [],
            'entitled_collection_id' => [],
            'bxgy_prerequisite_quantity' => 0,
            'bxgy_entitled_quantity' => 0,
            'bxgy_discount_rate' => 0,
            'bxgy_allocation_limit' => 0
        ];

        if (isset($data['discount_type'])) {
            $data['discount_type'] = trim($data['discount_type']);

            if (!array_key_exists($data['discount_type'], static::DISCOUNT_TYPES)) {
                $errors[] = 'Discount type is not exists';
            } else {
                $discount_type_data = [];

                if ($data['discount_type'] == 'percent') {
                    if (isset($data['discount_value'])) {
                        $discount_type_data['discount_value'] = intval($data['discount_value']);

                        if ($discount_type_data['discount_value'] < 1) {
                            $discount_type_data['discount_value'] = 1;
                        } else if ($discount_type_data['discount_value'] > 100) {
                            $discount_type_data['discount_value'] = 100;
                        }
                    } else {
                        $errors[] = 'Please enter percentage will be discount after apply the code';
                    }
                } else if ($data['discount_type'] == 'fixed_amount') {
                    if (isset($data['discount_value'])) {
                        $discount_type_data['discount_value'] = OSC::helper('catalog/common')->floatToInteger(floatval($data['discount_value']));

                        if ($discount_type_data['discount_value'] <= 0) {
                            $errors[] = 'Please enter amount will be discount after apply the code';
                        }
                    } else {
                        $errors[] = 'Please enter amount will be discount after apply the code';
                    }
                } else if ($data['discount_type'] == 'free_shipping') {
                    if (isset($data['prerequisite_country_code'])) {
                        $countries = OSC::helper('core/country')->getCountries();

                        $discount_type_data['prerequisite_country_code'] = array_map(function($country_code) {
                            return strtoupper(preg_replace('/[^a-zA-Z]/', '', $country_code));
                        }, $data['prerequisite_country_code']);
                        $discount_type_data['prerequisite_country_code'] = array_filter($discount_type_data['prerequisite_country_code'], function($country_code) use($countries) {
                            return strlen($country_code) == 2 && isset($countries[$country_code]);
                        });
                        $discount_type_data['prerequisite_country_code'] = array_unique($discount_type_data['prerequisite_country_code']);
                    }

                    if (isset($data['prerequisite_shipping_rate'])) {
                        $discount_type_data['prerequisite_shipping_rate'] = OSC::helper('catalog/common')->floatToInteger(floatval($data['prerequisite_shipping_rate']));

                        if ($discount_type_data['prerequisite_shipping_rate'] < 0) {
                            $discount_type_data['prerequisite_shipping_rate'] = 0;
                        }
                    }
                }

                if ($data['discount_type'] == 'bxgy') {
                    $discount_type_data = array_merge($discount_type_data, $this->verifyProduct($data, ['product' => 'entitled_product_id', 'variant' => 'entitled_variant_id', 'collection' => 'entitled_collection_id']));

                    foreach (['bxgy_prerequisite_quantity', 'bxgy_entitled_quantity'] as $key) {
                        if (isset($data[$key])) {
                            $discount_type_data[$key] = intval($data[$key]);

                            if ($discount_type_data[$key] < 1) {
                                $discount_type_data[$key] = 1;
                            }
                        }
                    }

                    if (isset($data['bxgy_discount_rate'])) {
                        $discount_type_data['bxgy_discount_rate'] = intval($data['bxgy_discount_rate']);

                        if ($discount_type_data['bxgy_discount_rate'] < 1) {
                            $discount_type_data['bxgy_discount_rate'] = 1;
                        } else if ($discount_type_data['bxgy_discount_rate'] > 100) {
                            $discount_type_data['bxgy_discount_rate'] = 100;
                        }
                    }

                    if (isset($data['bxgy_allocation_limit'])) {
                        $discount_type_data['bxgy_allocation_limit'] = intval($data['bxgy_allocation_limit']);

                        if ($discount_type_data['bxgy_allocation_limit'] < 0) {
                            $discount_type_data['bxgy_allocation_limit'] = 0;
                        }
                    }
                }

                if (in_array($data['discount_type'], ['bxgy', 'percent', 'fixed_amount'], true)) {
                    $discount_type_data = array_merge($discount_type_data, $this->verifyProduct($data, ['product' => 'prerequisite_product_id', 'variant' => 'prerequisite_variant_id', 'collection' => 'prerequisite_collection_id']));
                }

                if (in_array($data['discount_type'], ['free_shipping', 'percent', 'fixed_amount'], true)) {
                    if (isset($data['prerequisite_quantity'])) {
                        $discount_type_data['prerequisite_quantity'] = intval($data['prerequisite_quantity']);

                        if ($discount_type_data['prerequisite_quantity'] < 0) {
                            $discount_type_data['prerequisite_quantity'] = 0;
                        }
                    }

                    if (isset($data['prerequisite_subtotal'])) {
                        $discount_type_data['prerequisite_subtotal'] = OSC::helper('catalog/common')->floatToInteger(floatval($data['prerequisite_subtotal']));

                        if ($discount_type_data['prerequisite_subtotal'] < 0) {
                            $discount_type_data['prerequisite_subtotal'] = 0;
                        }
                    }
                    if (isset($data['prerequisite_type'])) {
                        $discount_type_data['prerequisite_type'] = $data['prerequisite_type'];
                    }
                }

                foreach ($discount_type_default_data as $key => $default_value) {
                    $data[$key] = isset($discount_type_data[$key]) ? $discount_type_data[$key] : $default_value;
                }

                if ($data['discount_type'] == 'bxgy') {
                    if (count($data['prerequisite_product_id']) < 1 && count($data['prerequisite_variant_id']) < 1 && count($data['prerequisite_collection_id']) < 1) {
                        $errors[] = 'Please select a prerequisite product/collection';
                    }

                    if (count($data['entitled_product_id']) < 1 && count($data['entitled_variant_id']) < 1 && count($data['entitled_collection_id']) < 1) {
                        $errors[] = 'Please select a entitled product/collection';
                    }
                }
            }
        } else {
            foreach ($discount_type_default_data as $key => $default_value) {
                unset($data[$key]);
            }
        }

        if (isset($data['prerequisite_customer_id'])) {
            if (!is_array($data['prerequisite_customer_id'])) {
                $data['prerequisite_customer_id'] = [];
            } else if (count($data['prerequisite_customer_id']) > 0) {
                $data['prerequisite_customer_group'] = [];

                $data['prerequisite_customer_id'] = array_map(function($customer_id) {
                    return intval($customer_id);
                }, $data['prerequisite_customer_id']);
                $data['prerequisite_customer_id'] = array_filter($data['prerequisite_customer_id'], function($customer_id) {
                    return $customer_id > 0;
                });

                if (count($data['prerequisite_customer_id']) > 0) {
                    $buff = [];
                    try {
                        $list_customers = OSC::helper('account/customer')->getCustomerByListIds($data['prerequisite_customer_id']);
                    } catch (Exception $ex) {
                        $list_customers = [];
                    }
                    if (count($list_customers) > 0) {
                        foreach ($list_customers as $customer) {
                            $buff[] = $customer['customer_id'];
                        }
                    }

                    $data['prerequisite_customer_id'] = $buff;
                }
            }
        }

        if (isset($data['prerequisite_customer_group'])) {
            if (!is_array($data['prerequisite_customer_group'])) {
                $data['prerequisite_customer_group'] = [];
            } else if (count($data['prerequisite_customer_group']) > 0) {
                $data['prerequisite_customer_id'] = [];

                $buff = [];

                foreach (OSC::helper('catalog/common')->collectCustomerGroup() as $group_key => $group_data) {
                    if (in_array($group_key, $data['prerequisite_customer_group'], true)) {
                        $buff[] = $group_key;
                    }
                }

                $data['prerequisite_customer_group'] = $buff;
            }
        }

        if (isset($data['discount_code'])) {
            try {
                $data['discount_code'] = static::cleanCode($data['discount_code']);
            } catch (Exception $ex) {
                $errors[] = $ex->getMessage();
            }
        }

        foreach (['added_timestamp', 'modified_timestamp', 'active_timestamp', 'deactive_timestamp', 'usage_limit', 'usage_counter'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (isset($data['maximum_amount'])) {
            $data['maximum_amount'] = floatval($data['maximum_amount']);
        }

        foreach (['auto_apply', 'once_per_customer', 'combine_flag', 'apply_across', 'auto_generated'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]) == 1 ? 1 : 0;
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'discount_code' => 'Discount code is empty',
                    'discount_type' => 'Discount type is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'auto_generated' => 0,
                    'prerequisite_customer_group' => [],
                    'prerequisite_customer_id' => [],
                    'usage_limit' => 0,
                    'usage_counter' => 0,
                    'once_per_customer' => 0,
                    'auto_apply' => 0,
                    'combine_flag' => 0,
                    'active_timestamp' => 0,
                    'deactive_timestamp' => 0,
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

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['prerequisite_product_id', 'prerequisite_variant_id', 'prerequisite_collection_id', 'prerequisite_country_code', 'entitled_product_id', 'entitled_variant_id', 'entitled_collection_id', 'prerequisite_customer_id', 'prerequisite_customer_group'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = implode(',', $data[$key]);
            }
        }

        $data['maximum_amount'] = OSC::helper('catalog/common')->floatToInteger(floatval($data['maximum_amount']));
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['prerequisite_product_id', 'prerequisite_variant_id', 'prerequisite_collection_id', 'prerequisite_country_code', 'entitled_product_id', 'entitled_variant_id', 'entitled_collection_id', 'prerequisite_customer_id', 'prerequisite_customer_group'] as $key) {
            if (isset($data[$key])) {
                if ($data[$key] == '') {
                    $data[$key] = [];
                } else {
                    $data[$key] = explode(',', $data[$key]);
                }
            }
        }

        $data['maximum_amount'] = OSC::helper('catalog/common')->integerToFloat(floatval($data['maximum_amount']));
    }

}
