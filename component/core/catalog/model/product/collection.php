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
class Model_Catalog_Product_Collection extends Abstract_Core_Model_Collection {

    protected $_variant_counted = false;

    public function countVariant($reset = false) {
        if (!$this->_loaded) {
            return $this;
        }

        if ($this->_variant_counted && !$reset) {
            $this;
        }

        $product_ids = [];

        foreach ($this as $model) {
            $product_ids[] = $model->getId();
        }

        if (count($product_ids) > 0) {
            $product_ids = implode(',', $product_ids);

            OSC::core('database')->query("SELECT product_id, COUNT(id) as `variants` FROM " . OSC::model('catalog/product_variant')->getTableName(true) . " WHERE FIND_IN_SET(product_id, '{$product_ids}') GROUP BY product_id", null, 'count_product_variant');

            foreach (OSC::core('database')->fetchArrayAll('count_product_variant') as $row) {
                $this->getItemByKey($row['product_id'])->setTotalVariant($row['variants']);
            }
        }

        $this->_variant_counted = true;

        return $this;
    }

    /**
     * 
     * @param Model_Catalog_Product $product
     * @return $this
     */
    public function loadFrequentltyBoughtTogether(Model_Catalog_Product $product, int $limit = 10) {return $this->setNull();
        $product_ids = OSC::helper('catalog/frequentlyBoughtTogether')->fetch($product, $limit, 1, Helper_Catalog_Common::displayedProductRegistry());

        if (count($product_ids) > 0) {
            Helper_Catalog_Common::displayedProductRegister($product_ids);

            $this->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                    ->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                    ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                    ->load();
        } else {
            $this->setNull();
        }

        if ($this->length() < $limit) {
            try {
                $relatd_products = $this->getNullModel()->getCollection()->loadRelated($product, $limit - $this->length());

                foreach ($relatd_products as $product) {
                    $this->addItem($product);
                }
            } catch (Exception $ex) {
                
            }
        }

        return $this;
    }

    /**
     * 
     * @param Model_Catalog_Cart $cart
     * @return $this
     */
    public function loadFrequentltyBoughtTogetherByCart(Model_Catalog_Cart $cart, int $limit = 10) {return $this->setNull();
        $product_ids = [];

        foreach ($cart->getLineItems() as $line_item) {
            $product_ids[] = $line_item->data['product_id'];
        }

        $product_ids = OSC::helper('catalog/frequentlyBoughtTogether')->fetchByMultiProducts($product_ids, $limit, 1, Helper_Catalog_Common::displayedProductRegistry());

        if (count($product_ids) > 0) {
            Helper_Catalog_Common::displayedProductRegister($product_ids);

            $this->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                    ->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                    ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                    ->load();
        } else {
            $this->setNull();
        }

        if ($this->length() < $limit) {
            try {
                $cart_products = [];

                foreach ($cart->getLineItems() as $line_item) {
                    $cart_products[] = $line_item->getProduct();
                }

                $relatd_products = $this->getNullModel()->getCollection()->loadRelatedByList($cart_products, $limit - $this->length());

                foreach ($relatd_products as $product) {
                    $this->addItem($product);
                }
            } catch (Exception $ex) {
                
            }
        }

        return $this;
    }

    public function loadSameProductByCart(Model_Catalog_Cart $cart, int $limit = 10) {
        $product_ids = [];
        $customer_location_code = OSC::helper('catalog/common')->getCustomerLocationCode();

        foreach ($cart->getLineItems() as $line_item) {
            if ($line_item->isCrossSellMode()) {
                continue;
            }
            $productId = intval($line_item->data['product_id']);

            if ($productId > 0 && !in_array($productId, $product_ids)) {
                $product_ids[] = $line_item->data['product_id'];
            }
        }

        if (!empty($product_ids)) {
            $listProduct = OSC::model('catalog/product')->getNullCollection()
                ->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                ->load();

            $product_ids = OSC::helper('catalog/sameProduct')->fetchSameProduct($listProduct, $limit, 1, Helper_Catalog_Common::displayedProductRegistry());

            Helper_Catalog_Common::displayedProductRegister($product_ids);

            $this->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                ->addCondition('supply_location', $customer_location_code, OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_AND)
                ->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->load();
        } else {
            $this->setNull();
        }

        if ($this->length() < $limit) {
            try {
                $cart_products = [];

                foreach ($cart->getLineItems() as $line_item) {
                    $cart_products[] = $line_item->getProduct();
                }

                $relatd_products = $this->getNullModel()->getCollection()->loadRelatedByList($cart_products, $limit - $this->length());

                foreach ($relatd_products as $product) {
                    $this->addItem($product);
                }
            } catch (Exception $ex) {

            }
        }

        return $this;
    }

    /**
     * 
     * @param Model_Catalog_Cart $cart
     * @return $this
     */
    public function loadFrequentltyBoughtTogetherByOrder(Model_Catalog_Order $order, int $limit = 10) {return $this->setNull();
        $product_ids = [];

        foreach ($order->getLineItems() as $line_item) {
            $product_ids[] = $line_item->data['product_id'];
        }

        $customer_location_code = OSC::helper('catalog/common')->getCustomerLocationCode();

        $product_ids = OSC::helper('catalog/frequentlyBoughtTogether')->fetchByMultiProducts(
            $product_ids,
            $limit,
            1,
            Helper_Catalog_Common::displayedProductRegistry()
        );

        if (count($product_ids) > 0) {
            Helper_Catalog_Common::displayedProductRegister($product_ids);

            $this->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                ->addCondition('supply_location', $customer_location_code, OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_AND)
                ->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->load();
        } else {
            $this->setNull();
        }

        if ($this->length() < $limit) {
            try {
                $order_products = [];

                foreach ($order->getLineItems() as $line_item) {
                    $order_products[] = $line_item->getProduct();
                }

                $relatd_products = $this->getNullModel()->getCollection()->loadRelatedByList($order_products, $limit - $this->length());

                foreach ($relatd_products as $product) {
                    $this->addItem($product);
                }
            } catch (Exception $ex) {
                
            }
        }

        return $this;
    }

    /**
     * 
     * @param Model_Catalog_Product $product
     * @return $this
     */
    public function loadRelated(Model_Catalog_Product $product, int $limit = 10) {
        $related_entries = OSC::helper('catalog/search_product')->fetchRelated($product, $limit, $limit, Helper_Catalog_Common::displayedProductRegistry());

        $related_product_ids = [];
        $customer_location_code = OSC::helper('catalog/common')->getCustomerLocationCode();

        foreach ([$related_entries['docs'], $related_entries['suggestion']['docs']] as $docs) {
            foreach ($docs as $doc) {
                $related_product_ids[] = $doc['id'];
            }
        }

        if (count($related_product_ids) > 0) {
            Helper_Catalog_Common::displayedProductRegister($related_product_ids);

            $this->addCondition('product_id', $related_product_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                ->addCondition('supply_location', $customer_location_code, OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_AND)
                ->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->load();
        } else {
            $this->setNull();
        }

        return $this;
    }

    /**
     * 
     * @param array $products
     * @return $this
     */
    public function loadRelatedByList(array $products, int $limit = 10) {
        $related_entries = OSC::helper('catalog/search_product')->fetchRelatedByList($products, $limit, $limit, Helper_Catalog_Common::displayedProductRegistry());

        $related_product_ids = [];
        $customer_location_code = OSC::helper('catalog/common')->getCustomerLocationCode();

        foreach ([$related_entries['docs'], $related_entries['suggestion']['docs']] as $docs) {
            foreach ($docs as $doc) {
                $related_product_ids[] = $doc['id'];
            }
        }

        if (count($related_product_ids) > 0) {
            Helper_Catalog_Common::displayedProductRegister($related_product_ids);

            $this->addCondition('product_id', $related_product_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                ->addCondition('supply_location', $customer_location_code, OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_AND)
                ->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->load();
        } else {
            $this->setNull();
        }

        return $this;
    }

    /**
     * @throws OSC_Database_Model_Exception
     * @throws OSC_Exception_Runtime
     */
    public function loadUpSale($limit = 10, $country_code = null, $province_code = null) {
        $collectionIds = OSC::helper('core/setting')->get('payment/reference_transaction/upsale_collection');
        $products = [];

        if (count($collectionIds) > 0) {
            $tmpIds = [];

            $customer_location_code = OSC::helper('catalog/common')->getCustomerLocationCode();

            foreach ($collectionIds as $collectionId) {
                $collection = OSC::model('catalog/collection')->load($collectionId);
                $collection->loadProducts([
                    'page_size' => 50,
                    'before_load_callback' => function (Model_Catalog_Product_Collection $product_collection, $customer_location_code) {
                        $skip_ids = Helper_Catalog_Common::displayedProductRegistry();

                        $product_collection->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                            ->addCondition('supply_location', $customer_location_code, OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_AND)
                            ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                            ->addCondition('product_id', array_unique($skip_ids), OSC_Database::OPERATOR_NOT_IN, OSC_Database::RELATION_AND)
                        ;
                    }
                ]);

                $skip_ids = Helper_Catalog_Common::displayedProductRegistry();

                /* @var $product Model_Catalog_Product */
                foreach ($collection->getProducts() as $product) {
                    if (!in_array($product->getId(), $tmpIds) && !in_array($product->getId(), $skip_ids) && $product->isCampaignMode() && !$product->isPhotoUploadMode()) {
                        $cart_option_config = $product->getCartFrmOptionConfig($country_code, $province_code, ['flag_feed' => true]);

                        if (!empty($cart_option_config['product_types'])) {
                            $tmpIds[] = $product->getId();
                            $products[] = OSC::helper('catalog/product')->getCommonDataOfProduct($product);
                        }
                    }
                }
            }
        }

        /**
         * Random $limit items in this products list
         */
        $results = [];
        $randoms = array_rand($products, $limit);
        foreach ($randoms as $item) {
            $results[] = $products[$item];
        }

        return $results;
    }

    /**
     * 
     * @param Model_Catalog_Product $product
     * @return $this
     */
    public function loadBestSelling(int $limit = 10, string $customer_location = '') {
        $skip_ids = Helper_Catalog_Common::displayedProductRegistry();

        $customer_location_code = $customer_location ?: OSC::helper('catalog/common')->getCustomerLocationCode();
        $customer_country_code = preg_replace('/^,([A-Z]{2})_.*$/i', '\1', $customer_location_code);

        $blocked_countries = OSC::helper('core/country')->getBlockCountries();
        $blocked_countries = array_keys($blocked_countries);

        if ($customer_country_code && in_array($customer_country_code, $blocked_countries)) {
            return $this;
        }

        if (count($skip_ids) > 0) {
            $this->addCondition('product_id', $skip_ids, OSC_Database::NEGATION_MARK . OSC_Database::OPERATOR_IN);
        }

        $this->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
            ->addCondition('supply_location', $customer_location_code, OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_AND)
            ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND);

        $this->sort('solds', 'DESC')->sort('views', 'DESC')->setLimit($limit)->load();

        $loaded_ids = [];

        foreach ($this as $model) {
            $loaded_ids[] = $model->getId();
        }

        if (count($loaded_ids) > 0) {
            Helper_Catalog_Common::displayedProductRegister($loaded_ids);
        }

        return $this;
    }

    /**
     * 
     * @return $this
     */
    public function loadRecentlyViewed(int $limit = 10) {
        $product_ids = Helper_Catalog_Common::recentlyViewedProductGet();
        $customer_location_code = OSC::helper('catalog/common')->getCustomerLocationCode();

        if (count($product_ids) > 0) {
            $product_ids = array_slice($product_ids, 0, $limit);

            $this->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                ->addCondition('supply_location', $customer_location_code, OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_AND)
                ->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->load();
        } else {
            $this->setNull();
        }

        return $this;
    }

    protected $_variant_pre_loaded = false;

    /**
     * @throws OSC_Exception_Runtime
     */
    public function preLoadVariantCollection() {
        if ($this->_variant_pre_loaded) {
            return $this;
        }

        $this->_variant_pre_loaded = true;

        $product_ids = [];

        foreach ($this as $product) {
            $product_ids[] = $product->getId();
        }

        $collection = OSC::model('catalog/product_variant')->getCollection();
        if (count($product_ids) > 0) {
            $str_product_ids = implode(',', $product_ids);
            $cache_key = "preLoadVariantCollection|product_id:,{$str_product_ids},|";
            $cache = OSC::core('cache')->get($cache_key);

            if ($cache !== false) {
                foreach ($cache as $item) {
                    $collection->addItem(OSC::model('catalog/product_variant')->bind($item));
                }
            } else {
                $DB = OSC::core('database');
                $DB->select('*', 'product_variant', "product_id IN ({$str_product_ids})", null, null, 'fetch_variant');

                $cache_data = [];
                while ($row = $DB->fetchArray('fetch_variant')) {
                    $item = OSC::model('catalog/product_variant')->bind($row);
                    $collection->addItem($item);
                    $cache_data[] = $item->data;
                }

                $DB->free('fetch_variant');

                OSC::core('cache')->set($cache_key, $cache_data, OSC_CACHE_TIME);
            }
        }


        $_collection = [];

        /* @var $variant Model_Catalog_Product_Variant */

        foreach ($collection as $variant) {
            if (!($_collection[$variant->data['product_id']] instanceof Model_Catalog_Product_Variant_Collection)) {
                $_collection[$variant->data['product_id']] = OSC::model('catalog/product_variant')->getCollection();
            }

            $_collection[$variant->data['product_id']]->addItem($variant);

            $variant->setProduct($this->getItemByPK($variant->data['product_id']));
        }

        /* @var $product Model_Catalog_Product */

        foreach ($this as $product) {
            $variant_collection = isset($_collection[$product->getId()]) ?
                $_collection[$product->getId()] :
                OSC::model('catalog/product_variant')->getCollection();

            $product->setVariants($variant_collection);

            $array_variants = [];
            foreach ($variant_collection as $item) {
                $array_variants[] = $item->data;
            }
            $product->setArrayVariants($array_variants);
        }

        return $this;
    }

    protected $_image_pre_loaded = false;

    /**
     * @throws OSC_Exception_Runtime
     */
    public function preLoadImageCollection() {
        if ($this->_image_pre_loaded) {
            return $this;
        }

        $this->_image_pre_loaded = true;

        $product_ids = [];

        foreach ($this as $product) {
            $product_ids[] = $product->getId();
        }

        $collection = OSC::model('catalog/product_image')->getCollection();
        if (count($product_ids) > 0) {
            $str_product_ids = implode(',', $product_ids);
            $cache_key = "preLoadImageCollection|product_id:,{$str_product_ids},|";
            $cache = OSC::core('cache')->get($cache_key);

            if ($cache !== false) {
                foreach ($cache as $item) {
                    $collection->addItem(OSC::model('catalog/product_image')->bind($item));
                }
            } else {
                $DB = OSC::core('database');
                $DB->select('*', 'catalog_product_image', "product_id IN ({$str_product_ids})", null, null, 'fetch_image');

                $cache_data = [];
                while ($row = $DB->fetchArray('fetch_image')) {
                    $item = OSC::model('catalog/product_image')->bind($row);
                    $collection->addItem($item);
                    $cache_data[] = $item->data;
                }

                $DB->free('fetch_image');

                OSC::core('cache')->set($cache_key, $cache_data, OSC_CACHE_TIME);
            }
        }

        $_image_collection = [];

        /* @var $variant Model_Catalog_Product_Variant */

        foreach ($collection as $image) {
            if (!($_image_collection[$image->data['product_id']] instanceof Model_Catalog_Product_Image_Collection)) {
                $_image_collection[$image->data['product_id']] = OSC::model('catalog/product_image')->getCollection();
            }

            $_image_collection[$image->data['product_id']]->addItem($image);

            $image->setProduct($this->getItemByPK($image->data['product_id']));
        }

        /* @var $product Model_Catalog_Product */

        foreach ($this as $product) {
            $image_collection = isset($_image_collection[$product->getId()]) ?
                $_image_collection[$product->getId()] :
                OSC::model('catalog/product_image')->getCollection();

            $product->setImages($image_collection);

            $array_images = [];
            foreach ($image_collection as $item) {
                $array_images[] = $item->data;
            }
            $product->setArrayImages($array_images);
        }

        return $this;
    }

    protected $_mockup_remove_pre_loaded = false;

    /**
     * @throws OSC_Exception_Runtime
     */
    public function preLoadMockupRemove() {
        if ($this->_mockup_remove_pre_loaded) {
            return $this;
        }

        $this->_mockup_remove_pre_loaded = true;

        $cache_key = "preLoadMockupRemove";
        $list_mockup_remove = OSC::core('cache')->get($cache_key);

        if ($list_mockup_remove !== false) {
            $list_mockup_remove = OSC::model('catalog/printTemplate_mockupRel')
                ->getCollection()
                ->getListIdMockupRemove()
                ->toArray();
            OSC::core('cache')->set($cache_key, $list_mockup_remove, OSC_CACHE_TIME);
        }

        foreach ($this as $product) {
            $product->setMockupRemove($list_mockup_remove);
        }

        return $this;
    }

    protected $_product_type_variant_pre_loaded = false;

    /**
     * @throws OSC_Exception_Runtime
     */
    public function preLoadProductTypeVariant() {
        if ($this->_product_type_variant_pre_loaded) {
            return $this;
        }

        $this->_product_type_variant_pre_loaded = true;

        $this->preLoadVariantCollection();

        $product_type_variant_ids = [];

        /* @var $product Model_Catalog_Product */

        $preload_design_ids = [];
        $preload_upload_mode = [];
        $DB = OSC::core('database');
        foreach ($this as $product) {
            /* @var $variant Model_Catalog_Product_Variant */

            foreach ($product->getVariants(false, true) as $variant) {
                if ($variant->isCampaign()) {
                    $product_type_variant_ids[] = $variant->data['product_type_variant_id'];
                }
            }

            foreach ($product->getDesignIds() as $design_id) {
                $preload_design_ids[$design_id] = $design_id;
                $preload_upload_mode[$design_id] = 0;
            }
        }

        /* Preload photo upload mode product */
        if (count($preload_design_ids) > 0) {
            $preload_design_ids = implode(',', array_unique($preload_design_ids));
            $DB->select(
                'design_id',
                'personalized_design',
                'design_id IN (' . $preload_design_ids . ') AND is_uploader = 1',
                null,
                null,
                'fetch_upload_mode'
            );
            while ($row = $DB->fetchArray('fetch_upload_mode')) {
                $preload_upload_mode[$row['design_id']] = intval($row['design_id']);
            }
            $DB->free('fetch_upload_mode');
        }
        OSC::helper('catalog/campaign')->setPreloadUploadMode($preload_upload_mode);

        $product_type_variant_ids = array_unique($product_type_variant_ids);

        if (count($product_type_variant_ids) < 1) {
            return $this;
        }

        $collection = OSC::model('catalog/productType_variant')->getCollection();
        $str_product_type_variant_ids = implode(',', $product_type_variant_ids);
        $cache_key = "preLoadProductTypeVariant|product_type_variant_id:,{$str_product_type_variant_ids},|";
        $cache = OSC::core('cache')->get($cache_key);

        $product_type_ids = [];
        if ($cache !== false) {
            foreach ($cache as $item) {
                $product_type_ids[$item['product_type_id']] = intval($item['product_type_id']);
                $collection->addItem(OSC::model('catalog/productType_variant')->bind($item));
            }
        } else {
            $DB->select('*', 'product_type_variant', "id IN ({$str_product_type_variant_ids})", null, null, 'fetch_product_type_variant');

            $cache_data = [];
            while ($row = $DB->fetchArray('fetch_product_type_variant')) {
                $product_type_ids[$row['product_type_id']] = intval($row['product_type_id']);
                $item = OSC::model('catalog/productType_variant')->bind($row);
                $collection->addItem($item);
                $cache_data[] = $item->data;
            }

            $DB->free('fetch_product_type_variant');

            OSC::core('cache')->set($cache_key, $cache_data, OSC_CACHE_TIME);
        }

        $preload_product_types = OSC::model('catalog/productType')
            ->getCollection()
            ->load($product_type_ids);
        OSC::helper('catalog/campaign')->setPreloadProductTypes($preload_product_types);
        OSC::helper('catalog/campaign')->setPreloadProductTypeVariants($collection);

        /* @var $product Model_Catalog_Product */

        foreach ($this as $product) {
            /* @var $variant Model_Catalog_Product_Variant */

            foreach ($product->getVariants(false, true) as $variant) {
                if ($variant->isCampaign()) {
                    $product_type_variant = $collection->getItemByPK($variant->data['product_type_variant_id']);

                    if ($product_type_variant instanceof Model_Catalog_ProductType_Variant) {
                        $variant->setProductTypeVariant($product_type_variant);
                        $variant->setArrayProductTypeVariant($product_type_variant->data);
                    }
                }
            }
        }

        return $this;
    }

    protected $_ab_test_product_price_pre_loaded = false;

    public function preLoadABTestProductPrice() {
        if ($this->_ab_test_product_price_pre_loaded) {
            return $this;
        }

        $this->_ab_test_product_price_pre_loaded = true;

        $client_location = OSC::helper('catalog/common')->getCustomerIPLocation();
        $country_code = $client_location['country_code'];

        if ($country_code) {
            return $this;
        }

        $preload_data = OSC::helper('autoAb/productPrice')->getABProductPriceOfCountry($country_code);
        OSC::helper('autoAb/productPrice')->setABProductPriceOfCountry($preload_data, $country_code);

        return $this;
    }

    public function getDesignIdByProductList() {
        try {
            $design_ids = [];

            foreach ($this as $key => $product) {
                $design_ids[$product->getId()] = $product->getDesignIdsByProduct();
            }

            return $design_ids;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
