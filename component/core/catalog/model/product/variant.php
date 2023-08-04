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
class Model_Catalog_Product_Variant extends Abstract_Core_Model {

    protected $_table_name = 'product_variant';
    protected $_pk_field = 'id';
    protected $_ukey_field = 'sku';

    protected $_allow_write_log = true;
    protected $_uploaded_mockup_videos = null;

    const STATE_ENABLE = 1;
    const STATE_DISABLE = 0;

    const STATE_CUSTOM_SHAPE = [
        'ON' => 1,
        'OFF' => 0
    ];

    const CUSTOM_SHAPE_BACK_LINE_TYPE = ['vertical', 'horizontal'];

    const CUSTOM_SHAPE_RED_LINE_TYPE = ['single'];

    /**
     *
     * @var Model_Catalog_Product
     */
    protected $_product_model = null;

    /**
     *
     * @var Model_Catalog_Product_Image_Collection
     */
    protected $_image_collection = null;
    protected $_video_collection = null;

    const WEIGHT_UNITS = 'g,kg,lb,oz';

    public function isCampaign() {
        return is_array($this->data['meta_data']) && isset($this->data['meta_data']['campaign_config']);
    }

    public function getTitle() {
        $options = [];
        foreach ($this->data['options'] as $data_option) {
            if ($data_option) {
                $options[] = $data_option;
            }
        }
        return implode(' / ', $options);
    }

    public function getOptions() {
        $options = [];

        $product = $this->getProduct();

        if (!$product) {
            throw new Exception('Product load failed');
        }

        foreach ($product->getOrderedOptions(true) as $option_idx => $option_data) {
            $options[] = ['title' => $option_data['title'], 'value' => $this->data['options'][$option_idx]];
        }

        return $options;
    }

    public function getDetailUrl() {
        return $this->getProduct()->getDetailUrl() . '?variant=' . $this->getId();
    }

    public function ableToOrder() {
        return $this->data['track_quantity'] != 1 || $this->data['overselling'] == 1 || $this->data['quantity'] > 0;
    }

    public function getFixedPriceData() {
        $best_price_data = $this->data['best_price_data'];

        if (empty($best_price_data['fixed_price_data'])) {
            return [];
        }

        return $best_price_data['fixed_price_data'];
    }

    public function hasFixedPriceData() {
        return !empty($this->getFixedPriceData());
    }

    public function getBestPriceByCampaign($country_code = '', $flag_feed = false) {
        $location = OSC::helper('core/common')->getClientLocation();
        $country_code_location = $flag_feed ? $country_code : $location['country_code'];

        $best_price_data = $this->data['best_price_data'];

        return $best_price_data[$country_code_location];
    }

    public function hasBestPriceInCountry($country_code) {
        $best_price_data = $this->data['best_price_data'];

        return isset($best_price_data[$country_code]);
    }

    public function isDefaultVariant() {
        if ($this->getId() < 1) {
            return false;
        }
        foreach ($this->data['options'] as $option => $value) {
            if ($value != null) {
                return false;
            }
        }
        return true;
    }

    public function getFloatPrice() {
        return OSC::helper('catalog/common')->integerToFloat(intval($this->data['price']));
    }

    public function getFloatCompareAtPrice() {
        return OSC::helper('catalog/common')->integerToFloat(intval($this->data['compare_at_price']));
    }

    public function getFloatCost() {
        return OSC::helper('catalog/common')->integerToFloat(intval($this->data['cost']));
    }

    public function getFloatWeight() {
        return OSC::helper('catalog/common')->integerToFloat(intval($this->data['weight']));
    }

    public function getWeightInGram() {
        return OSC::helper('catalog/common')->getWeightInGram($this->data['weight'] ?? 0, $this->data['weight_unit']);
    }

    public function getVariantTitle() {
        return $this->getProduct()->getProductTitle() . (count($this->getOptions()) < 1 ? '' : (' - ' . $this->getTitle()));
    }

    protected $_default_price_data = null;

    /**
     * Get default price from product type variant
     * @param bool $reload
     * @param $preload_product_type_variants
     * @return array
     */
    public function getDefaultPriceData(bool $reload = false, $preload_product_type_variants = null) {
        if ($this->_default_price_data === null || $reload) {
            $this->_default_price_data = [
                'price' => $this->data['price'],
                'compare_at_price' => $this->data['compare_at_price']
            ];

            if ($this->isSemiTest()) {
                return $this->_default_price_data;
            }

            try {
                if ($preload_product_type_variants instanceof Model_Catalog_ProductType_Variant_Collection) {
                    $preload_product_type_variant = $preload_product_type_variants->getItemByPK($this->data['product_type_variant_id']);
                }
                $product_type_variant = $preload_product_type_variant ?? $this->getProductTypeVariant($reload, true);

                if ($product_type_variant->getId() > 0) {
                    $this->_default_price_data['price'] = $product_type_variant->data['price'];
                    $this->_default_price_data['compare_at_price'] = $product_type_variant->data['compare_at_price'];
                }

                /* Get fixed price of variant */
                $fixed_price_data = $this->getFixedPriceData();
                if (!empty($fixed_price_data)) {
                    $this->_default_price_data['price'] = intval($fixed_price_data['price']) > 0 ?
                        $fixed_price_data['price'] + intval($fixed_price_data['plus_price']) :
                        $this->_default_price_data['price'] + intval($fixed_price_data['plus_price']);
                    $this->_default_price_data['compare_at_price'] = intval($fixed_price_data['compare_at_price']) ?
                        $fixed_price_data['compare_at_price'] + intval($fixed_price_data['plus_price']) :
                        $this->_default_price_data['compare_at_price'] + intval($fixed_price_data['plus_price']);
                }
            } catch (Exception $ex) {

            }
        }

        return $this->_default_price_data;
    }

    /**
     * Get price to show for customer
     * Variable $country_code using for feed
     * @param string $country_code
     * @param bool $flag_feed
     * @param bool $skip_ab_test
     * @param null $preload_product_type_variants
     * @return array
     */
    public function getPriceForCustomer(
        string $country_code = '',
        bool $flag_feed = false,
        bool $skip_ab_test = false,
        $preload_product_type_variants = null
    ): array {
        if ($this->isSemiTest()) {
            $result = $this->_getSemitestPriceData($country_code, $flag_feed, $skip_ab_test);
        } else {
            $result = $this->_getCampaignPriceData(
                $country_code,
                $flag_feed,
                $skip_ab_test,
                $preload_product_type_variants
            );
        }

        return $result;
    }

    /**
     * Get price data for campaign product
     * @param string $country_code
     * @param bool $flag_feed
     * @param bool $skip_ab_test
     * @param null $preload_product_type_variants
     * @return array|null
     */
    protected function _getCampaignPriceData(
        string $country_code,
        bool $flag_feed,
        bool $skip_ab_test,
        $preload_product_type_variants
    ) {
        $default_price_data = $this->getDefaultPriceData($flag_feed, $preload_product_type_variants);
        $default_price = $default_price_data['price'] ?? 0;

        /* Handle case price data of variant fixed */
        if ($this->hasFixedPriceData()) {
            return [
                'price' => $default_price_data['price'],
                'compare_at_price' => $default_price_data['compare_at_price']
            ];
        }

        /* Start get best price */
        $best_price_for_campaign = $this->getBestPriceByCampaign($country_code, $flag_feed);

        $version_best_price_campaign = 0;

        if ($best_price_for_campaign) {
            $version_best_price_campaign = array_values($best_price_for_campaign)[0];
        }

        if ($preload_product_type_variants instanceof Model_Catalog_ProductType_Variant_Collection) {
            $preload_product_type_variant = $preload_product_type_variants->getItemByPK($this->data['product_type_variant_id']);
        }

        $product_type_variant = $preload_product_type_variant ?? $this->getProductTypeVariant($flag_feed, true);

        $best_price_for_store = null;

        if ($product_type_variant->getId() > 0) {
            $best_price_for_store = $product_type_variant->getBestPriceByStore($country_code, $flag_feed);
        }

        $version_best_price_store = 0;

        if ($best_price_for_store) {
            $version_best_price_store = array_values($best_price_for_store)[0];
        }

        if ($version_best_price_store != 0 || $version_best_price_campaign != 0) {
            if ($version_best_price_store < $version_best_price_campaign) {
                $best_price = array_key_first($best_price_for_campaign);
            } else {
                $best_price = array_key_first($best_price_for_store);
            }

            return [
                'price' => $best_price,
                'compare_at_price' => $default_price_data['compare_at_price']
            ];
        }
        /* End get best price */

        if ($flag_feed) {
            return $default_price_data;
        }

        return [
            'price' => OSC::helper('autoAb/productPrice')->getPriceFromABTest($this, $default_price, $skip_ab_test),
            'compare_at_price' => $default_price_data['compare_at_price']
        ];
    }

    /**
     * Get price data for semi product
     * @param string $country_code
     * @param bool $flag_feed
     * @param bool $skip_ab_test
     * @return array
     */
    protected function _getSemitestPriceData(
        string $country_code = '',
        bool $flag_feed = false,
        bool $skip_ab_test = false
    ) {
        /* Default price of semitest product */
        $result = [
            'price' => $this->data['price'],
            'compare_at_price' => $this->data['compare_at_price']
        ];

        /* Get best price by location */
        $best_price_for_campaign = $this->getBestPriceByCampaign($country_code, $flag_feed);
        if ($best_price_for_campaign) {
            $result['price'] = array_key_first($best_price_for_campaign);

            return $result;
        }

        if ($flag_feed) {
            return $result;
        }

        /* Get price ab test */
        $result['price'] = OSC::helper('autoAb/productPrice')->getPriceFromABTest(
            $this,
            $this->data['price'],
            $skip_ab_test
        );

        return $result;
    }

    /**
     *
     * @param mixed $product
     * @return $this
     */
    public function setProduct($product) {
        $this->_product_model = ($product instanceof Model_Catalog_Product) ? $product : null;
        return $this;
    }

    /**
     *
     * @return $this->_product_model
     * @throws Exception
     */
    public function getProduct($reload = false) {
        if ($reload ||
            !($this->_product_model instanceof Model_Catalog_Product) ||
            ($this->_product_model->getId() > 0 && $this->_product_model->getId() != $this->data['product_id'])
        ) {
            $this->_product_model = static::getPreLoadedModel('catalog/product', $this->data['product_id']);
        }

        return $this->_product_model;
    }

    /**
     *
     * @var Model_Catalog_ProductType_Variant
     */
    protected $_product_type_variant_model = null;

    /**
     * @param false $reload
     * @param false $useCache
     * @return Model_Catalog_ProductType_Variant
     */
    public function getProductTypeVariant($reload = false, $useCache = false): Model_Catalog_ProductType_Variant {
        if ($this->_product_type_variant_model === null || $reload) {
            $product_type_variant_id = $this->data['product_type_variant_id'];

            if (!empty($product_type_variant_id)) {
                $cache_key = "|model.catalog.productType_variant|product_type_variant_id:,{$product_type_variant_id},|";
                if ($useCache && ($cache = OSC::core('cache')->get($cache_key)) !== false) {
                    $this->_product_type_variant_model = OSC::model('catalog/productType_variant')->bind($cache);
                } else {
                    try {
                        $preload_data = OSC::helper('catalog/campaign')->getPreloadProductTypeVariants();

                        $this->_product_type_variant_model = !empty($preload_data[$product_type_variant_id]) ?
                            OSC::model('catalog/productType_variant')->bind($preload_data[$product_type_variant_id]) :
                            OSC_Database_Model::getPreLoadedModel(
                                'catalog/productType_variant',
                                $product_type_variant_id
                            );
                    } catch (Exception $exception) { }

                    OSC::core('cache')->set($cache_key, $this->_product_type_variant_model->data, OSC_CACHE_TIME);
                }
            } else {
                $this->_product_type_variant_model = new Model_Catalog_ProductType_Variant;
            }
        }

        $using_array_to_load = false;
        if (!($this->_product_type_variant_model instanceof Model_Catalog_ProductType_Variant) ||
            $this->_product_type_variant_model->getId() < 1
        ) {
            $using_array_to_load = true;
        }

        if ($using_array_to_load) {
            $this->_product_type_variant_model = OSC::model('catalog/productType_variant')->bind($this->_array_product_type_variant);
        }

        return $this->_product_type_variant_model;
    }

    /**
     *
     * @param Model_Catalog_ProductType_Variant $product_type_variant
     * @return $this
     */
    public function setProductTypeVariant(Model_Catalog_ProductType_Variant $product_type_variant) {
        if ($product_type_variant->getId() > 0) {
            $this->_product_type_variant_model = $product_type_variant;
        }

        return $this;
    }

    protected $_array_product_type_variant = null;
    public function setArrayProductTypeVariant($array_product_type_variant) {
        $this->_array_product_type_variant = $array_product_type_variant;

        return $this;
    }

    public function getProductType() {
        return $this->getProductTypeVariant(false, true)->getProductType(true);
    }

    /**
     *
     * @param bool $flag_image_default
     * @param bool $flag_feed
     * @param bool $useCache
     * @return Model_Catalog_Product_Image
     * @throws Exception
     */
    public function getImage($flag_image_default = false, $flag_feed = false, $useCache = false) {
        $image = $this->getImages($flag_feed, $useCache)->getItem();

        return ($image instanceof Model_Catalog_Product_Image) ? $image : $this->getProduct()->getFeaturedImage($flag_image_default, $useCache);
    }

    /**
     * @param bool $flag_feed
     * @param bool $useCache
     * @return Model_Catalog_Product_Image_Collection
     * @throws OSC_Exception_Runtime
     * @throws Exception
     */
    public function getImages($flag_feed = false, $useCache = false) {
        if ($this->_image_collection === null) {
            $this->_image_collection = OSC::model('catalog/product_image')->getCollection();

            $meta_data = $this->data['meta_data'];
            $image_ids = $this->data['image_id'];

            $product_images = $this->getProduct()->getImages($flag_feed, $useCache);

            if ($image_ids == null || !is_array($image_ids)) {
                $image_ids = [];
            }

            if ($this->isCampaign() &&
                is_array($meta_data['campaign_config']['image_ids']) &&
                count($meta_data['campaign_config']['image_ids']) > 0
            ) {
                foreach ($this->data['meta_data']['campaign_config']['image_ids'] as $campaign_image_id) {
                    $image_ids = array_merge($image_ids, $campaign_image_id['image_ids']);
                }
            }

            foreach ($product_images as $image) {
                if (in_array($image->getId(), $image_ids)) {
                    if ($this->_image_collection instanceof Model_Catalog_Product_Image_Collection &&
                        $image instanceof Model_Catalog_Product_Image && $image->data['image_id']
                    ) {
                        $this->_image_collection->addItem($image);
                    }
                }
            }

            if ($this->_image_collection instanceof Model_Catalog_Product_Image_Collection) {
                $this->_image_collection->lock();
            }

        }

        return $this->_image_collection;
    }

    /**
     * @param bool $flag_feed
     * @param bool $useCache
     * @return Model_Catalog_Product_Image
     * @throws OSC_Exception_Runtime
     * @throws Exception
     */
    public function getVideos($flag_feed = false, $useCache = false) {
        if ($this->_video_collection !== null) return $this->_video_collection;

        $this->_video_collection = OSC::model('catalog/product_image')->getCollection();

        $video_ids = $this->data['video_id'];

        if (empty($video_ids)) return $this->_video_collection;

        if (!is_array($video_ids)) {
            $video_ids = [];
        }

        $product_videos = $this->getProduct()->getVideos($flag_feed, $useCache);

        foreach ($product_videos as $video) {
            if (in_array($video->getId(), $video_ids)) {
                if (
                    $this->_video_collection instanceof Model_Catalog_Product_Image_Collection &&
                    $video instanceof Model_Catalog_Product_Image &&
                    $video->data['image_id']
                ) {
                    $this->_video_collection->addItem($video);
                }
            }
        }

        if ($this->_video_collection instanceof Model_Catalog_Product_Image) {
            $this->_video_collection->lock();
        }

        return $this->_video_collection;
    }

    /**
     * @throws Exception
     */
    public function getImageUrl($flag_image_default = false, $flag_feed = false, $useCache = false) {
        $image = $this->getImage($flag_image_default, $flag_feed, $useCache);

        return $image ? $image->getUrl() : '';
    }

    public function incrementQuantity($value) {
        $value = intval($value);

        if ($value == 0) {
            return $this;
        }

        $DB = $this->getWriteAdapter();

        $DB->query("UPDATE {$this->getTableName(true)} SET quantity = (quantity + {$value}) WHERE {$this->getPkFieldName()} = {$this->getId()} AND track_quantity = 1 LIMIT 1;", null, 'increment_variant_quantity');

        $DB->free('increment_variant_quantity');

        $DB->select('quantity', $this->getTableName(), 'id = ' . $this->getId(), null, 1, 'fetch_new_quantity');

        $row = $DB->fetchArray('fetch_new_quantity');

        $DB->free('fetch_new_quantity');

        if ($row) {
            $this->setData('quantity', $row['quantity']);
            $this->setData('quantity', $row['quantity'], true);
        }

        return $this;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        $product_model = null;

        if (isset($data['product_id'])) {
            $data['product_id'] = intval($data['product_id']);

            if ($data['product_id'] < 1) {
                $errors[] = 'Product ID is empty';
            } else if ($this->getProduct()->getId() < 1) {
                $errors[] = 'Cannot verify product id';
            }
        }

        if (isset($data['image_id'])) {
            if (!is_array($data['image_id'])) {
                $data['image_id'] = [];
            }

            $data['image_id'] = array_map(function ($image_id) {
                return intval($image_id);
            }, $data['image_id']);
            $data['image_id'] = array_unique($data['image_id']);
            $data['image_id'] = array_filter($data['image_id'], function ($image_id) {
                if ($image_id < 1) {
                    return false;
                }

                return $this->getProduct()->getImages()->getItemByKey($image_id) instanceof Model_Catalog_Product_Image;
            });
        }

        if (isset($data['sku'])) {
            $data['sku'] = $this->cleanUkey($data['sku']);
        }

//        foreach (array('option1', 'option2', 'option3') as $key) {
//            if (isset($data[$key])) {
//                $data[$key] = trim($data[$key]);
//
//                if ($this->getProduct()->getId() < 1) {
//                    $errors[] = 'Cannot load product to verify variant option';
//                    break;
//                }
//
//                $options = $this->getProduct()->data['options'];
//
//                if ($data[$key] == '') {
//                    if ($options[$key] !== false) {
//                        $errors[] = $key . ' should be in the product option list';
//                    }
//                } else {
//                    if ($options[$key] === false) {
//                        $errors[] = $key . ' should be empty';
//                    } else if (!in_array($data[$key], $options[$key]['values'], true)) {
//                        $errors[] = $key . ' should be in the product option list';
//                    }
//                }
//            }
//        }

        foreach (array('price', 'compare_at_price', 'cost', 'weight') as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::helper('catalog/common')->floatToInteger(floatval($data[$key]));

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        foreach (array('dimension_width', 'dimension_height', 'dimension_length') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        foreach (array('track_quantity', 'overselling', 'require_shipping', 'keep_flat', 'require_packing') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]) == 1 ? 1 : 0;
            }
        }

        if (isset($data['weight_unit'])) {
            $data['weight_unit'] = trim($data['weight_unit']);

            if (!in_array($data['weight_unit'], explode(',', static::WEIGHT_UNITS), true)) {
                $errors[] = 'Weight unit is not allowed';
            }
        }

        if (isset($data['meta_data'])) {
            $data['product_type_variant_id'] = intval($data['meta_data']['campaign_config']['product_type_variant_id']);
            $data['meta_data'] = OSC::encode($data['meta_data']);
        }

        foreach (array('added_timestamp', 'modified_timestamp', 'quantity') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        //If controller frontend, don't save variant's price & compare_at_price
        if (OSC::controller() instanceof Abstract_Frontend_Controller && $this->registry('price_modified_by_rules') == 1) {
            unset($data['price']);
            unset($data['compare_at_price']);
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'product_id' => 'Product id is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'image_id' => [],
                    'design_id' => [],
                    'sku' => '',
                    'price' => 0,
                    'compare_at_price' => 0,
                    'cost' => 0,
                    'track_quantity' => 1,
                    'overselling' => 0,
                    'quantity' => 0,
                    'require_shipping' => 1,
                    'require_packing' => 0,
                    'keep_flat' => 1,
                    'weight' => 0,
                    'weight_unit' => current(explode(',', static::WEIGHT_UNITS)),
                    'dimension_width' => 0,
                    'dimension_height' => 0,
                    'dimension_length' => 0,
                    'position' => 0,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                if (count($errors) < 1 && (!isset($data['sku']) || !$data['sku'])) {
                    $data['sku'] = $this->getProduct()->getUkey() . '-' . strtoupper(uniqid(null, false) . OSC::randKey(2, 7));
                }
            } else {
                unset($data['product_id']);
//                unset($data['option1']);
//                unset($data['option2']);
//                unset($data['option3']);
                unset($data['sku']);
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    public function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['design_id', 'image_id', 'video_id'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = (!is_array($data[$key]) || count($data[$key]) < 1) ? '' : implode(',', $data[$key]);
            }
        }

        foreach (['options', 'best_price_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    public function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['design_id', 'image_id', 'video_id'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = $data[$key] === '' ? [] : explode(',', $data[$key]);
            }
        }

        if (isset($data['price']) && isset($data['compare_at_price'])) {
            $price = $data['price'];
            $compare_at_price = $data['compare_at_price'];

            OSC::helper('catalog/frontend')->applyPriceRules($price, $compare_at_price, ['product_id' => $data['product_id']]);

            if ($price != $data['price'] || $compare_at_price != $data['compare_at_price']) {
                $this->register('price_modified_by_rules', 1);

                $data['price'] = $price;
                $data['compare_at_price'] = $compare_at_price;
            }
        }

        foreach (['options', 'meta_data', 'best_price_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
                if ($key == 'options') {
                    foreach ($data[$key] as $key_option => $value_option) {
                        $data[$key_option] = $value_option;
                    }
                } else if($key == 'meta_data') {
                    if (isset($data[$key]['semitest_config']['shipping_price'])) {
                        $data[$key]['semitest_config']['shipping_price'] = OSC::helper('catalog/common')->integerToFloat(intval($data[$key]['semitest_config']['shipping_price']));
                    }
                    if (isset($data[$key]['semitest_config']['shipping_plus_price'])) {
                        $data[$key]['semitest_config']['shipping_plus_price'] = OSC::helper('catalog/common')->integerToFloat(intval($data[$key]['semitest_config']['shipping_plus_price']));
                    }
                }
            }
        }

    }

    public function __toArray() {
        return [
            'id' => intval($this->getId()),
            'product_id' => intval($this->data['product_id']),
            'image_id' => $this->data['image_id'],
            'video_id' => $this->data['video_id'],
            'design_id' => $this->data['design_id'],
            'sku' => $this->data['sku'],
            'options' => $this->data['options'],
            'option1' => $this->data['options']['option1'],
            'option2' => $this->data['options']['option2'],
            'option3' => $this->data['options']['option3'],
            'price' => $this->getFloatPrice(),
            'compare_at_price' => $this->getFloatCompareAtPrice(),
            'cost' => $this->getFloatCost(),
            'track_quantity' => intval($this->data['track_quantity']),
            'overselling' => intval($this->data['overselling']),
            'quantity' => intval($this->data['quantity']),
            'require_shipping' => intval($this->data['require_shipping']),
            'require_packing' => intval($this->data['require_packing']),
            'keep_flat' => intval($this->data['keep_flat']),
            'weight' => $this->getFloatWeight(),
            'weight_unit' => $this->data['weight_unit'],
            'dimension_width' => $this->data['dimension_width'],
            'dimension_height' => $this->data['dimension_height'],
            'dimension_length' => $this->data['dimension_length'],
            'added_date' => date('c', $this->data['added_timestamp']),
            'modified_date' => date('c', $this->data['modified_timestamp']),
            'position' => $this->data['position'],
            'meta_data' => $this->data['meta_data'],
            'product_type_variant_id' => $this->data['product_type_variant_id']
        ];
    }

    public function isSemiTest() {
        return count($this->data['design_id']) > 0;
    }

    protected $_image_collection_by_customer_upload = null;

    public function getImagesByCustomerUpload($flag_feed = false) {
        if ($this->_image_collection_by_customer_upload === null) {
            $this->_image_collection_by_customer_upload = OSC::model('catalog/product_image')->getCollection();
            $image_collection = $this->getImages();
            foreach ($image_collection as $image) {
                if ($image->data['is_static_mockup'] == 2) {
                    $this->_image_collection_by_customer_upload->addItem($image);
                }
            }
        }

        return $this->_image_collection_by_customer_upload;
    }

    protected $_image_collection_by_print_template = null;

    public function getImagesByPrintTemplate($flag_reload = false) {
        if ($this->_image_collection_by_print_template === null || $flag_reload) {
            $image_ids = [];

            if ($this->isCampaign() && is_array($this->data['meta_data']['campaign_config']['image_ids']) && count($this->data['meta_data']['campaign_config']['image_ids']) > 0) {
                foreach ($this->data['meta_data']['campaign_config']['image_ids'] as $campaign_image_id) {
                    if ($image_ids[$campaign_image_id['print_template_id']] == null) {
                        $image_ids[$campaign_image_id['print_template_id']] = [];
                    }

                    if ($campaign_image_id['image_ids'] == null) {
                        $campaign_image_id['image_ids'] = [];
                    }

                    $image_ids[$campaign_image_id['print_template_id']] = array_merge($image_ids[$campaign_image_id['print_template_id']], $campaign_image_id['image_ids']);
                }
            }

            $this->_image_collection_by_print_template = $image_ids;

        }

        return $this->_image_collection_by_print_template;
    }

    protected $_featured_image = null;

    public function getFeaturedImage($useCache = false, $options = []) {
        if ($this->_featured_image == null) {
            $image_collection = $this->getImages(false, $useCache);

            if (!($image_collection instanceof Model_Catalog_Product_Image_Collection) ||
                $image_collection->length() < 1
            ) {
                return null;
            }

            $flag_set_image = false;
            $list_customer_upload = [];

            foreach ($image_collection as $image) {
                if ($image->data['flag_main'] == 1) {
                    $this->_featured_image = $image;
                    $flag_set_image = true;
                    break;
                }
                $list_customer_upload[intval($image->data['position'])] =  $image->getId();
            }

            if (!$flag_set_image) {
                ksort($list_customer_upload);
                $this->_featured_image = $image_collection->getItemByPK($list_customer_upload[array_key_first($list_customer_upload)]);
            }
        }

        return $this->_featured_image;
    }

    public function getImageFeaturedUrl($useCache = false) {
        $image = $this->getFeaturedImage($useCache);

        if (!isset($image) || !($image instanceof Model_Catalog_Product_Image)) {
            return '';
        }

        return $image->getUrl();
    }

    public function getImageFeaturedUrlByAmazon($useCache = false) {
        $image = $this->getFeaturedImage($useCache, ['by_amazon']);

        if (!isset($image) || !($image instanceof Model_Catalog_Product_Image)) {
            return '';
        }

        return $image->getUrl();
    }
}
