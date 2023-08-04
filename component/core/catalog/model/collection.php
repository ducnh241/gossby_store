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
class Model_Catalog_Collection extends Abstract_Core_Model {

    protected $_table_name = 'catalog_collection';
    protected $_pk_field = 'collection_id';

    protected $_allow_write_log = true;

    protected $_option_conf = ['value' => 'collection_id', 'label' => 'title'];
    protected $_condition_operators = [
        'equals' => ['query_operator' => 'EQUAL', 'title' => 'is equal to'],
        'not_equals' => ['query_operator' => '!EQUAL', 'title' => 'is not equal to'],
        'greater_than' => ['query_operator' => 'GREATER_THAN', 'title' => 'is greater than'],
        'less_than' => ['query_operator' => 'LESS_THAN', 'title' => 'is less than'],
        'starts_with' => ['query_operator' => 'LIKE_RIGHT', 'title' => 'starts with'],
        'ends_with' => ['query_operator' => 'LIKE_LEFT', 'title' => 'ends with'],
        'contains' => ['query_operator' => 'LIKE', 'title' => 'contains'],
        'not_contains' => ['query_operator' => '!LIKE', 'title' => 'does not contains'],
        'created_x_day_ago' => ['query_operator' => 'GREATER_THAN', 'title' => 'x day ago']
    ];
    protected $_condition_fields = [
        'title' => [
            'title' => 'Product title',
            'field' => 'title',
            'type' => 'string',
            'skip_operators' => ['greater_than', 'less_than', 'created_x_day_ago']
        ],
        'type' => [
            'title' => 'Product type',
            'field' => 'product_type',
            'type' => 'string',
            'skip_operators' => ['greater_than', 'less_than', 'created_x_day_ago']
        ],
        'vendor' => [
            'title' => 'Product vendor',
            'field' => 'vendor',
            'type' => 'string',
            'skip_operators' => ['greater_than', 'less_than', 'created_x_day_ago']
        ],
        'price' => [
            'title' => 'Product price',
            'field' => 'price',
            'type' => 'float',
            'skip_operators' => ['starts_with', 'ends_with', 'contains', 'not_contains', 'created_x_day_ago']
        ],
        'compare_at_price' => [
            'title' => 'Compare at price',
            'field' => 'compare_at_price',
            'type' => 'float',
            'skip_operators' => ['starts_with', 'ends_with', 'contains', 'not_contains', 'created_x_day_ago']
        ],
        'topic' => [
            'title' => 'Topic',
            'field' => 'topic',
            'type' => 'string',
            'skip_operators' => ['greater_than', 'less_than', 'created_x_day_ago']
        ],
        'tag' => [
            'title' => 'Product tag',
            'field' => 'tags',
            'type' => 'string',
            'skip_operators' => ['greater_than', 'less_than', 'starts_with', 'ends_with', 'contains', 'not_contains', 'created_x_day_ago']
        ],
        'added_timestamp' => [
            'title' => 'Created date',
            'field' => 'added_timestamp',
            'type' => 'number',
            'skip_operators' => ['equals', 'not_equals', 'greater_than', 'less_than', 'starts_with', 'ends_with', 'contains', 'not_contains']
        ]
    ];
    protected $_sort_options = [
        'solds' => 'Best selling',
        'title_az' => 'Product title A-Z',
        'title_za' => 'Product title Z-A',
        'highest_price' => 'Highest price',
        'lowest_price' => 'Lowest price',
        'newest' => 'Newest',
        'oldest' => 'Oldest',
        'manual' => 'Manual',
    ];

    const CACHE_TIME_PRODUCT = 86400;

    const DATE_TYPE_RELATIVE = 'relative';
    const DATE_TYPE_ABSOLUTE = 'absolute';

    /**
     *
     * @var Model_Catalog_Product_Collection
     */
    protected $_product_collection = null;
    protected $_is_shipping_blocked_country = false;

    const COLLECT_AUTO = 'auto';
    const COLLECT_MANUAL = 'manual';
    const DEFAULT_COLLECT_METHOD = 'manual';

    public function getConditionOperators() {
        return $this->_condition_operators;
    }

    public function getConditionFields() {
        return $this->_condition_fields;
    }

    /**
     *
     * @param array $options page_size, page, before_load_callback
     * @return Model_Catalog_Product_Collection
     * @throws OSC_Exception_Runtime
     */
    public function loadProducts($options = []) {
        $flag_feed = isset($options['flag_feed']) && $options['flag_feed'];
        if ($this->_product_collection === null) {
            $default = ['page_size' => 25, 'page' => 1, 'before_load_callback' => null];

            if (!is_array($options)) {
                $options = array();
            }

            foreach ($default as $k => $v) {
                if (!isset($options[$k])) {
                    $options[$k] = $v;
                }
            }

            if ($flag_feed) {
                $this->_product_collection = OSC::model('catalog/product')->getCollection()->addField('product_id');
            } else {
                $this->_product_collection = OSC::model('catalog/product')->getCollection();
                $location_code = OSC::helper('catalog/common')->getCustomerLocationCode();
                $countries_render_beta_products = OSC::helper('core/setting')->get('catalog/product_listing/country_render_beta_product');

                if (OSC::controller() instanceof Abstract_Frontend_Controller ||
                    OSC::controller() instanceof Abstract_Frontend_ReactApiController
                ) {
                    if (OSC::helper('core/common')->isGuest()) {
                        $shipping_location = OSC::helper('catalog/common')->getCustomerShippingLocation();
                        $blocked_countries = OSC::helper('core/country')->getBlockCountries();
                        $blocked_countries = array_keys($blocked_countries);

                        if (in_array($shipping_location['country_code'], $blocked_countries)) {
                            $this->_is_shipping_blocked_country = true;
                            return $this->_product_collection;
                        } else if (!in_array($shipping_location['country_code'], $countries_render_beta_products)) {
                            $this->_product_collection->addCondition('supply_location', $location_code, OSC_Database::OPERATOR_LIKE);
                        } else {
                            $this->_product_collection->addClause('beta_product')
                                ->addCondition('supply_location', $location_code, OSC_Database::OPERATOR_LIKE, 'OR', 'beta_product')
                                ->addCondition('selling_type', Model_Catalog_Product::TYPE_SEMITEST, OSC_Database::OPERATOR_EQUAL, 'OR', 'beta_product');
                        }
                    }
                }
            }

            if ($this->getId() > 0) {
                if ($this->data['collect_method'] == static::COLLECT_MANUAL) {
                    $this->_product_collection->addCondition('collection_ids', ",{$this->getId()},", OSC_Database::OPERATOR_LIKE);
                } else {
                    $clause_idx = OSC::makeUniqid();

                    $this->_product_collection->addClause($clause_idx);

                    $relation = $this->data['auto_conditions']['matched_by'] == 'any' ? 'OR' : 'AND';

                    foreach ($this->data['auto_conditions']['conditions'] as $condition) {
                        if ($this->_condition_fields[$condition['field']]['field'] == 'tags') {
                            $condition_value = $condition['value'];

                            // escape regular expression
                            $brackets = ['(', ')', '{', '}', '[', '^', '$', '.', '*', '+', '?', '|'];
                            $escape_brackets = ['\\(', '\\)', '\\{', '\\}', '\\[', '\\^', '\\$', '\\.', '\\*', '\\+', '\\?', '\\|'];
                            $condition_value = str_replace($brackets, $escape_brackets, $condition_value);

                            $this->_product_collection->addCondition($this->_condition_fields[$condition['field']]['field'], '(^|,)\s*' . preg_quote($condition_value, "'") . '\s*(,|$)', $condition['operator'] == 'equals' ? OSC_Database::OPERATOR_REGEXP : OSC_Database::OPERATOR_NOT_REGEXP, $relation, $clause_idx);

                            continue;
                        }

                        if ($this->_condition_fields[$condition['field']]['field'] == 'added_timestamp'){
                            $condition['value'] = time() - intval($condition['value']) * 24 * 60 * 60;
                        }

                        if ($this->_condition_fields[$condition['field']]['field'] == 'price' || $this->_condition_fields[$condition['field']]['field'] == 'compare_at_price') {
                            $condition['value'] = $condition['value'] * 100;
                        }

                        $this->_product_collection->addCondition($this->_condition_fields[$condition['field']]['field'], $condition['value'], $this->_condition_operators[$condition['operator']]['query_operator'], $relation, $clause_idx);
                    }
                }
            }


            if ($flag_feed) {
                return $this->_product_collection->load();
            }

            if (count($options['filters']) > 0) {

                $product_ids = OSC::helper('filter/common')->getProductIdByFilter($options['filters']);

                if (!is_array($product_ids) || count($product_ids) < 1) {
                    $this->_product_collection = OSC::model('catalog/product')->getCollection()->getNullCollection()->setNull();
                    return $this->_product_collection;
                }

                $this->_product_collection->addCondition($this->_product_collection->getPkFieldName(), $product_ids, OSC_Database::OPERATOR_IN);
            }

            if (is_callable($options['before_load_callback'])) {
                $flag_filter = $options['filters'] > 0;
                call_user_func_array($options['before_load_callback'], [$this->_product_collection, $location_code, $flag_filter]);
            }

            if (!isset($options['sort']) || !in_array($options['sort'], OSC::helper('filter/search')->getSortOptions(true))) {
                $options['sort'] = 'default';
            }

            switch ($options['sort']) {
                case 'solds':
                    $flag_sort_sold = false;

                    $cache_key = null;
                    if ($this->data['best_selling_start'] && $this->data['best_selling_end']) {
                        $cache_key = OSC::helper('catalog/common')->getAbsoluteCacheKey($this->data['best_selling_start'], $this->data['best_selling_end']);
                    } elseif ($this->data['relative_range']) {
                        $cache_key = OSC::helper('catalog/common')->getRelativeCacheKey($this->data['relative_range']);
                    }

                    if ($cache_key) {
                        $cache_product_ids = OSC::core('cache')->get($cache_key);

                        if ($cache_product_ids !== false) {
                            $cache_product_ids = array_reverse(array_keys($cache_product_ids));
                            $str_product_ids = implode(',', $cache_product_ids);
                            $this->_product_collection
                                ->sort("FIELD({$this->_product_collection->getPkFieldName()}, {$str_product_ids})", 'DESC')
                                ->sort('added_timestamp', 'DESC');
                            $flag_sort_sold = true;
                        }
                    }

                    if (!$flag_sort_sold) {
                        $this->_product_collection->sort('solds', 'DESC');
                    }
                    break;
                case 'newest':
                    $this->_product_collection->sort('product_id', 'DESC');
                    break;
                default:
                    switch ($this->data['sort_option']) {
                        case 'title_az':
                            $this->_product_collection->sort('title', 'ASC');
                            break;
                        case 'title_za':
                            $this->_product_collection->sort('title', 'DESC');
                            break;
                        case 'highest_price':
                            $this->_product_collection->sort('price', 'DESC');
                            break;
                        case 'lowest_price':
                            $this->_product_collection->sort('price', 'ASC');
                            break;
                        case 'oldest':
                            $this->_product_collection->sort('product_id', 'ASC');
                            break;
                        case 'manual':
                            $this->_product_collection->sort('position_index', 'DESC')->sort('title', 'ASC');
                            break;
                        default:
                            $this->_product_collection->sort('product_id', 'DESC');
                            break;
                    }
                    break;
            }


            if ($options['page_size'] != "all") {
                if ($options['top'] && $options['top'] <= $options['page_size']) {
                    $this->_product_collection->setLimit($options['top']);
                } else {
                    $this->_product_collection->setPageSize($options['page_size'])->setCurrentPage($options['page']);
                }
            }

            $this->_product_collection->load();

            if ($options['page_size'] != "all") {
                if ($options['top'] && $options['top'] > $options['page_size']) {
                    $count_product_loaded = ($options['page_size'] * ($options['page'] - 1)) + $this->_product_collection->length();
                    if ($count_product_loaded > $options['top']) {
                        $count_item_redundant = $count_product_loaded - $options['top'];
                        for ($i = 0;$i < $count_item_redundant;$i++) {
                            $this->_product_collection->removeItem($this->_product_collection->length() - 1);
                        }
                    }
                }
            }

            foreach ($this->_product_collection as $product) {
                $product->setCatalogCollection($this);
            }

            $this->_product_collection->preLoadMockupRemove();
            $this->_product_collection->preLoadImageCollection();
            $this->_product_collection->preLoadABTestProductPrice();
            $this->_product_collection->preLoadVariantCollection()->preLoadProductTypeVariant();
        }

        return $this->_product_collection;
    }

    public function getProducts($options = []) {
        return $this->loadProducts($options);
    }

    public function length() {
        return $this->getProducts()->length();
    }

    public function collectionLength($options = []) {
        $product_collection = $this->getProducts($options);

        if ($this->_is_shipping_blocked_country) {
            return 0;
        }

        return $product_collection->collectionLength();
    }

    public function getCurrentPage() {
        return $this->getProducts()->getCurrentPage();
    }

    public function getPageSize() {
        return $this->getProducts()->getPageSize();
    }

    public function getSortOptions() {
        return $this->_sort_options;
    }

    public function getDetailUrl($get_absolute_url = true) {
        $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
        $url = $current_lang_key . '/collection/' . $this->data['slug'];

        return ($get_absolute_url ? OSC_FRONTEND_BASE_URL : '') . '/' . $url;
    }

    public function getImageUrl() {
        return $this->data['image'] ? OSC::core('aws_s3')->getStorageUrl($this->data['image']) : '';
    }

    public function getMetaImageUrl() {
        return $this->data['meta_tags']['image'] ? OSC::core('aws_s3')->getStorageUrl($this->data['meta_tags']['image']) : '';
    }

    public function getOgImageUrl()
    {
        $og_image = $this->getMetaImageUrl();

        if (!$og_image) {
            $og_image = $this->getImageUrl();
        }

        return $og_image ? $og_image : OSC::helper('frontend/template')->getMetaImage()->url;
    }

    public function getViewableConditions() {
        if ($this->data['collect_method'] == static::COLLECT_MANUAL) {
            return array('--');
        }

        $conditions = array();

        foreach ($this->data['auto_conditions']['conditions'] as $condition) {
            $conditions[] = $this->_condition_fields[$condition['field']]['title'] . ' ' . $this->_condition_operators[$condition['operator']]['title'] . ' ' . $condition['value'];
        }

        return $conditions;
    }

    public function getBestSellingRange(): ?string
    {
        if (!$this->data['best_selling_start'] && !$this->data['best_selling_end']) {
            return null;
        }

        foreach (array('best_selling_start', 'best_selling_end') as $key) {
            $data[$key] = date('d/m/Y', $this->data[$key]);
        }
        return "{$data['best_selling_start']} - {$data['best_selling_end']}";
    }

    /**
     *
     * @param Model_Catalog_Product $product
     * @return boolean
     */
    public function productIsInCollection($product) {
        if ($this->data['collect_method'] == static::COLLECT_MANUAL) {
            return in_array($this->getId(), is_array($product->data['collection_ids']) ? $product->data['collection_ids'] : array());
        }

        if (!is_array($this->data['auto_conditions'])) {
            return false;
        }

        $matched_counter = 0;

        foreach ($this->data['auto_conditions']['conditions'] as $condition) {
            $compare_at_value = $product->data[$this->_condition_fields[$condition['field']]['field']];
            $condition_value = $condition['value'];

            if ($this->_condition_fields[$condition['field']]['type'] === 'float') {
                $condition_value = OSC::helper('catalog/common')->floatToInteger(floatval($condition_value));
                $compare_at_value = intval($compare_at_value);
            }

            if ($condition['field'] === 'tag') {
                if ($condition['operator'] == 'equals') {
                    if (!in_array($condition_value, $compare_at_value, true)) {
                        continue;
                    }
                } else {
                    if (in_array($condition_value, $compare_at_value, true)) {
                        continue;
                    }
                }
            } else {
                if ($condition['operator'] == 'equals') {
                    if ($compare_at_value !== $condition_value) {
                        continue;
                    }
                } else if ($condition['operator'] == 'not_equals') {
                    if ($compare_at_value === $condition_value) {
                        continue;
                    }
                } else if ($condition['operator'] == 'greater_than') {
                    if ($compare_at_value <= $condition_value) {
                        continue;
                    }
                } else if ($condition['operator'] == 'less_than') {
                    if ($compare_at_value >= $condition_value) {
                        continue;
                    }
                } else if ($condition['operator'] == 'starts_with') {
                    if (strpos(strtolower(trim($compare_at_value)), strtolower(trim($condition_value))) !== 0) {
                        continue;
                    }
                } else if ($condition['operator'] == 'ends_with') {
                    if (strpos(strtolower(trim($compare_at_value)), strtolower(trim($condition_value))) !== (strlen($compare_at_value) - strlen($condition_value))) {
                        continue;
                    }
                } else if ($condition['operator'] == 'contains') {
                    if (strpos(strtolower(trim($compare_at_value)), strtolower(trim($condition_value))) === false) {
                        continue;
                    }
                } else if ($condition['operator'] == 'not_contains') {
                    if (strpos(strtolower(trim($compare_at_value)), strtolower(trim($condition_value))) !== false) {
                        continue;
                    }
                }
            }

            if ($this->data['auto_conditions']['matched_by'] == 'any') {
                return true;
            }

            $matched_counter ++;
        }

        return $matched_counter == count($this->data['auto_conditions']['conditions']);
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (array('meta_tags', 'auto_conditions') as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
        foreach (['relative_range', 'top'] as $key) {
            if (isset($data[$key]) && !$data[$key]) {
                $data[$key] = null;
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (array('meta_tags', 'auto_conditions') as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
        foreach (['relative_range', 'top'] as $key) {
            if (isset($data[$key]) && !$data[$key]) {
                $data[$key] = null;
            }
        }
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        $block_image_config = array(
            'url_processor' => array(Helper_Core_Editor, 'imageUrlProcessor'),
            'control_align_enable' => true,
            'control_align_level_enable' => true,
            'control_align_overflow_mode' => true,
            'control_align_full_mode' => true,
            'control_zoom_enable' => true,
            'control_caption_enable' => true
        );
        $embed_block_config = array(
            'control_zoom_enable' => false,
            'control_align_level_enable' => true,
            'control_caption_enable' => true
        );

        foreach (array('description') as $key) {
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

        if (isset($data['image'])) {
            $data['image'] = trim($data['image']);

            if ($data['image'] !== '' && !OSC::core('aws_s3')->doesStorageObjectExist($data['image'])) {
                $errors[] = 'Image file is not exists';
            }
        }

        if (isset($data['meta_tags']['image'])) {
            $data['meta_tags']['image'] = trim($data['meta_tags']['image']);

            if ($data['meta_tags']['image'] !== '' && !OSC::core('aws_s3')->doesStorageObjectExist($data['meta_tags']['image'])) {
                $errors[] = 'Image file is not exists';
            }
        }

        if (isset($data['collect_method'])) {
            $data['collect_method'] = trim($data['collect_method']);

            if ($data['collect_method'] !== static::COLLECT_AUTO) {
                $data['collect_method'] = static::COLLECT_MANUAL;
            }
        }

        if (isset($data['auto_conditions'])) {
            if (!is_array($data['auto_conditions'])) {
                $data['auto_conditions'] = array();
            }

            $conditions = array();

            if (isset($data['auto_conditions']['conditions']) && is_array($data['auto_conditions']['conditions'])) {
                foreach ($data['auto_conditions']['conditions'] as $condition) {
                    if (!is_array($condition) || !isset($condition['field']) || !isset($condition['operator']) || !isset($condition['value'])) {
                        continue;
                    }

                    $condition['field'] = strtolower(trim($condition['field']));
                    $condition['operator'] = strtolower(trim($condition['operator']));
                    $condition['value'] = trim($condition['value']);

                    if ($condition['value'] === '' || !isset($this->_condition_fields[$condition['field']]) || !isset($this->_condition_operators[$condition['operator']]) || in_array($condition['operator'], $this->_condition_fields[$condition['field']]['skip_operators'], true)) {
                        continue;
                    }

                    if ($this->_condition_fields[$condition['field']]['skip_operators'] === 'float') {
                        $condition['value'] = round(floatval($condition['value']), 2);
                    }

                    $conditions[] = array(
                        'field' => $condition['field'],
                        'operator' => $condition['operator'],
                        'value' => $condition['value']
                    );
                }
            }

            $data['auto_conditions'] = array(
                'matched_by' => (isset($data['auto_conditions']['matched_by']) && $data['auto_conditions']['matched_by'] == 'any') ? 'any' : 'all',
                'conditions' => $conditions
            );
        }

        if (isset($data['collect_method']) || isset($data['auto_conditions'])) {
            $collect_method = isset($data['collect_method']) ? $data['collect_method'] : (($this->getId() > 0) ? $this->getData('collect_method', true) : static::DEFAULT_COLLECT_METHOD);
            $collect_conditions = isset($data['auto_conditions']) ? $data['auto_conditions'] : (($this->getId() > 0) ? $this->getData('auto_conditions', true) : array());

            if ($collect_method === static::COLLECT_MANUAL) {
                $data['auto_conditions'] = array();
            } else {
                if (!is_array($collect_conditions) || !isset($collect_conditions['matched_by']) || !isset($collect_conditions['conditions']) || count($collect_conditions['conditions']) < 1) {
                    $errors[] = 'Need have least one condition with auto collect method';
                }
            }
        }

        if (isset($data['sort_option'])) {
            $data['sort_option'] = strtolower(trim($data['sort_option']));

            if (!isset($this->_sort_options[$data['sort_option']])) {
                $data['sort_option'] = 'desc:date';
            }
        }

        if (isset($data['meta_tags'])) {
            if (!is_array($data['meta_tags'])) {
                $data['meta_tags'] = array();
            }
                     
            $meta_tags = array();

            if (isset($data['meta_tags']['banner'])) {
                $banner = [];
                
                foreach (['option', 'title', 'url', 'pc', 'mobile'] as $key) {
                    $banner[$key] = isset($data['meta_tags']['banner'][$key]) ? trim($data['meta_tags']['banner'][$key]) : '';
                }
            }

            foreach (['title', 'slug', 'keywords', 'description', 'image', 'banner'] as $key) {
                if ($key == 'banner') {
                    $meta_tags[$key] = $banner;
                } else {
                    $meta_tags[$key] = isset($data['meta_tags'][$key]) ? trim($data['meta_tags'][$key]) : '';
                }
            }

            $data['meta_tags'] = $meta_tags;
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
                $require_fields = array(
                    'title' => 'Collection title is empty',
                    'slug' => 'Slug is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'description' => '',
                    'image' => '',
                    'collect_method' => static::DEFAULT_COLLECT_METHOD,
                    'auto_conditions' => array(),
                    'sort_option' => 'desc:date',
                    'meta_tags' => array(),
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

    protected function _afterSave() {
        parent::_afterSave();

        if ($this->getLastActionFlag() === static::UPDATE_FLAG && $this->isModified('collect_method') && $this->data['collect_method'] != static::COLLECT_MANUAL) {
            Cron_Catalog_Product_CorrectCollectionIds::addQueue();
        }

        $keys = array('title', 'description');

        $index_keywords = array();

        foreach ($keys as $key) {
            if (isset($this->data[$key]) && !isset($index_keywords[$key])) {
                $index_keywords[$key] = strip_tags($this->data[$key]);
            }
        }

        $index_keywords = implode(' ', $index_keywords);

        if ($this->isModified('best_selling_start') || $this->isModified('best_selling_end')) {
            OSC::core('cron')->addQueue('catalog/collection_resetCacheProductQueue', ['collection_id' => $this->getId()]);
        }

        OSC::helper('backend/common')->indexAdd('', 'catalog', 'collection', $this->getId(), $index_keywords, array('filter_data' => array('collect_method' => $this->data['collect_method'])));
        OSC::helper('frontend/common')->indexAdd('', 'catalog', 'collection', $this->getId(), $index_keywords, array('filter_data' => array('collect_method' => $this->data['collect_method'])));
    }

    protected function _afterDelete() {
        parent::_afterDelete();

        if ($this->data['collect_method'] == static::COLLECT_MANUAL) {
            Cron_Catalog_Product_CorrectCollectionIds::addQueue();
        }

        OSC::helper('backend/common')->indexDelete('', 'catalog', 'collection', $this->getId());
        OSC::helper('frontend/common')->indexDelete('', 'catalog', 'collection', $this->getId());

        OSC::helper('catalog/collectionProductRel')->deleteByField('collection_id', $this->getId());
        OSC::helper('feed/common')->triggerDeleteBlock(['collection_id' => $this->getId()]);
        try {
            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getWriteAdapter();
            $DB->delete('alias', "destination='catalog/collection/{$this->getId()}'", 1);
        } catch (Exception $ex){

        }
    }

    protected $_preload_product_images = null;

    /**
     * @throws OSC_Exception_Runtime
     */
    public function preloadProductImages() {
        $product_ids = [];

        foreach ($this->getProducts() as $product) {
            $product_ids[] = $product->getId();
        }

        $str_product_ids = implode(',', $product_ids);
        $cache_key = "collectionPreloadProductImages|product_ids:,{$str_product_ids},|";

        $cache = OSC::core('cache')->get($cache_key);
        if ($cache !== false) {
            $collection = OSC::model('catalog/product_image')->getCollection();

            foreach ($cache as $value) {
                $collection->addItem(OSC::model('catalog/product_image')->bind($value));
            }

        } else {
            $cache_data = [];
            $collection = OSC::model('catalog/product_image')
                ->getCollection()
                ->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN)
                ->sort('position', 'ASC')
                ->sort('image_id', 'ASC')
                ->load();
            foreach ($collection as $model) {
                $cache_data[] = $model->data;
            }
            OSC::core('cache')->set($cache_key, $cache_data, OSC_CACHE_TIME);
        }

        foreach ($collection as $item) {
            $product_image_collection = $this->_preload_product_images[$item->data['product_id']];

            if (!($product_image_collection instanceof Model_Catalog_Product_Image_Collection)) {
                $product_image_collection = OSC::model('catalog/product_image')->getCollection();
            }
            $product_image_collection->addItem($item);

            $this->_preload_product_images[$item->data['product_id']] = $product_image_collection;
        }

        return $this->_preload_product_images;
    }

    public function getCollectionBanner() {
        if (isset($this->data['meta_tags']['banner'])) {
            $banner = $this->data['meta_tags']['banner'];

            if (isset($banner['option']) && $banner['option'] == 'off') {
                return ['enable' => 0];
            }

            if (isset($banner['option']) && $banner['option'] == 'current') {
                return [
                    'enable' => 1,
                    'title' => isset($banner['title']) && !!$banner['title'] ? $banner['title'] : $this->data['title'],
                    'url' => isset($banner['url']) ? $banner['url'] : '',
                    'pc' => OSC::core('aws_s3')->getStorageUrl($banner['pc']),
                    'mobile' => OSC::core('aws_s3')->getStorageUrl($banner['mobile'])
                ];
            }
        } 

        $banner_enable = OSC::helper('core/setting')->get('catalog/collection_banner/enable');

        if (isset($banner_enable) && $banner_enable != 0) {
            $banner_title = OSC::helper('core/setting')->get('catalog/collection_banner/title');
            $banner_url = OSC::helper('core/setting')->get('catalog/collection_banner/url');
            $banner_pc = OSC::helper('core/setting')->get('catalog/collection_banner/pc_image') ? OSC::core('aws_s3')->getStorageUrl(OSC::helper('core/setting')->get('catalog/collection_banner/pc_image')['file']) : '';
            $banner_mobile = OSC::helper('core/setting')->get('catalog/collection_banner/mobile_image') ? OSC::core('aws_s3')->getStorageUrl(OSC::helper('core/setting')->get('catalog/collection_banner/mobile_image')['file']) : '';

            return [
                'enable' => $banner_enable,
                'title' => isset($banner_title) && !!$banner_title  ? $banner_title : $this->data['title'],
                'url' => $banner_url,
                'pc' => $banner_pc,
                'mobile' => $banner_mobile
            ];            
        }

        return ['enable' => 0];
    }
}
