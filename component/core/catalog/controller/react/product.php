<?php

class Controller_Catalog_React_Product extends Abstract_Frontend_ReactApiController {
    public function actionCollection() {
        $collection = OSC::model('catalog/collection');

        $id = intval($this->_request->get('id')) ?? 0;

        if ($id < 1) {
            $this->error('Collection ID is incorrect', $this::CODE_BAD_REQUEST, ['log_code' => $this::CODE_COLLECTION_PRODUCT_MISSING_PARAM]);
        }

        try {
            $collection->load($id);

            $this->sendSuccess($collection->toArray());
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetListProductByCollection() {
        $page = intval($this->_request->get('page'));
        $page = max($page, 1);
        $size = intval($this->_request->get('size'));
        $size = $size > 0 && $size <= 20 ? $size : 10;

        $collection_id = intval($this->_request->get('collection_id'));
        if ($collection_id < 1) {
            $this->sendError('Collection ID is incorrect', $this::CODE_BAD_REQUEST);
        }

        $_filters = $this->_request->get('filter_id_options');

        $filters = OSC::helper('filter/common')->validateFilterOptions($_filters);

        $sort = trim($this->_request->get('sort', 'default'));

        $this->apiOutputCaching([
            'collection_id' => $collection_id,
            'page' => $page,
            'size' => $size,
            'sort' => $sort,
            'filters' => $filters
        ], 0, ['using_customer_shipping_location']);

        $collection = OSC::model('catalog/collection');
        try {
            $collection->load($collection_id);
            if ($sort == 'default') {
                $sort = $collection->data['sort_option'];
            }

            $collection->loadProducts([
                'page_size' => $size,
                'page' => $page,
                'filters' => $filters,
                'sort' => $sort,
                'before_load_callback' => function (Model_Catalog_Product_Collection $product_collection) {
                    $product_collection->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                        ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND);
                },
                'top' => $collection->data['top']
            ]);

            $products = $collection->getProducts();
            $page_index = $products->getCurrentPage();
            $page_size = $products->getPageSize();
            $total_item = $collection->collectionLength([
                'before_load_callback' => function (Model_Catalog_Product_Collection $product_collection) {
                    $product_collection->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                        ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND);
                }
            ]);
            $total_item = intval($collection->data['top']) > 0 ? min($collection->data['top'], $total_item) : $total_item;

            $collection_url = $collection->getDetailUrl();

            if (isset($page) && ($page > 1)) {
                $collection_url = $collection_url . '/page/' . $page;
            }

            $auto_conditions = $collection->data['auto_conditions']['conditions'] ?? [];

            $options = [];
            if (is_array($auto_conditions) && (in_array('price', array_column($auto_conditions, 'field'))
                    || in_array('compare_at_price', array_column($auto_conditions, 'field')))) {
                $options['skip_ab_test_price'] = true;
            }

            $result = [
                'collection' => OSC::helper('catalog/product')->formatCollectionApi($collection),
                'products' => OSC::helper('catalog/product')->formatProductApi($products, $options),
                'meta_data' => [
                    'canonical' => $collection_url,
                    'url' => $collection_url,
                    'seo_title' => $collection->data['meta_tags']['title'] ?: $collection->data['title'],
                    'seo_image' => $collection->getOgImageUrl(),
                    'seo_description' => $collection->data['meta_tags']['description'],
                    'seo_keywords' => $collection->data['meta_tags']['keywords']
                ],
                'banner' => $collection->getCollectionBanner(),
                'page' => $page_index,
                'size' => $page_size,
                'total' => intval($total_item),
                'sort' => [
                    'options' => OSC::helper('filter/search')->getSortOptions(),
                    'default' => $collection->data['sort_option']
                ]
            ];

            if (intval($total_item) < 1) {
                OSC::helper('core/common')->sendNotifyEmptyProduct($collection_id, $collection_url);
            }

            $options = [];

            if ($collection->data['meta_tags']['description']) {
                $options['sref_desc'] = 1;
            }

            $this->sendSuccess($result, $options);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetProductDetail() {
        $id = intval($this->_request->get('id'));
        $ukey = strval($this->_request->get('ukey'));
        $collection_id = intval($this->_request->get('collection_id'));
        $variant_id = intval($this->_request->get('variant'));
        $product = OSC::model('catalog/product');
        $is_simple = intval($this->_request->get('is_simple'));
        $is_simple = in_array($is_simple, [0, 1]) ? $is_simple : 0;
        $atp = intval($this->_request->get('atp')) == 1 ? 1 : 0;
        $force_change_location = strval($this->_request->get('fcl'));
        $flag_feed = false;

        $out_put_options = [
            'using_customer_shipping_location',
            'atp' => $atp
        ];

        try {
            if ($id > 0) {
                $product = OSC::helper('catalog/product')->getProduct(['id' => $id], true);
            } else {
                if (strlen($ukey) != 15) {
                    $this->sendError('Not Found', $this::CODE_NOT_FOUND);
                }

                $product = OSC::helper('catalog/product')->getProduct(['ukey' => $ukey], true);
            }

            $key_first_product_view = OSC_Controller::makeRequestChecksum('first_product_view', OSC_SITE_KEY);
            $value_first_product_view = OSC::cookieGet($key_first_product_view);
            if (empty($value_first_product_view)) {
                OSC::cookieSetCrossSite($key_first_product_view, $product->getId());
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }

        try {
            // handle sref source, sref dest product
            Observer_Catalog_Common::handleSrefSourceDestByProduct($product);
        } catch (Exception $ex) { }

        try {
            // handle abtest tab
            $ab_test_cookie_data = OSC::helper('frontend/frontend')->handleAbtestTab($product);
        } catch (Exception $ex) { }

        $cache_key = [
            'id' => $id,
            'ukey' => $ukey,
            'collection_id' => $collection_id,
            'variant_id' => $variant_id,
            'atp' => $atp,
            'is_simple' => $is_simple
        ];

        $cache_key['ab_test_ver_4_tab_product_value'] = null;

        if (!empty($ab_test_cookie_data['key']) &&
            !empty($ab_test_cookie_data['value']) &&
            $ab_test_cookie_data['key'] === OSC::AB_VER4_TAB_PRODUCT['key']) {
            $cache_key['ab_test_ver_4_tab_product_value'] = $ab_test_cookie_data['value'];
        }

        $out_put_options['tracking_event'] = [
            'id' => $id,
            'ukey' => $ukey,
            'variant_id' => $variant_id
        ];

        $country_code = '';
        $province_code = '';
        //Handle location for bot
        if ($force_change_location && !OSC::cookieGet('cart-quantity')) {
            $country_code = $force_change_location;
            $flag_feed = true;
            $province_code = null;
            $out_put_options[] = 'flag_feed';
            OSC::cookieSetCrossSite('customer_country_code', $country_code);
            OSC::cookieSetCrossSite('customer_province_code', $province_code);
        }

        $this->apiOutputCaching($cache_key, 0, $out_put_options);

        $result = [
            'product' => [],
            'json_schema' => []
        ];

        $result['available'] = true;

        try {
            if ($product->data['discarded'] == 1) {
                throw new Exception('Product is not exists', $this::CODE_NOT_FOUND);
            }
            if (!$country_code) {
                $location = OSC::helper('catalog/common')->getCustomerShippingLocation();
                $country_code = $location['country_code'];
                $province_code = $location['province_code'];
            }

        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }

        $product_detail_url = $product->getDetailUrl(null, true);

        /* Check if product not supply in location, return new result */
        if (!$product->isSupplyInLocation($country_code . '_' . $province_code)) {
            $image_url = $product->getFeaturedImageUrl(false, true);
            if ($image_url) {
                $image_url = OSC::helper('core/image')->imageOptimize($image_url, 400, 400, true);
            }

            $result['product'] = [
                'product_id' => $product->data['product_id'],
                'description' => $product->data['description'],
                'amp_url' => $product->getAmpUrl(),
                'avatar' => OSC::wrapCDN($image_url),
                'identifier' => $product->getProductIdentifier(),
                'virtual_title' => $product->getProductTitle(),
                'topic' => $product->data['topic'],
                'title' => $product->data['title'],
                'url' => $product_detail_url,
                'meta_data' => [
                    'canonical' => $product_detail_url,
                    'url' => $product_detail_url,
                    'seo_title' => $product->data['meta_tags']['title'] ?: $product->getProductTitle(true),
                    'seo_image' => $product->getOgImageUrl(),
                    'seo_keywords' => $product->getProductKeyWord(),
                    'seo_description' => $product->data['meta_tags']['description']
                ],
            ];

            $options = [];
            $result['available'] = false;

            try {
                OSC::helper('core/common')->sendNotifyHiddenProduct($product->getId(), $country_code, $province_code);
            } catch (Exception $exception) {
                //
            }

            $this->sendSuccess($result, $options);
        }

        /* @var $variant Model_Catalog_Product_Variant */
        if ($product->isCampaignMode()) {
            $variant = $product->getSelectedOrFirstAvailableVariant(false, $country_code, $province_code);
        } else {
            $variant = $product->getSelectedOrFirstAvailableVariant();
        }

        if (!$variant) {
            $this->sendError('Variant is not exists', $this::CODE_NOT_FOUND);
        }

        $image_url = $variant ? $variant->getImageUrl(false, false, true) : '';

        if (!$image_url) {
            $image_url = $product->getFeaturedImageUrl(false, true);
        }

        if ($image_url) {
            $image_url = OSC::helper('core/image')->imageOptimize($image_url, 400, 400, true);
        }

        $cut_off_timestamp = OSC::helper('catalog/common')->getCutOffTimestamp($variant);
        $cut_off_title = trim($this->setting('shipping/cut_off_time/title'));

        $price = 0;
        $compare_at_price = 0;

        if ($variant && $variant->ableToOrder()) {
            $price_data = $variant->getPriceForCustomer($country_code, $flag_feed, boolval($atp));
            $price = $price_data['price'];
            $compare_at_price = $price_data['compare_at_price'];
        }

        if (empty($is_simple) || $is_simple !== 1) {
            $result['json_schema'] = OSC::helper('catalog/product')->getJsonSchema(
                [
                    'product' => $product,
                    'product_detail_url' => $product_detail_url,
                    'variant' => $variant,
                    'price' => $price
                ]
            );

            try {
                // handle sref source, sref dest product
                Observer_Catalog_Common::handleSrefSourceDestByProduct($product);
            } catch (Exception $ex) { }

            try {
                // handle abtest tab
                $ab_test_cookie_data = OSC::helper('frontend/frontend')->handleAbtestTab($product);
            } catch (Exception $ex) { }
        }

        $country_code_place_of_manufacture = OSC::helper('core/country')->getCountryCodePlaceOfManufacture();
        $is_live_preview = $product->isLivePreview();

        if ($collection_id) {
            try {
                $collection = OSC::model('catalog/collection')->load($collection_id);
                $product->data['collection']['collection_name'] = $collection->data['title'];
                $product->data['collection']['url'] = $collection->getDetailUrl();
            } catch (Exception $ex) {
                $collection_product = $product->getCollections()->getItem();
                $product->data['collection']['collection_name'] = $collection_product->data['title'];
                $product->data['collection']['url'] = $collection_product->getDetailUrl();
            }
        } else {
            $collection_products = $product->getCollections();
            if ($collection_products->length() > 0) {
                $collection_product = $product->getCollections()->getItem();
                $product->data['collection']['collection_name'] = $collection_product->data['title'];
                $product->data['collection']['url'] = $collection_product->getDetailUrl();
            } else {
                $product->data['collection']['collection_name'] = '';
                $product->data['collection']['url'] = '';
            }
        }

        $result['product'] = [
            'product_id' => $product->data['product_id'],
            'collection' => [
                'title' =>  $product->data['collection']['collection_name'],
                'link' =>   $product->data['collection']['url']
            ],
            'meta_data' => [
                'canonical' => $product_detail_url,
                'url' => $product_detail_url,
                'seo_title' => $product->data['meta_tags']['title'] ?: $product->getProductTitle(true),
                'seo_image' => $product->getOgImageUrl($image_url),
                'seo_keywords' => $product->getProductKeyWord(),
                'seo_description' => $product->data['meta_tags']['description']
            ],
            'seo_tags' => $product->getProductSeoTags(),
            'is_live_preview' => $is_live_preview,
            'upc' => $product->data['upc'],
            'sku' => $product->data['sku'],
            'slug' => $product->data['slug'],
            'url' => $product_detail_url,
            'amp_url' => $product->getAmpUrl(),
            'title' => $product->data['title'],
            'virtual_title' => $product->getProductTitle(),
            'topic' => $product->data['topic'],
            'identifier' => $product->getProductIdentifier(),
            'description' => $product->data['description'],
            'content' => $product->data['content'],
            'price' => $price,
            'compare_at_price' => $compare_at_price,
            'cut_off_time' => [
                'title' => $cut_off_timestamp > 0 && $cut_off_title ? str_replace(['{{date}}', '{{tdate}}', '{{sdate}}', '{{tsdate}}', '{{date_time}}', '{{tdate_time}}', '{{sdate_time}}', '{{tsdate_time}}'], [date('d/m/Y', $cut_off_timestamp), date('M d, Y', $cut_off_timestamp), date('d/m', $cut_off_timestamp), date('M d', $cut_off_timestamp), date('H:i d/m/Y', $cut_off_timestamp), date('H:i M d, Y', $cut_off_timestamp), date('H:i d/m', $cut_off_timestamp), date('H:i M d', $cut_off_timestamp)], $cut_off_title) : '',
                'message' => $cut_off_timestamp > 0 ? str_replace(['{{date}}', '{{tdate}}', '{{sdate}}', '{{tsdate}}', '{{date_time}}', '{{tdate_time}}', '{{sdate_time}}', '{{tsdate_time}}'], [date('d/m/Y', $cut_off_timestamp), date('M d, Y', $cut_off_timestamp), date('d/m', $cut_off_timestamp), date('M d', $cut_off_timestamp), date('H:i d/m/Y', $cut_off_timestamp), date('H:i M d, Y', $cut_off_timestamp), date('H:i d/m', $cut_off_timestamp), date('H:i M d', $cut_off_timestamp)], $this->setting('shipping/cut_off_time/' . ($cut_off_timestamp <= time() ? 'message_after_time' : 'message_before_time'))) : '',
                'is_reached' => $cut_off_timestamp < time()
            ],
            'personalized_in' => [
                'country_code' => mb_strtolower($country_code_place_of_manufacture),
                'country_name' => OSC::helper('core/country')->getCountryTitle($country_code_place_of_manufacture)
            ],
            'delivery_time' => OSC::helper('catalog/react_common')->getDeliveryTime($variant),
            'countdown_expire_timestamp' => OSC::helper('dls/catalog_product')->getCampaignExpireEstimate($product),
            'tags' => $product->data['tags'],
            'avatar' => OSC::wrapCDN($image_url)
        ];

        $selected_product_variant_id = !empty($variant_id) ? $variant_id : ($variant->data['id'] ?? '');

        if ($product->isCampaignMode()) {
            // Comment vì anh Quang Anh request photo upload chuyển sang form campaign (form thẳng)
            // $product_mode = $product->isPhotoUploadMode() ? 'campaign2' : 'campaign';
            $product_mode = 'campaign';

            $cart_option_config = $product->getCartFrmOptionConfig($country_code, $province_code, [
                'atp' => $atp,
                'flag_url_from_feed' => $flag_feed
            ]);

            if (empty($cart_option_config['product_types'])) {
                try {
                    OSC::helper('core/common')->sendNotifyHiddenProduct($product->getId(), $country_code, $province_code);
                } catch (Exception $exception) {

                }

                $result['available'] = false;
                $this->sendSuccess($result);
            }

            $campaign_config = OSC::helper('catalog/campaign')->prepareCampaignConfig($product);

            $list_product_variants = $cart_option_config['product_variants'];
            $selected_product_variant_ukey = array_column($list_product_variants, 'ukey')[array_search($selected_product_variant_id, array_column($list_product_variants, 'product_variant_id'))];

            $result['product']['cart_form_config'] = [
                'campaign_config' => [
                    'product_variant_id' => $selected_product_variant_id,
                    'product_variant_ukey' => $selected_product_variant_ukey,
                    'cart_option_config' => $cart_option_config,
                    'campaign_config' => $campaign_config
                ]
            ];

            foreach ($list_product_variants as $product_variant) {
                $result['product']['variants'][$product_variant['id']] = [
                    'sku' => $product_variant['sku'],
                    'title' => $product_variant['title'],
                    'price' => $product_variant['price'],
                    'compare_at_price' => $product_variant['compare_at_price'],
                    'image_ids' => $product_variant['id']
                ];
            }
        } else {
            $product_mode = 'semitest';

            $cart_option_config = $product->getCartFrmOptionConfigSemitest(['atp' => $atp]);
            $list_product_variants = $cart_option_config['product_variants'];
            $selected_product_variant_ukey = array_column($list_product_variants, 'option_value')[array_search($selected_product_variant_id, array_column($list_product_variants, 'product_variant_id'))];

            $result['product']['cart_form_config'] = [
                'semitest_config' => [
                    'product_variant_id' => $selected_product_variant_id,
                    'product_variant_ukey' => $selected_product_variant_ukey,
                    'cart_option_config' => $cart_option_config,
                    'is_disable_preview' => isset($product->data['meta_data']['is_disable_preview']) ? intval($product->data['meta_data']['is_disable_preview']) : 0
                ]
            ];
        }
        $is_active_ab_test = OSC::helper('frontend/frontend')->flagProductActiveAbtestTab($product->getId());

        $result['product']['log_options_flag'] = $is_active_ab_test;
        $result['product']['tab_flag'] = $is_live_preview || ($product->checkHasTabDesign() &&
            $is_active_ab_test &&
            !empty($ab_test_cookie_data['key']) &&
            !empty($ab_test_cookie_data['value']) &&
            $ab_test_cookie_data['key'] === OSC::AB_VER4_TAB_PRODUCT['key'] &&
            $ab_test_cookie_data['value'] === 'has_tab');
        $result['product']['ab_test_key'] = $ab_test_cookie_data['key'] ?? null;
        $result['product']['ab_test_value'] = $ab_test_cookie_data['value'] ?? null;
        $result['product']['cart_form_config']['mode'] = $product_mode;
        $result['product']['images'] = $product->getArrayImage();
        $result['product']['videos'] = $product->getArrayVideos();

        $result['cart_note'] = [
            'Shipping calculated at checkout',
            'Import Duty and GST/VAT applicable in your country not included',
            'Guaranteed safe and secure checkout via:'
        ];

        $variant_selected_or_first = $variant->getId();
        $event_data = [
            'product_id' => $product->getId(),
            'variant_id' => $variant_selected_or_first
        ];

        OSC::helper('report/common')->addRecordEvent('catalog/product_view', $event_data);

        $options = [];

        if ($product->data['meta_tags']['description']) {
            $options['sref_desc'] = 1;
        }

        $this->sendSuccess($result, $options);
    }

    public function actionGetBestSelling() {
        $size = intval($this->_request->get('size', 6));
        $size = $size > 0 && $size <= 20 ? $size : 10;
        $this->apiOutputCaching([
            'size' => $size
        ], 0, ['using_customer_shipping_location']);

        try {
            $product_items = OSC::model('catalog/product')->getCollection()->loadBestSelling($size);

            $result = [
                'products' => OSC::helper('catalog/product')->formatProductApi($product_items),
            ];

            $this->sendSuccess($result);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetRecentViewProduct() {
        $product = OSC::model('catalog/product');

        if (OSC::helper('core/setting')->get('catalog/detail/enable_recently_product')) {
            try {
                $recently_viewed_products = $product->getNullModel()->getCollection()->loadRecentlyViewed(4);

                $result = [
                    'products' => OSC::helper('catalog/product')->formatProductApi($recently_viewed_products)
                ];

                $this->sendSuccess($result);
            } catch (Exception $ex) {
                $this->sendError($ex->getMessage(), $ex->getCode());
            }
        }

        $this->sendSuccess([]);
    }

    public function actionGetAllProduct() {
        $page = intval($this->_request->get('page'));
        $page = max($page, 1);
        $size = intval($this->_request->get('size'));
        $size = $size > 0 && $size <= 20 ? $size : 10;

        $_filters = $this->_request->get('filter_id_options');

        $filters = OSC::helper('filter/common')->validateFilterOptions($_filters);

        $sort = trim($this->_request->get('sort', 'solds'));

        try {
            $collection = OSC::model('catalog/collection');

            $collection->bind(array(
                'title' => 'All products',
            ))->lock();

            $collection->loadProducts([
                'page_size' => $size,
                'page' => $page,
                'filters' => $filters,
                'sort' => $sort,
                'before_load_callback' => function (Model_Catalog_Product_Collection $product_collection) {
                    $product_collection->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                        ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND);
                }
            ]);

            $products = $collection->getProducts();
            $page_index = $products->getCurrentPage();
            $page_size = $products->getPageSize();
            $total_item = $products->collectionLength();

            $this->sendSuccess([
                'collection' => OSC::helper('catalog/product')->formatCollectionApi($collection),
                'products' => OSC::helper('catalog/product')->formatProductApi($products),
                'banner' => $collection->getCollectionBanner(),
                'page' => $page_index,
                'size' => $page_size,
                'total' => intval($total_item),
                'sort' => [
                    'options' => OSC::helper('filter/search')->getSortOptions(),
                    'default' => 'solds'
                ]
            ]);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetEstimatedDeliveryTime() {
        $result = [];

        $country_code = $this->_request->get('country_code');
        $province_code = $this->_request->get('province_code');

        try {
            // handle country , province
            if (empty($country_code) ||
                is_null(OSC::helper('core/country')->getCountryTitle($country_code))
            ) {
                $shipping_location =  OSC::helper('catalog/common')->getCustomerShippingLocation();

                $country_code =  $shipping_location['country_code'];
                if (empty($province_code) ||
                    is_null(OSC::helper('core/country')->getProvinceTitle($country_code, $province_code))
                ) {
                    $province_code = $shipping_location['province_code'];
                }
            }

            $cart = OSC::helper('catalog/common')->getCart(false);

            if ($cart instanceof Model_Catalog_Cart) {
                $cart->setData([
                    'shipping_country' => OSC::helper('core/country')->getCountryTitle($country_code),
                    'shipping_country_code' => $country_code,
                    'shipping_province_code' => $province_code,
                    'shipping_province' => OSC::helper('core/country')->getProvinceTitle($country_code, $province_code),
                ])->save();
            }

            OSC::cookieSetCrossSite('customer_country_code', $country_code);
            OSC::cookieSetCrossSite('customer_province_code', $province_code);

            $product_variant_id = intval($this->_request->get('variant_id'));

            if ($product_variant_id > 0) {
                $variant = OSC::model('catalog/product_variant')->load($product_variant_id);
                $result = OSC::helper('catalog/react_common')->getEstimatedDeliveryTime($variant, $country_code, $province_code, 1, ['product_detail' => 1]);
            }

        } catch (Exception $ex) { }

        $this->sendSuccess($result);
    }

    public function actionGetProductBySeoTags() {
        $tag_alias = $this->_request->get('tag_alias', '');

        try {
            $product_items = OSC::model('catalog/product')->getCollection()->addCondition('seo_tags', '%"collection_slug":"' . $tag_alias . '"%', OSC_Database::OPERATOR_LIKE)->load();

            $result = [
                'title' => OSC::helper('catalog/product')->getTagTitle($product_items->getItem()->data['seo_tags'], $tag_alias),
                'products' => OSC::helper('catalog/product')->formatProductApi($product_items),
            ];

            $this->sendSuccess($result);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetSvgDefault()
    {
        $campaign_id = intval($this->_request->get('campaign_id'));
        $print_template_id = intval($this->_request->get('print_template_id'));

        try {
            $designs = OSC::helper('catalog/product')->getSvgDefault($campaign_id, $print_template_id);
            $this->sendSuccess($designs);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetProductTypeUkeyByGroupName()
    {
        $group_name = trim($this->_request->get('group_name', ''));

        try {
            $group_product_type = OSC::helper('catalog/productType')->groupProductType();
            $result = isset($group_product_type[$group_name]) ? $group_product_type[$group_name] : [];
        } catch (Exception $ex) {
            $result = [];
        }

        $this->sendSuccess($result);
    }

    public function actionGetSizeGuide() {
        $product_type_id = $this->_request->get('product_type_id', 0);

        $this->apiOutputCaching([
            'product_type_id' => $product_type_id,
        ], 0, ['ignore_location']);

        try {
            $product_type = OSC::model('catalog/productType')->load($product_type_id);
            $size_guide = $product_type->data['size_guide_data'];
            if ($size_guide['image']) {
                $size_guide['image'] = $product_type->getSizeGuideImageUrl();
            }
            $this->_ajaxResponse($size_guide);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }
}
