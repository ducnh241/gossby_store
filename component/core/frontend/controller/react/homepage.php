<?php

class Controller_Frontend_React_Homepage extends Abstract_Frontend_ReactApiController
{
    public function actionGetHomeSection()
    {
        $this->apiOutputCaching([
            'function' => __FUNCTION__,
        ], 0, ['using_customer_shipping_location']);

        try {
            $results = $this->_getHomePageV3();
            $this->sendSuccess($results);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }
    }

    protected function _getHomePageV3()
    {
        $results = [];
        $homepage_v3_data = null;

        try {
            $homepage_v3_data = OSC::model('core/setting')->loadByUKey('frontend/homepage_v3');
            $homepage_v3_data = $homepage_v3_data->data['setting_value'];
        } catch (Exception $e) {
        }

        if (!is_array($homepage_v3_data) || count($homepage_v3_data) < 1) {
            throw new Exception('Config invalid');
        }

        foreach ($homepage_v3_data as $section => $data) {
            switch ($section) {
                case 'community':
                case 'popular_collection':
                    $results[$section] = $data;
                    if (isset($data['video'])) {
                        $results[$section]['video'] = array_values(OSC::decode($data['video']))[0];
                    }
                    foreach ($data['items'] as $key => $item) {
                        $results[$section]['items'][$key]['images']['pc'] = (isset($item['images']['pc']) && $item['images']['pc']) ? OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($item['images']['pc'])) : '';
                        $results[$section]['items'][$key]['images']['mobile'] = (isset($item['images']['mobile']) && $item['images']['mobile']) ? OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($item['images']['mobile'])) : '';
                        if (isset($item['video'])) {
                            $results[$section]['items'][$key]['video'] = array_values(OSC::decode($item['video']))[0];
                        }
                    }
                    break;
                case 'popular_categories':
                    $results[$section] = $data;
                    $n = 0;
                    foreach ($data['items'] as $key => $item) {
                        $results[$section]['items'][$n] = $item;
                        $results[$section]['items'][$n]['images']['pc'] = (isset($item['images']['pc']) && $item['images']['pc']) ? OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($item['images']['pc'])) : '';
                        $results[$section]['items'][$n]['images']['mobile'] = (isset($item['images']['mobile']) && $item['images']['mobile']) ? OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($item['images']['mobile'])) : '';
                        unset($results[$section]['items'][$key]);
                        $n++;
                    }

                    break;
                case 'sales_campaign':
                    if (!intval($data['on_off'])) {
                        unset($results[$section]);
                        break;
                    }
                    $results[$section]['banner_url'] = trim($data['banner_url']);
                    $results[$section]['images']['pc'] = (isset($data['images']['pc']) && $data['images']['pc']) ? OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($data['images']['pc'])) : '';
                    $results[$section]['images']['mobile'] = (isset($data['images']['mobile']) && $data['images']['mobile']) ? OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($data['images']['mobile'])) : '';
                    break;
                case 'customer_review':
                    $review_ids = explode('-', $data);
                    $review_ids = array_map(function ($review_id) {
                        return trim($review_id);
                    }, $review_ids);

                    $reviews = OSC::model('catalog/product_review')->getCollection()
                        ->addCondition('record_id', $review_ids, OSC_Database::OPERATOR_IN)
                        ->addCondition('has_photo', 1, OSC_Database::OPERATOR_EQUAL)
                        ->addCondition('vote_value', 5, OSC_Database::OPERATOR_EQUAL)
                        ->addCondition('state', Model_Catalog_Product_Review::STATE_APPROVED, OSC_Database::OPERATOR_EQUAL)
                        ->load();
                    $reviews_formatted = OSC::helper('catalog/product_review')->renderProductReviewApi($reviews);

                    $review_position = array_flip($review_ids);

                    uasort($reviews_formatted, function ($a, $b) use ($review_position) {
                        if (isset($review_position[$a['review_id']]) && isset($review_position[$b['review_id']])) {
                            if ($review_position[$a['review_id']] == $review_position[$b['review_id']]) {
                                return 0;
                            }
                            return $review_position[$a['review_id']] < $review_position[$b['review_id']] ? -1 : 1;
                        }
                        return 0;
                    });

                    $review = OSC::model('catalog/product_review');
                    $aggregate_review = $review->getAggregateReview();

                    $results['reviews']['items'] = array_slice(array_values($reviews_formatted), 6);
                    $results['reviews']['total_review'] = $aggregate_review['total_review'] ?? 0;
                    break;
                case 'collection':
                    $results['collection'] = $data;

                    $collection_id = intval($data['collection_id']);
                    $size = max($data['number_pc'], $data['number_mobile']);

                    $collection = OSC::model('catalog/collection');
                    $collection->load($collection_id);
                    $sort = $collection->data['sort_option'];

                    $collection->loadProducts([
                        'page_size' => $size,
                        'page' => 1,
                        'filters' => [],
                        'sort' => $sort,
                        'before_load_callback' => function (Model_Catalog_Product_Collection $product_collection) {
                            $product_collection->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                                ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND);
                        },
                        'top' => $collection->data['top']
                    ]);

                    $products = $collection->getProducts();
                    $auto_conditions = $collection->data['auto_conditions']['conditions'] ?? [];
                    $options = [];

                    if (is_array($auto_conditions) && (in_array('price', array_column($auto_conditions, 'field'))
                            || in_array('compare_at_price', array_column($auto_conditions, 'field')))) {
                        $options['skip_ab_test_price'] = true;
                    }

                    $results['collection']['url'] = $collection->getDetailUrl();
                    $results['collection']['products'] = OSC::helper('catalog/product')->formatProductApi($products, $options);

                    break;
                case 'banner':
                    foreach ($data as $item) {
                        $item['images']['pc'] = (isset($item['images']['pc']) && $item['images']['pc']) ? OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($item['images']['pc'])) : '';
                        $item['images']['mobile'] = (isset($item['images']['mobile']) && $item['images']['mobile']) ? OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($item['images']['mobile'])) : '';

                        $results[$section][] = $item;
                    }
                    break;
                default:
                    $results[$section] = $data;
                    foreach ($data as $key => $item) {
                        if (preg_match("/\/catalog\/collection\/(\d+)\/([a-zA-Z0-9-_]+)(\/)?$/i", $item['url'], $matches)) {
                            $results[$section][$key]['id'] = intval($matches[1]);
                        }

                        if (isset($item['url']) && $item['url']) {
                            $results[$section][$key]['url'] = $item['url'];
                        }

                        if ((isset($item['images']['pc']) && $item['images']['pc'])) {
                            $results[$section][$key]['images']['pc'] = OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($item['images']['pc']));
                        }

                        if (isset($item['images']['mobile']) && $item['images']['mobile']) {
                            $results[$section][$key]['images']['mobile'] = OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($item['images']['mobile']));
                        }

                    }
                    break;
            }
        }

        return $results;
    }

    public function actionGetCommonLayout()
    {
        $this->sendSuccess(['ping' => 'poong']);
    }

    public function actionGetCommonLayoutV1()
    {
        $this->sendSuccess(OSC::helper('frontend/common')->getCommonLayoutV1());
    }

    public function actionGetSvgDefault()
    {
        $homepage_settings = OSC::helper('core/setting')->get('frontend/homepage_v3', []);
        $data_preview_3d = $homepage_settings['preview_3d'] ?? '';
        $data_preview_3ds = explode("\n", $data_preview_3d);

        $this->apiOutputCaching([
            'function' => __FUNCTION__
        ], 0, ['homepage_v3_preview3d']);

        $data = [];
        foreach ($data_preview_3ds as $key => $item_previews) {
            $item_previews = explode('-', $item_previews);
            if (isset($item_previews[0]) && intval($item_previews[0]) && isset($item_previews[1]) && intval($item_previews[1])) {
                $data[$key]['campaign_id'] = intval($item_previews[0]);
                $data[$key]['print_template_id'] = intval($item_previews[1]);
            }
        }

        $results = [];
        try {
            $location = OSC::helper('catalog/common')->getCustomerShippingLocation();
            $country_code = $location['country_code'];
            $province_code = $location['province_code'];
            foreach ($data as $key_data => $item) {
                $product = OSC::helper('catalog/product')->getProduct(['id' => $item['campaign_id']], true);
                if (!$product->isExists()) {
                    continue;
                }
                $variant = $product->getSelectedOrFirstAvailableVariant(false, $country_code, $province_code);
                $cart_option_config = $product->getCartFrmOptionConfig($country_code, $province_code);
                $list_product_variants = $cart_option_config['product_variants'];
                $selected_product_variant_id = $variant->getId();
                $selected_product_variant_ukey = array_column($list_product_variants, 'ukey')[array_search($selected_product_variant_id, array_column($list_product_variants, 'product_variant_id'))];
                $campaign_config = OSC::helper('catalog/campaign')->prepareCampaignConfig($product);

                $results[$key_data]['cart_form_config'] = [
                    'product_variant_id' => $variant->getId(),
                    'product_variant_ukey' => $selected_product_variant_ukey,
                    'cart_option_config' => $cart_option_config,
                    'campaign_config' => $campaign_config,
                    'product' => [
                        'product_id' => $product->data['product_id'],
                        'title' => $product->data['title'],
                        'virtual_title' => $product->getProductTitle(),
                        'topic' => $product->data['topic'],
                        'identifier' => $product->getProductIdentifier(),
                        'url' => $product->getDetailUrl(null, false)
                    ],
                ];
                $print_template_id = $item['print_template_id'];
                $results[$key_data]['svg_layer'] = OSC::helper('catalog/product')->getSvgDefault($item['campaign_id'], $print_template_id);
                $design_id = 0;
                $print_template_configs = $product->data['meta_data']['campaign_config']['print_template_config'] ?? [];
                foreach ($print_template_configs as $print_template_config) {
                    if ($print_template_config['print_template_id'] == $print_template_id) {
                        $design_id = $print_template_config['segments']['front']['source']['design_id'] ?? 0;
                        break;
                    }
                }
                $design = OSC::model('personalizedDesign/design')->load($design_id);
                $results[$key_data]['svg_file'] = OSC::helper('personalizedDesign/common')->renderSvg($design);
            }
            $this->sendSuccess($results);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }
}