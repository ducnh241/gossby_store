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
class Model_Catalog_Cart_Item extends Abstract_Core_Model {

    protected $_table_name = 'catalog_cart_item';
    protected $_pk_field = 'item_id';

    protected $_json_fields = ['custom_data', 'price_data', 'additional_data', 'custom_price_data', 'addon_services'];

    protected $_ukey_editable = true; // For edit product type variant in cart item

    /**
     *
     * @var Model_Catalog_Cart
     */
    protected $_cart_model = null;

    /**
     *
     * @var Model_Catalog_Product
     */
    protected $_product_model = null;

    /**
     *
     * @var Model_Catalog_Product_Variant
     */
    protected $_variant_model = null;

    public function addDiscount($discount_code, $discount_price, $discount_type) {
        $this->register('discount', [
            'discount_code' => $discount_code,
            'discount_price' => intval($discount_price),
            'discount_type' => $discount_type
        ]);
    }

    public function removeDiscount() {
        $this->register('discount', []);
    }

    public function getDiscount() {
        return $this->registry('discount');
    }

    public function getFloatWeight() {
        return OSC::helper('catalog/common')->integerToFloat(intval($this->data['weight']));
    }

    public function getWeightInGram() {
        return OSC::helper('catalog/common')->getWeightInGram($this->data['weight'], $this->data['weight_unit']);
    }

    public function getFloatCompareAtAmount() {
        return OSC::helper('catalog/common')->integerToFloat($this->getCompareAtAmount());
    }

    public function getCompareAtAmount() {
        return $this->data['quantity'] * $this->data['compare_at_price'];
    }

    public function getFloatPrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->getPrice());
    }

    public function getPrice() {
        return intval($this->data['price']);
    }

    public function getFloatAmount() {
        return OSC::helper('catalog/common')->integerToFloat($this->getAmount());
    }

    public function getAmount() {
        return $this->data['quantity'] * $this->data['price'];
    }

    public function isSemiTest() {
        if (!isset($this->data['custom_data']) || !is_array($this->data['custom_data']) || count($this->data['custom_data']) < 1) {
            return true;
        }

        foreach ($this->data['custom_data'] as $custom_data_entry) {
            if ($custom_data_entry['type'] == 'semitest') {
                return true;
            }
        }

        return false;
    }


    public function getCrossSellDataIdx() {
        if (!isset($this->data['custom_data']) || !is_array($this->data['custom_data']) || count($this->data['custom_data']) < 1) {
            return null;
        }

        foreach ($this->data['custom_data'] as $idx => $custom_data_entry) {
            if ($custom_data_entry['key'] == '2dcrosssell') {
                return $idx;
            }
        }

        return null;
    }

    public function getCrossSellData() {
        $idx = $this->getCrossSellDataIdx();

        if ($idx === null) {
            return null;
        }

        return $this->data['custom_data'][$idx]['data'];
    }

    public function isCrossSellMode() {
        return $this->getCrossSellDataIdx() !== null;
    }

    public function getCrossSellDesignId() {
        $data = $this->getCrossSellData();

        if ($data) {
            return $data['print_template']['segment_source']['front']['source']['design_id'];
        }

        return null;
    }

    /**
     *
     * @var Model_Catalog_ProductType_Variant
     */
    protected $_product_type_variant_model = null;

    public function getProductTypeVariant() {
        if ($this->_product_type_variant_model == null && $this->getProductTypeVariantId() > 0) {
            $this->_product_type_variant_model = OSC::model('catalog/productType_variant')->load($this->getProductTypeVariantId());
        }
        return $this->_product_type_variant_model;
    }

    /**
     *
     * @var Model_Catalog_ProductType
     */
    protected $_product_type_model = null;

    public function getProductType() {
        $product_type_variant = $this->getProductTypeVariant();
        if ($this->_product_type_model == null && $product_type_variant instanceof Model_Catalog_ProductType_Variant && $product_type_variant->getId() > 0) {
            $this->_product_type_model = $product_type_variant->getProductType();
        }
        return $this->_product_type_model;
    }

    /**
     * @return int
     */
    public function getProductTypeVariantId() {
        if ($this->isCampaignMode()) {
            $product_type_variant_id = intval($this->getCampaignData()['product_type_variant_id']);
        } else if ($this->isCrossSellMode()) {
            $product_type_variant_id = intval($this->getCrossSellData()['product_type_variant_id']);
        } else {
            /*TODO Handle*/
            $product_type_variant_id = 0;
        }

        return $product_type_variant_id;
    }

    public function getProductTypeVariantTitle() {
        $title = '';
        if ($this->isCampaignMode()) {
            $title = $this->getCampaignData()['product_type']['title'] ?? '';
        } else if ($this->isCrossSellMode()) {
            $title = $this->getCrossSellData()['product_type']['title'] ?? '';
        }

        return $title;
    }

    public function getTaxValue() {
        return $this->data['tax_value'];
    }

    public function getTaxAmount() {
        $amount = $this->getAmount();
        $tax_value = $this->getTaxValue() ?? 0;

        return $amount * $tax_value / 100;
    }

    public function getFloatAmountWithDiscount() {
        return OSC::helper('catalog/common')->integerToFloat($this->getAmountWithDiscount());
    }

    public function getAmountWithDiscount($include_addon = false) {
        $amount = $this->getAmount();
        if ($include_addon) {
            $amount += $this->getAddonServicePrice();
        }

        $discount = $this->getDiscount();

        if ($discount) {
            $amount = max(0, $amount - $discount['discount_price']);
        }

        return $amount;
    }

    public function getTaxAmountWithDiscount() {
        $amount = $this->getAmountWithDiscount();
        $tax_value = $this->getTaxValue() ?? 0;

        return $amount * $tax_value / 100;
    }

    public function getTaxAmountAddonService()
    {
        $amount = $this->getAddonServicePrice();
        $tax_value = $this->getTaxValue() ?? 0;

        return $amount * $tax_value / 100;
    }

    /**
     * 
     * @return Model_Catalog_Cart
     */
    public function getCart() {
        if ($this->_cart_model === null) {
            $this->_cart_model = static::getPreLoadedModel('catalog/cart', $this->data['cart_id']);
        }

        return $this->_cart_model;
    }

    /**
     * 
     * @param Model_Catalog_Cart $cart
     * @return $this
     */
    public function setCart($cart) {
        $this->_cart_model = ($cart instanceof Model_Catalog_Cart) ? $cart : null;
        return $this;
    }

    /**
     * 
     * @param boolean $reload
     * @return $this->_variant_model
     */
    public function getVariant($reload = false) {
        if ($reload || !($this->_variant_model instanceof Model_Catalog_Product_Variant)) {
            $this->_variant_model = static::getPreLoadedModel('catalog/product_variant', $this->data['variant_id']);
        }

        return $this->_variant_model;
    }

    /**
     * 
     * @param mixed $variant
     * @return $this
     */
    public function setVariant($variant = null) {
        $this->_variant_model = ($variant instanceof Model_Catalog_Product_Variant) ? $variant : null;
        return $this;
    }

    /**
     * 
     * @param boolean $reload
     * @return $this->_product_model
     */
    public function getProduct($reload = false) {
        if ($reload || !($this->_product_model instanceof Model_Catalog_Product)) {
            $this->_product_model = static::getPreLoadedModel('catalog/product', $this->data['product_id']);
        }

        return $this->_product_model;
    }

    /**
     * 
     * @param mixed $product
     * @return $this
     */
    public function setProduct($product = null) {
        $this->_product_model = ($product instanceof Model_Catalog_Product) ? $product : null;
        return $this;
    }

    public function getCampaignData() {
        if (!isset($this->data['custom_data']) || !is_array($this->data['custom_data']) || count($this->data['custom_data']) < 1) {
            return null;
        }

        foreach ($this->data['custom_data'] as $custom_data_entry) {
            if ($custom_data_entry['key'] == 'campaign') {
                $data = $custom_data_entry['data'];
                if (isset($data['print_template']['preview_config']) && !empty($data['print_template']['preview_config']) && isset($data['product_type']['options']) && !empty($data['product_type']['options'])) {
                    OSC::helper('catalog/campaign')->replaceLayerUrl($data['print_template']['preview_config'], $data['product_type']['options']['keys']);
                }

                return $data;
            }
        }

        return null;
    }

    public function getPackData() {
        if (!isset($this->data['additional_data']['pack'])) {
            return null;
        }

        return $this->data['additional_data']['pack'];
    }

    public function getOptions() {
        $campaign_data = $this->getCampaignData();

        $options = [];

        foreach ($campaign_data['options'] as $option_key => $option) {
            $options[$option_key] = $option['value']['key'];
        }

        return $options;
    }

    public function getColorOption() {
        $options = $this->getOptions();

        return $options['color'] ?? null;
    }

    public function isCampaignMode() {
        return $this->getCampaignData() !== null;
    }

    public function isAvailableToOrder(&$used_supplier_variant = []) {
        if ($this->isCampaignMode() || $this->isCrossSellMode()) {
            $location = OSC::helper('catalog/common')->getCustomerShippingLocation();
            $country_code = $location['country_code'];
            $province_code = $location['province_code'];
            $product_type_variant_id = $this->getProductTypeVariantId();
            $is_selling_variant_in_country = null;

            foreach ($used_supplier_variant as $supplier_variant) {
                if (isset($supplier_variant['product_type_variant_id']) &&
                    isset($supplier_variant['country_code']) &&
                    isset($supplier_variant['province_code']) &&
                    $supplier_variant['product_type_variant_id'] === $product_type_variant_id &&
                    $supplier_variant['country_code'] === $country_code &&
                    $supplier_variant['province_code'] === $province_code) {
                    $is_selling_variant_in_country = $supplier_variant['is_selling_variant_in_country'];
                    break;
                }
            }

            if (!is_null($is_selling_variant_in_country)) {
                return $is_selling_variant_in_country;
            }

            $is_selling_variant_in_country = OSC::helper('supplier/location')->isSellingVariantInCountry(
                $product_type_variant_id,
                $country_code,
                $province_code
            );

            $used_supplier_variant[] = [
                'product_type_variant_id' => $product_type_variant_id,
                'country_code' => $country_code,
                'province_code' => $province_code,
                'is_selling_variant_in_country' => $is_selling_variant_in_country
            ];

            return $is_selling_variant_in_country;
        }

        return true;
    }

    public function getAddonServices($exclude_expired = false, $options = []): array
    {
        $options['has_pack'] = (bool)$this->getPackData();

        $list_addon_service = OSC::helper('catalog/cart')->getAddonServiceDataOfCartItem($this);
        $list_addon_service_expired = $list_addon_service['addon_service_expired'] ?? [];
        $list_addon_service_unavailable = $list_addon_service['addon_service_unavailable'] ?? [];

        $item_addon_services = (isset($this->data['custom_price_data']['addon_services'])) ? $this->data['custom_price_data']['addon_services'] : null;

        $update_custom_price_data = false;
        $addon_service_remove_keys = [];
        if ($item_addon_services) {
            $item_addon_service_ids = array_keys($item_addon_services);
            $data_addon_service_ids = array_column($list_addon_service['data_addon_service'], 'id');

            foreach ($item_addon_services as $key => $addon) {
                if (!in_array($key, $data_addon_service_ids)) {
                    $addon_service_remove_keys[] = $key;
                    unset($item_addon_services[$key]);
                }

                // Validate A/B test status of addon service
                foreach ($addon as $option_key => $addon_value) {
                    if (!isset($list_addon_service['data_addon_service']['_' . $key]['options'][$option_key])) {
                        $addon_service_remove_keys[] = $key;
                        $update_custom_price_data = true;

                        unset($item_addon_services[$key]);
                    }
                }
            }

            // Check for update custom_price_data['addon_service']
            if (count($item_addon_service_ids) !== count($data_addon_service_ids) ||
                array_diff($item_addon_service_ids, $data_addon_service_ids) !== array_diff($data_addon_service_ids, $item_addon_service_ids)) {
                $update_custom_price_data = true;
            }
        }

        foreach ($list_addon_service_unavailable as $key => $addon_service_unavailable) {
            if (isset($list_addon_service['addon_service_unavailable'][$key]) && !in_array($addon_service_unavailable['id'], $addon_service_remove_keys)) {
                unset($list_addon_service['addon_service_unavailable'][$key]);
            }
        }

        foreach ($list_addon_service_expired as $key => $addon_service_expired) {
            if (isset($list_addon_service['addon_service_expired'][$key]) && !in_array($addon_service_expired['id'], $addon_service_remove_keys)) {
                unset($list_addon_service['addon_service_expired'][$key]);
            }
        }

        // Hard update custom_price_data when addon_services has changed
        if ($update_custom_price_data) {
            $custom_price_data = $this->data['custom_price_data'];

            if (count($item_addon_services) > 0) {
                $custom_price_data['addon_services'] = $item_addon_services;
            } else {
                // Remove key addon_services in custom_price_data if all addon_service of this item are unavailable
                unset($custom_price_data['addon_services']);
            }

            $this->setData(['custom_price_data' => $custom_price_data])->save();
            $this->reload();

            //Update cart custom_price_data
            // TODO: fix n+1 query
            $cart = OSC::model('catalog/cart')->load($this->data['cart_id']);
            $cartCustomPriceData = OSC::helper('addon/service')->updateCartCustomPriceData($cart);
            $cart->setData([
                'custom_price_data' => $cartCustomPriceData
            ])->save();
            $cart->reload();
        }

        //Giu nguyen gia addon cua nhung item da add cart, khi addon thay doi gia
        foreach ($item_addon_services as $key_addon_id => $item_addon_service) {
            foreach ($item_addon_service as $ukey_addon_selected => $item_addon_selected) {
                if (isset($list_addon_service['data_addon_service']['_' . $key_addon_id]['options'][$ukey_addon_selected]['price']) && $item_addon_selected['price']) {
                    $list_addon_service['data_addon_service']['_' . $key_addon_id]['options'][$ukey_addon_selected]['price'] = $item_addon_selected['price'];
                    $list_addon_service['data_addon_service']['_' . $key_addon_id]['options'][$ukey_addon_selected]['flag_change_price'] = true;
                }
            }
        }

        return [
            'configs' => $list_addon_service,
            'selected' => $item_addon_services,
            'total_price' => $this->getAddonServicePrice(true, $exclude_expired)
        ];
    }

    public function getAddonServicePrice($include_qty = true, $exclude_expired = true)
    {
        $addon_service_price = 0;
        $addon_services = $this->data['custom_price_data']['addon_services'] ?? [];
        if (count($addon_services)) {
            $pack = $this->getPackData();
            $product_addon_services = OSC::helper('catalog/cart')->getAddonServiceDataOfCartItem($this);
            $addon_service_expired_ids = array_column($product_addon_services['addon_service_expired'] ?? [], 'id');
            $addon_service_unavailable_ids = array_column($product_addon_services['addon_service_unavailable'] ?? [], 'id');
            foreach ($addon_services as $addon_id => $addon_data) {
                try {
                    if (in_array($addon_id, $addon_service_expired_ids) || in_array($addon_id, $addon_service_unavailable_ids)) {
                        continue;
                    }

                    if (count($addon_data) > 0) {
                        $addon_model = OSC::model('addon/service')->load($addon_id);
                        $addon_type = $addon_model->data['type'];
                        foreach ($addon_data as $option_value) {
                            $option_price = isset($option_value['price']) && !empty($option_value['price']) ? intval($option_value['price']) : 0;
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
                } catch (Exception $exception) {}
            }
        }
        return $addon_service_price;
    }

    public function checkShippingByCountry($country_code = '', $province_code = '') {
        if ($this->isCampaignMode() || $this->isCrossSellMode()) {
            return OSC::helper('supplier/location')->isSellingVariantInCountry(
                $this->getProductTypeVariantId(),
                $country_code,
                $province_code
            );
        }

        return true;
    }

    protected function _getCampaignSku($product = null) {
        if (!($product instanceof Model_Catalog_Product)) {
            $product = $this->getPreLoadedModel('catalog/product', $this->getData('product_id'));
        }

        $campaign_data = $this->getCampaignData();

        return implode('/', array_filter([
            $product->data['sku'],
            $campaign_data['product_type']['ukey'] ?? '',
            $campaign_data['product_type']['options']['keys'] ?? '',
        ]));
    }

    public function checkDeignIdInProduct() {
        if ($this->isCampaignMode()) {

            $product = $this->getProduct();

            if (!($product instanceof Model_Catalog_Product)) {
                return false;
            }

            $list_design_in_campaign = $product->getListDesignIdWithPrintTemplate();

            $custom_data_entries = $this->getCampaignData();

            $print_template_id =  $custom_data_entries['print_template']['print_template_id'];

            if (!isset($list_design_in_campaign[$print_template_id]) || !is_array($list_design_in_campaign[$print_template_id]) || count($list_design_in_campaign[$print_template_id]) < 1) {
                return false;
            }

            $personalized_design_ids_in_cart = [];
            $design_last_update = [];

            foreach ($custom_data_entries['print_template']['segment_source'] as $segment_key => $segment_source) {
                if ($segment_source['source']['type'] == 'personalizedDesign') {
                    $personalized_design_ids_in_cart['personalizedDesign'][$segment_key] = $segment_source['source']['design_id'];
                    $design_last_update[$segment_source['source']['design_id']] = $segment_source['source']['design_last_update'];
                }

                if ($segment_source['source']['type'] == 'image') {
                    $personalized_design_ids_in_cart['image'][$segment_key] = $segment_source['source']['image_id'];
                }

            }

            if (md5(OSC::encode($list_design_in_campaign[$print_template_id])) !== md5(OSC::encode($personalized_design_ids_in_cart))) {
                return false;
            }

            $design_ids = array_values($personalized_design_ids_in_cart['personalizedDesign']);

            $list_design_last_update = OSC::helper('catalog/campaign_design')->checkValidateByLastUpdateDesign($design_ids, $design_last_update);

            if ($list_design_last_update) {
                return true;
            }

            $design_collection = null;

            if (count($design_ids) > 0) {
                $design_collection = OSC::helper('catalog/campaign_design')->loadPersonalizedDesigns($design_ids);
            }

            foreach ($custom_data_entries['print_template']['segment_source'] as $segment_key => $segment_source) {
                if ($segment_source['source']['type'] == 'personalizedDesign') {
                    if ($design_collection == null) {
                        return false;
                    }

                    $personalized_design = $design_collection->getItemByPK($segment_source['source']['design_id']);

                    if (!($personalized_design instanceof Model_PersonalizedDesign_Design)) {
                        return false;
                    }

                    try {
                        Observer_Catalog_Campaign::validatePersonalizedDesign($personalized_design, $segment_source['source']['config']);
                    } catch (Exception $ex) {
                        return false;
                    }
                }
            }
        }

        return true;
    }


    public function incrementQuantity($value = 1) {
        if ($this->getId() < 1) {
            throw new OSC_Database_Model_Exception("Model have loaded before update quantity");
        }

        $value = intval($value);

        if ($value == 0) {
            return $this;
        }

        $variant_id = $this->getData('variant_id', true);
        $custom_price_data = $this->getData('custom_price_data');
        $custom_price_data = is_array($custom_price_data) && !empty($custom_price_data) ? $custom_price_data : [];
        if ($variant_id < 1) {
            throw new Exception('Data is missing to update');
        }

        try {
            /* @var $variant Model_Catalog_Product_Variant */
            $variant = $this->getPreLoadedModel('catalog/product_variant', $variant_id);

            if (!$variant->ableToOrder()) {
                throw new Exception('Variant #' . $variant_id . ' is not able to order');
            }
        } catch (Exception $ex) {
            if ($ex->getCode() === 404) {
                throw new Exception('Variant with ID #' . $variant_id . ' is not exists');
            } else {
                throw new Exception($ex->getMessage());
            }
        }

        $DB = $this->getWriteAdapter();

        $atp = false;
        if (isset($this->data['additional_data']['atp']) && $this->data['additional_data']['atp'] == 1) {
            $atp = true;
        }

        $prices = $variant->getPriceForCustomer('', false, $atp);
        $price = $prices['price'];
        $compare_at_price = $prices['compare_at_price'];

        $pack_data = $this->getPackData();
        if (!is_null($pack_data)) {
            $quantity = $pack_data['quantity'];
            $discount_type = $pack_data['discount_type'];

            $discount_price = $discount_type === Model_Catalog_Product_Pack::PERCENTAGE ?
                round($prices['price'] * $quantity * $pack_data['discount_value'] / 100) :
                OSC::helper('catalog/common')->floatToInteger($pack_data['discount_value']);

            $price = $prices['price'] * $quantity - intval($discount_price);
            $compare_at_price = $prices['compare_at_price'] * $quantity;
        }

        $cost = $variant->data['cost'];
        $require_shipping = $variant->data['require_shipping'];
        $require_packing = $variant->data['require_packing'];
        $keep_flat = $variant->data['keep_flat'];
        $weight = $variant->data['weight'];
        $weight_unit = $variant->data['weight_unit'];
        $dimension_width = $variant->data['dimension_width'];
        $dimension_height = $variant->data['dimension_height'];
        $dimension_length = $variant->data['dimension_length'];
        $sku = $variant->data['sku'];
        $custom_price_data = OSC::encode($custom_price_data);

        $query = <<<EOF
UPDATE `{$this->getTableName(true)}`
SET
    `quantity` = (`quantity` + {$value}),
    `sku` = :sku,
    `vendor` = :vendor,
    `price` = '{$price}',
    `cost` = '{$cost}',
    `compare_at_price` = '{$compare_at_price}',
    `require_shipping` = '{$require_shipping}',
    `require_packing` = '{$require_packing}',
    `keep_flat` = '{$keep_flat}',
    `weight` = '{$weight}',
    `weight_unit` = '{$weight_unit}',
    `dimension_width` = '{$dimension_width}',
    `dimension_height` = '{$dimension_height}',
    `dimension_length` = '{$dimension_length}',
    `custom_price_data` = '{$custom_price_data}'
WHERE `{$this->_pk_field}` = {$this->getId()}
LIMIT 1
EOF;

        $params['sku'] = $sku;
        $params['vendor'] = $variant->getProduct()->data['vendor'];

        $DB->query($query, $params, 'update_cart_item_quantity');

        if ($DB->getNumAffected('update_cart_item_quantity') < 1) {
            throw new OSC_Database_Model_Exception("Cannot update database");
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

                if (!is_string($entry['text'])) {
                    unset($entry['text']);
                }

                if (!is_string($entry['type'])) {
                    unset($entry['type']);
                }

                if (!is_string($entry['title'])) {
                    unset($entry['title']);
                }

                $custom_data[$idx] = $entry;
            }

            $custom_data = array_values($custom_data);
        }

        return $custom_data;
    }

    public function makeUkey($cart_id, $variant_id, $custom_data = [], $custom_price_data = [], $additional_data = []) {
        // Don't change ukey by addition_data when customer visit from Google feed
        if (isset($additional_data['atp'])) {
            unset($additional_data['atp']);
        }
        $customData = $this->_cleanCustomData($custom_data);
        $customData = isset($custom_price_data) && !empty($custom_price_data) ? array_merge($customData, $custom_price_data) : $customData;
        $customData = isset($additional_data) && !empty($additional_data) ? array_merge($customData, $additional_data) : $customData;
        return $cart_id . ':' . $variant_id . ':' . md5(OSC::encode($customData));
    }

    protected function _beforeSave() {
        parent::_beforeSave();
        $data = $this->_collectDataForSave();

        $errors = [];
        $is_cross_sell = false;
        if (isset($data['additional_data']['is_cross_sell']) && $data['additional_data']['is_cross_sell'] == 1) {
            $is_cross_sell = true;
        }

        if (isset($data['cart_id'])) {
            if ($this->getActionFlag() === static::INSERT_FLAG) {
                $data['cart_id'] = intval($data['cart_id']);

                if ($data['cart_id'] < 1) {
                    $errors[] = 'Cart ID is empty';
                } else {
                    try {
                        $cart = OSC::model('catalog/cart')->load($data['cart_id']);
                    } catch (Exception $ex) {
                        if ($ex->getCode() === 404) {
                            $errors[] = 'Cart with ID #' . $data['cart_id'] . ' is not exists';
                        } else {
                            $errors[] = $ex->getMessage();
                        }
                    }
                }
            }
        }

        if (!$is_cross_sell) {
            foreach (['price', 'compare_at_price', 'product_id'] as $key) {
                if (isset($data[$key])) {
                    unset($data[$key]);
                }
            }
        }

        $variant = null;
        $variant_price_data = null;

        if (isset($data['variant_id'])) {
            if ($this->getActionFlag() === static::INSERT_FLAG) {
                $data['variant_id'] = intval($data['variant_id']);

                if ($data['variant_id'] < 1) {
                    $errors[] = 'Variant ID is empty';
                } else {
                    try {
                        /* @var $variant Model_Catalog_Product_Variant */
                        $variant = OSC::model('catalog/product_variant')->load($data['variant_id']);

                        $atp = false;
                        if (isset($data['additional_data']['atp']) && $data['additional_data']['atp'] == 1) {
                            $atp = true;
                        }
                        $variant_price_data = $variant->getPriceForCustomer('', false, $atp);

                        if (!$variant->ableToOrder()) {
                            $errors[] = 'Variant #' . $data['variant_id'] . ' is not able to order';
                        } else {
                            $data['product_id'] = $variant->data['product_id'];
                            $data['sku'] = $variant->data['sku'];
                            $data['vendor'] = $variant->getProduct()->data['vendor'];
                            $data['price'] = $variant_price_data['price'];
                            $data['tax_value'] = $this->getTaxValue();
                            $data['cost'] = $variant->data['cost'];
                            $data['compare_at_price'] = $variant_price_data['compare_at_price'];
                            $data['require_shipping'] = $variant->data['require_shipping'];
                            $data['require_packing'] = $variant->data['require_packing'];
                            $data['keep_flat'] = $variant->data['keep_flat'];
                            $data['weight'] = $variant->data['weight'];
                            $data['weight_unit'] = $variant->data['weight_unit'];
                            $data['dimension_width'] = $variant->data['dimension_width'];
                            $data['dimension_height'] = $variant->data['dimension_height'];
                            $data['dimension_length'] = $variant->data['dimension_length'];
                        }
                    } catch (Exception $ex) {
                        if ($ex->getCode() === 404) {
                            $errors[] = 'Variant with ID #' . $data['variant_id'] . ' is not exists';
                        } else {
                            $errors[] = $ex->getMessage();
                        }
                    }
                }
            }
        }

        if (isset($data['custom_price_data'])) {
            if (isset($data['custom_price_data']['buy_design']['buy_design_price'])) {
                $data['custom_price_data']['buy_design']['buy_design_price'] = intval($data['custom_price_data']['buy_design']['buy_design_price']);
            }

            if (isset($data['custom_price_data']['mug_size']['price'])) {
                $data['custom_price_data']['mug_size']['price'] = intval($data['custom_price_data']['mug_size']['price']);

                //If is set mug size, replace variant price by mug size price
                $data['price'] = $data['custom_price_data']['mug_size']['price'];
            }
        }

        if ($is_cross_sell) {
            $data['variant_id'] = 0;
            $data['product_id'] = 0;
        }

        if (isset($data['custom_data'])) {
            $data['custom_data'] = $this->_cleanCustomData($data['custom_data']);
        }

        if (isset($data['quantity'])) {
            $data['quantity'] = intval($data['quantity']);

            if ($data['quantity'] < 1) {
                $errors[] = 'Quantity is need greater than 0';
            }
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
                $require_fields = [
                    'cart_id' => 'Cart ID is empty',
                    'price' => 'Variant price is empty',
                    'cost' => 'Variant cost is empty',
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

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'sku' => '',
                    'vendor' => '',
                    'custom_data' => [],
                    'compare_at_price' => 0,
                    'amount' => 0,
                    'additional_data' => [],
                    'price_data' => [],
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                if (count($errors) < 1) {
                    $data['ukey'] = $this->makeUkey($data['cart_id'], $data['variant_id'], $data['custom_data'], $data['custom_price_data'], $data['additional_data']);
                }
            } else {
                unset($data['cart_id']);
                unset($data['variant_id']);

                if (isset($data['quantity']) && !$this->isCrossSellMode()) {
                    $variant_id = $this->getData('variant_id', false);
                    if ($variant_id < 1) {
                        $errors[] = 'Data is missing to update';
                    } else {
                        try {
                            /* @var $variant Model_Catalog_Product_Variant */
                            $variant = OSC::model('catalog/product_variant')->load($variant_id);

                            $atp = false;
                            if (isset($data['additional_data']['atp']) && $data['additional_data']['atp'] == 1) {
                                $atp = true;
                            }
                            $variant_price_data = $variant->getPriceForCustomer('', false, $atp);

                            if (!$variant->ableToOrder()) {
                                $errors[] = 'Variant #' . $data['variant_id'] . ' is not able to order';
                            } else {
                                $data['variant_id'] = $variant_id;
                                $data['sku'] = $variant->data['sku'];
                                $data['vendor'] = $variant->getProduct()->data['vendor'];
                                $data['price'] = $data['custom_price_data']['mug_size']['price'] ?? $variant_price_data['price'];
                                $data['tax_value'] = $this->getTaxValue();
                                $data['cost'] = $variant->data['cost'];
                                $data['compare_at_price'] = $variant_price_data['compare_at_price'];
                                $data['require_shipping'] = $variant->data['require_shipping'];
                                $data['require_packing'] = $variant->data['require_packing'];
                                $data['keep_flat'] = $variant->data['keep_flat'];
                                $data['weight'] = $variant->data['weight'];
                                $data['weight_unit'] = $variant->data['weight_unit'];
                                $data['dimension_width'] = $variant->data['dimension_width'];
                                $data['dimension_height'] = $variant->data['dimension_height'];
                                $data['dimension_length'] = $variant->data['dimension_length'];
                            }
                        } catch (Exception $ex) {
                            if ($ex->getCode() === 404) {
                                $errors[] = 'Variant with ID #' . $data['variant_id'] . ' is not exists';
                            } else {
                                $errors[] = $ex->getMessage();
                            }
                        }
                    }
                }
            }
        }

        if (isset($data['custom_data']) && is_array($data['custom_data']) && count($data['custom_data']) > 0) {
            foreach ($data['custom_data'] as $custom_data_entry) {
                if ($custom_data_entry['key'] == 'campaign') {
                    $data['price'] = $variant_price_data['price'];
                    $data['cost'] = $variant->data['cost'];
                    $data['require_shipping'] = $variant->data['require_shipping'];
                    $data['require_packing'] = $variant->data['require_packing'];
                    $data['keep_flat'] = $variant->data['keep_flat'];
                    $data['weight'] = $variant->data['weight'];
                    $data['weight_unit'] = $variant->data['weight_unit'];
                    $data['dimension_width'] = $variant->data['dimension_width'];
                    $data['dimension_height'] = $variant->data['dimension_height'];
                    $data['dimension_length'] = $variant->data['dimension_length'];
                    $data['sku'] = $this->_getCampaignSku($variant->getProduct());
                }
            }
        }

        if (isset($data['additional_data']) && is_array($data['additional_data']) && count($data['additional_data']) > 0 && isset($data['additional_data']['pack'])) {
            $pack_data = $data['additional_data']['pack'];

            $product_pack_price = OSC::helper('catalog/product')->getProductPriceByPack($pack_data, ['price' => $variant_price_data['price'], 'compare_at_price' => $data['compare_at_price']]);

            $data['price'] = $product_pack_price['price'];
            $data['compare_at_price'] = $product_pack_price['compare_at_price'];
        }

        if (isset($data['price']) && $data['price'] <= 0) {
            $product_title = '';
            $product_url = OSC_FRONTEND_BASE_URL;
            try {
                if ($variant instanceof Model_Catalog_Product_Variant) {
                    $product = $variant->getProduct();
                    $product_title = $product->getProductTitle();
                    $product_url = $product->getDetailUrl();
                }
            } catch (Exception $ex) {
                //
            }

            try {
                $message = OSC::$base_url . ': Cart ID #' . $data['cart_id'] . "\nProduct ID #" . $data['product_id'] .
                    ': ' . $product_title . " have price <= 0 in the store.\nProduct URL: " . $product_url;

                OSC::helper('core/telegram')->sendMessage($message, OSC::helper('core/setting')->get('error_payment_notifications/telegram_group_id'));
            } catch (Exception $exception) {

            }

            $errors[] = 'Unable to add the product to cart because of some issue with the product "' . $product_title . '"';
        }

        $this->resetDataModifiedMap()->setData($data);

        $this->_changeCartModifiedTimestamp();

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _beforeDelete() {
        parent::_beforeDelete();

        $this->_changeCartModifiedTimestamp();
    }

    protected function _changeCartModifiedTimestamp() {
        try {
            $cart_id = $this->data['cart_id'];
            $DB = $this->getWriteAdapter();
            $DB->update('catalog_cart', ['modified_timestamp' => time()], 'cart_id = ' . $cart_id, 1, 'update_data_cart_' . $cart_id);
            $DB->free('update_data_cart_' . $cart_id);
        } catch (Exception $ex) { }
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach ($this->_json_fields as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach ($this->_json_fields as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

    /**
     * Validate this addon service has expired for this line_item (Product)
     * @return bool|void
     * @throws OSC_Database_Model_Exception
     */
    public function _validateAddonService($options = []) {
        if (isset($this->data['custom_price_data']['addon_services']) && count($this->data['custom_price_data']['addon_services']) > 0) {
            $custom_price_data = $this->data['custom_price_data'];
            $product_addon_services = OSC::helper('catalog/cart')->getAddonServiceDataOfCartItem($this, $options);
            $data_addon_service_ids = array_column($product_addon_services['data_addon_service'], 'id');

            $need_update = false;
            foreach ($this->data['custom_price_data']['addon_services'] as $addon_id => $addon_options) {
                if (!in_array($addon_id, $data_addon_service_ids)) {
                    unset($custom_price_data['addon_services'][$addon_id]);
                    $need_update = true;
                }
            }

            if (empty($custom_price_data['addon_services'])) {
                unset($custom_price_data['addon_services']);
            }

            if ($need_update) {
                $this->setData(['custom_price_data' => $custom_price_data])->save();
                $this->reload();
                return true;
            }
        }
    }

    /**
     * Reset addon service on database and then Auto select addon service to re-update to custom_price_data
     * @param array $options
     * @return bool
     * @throws OSC_Database_Model_Exception
     */
    public function _resetAddonService($options = []) {
        $custom_price_data = $this->data['custom_price_data'];
        unset($custom_price_data['addon_services']);

        $product_addon_services = OSC::helper('catalog/cart')->getAddonServiceDataOfCartItem($this, $options);
        $data_addon_service = $product_addon_services['data_addon_service'];

        if (count($data_addon_service) > 0) {
            foreach ($data_addon_service as $addon) {
                $select_option_key = $addon['auto_select'];

                if ($select_option_key && isset($addon['options'][$select_option_key])) {
                    $custom_price_data['addon_services'][$addon['id']] = [];
                    $custom_price_data['addon_services'][$addon['id']][$select_option_key] = $addon['options'][$select_option_key];
                    $custom_price_data['addon_services'][$addon['id']][$select_option_key]['message'] = '';
                    $custom_price_data['addon_services'][$addon['id']][$select_option_key]['type'] = $addon['type'];
                    $custom_price_data['addon_services'][$addon['id']][$select_option_key]['campaign_title'] = $addon['title'];
                    $custom_price_data['addon_services'][$addon['id']][$select_option_key]['version_id'] = $addon['version_id'];
                }
            }
        }

        $this->setData(['custom_price_data' => $custom_price_data])->save();
        $this->reload();
    }

    /**
     * @param array $options
     * @return bool|void
     * @throws OSC_Database_Model_Exception
     * Update data addon service when change product type or product type variant or addon  has deactived or deleted
     */
    public function checkUpdateAddonServices($options = []) {
        $list_addon_service = OSC::helper('catalog/cart')->getAddonServiceDataOfCartItem($this, $options);
        $item_addon_services = $this->data['custom_price_data']['addon_services'] ?? [];

        $item_addon_service_ids = array_keys($item_addon_services);
        $data_addon_service_ids = array_column($list_addon_service['data_addon_service'], 'id');

        $remove_addon_keys = array_diff($item_addon_service_ids, $data_addon_service_ids);
        $add_addon_keys = array_diff($data_addon_service_ids, $item_addon_service_ids);

        foreach ($remove_addon_keys as $remove_key) {
            unset($item_addon_services[$remove_key]);
        }

        foreach ($add_addon_keys as $add_key) {
            $adding_addon = $list_addon_service['data_addon_service']['_' . $add_key];

            if ($adding_addon
            && $adding_addon['auto_select']
            && $adding_addon['options'][$adding_addon['auto_select']]
            && $adding_addon['is_hide'] == 0) {
                $adding_option = $adding_addon['options'][$adding_addon['auto_select']];
                $adding_option['id'] = $adding_addon['auto_select'];
                $adding_option['campaign_title'] = $adding_addon['title'];
                $adding_option['version_id'] = $adding_addon['version_id'];
                $adding_option['type'] = $adding_addon['type'];
                $adding_option['message'] = "";

                $item_addon_services[$add_key] = [
                    $adding_addon['auto_select'] => $adding_option,
                ];
            }
        }

        if (count($remove_addon_keys) || count($add_addon_keys)) {
            $custom_price_data = $this->data['custom_price_data'];

            if (count($item_addon_services) > 0) {
                $custom_price_data['addon_services'] = $item_addon_services;
            } else {
                // If all addon_service has unavailable and addon_service data has empty, remove param addon_services
                unset($custom_price_data['addon_services']);
            }

            $this->setData(['custom_price_data' => $custom_price_data])->save();

            return true;
        }

        return false;
    }


}
