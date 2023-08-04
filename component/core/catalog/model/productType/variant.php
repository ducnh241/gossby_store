<?php

class Model_Catalog_ProductType_Variant extends Abstract_Core_Model {
    protected $_table_name = 'product_type_variant';
    protected $_pk_field = 'id';
    protected $_ukey_field = 'ukey';

    protected $_allow_write_log = true;
    protected $_obj_fields = [
        'base_cost_configs', 'best_price'
    ];

    const STATE_ENABLE = 1;
    const STATE_DISABLE = 0;

    /**
     *
     * @var Model_Catalog_ProductType
     */
    protected $_product_type_model = null;
    protected $_product_type_description = '';

    /**
     *
     * @var Model_Catalog_Order_Item_Collection
     */
    protected $_location_price_collection = null;

    /**
     *
     * @var Model_Catalog_SupplierVariantRel_Collection
     */
    protected $_print_template_model = null;

    /**
     *
     * @return \Model_Catalog_ProductType
     */
    public function getProductType($useCache = false): Model_Catalog_ProductType {
        if ($this->_product_type_model === null) {
            $product_type_id = $this->data['product_type_id'];

            if (!empty($product_type_id)) {
                $cache_key = "|model.catalog.productType|product_type_id,{$product_type_id},|";
                if ($useCache && ($cache = OSC::core('cache')->get($cache_key)) !== false) {
                    $this->_product_type_model = OSC::model('catalog/productType')->bind($cache);
                } else {
                    $this->_product_type_model = OSC::model('catalog/productType')->load($product_type_id);

                    OSC::core('cache')->set($cache_key, $this->_product_type_model->data, OSC_CACHE_TIME);
                }
            } else {
                $this->_product_type_model = new Model_Catalog_ProductType;
            }
        }

        return $this->_product_type_model;
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (!is_array($data['base_cost_configs'])) {
            $data['base_cost_configs'] = [];
        }

        foreach ($data['base_cost_configs'] as $key => $value) {
            $data[$key] = [
                'quantity' => intval($value['quantity']),
                'base_cost' => OSC::helper('catalog/common')->floatToInteger(floatval($value['base_cost']))
            ];
        }

        foreach ($this->_obj_fields as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach ($this->_obj_fields as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }

        if (!is_array($data['base_cost_configs'])) {
            $data['base_cost_configs'] = [];
        }

        foreach ($data['base_cost_configs'] as $key => $value) {
            $data[$key] = [
                'quantity' => intval($value['quantity']),
                'base_cost' => OSC::helper('catalog/common')->integerToFloat(intval($value['base_cost']))
            ];
        }
    }

    public function getPrintsTemplate(): Model_Catalog_SupplierVariantRel_Collection {
        if ($this->_print_template_model === null) {
            $product_type_variant_id = $this->getId();;

            if (!empty($product_type_variant_id)) {
                $this->_print_template_model = OSC::model('catalog/supplierVariantRel')->getCollection()->addCondition('product_type_variant_id', $product_type_variant_id)->load();
            } else {
                 $this->_print_template_model = new Model_Catalog_SupplierVariantRel_Collection;
            }
        }

        return $this->_print_template_model;
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

    /**
     * @param false $remove_opt_key_from_opt_value_key
     * @return array[]
     * @throws OSC_Exception_Runtime
     */
    public function getOptionValues() {
        $pairs = explode('_', explode('/', $this->data['ukey'], 2)[1]);
        $pairs = array_map(function ($data) {
            $data = explode(':', $data);
            return [
                'option_id' => $data[0],
                'option_value_id' => $data[1]
            ];
        }, $pairs);

        $option_ids = [];
        $option_value_ids = [];

        foreach ($pairs as $pair) {
            $option_ids[] = $pair['option_id'];
            $option_value_ids[] = $pair['option_value_id'];
        }

        $option_collection = OSC::model('catalog/productType_option')->getCollection()->load($option_ids);
        $option_value_collection = OSC::model('catalog/productType_optionValue')->getCollection()->load($option_value_ids);

        foreach ($pairs as $idx => $pair) {
            $option = $option_collection->getItemByPK($pair['option_id']);

            if (!$option) {
                throw new Exception('Option #' . $pair['option_id'] . ' is not exist');
            }

            $option_value = $option_value_collection->getItemByPK($pair['option_value_id']);

            if (!$option_value) {
                throw new Exception('Option value #' . $pair['option_value_id'] . ' is not exist');
            }

            $pair['option_key'] = $option->getUkey();
            $pair['option_value_key'] = $option_value->getUkey();

            $pair['type'] = $option->data['type'];
            $pair['title'] = $option->data['title'];
            $pair['value'] = $option_value->data['title'];
            $pair['meta_data'] = $option_value->data['meta_data'];

            $pairs[$idx] = $pair;
        }

        $keys = implode('|', array_map(function ($item) {
            return $item['option_key'] . ':' . static::removeOptKeyFromOptKeyValue($item['option_value_key']);
        }, $pairs));

        return ['keys' => $keys, 'items' => $pairs];
    }

    public function getColorOption() {
        $pairs = explode('_', explode('/', $this->data['ukey'], 2)[1]);
        $pairs = array_map(function($data) {
            $data = explode(':', $data);
            return [
                'option_id' => $data[0],
                'option_value_id' => $data[1]
            ];
        }, $pairs);

        foreach ($pairs as $pair) {
            if (in_array($pair['option_id'], ['3', '5', '6', '7'])) {
                $option_value = OSC::model('catalog/productType_optionValue')->load($pair['option_value_id']);

                $color = explode('/', $option_value->data['ukey'])[1];
            }
        }

        return $color;
    }

    public static function removeOptKeyFromOptKeyValue($option_value_key) {
        return explode('/', $option_value_key, 2)[1];
    }

    /**
     *
     * @param boolean $reload
     * @return Model_Catalog_ProductType_VariantLocationPrice_Collection|OSC_Database_Model_Collection
     * @throws OSC_Exception_Runtime
     */
    public function getLocationPrices($reload = false) {
        if ($this->_location_price_collection === null || $reload) {
            $this->_location_price_collection = OSC::model('catalog/productType_variantLocationPrice')
                ->getCollection()
                ->addCondition('product_type_variant_id', $this->getId())
                ->load();

            foreach ($this->_location_price_collection as $location_price) {
                $location_price->setProductTypeVariant($this);
            }
        }

        return $this->_location_price_collection;
    }

    /**
     * @return bool
     * @throws OSC_Exception_Runtime
     */
    public function hasLocationPrices(): bool {
        return $this->getLocationPrices()->length() > 0;
    }

    public function getBestPriceByStore($country_code = '', $flag_feed = false) {
        $location = OSC::helper('core/common')->getClientLocation();
        $country_code_location = $flag_feed ? $country_code : $location['country_code'];

        $best_price_data = $this->data['best_price'];

        return $best_price_data[$country_code_location];
    }

    public function hasBestPriceInCountryByStore($country_code) {
        $best_price_data = $this->data['best_price'];

        return isset($best_price_data[$country_code]);
    }

    protected function _afterSave()
    {
        parent::_afterSave();

        //Update price for product to filter
//        OSC::core('cron')->addQueue('catalog/product_seedingProductPrice', null,
//            [
//                'requeue_limit' => -1, 'estimate_time' => 60 * 60
//            ]
//        );
    }
}