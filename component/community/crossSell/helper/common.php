<?php

class Helper_CrossSell_Common extends OSC_Object {

    const PUSH_MOCKUP_URL = 'api/v1/mockup/receiveMockups';
    const GET_DATA_RECOMMEND_CART = 'api/v1/design/getCartPage';
    const GET_MOCKUP_LAYER_CONFIG = 'api/v1/design/getMockupLayerConfig';
    const GET_MOCKUP_LAYER_CONFIGS = 'api/v1/design/getMockupLayerConfigs';
    const GET_DESIGN_LINK = 'api/v1/design/getDesignLink';
    const GET_PREVIEW_CONFIGS = 'api/v1/design/getPreviewConfigs';

    public function getProductTypes() {
        $cache_key = '_getListProductTypesCrossSell';

        $cache = OSC::core('cache')->get($cache_key);

        $collection = OSC::model('catalog/productType')->getCollection();

        if ($cache) {
            foreach ($cache as $product_type) {
                $collection->addItem(OSC::model('catalog/productType')->bind($product_type));
            }
        } else {
            $collection->addCondition('is_cross_sell', 1)
                ->addCondition('status', 1)
                ->load();

            $productTypes = [];
            foreach ($collection as $productType) {
                $productTypes[] = $productType->data;
            }

            OSC::core('cache')->set($cache_key, $productTypes, OSC_CACHE_TIME);
        }

        return $collection;
    }

    public function getProductTypeIds() {
        $productTypes = $this->getProductTypes();
        $productTypeIds = [];
        foreach ($productTypes as $productType) {
            $productTypeIds[] = $productType->getId();
        }
        return $productTypeIds;
    }

    public function renderCampaignMockup($design_id, $side, $tmp_aws, $design_url, $file_time) {
        $version = time();

        $data_queue = [];

        $config_segments_collection = OSC::model('crossSell/segmentsConfig')->getCollection()
            ->addCondition('segments_type', $side, OSC_Database::OPERATOR_EQUAL)->load();

        $print_template_maps_collection = OSC::model('crossSell/printTemplateMaps')->getCollection()->load();

        $config_segments = [];

        foreach ($config_segments_collection as $segments) {
            $config_segments[$segments->data['product_type_id']] = $segments->data['segments'];
        }

        $group_variants = [];

        $print_template_ids = [];

        $product_type_variant_ids = [];


        foreach ($print_template_maps_collection as $print_template_map) {
            $print_template_ids[] = $print_template_map->data['print_template_id'];
            $group_variants[$print_template_map->data['group_product_variant']][] = $print_template_map->data['product_type_variant_id'];
            $product_type_variant_ids[] = $print_template_map->data['product_type_variant_id'];
        }


        if (count($print_template_ids) < 1) {
            throw new Exception('template render mockup cross sell not found');
        }

        if (count($product_type_variant_ids) < 1) {
            throw new Exception('product_type_variant_ids render mockup cross sell not found');
        }

        $print_template_collection = OSC::model('catalog/printTemplate')->getCollection()->load(array_unique($print_template_ids));

        $product_type_variant_collection = OSC::model('catalog/productType_variant')->getCollection()->load(array_unique($product_type_variant_ids));

        $list_mockup_model = [];

        $list_mockup_rel_collection = [];

        $segments_render_mockup = [];

        foreach (array_unique($print_template_ids) as $print_template_id) {
            switch ($side) {
                case Model_CrossSell_SegmentsConfig::SEGMENTS_TYPE_FRONT:
                    $segments_render_mockup[$print_template_id]['front'] = 'system';
                    break;
                case Model_CrossSell_SegmentsConfig::SEGMENTS_TYPE_BACK:
                    $segments_render_mockup[$print_template_id]['back'] = 'system';
                    break;
                default:
                    $segments_render_mockup[$print_template_id]['front'] = 'system';
                    $segments_render_mockup[$print_template_id]['back'] = 'system';
                    break;
            }
        }

        foreach ($group_variants as $group_key => $data) {
            $print_template_id = explode('_', $group_key)[0];

            if (isset($list_mockup_rel_collection[$print_template_id])) {
                $mockup_rel_collection = $list_mockup_rel_collection[$print_template_id];
            } else {
                $mockup_rel_collection = OSC::model('catalog/printTemplate_mockupRel')->getCollection()->getByTemplateId($print_template_id);
                $list_mockup_rel_collection[$print_template_id] = $mockup_rel_collection;
            }

            $print_template = $print_template_collection->getItemByPK($print_template_id);

            if (!($print_template instanceof Model_Catalog_PrintTemplate)) {
                throw new Exception("prints template model error id " . $print_template_id);
            }

            $product_type_variant_id = $data[0];

            foreach ($mockup_rel_collection as $_model_mockup_rel) {
                $option = [];

                $variant_product_type_ids = $_model_mockup_rel->data['variant_product_type_ids'];

                //check render mockup dung voi product_type_variant duoc quy dinh san
                if (isset($variant_product_type_ids) && is_array($variant_product_type_ids) && count($variant_product_type_ids) > 0 && !in_array($product_type_variant_id, $variant_product_type_ids)) {
                    continue;
                }

                if (isset($list_mockup_model[$_model_mockup_rel->data['mockup_id']])) {
                    $mockup = $list_mockup_model[$_model_mockup_rel->data['mockup_id']];
                } else {
                    $mockup = OSC::model('catalog/mockup')->load($_model_mockup_rel->data['mockup_id']);
                    $list_mockup_model[$_model_mockup_rel->data['mockup_id']] = $mockup;
                }

                if ($_model_mockup_rel->data['is_static_mockup'] == 1) {
                    $option['default'] = 1;
                }

                $print_template_id_default = $_model_mockup_rel->data['print_template_id_default'];

                if (isset($print_template_id_default) && intval($print_template_id_default) > 0){
                    $option['template_default'] = intval($print_template_id_default);
                }

                $additional_data = $_model_mockup_rel->data['additional_data'];

                $mockup_map = $additional_data['map_mockups'][$print_template->getId()];

                if (is_array($mockup_map)) {
                    $mockup_map_key = array_key_first($mockup_map);

                    $flag_continue = false;

                    $flag_continue = (isset($segments_render_mockup[$print_template->getId()][$mockup_map_key]) && $mockup_map[$mockup_map_key] == Model_Catalog_PrintTemplate_MockupRel::MOCKUP_DEFAULT)
                        || (!isset($segments_render_mockup[$print_template->getId()][$mockup_map_key]) && $mockup_map[$mockup_map_key] == Model_Catalog_PrintTemplate_MockupRel::MOCKUP_RENDER_BY_SYSTEM);

                    if ($flag_continue) continue;
                }

                $product_type_variant = $product_type_variant_collection->getItemByPK($product_type_variant_id);

                $segments = $config_segments[$product_type_variant->data['product_type_id']];

                $segments = OSC::helper('catalog/campaign_design')->getSegmentRenderData($print_template, OSC::helper('catalog/campaign_design')->getSegmentSourcesCrossSell($segments['segments'], $design_url));

                $_data =  [
                    'segments' => $segments,
                    'commands' => OSC::helper('catalog/campaign_mockup_command')->parse($mockup->data['config'], OSC::helper('catalog/campaign_mockup')->preCommandParams($segments, $product_type_variant->getOptionValues()))
                ];

                if (isset($option['default']) && $option['default'] == 1) {
                    $mockup_url = $_data['commands'][0]['source'];

                    $mockup_url_name = $mockup_url.'_'.filemtime(OSC_ROOT_PATH . '/' .str_replace(OSC::$base_url.'/','', $mockup_url));

                    $mockup_file_name = $tmp_aws . md5($mockup_url_name) . '.png';


                    if (!OSC_Storage::isExists($mockup_file_name)){
                        $tmp_file_name = 'mockup_default/' . md5($mockup_url_name) . '.png';

                        if (!OSC_Storage::tmpFileExists($tmp_file_name)) {
                            OSC_Storage::tmpSaveFile($mockup_url, $tmp_file_name);
                        }

                        try {
                            OSC::imageIsNotCorrupt(OSC_Storage::tmpGetFilePath($tmp_file_name));
                        } catch (Exception $ex) {
                            @unlink(OSC_Storage::tmpGetFilePath($tmp_file_name));
                            throw new Exception($ex->getMessage());
                        }

                        OSC_Storage::tmpMoveFile($tmp_file_name, $mockup_file_name);
                    }

                    $image_ukey = $design_id . '_' . md5($mockup_url_name);

                    try {
                        $image = OSC::model('crossSell/image')->loadByUKey($image_ukey);
                    }catch (Exception $ex) {
                        if ($ex->getCode() != 404) {
                            throw new Exception($ex->getMessage());
                        }

                        $image = OSC::model('crossSell/image')->setData([
                            'design_id' => $design_id,
                            'ukey' => $image_ukey,
                            'position' => $_model_mockup_rel->data['position'],
                            'flag_main' => $_model_mockup_rel->data['flag_main'],
                            'filename' => $mockup_file_name,
                            'is_default_mockup' => 1
                        ])->save();
                    }

                    $ukeys_push_mockup = [];

                    foreach ($data as $variant_id) {
                        $ukeys_push_mockup[] = $design_id . '_' . $variant_id;
                    }

                    $queue_push_mockup_collection = OSC::model('crossSell/pushMockup')->getCollection()->getItemsByUkeys($ukeys_push_mockup);

                    foreach ($queue_push_mockup_collection as $queue_push_mockup) {
                        $data_push_mockup = $queue_push_mockup->data['data'];
                        $count_mockup = $queue_push_mockup->data['count_mockup'];

                        $data_push_mockup['mockups'][] = $image->getId();

                        $queue_push_mockup->setData(
                            [
                                'data' => $data_push_mockup,
                                'count_mockup' => $count_mockup + 1
                            ]
                        )->save();
                    }

                    continue;
                }

                $mockup_ukey = $design_id . '_' . $_model_mockup_rel->getId() . '_' . md5(OSC::encode($_data['commands'])) . '_' . $version;

                $data_queue[] = [
                    'mockup_ukey' => $mockup_ukey,
                    'product_id' => $design_id,
                    'print_template_id' => $print_template->getId(),
                    'callback_data' => [
                        'timestamp' => time(),
                        'mockup_id' => $mockup->getId(),
                        'position' => $_model_mockup_rel->data['position'],
                        'flag_main' => $_model_mockup_rel->data['flag_main'],
                        'version' => $version,
                        'list_variant_ids' => $data,
                        'design_id' => $design_id,
                        'folder' => $tmp_aws,
                        'file_time' => $file_time,
                    ],
                    'mockup_data' => $_data,
                    'version' => $version,
                    'mockup_type' => 'cross_sell'
                ];
            }
        }

        if (count($data_queue) > 0) {
            foreach ($data_queue as $queue) {
                OSC::model('catalog/product_bulkQueue')->setData([
                    'ukey' => $design_id . '_renderCampaignMockup_' . OSC::makeUniqid(),
                    'member_id' => 1,
                    'action' => 'renderCampaignMockup',
                    'queue_data' => $queue
                ])->save();
            }

            for ($i = 1; $i <= 10; $i ++) {
                OSC::core('cron')->addQueue('catalog/campaign_renderMockup', null, ['ukey' => 'catalog/renderCampaignMockup:' . $i, 'requeue_limit' => -1, 'estimate_time' => 60 * 20]);
            }
        }
    }

    public function callApi($request_path, $request_data, $options = []) {
        try {
            if (!defined('CROSS_SELL_KEY')) {
                throw new Exception('CROSS_SELL_KEY key not found');
            }

            if (!defined('CROSS_SELL_SECRET')) {
                throw new Exception('CROSS_SELL_SECRET not found');
            }

            $header = [
                'Request-Type' => 'Service',
                'Content-Type' => 'application/json',
                'OSC-Service' => CROSS_SELL_KEY,
                'OSC-Request-Signature' => OSC_Controller::makeRequestChecksum(OSC::encode($request_data), CROSS_SELL_SECRET)
            ];

            $response = OSC::core('network')->curl(OSC::getServiceUrlCrossSell() . '/' . $request_path, [
                'timeout' => 60,
                'headers' => $header,
                'json' => $request_data
            ]);

            $response_data = $response['content'];

            if (!is_array($response_data)) {
                $response_data = OSC::decode($response_data, true);
            }

            if (!is_array($response_data)
                || !isset($response_data['errorCode'])
                || $response_data['errorCode'] != 0) {
                throw new Exception(substr('Response data is incorrect: ' . print_r($response_data, 1), 0, 1000), intval($response['response_code']));
            }

            return $response_data['data'];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public function getDataCartItemDefault(Model_Catalog_Cart $cart, array $datas) {
        $variant_price_cross_sell = $this->getPriceData(true, $datas['product_type_variant'], $datas['type_page']);

        $datas['sku'] = '2dcrosssell-' . strtoupper(uniqid(null, false) . OSC::randKey(2, 7));
        $datas['vendor'] = '2dcrosssell';
        $datas['cost'] = 0;
        $datas['tax_value'] = null;
        $datas['price'] = isset($variant_price_cross_sell['price']) ? intval($variant_price_cross_sell['price']) : 0;
        $datas['compare_at_price'] = isset($variant_price_cross_sell['compare_at_price']) ? intval($variant_price_cross_sell['compare_at_price']) : 0;
        $datas['require_shipping'] = 1;
        $datas['require_packing'] = 1;
        $datas['keep_flat'] = 0;
        $datas['weight'] = 0;
        $datas['weight_unit'] = 'kg';
        $datas['dimension_width'] = 0;
        $datas['dimension_height'] = 0;
        $datas['dimension_length'] = 0;

        unset($datas['product_type_variant']);
        unset($datas['type_page']);

        return $datas;
    }

    protected $_default_price_data = null;

    /**
     * Get default price from product type variant
     * @param bool $reload
     * @param $preload_product_type_variants
     * @return array
     */
    public function getPriceData(bool $reload = false, $product_type_variant, $type_page = 'cart') {
        if ($this->_default_price_data === null || $reload) {
            $price = [
                'price' => 0,
                'compare_at_price' => 0
            ];

            try {
                if ($product_type_variant->getId() > 0) {
                    $price['price'] = $product_type_variant->data['price'];
                    $price['compare_at_price'] = $product_type_variant->data['compare_at_price'];
                }

                $collection = OSC::model('crossSell/crossSell')
                    ->getCollection()
                    ->addCondition('product_type_variant_id', $product_type_variant->getId())
                    ->addCondition('type', $type_page == 'cart' ? 'cart' : 'thank_you')
                    ->setLimit(1)
                    ->load();

                if ($collection->getItem()) {
                    $crossSell = $collection->getItem();
                    if (in_array($crossSell->data['discount_type'], ['percent', 'fixed_amount']) && $crossSell->data['discount_value'] != 0) {
                        if ($crossSell->data['discount_type'] == 'percent') {
                            $price['price'] = round(((100 - intval($crossSell->data['discount_value'])) * $price['price']) / 100);
                        } else {
                            $price['price'] = $price['price'] - $crossSell->data['discount_value']*100;
                        }
                    }
                }

            } catch (Exception $ex) {

            }
            $this->_default_price_data = $price;
        }

        return $this->_default_price_data;
    }

    public function getDataCartItemCrossSell(Model_Catalog_Cart_Item $line_item) {
        try {
            
            $product_type_variant_id = $line_item->getProductTypeVariantId();
            $product_type_variant = OSC::model('catalog/productType_variant')->load($product_type_variant_id);
            $product_type = $product_type_variant->getProductType();
            $cut_off_timestamp = OSC::helper('catalog/common')->getCutOffTimestamp(null, $product_type);
            $cut_off_title = trim($this->setting('shipping/cut_off_time/title'));
            $country_code_place_of_manufacture = OSC::helper('core/country')->getCountryCodePlaceOfManufacture();

            $crossSellData = $line_item->getCrossSellData();
            if ($crossSellData === null) {
                throw new Exception('Line item is not cross sell');
            }

            $design_id = $crossSellData['print_template']['segment_source']['front']['source']['design_id'];
            $design_link = $crossSellData['print_template']['segment_source']['front']['source']['mockup_url'];
            $design_thumbnail = $crossSellData['print_template']['segment_source']['front']['source']['design_url'];
            
            $title = $crossSellData['print_template']['segment_source']['front']['source']['title'];
            $crosssell_config = [
                'title' => $title,
                'url' => $crossSellData['print_template']['segment_source']['front']['source']['mockup_url'] ?? '',
                'design_id' => $design_id,
                'product_type_variant_id' => $crossSellData['product_type_variant_id'],
                'preview_config' => $crossSellData['preview_config'],
                'link' => $design_link,
                'link_thumbnail' => $design_thumbnail,
            ];

            return [
                'id' => $line_item->getId(),
                'quantity' => $line_item->data['quantity'],
                'product_title' => $title ?? '',
                'product_type_title' => $crossSellData !== null ? $crossSellData['product_type']['title'] : null,
                'product_option_keys' => $crossSellData !== null && $crossSellData['product_type']['options']['keys']
                    ? 'ukey:' . $crossSellData['product_type']['ukey'] . '|' . $crossSellData['product_type']['options']['keys']
                    : null,
                'pack' => null,
                'price' => $line_item->getPrice(),
                'options'=> [],
                'url' => null,
                'semitest_config' => null,
                'campaign_config' => null,
                'crosssell_config' => $crosssell_config,
                'shipping_semitest' => null,
                'is_available' => $line_item->isAvailableToOrder(),
                'error_cart_item' => null,
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
            ];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

    }

    public function isEnableRecommend($type_page) {
        $flag = 0;
        try {
            switch ($type_page) {
                case 'cart':
                    $flag = intval(OSC::helper('core/setting')->get('cross_sell/cart/is_enable_recommend'));
                    break;
                case 'thankyou':
                    $flag = intval(OSC::helper('core/setting')->get('cross_sell/thank_you/is_enable_recommend'));
                    break;
                default:
                    throw new Exception('Data type page is incorrect');
            }
        } catch (Exception $ex) {

        }
        return $flag === 1 ? true : false;

    }

    public function getSizeRecommend($type_page) {
        $size = [
            'show_recommend' => 0,
            'show_people' => 0
        ];
        try {
            switch ($type_page) {
                case 'cart':
                    $size['show_recommend'] = intval(OSC::helper('core/setting')->get('cross_sell/cart/number_of_show_recommend'));
                    $size['show_people'] = intval(OSC::helper('core/setting')->get('cross_sell/cart/number_of_show_people'));
                    break;
                case 'thankyou':
                    $size['show_recommend'] = intval(OSC::helper('core/setting')->get('cross_sell/thank_you/number_of_show_recommend'));
                    $size['show_people'] = intval(OSC::helper('core/setting')->get('cross_sell/thank_you/number_of_show_people'));
                    break;
                default:
                    throw new Exception('Data type page is incorrect');
            }
        } catch (Exception $ex) {

        }
        return $size;

    }

    public function getDefaultData() {
        try {
            $list_default_light = [];
            $list_default_dark = [];
            $default = [];
            $config_light = $this->getProductTypeVariantIdsByTypeDesign('light');
            $config_dark = $this->getProductTypeVariantIdsByTypeDesign('dark');

            foreach ($this->getProductTypes() as $productType) {
                if (isset($productType->data['default_for_light_design']['product_type_variant_ids']) &&
                    count($productType->data['default_for_light_design']['product_type_variant_ids']) > 0
                ) {
                    foreach ($productType->data['default_for_light_design']['product_type_variant_ids'] as $product_type_variant_id) {
                        if (in_array($product_type_variant_id, $config_light['list_product_type_variant_ids'])) {
                            $list_default_light[] = $product_type_variant_id;
                        }
                    }
                }

                if (isset($productType->data['default_for_dark_design']['product_type_variant_ids']) &&
                    count($productType->data['default_for_dark_design']['product_type_variant_ids']) > 0
                ) {
                    foreach ($productType->data['default_for_dark_design']['product_type_variant_ids'] as $product_type_variant_id) {
                        if (in_array($product_type_variant_id, $config_dark['list_product_type_variant_ids'])) {
                            $list_default_dark[] = $product_type_variant_id;
                        }
                    }
                }
            }
            if (empty($list_default_light)) {
                $list_default_light = $config_light['list_product_type_variant_ids'];
            }

            if (empty($list_default_dark)) {
                $list_default_dark = $config_dark['list_product_type_variant_ids'];
            }

            $default['list_default_light'] = $list_default_light;
            $default['list_default_dark'] = $list_default_dark;
            $default['list_default_ids'] = array_merge($list_default_dark, $list_default_light);
        } catch (Exception $ex) {

        }
        return $default;

    }

    public function getCartFrmOptionConfig($country_code = '', $province_code = '', $options = []) {
        try {
            static $cached = [];

            $type_page = $options['type_page'] ?? 'cart';
            $type_design = $options['type_design'];
            $column_default_product_type = $type_design == 'light' ? 'default_for_light_design' : 'default_for_dark_design';
            $response = $options['response'];
            $product_types = $options['product_types'];
            $product_type_variants = $options['product_type_variants'];
            $default_product_type_variant_id = intval($options['default_product_type_variant_id']);

            $cache_key = $type_page. '-' . md5(OSC::encode($response)) . '-' . $country_code . '-' . $province_code;

            if (isset($cached[$cache_key])) {
                return $cached[$cache_key];
            }

            $product_type_variant_ids = [];
            $option_ids = [];
            $option_value_ids = [];
            $result = [
                'product_types' => [],
                'product_variants' => [],
                'options' => [],
                'option_values' => [],
                'keys' => [],
                'images' => [],
                'default_variant' => []
            ];

            $list_default_variant_ids = [];
            foreach ($product_types as $product_type) {
                $product_type_key = '_' . $product_type->getId();
                $result['product_types'][$product_type_key] = [
                    'id' => $product_type->data['id'],
                    'group_name' => $product_type->data['group_name'],
                    'title' => $product_type->data['title'],
                    'custom_title' => $product_type->data['custom_title'],
                    'image' => OSC::core('template')->getImage($product_type->data['image']),
                    'ukey' => $product_type->data['ukey'],
                    'list_option_value' => [],
                    'options' => [],
                    'product_type_option_ids' => $product_type->data['product_type_option_ids']
                ];

                $active = false;

                foreach ($product_type->getProductTypeVariants() as $product_type_variant) {
                    if (!in_array($product_type_variant->getId(), array_keys($product_type_variants))) {
                        continue;
                    }
                    $active = true;
                    $product_type_variant_ids[] = $product_type_variant->getId();
                    $data_price = $this->getPriceData(true, $product_type_variant, $type_page);

                    $result['product_variants'][$product_type_variant->data['ukey']] = [
                        'product_type' => $product_type->getId(),
                        'product_type_ukey' => $product_type_key,
                        'ukey' => $product_type_variant->data['ukey'],
                        'position' => 0,
                        'id' => $product_type_variant->getId(),
                        'title' => $product_type_variant->data['title'],
                        'price' => $data_price['price'],
                        'compare_at_price' => $data_price['compare_at_price'],
                        'product_variant_ukey' => $product_type_variant->getOptionValues()['keys']
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

                                    $result['product_variants'][$product_type_variant->data['ukey']]['options'][$item[0]] = $item[1];
                                }
                            }
                        }
                    }
                }

                if ($active == false) {
                    unset($result['product_types'][$product_type_key]);
                    continue;
                }

                //Prepare data for option values
                if (!empty($option_value_ids)) {
                    foreach (OSC::helper('catalog/campaign')->getProductTypeOptionValue($option_value_ids) as $option_value) {
                        $result['option_values'][$option_value['id']] = array_merge([
                            'id' => $option_value['id'],
                            'product_type_option_id' => $option_value['product_type_option_id'],
                            'title' => $option_value['title'],
                            'ukey' => $option_value['ukey']
                        ], $option_value['meta_data'] ?? []);
                    }
                }

                uasort($result['option_values'], function ($a, $b) use ($option_value_ids) {
                    $position_a = array_search($a['id'], $option_value_ids);
                    $position_b = array_search($b['id'], $option_value_ids);

                    return $position_a > $position_b;
                });

                $list_default_variant_ids = array_merge($list_default_variant_ids, $product_type->data[$column_default_product_type]['product_type_variant_ids']);
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
                }
            }

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
            $default_variant = [];
            foreach ($result['product_variants'] as $variant) {
                $default_variant[] = [
                    'product_type_variant_key' => $variant['ukey'],
                    'product_type_variant_id' => $variant['id'],
                    'price' => $variant['price'],
                    'product_type_id' => $variant['product_type'],
                    'options' => $variant['options'],
                ];
            }

            if ($default_product_type_variant_id > 0) {
                $result['default_variant'] = $default_variant[array_search($default_product_type_variant_id, array_column($default_variant, 'product_type_variant_id'))];
            } else {
                usort($default_variant, function($v1, $v2) {
                    return $v1['price'] <=> $v2['price'];
                });
                $result['default_variant'] = !empty($default_variant) ? $default_variant[0] : [];
            }

            $cached[$cache_key] = $result;

            return $cached[$cache_key];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getDataToSaveOrderItem(Model_Catalog_Cart_Item $line_item) {
        try {
            if ($line_item->getId() < 1){
                throw new Exception('Cart item is incorrect');
            }

            $crossSellData = $line_item->getCrossSellData();

            if (!$crossSellData) {
                throw new Exception('Data cross sell is incorrect');
            }

            if (is_array($line_item->data['additional_data'])) {
                $line_item->data['additional_data'] = [];
            }
            $line_item->data['additional_data']['is_cross_sell'] = 1;

            $item_data = [
                'title' => $crossSellData['print_template']['segment_source']['front']['source']['title'] ?? 'Cross sell Tshirt',
                'product_type' => $crossSellData['product_type'],
                'product_type_variant_id' => $crossSellData['product_type_variant_id'],
                'options' => [],
                'tax_value' => $line_item->getTaxValue(),
                'discount' => $line_item->getDiscount(),
                'additional_data' => $line_item->data['additional_data'],
                'fulfilled_quantity' => 0,
                'refunded_quantity' => 0,
                'other_quantity' => 0
            ];

            $columns = [
                'product_id',
                'variant_id',
                'sku',
                'vendor',
                'price',
                'tax_value',
                'cost',
                'quantity',
                'require_shipping',
                'require_packing',
                'weight',
                'weight_unit',
                'keep_flat',
                'dimension_width',
                'dimension_height',
                'dimension_length'
            ];

            foreach ($columns as $key) {
                $item_data[$key] = $line_item->data[$key];
            }

            if ($crossSellData) {
                $item_data['options'] = [['title' => 'Product type', 'value' => $crossSellData['product_type']['title']]];
                foreach ($crossSellData['options'] as $option) {
                    $item_data['options'][] = [
                        'title' => $option['title'],
                        'value' => $option['value']['title'],
                        'key' => $option['value']['key']
                    ];
                }
            }

            $item_data['additional_data'] = $line_item->data['additional_data'];

            $item_data['additional_data']['cart_item_id'] = $line_item->getId();

            return $item_data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function isSellingProductTypeVariantInCountry(int $product_type_variant_id, $country_code = '', $province_code = '') {
        if ($product_type_variant_id === 0) {
            return true;
        }

        $location_code = ',' . $country_code . '_' . $province_code . ',';

        $DB = OSC::core('database');

        $query = <<<EOF
SELECT count (*) as total
FROM osc_supply_variant
WHERE product_type_variant_id = {$product_type_variant_id}
AND location_parsed LIKE '%{$location_code}%';
EOF;

        $DB->query($query);

        return count($DB->fetchArrayAll()) > 0;
    }

    public function getDataOrderItem(Model_Catalog_Order_Item $line_item) {
        if (!$line_item->isCrossSellMode()) {
            return [];
        }
        $crossSellData = $line_item->getCrossSellData();

        $crossSellData = $line_item->getCrossSellData();
        if ($crossSellData === null) {
            throw new Exception('Line item is not cross sell');
        }

        $crosssell_config = [
            'url' => $line_item->getImageUrl(),
            'title' => $line_item->data['title'],
            'design_id' => $crossSellData['print_template']['segment_source']['front']['source']['design_id'],
            'product_type_variant_id' => $line_item->data['product_type_variant_id'],
            'preview_config' => $crossSellData['preview_config'],
            'link' => $crossSellData['print_template']['segment_source']['front']['source']['design_url'],
            'link_thumbnail' => $crossSellData['print_template']['segment_source']['front']['source']['mockup_url'],
        ];

        $result = [
            'id' => $line_item->getId(),
            'quantity' => $line_item->data['quantity'],
            'other_quantity' => $line_item->data['other_quantity'],
            'variant_id' => $line_item->data['variant_id'],
            'product_id' => $line_item->data['product_id'],
            'product_title' => $line_item->data['title'],
            'product_type_title' => count($line_item->data['options']) > 0 ? $line_item->getVariantOptionsText() : '',
            'pack' => $line_item->getPackData(),
            'price' => $line_item->data['price'],
            'options' => $line_item->getVariantOptionsText(),
            'image_url' => $line_item->getImageUrl(),
            'url' => '',
            'semitest_config' => null,
            'campaign_config' => null,
            'crosssell_config' => $crosssell_config,
            'flag_pcs' => 1,
            'item_resend' => isset($additional_data['resend']['resend']) ? true : false,
            'show_edit_design' => 0,
            'discount' => $line_item->data['discount']
        ];

        return $result;
    }

    public function updateCartByThankyouPage(Model_Catalog_Cart $cart, $item = []) {
        try {
            $order_ukey = trim(strval($item['order_ukey']));

            $order = OSC::model('catalog/order');

            try {
                $order->loadByOrderUKey($order_ukey);
            } catch (Exception $ex) { }

            if ($order->getId() < 1) {
                throw new Exception('Order is not exist');
            }

            $option_billing = 'same';
            $data_update = [
                'email' => $order->data['email']
            ];

            foreach ($order->getShippingAddress() as $key => $value) {
                $data_update['shipping_'.$key] = $value;
            }

            foreach ($order->getBillingAddress() as $key => $value) {
                $data_update['billing_'.$key] = $value;
                if ($option_billing == 'same' && $data_update['shipping_'.$key] != $value) {
                    $option_billing = 'another';
                }
            }

            // create address account crm

            try {
                $address_shipping = [
                    'shop_id' => $order->data['shop_id'],
                    'customer_id' => $order->data['crm_customer_id'],
                    'address' => OSC::helper('account/address')->getDataAddressByOrder($order, 'shipping'),
                ];
                $customer_shipping = OSC::helper('account/address')->findOrCreate($address_shipping);

                $data_update['shipping_address_id'] = $customer_shipping['id'];
            } catch (Exception $ex) {
            }
            if ($option_billing != 'same') {
                try {
                    $address_billing = [
                        'shop_id' => $order->data['shop_id'],
                        'customer_id' => $order->data['crm_customer_id'],
                        'address' => OSC::helper('account/address')->getDataAddressByOrder($order, 'billing', 1),
                    ];

                    $customer_billing = OSC::helper('account/address')->findOrCreate($address_billing);

                    $data_update['billing_address_id'] = $customer_billing['id'];
                } catch (Exception $ex) {

                }
            } else {
                $data_update['billing_address_id'] = $data_update['shipping_address_id'];
            }


            $cart->setData($data_update)->save();
        } catch (Exception $ex) {

        }
    }

    public function getProductTypeVariantIdsByTypeDesign($type = 'light') {
        $column = 'is_light_design';
        if ($type !== 'light') {
            $column = 'is_dark_design';
        }

        $DB = OSC::core('database');

        $query = <<<EOF
SELECT product_type_id, product_type_variant_id
FROM osc_cross_sell_design_color
WHERE {$column} = 1;
EOF;

        $DB->query($query, null, 'fetch_product_type_variant_variant');
        $result = [
            'list_product_type_ids' => [],
            'list_product_type_variant_ids' => []
        ];
        while ($row = $DB->fetchArray('fetch_product_type_variant_variant')) {
            $result['list_product_type_ids'][$row['product_type_id']][] = intval($row['product_type_variant_id']);
            $result['list_product_type_variant_ids'][] = intval($row['product_type_variant_id']);
        }

        $DB->free('fetch_product_type_variant_variant');

        return $result;
    }

    public function _addProductToCartBuyAgain(Model_Catalog_Cart $cart, Model_Catalog_Order_Item $order_item) {
        try {
            $item = $order_item->data;

            $item['custom_data'] = $order_item->getOrderItemMeta()->data['custom_data'];

            $additional_data = [];

            foreach ($item['additional_data'] as $key => $value) {
                if ($key !== 'is_cross_sell') {
                    continue;
                }
                $additional_data[$key] = $value;
            }

            $quantity = intval($item['quantity']);

            $line_item = OSC::model('catalog/cart_item');

            $line_item_ukey = $line_item->makeUkey($cart->getId(), 0, $item['custom_data'], [], $additional_data);

            try {
                $line_item->loadByUKey($line_item_ukey);
            } catch (Exception $ex) {

            }

            try {
                $product_type_variant = OSC::model('catalog/productType_variant')->load($item['product_type_variant_id']);
            } catch (Exception $ex) {
                throw new Exception('Not exist product type variant');
            }

            if ($line_item->getId() < 1) {
                $data = [
                    'cart_id' => $cart->getId(),
                    'quantity' => $quantity,
                    'custom_data' => $item['custom_data'],
                    'product_type_variant' => $product_type_variant,
                    'additional_data' => $additional_data,
                    'type_page' => 'thankyou'
                ];
                $data_add_cart = OSC::helper('crossSell/common')->getDataCartItemDefault($cart, $data);
                $line_item->setData($data_add_cart)->save();

                $cart->getLineItems()->addItem($line_item);
            } else {
                try {
                    $line_item->setData(['quantity' => $line_item->data['quantity'] + $quantity])->save();

                    $line_item->reload();
                } catch (Exception $ex) {

                }
            }

            $_SESSION['cart_new_item'] = $line_item->getId();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}