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
class Model_Catalog_Order_Item extends Abstract_Core_Model {

    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';
    const PAYMENT_STATUS = [
        'pending' => 'Pending',
        'void' => 'Void',
        'authorized' => 'Authorized',
        'paid' => 'Paid',
        'partially_paid' => 'Partially paid',
        'partially_refunded' => 'Partially refunded',
        'refunded' => 'Refunded'
    ];

    const SYNC_AIRTABLE = [
        'NONE' => 0,
        'SYNCED' => 1
    ];

    protected $_table_name = 'catalog_order_item';
    protected $_pk_field = 'master_record_id';

    /**
     *
     * @var Model_Catalog_Order
     */
    protected $_order = null;

    /**
     *
     * @var Model_Catalog_Order_Item_Meta
     */
    protected $_order_item_meta = null;

    /**
     *
     * @var Model_Catalog_Product_Variant
     */
    protected $_variant = null;

    /**
     *
     * @var Model_Catalog_Product
     */
    protected $_product = null;

    /**
     *
     * @return $this->_order
     */
    public function getOrder() {
        if ($this->_order === null) {
            $this->_order = $this->getPreLoadedModel('catalog/order', $this->data['order_master_record_id']);
        }

        return $this->_order;
    }

    /**
     *
     * @param Model_Catalog_Order $order
     * @return $this
     */
    public function setOrder($order) {
        $this->_order = ($order instanceof Model_Catalog_Order) ? $order : null;
        return $this;
    }

    /**
     *
     * @param Model_Catalog_Order_Item_Meta $order_item_meta
     * @return $this
     */
    public function setOrderItemMeta($order_item_meta) {
        $this->_order_item_meta = ($order_item_meta instanceof Model_Catalog_Order_Item_Meta) ? $order_item_meta : null;
        return $this;
    }

    /**
     *
     * @return $this->_order
     */

    public function getOrderItemMeta() {
        if ($this->_order_item_meta === null) {
            $this->_order_item_meta = OSC::model('catalog/order_item_meta')->loadByMetaId(intval($this->data['order_item_meta_id']));
        }

        return $this->_order_item_meta;
    }


    public function getImageUrl() {
        if ($this->isCrossSellMode()) {
            return $this->data['image_url'];
        }
        return OSC::core('aws_s3')->getStorageUrl($this->data['image_url']);
    }

    public function getEditCustomizeUrl() {
        $skey = md5($this->getOrder()->getOrderUkey() . $this->getId());
        return OSC_FRONTEND_BASE_URL . '/catalog/frontend/editCustomize/skey/' . $skey . '/line-id/' . $this->getId();
    }

    /**
     *
     * @return $this->_product
     */
    public function getProduct() {
        if ($this->_product === null) {
            $this->_product = $this->getPreLoadedModel('catalog/product', $this->data['product_id']);
        }

        return $this->_product;
    }

    public function getAddonServices(): array
    {
        $data_addon_services = [];
        $list_addon_type_name = Model_Addon_Service::TYPE_NAME_ARR;
        if (isset($this->data['custom_price_data']['addon_services']) && is_array($this->data['custom_price_data']['addon_services']) && count($this->data['custom_price_data']['addon_services']) > 0) {
            $addon_service_ids = array_keys($this->data['custom_price_data']['addon_services']);
            $addon_service_collection = OSC::model('addon/service')->getCollection()
                ->addCondition('id', $addon_service_ids, OSC_Database::OPERATOR_IN)
                ->load();
            $data_addon_service_collection = [];
            foreach ($addon_service_collection as $addon_service) {
                $data_addon_service_collection[$addon_service->getId()]['title'] = $addon_service->data['title'];
            }
            foreach ($this->data['custom_price_data']['addon_services'] as $addon_service_id => $addon_services) {
                foreach ($addon_services as $ukey => $addon_service) {
                    $data_addon_services[$addon_service_id][$ukey] = $addon_service;
                    $data_addon_services[$addon_service_id][$ukey]['type'] = strtolower($list_addon_type_name[$addon_service['type']] ?? 'Single');
                    $data_addon_services[$addon_service_id]['title'] = $data_addon_service_collection[$addon_service_id]['title'] ?? '';
                }
            }
        }
        return $data_addon_services;
    }

    public function getAddonServicePrice($include_qty = true)
    {
        $addon_service_price = 0;
        $addon_services = $this->data['custom_price_data']['addon_services'] ?? [];
        if (count($addon_services)) {
            $pack = $this->getPackData();
            foreach ($addon_services as $addon_data) {
                try {
                    if (count($addon_data) > 0) {
                        foreach ($addon_data as $option_value) {
                            $option_price = isset($option_value['price']) && !empty($option_value['price']) ? intval($option_value['price']) : 0;
                            $addon_type = isset($option_value['type']) && !empty($option_value['type']) ? intval($option_value['type']) : 0;
                            if ($addon_type == Model_Addon_Service::TYPE_VARIANT) {
                                if ($pack) {
                                    //calculator discount pack
                                    if ($pack['discount_type'] == Model_Catalog_Product_Pack::FIXED_AMOUNT) {
                                        $option_price = max(0, $option_price * $pack['quantity'] - $pack['discount_value'] * 100);
                                    } elseif ($pack['discount_type'] == Model_Catalog_Product_Pack::PERCENTAGE) {
                                        $option_price = max(0, $option_price * $pack['quantity'] - ($option_price * $pack['quantity'] * $pack['discount_value'] / 100));
                                    }
                                }
                                $option_price = $option_price - $this->getPrice();
                            }
                            $addon_service_price += $include_qty ? $option_price * $this->data['quantity'] : $option_price;
                        }
                    }
                } catch (Exception $exception) {

                }
            }
        }
        return $addon_service_price;
    }

    /**
     *
     * @param Model_Catalog_Product $product
     * @return $this
     */
    public function setProduct($product) {
        $this->_product = ($product instanceof Model_Catalog_Product) ? $product : null;
        return $this;
    }

    /**
     *
     * @return $this->_variant
     */
    public function getVariant() {
        if ($this->_variant === null) {
            $this->_variant = $this->getPreLoadedModel('catalog/product_variant', $this->data['variant_id']);
        }

        return $this->_variant;
    }

    /**
     *
     * @param Model_Catalog_Product_Variant $variant
     * @return $this
     */
    public function setVariant($variant) {
        $this->_variant = ($variant instanceof Model_Catalog_Product_Variant) ? $variant : null;
        return $this;
    }

    public function getVariantTitle() {
        return $this->data['title'] . (count($this->data['options']) < 1 ? '' : (' - ' . $this->getVariantOptionsText()));
    }

    public function getVariantOptionsText() {
        return implode(' / ', array_map(function($option) {
                    return $option['value'];
                }, $this->data['options']));
    }

    public function getWeightInGram() {
        return OSC::helper('catalog/common')->getWeightInGram(intval($this->data['weight']), $this->data['weight_unit']);
    }

    public function getFloatCompareAtAmount() {
        return OSC::helper('catalog/common')->integerToFloat($this->getCompareAtAmount());
    }

    public function getFloatWeight() {
        return OSC::helper('catalog/common')->integerToFloat(intval($this->data['weight']));
    }

    public function getFloatPrice() {
        return OSC::helper('catalog/common')->integerToFloat(intval($this->data['price'] + $this->getAddonServicePrice(false)));
    }

    public function getFloatAmount() {
        return OSC::helper('catalog/common')->integerToFloat($this->getAmount());
    }

    public function getAmount() {
        return $this->data['quantity'] * ($this->data['price'] + $this->getAddonServicePrice(false));
    }

    public function getTaxValue() {
        $tax_order = $this->getOrder()->getTaxValue();
        $tax_value = intval($this->data['tax_value']);

        return $tax_value === 200 ? $tax_order : $tax_value;
    }

    public function getPriceWithDiscount(): int {
        $price = intval($this->data['price']);

        if ($this->data['discount'] && $this->data['discount']['discount_type'] == 'percent') {
            $price -= intval($this->data['discount']['discount_price']) / intval($this->data['quantity']);
        }

        return intval($price);
    }

    public function getDiscountPercent(): int {
        return ($this->data['discount'] && $this->data['discount']['discount_type'] == 'percent') ? round(intval($this->data['discount']['discount_price']) / $this->getAmount() * 100) : 0;

        return intval($price);
    }

    public function getFloatAmountWithDiscount() {
        return OSC::helper('catalog/common')->integerToFloat($this->getAmountWithDiscount());
    }

    public function getAmountWithDiscount(): int {
        $amount = $this->getAmount();

        $discount = $this->data['discount'];

        if ($discount) {
            $amount = max(0, $amount - $discount['discount_price']);
        }

        return $amount;
    }

    public function getFloatAmountWithDiscountByQty($quantity) {
        return OSC::helper('catalog/common')->integerToFloat($this->getAmountWithDiscountByQty($quantity));
    }

    public function getAmountWithDiscountByQty($quantity) {
        $amount = intval($quantity) * $this->data['price'];

        $discount = $this->data['discount'];

        if ($discount) {
            $amount = max(0, $amount - $discount['discount_price']);
        }

        return $amount;
    }

    public function getFulfillableQuantity() {
        return intval($this->data['quantity']) - intval($this->data['refunded_quantity']) - intval($this->data['fulfilled_quantity']) - intval($this->data['process_quantity']);
    }

    public function getPackTitle() {
        return $this->getPackData() ? $this->getPackData()['title'] : null;
    }

    public function getMarketingPointPackRate() {
        $pack_data = $this->getPackData();

        return $pack_data ? intval($pack_data['marketing_point_rate']) : 10000;
    }

    public function getPackData() {
        if (!isset($this->data['additional_data']['pack'])) {
            return null;
        }

        return $this->data['additional_data']['pack'];
    }

    public function getCrossSellDataIdx() {
        $order_item_data = $this->getOrderItemMeta();
        if (!isset($order_item_data->data['custom_data']) || !is_array($order_item_data->data['custom_data']) || count($order_item_data->data['custom_data']) < 1) {
            return null;
        }

        foreach ($order_item_data->data['custom_data'] as $idx => $custom_data_entry) {
            if ($custom_data_entry['key'] == '2dcrosssell') {
                return $idx;
            }
        }

        return null;
    }

    public function getCrossSellData() {
        $order_item_data = $this->getOrderItemMeta();
        $idx = $this->getCrossSellDataIdx();

        if ($idx === null) {
            return null;
        }

        return $order_item_data->data['custom_data'][$idx]['data'];
    }

    public function isCrossSellMode() {
        return $this->getCrossSellDataIdx() !== null;
    }

    public function isSemitestMode() {
        return $this->getSemitestDataIdx() !== null;
    }

    public function getSemitestDataIdx() {
        $order_item_data = $this->getOrderItemMeta();
        if (!isset($order_item_data->data['custom_data']) || !is_array($order_item_data->data['custom_data']) || count($order_item_data->data['custom_data']) < 1) {
            return null;
        }

        foreach ($order_item_data->data['custom_data'] as $idx => $custom_data_entry) {
            if ($custom_data_entry['type'] == 'semitest') {
                return $idx;
            }
        }

        return null;
    }

    public function getSemitestData() {
        $order_item_data = $this->getOrderItemMeta();
        $idx = $this->getSemitestDataIdx();

        if ($idx === null) {
            return null;
        }

        return $order_item_data->data['custom_data'][$idx]['data'];
    }

    public function getCampaignDataIdx() {
        $order_item_data = $this->getOrderItemMeta();
        if (!isset($order_item_data->data['custom_data']) || !is_array($order_item_data->data['custom_data']) || count($order_item_data->data['custom_data']) < 1) {
            return null;
        }

        foreach ($order_item_data->data['custom_data'] as $idx => $custom_data_entry) {
            if ($custom_data_entry['key'] == 'campaign') {
                return $idx;
            }
        }

        return null;
    }

    public function getCampaignData() {
        $idx = $this->getCampaignDataIdx();

        if ($idx === null) {
            return null;
        }
        $order_item_data = $this->getOrderItemMeta();
        return $order_item_data->data['custom_data'][$idx]['data'];
    }

    public function isCampaignMode() {
        return $this->getCampaignDataIdx() !== null;
    }

    public function getCampaignOrderLineItemMockupUrl() {
        if ($this->isResend()) {
            $design_urls = $this->data['design_url'];
            $is_use_design_item_resend = false;
            $id_item_resend = $this->data['additional_data']['resend']['item_id_duplicate'];

            if (count($design_urls) > 0) {
                foreach ($design_urls as $design_url) {
                    foreach ($design_url as $url_string) {
                        if (is_numeric(stripos($url_string, '/' . $id_item_resend . '/'))) {
                            $is_use_design_item_resend = true;
                            break;
                        }
                    }

                    if ($is_use_design_item_resend) {
                        break;
                    }
                }
            }

            if ($is_use_design_item_resend) {
                try {
                    $model_item_resend = OSC::model('catalog/order_item')->load($id_item_resend);
                    return OSC::getServiceUrlPersonalizedDesign() . '/storage/' . OSC::helper('catalog/campaign')->getOrderLineItemMockupFileName($model_item_resend);
                } catch (Exception $ex) {
                    return '';
                }
            }
        }

        return OSC::getServiceUrlPersonalizedDesign() . '/storage/' . OSC::helper('catalog/campaign')->getOrderLineItemMockupFileName($this);
    }

    public function isResend(): bool
    {
        $is_resend = $this->data['additional_data']['resend']['resend'];

        if (isset($is_resend)) {
            return true;
        }

        return false;
    }

    /**
     * 
     * @param int $quantity
     * @return $this
     * @throws Exception
     */
    public function incrementRefundedQuantity(int $quantity, $is_fulfilled = false) {
        if ($quantity <= 0) {
            throw new Exception('Refund quantity is incorrect');
        }

        if (!$is_fulfilled) {
            $query = <<<EOF
UPDATE {$this->getTableName(true)}
SET
    `refunded_quantity` = (`refunded_quantity` + {$quantity})
WHERE
    `master_record_id` = {$this->getId()} AND
    `quantity` >= (`refunded_quantity` + `fulfilled_quantity` + `process_quantity` + {$quantity})
LIMIT 1
EOF;
        } else {
            $query = <<<EOF
UPDATE {$this->getTableName(true)}
SET
    `refunded_quantity` = (`refunded_quantity` + {$quantity}),
    `fulfilled_quantity` = (`fulfilled_quantity` - {$quantity})
WHERE
    `master_record_id` = {$this->getId()} AND
    `quantity` >= (`refunded_quantity` + `fulfilled_quantity` + `process_quantity`)
LIMIT 1
EOF;
        }

        $this->getWriteAdapter()->query($query, null, 'increment_item_refund_quantity');

        if ($this->getWriteAdapter()->getNumAffected('increment_item_refund_quantity') < 1) {
            throw new Exception('Cannot update refunded quantity');
        }

        $this->reload();

        return $this;
    }

    /**
     * 
     * @param int $quantity
     * @return $this
     * @throws Exception
     */
    public function incrementFulfilledQuantity(int $quantity) {
        $quantity = intval($quantity);

        if ($quantity == 0) {
            throw new Exception('Fulfilled quantity is incorrect');
        }

        $query = <<<EOF
UPDATE {$this->getTableName(true)}
SET
    `fulfilled_quantity` = (`fulfilled_quantity` + {$quantity})
WHERE
    `master_record_id` = {$this->getId()} AND
    `quantity` >= (`refunded_quantity` + `fulfilled_quantity` + `process_quantity` + {$quantity})
LIMIT 1
EOF;

        $this->getWriteAdapter()->query($query, null, 'increment_item_fulfilled_quantity');

        if ($this->getWriteAdapter()->getNumAffected('increment_item_fulfilled_quantity') < 1) {
            throw new Exception('Cannot update fulfilled quantity');
        }

        $this->reload();

        return $this;
    }

    public function incrementProcessQuantity(int $quantity) {
        $quantity = intval($quantity);

        if ($quantity == 0) {
            throw new Exception('Process quantity is incorrect');
        }

        $query = <<<EOF
UPDATE {$this->getTableName(true)}
SET
    `process_quantity` = (`process_quantity` + {$quantity})
WHERE
    `master_record_id` = {$this->getId()} AND
    `quantity` >= (`refunded_quantity` + `fulfilled_quantity` + `process_quantity` + {$quantity})
LIMIT 1
EOF;

        $this->getWriteAdapter()->query($query, null, 'increment_item_process_quantity');

        if ($this->getWriteAdapter()->getNumAffected('increment_item_process_quantity') < 1) {
            throw new Exception('Cannot update process quantity');
        }

        $this->reload();

        return $this;
    }

    protected function _cleanCustomData($custom_data) {
        if (!is_array($custom_data)) {
            $custom_data = [];
        } else {
            foreach ($custom_data as $idx => $entry) {
                if (!is_array($entry) || !isset($entry['key']) || !isset($entry['data'])) {
                    unset($custom_data[$idx]);
                    continue;
                }

                foreach ($entry as $k => $v) {
                    if (!in_array($k, ['key', 'title', 'text', 'data','type'])) {
                        unset($entry[$k]);
                    }
                }

                if (!is_string($entry['key'])) {
                    unset($custom_data[$idx]);
                    continue;
                }

                if (!is_string($entry['title'])) {
                    unset($entry['title']);
                }

                if (!is_string($entry['text'])) {
                    unset($entry['text']);
                }
                if (!is_string($entry['type'])) {
                    unset($entry['type']);
                }
                $custom_data[$idx] = $entry;
            }

            $custom_data = array_values($custom_data);
        }

        return $custom_data;
    }

    public function getColorItem() {
        $color = null;

        foreach ($this->data['options'] as $option) {
            if ($option['title'] === 'Color') {
                $color = strtolower($option['value']);
            }
        }

        return $color;
    }

    public function isPackItem() {
        return $this->data['other_quantity'] > 0;
    }

    protected function _afterSave() {
        parent::_afterSave();

        if ($this->getLastActionFlag() == static::INSERT_FLAG) {
            $this->_updateData();
        }
    }

    /**
     * @throws Exception
     */
    protected function _beforeSave() {
//        if ($this->getActionFlag() != static::INSERT_FLAG) {
//            throw new Exception('Model not allowed to update');
//        }

        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['quantity'])) {
            $data['quantity'] = intval($data['quantity']);

            if ($data['quantity'] < 1) {
                $errors[] = 'Quantity is need greater than 0';
            }
        }

        if (isset($data['custom_data'])) {
            unset($data['custom_data']);
        }

        if (isset($data['additional_data'])) {
            if (!is_array($data['additional_data'])) {
                $data['additional_data'] = [];
            }
        }

        $is_cross = false;
        if (isset($data['additional_data']['is_cross_sell']) && $data['additional_data']['is_cross_sell'] == 1) {
            $is_cross = true;
        }

        if (isset($data['variant_id']) && !$is_cross) {
            $data['variant_id'] = intval($data['variant_id']);

            if ($data['variant_id'] < 1) {
                $errors[] = 'Variant ID is empty';
            } else {
                $variant = OSC_Database_Model::getPreLoadedModel('catalog/product_variant', $data['variant_id']);

                if (!($variant instanceof Model_Catalog_Product_Variant)) {
                    $errors[] = 'Variant is not exists';
                } else {
                    $data['product_id'] = $variant->getProduct()->getId();

                    if (!isset($data['image_url'])) {
                        $image = $variant->getImage();

                        if ($image) {
                            $image_path_local = OSC_Storage::getStoragePath($image->data['filename']);
                            $image_path_s3 = OSC::core('aws_s3')->getStoragePath($image->data['filename']);

                            $line_image_name = 'order/' . $data['product_id'] . '/' . preg_replace('/^.+\/([^\/]+)$/', '\\1', $image_path_local);
                            $line_image_path_local = OSC_Storage::getStoragePath($line_image_name);
                            $line_image_path_s3 = OSC::core('aws_s3')->getStoragePath($line_image_name);

                            if (!OSC::core('aws_s3')->doesObjectExist($line_image_path_s3)) {
                                if (OSC::core('aws_s3')->doesObjectExist($image_path_s3)) {
                                    try {
                                        /* Processing image in local and send to s3 */
                                        OSC::core('aws_s3')->download($image_path_s3, $line_image_path_local);

                                        $image_processor = new OSC_Image();
                                        $image_processor
                                            ->setImage($line_image_path_local)
                                            ->setJpgQuality(65)
                                            ->resize(250)
                                            ->save();

                                        OSC::core('aws_s3')->upload($line_image_path_local, $line_image_path_s3);

                                        $data['image_url'] = $line_image_name;
                                    } catch (Exception $ex) {

                                    }
                                }
                            } else {
                                $data['image_url'] = $line_image_name;
                            }
                        }
                    }
                }
            }
        }

        if (isset($data['fraud_risk_level'])) {
            unset($data['fraud_risk_level']);
        }

        if (isset($data['payment_data']['ba_token'])) {
            unset($data['payment_data']['ba_token']);
        }

        if (isset($data['payment_data']['ba_id'])) {
            unset($data['payment_data']['ba_id']);
        }

        if (isset($data['fraud_data'])) {
            if (!is_array($data['fraud_data']) || !isset($data['fraud_data']['score'])) {
                $data['fraud_data'] = null;
            } else {
                $data['fraud_data'] = [
                    'score' => intval($data['fraud_data']['score']),
                    'info' => isset($data['fraud_data']['info']) ? trim($data['fraud_data']['info']) : ''
                ];

                if ($data['fraud_data']['score'] < 0 || $data['fraud_data']['score'] > 100) {
                    $data['fraud_data'] = null;
                } else {
                    $matched_fraud_level_key = null;
                    $matched_fraud_level_info = null;

                    $fraud_levels = Model_Catalog_Order::FRAUD_RISK_LEVEL;

                    uasort($fraud_levels, function($a, $b) {
                        if ($a['score'] == $b['score']) {
                            return 0;
                        }

                        return ($a['score'] < $b['score']) ? -1 : 1;
                    });

                    foreach ($fraud_levels as $fraud_level_key => $fraud_level_info) {
                        if ($fraud_level_info['score'] <= $data['fraud_data']['score'] && ($matched_fraud_level_info === null || $matched_fraud_level_info['score'] < $fraud_level_info['score'])) {
                            $matched_fraud_level_key = $fraud_level_key;
                            $matched_fraud_level_info = $fraud_level_info;
                        }
                    }

                    $data['fraud_risk_level'] = $matched_fraud_level_key;
                }
            }

            $data['fraud_data'] = $data['fraud_data'] ?? null;
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
                if (isset($data['custom_data'])) {
                    $data['custom_data'] = $this->_cleanCustomData($data['custom_data']);
                }

                $require_fields = [
                    'title' => 'Title is empty',
                    'options' => 'Options is empty',
                    'price' => 'Variant price is empty',
                    'quantity' => 'Quantity is empty',
                    'require_shipping' => 'Require shipping is empty',
                    'require_packing' => 'Require packing is empty',
                    'keep_flat' => 'Keep flat is empty',
                    'weight' => 'Weight is empty',
                    'weight_unit' => 'Weight unit is empty',
                    'dimension_width' => 'Dimension width is empty',
                    'dimension_height' => 'Dimension height is empty',
                    'dimension_length' => 'Dimension length is empty'
                ];

                if (!$is_cross) {
                    $require_fields['product_id'] = 'Product ID is empty';
                    $require_fields['variant_id'] = 'Variant ID is empty';
                }

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $temporary_data = time();

                $default_fields = [
                    'master_ukey' => $temporary_data,
                    'item_id' => $temporary_data,
                    'shop_id' => OSC::getShop()->getId(),
                    'image_url' => null,
                    'sku' => '',
                    'vendor' => '',
                    'refunded_quantity' => 0,
                    'fulfilled_quantity' => 0,
                    'additional_data' => [],
                    'payment_data' => [],
                    'fraud_data' => [],
                    'payment_status' => '',
                    'discount' => [],
                    'design_url' => [],
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                if (count($errors) < 1) {
                    $data['ukey'] = $data['order_id'] . ':' . $data['variant_id'].':'.OSC::makeUniqid();
                }
            } else {
                unset($data['ukey']);
                unset($data['order_id']);
                unset($data['added_timestamp']);
                $data['modified_timestamp'] = time();
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

        foreach (['options', 'upsale_data', 'discount', 'additional_data', 'custom_price_data', 'payment_data', 'fraud_data', 'design_url'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['options', 'upsale_data', 'discount', 'additional_data', 'custom_price_data', 'payment_data', 'fraud_data', 'design_url'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }
    public function incrementQuantity(int $quantity) {
        $quantity = intval($quantity);

        if ($quantity == 0) {
            throw new Exception('quantity is incorrect');
        }

        $query = <<<EOF
UPDATE {$this->getTableName(true)}
SET
    `quantity` = (`quantity` + {$quantity})
WHERE
    `master_record_id` = {$this->getId()} AND
    `quantity` >= (`refunded_quantity` + `fulfilled_quantity` + `process_quantity` + {$quantity})
LIMIT 1
EOF;

        $this->getWriteAdapter()->query($query, null, 'increment_item_quantity');

        if ($this->getWriteAdapter()->getNumAffected('increment_item_quantity') < 1) {
            throw new Exception('Cannot update quantity');
        }

        $this->reload();

        return $this;
    }

    /**
     *
     * @param int $order_record_id
     * @return $this
     * @throws Exception
     */
    public function loadByOrderRecordId(int $order_master_record_id) {
        if ($order_master_record_id < 1) {
            throw new Exception('order master record id is empty');
        }

        return $this->setCondition(['condition' => '`order_master_record_id` = :order_master_record_id', 'params' => ['order_master_record_id' => $order_master_record_id]])->load();
    }

    /**
     *
     * @param int $shop_id
     * @param int $line_item_id
     * @return \Model_Catalog_Order_Item
     * @throws Exception
     */
    public function loadByItemId(int $line_item_id): Model_Catalog_Order_Item {
        if ($line_item_id < 1) {
            throw new Exception('Line Item ID is need greater than 0');
        }

        return $this->setCondition('shop_id = ' .   OSC::getShop()->getId() . ' AND item_id = ' . intval($line_item_id))->load();
    }

    public function getDesignUrL() {
        $urls = [];
        $design_urls = $this->data['design_url'];
        if (count($design_urls) > 0) {
            foreach ($design_urls as $design_url) {
                $urls[]  = $design_url['default'];
            }
        }
        return implode('; ', $urls);
    }

    public function isUpsaleItem(): bool {
        if (is_array($this->data['payment_data']) && !empty($this->data['payment_data']['authid'])) {
            return true;
        }

        return false;
    }

    public function getUpsalePriceData(): array {
        if (is_array($this->data['upsale_data']) && !empty($this->data['upsale_data']['price_data'])) {
            return $this->data['upsale_data']['price_data'];
        }

        return [];
    }

    protected function _updateData() {
        $this->getWriteAdapter()->update($this->getTableName(), ['item_id' => $this->getId(), 'master_ukey' => $this->data['shop_id'].':'.$this->getId()], 'master_record_id=' . $this->getId(), 1, 'update_data_item_'.$this->getId());

        $this->reload();
    }

    public function checkItemWaitDesign(): bool {
        $additional_data = $this->data['additional_data'];

        if (isset($additional_data['edit_design_change_product_type'])) {
            return true;
        }

        return false;
    }

    public function getProductTypeVariantId(){
        $campaign_data = $this->getCampaignData();

        return $campaign_data['product_type_variant_id'] ?? null;
    }

    public function hasSpecialCharacter() {

        try {

            if ($this->isSemitestMode()) {
                $custom_data = $this->getSemitestData();
            } else if ($this->getCampaignDataIdx()) {
                $custom_data = $this->getCampaignData();
            } else {
                $custom_data = [];
            }

            if (!isset($custom_data['print_template']['segment_source']) || !is_array($custom_data['print_template']['segment_source']) ) {

                return false;
            }

            foreach ($custom_data['print_template']['segment_source'] as $segment) {
                if (!array_key_exists('source', $segment) || !array_key_exists('config', $segment['source']) || !is_array($segment['source']['config'])) {
                    continue;
                }

                foreach ($segment['source']['config'] as $config) {
                    if (preg_match('/[^a-zA-Z0-9\`\~\!\@\#\$\%\^\&\*\(\)\-\_\=\+\{\[\}\]\\\|\:\;\"\'\,\<\.\>\/\?\s]/', $config)) {
                        return true;
                    }
                }
            }
        } catch (Exception $ex) {
            return false;
        }

        return false;

    }

    public function getPrice(): int
    {
        return intval($this->data['price']);
    }

    public function getRevenue($variant_price, $items_shipping_info) {

        $quantity = $this->data['other_quantity'] > 1 ?
            $this->data['quantity'] * $this->data['other_quantity'] :
            $this->data['quantity'];

        $item_sub_price = $variant_price * $quantity;

        // Calculate shipping price
        $cart_item_id = $this->data['additional_data']['cart_item_id'];
        $item_shipping_price = $items_shipping_info[$cart_item_id];

        // Calculate tax price
        $tax_value = intval($this->data['tax_value']);
        $item_tax_price = ($item_sub_price + $item_shipping_price) * $tax_value / 100;
        $item_tax_price = intval(round($item_tax_price));

        return $item_sub_price + $item_shipping_price + $item_tax_price;

    }

}