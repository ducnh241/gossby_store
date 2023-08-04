<?php

class Model_Catalog_ProductType extends Abstract_Core_Model {

    const STATE_ENABLE = 1;
    const STATE_AMAZON_ENABLE = 1;
    const STATE_DISABLE = 0;
    const STATE_AMAZON_DISABLE = 1;

    protected $_table_name = 'product_type';
    protected $_pk_field = 'id';
    protected $_ukey_field = 'ukey';

    protected $_allow_write_log = true;

    protected $_product_type_variant_collection = null;
    protected $_product_pack_collection = null;
    protected $_product_type_description = '';

    // status row value
    const STATUS = [
        'can_create_product' => 1,
        'can_not_create_product' => 0
    ];

    /**
     * @param boolean $reload
     * @return Model_Catalog_ProductType_Variant_Collection
     * @throws OSC_Exception_Runtime
     */
    public function getProductTypeVariants($reload = false) {
        if ($this->_product_type_variant_collection === null || $reload) {
            $this->_product_type_variant_collection = OSC::model('catalog/productType_variant')
                ->getCollection();

            if ($this->getId() > 0) {
                $this->_product_type_variant_collection
                    ->addCondition('status', 1, OSC_Database::OPERATOR_EQUAL)
                    ->addCondition('product_type_id', $this->getId())
                    ->load();
                $this->_product_type_variant_collection->preLoadModelData();

                foreach ($this->_product_type_variant_collection as $product_type_variant) {
                    $product_type_variant->setProductType($this);
                }
            }
        }

        return $this->_product_type_variant_collection;
    }

    /**
     * @param boolean $reload
     * @return Model_Catalog_Product_Pack_Collection|OSC_Database_Model_Collection
     * @throws OSC_Exception_Runtime
     */
    public function getProductPacks($reload = false) {
        if ($this->_product_pack_collection === null || $reload) {
            $this->_product_pack_collection = OSC::model('catalog/product_pack')->getCollection();

            if ($this->getId() > 0) {
                $this->_product_pack_collection->addCondition('product_type_id', $this->getId())->load();
                $this->_product_pack_collection->preLoadModelData();

                foreach ($this->_product_pack_collection as $product_pack) {
                    $product_pack->setProductType($this);
                }
            }
        }

        return $this->_product_pack_collection;
    }

    public function getAllProductTypeInUsing()
    {
        $product_type_in_use = OSC::helper('catalog/common')->fetchProductTypes();
        return OSC::model('catalog/productType')->getCollection()
            ->addField('id', 'ukey', 'title')
            ->addCondition('ukey', $product_type_in_use, OSC_Database::OPERATOR_IN)
            ->sort('title')
            ->load();
    }

    public function getSizeGuideImageUrl() {
        if (!$this->data['size_guide_data']['image']) return "";
        return OSC::core('aws_s3')->getStorageUrl($this->data['size_guide_data']['image']);
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);
        foreach (['identifier', 'default_for_light_design', 'default_for_dark_design', 'size_guide_data'] as $column) {
            if (isset($data[$column])) {
                $data[$column] = OSC::decode($data[$column], true);
            }
            if ($column === 'size_guide_data' && $data[$column] === null) {
                $data[$column] = [
                    'allow' => 0,
                    'image' => '',
                    'data' => ''
                ];
            }
        }
    }

    protected function _preDataForSave(&$data)
    {
        parent::_preDataForSave($data);

        foreach (['default_for_light_design', 'default_for_dark_design', 'size_guide_data'] as $column) {
            if (isset($data[$column])) {
                $data[$column] = OSC::encode($data[$column]);
            }
        }
    }

    public function getPackAutoDefault() {
        $is_pack_default = Model_Catalog_Product_Pack::STATE_PACK_AUTO['OFF'];
        $is_pack_auto_off = Model_Catalog_Product_Pack::STATE_PACK_AUTO['ON'];

        try {
            $collection = OSC::model('catalog/product_pack')
                ->getCollection()
                ->addCondition('product_type_id', $this->data['id'])
                ->load();

            if ($collection->length() > 0) {
                foreach ($collection as $pack) {
                    if ($pack->data['is_pack_auto'] == $is_pack_auto_off) {
                        $is_pack_default = $is_pack_auto_off;
                        break;
                    }
                }
            }

            return $is_pack_default;
        } catch (Exception $ex) {
        }
    }

}