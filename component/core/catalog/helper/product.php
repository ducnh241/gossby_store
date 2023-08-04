<?php

class Helper_Catalog_Product extends OSC_Object
{
    private $__product_identifier_cached_key = 'list_product_identifier';
    private $__product_identifier_cached_ttl = 60 * 60 * 24 * 1;

    public function getAutoAbTestPriceKey()
    {
        return '_atp_' . md5('skip_ab_test_price');
    }

    public function getProductHasTab($id, $ukey)
    {
        $cache = OSC::core('cache');
        $cache_key_prefix = OSC::model('catalog/product')->getHasTabCachePrefix();
        $cache_key = $cache_key_prefix . $id . '_' . $ukey;
        $has_tab = $cache->get($cache_key);

        if (!$has_tab) {
            if ($id > 0) {
                $product = OSC::helper('catalog/product')->getProduct(['id' => $id], true);
            } else {
                if (strlen($ukey) != 15) {
                    throw new Exception('Product not found');
                }
                $product = OSC::helper('catalog/product')->getProduct(['ukey' => $ukey], true);
            }

            $has_tab = $product->setProductHasTabCache();
        }

        return $has_tab;
    }

    public function getTrackingEventData($product_id, $product_ukey, $variant_id)
    {
        $cache = OSC::core('cache');
        $cache_key = 'product_event_' . $product_id . '_' . $product_ukey . '_' . $variant_id;
        $event_data = $cache->get($cache_key);
        $selected_product_variant_id = 0;

        if (!$event_data) {
            if ($product_id > 0) {
                $product = OSC::helper('catalog/product')->getProduct(['id' => $product_id], true);
            } else {
                if (strlen($product_ukey) != 15) {
                    throw new Exception('Product not found');
                }
                $product = OSC::helper('catalog/product')->getProduct(['ukey' => $product_ukey], true);
            }

            if ($variant_id > 0) {
                $selected_product_variant_id = $variant_id;
            } else {
                $location = OSC::helper('catalog/common')->getCustomerShippingLocation();
                $country_code = $location['country_code'] ?? '';
                $province_code = $location['province_code'] ?? '';

                if ($product->isCampaignMode()) {
                    $variant = $product->getSelectedOrFirstAvailableVariant(false, $country_code, $province_code);
                } else {
                    $variant = $product->getSelectedOrFirstAvailableVariant();
                }

                if ($variant && $variant instanceof Model_Catalog_Product_Variant) {
                    $selected_product_variant_id = $variant->getId();
                }
            }

            $event_data = [
                'product_id' => $product->getId(),
                'variant_id' => $selected_product_variant_id
            ];

            $cache->set($cache_key, $event_data, 30 * 24 * 60 * 60); // 30 days
        }

        return $event_data;
    }

    public function resyncFeed()
    {
        OSC::helper('catalog/common')->reloadFileFeedFlag();
    }

    public function getProductDefaultTabs()
    {
        $collection = OSC::model('catalog/productTabs')->getCollection();
        $collection->addCondition('apply_all', 1, '=');
        $collection->load();
        $data = array();
        foreach ($collection as $coll) {
            $data[] = $coll->data;
        }
        return $data;
    }

    public function collectOptionTypes()
    {
        return [
            'default' => 'Default (select)',
            'button' => 'Button',
            'clothing_size' => 'Clothing Size',
            'product_type' => 'Product Type',
            'poster_size' => 'Poster Size',
            'product_color' => 'Product Color'
        ];
    }

    public function delete(int $product_id): Model_Catalog_Product
    {
        if ($product_id < 1) {
            throw new Exception('Product ID is empty');
        }

        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            /* @var $product Model_Catalog_Product */
            $product = OSC::model('catalog/product')->load($product_id);

            $product->delete();

            OSC::core('observer')->dispatchEvent('catalog/productDeleted', $product);

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        OSC_Database_Model::unlockPreLoadedModel($locked_key);

        try {
            OSC::core('aws_s3')->deleteStorageFile('product/' . $product->getId());
        } catch (Exception $ex) {
        }

        return $product;
    }

    public function getSomeWords($str, $count = 10)
    {
        $str = trim(strip_tags($str));
        $words = preg_split('#\s+#is', $str);
        $min = min($count, count($words));

        return implode(' ', array_slice($words, 0, $min)) . ($min == $count ? '...' : '');
    }

    public function formatProductApi(Model_Catalog_Product_Collection $product_collection, $options = [])
    {
        $skip_ab_test_price = isset($options['skip_ab_test_price']) && $options['skip_ab_test_price'];
        $reload = isset($options['flag_reload']) && $options['flag_reload'];
        $result = [];

        if ($product_collection->length() > 0) {
            /**
             * @var $product Model_Catalog_Product
             */

            $location = OSC::helper('catalog/common')->getCustomerShippingLocation();
            $country_code = $location['country_code'] ?? '';
            $province_code = $location['province_code'] ?? '';

            foreach ($product_collection as $product) {
                $price = 0;
                $compare_at_price = 0;
                $variant = $product->getSelectedOrFirstAvailableVariant($reload, $country_code, $province_code);
                if ($variant && $variant->ableToOrder()) {
                    $price_data = $variant->getPriceForCustomer($country_code, false, $skip_ab_test_price);
                    $price = $price_data['price'];
                    $compare_at_price = $price_data['compare_at_price'];
                }

                if ($price === 0) {
                    continue;
                }

                $price_pack_by_variant = $this->__getPackPriceByVariant($variant, ['price' => $price, 'compare_at_price' => $compare_at_price]);

                $price = $price_pack_by_variant['price'];

                $compare_at_price = $price_pack_by_variant['compare_at_price'];

                $featured_image_url = $variant->getImageFeaturedUrl(true);

                if (!isset($featured_image_url) || $featured_image_url == '') {
                    $featured_image_url = $product->getFeaturedImageUrl();
                }

                $product_id = $product->data['product_id'];

                $product_url = $product->getDetailUrl(null, false);

                if ($skip_ab_test_price) {
                    $product_url .= (strpos($product_url, '?') !== false ? '&' : '?') . 'atp=1';
                }

                $first_view_videos = $variant->getVideos();

                $result[] = [
                    'product_id' => $product->data['product_id'],
                    'sku' => $product->data['sku'],
                    'slug' => $product->data['slug'],
                    'title' => $product->data['title'],
                    'virtual_title' => $product->getProductTitle(),
                    'topic' => $product->data['topic'],
                    'identifier' => $product->getProductIdentifier(),
                    'price' => $price,
                    'compare_at_price' => $compare_at_price,
                    'percent' => number_format($price / $compare_at_price * 100, 0),
                    'image' => OSC::wrapCDN(OSC::helper('core/image')->imageOptimize($featured_image_url, 500, 500, false, true)),
                    'url' => $product_url,
                    'has_video' => $first_view_videos->length() ? 1 : 0
                ];
            }
        }

        return $result;
    }

    public function formatCollectionApi(Model_Catalog_Collection $collection)
    {
        $result = [];

        if ($collection) {
            $result = [
                'collection_id' => $collection->data['collection_id'],
                'title' => $collection->data['title'],
                'slug' => $collection->data['slug'],
                'description' => $collection->data['description'],
                'allow_index' => $collection->data['allow_index'],
                'image' => OSC::wrapCDN($collection->getImageUrl()),
                'url' => $collection->getDetailUrl(false)
            ];
        }

        return $result;
    }

    public function getJsonSchema($params = []): array
    {
        $product = $params['product'] ?? null;
        $price = $params['price'] ?? 0;
        $price = OSC::helper('catalog/common')->integerToFloat(intval($price));
        $product_detail_url = $params['product_detail_url'] ?? '';

        if (!$product instanceof Model_Catalog_Product) {
            return [];
        }

        $product_images = [];
        $site_name = OSC::helper('core/setting')->get('theme/site_name');

        foreach ($product->getImages() as $key => $image) {
            $product_images[] = OSC::wrapCDN(OSC::helper('core/image')->imageOptimize($image->getUrl(), 1000, 1000, false));

            if ($key >= 4) {
                break;
            }
        }

        $result['news_article'] = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $product_detail_url
            ],
            'headline' => $product->getProductTitle(),
            'image' => $product_images,
            'datePublished' => date('Y-m-d H:i:s', intval($product->data['added_timestamp'])),
            'dateModified' => date('Y-m-d H:i:s', intval($product->data['modified_timestamp'])),
            'author' => [
                '@type' => 'Person',
                'name' => $site_name
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $site_name,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => OSC::wrapCDN(OSC::helper('frontend/template')->getLogo()->url)
                ]
            ]
        ];

        /* @var $review Model_Catalog_Product_Review */
        $review = OSC::model('catalog/product_review');
        $aggregate_review = $review->getAggregateReview($product->getId());
        $average_review_point = $aggregate_review['avg_vote_value'] ?? 0;
        $total_review = $aggregate_review['total_review'] ?? 0;

        $result['product'] = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->getProductTitle(),
            'image' => $product_images,
            'description' => htmlspecialchars(OSC::core('string')->removeInvalidCharacter(strip_tags($product->data['description']))),
            'sku' => $product->data['sku'],
            'brand' => [
                '@type' => 'Brand',
                'name' => $site_name
            ],
            'review' => [
                '@type' => 'Review',
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => $average_review_point,
                    'bestRating' => '5'
                ],
                'author' => [
                    '@type' => 'Person',
                    'name' => $site_name
                ]
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $average_review_point,
                'reviewCount' => $total_review
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => $product_detail_url,
                'priceCurrency' => 'USD',
                'price' => $price,
                'priceValidUntil' => date('Y-m-d', $product->data['added_timestamp']),
                'itemCondition' => 'https://schema.org/UsedCondition',
                'availability' => 'https://schema.org/InStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $site_name
                ]
            ]
        ];

        return $result;
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getBestSelling(int $limit = 10): array
    {
        $result = [];

        try {
            $best_selling_products = OSC::model('catalog/product')->getCollection()->loadBestSelling($limit);

            foreach ($best_selling_products as $product) {
                $result[] = $this->getCommonDataOfProduct($product);;
            }
        } catch (Exception $ex) {
        }

        return $result;
    }

    public function getFrequentltyBoughtTogetherByOrder($order, int $limit = 10): array
    {
        $result = [];

        try {
            $frequently_bought_together_products = OSC::model('catalog/product')
                ->getCollection()
                ->loadFrequentltyBoughtTogetherByOrder($order, $limit);

            foreach ($frequently_bought_together_products as $product) {
                $result[] = $this->getCommonDataOfProduct($product);
            }
        } catch (Exception $ex) {
        }

        return $result;
    }

    /**
     * @param $product
     * @return array
     */
    public function getCommonDataOfProduct($product): array
    {
        $price_data = $product->getProductPriceData();

        $variant = $product->getSelectedOrFirstAvailableVariant();

        $featured_image_url = $variant->getImageFeaturedUrl(true);

        if (!isset($featured_image_url) || $featured_image_url == '') {
            $featured_image_url = $product->getFeaturedImageUrl();
        }

        $price_pack_by_variant = $this->__getPackPriceByVariant($variant, $price_data);

        return [
            'product_id' => $product->getId(),
            'sku' => $product->data['sku'],
            'slug' => $product->data['slug'],
            'title' => $product->data['title'],
            'virtual_title' => $product->getProductTitle(),
            'topic' => $product->data['topic'],
            'identifier' => $product->getProductIdentifier(),
            'price' => $price_pack_by_variant['price'],
            'compare_at_price' => $price_pack_by_variant['compare_at_price'],
            'image' => OSC::wrapCDN(OSC::helper('core/image')->imageOptimize($featured_image_url, 500, 500, false, true)),
            'url' => $product->getDetailUrl(null, false)
        ];
    }

    public function getSku()
    {
        $sku = uniqid(null, false);
        $type_rand_key = 7;

        if (preg_match('/^[0-9]+$/i', $sku)) {
            $type_rand_key = 4;
        } elseif (preg_match('/^[a-zA-Z]+$/i', $sku)) {
            $type_rand_key = 2;
        }

        $sku .= OSC::randKey(2, $type_rand_key);

        return strtoupper($sku);
    }

    /**
     * @param $product_ids
     * @param false $reload
     * @return array
     * @throws OSC_Exception_Runtime
     */
    public function getPriceDataByProductIds($product_ids, $reload = false)
    {
        $result = [];
        $product_collection = OSC::model('catalog/product')
            ->getCollection()
            ->addField('meta_data')
            ->load($product_ids);

        foreach ($product_collection as $product) {
            $variant = $product->getSelectedOrFirstAvailableVariant($reload);

            if ($variant && $variant->ableToOrder()) {
                $price_data = $variant->getPriceForCustomer();
                $price = $price_data['price'];
                $compare_at_price = $price_data['compare_at_price'];

                if ($price === 0 || $compare_at_price === 0) {
                    continue;
                }

                $result[$product->getId()] = [
                    'price' => $price,
                    'compare_at_price' => $compare_at_price,
                ];
            }
        }

        return $result;
    }

    /**
     * @param $product_id
     * @return array
     * @throws OSC_Exception_Runtime
     */
    public function getVariantPriceDataByProductId($product_id)
    {
        $result = [];
        $product_variant_collection = OSC::model('catalog/product_variant')
            ->getCollection()
            ->addCondition('product_id', $product_id, OSC_Database::OPERATOR_EQUAL)
            ->addField('id', 'product_id', 'product_type_variant_id', 'best_price_data', 'design_id', 'price', 'compare_at_price')
            ->load();

        foreach ($product_variant_collection as $product_variant) {
            $result[$product_variant->getId()] = $product_variant->getPriceForCustomer();
        }

        return $result;
    }

    /**
     * @param array $content
     * @return array
     */
    public function replaceContentApiHomePage(array $content)
    {
        $product_ids = [];
        foreach ($content as $value) {
            if ($value['type'] === 'sections') {
                foreach ($value['data']['products'] as $data) {
                    $product_ids[] = $data['product_id'];
                }
            }
        }

        if (empty($product_ids)) {
            return $content;
        }

        $product_prices = OSC::helper('catalog/product')->getPriceDataByProductIds($product_ids);

        foreach ($content as $content_key => $value) {
            if ($value['type'] === 'sections') {
                foreach ($value['data']['products'] as $product_key => $data) {
                    if (isset($product_prices[$data['product_id']]) && is_array($product_prices[$data['product_id']])) {
                        $content[$content_key]['data']['products'][$product_key]['price'] = $product_prices[$data['product_id']]['price'];
                        $content[$content_key]['data']['products'][$product_key]['compare_at_price'] = $product_prices[$data['product_id']]['compare_at_price'];
                    }
                }
            }
        }

        return $content;
    }

    public function getBaseCostConfig($variant_data = [], $country_codes = []): array
    {
        if (count($variant_data) === 0) {
            return [];
        }

        $list_country_codes = [];
        $product_type_variant_ids = OSC::helper('core/common')->parseProductTypeVariantIds($variant_data);
        $location_variant_collection = OSC::model('catalog/productType_variantLocationPrice')
            ->getCollection()
            ->addField('product_type_variant_id', 'location_data', 'base_cost_configs')
            ->addCondition('product_type_variant_id', $product_type_variant_ids, OSC_Database::OPERATOR_IN)
            ->load();
        $variant_collection = OSC::model('catalog/productType_variant')
            ->getCollection()
            ->addField('title', 'base_cost_configs')
            ->load($product_type_variant_ids);

        $data = [];
        $variants = [];
        foreach ($variant_collection as $variant) {
            $variants[$variant->getId()] = [
                'title' => $variant->data['title']
            ];
        }

        foreach ($variant_collection as $variant) {
            if (is_array($variant->data['base_cost_configs']) && count($variant->data['base_cost_configs']) > 0) {
                foreach ($variant->data['base_cost_configs'] as $value) {
                    $data[$variant->getId() . '_' . $value['quantity']] = [
                        'title' => $variant->data['title'],
                        'country_name' => 'Default',
                        'quantity' => $value['quantity'],
                        'base_cost' => $value['base_cost']
                    ];
                }
            }
        }

        foreach ($location_variant_collection as $location_variant) {
            $location_data = $location_variant->data['location_data'];
            if (!isset($list_country_codes[$location_data])) {
                $list_country_codes[$location_data] = OSC::helper('core/country')->getCountryCodeByLocation([$location_data]);
            }

            foreach ($list_country_codes[$location_data] as $country_code) {
                if ((in_array($country_code, $country_codes)) &&
                    is_array($location_variant->data['base_cost_configs']) &&
                    count($location_variant->data['base_cost_configs']) > 0
                ) {
                    foreach ($location_variant->data['base_cost_configs'] as $value) {
                        $data[$location_variant->data['product_type_variant_id'] . '_' . $value['quantity']] = [
                            'title' => $variants[$location_variant->data['product_type_variant_id']]['title'],
                            'country_name' => OSC::helper('core/country')->getCountryTitle($country_code),
                            'quantity' => $value['quantity'],
                            'base_cost' => $value['base_cost']
                        ];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @return false
     * @throws OSC_Exception_Runtime
     */
    public function checkCountryHasABTestPrice()
    {
        $client_location = OSC::helper('catalog/common')->getCustomerIPLocation();
        $country_code = $client_location['country_code'];

        if (empty($country_code)) {
            return false;
        }

        $cache_key = 'countryABTestPrice';
        $rows = OSC::core('cache')->get($cache_key);

        if ($rows === false) {
            $config_collection = OSC::model('autoAb/productPrice_config')
                ->getCollection()
                ->addCondition('status', Model_AutoAb_ProductPrice_Config::STATUS_ALLOW)
                ->addField('id', 'location_data')
                ->load();
            $config_ids = array_column($config_collection->toArray(), 'id');
            $config_location_datas = array_column($config_collection->toArray(), 'location_data');

            if (empty($config_ids)) {
                OSC::core('cache')->set($cache_key, [], OSC_CACHE_TIME);
                return false;
            }

            $rows = [];
            foreach ($config_location_datas as $config_location_data) {
                if (in_array('*', $config_location_data)) {
                    $rows = array_keys(OSC::helper('core/country')->getCountries());
                    OSC::core('cache')->set($cache_key, $rows, OSC_CACHE_TIME);

                    return in_array($country_code, $rows);
                } else {
                    foreach ($config_location_data as $config_country_code) {
                        $rows[$config_country_code] = $config_country_code;
                    }
                }
            }

            OSC::core('cache')->set($cache_key, array_values($rows), OSC_CACHE_TIME);
        }

        return in_array($country_code, $rows);
    }

    public function getTagTitle($seo_tags, $tag_alias)
    {
        $tag_title = '';
        foreach ($seo_tags as $tag) {
            if ($tag['collection_slug'] == $tag_alias) {
                $tag_title = $tag['collection_title'];
            }
        }

        return $tag_title;
    }

    public function updateSeoTags($collection_id, $old_tag_title, $new_tag_title)
    {
        $DB = OSC::core('database')->getWriteAdapter();

        if ($old_tag_title == $new_tag_title) {
            return;
        }

        $add_condition_old = '"collection_id":"' . $collection_id . '","collection_title":"' . $old_tag_title  . '"';
        $update_seo = $DB->query('UPDATE osc_catalog_product SET seo_tags = REPLACE(seo_tags,\'' . preg_quote($add_condition_old, "'") . '\',\'"collection_id":"' . $collection_id . '","collection_title":"' . preg_quote($new_tag_title, "'") . '"\') WHERE seo_tags REGEXP \'' . preg_quote($add_condition_old, "'") . '\'', null, 'update_seo_tags');

        $DB->free('update_seo_tags');
        return $update_seo;
    }

    public function fetchProductPixelIds($product)
    {
        $skip_facebook_pixel_in_campaign = intval(OSC::helper('core/setting')->get('tracking/facebook_pixel/skip_pixel_in_campaign')) == 1 ? 1 : 0;
        if ($skip_facebook_pixel_in_campaign) {
            return [];
        }

        if (!($product instanceof Model_Catalog_Product)) {
            return [];
        }

        $pixel_ids = [];

        foreach ($product->data['tags'] as $tag) {
            if (strtolower(substr($tag, 0, 14)) == 'meta:fb_pixel:') {
                $pixel_ids[] = substr($tag, 14);
            }
        }

        return $pixel_ids;
    }

    public function getListProductIdentifiers()
    {
        $product_list_identifiers = OSC::core('cache')->get($this->__product_identifier_cached_key);

        try {
            if ($product_list_identifiers === false) {
                $product_types = OSC::model('catalog/productType')->getCollection()->addField('identifier')->load();
                foreach ($product_types as $key => $item) {
                    foreach ($item->data['identifier'] as $key2 => $identifier) {
                        $product_list_identifiers[] = $identifier;
                    }
                }

                OSC::core('cache')->set($this->__product_identifier_cached_key, $product_list_identifiers, $this->__product_identifier_cached_ttl);
            }
        } catch (Exception $ex) {
        }

        return $product_list_identifiers;
    }

    public function getProduct($options = [], $useCache = false)
    {
        $cache_key = __FUNCTION__ . "|helper.catalog.product";
        if (isset($options['id']) && !empty($options['id'])) {
            $cache_key .= "|product_id:,{$options['id']},|";
        }

        if (isset($options['ukey']) && !empty($options['ukey'])) {
            $cache_key .= "|sku:,{$options['ukey']},|";
        }

        $product = OSC::model('catalog/product');

        if ($useCache && ($cache = OSC::core('cache')->get($cache_key)) !== false) {
            $product = OSC::model('catalog/product')->bind($cache);
            return $product;
        }

        if (isset($options['id']) && !empty($options['id'])) {
            try {
                $product->load($options['id']);
            } catch (Exception $exception) {
            }
        } else if (isset($options['ukey']) && !empty($options['ukey'])) {
            try {
                $product->loadByUKey($options['ukey']);
            } catch (Exception $exception) {
            }
        }

        if ($product instanceof Model_Catalog_Product && !empty($product->data)) {
            OSC::core('cache')->set($cache_key, $product->data, OSC_CACHE_TIME);
        }

        return $product;
    }

    public function getListProductInfo($product_ids, $additional_data = []) {
        $err = [];
        $products = [];
        $_product_ids = [];
        foreach ($product_ids as $product_id) {
            if (!is_numeric($product_id)) {
                $err[] = 'Product ID: ' . $product_id . ' is not a number. ' . "\n";
            } else {
                $_product_ids[] = intval($product_id);
            }
        }

        $_product_ids = array_unique($_product_ids);

        try {
            $collections = OSC::model('catalog/product')
                ->getCollection()
                ->addField('product_id', 'sku', 'topic', 'title', 'slug', 'solds')
                ->addCondition('product_id', $_product_ids, OSC_Database::OPERATOR_IN)
                ->load();

            $member = OSC::helper('user/authentication')->getMember();

            foreach ($collections as $product) {
                $product->data['image'] = $product->getFeaturedImageUrl();
                $product->data['product_title'] = $product->getProductTitle(false, false, true);
                $product->data['product_url'] = $product->getDetailUrl();
                $product->data['analytic_url'] = OSC::getUrl('srefReport/backend/productDetail', ['id' => $product->getId(), 'product_page' => 1]);
                if (isset($additional_data[$product->getId()])) {
                    $product->data['added_by'] = $additional_data[$product->getId()]['added_by'];
                    $product->data['added_date'] = $additional_data[$product->getId()]['added_date'];
                    $product->data['added_date_format'] = date('d/m/Y H:i:s', $additional_data[$product->getId()]['added_date']);
                } else {
                    $product->data['added_by'] = $member->data['username'];
                    $product->data['added_date'] = time();
                    $product->data['added_date_format'] = date('d/m/Y H:i:s', $product->data['added_date']);
                }

                $products[] = $product->toArray();
            }

            if (count($products) < count($_product_ids)) {
                $err_id = implode(', ', array_values(
                    array_diff(
                        $_product_ids,
                        array_column($products, 'product_id')
                    )
                ));
                $err[] = 'Product ID: ' . $err_id . ' is incorrect.' . "\n";
            }
        } catch (Exception $ex) {
            $err[] = $ex->getMessage();
        }

        return [
            'products' => $products,
            'err' => $err
        ];
    }

    public function clearPrintTemplateDeactiveByDuplicate($print_template_configs)
    {
        try {
            $print_template_config_deactive = [
                'print_template_deactive_ids' => [],
                'print_template_configs' => $print_template_configs
            ];

            $print_template_config_deactive_collection = OSC::model('catalog/printTemplate')->getCollection()->addCondition('status', 0)->addField('title')->load();

            foreach ($print_template_config_deactive_collection as $print_template_config) {
                $print_template_config_deactive['print_template_deactive_ids'][] = $print_template_config->data['id'];
            }

            foreach ($print_template_config_deactive['print_template_configs'] as $key => $print_template) {
                if (in_array($print_template['print_template_id'], $print_template_config_deactive['print_template_deactive_ids'])) {
                    unset($print_template_config_deactive['print_template_configs'][$key]);
                }
            }

            return $print_template_config_deactive;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getSvgDefault($campaign_id, $print_template_id)
    {
        try {
            if ($campaign_id < 1) {
                throw new Exception('Campaign id incorrect', 405);
            }

            if ($print_template_id < 1) {
                throw new Exception('print template  id incorrect', 405);
            }

            $product = OSC::model('catalog/product')->load($campaign_id);

            OSC::model('catalog/printTemplate')->load($print_template_id);

            $print_template_config = null;

            foreach ($product->data['meta_data']['campaign_config']['print_template_config'] as $config) {
                if ($config['print_template_id'] == $print_template_id) {
                    $print_template_config = $config;
                    break;
                }
            }

            if (!$print_template_config) {
                throw new Exception('Missing print template config #' . $print_template_id . ' in product #' . $product->getId());
            }

            $options = ['skip_validate_config', 'layer_data'];

            $image_ids = [];

            $personalized_design_ids = [];

            foreach ($print_template_config['segments'] as $segment) {
                if ($segment['source']['type'] == 'personalizedDesign') {
                    $personalized_design_ids[] = $segment['source']['design_id'];
                } else if ($segment['source']['type'] == 'image') {
                    $image_ids[] = $segment['source']['image_id'];
                }
            }

            $collection_personalized_design = OSC::model('personalizedDesign/design')->getCollection();

            $collection_image = OSC::model('catalog/campaign_imageLib_item')->getCollection();

            if (count($personalized_design_ids) > 0) {
                $collection_personalized_design = OSC::helper('personalizedDesign/common')->getPersonalizedDesign($personalized_design_ids);
            }

            if (count($image_ids) > 0) {
                $collection_image = $collection_image->load($image_ids);
            }

            $designs = [];

            foreach ($print_template_config['segments'] as $segment_key => $segment) {
                if ($segment['source']['type'] == 'personalizedDesign') {
                    $personalized_design = $collection_personalized_design->getItemByKey($segment['source']['design_id']);

                    if (!($personalized_design instanceof Model_PersonalizedDesign_Design) || intval($personalized_design->getId()) < 1) {
                        try {
                            $personalized_design = OSC::model('personalizedDesign/design')->load($segment['source']['design_id']);
                        } catch (Exception $ex) {
                            throw new Exception('Personalized design #' . $segment['source']['design_id'] . ' ' . $ex->getMessage());
                        }

                        $collection_personalized_design->addItem($personalized_design);
                    }

                    $personalized_options = [];

                    if (isset($segment['source']['option_default_values']['options'])) {
                        $personalized_options = is_array($segment['source']['option_default_values']['options']) ? $segment['source']['option_default_values']['options'] : OSC::decode($segment['source']['option_default_values']['options']);
                    }

                    $designs['_' . $personalized_design->getId()] = [
                        'svg' => OSC::helper('personalizedDesign/common')->renderSvg($personalized_design, $personalized_options, $options),
                        'document' => $personalized_design->data['design_data']['document']
                    ];
                } else if ($segment['source']['type'] == 'image') {
                    /* @var $image Model_Catalog_Campaign_ImageLib_Item */
                    $image = $collection_image->getItemByKey($segment['source']['image_id']);

                    if (!($image instanceof Model_Catalog_Campaign_ImageLib_Item) || intval($image->getId()) < 1) {
                        try {
                            $image = OSC::model('catalog/campaign_imageLib_item')->load($segment['source']['image_id']);
                        } catch (Exception $ex) {
                            throw new Exception('Image #' . $segment['source']['image_id'] . ' ' . $ex->getMessage());
                        }

                        $collection_image->addItem($image);
                    }

                    $segment['source']['file_name'] = $image->data['filename'];

                    $designs['_' . $image->getId()] = [
                        'svg' => $image->data['filename'],
                        'document' => ''
                    ];
                }
            }

            return $designs;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getProductIdsByRange($date_start, $date_end): array
    {
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getAdapter('db_master');
        $query = "SELECT COUNT( product_id ) AS sold, product_id FROM osc_catalog_order_item WHERE added_timestamp >= {$date_start} AND added_timestamp <= {$date_end} AND product_id > 0 GROUP BY product_id ORDER BY sold DESC";

        $cache_product_ids = [];
        try {
            $DB->query($query, null, 'fetch_bestselling');
            while ($row = $DB->fetchArray('fetch_bestselling')) {
                $cache_product_ids[$row['product_id']] = $row['sold'];
            }
            $DB->free('fetch_bestselling');
        } catch (Exception $ex) {
        }
        return $cache_product_ids;
    }

    public function setCacheProductByCatalogCollection($cache_key, $range_start, $range_end)
    {
        $cache_product_ids = OSC::helper('catalog/product')->getProductIdsByRange($range_start, $range_end);
        OSC::core('cache')->set($cache_key, $cache_product_ids, Model_Catalog_Collection::CACHE_TIME_PRODUCT);
        return $cache_product_ids;
    }

    /**
     * @param $product_collection
     * @return array
     * @throws OSC_Exception_Runtime
     */
    public function getProductsFixedPriceStatus($product_collection) {
        $result = [];

        foreach ($product_collection as $product) {
            $result[$product->getId()] = false;
        }

        $variant_collection = OSC::model('catalog/product_variant')->getCollection()
            ->addField('id', 'product_id', 'best_price_data')
            ->addCondition('product_id', array_keys($result), OSC_Database::OPERATOR_IN)
            ->load();

        foreach ($variant_collection as $variant) {
            if ($variant->hasFixedPriceData()) {
                $result[$variant->data['product_id']] = true;
            }
        }

        return $result;
    }

    private function __getPackPriceByVariant(Model_Catalog_Product_Variant $variant, array $price_data = []): array {
        if (empty($price_data)) {
            throw new Exception('Price data is empty');
        }

        $product_type_variant = $variant->getProductTypeVariant();

        $product_type = $product_type_variant->getProductType();

        $product_packs = $product_type->getProductPacks();

        $status_pack_auto_off = Model_Catalog_Product_Pack::STATE_PACK_AUTO['OFF'];
        $is_pack_auto = Model_Catalog_Product_Pack::STATE_PACK_AUTO['ON'];;

        foreach ($product_packs as $pack_model) {
            if ($pack_model->data['is_pack_auto'] == $status_pack_auto_off) {
                $is_pack_auto = $status_pack_auto_off;
                break;
            }
        }

        $pack_prices = [
            'price' => [],
            'compare_at_price' => []
        ];

        if ($is_pack_auto == $status_pack_auto_off) {
            foreach ($product_packs as $pack) {
                $product_pack_price = OSC::helper('catalog/product')->getProductPriceByPack($pack->data, ['price' => $price_data['price'], 'compare_at_price' => $price_data['compare_at_price']]);

                $pack_prices['price'][] = $product_pack_price['price'];
                $pack_prices['compare_at_price'][] = $product_pack_price['compare_at_price'];
            }
        }

        $price = empty($pack_prices['price']) ? $price_data['price'] : min($pack_prices['price']);
        $compare_at_price = empty($pack_prices['compare_at_price']) ? $price_data['compare_at_price'] : min($pack_prices['compare_at_price']);

        return [
            'price' => $price,
            'compare_at_price' => $compare_at_price
        ];
    }

    public function getProductPriceByPack(array $pack_data, array $price_data) {
        if (empty($pack_data) || empty($price_data)) {
            throw new Exception('Pack Data Incorrect');
        }

        $data = [];

        $quantity = $pack_data['quantity'];

        $discount_type = $pack_data['discount_type'];

        $discount_price = $discount_type === Model_Catalog_Product_Pack::PERCENTAGE ?
            round($price_data['price'] * $quantity * $pack_data['discount_value'] / 100) :
            OSC::helper('catalog/common')->floatToInteger($pack_data['discount_value']);

        $data['price'] = $price_data['price'] * $quantity - intval($discount_price);
        $data['compare_at_price'] = $price_data['compare_at_price'] * $quantity;

        return $data;
    }

    public function bulkUploadProductBeta($product_beta_data, $variants, $images_data, $member_id) {
        /* @var $model Model_Catalog_Product */
        $model = OSC::model('catalog/product');
        $model->setData($product_beta_data);

        OSC::core('observer')->dispatchEvent('catalog/product/postFrmSaveData', ['model' => $model]);

        $model->save();

        OSC::helper('filter/tagProductRel')->saveTagProductRel([], $model, $member_id);

        OSC::core('cron')->addQueue('catalog/product_updateBetaOrder', ['product_id' => $model->getId()], ['ukey' => 'catalog/product_updateBetaOrder', 'requeue_limit' => -1, 'estimate_time' => 60]);

        $default_variant = $model->getVariants(true)->getItem();

        if ($default_variant && ($default_variant->data['price'] != $model->data['price'] || $default_variant->data['compare_at_price'] != $model->data['compare_at_price'])) {
            try {
                $model->setData([
                    'price' => $default_variant->data['price'],
                    'compare_at_price' => $default_variant->data['compare_at_price']
                ])->save();
            } catch (Exception $ex) {

            }
        }

        $model->reload();

        $this->processPostVariants($model, $variants, []);

        $image_collection = $model->getImages();

        $variant_image = [];

        foreach ($images_data as $key => $images) {
            $images = explode(',', $images);

            foreach ($images as $_key => $image) {
                $image = trim($image);

                $filename_from_url = parse_url($image);

                $ext = pathinfo($filename_from_url['path'], PATHINFO_EXTENSION);

                $tmp_file_name = 'bulk_upload_product/mockup/' . md5($image) . '.' . $ext;

                try {
                    OSC_Storage::tmpSaveFile($image, $tmp_file_name);
                } catch (Exception $ex) {
                    continue;
                }

                try {
                    OSC::imageIsNotCorrupt(OSC_Storage::tmpGetFilePath($tmp_file_name));
                } catch (Exception $ex) {
                    @unlink(OSC_Storage::tmpGetFilePath($tmp_file_name));
                    continue;
                }

                $tmp_file_path_s3 = OSC::core('aws_s3')->getTmpFilePath($tmp_file_name);

                $options = [
                    'overwrite' => true,
                    'permission_access_file' => 'public-read'
                ];

                OSC::core('aws_s3')->upload(OSC_Storage::tmpGetFilePath($tmp_file_name), $tmp_file_path_s3, $options);


                $filename = 'product/' . $model->getId() . '/' . OSC::makeUniqid() . '.' . $ext;
                $filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);

                try {
                    OSC::core('aws_s3')->copy($tmp_file_path_s3, $filename_s3);
                } catch (Exception $ex) {
                    continue;
                }

                $image_model = $image_collection->getNullModel();

                try {
                    $image_model->setData([
                        'product_id' => $model->getId(),
                        'position' => $_key + 1,
                        'filename' => $filename
                    ])->save();

                    $image_collection->addItem($image_model);

                    $variant_image[$key][] = $image_model->getId();
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }
            }
        }

        $model->reload();

        foreach ($model->getVariants() as $key => $variant) {
            if (count($variant_image) == 1) {
                $variant->setData(['image_id' => $variant_image[0]])->save();
            } else if (count($variant_image[$key]) > 1) {
                if (isset($variant_image[$key])) {
                    $variant->setData(['image_id' => $variant_image[$key]])->save();
                }
            }
        }

    }

    protected function _verifyVariantOption($variant, $product) {
        if ($variant instanceof Model_Catalog_Product_Variant) {
            $variant_option = [
                'option1' => $variant->data['options']['option1'],
                'option2' => $variant->data['options']['option2'],
                'option3' => $variant->data['options']['option3']
            ];
        } else {
            $variant_option = [
                'option1' => $variant[0],
                'option2' => $variant[1],
                'option3' => $variant[2]
            ];
        }

        foreach (['option1', 'option2', 'option3'] as $key) {
            if ($product->data['options'][$key] === false) {
                if ($variant_option[$key] !== '') {
                    return false;
                }
            } else {
                if (!in_array($variant_option[$key], $product->data['options'][$key]['values'], true)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     *
     * @param Model_Catalog_Product $product
     * @param array $variants
     * @param array $image_map
     */
    public function processPostVariants($product, $variants, $image_map) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        try {
            $variant_collection = $product->getVariants();

            $variant_counter = $variant_collection->length();

            if (!$variant_counter) {
                $variant_counter = 0;
            }

            if ($variant_counter > 0 && (!$variant_collection->getItem()->isDefaultVariant() || count($variants) > 0)) {
                foreach ($variant_collection as $variant_model) {
                    $variant_id = $variant_model->getId();

                    if (!isset($variants[$variant_id]) || $variant_model->isDefaultVariant() || !$this->_verifyVariantOption($variant_model, $product)) {
                        try {
                            $variant_collection->removeItemByKey($variant_id);
                            $variant_model->delete();

                            $variant_counter--;
                        } catch (Exception $ex) {
                            throw new Exception($ex->getMessage());
                        }

                        continue;
                    }

                    $this->_processPostVariant($product, $variant_model, $variants[$variant_id], $image_map);

                    unset($variants[$variant_id]);
                }
            }

            foreach ($variants as $variant_input) {
                if ($this->_processPostVariant($product, $variant_collection->getNullModel(), $variant_input, $image_map) === true) {
                    $variant_counter++;
                }
            }

            if ($variant_counter < 1 || ($variant_collection->getItem() && $variant_collection->getItem()->isDefaultVariant())) {
                $this->_processPostVariant($product, $variant_counter > 0 ? $variant_collection->getItem() : $variant_collection->getNullModel(), null, $image_map, true);
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

    }

    protected function _processPostVariant($product, $variant_model, $variant_input, $image_map, $is_default_variant = false) {
        try {
            $variant_data_key_map = [
                'option1' => 'option1',
                'option2' => 'option2',
                'option3' => 'option3',
                'image_id' => 'image_id',
                'video_id' => 'video_id',
                'price' => 'price',
                'compare_at_price' => 'compare_at_price',
                'design_id' => 'design_id',
                'overselling' => 'overselling',
                'position' => 'position'
            ];

            if (!is_array($variant_input)) {
                $variant_input = [];
            }

            $variant_data = [];

            foreach ($variant_data_key_map as $k1 => $k2) {
                if (!isset($variant_input[$k1])) {
                    continue;
                }

                if ($k1 == 'position' && $variant_input[$k1] == 0) {
                    throw new Exception('Product Variant Position Incorrect');
                }

                if ($k1 == 'image_id') {
                    $buff = [];

                    foreach (explode(',', $variant_input[$k1]) as $image_id) {
                        if (isset($image_map[(string) $image_id])) {
                            $buff[] = $image_map[(string) $image_id];
                        }
                    }

                    $variant_input[$k1] = $buff;
                }

                $variant_data[$k2] = $variant_input[$k1];
            }

            if ($variant_data['video_id'] && is_string($variant_data['video_id'])) {
                $variant_data['video_id'] = explode(',', $variant_data['video_id']);
            }

            $variant_data['meta_data']['video_config']['position'] = $variant_input['video_position'];

            $variant_meta_data_key_map = [
                'shipping_price' => 'shipping_price',
                'shipping_plus_price' => 'shipping_plus_price',
            ];

            foreach ($variant_meta_data_key_map as $k1 => $k2) {
                if (!isset($variant_input[$k1])) {
                    continue;
                }

                $variant_data['meta_data']['semitest_config'][$k2] = OSC::helper('catalog/common')->floatToInteger(floatval($variant_input[$k1]));
            }

            $variant_configs = OSC::decode($variant_input['meta_data'])['variant_config'];

            $this->__verifyVariantConfigVariant($variant_configs);

            if (count($variant_configs) > 0) {
                $isset_default_variant_config = false;

                foreach ($variant_configs as $key => $config) {
                    if ($config['is_default'] == true) {
                        $isset_default_variant_config = true;
                    }

                    foreach ($config['print_template_config'] as $k2 => $print_config) {
                        foreach ($print_config['segments']['source'] as $source_key => $source) {
                            unset($variant_configs[$key]['print_template_config'][$k2]['segments']['source'][$source_key]['svg_content']);
                        }

                        $variant_configs[$key]['print_template_config'][$k2]['print_template_beta_id'] = $print_config['print_template_beta']['id'];
                        unset($variant_configs[$key]['print_template_config'][$k2]['print_template_beta']);
                    }
                }

                if (!$isset_default_variant_config) $variant_configs[0]['is_default'] = true;
            }

            $variant_data['meta_data']['variant_config'] = $variant_configs;

            if (count($variant_data) < 1 && $variant_model->getId() > 0) {
                return false;
            }

            if (!$is_default_variant) {
                if (!$this->_verifyVariantOption([$variant_data['option1'], $variant_data['option2'], $variant_data['option3']], $product)) {
                    return false;
                }
            } else {
                foreach (['option1', 'option2', 'option3'] as $key) {
                    $variant_data['options'][$key] = '';
                }
            }

            $variant_data['product_id'] = $product->getId();
            $variant_data['overselling'] = 1;

            if (!isset($variant_data['sku']) || !$variant_data['sku']) {
                $variant_data['sku'] = $product->getUkey() . '-' . strtoupper(uniqid(null, false) . OSC::randKey(2, 7));
            }

            try {
                foreach (['option1', 'option2', 'option3'] as $key) {
                    $variant_data['options'][$key] = $variant_data[$key];
                    unset($variant_data[$key]);
                }

                $variant_model->setData($variant_data)->save();
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            return true;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    private function __verifyVariantConfigVariant($variant_configs) {
        if (count($variant_configs) < 1) {
            return;
        }

        foreach ($variant_configs as $key => $config) {
            if (!isset($config['title']) || empty($config['title'])) {
                throw new Exception('Print template config title is not found');
            }

            if (!isset($config['supplier']) || count($config['supplier']) < 1) {
                throw new Exception('Supplier is empty');
            }


            foreach ($config['print_template_config'] as $k2 => $print_config) {
                if (count($print_config['segments']['source']) < 1) {
                    throw new Exception('Variant config[' . $config['title'] . '] have config is empty');
                }

                foreach ($print_config['segments']['source'] as $source_key => $source) {
                    $this->__verifyVariantConfigSource($source);
                }
            }

            if (!isset($config['is_default'])) {
                throw new Exception('is default not found');
            }

            if (isset($config['custom_shape']['is_enable'])) {
                if (!in_array($config['custom_shape']['is_enable'], array_values(Model_Catalog_Product_Variant::STATE_CUSTOM_SHAPE))) {
                    throw new Exception('Custom shape is enable incorrect');
                }

                if (!in_array($config['custom_shape']['black_line']['type'], Model_Catalog_Product_Variant::CUSTOM_SHAPE_BACK_LINE_TYPE)) {
                    throw new Exception('Custom shape black line type incorrect');
                }

                if (!in_array($config['custom_shape']['red_line']['type'], Model_Catalog_Product_Variant::CUSTOM_SHAPE_RED_LINE_TYPE)) {
                    throw new Exception('Custom shape red line line type incorrect');
                }

            }
        }
    }

    private function __verifyVariantConfigSource($source) {
        if (intval($source['design_id']) < 1) {
            throw new Exception('Design id  not found in print template config beta ');
        }

        $field_verify = [
            'position' => ['x', 'y'],
            'dimension' => ['width', 'height'],
            'orig_size' => ['width', 'height']
        ];

        foreach ($field_verify as $field => $child) {
            if (count($source[$field]) < 0) {
                throw new Exception($field . ' not found in print template config beta');
            }

            foreach ($child as $field_child) {
                $message = 'Invalid ' . $field . ' in print template config beta';

                $is_condition = false;

                if ($field == 'position') {
                    $is_condition = !is_numeric($source[$field][$field_child]);
                }

                if (in_array($field, ['dimension', 'orig_size'])) {
                    $is_condition = floatval($source[$field][$field_child]) <= 0;
                }

                if ($source[$field][$field_child] === null || $is_condition) {
                    throw new Exception($message);
                }
            }
        }
    }

    /**
     * @param array $custom_data
     * @param Model_Catalog_Product_Variant $variant
     * @return array
     * @throws Exception
     */
    public function getFormEditDesignSemitest( array $custom_data, Model_Catalog_Product_Variant $variant) : array {
        if (count($custom_data) < 1) {
            throw new Exception('Custom data is empty');
        }

        try {
            $design_ids = $variant->data['design_id'];

            if (count($design_ids) < 1) {
                throw new Exception('Design id not found');
            }

            try {
                $design_collection = OSC::model('personalizedDesign/design')->getCollection()->addField('design_id', 'design_data')->load($design_ids);
            } catch (Exception $ex) {
                throw new Exception('Cannot load personalized design');
            }

            $form_edit_design_data = [];

            $design_data = [];

            foreach ($design_ids as $index => $design_id) {
                $design = $design_collection->getItemByPK($design_id);

                $custom_data_source = $custom_data['data'][$design_id];

                $config = isset($custom_data_source['config']) ? $custom_data_source['config'] : [];

                $config_preview = isset($custom_data_source['config_preview']) ? $custom_data_source['config_preview'] : [];

                $form_data = $design->extractPersonalizedFormData();

                $form_edit_design_data[$design_id] = [
                    'key' => 'design_' . ($index + 1),
                    'title' => 'design ' . ($index + 1),
                    'personalized_design_id' => $design_id,
                    'config' => $config,
                    'config_preview' => $config_preview,
                    'width' => $design->data['design_data']['document']['width'],
                    'height' => $design->data['design_data']['document']['height'],
                    'design_last_update' => time(),
                    'components' => $form_data["components"],
                    'image_data' => $form_data["image_data"],
                    'document_type' => $design->data['design_data']['document']
                ];

                $design_data[$design->getId()] = $design;
            }

            if (count($design_data) < 1) {
                throw new Exception('Can not get design data');
            }

            foreach ($design_ids as $design_id) {
                $form_edit_design_data[$design_id]['is_show_title'] = count($form_edit_design_data) > 1 ? 1 : 0;
            }

            return ['designs' => array_values($form_edit_design_data), 'design_data' => $design_data];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected $_data_shipping_semitest = [];

    public function getPriceShippingSemitest() {
        if (empty($this->_data_shipping_semitest)) {
            $result = [
                'shipping_price' => 7.99,
                'shipping_plus_price' => 4,
            ];

            $location = OSC::helper('catalog/common')->getCustomerShippingLocation();

            if (strtoupper($location['country_code']) == 'US') {
                $result['shipping_price'] = 6.99;
            }

            $this->_data_shipping_semitest = $result;
        }

        return $this->_data_shipping_semitest;
    }
}
