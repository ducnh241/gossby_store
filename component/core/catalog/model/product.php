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
class Model_Catalog_Product extends Abstract_Core_Model {

    protected $_table_name = 'catalog_product';
    protected $_pk_field = 'product_id';
    protected $_ukey_field = 'sku';

    protected $_allow_write_log = true;

    protected $_option_conf = ['value' => 'product_id', 'label' => 'title'];

    const HAS_TAB_PREFIX = 'product_has_tab_';

    const TYPE_PRODUCT_DEFAULT = 0;
    const TYPE_PRODUCT_AWZ = 1;
    const TYPE_CAMPAIGN = 1;
    const TYPE_SEMITEST = 2;

    const TOPIC_SEMITEST_DEFAULT = 'Semitest';

    const JSON_COLUMNS = [
        'meta_tags',
        'options',
        'meta_data',
        'seo_tags',
        'addon_service_data',
        'additional_data'
    ];

    const STATUS_PRODUCT_PREVIEW = [
        'DISABLE' => 0,
        'ENABLE' => 1
    ];

    /**
     *
     * @var Model_Catalog_Collection
     */
    protected $_catalog_collection = null;

    /**
     *
     * @var Model_Catalog_Product_Collection
     */
    protected $_product_collection = null;

    /**
     *
     * @var Model_Catalog_Product_Image_Collection
     */
    protected $_mockup_collection = null;
    protected $_image_collection = null;
    protected $_video_collection = null;

    protected $_image_array = null;
    protected $_video_array = null;

    /**
     *
     * @var Model_Catalog_Product_Variant_Collection
     */
    protected $_variant_collection = null;

    /**
     *
     * @var Model_Catalog_Collection_Collection
     */
    protected $_collection_collection = null;

    /**
     *
     * @var integer
     */
    protected $_total_variant = null;
    protected $_buy_design_price = null;

    protected $_list_design_id_in_print_template = null;

    public function getHasTabCachePrefix() {
        return self::HAS_TAB_PREFIX;
    }

    /**
     *
     * @return bool
     */
    public function isAvailable(): bool {
        return $this->data['discarded'] == 0;
    }

    protected $_selected_or_first_available_variant = null;

    /**
     * @param bool $reload
     * @param string $country_code
     * @param string $province_code
     * @return Model_Catalog_Product_Variant
     * @throws OSC_Exception_Runtime
     */
    public function getSelectedOrFirstAvailableVariant($reload = false, $country_code = '', $province_code = '') {
        if ($this->_selected_or_first_available_variant === null) {
            $current_variant_id = intval(OSC::core('request')->get('variant', OSC::registry('catalog/current/variant_id') ?? 0));

            $list_variants = $this->getVariants($reload, true);
            $list_product_variants = [];

            if ($list_variants->length() > 0) {
                foreach ($list_variants as $variant) {
                    $list_product_variants[] = $variant;
                }
            }

            //Reorder list_product_variants if product is campaign and flag is_reorder = 1
            if ($this->isCampaignMode() &&
                !empty($this->data['meta_data']['campaign_config']['is_reorder']) &&
                empty($current_variant_id)
            ) {
                if ((!empty($country_code) || !empty($province_code))) {
                    $product_variant = $this->getSelectedOrFirstAvailableCampaignVariant($country_code, $province_code);
                    $current_variant_id = $product_variant['product_variant_id'];
                } else {
                    $this->_sortVariantsByPosition($list_product_variants);
                }
            }

            if ($this->isSemitestMode() && empty($current_variant_id)) {
                $this->_sortVariantsByPosition($list_product_variants);
            }

            /* Find selected or first available variant */
            foreach ($list_product_variants as $variant) {
                if ($variant->ableToOrder()) {
                    if (!$this->_selected_or_first_available_variant) {
                        $this->_selected_or_first_available_variant = $variant;

                        if ($current_variant_id < 1) {
                            break;
                        }
                    } else {
                        if ($variant->getId() == $current_variant_id) {
                            $this->_selected_or_first_available_variant = $variant;
                            break;
                        }
                    }
                }
            }
        }

        return $this->_selected_or_first_available_variant;
    }

    protected function _sortVariantsByPosition(&$list_product_variants) {
        usort($list_product_variants, function ($a, $b) {
            $position_a = isset($a->data['position']) ? $a->data['position'] : $a['position'];
            $position_b = isset($b->data['position']) ? $b->data['position'] : $b['position'];

            if ($position_a == 0) {
                return 1;
            }
            if ($position_b == 0) {
                return -1;
            }
            if ($position_a == $position_b) {
                return 0;
            }
            return ($position_a < $position_b) ? -1 : 1;
        });
    }

    protected $_selected_or_first_available_campaign_variant = null;

    /**
     * @throws OSC_Exception_Runtime
     */
    public function getSelectedOrFirstAvailableCampaignVariant($country_code = '', $province_code = '') {
        if ($this->_selected_or_first_available_campaign_variant === null) {
            $cart_form_config = $this->getCartFrmOptionConfig($country_code, $province_code);
            $list_product_variants = $cart_form_config['product_variants'];

            $this->_selected_or_first_available_campaign_variant = array_values($list_product_variants)[0];

            $list_product_types = $cart_form_config['product_types'];
            $list_product_type_keys = array_values(array_map(function ($item) {
                return $item['ukey'];
            }, $list_product_types));

            //Check index of first variant's product_type in list_product_type
            //If non-exists, return first variant with first product_type in list_product_type
            //If exist, get first variant with index product_type in list_product_type = 0
            $return_variant_index = array_search(
                $this->_selected_or_first_available_campaign_variant['product_type_ukey'],
                $list_product_type_keys
            );

            if ($return_variant_index !== 0) {
                foreach ($list_product_variants as $product_variant) {
                    if (array_search($product_variant['product_type_ukey'], $list_product_type_keys) == 0) {
                        $this->_selected_or_first_available_campaign_variant = $product_variant;
                        break;
                    }
                }
            }
        }

        return $this->_selected_or_first_available_campaign_variant;
    }

    /* TODO remove this function */
    protected $_lowest_price_variant = null;

    public function getLowestPriceAvailableVariant() {
        if ($this->_lowest_price_variant === null) {
            $return_variant = null;

            $list_product_variant = $this->getVariants();

            $list_product_type_variant_ids = [];
            foreach ($list_product_variant as $product_variant) {
                if (isset($product_variant->data['meta_data']['campaign_config']['product_type_variant_id']) && !empty($product_variant->data['meta_data']['campaign_config']['product_type_variant_id'])) {
                    $list_product_type_variant_ids[] = $product_variant->data['meta_data']['campaign_config']['product_type_variant_id'];
                }
            }

            if (!empty($list_product_type_variant_ids)) {
                $list_product_type_variant = OSC::model('catalog/productType_variant')
                    ->getCollection()
                    ->addField('id', 'price', 'compare_at_price')
                    ->addCondition('id', $list_product_type_variant_ids, OSC_Database::OPERATOR_IN)
                    ->setLimit(count($list_product_type_variant_ids))
                    ->load()->toArray();

                foreach ($list_product_variant as $variant) {
                    if ($variant->ableToOrder()) {
                        $key = array_search($variant->data['meta_data']['campaign_config']['product_type_variant_id'], array_column($list_product_type_variant, 'id'));

                        $product_type_variant = $key !== false && isset($list_product_type_variant[$key]) ? $list_product_type_variant[$key] : [];
                        $variant->setData([
                            'price' => $product_type_variant['price'] ?? 0,
                            'compare_at_price' => $product_type_variant['compare_at_price'] ?? 0,
                        ]);

                        if (!$return_variant) {
                            if (isset($variant->data['price']) && !empty($variant->data['price'])) {
                                $return_variant = $variant;
                            }
                        } else {
                            if ($variant->data['price'] < $return_variant->data['price']) {
                                $return_variant = $variant;
                            }
                        }
                    }
                }
            }

            $this->_lowest_price_variant = $return_variant;
        }

        return $this->_lowest_price_variant;
    }

    public function hasOnlyDefaultVariant() {
        foreach ($this->data['options'] as $option) {
            if (is_array($option)) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * @param type $catalog_collection
     * @return $this
     */
    public function setCatalogCollection($catalog_collection = null) {
        $this->_catalog_collection = $catalog_collection;
        return $this;
    }

    /**
     * @return class|Model_Catalog_Collection|OSC_Database_Model
     */
    public function getCatalogCollection() {
        if ($this->_catalog_collection === null) {
            $this->_catalog_collection = OSC::model('catalog/collection');
            $this->_catalog_collection->bind(['title' => 'All products'], false)->lock();
        }

        return $this->_catalog_collection;
    }

    public function collectionAdd($collection_ids) {
        if ($this->getId() < 1) {
            throw new Exception('Produc is not loaded');
        }

        if (!is_array($collection_ids)) {
            $collection_ids = array($collection_ids);
        }

        $collection_ids = array_map(function($collection_id) {
            return intval($collection_id);
        }, $collection_ids);

        $collection_ids = array_filter($collection_ids, function($collection_id) {
            return $collection_id > 0;
        });

        $collection_ids = array_unique($collection_ids);

        if (count($collection_ids) < 1) {
            throw new Exception('Collection ID is empty');
        }

        try {
            $collection_list = OSC::model('catalog/collection')->getCollection()->load($collection_ids);

            $collection_ids = [];

            foreach ($collection_list as $collection) {
                if ($collection->data['collect_method'] == Model_Catalog_Collection::COLLECT_MANUAL) {
                    $collection_ids[] = $collection->getId();
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        if (count($collection_ids) < 1) {
            throw new Exception('Collection ID is empty');
        }

        $query_id = 'update_catalog_product_collection_ids';

        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        $value = 'collection_ids';

        foreach ($collection_ids as $collection_id) {
            $value = "CONCAT(@tmpVar:={$value}, IF(LOCATE(',{$collection_id},', @tmpVar) > 0, '', IF(@tmpVar = '', ',{$collection_id},','{$collection_id},')))";
        }

        $DB->query("UPDATE {$this->getTableName(true)} SET collection_ids = {$value} WHERE product_id = {$this->getId()} LIMIT 1", null, $query_id);

        if ($DB->getNumAffected($query_id) > 0) {
            $this->resetCache()->reload();
        }

        return $collection_ids;
    }

    public function collectionRemove($collection_ids) {
        if ($this->getId() < 1) {
            throw new Exception('Produc is not loaded');
        }

        if (!is_array($collection_ids)) {
            $collection_ids = array($collection_ids);
        }

        $collection_ids = array_map(function($collection_id) {
            return intval($collection_id);
        }, $collection_ids);

        $collection_ids = array_filter($collection_ids, function($collection_id) {
            return $collection_id > 0;
        });

        $collection_ids = array_unique($collection_ids);

        if (count($collection_ids) < 1) {
            throw new Exception('Collection ID is empty');
        }

        $query_id = 'update_catalog_product_collection_ids';

        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        $value = 'collection_ids';

        foreach ($collection_ids as $collection_id) {
            $value = "REPLACE(@tmpVar:={$value}, ',{$collection_id},', IF(@tmpVar = ',{$collection_id},', '',','))";
        }

        $DB->query("UPDATE {$this->getTableName(true)} SET collection_ids = {$value} WHERE product_id = {$this->getId()} LIMIT 1", null, $query_id);

        if ($DB->getNumAffected($query_id) > 0) {
            $this->resetCache()->reload();
        }

        return $collection_ids;
    }

    public function getCampaignData() {
        if (!$this->isCampaignMode()) {
            return null;
        }

        return $this->data['meta_data']['campaign_config'];
    }

    public function getCampaignSku($product_type, $options) {
        $sku = $this->data['sku'] . '/' . $product_type;

        if (!empty($options)) {
            foreach ($options as $option_key => $option_value_key) {
                $sku .= '/' . $option_value_key;
            }
        }

        return $sku;
    }

    public function isSemitestMode() {
        return $this->data['selling_type'] == self::TYPE_SEMITEST;
    }

    public function isCampaignMode() {
        return $this->data['selling_type'] == self::TYPE_CAMPAIGN;
    }

    public function isPhotoUploadMode() {
        $flag_mode = $this->registry('photo_upload_mode');

        if ($flag_mode !== null) {
            return $flag_mode;
        }

        $design_ids = $this->getDesignIds();

        if (count($design_ids) < 1) {
            $this->register('photo_upload_mode', 0);
            return false;
        }

        $str_design_ids = implode(',', array_unique($design_ids));

        $cache_key = __FUNCTION__ . "|product_id:,{$this->getId()},|design_ids:,{$str_design_ids},|";
        $cache = OSC::core('cache')->get($cache_key);
        if ($cache) {
            $is_upload_mode = (int) $cache;
        } else {
            $preload_upload_mode = OSC::helper('catalog/campaign')->getPreloadUploadMode();
            $flag_query = false;
            $is_upload_mode = 0;
            foreach ($design_ids as $design_id) {
                if (!isset($preload_upload_mode[$design_id])) {
                    $flag_query = true;
                    break;
                }
                if ($preload_upload_mode[$design_id] > 0) {
                    $is_upload_mode = 1;
                }
            }

            if ($flag_query) {
                $this->getReadAdapter()->select(
                    'design_id',
                    'personalized_design',
                    'design_id IN (' . $str_design_ids . ') AND is_uploader = 1',
                    null,
                    1,
                    'fetch_upload_mode'
                );

                $is_upload_mode = $this->getReadAdapter()->rowCount('fetch_upload_mode') > 0 ? 1 : 0;
            }

            OSC::core('cache')->set($cache_key, $is_upload_mode, OSC_CACHE_TIME);
        }

        $this->register('photo_upload_mode', $is_upload_mode);

        return $is_upload_mode;
    }

    public function getDesignIds() {
        $campaign_data = $this->getCampaignData();

        if (!$campaign_data['print_template_config']) {
            return [];
        }

        $design_ids = [];

        foreach ($campaign_data['print_template_config'] as $print_template_config) {
            foreach ($print_template_config['segments'] as $segment) {
                if ($segment['source']['type'] == 'personalizedDesign') {
                    $design_ids[] = intval($segment['source']['design_id']);
                }
            }
        }

        return $design_ids;
    }

    protected $_print_template = null;

    public function getPrintTemplates() {
        if ($this->_print_template === null) {
            $result = [];
            if (isset($this->data['meta_data']['campaign_config']['print_template_config']) &&
                is_array($this->data['meta_data']['campaign_config']['print_template_config']) &&
                !empty($this->data['meta_data']['campaign_config']['print_template_config'])
            ) {
                foreach ($this->data['meta_data']['campaign_config']['print_template_config'] as $print_template_config) {
                    if (isset($print_template_config['print_template_id'])) {
                        $result[] = $print_template_config['print_template_id'];
                    }
                }
            }

            $this->_print_template = $result;
        }

        return $this->_print_template;
    }

    /* TODO remove this function */
    public function getAvailableProductVariantByCountry($country_code = '', $province_code = '', $flag_feed = false) {
        $result = [];
        $list_product_variants = $this->getVariants(false, true);

        $product_type_variant_ids = [];

        if ($list_product_variants->length() > 0) {
            foreach ($list_product_variants as $product_variant) {
                if (isset($product_variant->data['meta_data']['campaign_config']['product_type_variant_id']) && !empty($product_variant->data['meta_data']['campaign_config']['product_type_variant_id'])) {
                    $product_type_variant_ids[] = $product_variant->data['meta_data']['campaign_config']['product_type_variant_id'];
                }
            }

            if (empty($product_type_variant_ids)) {
                return [];
            }

            $list_filter_product_type_variant_id = OSC::helper('supplier/location')->getPrintTemplateForCustomer($product_type_variant_ids, $country_code, $province_code, $flag_feed);

            //Query list print_template_id from available $list_filter_product_type_variant_id
            $print_template_ids = array_unique(array_merge(...array_column($list_filter_product_type_variant_id, 'print_template_id')));

            $list_replaceable_print_template = !empty($print_template_ids) ? OSC::helper('catalog/campaign')->findReplaceablePrintTemplate($print_template_ids) : [];
            if (!empty($list_replaceable_print_template)) {
                //Append list replaceable print_template to $list_filter_product_type_variant_id
                $list_filter_product_type_variant_id = array_map(function ($item) use ($list_replaceable_print_template) {
                    $list_append_print_template_id = [];
                    foreach ($item['print_template_id'] as $print_template_id) {
                        $list_append_print_template_id = array_merge($list_append_print_template_id, $list_replaceable_print_template[$print_template_id]);
                    }

                    if (!empty($list_append_print_template_id)) {
                        $item['print_template_id'] = array_unique(array_merge($item['print_template_id'], $list_append_print_template_id));
                    }

                    return $item;
                }, $list_filter_product_type_variant_id);
            }

            foreach ($list_product_variants as $product_variant) {
                $key = array_search($product_variant->data['meta_data']['campaign_config']['product_type_variant_id'], array_column($list_filter_product_type_variant_id, 'product_type_variant_id'));

                $list_print_template_ids = $key !== false && isset($list_filter_product_type_variant_id[$key]['print_template_id']) ? $list_filter_product_type_variant_id[$key]['print_template_id'] : [];

                $print_template_id = 0;
                //Find print_template_id existed in product's campaign config
                foreach ($list_print_template_ids as $item) {
                    if (array_search($item, array_column($this->data['meta_data']['campaign_config']['print_template_config'], 'print_template_id')) !== false) {
                        $print_template_id = $item;
                        break;
                    }
                }

                if (isset($product_variant->data['meta_data']['campaign_config']['product_type_variant_id']) && !empty($product_variant->data['meta_data']['campaign_config']['product_type_variant_id']) && !empty($print_template_id)) {
                    $product_variant_price = $product_variant->getPriceForCustomer();
                    $product_variant->setData([
                        'price' => $product_variant_price['price'],
                        'compare_at_price' => $product_variant_price['compare_at_price']
                    ]);

                    $result[] = $product_variant;
                }
            }
        }

        usort($result, function ($a, $b) {
            return $a->data['price'] > $b->data['price'];
        });

        return $result;
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    public function getCartFrmOptionConfig($country_code = '', $province_code = '', $options = []) {
        static $cached = [];
        $flag_feed = isset($options['flag_feed']) && $options['flag_feed'];
        $flag_url_from_feed = isset($options['flag_url_from_feed']) && $options['flag_url_from_feed'];

        $cache_key = $this->getId() . '-' . $country_code . '-' . $province_code . ':' . ($flag_feed ? 1 : 0);

        if (isset($cached[$cache_key])) {
            return $cached[$cache_key];
        }

        $use_cache = !$flag_feed;
        $list_variants = $this->getVariants($flag_feed, $use_cache);

        $result = [
            'product_types' => [],
            'product_variants' => [],
            'options' => [],
            'option_values' => [],
            'keys' => [],
            'images' => []
        ];

        $option_ids = [];
        $option_value_ids = [];
        $product_type_ids = [];
        $product_type_variant_ids = [];
        $product_type_description_ids = [];
        $list_product_variants = [];
        $is_reorder_variant = !empty($this->data['meta_data']['campaign_config']['is_reorder']);
        $skip_ab_test_price = $options['atp'] === 1 ?? false;

        if ($list_variants->length() > 0) {
            foreach ($list_variants as $variant) {
                $list_product_variants[] = $variant;
            }

            //Reorder list_product_variants if flag is_reorder = 1
            if ($is_reorder_variant) {
                $this->_sortVariantsByPosition($list_product_variants);
            }

            foreach ($list_product_variants as $product_variant) {
                if (isset($product_variant->data['product_type_variant_id']) &&
                    !empty($product_variant->data['product_type_variant_id'])
                ) {
                    $product_type_variant_ids[] = $product_variant->data['product_type_variant_id'];
                }
            }

            $preload_product_type_variants = OSC::helper('catalog/campaign')->getPreloadProductTypeVariant($product_type_variant_ids);
            if ($preload_product_type_variants->length()) {
                foreach ($preload_product_type_variants as $product_type_variant) {
                    if (!in_array($product_type_variant->data['product_type_id'], $product_type_ids)) {
                        $product_type_ids[] = $product_type_variant->data['product_type_id'];
                    }

                    if (!in_array($product_type_variant->data['description_id'], $product_type_description_ids)) {
                        $product_type_description_ids[] = $product_type_variant->data['description_id'];
                    }
                }
            }

            $product_type_id_deactive = OSC::helper('catalog/productType')->getProductTypeIdDeactive();

            foreach ($product_type_ids as $key => $product_type_id) {
                if (in_array($product_type_id, $product_type_id_deactive)) {
                    unset($product_type_ids[$key]);
                }
            }


            $preload_product_types = OSC::helper('catalog/campaign')->getPreloadProductType($product_type_ids);
            $preload_packs = OSC::helper('catalog/campaign')->getPreloadPacksByProductTypes($product_type_ids);

            if ($preload_product_types->length()) {
                foreach ($preload_product_types as $product_type) {
                    if (!in_array($product_type->data['description_id'], $product_type_description_ids)) {
                        $product_type_description_ids[] = $product_type->data['description_id'];
                    }
                }
            }
            $preload_product_type_descriptions = OSC::helper('catalog/campaign')->getPreloadProductTypeDescription($product_type_description_ids);

            $skip_auth = isset($options['skip_auth']);

            //Query list product_type_variant_id map with list print_template by location
            $list_filter_product_type_variant_id = OSC::helper('supplier/location')->getPrintTemplateForCustomer($product_type_variant_ids, $country_code, $province_code, $flag_feed, $skip_auth);

            //Query list print_template_id from available $list_filter_product_type_variant_id
            $print_template_ids = array_unique(array_merge(...array_column($list_filter_product_type_variant_id, 'print_template_id')));

            $list_replaceable_print_template = !empty($print_template_ids) ? OSC::helper('catalog/campaign')->findReplaceablePrintTemplate($print_template_ids, true) : [];

            if (!empty($list_replaceable_print_template)) {
                //Append list replaceable print_template to $list_filter_product_type_variant_id
                $list_filter_product_type_variant_id = array_map(function ($item) use ($list_replaceable_print_template) {
                    $list_append_print_template_id = [];
                    foreach ($item['print_template_id'] as $print_template_id) {
                        $list_append_print_template_id = array_merge($list_append_print_template_id, $list_replaceable_print_template[$print_template_id]);
                    }

                    if (!empty($list_append_print_template_id)) {
                        $item['print_template_id'] = array_unique(array_merge($item['print_template_id'], $list_append_print_template_id));
                    }

                    return $item;
                }, $list_filter_product_type_variant_id);
            }

            $print_template_ids = array_unique(array_merge(...array_column($list_filter_product_type_variant_id, 'print_template_id')));
            $list_print_template = OSC::helper('catalog/campaign')->getPrintTemplate($print_template_ids);
            $list_images = $this->getArrayImage($flag_feed);

            /* @var $product_variant Model_Catalog_Product_Variant */
            foreach ($list_product_variants as $product_variant) {
                $key = array_search($product_variant->data['meta_data']['campaign_config']['product_type_variant_id'], array_column($list_filter_product_type_variant_id, 'product_type_variant_id'));

                $list_print_template_ids = $key !== false && isset($list_filter_product_type_variant_id[$key]['print_template_id']) ? $list_filter_product_type_variant_id[$key]['print_template_id'] : [];

                $print_template_id = 0;
                //Find print_template_id existed in product's campaign config
                foreach ($list_print_template_ids as $item) {
                    if (array_search($item, array_column($this->data['meta_data']['campaign_config']['print_template_config'], 'print_template_id')) !== false) {
                        $print_template_id = $item;
                        break;
                    }
                }

                if (isset($product_variant->data['meta_data']['campaign_config']['product_type_variant_id']) && !empty($product_variant->data['meta_data']['campaign_config']['product_type_variant_id']) && !empty($print_template_id)) {
                    $print_template = $list_print_template[array_search($print_template_id, array_column($list_print_template, 'id'))];

                    $product_type_variant = $preload_product_type_variants->getItemByPK($product_variant->data['product_type_variant_id']);
                    $product_type = $preload_product_types->getItemByPK($product_type_variant->data['product_type_id']);

                    if (!$product_type) {
                        continue;
                    }

                    $product_type_key = $flag_feed ? $product_type->data['ukey'] : '_' . $product_type->data['id'];

                    $product_variant_price = $product_variant->getPriceForCustomer(
                        $country_code,
                        $flag_feed || $flag_url_from_feed,
                        $skip_ab_test_price,
                        $preload_product_type_variants
                    );

                    if (isset($product_type_key) && !empty($product_type_key) && empty($result['product_types'][$product_type_key])) {
                        $product_type_description = $preload_product_type_descriptions
                            ->getItemByPK($product_type->data['description_id'])
                            ->data['description'];

                        $result['product_types'][$product_type_key] = [
                            'id' => $product_type->data['id'],
                            'group_name' => $product_type->data['group_name'],
                            'title' => $product_type->data['title'],
                            'custom_title' => $product_type->data['custom_title'],
                            'description_id' => $product_type->data['description_id'],
                            'description' => $product_type_description,
                            'image' => OSC_CMS_BASE_URL . '/resource/template/core/image/' . $product_type->data['image'],
                            'ukey' => $product_type->data['ukey'],
                            'list_option_value' => [],
                            'options' => [],
                            'product_type_option_ids' => $product_type->data['product_type_option_ids'],
                            'size_guide_data' => $product_type->data['size_guide_data']
                        ];
                    }

                    $product_type_variant_description = $preload_product_type_descriptions
                        ->getItemByPK($product_type_variant->data['description_id'])
                        ->data['description'];

                    $videos = $product_variant->getVideos()->toArray();
                    $video_ids = array_column($videos, 'id');
                    $video_positions = isset($product_variant->data['meta_data']['video_config']['position'])
                        ? array_values($product_variant->data['meta_data']['video_config']['position'])
                        : [];

                    $data = [
                        'product_type' => $product_type->data['id'],
                        'product_type_ukey' => $product_type->data['ukey'],
                        'position' => $product_variant->data['position'],
                        'product_variant_id' => $product_variant->data['id'],
                        'product_type_variant_id' => $product_variant->data['product_type_variant_id'],
                        'sku' => $product_variant->data['sku'],
                        'weight_unit' => $product_variant->data['weight_unit'],
                        'weight' => $product_variant->data['weight'],
                        'description_id' => $product_type_variant->data['description_id'],
                        'description' => $product_type_variant_description,
                        'title' => $product_type_variant->data['title'],
                        'custom_title' => $product_type_variant->data['custom_title'],
                        'ukey' => $product_type_variant->data['ukey'],
                        'option_values' => [],
                        'price' => $product_variant_price['price'],
                        'compare_at_price' => $product_variant_price['compare_at_price'],
                        'options' => [],
                        'print_template_id' => $print_template_id,
                        'images' => [],
                        'video_ids' => $video_ids,
                        'video_position' => $video_positions,
                        'preview_config' => [],
                        'segments' => [],
                        'print_file' => [],
                    ];

                    //Prepare list pair option - option_value and list_option_value for each product_type_variant
                    if (!empty($product_type_variant->data['ukey'])) {
                        $ukey = explode('/', $product_type_variant->data['ukey']);

                        if (isset($ukey[1]) && !empty($ukey[1]) && !in_array($ukey[1], $result['product_variants'])) {
                            $result['keys'][] = $ukey[1];
                        }

                        $ukey = isset($ukey[1]) && !empty($ukey[1]) ? explode('_', $ukey[1]) : [];

                        if (!empty($ukey)) {
                            foreach ($ukey as $item) {
                                $item = explode(':', $item);

                                if (isset($item[1]) && !empty($item[1])) {
                                    if (!in_array($item[1], $option_value_ids)) {
                                        $option_value_ids[] = $item[1];
                                    }

                                    if (isset($item[0]) && !empty($item[0]) && !in_array($item[0], $option_ids)) {
                                        $option_ids[] = $item[0];
                                    }

                                    if (!in_array(intval($item[1]), $result['product_types'][$product_type_key]['list_option_value'])) {
                                        $result['product_types'][$product_type_key]['list_option_value'][] = intval($item[1]);
                                    }

                                    $data['options'][$item[0]] = $item[1];
                                }
                            }
                        }
                    }

                    foreach ($product_variant->data['meta_data']['campaign_config']['image_ids'] as $image_item) {
                        if (isset($image_item['print_template_id']) && $image_item['print_template_id'] == $print_template_id && isset($image_item['image_ids']) && !empty($image_item['image_ids'])) {
                            foreach ($image_item['image_ids'] as $image_id) {
                                $image = $list_images[$image_id];

                                $data['images'][] = [
                                    'id' => intval($image['id']) ?? 0,
                                    'url' => $image['url'],
                                    'position' => $image['position'],
                                    'flag_main' => $image['flag_main'],
                                    'is_static_mockup' => $image['is_static_mockup']
                                ];
                            }

                            usort($data['images'], function ($a, $b) {
                                if (isset($a['position']) && isset($b['position'])) {
                                    return $a['position'] > $b['position'];
                                } else {
                                    return 0;
                                }
                            });
                            break;
                        }
                    }

                    $data['preview_config'] = $print_template['config']['preview_config'];
                    $data['segments'] = $print_template['config']['segments'];

                    if (is_array($print_template['config']['preview_config_3d']) && count($print_template['config']['preview_config_3d']) > 0) {
                        $data['preview_config_3d'] = [];

                        $image_core = 'resource/template/core/image';

                        if (isset($print_template['config']['preview_config_3d']['show_model_by_product_variant']) && $print_template['config']['preview_config_3d']['show_model_by_product_variant'] == 1 && $print_template['config']['preview_config_3d']['config'][$product_variant->data['product_type_variant_id']]) {
                            foreach ($print_template['config']['preview_config_3d']['config'][$product_variant->data['product_type_variant_id']] as $key => $value) {
                                $data['preview_config_3d'][$key] = OSC_CMS_BASE_URL . '/' . $image_core . '/' . $value;
                            }
                        } else {
                            foreach ($print_template['config']['preview_config_3d'] as $key => $value) {
                                $data['preview_config_3d'][$key] = OSC_CMS_BASE_URL . '/' . $image_core . '/' . $value;
                            }
                        }
                    }

                    $result['product_variants'][$data['ukey']] = $data;
                }
            }

            //Prepare data for options
            if (!empty($option_ids)) {
                foreach (OSC::helper('catalog/campaign')->getProductTypeOption($option_ids) as $option) {
                    $result['options'][$option['id']] = [
                        'id' => $option['id'],
                        'title' => $option['title'],
                        'ukey' => $option['ukey'],
                        'type' => $option['type'],
                        'is_show_option' => $option['is_show_option']
                    ];
                }
            }

            uasort($result['options'], function ($a, $b) use ($option_ids) {
                $position_a = array_search($a['id'], $option_ids);
                $position_b = array_search($b['id'], $option_ids);

                return $position_a > $position_b;
            });

            //Prepare data for option values
            if (!empty($option_value_ids)) {
                foreach (OSC::helper('catalog/campaign')->getProductTypeOptionValue($option_value_ids) as $option_value) {
                    $result['option_values'][$option_value['id']] = array_merge([
                        'id' => $option_value['id'],
                        'product_type_option_id' => $option_value['product_type_option_id'],
                        'title' => $option_value['title'],
                        'ukey' => $option_value['ukey'],
                        //If reorder product_variant, remove reorder option_value
                        'position' => !$is_reorder_variant ? $option_value['position'] : 0
                    ], $option_value['meta_data'] ?? []);
                }
            }

            uasort($result['option_values'], function ($a, $b) use ($option_value_ids) {
                $position_a = array_search($a['id'], $option_value_ids);
                $position_b = array_search($b['id'], $option_value_ids);

                return $position_a > $position_b;
            });

            //Reorder product_type by field osc_catalog_product.product_type
            $setting_product_types = explode(',', $this->data['product_type']);
            $setting_product_types = array_map(function ($item) {
                return trim($item);
            }, $setting_product_types);

            uasort($result['product_types'], function ($a, $b) use ($setting_product_types) {
                $position_a = array_search($a['ukey'], $setting_product_types);
                $position_b = array_search($b['ukey'], $setting_product_types);

                return $position_a > $position_b;
            });

            //Prepare data options for each product type
            foreach ($result['product_types'] as &$product_type) {
                if (isset($product_type['product_type_option_ids']) && !empty($product_type['product_type_option_ids'])) {
                    $list_option = explode(',', $product_type['product_type_option_ids']);

                    $last_option = 0;
                    foreach ($list_option as $option) {
                        if (in_array($option, $option_ids)) {
                            $list_option_value = $product_type['list_option_value'];
                            $option_values = array_filter($result['option_values'], function ($item) use ($option, $list_option_value) {
                                return $item['product_type_option_id'] == $option && in_array($item['id'], $list_option_value);
                            });

                            $product_type['options'][] = [
                                'last_option' => intval($last_option),
                                'id' => intval($option),
                                'option_values' => array_values(array_map(function ($item) {
                                    return $item['id'];
                                }, $option_values))
                            ];

                            $last_option = intval($option);
                        }
                    }
                }

                $product_packs = $preload_packs[$product_type['id']];
                $product_type['packs'] = [];
                if (!empty($product_packs)) {
                    $flag_regular = true;
                    foreach ($product_packs as $product_pack) {
                        if ($product_pack['quantity'] === 1 || $product_pack['is_pack_auto'] === 0) {
                            $flag_regular = false;
                        }

                        $product_type['packs'][] = [
                            'id' => $product_pack['id'],
                            'title' => $product_pack['title'],
                            'quantity' => $product_pack['quantity'],
                            'discount_type' => $product_pack['discount_type'],
                            'discount_value' => $product_pack['discount_value'],
                            'note' => $product_pack['note']
                        ];
                    }

                    if ($flag_regular) {
                        array_unshift($product_type['packs'], [
                            'id' => 0,
                            'title' => 'Pack 1',
                            'quantity' => 1,
                            'discount_type' => 0,
                            'discount_value' => 0,
                            'note' => ''
                        ]);
                    }
                }
            }

            //Prepare data options and fetch preview_config's layer for each product type variant
            foreach ($result['product_variants'] as &$product_variant) {
                if (!empty($product_variant['options'])) {
                    $replace_key = []; $replace_value = [];
                    foreach ($product_variant['options'] as $option_id => &$option_value_id) {
                        $option_value_id = (int) $option_value_id;
                        $option = array_key_first(array_filter($result['options'], function ($item) use ($option_id) {
                            return $item['id'] == $option_id;
                        }));

                        $selected_option = $result['options'][$option];

                        $option = $selected_option['ukey'] ?? '';

                        $option_value = array_key_first(array_filter($result['option_values'], function ($item) use ($option_value_id) {
                            return $item['id'] == $option_value_id;
                        }));

                        $selected_option_value = $result['option_values'][$option_value];

                        $option_value = $selected_option_value['ukey'] ?? '';

                        if (!empty($option) && !empty($option_value)) {
                            $replace_key[] = '{opt.' . $option . '}';
                            $replace_value[] = str_replace($option . '/', '', $option_value);
                        }

                        $product_variant['option_values'][] = [
                            'option_id' => $option_id,
                            'option_value' => $option_value_id,
                            'option_key' => $option,
                            'option_value_key' => $option_value,
                            'type' => $selected_option['type'] ?? '',
                            'title' => $selected_option['title'] ?? '',
                            'value' => $selected_option_value['title'] ?? ''
                        ];
                    }

                    if (!empty($replace_key) && !empty($replace_value)) {
                        foreach ($product_variant['preview_config'] as &$preview_config) {
                            if (isset($preview_config['layer']) && !empty($preview_config['layer'])) {
                                foreach ($preview_config['layer'] as &$layer) {
                                    if ($layer !== 'main') {
                                        $layer = str_replace($replace_key, $replace_value, $layer);
                                    }
                                }
                            }
                        }

                        if (is_array($product_variant['preview_config_3d']) && count($product_variant['preview_config_3d']) > 0) {
                            foreach ($product_variant['preview_config_3d'] as $key => &$preview_config_3d) {
                                $preview_config_3d = str_replace($replace_key, $replace_value, $preview_config_3d);
                            }
                        }
                    }
                }
            }
        }

        $cached[$cache_key] = $result;

        return $cached[$cache_key];
    }

    public function getCartFrmOptionConfigSemitest($options = []) {
        static $cached = [];

        $cache_key = $this->getId() . '-semitest';

        if (isset($cached[$cache_key])) {
            return $cached[$cache_key];
        }

        $product_variants = $this->getVariants(false, true);

        $list_product_variants = [];

        foreach ($product_variants as $variant) {
            $list_product_variants[] = $variant;
        }

        $this->_sortVariantsByPosition($list_product_variants);

        $result = [
            'product_variants' => [],
            'options' => array_values($this->getOrderedOptions(true)),
            'option_values' => []
        ];

        $skip_ab_test_price = $options['atp'] === 1 ?? false;

        $data_shipping_semitest = OSC::helper('catalog/product')->getPriceShippingSemitest();
        $shipping_price = $data_shipping_semitest['shipping_price'] ?? 0;
        $shipping_plus_price = $data_shipping_semitest['shipping_plus_price'] ?? 0;

        foreach ($list_product_variants as $variant) {
            $images = [];

            foreach ($variant->getImages() as $image) {
                $images[] = $image->getId();
            }

            $videos = $variant->getVideos()->toArray();
            $video_ids = [];

            foreach ($videos as $video) {
                $video_ids[] = $video['id'];
            }

            $option_value = [];
            foreach ($variant->getOptions() as $option) {
                $option_value[] = $option['value'];
            }

            $value = (count($option_value) > 0) ? implode('||', $option_value) : '';

            $price_data = $variant->getPriceForCustomer('', false, $skip_ab_test_price);
            $video_positions = isset($variant->data['meta_data']['video_config']['position'])
                ? array_values($variant->data['meta_data']['video_config']['position'])
                : [];

            $result['product_variants'][$value] = [
                'option_value' => $value,
                'product_variant_id' => $variant->getId(),
                'personalize_design_id' => count($variant->data['design_id']) > 0 ? $variant->data['design_id'] : [],
                'images' => count($images) > 0 ? $images : [],
                'video_ids' => $video_ids,
                'video_position' => $video_positions,
                'price' => $price_data['price'],
                'compare_at_price' => $price_data['compare_at_price'],
                'shipping_price' => $shipping_price,
                'shipping_plus_price' => $shipping_plus_price,
                'ukey' => $variant->data['sku']
            ];

        }

        $cached[$cache_key] = $result;

        return $cached[$cache_key];
    }

    /**
     * getListProductTagsWithoutRootTag
     *
     * @param  mixed $return_string
     * @param  mixed $clean_alias
     * @return void
     */
    public function getListProductTagsWithoutRootTag($return_string = false, $clean_alias = false) {
        $list_tags = [];
        $tags = OSC::model('filter/tag')->getCollection()->load(array_values($this->getProductTagSelected()));

        foreach ($tags as $tag) {
            if ($tag->data['parent_id'] == 0) continue;
            $title = $tag->data['title'];

            if ($clean_alias) {
                $collection_title = str_replace('-', '_', $title);
                $list_tags[] = OSC::core('string')->cleanAliasKey($title, '_');
            } else {
                $list_tags[] = $title;
            }
        }

        if ($return_string) {
            return implode(', ', $list_tags);
        }
        return $list_tags;
    }

    public function setImages($image_collection) {
        if ($this->_image_collection === null) {
            $this->_image_collection = $image_collection;
        }

        return $this;
    }

    protected $_array_images = null;
    public function setArrayImages($array_images) {
        if ($this->_array_images === null) {
            $this->_array_images = $array_images;
        }

        return $this;
    }

    /**
     *
     * @param boolean $reload
     * @return Model_Catalog_Product_Image_Collection
     * @throws OSC_Exception_Runtime
     */
    public function getMockups($reload = false, $useCache = false) {
        if (!$this->_mockup_collection instanceof Model_Catalog_Product_Image_Collection || $reload) {
            $this->_mockup_collection = OSC::model('catalog/product_image')->getCollection();

            $cache_key = "getMockup|product_id:,{$this->getId()},|";
            $cache = OSC::core('cache')->get($cache_key);

            if ($this->getId() > 0) {
                if ($cache !== false && $useCache) {
                    foreach ($cache as $mockup) {
                        $model_mockup = OSC::model('catalog/product_image')->bind($mockup);
                        $this->_mockup_collection->addItem($model_mockup);
                        $model_mockup->setProduct($this);
                    }
                } else {
                    $this->_mockup_collection->loadByProductId($this->getId());

                    $cache_data = [];
                    foreach ($this->_mockup_collection as $model_mockup) {
                        $cache_data[] = $model_mockup->data;
                        $model_mockup->setProduct($this);
                    }

                    if (count($cache_data) > 0) {
                        OSC::core('cache')->set($cache_key, $cache_data, OSC_CACHE_TIME);
                    }
                }
            }
        }

        return $this->_mockup_collection;
    }

    /**
     *
     * @param boolean $reload
     * @return Model_Catalog_Product_Image_Collection
     * @throws OSC_Exception_Runtime
     */
    public function getImages($reload = false, $useCache = false) {
        if (!$this->_image_collection instanceof Model_Catalog_Product_Image_Collection) {
            $this->_image_collection = OSC::model('catalog/product_image')->getCollection();

            $mockups = $this->getMockups($reload, $useCache);

            foreach ($mockups as $mockup_item) {
                if ($mockup_item->data['is_static_mockup'] !== 3) {
                    $this->_image_collection->addItem($mockup_item);
                }
            }
        }

        $using_array_to_load = false;
        foreach ($this->_image_collection as $model) {
            if ($model->getId() < 1) {
                $using_array_to_load = true;
            }
        }
        if ($using_array_to_load) {
            $this->_image_collection = OSC::model('catalog/product_image')->getCollection();

            foreach ($this->_array_images as $data) {
                $image = OSC::model('catalog/product_image')->bind($data);
                $this->_image_collection->addItem($image);
                $image->setProduct($this);
            }
        }

        $this->removeMockupImageNotUsing();

        return $this->_image_collection;
    }

    /**
     *
     * @param boolean $reload
     * @return Model_Catalog_Product_Image_Collection
     * @throws OSC_Exception_Runtime
     */
    public function getVideos($reload = false, $useCache = false) {
        if (!$this->_video_collection instanceof Model_Catalog_Product_Image_Collection) {
            $this->_video_collection = OSC::model('catalog/product_image')->getCollection();

            $mockups = $this->getMockups($reload, $useCache);

            foreach ($mockups as $mockup_item) {
                if ($mockup_item->data['is_static_mockup'] === 3) {
                    $this->_video_collection->addItem($mockup_item);
                }
            }
        }

        return $this->_video_collection;
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    public function removeMockupImageNotUsing() {
        if ($this->_image_collection instanceof Model_Catalog_Product_Image_Collection &&
            $this->_image_collection->length() > 0
        ) {
            if ($this->_list_mockup_remove === null) {
                $cache_key = "preLoadMockupRemove";
                $this->_list_mockup_remove = OSC::core('cache')->get($cache_key);

                if ($this->_list_mockup_remove !== false) {
                    $this->_list_mockup_remove = OSC::model('catalog/printTemplate_mockupRel')
                        ->getCollection()
                        ->getListIdMockupRemove()
                        ->toArray();
                    OSC::core('cache')->set($cache_key, $this->_list_mockup_remove, OSC_CACHE_TIME);
                }
            }

            /* @var $image Model_Catalog_Product_Image */
            foreach ($this->_image_collection as $image) {
                $flag_remove = false;

                $image_ukey = $image->getUkey();
                $ex = explode('_',$image_ukey);

                if (isset($ex[1])) {
                    foreach ($this->_list_mockup_remove as $mockup_rel_id) {
                        if ($ex[0] . '_' .$ex[1] == $this->getId() . '_' . $mockup_rel_id['id']) {
                            $flag_remove = true;
                            break;
                        }
                    }
                }

                if ($flag_remove) {
                    $this->_image_collection->removeItemByKey($image->getId());
                }
            }
        }
    }

    protected $_list_mockup_remove = null;

    public function setMockupRemove($list_mockup_remove) {
        $this->_list_mockup_remove = $list_mockup_remove;

        return $this;
    }

    public function getArrayImage($reload = false) {
        if ($this->_image_array === null || $reload) {
            $this->_image_array = [];

            $list_mockup_remove_collection = OSC::model('catalog/printTemplate_mockupRel')->getCollection()->getListIdMockupRemove()->toArray();

            foreach ($this->getImages($reload)->toArray() as $image) {
                $flag_remove = false;

                $image_ukey = $image['ukey'];
                $ex = explode('_',$image_ukey);

                if (isset($ex[1])) {
                    foreach ($list_mockup_remove_collection as $mockup_rel_id) {
                        if ($ex[0] . '_' .$ex[1] == $this->getId() . '_' . $mockup_rel_id['id']) {
                            $flag_remove = true;
                            break;
                        }
                    }
                }

                if ($flag_remove) {
                    continue;
                }

                $this->_image_array[$image['id']] = [
                    'id' => $image['id'],
                    'ukey' => $image['ukey'],
                    'position' => $image['position'],
                    'flag_main' => $image['flag_main'],
                    'url' => OSC::wrapCDN(OSC::helper('core/image')->imageOptimize($image['url'], 1000, 1000, false)),
                    'alt' => $image['alt'],
                    'width' => $image['width'],
                    'height' => $image['height'],
                    'filename' => $image['filename'],
                    'is_static_mockup' => $image['is_static_mockup']
                ];
            }
        }

        return $this->_image_array;
    }

    public function getArrayVideos($reload = false) {
        if ($this->_video_array === null || $reload) {
            $this->_video_array = [];

            foreach ($this->getVideos($reload)->toArray() as $video) {

                $this->_video_array[$video['id']] = [
                    'id' => $video['id'],
                    'url' => $video['url'],
                    'duration' => $video['duration'],
                    'position' => $video['position'],
                    'thumbnail' => $video['thumbnail'] ? OSC::wrapCDN(OSC::helper('core/image')->imageOptimize($video['thumbnail'], 1000, 1000, false)) : '',
                ];
            }
        }

        return $this->_video_array;
    }

    /**
     *
     * @param integer $total_variant
     * @return $this
     */
    public function setTotalVariant($total_variant) {
        $this->_total_variant = intval($total_variant);
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getTotalVariant() {
        if ($this->_total_variant === null) {
            $this->_total_variant = $this->getVariants(false, true)->length();
        }

        return $this->_total_variant;
    }

    /**
     *
     * @param bool $reload
     * @param bool $useCache
     * @return Model_Catalog_Product_Variant_Collection
     * @throws OSC_Exception_Runtime
     */
    public function getVariants(bool $reload = false, bool $useCache = false): Model_Catalog_Product_Variant_Collection {
        if ($this->_variant_collection === null || $reload) {
            $this->_variant_collection = OSC::model('catalog/product_variant')->getCollection();

            $product_id = $this->getId();
            if ($product_id > 0) {
                $cache_key = "|model.catalog.product_variant|product_id:,{$product_id},|";
                $this->_variant_collection = OSC::model('catalog/product_variant')->getCollection();

                if ($useCache && ($cache = OSC::core('cache')->get($cache_key)) !== false) {
                    foreach ($cache as $item) {
                        $this->_variant_collection->addItem(OSC::model('catalog/product_variant')->bind($item));
                    }
                } else {
                    $DB = OSC::core('database');
                    $DB->select('*', 'product_variant', 'product_id = ' . $product_id, null, null, 'fetch_variant');

                    $cache_data = [];
                    while ($row = $DB->fetchArray('fetch_variant')) {
                        $item = OSC::model('catalog/product_variant')->bind($row);
                        $this->_variant_collection->addItem($item);
                        $cache_data[] = $item->data;
                    }

                    $DB->free('fetch_variant');

                    OSC::core('cache')->set($cache_key, $cache_data, OSC_CACHE_TIME);
                }

                /* @var $variant Model_Catalog_Product_Variant */
                foreach ($this->_variant_collection as $variant) {
                    $variant->setProduct($this);
                }
            }
        }

        $using_array_to_load = false;
        foreach ($this->_variant_collection as $model) {
            if ($model->getId() < 1) {
                $using_array_to_load = true;
            }
        }
        if ($using_array_to_load) {
            $this->_variant_collection = OSC::model('catalog/product_variant')->getCollection();

            foreach ($this->_array_variants as $data) {
                $variant = OSC::model('catalog/product_variant')->bind($data);
                $this->_variant_collection->addItem($variant);
                $variant->setProduct($this);
            }
        }

        return $this->_variant_collection;
    }

    public function setVariants($variant_collection) {
        if ($this->_variant_collection === null) {
            $this->_variant_collection = $variant_collection;
        }

        return $this;
    }

    protected $_array_variants = null;
    public function setArrayVariants($array_variants) {
        if ($this->_array_variants === null) {
            $this->_array_variants = $array_variants;
        }

        return $this;
    }

    /**
     *
     * @param boolean $reload
     * @return $this->_product_collection
     */
    public function getProductCollections($reload = false) {
        if ($this->_product_collection === null || $reload) {
            $this->_product_collection = OSC::model('catalog/product')->getNullCollection()
                    ->addCondition('product_id', $this->getId(), OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                    ->load();
        }

        return $this->_product_collection;
    }

    /**
     *
     * @param boolean $reload
     * @return $this->_collection_collection
     */
    public function getCollections($reload = false) {
        if ($this->_collection_collection === null || $reload) {
            $this->_collection_collection = OSC::model('catalog/collection')->getCollection();

            if ($this->getId() > 0) {
                $this->_collection_collection->addCondition('collect_method', Model_Catalog_Collection::COLLECT_AUTO);

                if (is_array($this->data['collection_ids']) && count($this->data['collection_ids']) > 0) {
                    $this->_collection_collection->addCondition($this->_collection_collection->getPkFieldName(), $this->data['collection_ids'], OSC_Database::OPERATOR_FIND_IN_SET, OSC_Database::RELATION_OR);
                }

                $this->_collection_collection->sort('collect_method', 'ASC')->load();

                foreach ($this->_collection_collection as $collection) {
                    if ($collection->data['collect_method'] == Model_Catalog_Collection::COLLECT_AUTO && !$collection->productIsInCollection($this)) {
                        $this->_collection_collection->removeItemByKey($collection->getId());
                    }
                }
            }
        }

        return $this->_collection_collection;
    }

    /**
     * getListCollectionTitle
     *
     * @param  mixed $return_string
     * @return void
     */
    public function getListCollectionTitle($return_string = false) {
        $collections = $this->getCollections();
        $list_collection_title = [];
        foreach ($collections as $collection) {
            $collection_title = str_replace('-', '_', $collection->data['title']);
            $list_collection_title[] = OSC::core('string')->cleanAliasKey($collection_title, '_');
        }
        if ($return_string) {
            return implode(', ', $list_collection_title);
        }
        return $list_collection_title;
    }

    public function getOrderedOptions($skip_null_option = false) {
        $options = $this->data['options'];

        if (!$options) {
            return [];
        }

        if ($skip_null_option) {
            foreach ($options as $idx => $option) {
                if (!$option) {
                    unset($options[$idx]);
                }
            }
        }

        uasort($options, function($a, $b) {
            if (!$a) {
                return 1;
            } else if (!$b) {
                return 0;
            }

            if ($a['position'] == $b['position']) {
                return 0;
            }

            return ($a['position'] < $b['position']) ? -1 : 1;
        });

        return $options;
    }

    public function getFloatPrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->data['price']);
    }

    public function checkMasterLock(): bool {
        if ($this->data['master_lock_flag'] != 0) {
            return true;
        }

        return false;
    }

    public function getFloatCompareAtPrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->data['compare_at_price']);
    }

    /**
     *
     * @param Model_Catalog_Collection $catalog_collection
     * @return string
     */
    public function getDetailUrl($catalog_collection = null, $get_absolute_url = true)
    {
        $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
        $url = ($get_absolute_url ? OSC_FRONTEND_BASE_URL : '') . '/' . $current_lang_key . '/product/' . $this->getUkey() . '/';

        if (!$catalog_collection) {
            $catalog_collection = $this->getCatalogCollection();
        }

        if (($catalog_collection instanceof Model_Catalog_Collection) && $catalog_collection->getId() > 0) {
            $url .= $catalog_collection->getId() . '/';
        }

        $url .= $this->data['slug'];

        return $url;
    }

    public function getAmpUrl()
    {
        $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
        return OSC_FRONTEND_BASE_URL . '/' . $current_lang_key . '/amp/' . $this->getId() . '/' . $this->data['slug'];
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    public function getFeaturedImage($flag_image_default = false, $useCache = false) {
        $image_collection = $this->getImages(false, $useCache);

        if ($flag_image_default) {
            return $image_collection->getItem();
        }

        $list_customer_upload = [];

        foreach($image_collection as $image) {
            if ($image->data['flag_main'] == 1) {
                return $image;
            }
            $list_customer_upload[intval($image->data['position'])] = $image->getId();
        }

        ksort($list_customer_upload);

        return $image_collection->getItemByPK($list_customer_upload[array_key_first($list_customer_upload)]);
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    public function getFeaturedImageUrl($flag_image_default = false, $useCache = false) {
        $image = $this->getFeaturedImage($flag_image_default, $useCache);

        if (!$image) {
            return '';
        }

        return $image->getUrl();
    }

    public function getBuyDesignPrice() {
        if ($this->_buy_design_price == null) {
            $default_buy_design_price = OSC::helper('core/setting')->get('catalog/product/default_buy_design_price');
            $default_buy_design_price = !empty($default_buy_design_price) ? $default_buy_design_price * 100 : 0;
            $this->_buy_design_price = isset($this->data['meta_data']['buy_design']['buy_design_price']) && !empty($this->data['meta_data']['buy_design']['buy_design_price']) ? $this->data['meta_data']['buy_design']['buy_design_price'] : $default_buy_design_price;
        }

        return $this->_buy_design_price;
    }

    public function getProductTitle($include_identifier = false, $include_prefix = true, $skip_condition = false)
    {
        $product_title = [];
        if (!$this->isSemitestMode() || $this->data['topic'] !== $this::TOPIC_SEMITEST_DEFAULT || $skip_condition) {
            if ($include_identifier && $this->getProductIdentifier()) {
                $product_title[] = $this->getProductIdentifier();
            }

            if ($include_prefix) {
            }

            if ($this->data['topic']) {
                $product_title[] = $this->data['topic'];
            }
        }
        $product_title[] = $this->data['title'];
        return implode(' - ', $product_title);
    }

    public function getListDesignIdWithPrintTemplate() {
        if ($this->_list_design_id_in_print_template == null) {
            $campaign_config = $this->data['meta_data']['campaign_config']['print_template_config'];

            if (is_array($campaign_config) && count($campaign_config) > 0) {
                foreach ($campaign_config as $value) {

                    if (is_array($value['segments']) && count($value['segments']) > 0) {

                        $map_design_id = [];

                        foreach ($value['segments'] as $key => $segment_source_campaign) {
                            if ($segment_source_campaign['source']['type'] == 'personalizedDesign' && isset($segment_source_campaign['source']['design_id']) && !empty($segment_source_campaign['source']['design_id'])) {
                                $map_design_id['personalizedDesign'][$key] =  $segment_source_campaign['source']['design_id'];
                            }

                            if ($segment_source_campaign['source']['type'] == 'image' && isset($segment_source_campaign['source']['image_id']) && !empty($segment_source_campaign['source']['image_id'])) {
                                $map_design_id['image'][$key] = $segment_source_campaign['source']['image_id'];
                            }
                        }

                        $this->_list_design_id_in_print_template[$value['print_template_id']] = $map_design_id;
                    }
                }
            }
        }

        return $this->_list_design_id_in_print_template;
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (static::JSON_COLUMNS as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }

        if (isset($data['tags'])) {
            $data['tags'] = implode(',', $data['tags']);
        }

        if (isset($data['position_index'])) {
            $data['position_index'] = round(round($data['position_index'], 4) * 10000);
        }

        if (isset($data['collection_ids'])) {
            if (count($data['collection_ids']) > 0) {
                $data['collection_ids'] = ',' . implode(',', $data['collection_ids']) . ',';
            } else {
                $data['collection_ids'] = '';
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (static::JSON_COLUMNS as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }

        if (isset($data['tags'])) {
            $data['tags'] = ($data['tags'] === '') ? [] : explode(',', $data['tags']);
        }

        if (isset($data['position_index'])) {
            $data['position_index'] = round($data['position_index'] / 10000, 4);
        }

        if (isset($data['collection_ids'])) {
            $data['collection_ids'] = preg_replace('/(^,+|,+$)/', '', $data['collection_ids']);
            $data['collection_ids'] = preg_replace('/,{2,}/', ',', $data['collection_ids']);

            if ($data['collection_ids'] != '') {
                $data['collection_ids'] = explode(',', $data['collection_ids']);
                $data['collection_ids'] = array_map(function($collection_id) {
                    return intval($collection_id);
                }, $data['collection_ids']);
            } else {
                $data['collection_ids'] = [];
            }
        }

        if (isset($data['meta_data']) && is_array($data['meta_data']) && isset($data['meta_data']['campaign_config']) && is_array($data['meta_data']['campaign_config']) && count($data['meta_data']['campaign_config']) > 0) {
            foreach ($data['meta_data']['campaign_config'] as $product_type => $product_data) {
                $price = OSC::helper('catalog/common')->floatToInteger(floatval($product_data['price']));
                $compare_at_price = OSC::helper('catalog/common')->floatToInteger(floatval($product_data['compare_at_price']));

                OSC::helper('catalog/frontend')->applyPriceRules($price, $compare_at_price, ['product_type' => $product_type]);

                $price = OSC::helper('catalog/common')->integerToFloat($price);
                $compare_at_price = OSC::helper('catalog/common')->integerToFloat($compare_at_price);

                if ($price != $product_data['price'] || $compare_at_price != $product_data['compare_at_price']) {
                    $this->register('price_modified_by_rules', 1);

                    $data['meta_data']['campaign_config'][$product_type]['price'] = $price;
                    $data['meta_data']['campaign_config'][$product_type]['compare_at_price'] = $compare_at_price;

                    $this->register('data_modified_by_rules', 1);
                }
            }
        }
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        if ($this->registry('data_modified_by_rules') == 1) {
            $this->_error('It\'s unable to update product that modified by price rules');
            return false;
        }

        $errors = [];

        if (isset($data['topic'])) {
            $data['topic'] = trim($data['topic']);
            $data['topic'] = OSC::core('string')->removeInvalidCharacter($data['topic']);
            if (!$data['topic']) {
                $errors[] = 'Topic is empty';
            }
        }

        if (isset($data['title'])) {
            $data['title'] = trim($data['title']);
            if (!$data['title']) {
                $errors[] = 'Title is empty';
            }
        }

        if (isset($data['vendor'])) {
            $member_collection = OSC::model('user/member')->getCollection()->addCondition('username' , $data['vendor'])->setLimit(1)->load();

            if (!$member_collection->getItem()) {
                //$errors[] = 'Vendor is username member idea research';
            }
        }

        $block_image_config = array(
            'url_processor' => array(Helper_Core_Editor, 'imageUrlProcessor'),
            'control_align_enable' => true,
            'control_align_level_enable' => false,
            'control_align_overflow_mode' => false,
            'control_align_full_mode' => false,
            'control_zoom_enable' => false,
            'control_caption_enable' => true
        );
        $embed_block_config = array(
            'control_zoom_enable' => false,
            'control_align_level_enable' => true,
            'control_caption_enable' => true
        );

        foreach (array('content', 'description') as $key) {
            if (isset($data[$key])) {
                try {
                    $data[$key] = OSC::core('editor')->config(array('image_enable' => false))
                            ->addPlugins(array('name' => 'textColor'), array('name' => 'highlight'), array('name' => 'blockImage', 'config' => $block_image_config), array('name' => 'embedBlock', 'config' => $embed_block_config))
                            ->clean($data[$key]);
                } catch (Exception $ex) {
                    $data[$key] = '';
                }
            }
        }

        if (isset($data['position_index'])) {
            $data['position_index'] = floatval($data['position_index']);
        }

        foreach (array('product_type', 'vendor') as $key) {
            if (isset($data[$key])) {
                $data[$key] = trim($data[$key]);
            }
        }

        if (isset($data['tags'])) {
            if (!is_array($data['tags'])) {
                $data['tags'] = [];
            }

            $tags = [];

            foreach ($data['tags'] as $tag) {
                $tag = trim($tag);

                if (strlen($tag) > 0) {
                    $tags[] = $tag;
                }
            }

            $data['tags'] = $tags;
        }
        if (isset($data['meta_tags']['image'])) {
            $data['meta_tags']['image'] = trim($data['meta_tags']['image']);

            if ($data['meta_tags']['image'] !== '' && !OSC::core('aws_s3')->doesStorageObjectExist($data['meta_tags']['image'])) {
                $errors[] = 'Image file is not exists';
            }
        }

        if (isset($data['meta_tags'])) {
            if (!is_array($data['meta_tags'])) {
                $data['meta_tags'] = [];
            }

            $meta_tags = [];

            foreach (['title', 'keywords', 'description', 'image', 'is_clone'] as $key) {
                $meta_tags[$key] = isset($data['meta_tags'][$key]) ? trim($data['meta_tags'][$key]) : '';
            }

            $data['meta_tags'] = $meta_tags;
        }

        if (isset($data['collection_ids'])) {
            if (!is_array($data['collection_ids'])) {
                $data['collection_ids'] = [];
            }

            $data['collection_ids'] = array_map(function($collection_id) {
                return intval($collection_id);
            }, $data['collection_ids']);

            $data['collection_ids'] = array_filter($data['collection_ids'], function($collection_id) {
                return $collection_id > 0;
            });

            if (count($data['collection_ids']) > 0) {
                $data['collection_ids'] = array_unique($data['collection_ids']);

                $collection_collection = OSC::model('catalog/collection')->getCollection()->load($data['collection_ids']);

                $data['collection_ids'] = [];

                foreach ($collection_collection as $collection_model) {
                    if ($collection_model->data['collect_method'] == Model_Catalog_Collection::COLLECT_MANUAL) {
                        $data['collection_ids'][] = $collection_model->getId();
                    }
                }
            }
        }

        if (isset($data['solds'])) {
            $data['solds'] = intval($data['solds']);

            if ($data['solds'] < 1) {
                $data['solds'] = 0;
            }
        }

        foreach (array('price', 'compare_at_price') as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::helper('catalog/common')->floatToInteger(floatval($data[$key]));

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (isset($data['upc'])) {
            $data['upc'] = trim($data['upc']);

            if (!$data['upc']) {
                $data['upc'] = null;
            }
        }

        if (isset($data['options'])) {
            if (!is_array($data['options'])) {
                $data['options'] = [];
            }

            $options = [];

            foreach (array('option1', 'option2', 'option3') as $key) {
                $options[$key] = false;

                if (!isset($data['options'][$key]) || !is_array($data['options'][$key]) || !isset($data['options'][$key]['values']) || !is_array($data['options'][$key]['values'])) {
                    continue;
                }

                $option_values = [];

                foreach ($data['options'][$key]['values'] as $value) {
                    $value = trim($value);

                    if (strlen($value) > 0) {
                        $option_values[] = $value;
                    }
                }

                if (count($option_values) < 1) {
                    return;
                }

                $options[$key] = array(
                    'title' => isset($data['options'][$key]['title']) ? (string) $data['options'][$key]['title'] : '',
                    'position' => isset($data['options'][$key]['position']) ? intval($data['options'][$key]['position']) : substr($key, 6),
                    'type' => isset($data['options'][$key]['type']) ? preg_replace('/[^a-zA-Z0-9\_\-]/', '', strval($data['options'][$key]['type'])) : '',
                    'values' => array_values(array_unique($option_values))
                );
            }

            $data['options'] = $options;
        }

        foreach (['added_timestamp', 'modified_timestamp'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        foreach (['discarded', 'listing'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]) == 1 ? 1 : 0;
            }
        }

        if (isset($data['sku'])) {
            $data['sku'] = $this->cleanUkey($data['sku']);
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'title' => 'Product title is empty',
                    'vendor' => 'Vendor is empty',
                    'slug' => 'Slug is empty',
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'position_index' => 0,
                    'member_id' => 0,
                    'description' => '',
                    'content' => '',
                    'product_type' => '',
                    'price' => 0,
                    'compare_at_price' => 0,
                    'discarded' => 0,
                    'listing' => OSC::helper('core/setting')->get('catalog/product_default/listing') == 1 ? 1 : 0,
                    'solds' => 0,
                    'upc' => null,
                    'tags' => [],
                    'meta_tags' => [
                        'title' => '',
                        'description' => '',
                        'keywords' => ''
                    ],
                    'meta_data' => [
                        'skip_feed' => 0,
                        'marketing_point' => ''
                    ],
                    'additional_data' => [],
                    'options' => ['options' => []],
                    'collection_ids' => [],
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                if (count($errors) < 1 && (!isset($data['sku']) || !$data['sku'])) {
                    $data['sku'] = OSC::helper('catalog/product')->getSku();
                }
            } else {
                unset($data['sku']);
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _afterSave() {
        parent::_afterSave();

        $keys = ['title', 'description', 'content', 'tags', 'vendor', 'product_type'];

        $index_keywords = [];

        foreach ($keys as $key) {
            if (isset($this->data[$key]) && !isset($index_keywords[$key])) {
                $index_keywords[$key] = strip_tags($this->data[$key]);
            }
        }

        $index_keywords = implode(' ', $index_keywords);

        OSC::helper('backend/common')->indexAdd('', 'catalog', 'product', $this->getId(), $index_keywords);
        OSC::helper('frontend/common')->indexAdd('', 'catalog', 'product', $this->getId(), $index_keywords);

        try {
            if ($this->getLastActionFlag() == static::INSERT_FLAG) {
                OSC::core('observer')->dispatchEvent('catalog/productCreated', $this);
            } else {
                OSC::core('observer')->dispatchEvent('catalog/productUpdated', $this);
            }
        } catch (Exception $ex) {

        }

        if (!$this->isAvailable() || $this->data['listing'] === 0) {
            OSC::helper('catalog/collectionProductRel')->deleteByField('product_id', $this->getId());
        }

        // Reset cache product contain design has tab
        $this->setProductHasTabCache();

        OSC::core('observer')->addObserver('shutdown', ['Model_Catalog_Product', 'setSupplyLocationOfProduct'], null, null, $this->getId());
    }

    public static function setSupplyLocationOfProduct($params, $product_id) {
        OSC::helper('supplier/location')->setSupplyLocationOfProduct($product_id);

        //Sync to ES
        try {
            OSC::core('observer')->dispatchEvent('model_catalog_product_save', [
                'product_id' => $product_id,
                'columns' => 'supply_location, product_id'
            ]);
        } catch (Exception $exception) { }
    }

    protected function _beforeDelete() {
        parent::_beforeDelete();
    }

    protected function _afterDelete() {
        parent::_afterDelete();
        if ($this->isCampaignMode()) {
            try {
                OSC::helper('catalog/campaign')->syncAfterDelete($this->getId());
            } catch (Exception $ex) {

            }
        }

        OSC::core('observer')->dispatchEvent('catalog/algoliaSyncProduct',
            [
                'product_id' => $this->getId(),
                'sync_type' => Helper_Catalog_Algolia_Product::SYNC_TYPE_DELETE_PRODUCT
            ]
        );

        OSC::helper('backend/common')->indexDelete('', 'catalog', 'product', $this->getId());
        OSC::helper('frontend/common')->indexDelete('', 'catalog', 'product', $this->getId());

        OSC::helper('catalog/collectionProductRel')->deleteByField('product_id', $this->getId());

        OSC::helper('d2/common')->afterProductD2Delete($this);
        OSC::helper('feed/common')->triggerDeleteBlock(['product_id' => $this->getId()]);
        OSC::helper('feed/common')->triggerDeleteCustomTitle($this->getId());

        OSC::helper('filter/tagProductRel')->deleteTagProductRel($this->getId());

        try {
            $variants = $this->getVariants();
            $images = $this->getImages();
            if ($variants instanceof Model_Catalog_Product_Variant_Collection && $variants->length() > 0) {
                $variants->delete();
            }

            if ($images instanceof Model_Catalog_Product_Image_Collection && $images->length() > 0) {
                $images->delete();
            }
            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getWriteAdapter();
            $DB->delete('alias', "destination='catalog/product/{$this->getId()}'", 1);
        }catch (Exception $ex){

        }
    }



    public function getMetaImageUrl() {
        return $this->data['meta_tags']['image'] ? OSC::core('aws_s3')->getStorageUrl($this->data['meta_tags']['image']) : '';
    }

    public function getOgImageUrl($image_url = '')
    {
        $og_image = $this->getMetaImageUrl();

        if (!$og_image) {
            $og_image = $image_url;
        }

        return $og_image ? $og_image : OSC::helper('frontend/template')->getMetaImage()->url;
    }

    protected $_product_identifier = null;

    /**
     * @throws OSC_Exception_Runtime
     */
    private function _getProductIdentifier() {
        $cache_key = '_getProductIdentifier|product_id:,' . $this->data['product_id'] . ',|';
        $cache = OSC::core('cache')->get($cache_key);

        if ($cache) {
            return $cache;
        }

        $product_variants = $this->getVariants(false, true);
        $product_type_group = '';
        $identifier = [];

        $product_type_ids = [];
        $product_type_variant_ids = [];
        foreach ($product_variants as $product_variant) {
            $product_type_variant_ids[] =  $product_variant->data['product_type_variant_id'];
        }

        $preload_product_type_variants = OSC::helper('catalog/campaign')->getPreloadProductTypeVariant($product_type_variant_ids);
        if ($preload_product_type_variants->length()) {
            foreach ($preload_product_type_variants as $product_type_variant) {
                $product_type_ids[$product_type_variant->getId()] = $product_type_variant->data['product_type_id'];
            }
        }

        $preload_product_types = OSC::helper('catalog/campaign')->getPreloadProductType(array_unique($product_type_ids));

        foreach ($product_variants as $product_variant) {
            $product_type_id = $product_type_ids[$product_variant->data['product_type_variant_id']];
            $product_type = $preload_product_types->getItemByPK($product_type_id);
            //If product has many different type of product return title ''
            if ($product_type_group !== '' && $product_type_group !== $product_type->data['group_name']) {
                return '';
            }

            $product_type_group = $product_type->data['group_name'];
            $identifier = $product_type->data['identifier'];
        }

        if (!is_array($identifier) || count($identifier) < 1) {
            return '';
        }

        $flag_image = false;
        $flag_personalized = false;
        $flag_photo = $this->isPhotoUploadMode();

        foreach ($this->data['meta_data']['campaign_config']['print_template_config'] as $print_template_config) {
            foreach ($print_template_config['segments'] as $segment_key => $segment_source) {
                if ($segment_source['source']['type'] === 'personalizedDesign') {
                    $flag_personalized = true;
                }

                if ($segment_source['source']['type'] === 'image') {
                    $flag_image = true;
                }
            }
        }

        switch (true) {
            case $flag_photo:
                $result = $identifier['photo'] ?? '';
                break;
            case $flag_personalized:
                $result = $identifier['personalized'] ?? '';
                break;
            case $flag_image:
                $result = $identifier['image'] ?? '';
                break;
            default:
                $result = '';
                break;
        }

        if ($result) {
            OSC::core('cache')->set($cache_key, $result, OSC_CACHE_TIME);
        }

        return $result;
    }

    /**
     * Get Product Identifier
     * @return mixed|string
     * @throws OSC_Exception_Runtime
     */
    public function getProductIdentifier() {
        if ($this->_product_identifier === null) {
            $this->_product_identifier = $this->_getProductIdentifier();
        }

        return $this->_product_identifier;
    }

    /**
     * @return array
     * @throws OSC_Exception_Runtime
     */
    public function getProductPriceData(): array {
        $result = [
            'price' => 0,
            'compare_at_price' => 0
        ];

        $variant = $this->getSelectedOrFirstAvailableVariant();

        if ($variant && $variant->ableToOrder()) {
            $price_data = $variant->getPriceForCustomer();
            $price = $price_data['price'];
            $compare_at_price = $price_data['compare_at_price'];
        }

        $result['price'] = $price;
        $result['compare_at_price'] = $compare_at_price;

        return $result;
    }

    public function getProductKeyWord() {
        $seo_tags = $this->data['seo_tags'];
        $meta_keywords = $this->data['meta_tags']['keywords'];

        if($seo_tags){
            $meta_keywords = implode(', ', array_column($seo_tags, 'collection_title'));
        }

        return $meta_keywords;
    }

    public function getProductSeoTags() {
        $seo_tags = $this->data['seo_tags'];
        $collection_ids = [];

        foreach ($seo_tags as $key => $tag) {
            if ($tag['collection_id'] != 0) {
                $collection_ids[] = $tag['collection_id'];
            } else {
                $seo_tags[$key]['collection_link'] = OSC_FRONTEND_BASE_URL . '/tags/' . $tag['collection_slug'];
            }
        }

        if ($collection_ids) {
            try {
                $collection_model = OSC::model('catalog/collection')->getCollection()->addField('collection_id', 'slug', 'meta_tags')->load($collection_ids);
                foreach ($collection_model as $collection) {
                    foreach ($seo_tags as $key => $item) {
                        if ($item['collection_id'] == $collection->data['collection_id']) {
                            $seo_tags[$key]['collection_link'] = $collection->getDetailUrl();
                        }
                    }
                }
            } catch (Exception $ex) {
            }
        }

        return $seo_tags;
    }

    public function getDataVariantsSemitest(): array {
        $variants = $this->getVariants(false, true)->toArray();

        $this->_sortVariantsByPosition($variants);

        $results = [];

        if (count($variants) > 0) {
            foreach ($variants as $variant) {
                $results[] = [
                    'id' => $variant['id'],
                    'design_id' => $variant['design_id'],
                    'sku' => $variant['sku'],
                    'image_id' => $variant['image_id'],
                    'video_id' => $variant['video_id'],
                    'video_position' => array_values($variant['meta_data']['video_config']['position'] ?? []) ,
                    'options' => $variant['options'],
                    'option1' => $variant['option1'],
                    'option2' => $variant['option2'],
                    'option3' => $variant['option3'],
                    'shipping_price' => $variant['meta_data']['semitest_config']['shipping_price'] ?? 0,
                    'shipping_plus_price' => $variant['meta_data']['semitest_config']['shipping_plus_price'] ?? 0,
                    'price' => $variant['price'],
                    'compare_at_price' => $variant['compare_at_price'],
                    'meta_data' => is_array($variant['meta_data']) ? $variant['meta_data'] : [],
                    'position' => $variant['position']
                ];
            }
        }

        return $results;
    }

    public function isSupplyInLocation($location_code = null) {
        if (!$this->isCampaignMode() || OSC::helper('core/common')->isLoggedMember()) {
            return true;
        }

        if (empty($location_code)) {
            $location_code = OSC::helper('catalog/common')->getCustomerLocationCode();
        }

        return strpos($this->data['supply_location'], $location_code) !== false;
    }

    public function isLivePreview() {
        return $this->data['personalized_form_detail'] === 'live_preview' ? 1 : 0;
    }

    public function checkHasTabDesign() {
        if (
            !intval(OSC::helper('core/setting')->get('catalog/product/enable_product_detail_v4'))
            || $this->isLivePreview() // Gossby is blocking v4 tab. Tab is just used for felime 's product
        ) {
            return 0;
        }

        $personalized_design_ids = [];
        foreach ($this->data['meta_data']['campaign_config']['print_template_config'] as $print_template_config) {
            foreach ($print_template_config['segments'] as $segment) {
                if ($segment['source']['type'] == 'personalizedDesign') {
                    $personalized_design_ids[] = $segment['source']['design_id'];
                }
            }
        }

        if (count($personalized_design_ids) > 0) {

            $personalized_design_model = OSC::model('personalizedDesign/design');
            $personalized_design_pk = $personalized_design_model->getPkFieldName();

            $ids = $personalized_design_model->getCollection()
                ->addField($personalized_design_pk)
                ->addCondition($personalized_design_pk, $personalized_design_ids, OSC_Database::OPERATOR_IN)
                ->addCondition('tab_flag', 1, OSC_Database::OPERATOR_EQUAL)
                ->load()
                ->getItems();
            if (count($ids) > 0) {
                return 1;
            }

            return 0;

        }

        return 0;
    }

    public function setProductHasTabCache() {
        try {
            $has_tab = $this->checkHasTabDesign();
            $cache = OSC::core('cache');
            // Many keys because React call with ID or Ukey or both
            $cache_keys = [
                self::HAS_TAB_PREFIX . $this->getId() . '_' . $this->getUkey(),
                self::HAS_TAB_PREFIX . '0_' . $this->getUkey(),
                self::HAS_TAB_PREFIX . '_' . $this->getUkey(),
                self::HAS_TAB_PREFIX . $this->getId() . '_',
            ];

            foreach ($cache_keys as $key) {
                $cache->set($key, $has_tab, 30*24*60*60); // 30 days
            }

            return $has_tab;
        } catch (Exception $ex) {

        }
    }

    public function checkIssetImageMain() {
        $product_image_length = 0;

        try {
            $product_image_length = OSC::model('catalog/product_image')->getCollection()
                ->addField('image_id')
                ->addCondition('product_id', $this->getId())
                ->addCondition('flag_main', 1)
                ->load()
                ->length();
        } catch (Exception $ex) { }

        return $product_image_length;
    }

    public function getProductTagSelected() {
        $product_tag_selected = [];

        $product_id = $this->getId();

        if (intval($product_id) < 1) {
            return $product_tag_selected;
        }

        try {
            $product_tag = OSC::model('filter/tagProductRel')
                ->getCollection()
                ->addField('tag_id')
                ->addCondition('product_id', $product_id)
                ->load();

            foreach ($product_tag as $tag) {
                $product_tag_selected[$tag->data['tag_id']] = $tag->data['tag_id'];
            }

            return $product_tag_selected;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getDesignIdsByProduct() {
        try {
            $design_ids = [];

            $meta_data = $this->data['meta_data'];

            if ($this->isCampaignMode()) {
                foreach ($meta_data['campaign_config']['print_template_config'] as $print_template_config) {
                    foreach ($print_template_config['segments'] as $segment) {
                        $design_id = $segment['source']['design_id'];
                        if (intval($design_id) < 1) {
                            continue;
                        }
                        $design_ids[$design_id] = $design_id;
                    }
                }
            }

            if ($this->isSemitestMode()) {
                $variants = $this->getVariants();
                foreach ($variants as $variant) {
                    foreach ($variant->data['design_id'] as $design_id) {
                        if (intval($design_id) < 1) {
                            continue;
                        }

                        $design_ids[$design_id] = $design_id;
                    }
                }
            }

            return array_values($design_ids);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
