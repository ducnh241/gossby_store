<?php

class Helper_Catalog_Campaign extends OSC_Object
{
    public function orderLineItemGetDesignEditFrmData($line_item, $campaign_data) {
        $personalized_design_configs = [];
        $campaign_data = $campaign_data['data'] ?? [];

        $tempArray = [];
        $sameDesign = false;

        $cart_option_config = null;
        $product_config = null;
        $current_option = null;

        if (isset($campaign_data['product_type_variant_id']) && !empty($campaign_data['product_type_variant_id'])) {
            $product_type_variant = OSC::model('catalog/productType_variant')->load($campaign_data['product_type_variant_id']);
            $current_option['product_type_id'] = $product_type_variant->data['product_type_id'];
        }

        if (isset($campaign_data['product_type']) && !empty($campaign_data['product_type'])) {
            $current_option['product_type'] = $campaign_data['product_type'];
        }

        if (isset($campaign_data['print_template']['segment_source']) && !empty($campaign_data['print_template']['segment_source'])) {
            $print_template = $campaign_data['print_template'] ?? [];

            try {
                $product = OSC::model('catalog/product')->load($line_item->data['product_id']);
            }catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }

            $order = $line_item->getOrder();

            $shipping_country_code = $order->data['shipping_country_code'];

            if (!isset($shipping_country_code)) {
                $shipping_country_code = '';
            }

            $shipping_province_code = $order->data['shipping_province_code'];

            if (!isset($shipping_province_code)) {
                $shipping_province_code = '';
            }

            if (isset($product) && $product instanceof Model_Catalog_Product) {
                $product_config = OSC::helper('catalog/campaign')->prepareCampaignConfig($product);
                $cart_option_config = $product->getCartFrmOptionConfig($shipping_country_code, $shipping_province_code, ['skip_auth' => 1]);

                if (!isset($cart_option_config['product_types']) || empty($cart_option_config['product_types'])) {
                    throw new Exception('Product is not exists');
                }

                $current_option['product_id'] = $product->getId();
            }

            $campaign_config = $product->data['meta_data']['campaign_config']['print_template_config'];

            OSC::helper('catalog/campaign')->replaceLayerUrl($print_template['preview_config'], $campaign_data['product_type']['options']['keys']);

            $map_print_template_design_campaign = [];

            if (is_array($campaign_config) && count($campaign_config) > 0) {
                foreach ($campaign_config as $value) {
                    if ($value['print_template_id'] != $print_template['print_template_id']) {
                        continue;
                    }

                    if (is_array($value['segments']) && count($value['segments']) > 0) {
                        foreach ($value['segments'] as $key => $segment_source_campaign) {
                            if ($segment_source_campaign['source']['type'] == 'personalizedDesign' && isset($segment_source_campaign['source']['design_id']) && !empty($segment_source_campaign['source']['design_id'])) {
                                $map_print_template_design_campaign[$key] = $segment_source_campaign['source']['design_id'];
                            }
                        }
                    }
                }
            }

            $segment_sources = $campaign_config[array_search($print_template['print_template_id'], array_column($campaign_config, 'print_template_id'))]['segments'];

            if (is_array($segment_sources) && count($segment_sources) < 1) {
                $segment_sources = [];
                foreach ($print_template['segment_source'] as $key => $segment_sources_item) {
                    unset($segment_sources_item['source']['svg']);
                    unset($segment_sources_item['source']['config']);
                    unset($segment_sources_item['source']['config_preview']);
                    $segment_sources[$key] = $segment_sources_item;
                }
            }

            foreach ($campaign_data['print_template']['segment_source'] as $segment_key => $segment_source) {
                if ($segment_source['source']['type'] == 'personalizedDesign' && isset($segment_source['source']['design_id']) && !empty($segment_source['source']['design_id'])) {
                    $preview_config = [];

                    foreach ($print_template['preview_config'] as $config_item) {
                        if (isset($config_item['config'][$segment_key]) && !empty($config_item['config'][$segment_key])) {
                            $preview_config = $config_item;
                            break;
                        }
                    }

                    $design_id = $segment_source['source']['design_id'];

                    $flag_use_design_in_campaign = true;

                    if ($design_id != $map_print_template_design_campaign[$segment_key] && intval($map_print_template_design_campaign[$segment_key]) > 0) {
                        $design_id = $map_print_template_design_campaign[$segment_key];
                        $flag_use_design_in_campaign = false;
                    }

                    if (array_key_exists($design_id, $personalized_design_configs)) {
                        $sameDesign = $design_id;
                        $tempArray[$design_id] = [
                            'title' => $preview_config['title'] ?? '',
                            'key' => $segment_key,
                            'personalized_design_id' => $design_id,
                            'config' => $flag_use_design_in_campaign == true ? $segment_source['source']['config'] : [],
                            'document_type' => '',
                            'components' => '',
                            'mockup_config' => [
                                'title' => $preview_config['title'] ?? '',
                                'product_type' => $segment_key,
                                'design_key' => $segment_key,
                                'design' => [
                                    'svg' => ''
                                ],
                                'preview_config' => $preview_config,
                                'segment_configs' => $print_template['segments'] ?? [],
                                'segment_sources' => $segment_sources
                            ]
                        ];
                    } else {
                        $personalized_design_configs[$design_id] = [
                            'title' => $preview_config['title'] ?? '',
                            'key' => $segment_key,
                            'personalized_design_id' => $design_id,
                            'config' => $flag_use_design_in_campaign == true ? $segment_source['source']['config'] : [],
                            'document_type' => '',
                            'components' => '',
                            'mockup_config' => [
                                'title' => $preview_config['title'] ?? '',
                                'product_type' => $segment_key,
                                'design_key' => $segment_key,
                                'design' => [
                                    'svg' => ''
                                ],
                                'preview_config' => $preview_config,
                                'segment_configs' => $print_template['segments'] ?? [],
                                'segment_sources' => $segment_sources
                            ]
                        ];
                    }
                }
            }

            if (count($personalized_design_configs) < 1) {
                throw new Exception('No personalized config was found');
            }

            $design_collection = OSC::model('personalizedDesign/design')->getCollection()->load(array_keys($personalized_design_configs));

            if ($design_collection->length() != count($personalized_design_configs)) {
                throw new Exception('Personalized design is not exists');
            }

            foreach ($design_collection as $design) {
                $form_data = $design->extractPersonalizedFormData();
                $design_id = $design->getId();

                $personalized_design_configs[$design_id]['components'] = $form_data['components'];
                $personalized_design_configs[$design_id]['image_data'] = $form_data['image_data'];
                $personalized_design_configs[$design_id]['document_type'] = $design->data['design_data']['document'];
            }

            if ($sameDesign) {
                $tempArray[$sameDesign]['components'] = $personalized_design_configs[$sameDesign]['components'];
                $tempArray[$sameDesign]['image_data'] = $personalized_design_configs[$sameDesign]['image_data'];
                $tempArray[$sameDesign]['document_type'] = $personalized_design_configs[$sameDesign]['document_type'];

                $personalized_design_configs[] = $tempArray[$sameDesign];
            }
        }

        return [
            'current_option' => $current_option,
            'cart_form_config' => $cart_option_config,
            'product_config' => $product_config,
            'designs' => array_values($personalized_design_configs),
            'flag_show_edit_product_type' =>  0
        ];
    }

    public function orderLineItemVerifyNewDesignData($campaign_data, $new_config, $reset_print_template = false, $product_id = 0, $reset_segment_source = false) {
        if (!is_array($new_config) || count($new_config) < 1) {
            throw new Exception('Config is incorrect');
        }

        $print_template_id = $campaign_data['data']['print_template']['print_template_id'] ?? 0;

        try {
            $product = OSC::model('catalog/product')->load($product_id);
            $segments = null;

            if (is_array($product->data['meta_data']['campaign_config']['print_template_config']) && count($product->data['meta_data']['campaign_config']['print_template_config']) > 0) {
                foreach ($product->data['meta_data']['campaign_config']['print_template_config'] as $print_template_config) {
                    if ($print_template_config['print_template_id'] == $print_template_id) {
                        $segments = $print_template_config['segments'];
                        break;
                    }
                }

                if ($segments == null) {
                    throw new Exception('Not found print template id ' . $print_template_id . ' in product id ' . $product_id, 404);
                }

                foreach ($segments as $segment_key => $segment_source) {
                    $campaign_data['data']['print_template']['segment_source'][$segment_key] = $segment_source;
                }
            }
        } catch (Exception $ex) {
            if ($ex->getCode() != 404) {
                throw new Exception($ex->getMessage());
            }
        }

        foreach ($campaign_data['data']['print_template']['segment_source'] as $segment_key => &$segment_source) {
            if (!isset($new_config[$segment_key])) {
                throw new Exception('Missing config for design [' . $segment_key . ']');
            }

            try {

                $design_model = OSC::model('personalizedDesign/design')->load($segment_source['source']['design_id']);

                $personalized_options = $new_config[$segment_key];

                $personalizedDesign = Observer_Catalog_Campaign::validatePersonalizedDesign($design_model, $personalized_options);

                OSC::helper('personalizedDesign/common')->verifyCustomConfig($design_model, $personalized_options);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            $segment_source['source']['svg'] = $personalizedDesign['svg'] ?? '';
            $segment_source['source']['config'] = $personalizedDesign['config'] ?? [];
            $segment_source['source']['config_preview'] = $personalizedDesign['config_preview'] ?? [];
            $segment_source['source']['design_last_update'] = $personalizedDesign['design_last_update'] ?? 0;
        }

        if ($reset_print_template) {
            try {
                $print_template = OSC::model('catalog/printTemplate')->load($print_template_id);

                if (isset($print_template->data['config']['preview_config']) && !empty($print_template->data['config']['preview_config'])) {
                    $campaign_data['data']['print_template']['preview_config'] = $print_template->data['config']['preview_config'];
                }

                if (isset($print_template->data['config']['segments']) && !empty($print_template->data['config']['segments'])) {
                    $campaign_data['data']['print_template']['segments'] = $print_template->data['config']['segments'];
                }

                if (isset($print_template->data['config']['print_file']) && !empty($print_template->data['config']['print_file'])) {
                    $campaign_data['data']['print_template']['print_file'] = $print_template->data['config']['print_file'];
                }
            } catch (Exception $exception) {
                throw new Exception('No print template found');
            }
        }

        return $campaign_data;
    }

    public function getOrderLineItemMockupFileName($line_item) {
        return 'catalog/campaign/order/' . date('Ymd', $line_item->data['added_timestamp']) . '/' . $line_item->data['order_id'] . '/' . $line_item->data['item_id'] . '/' . $line_item->data['added_timestamp'] . '.jpg';
    }

    public function makeDesignHash($product_type_config) {
        return md5(OSC::encode($product_type_config['design']) . OSC::encode(isset($product_type_config['option']) ? $product_type_config['option'] : []));
    }

    public function renderMockupData($product) {
        $mockup_configs = [];

        $campaign_root_name = 'catalog/campaign/mockup/' . $product->getId();
        $campaign_root_path = OSC_Storage::getStoragePath($campaign_root_name);

        foreach ($product->getCampaignProductTypeVariants() as $product_type_variant) {
            $has_option = isset($product_type_variant['data']['option']) && is_array($product_type_variant['data']['option']);

            foreach ($product_type_variant['config']['design'] as $design_key => $design_config) {
                $main_design_keys[] = $design_key;

                if (!$has_option) {
                    $mockup_file_path = $campaign_root_path . '/' . $product_type_variant['data']['type'] . '/' . $design_key . '.json';

                    $mockup_configs[$mockup_file_path] = [
                        'title' => $product_type_variant['data']['design'][$design_key]['title'],
                        'product_type' => $product_type_variant['data']['type'],
                        'image_mode' => true,
                        'design_key' => $design_key,
                        'design' => $design_config,
                        'mockup_config' => $product_type_variant['data']['mockup_config'][$design_key],
                        'design_area' => $product_type_variant['data']['design'][$design_key]['area']
                    ];
                }
            }

            if ($has_option) {
                foreach ($product_type_variant['config']['option'] as $value_key => $value_designs) {
                    $design_keys = [];

                    if (is_array($value_designs) && count($value_designs) > 0) {
                        foreach ($value_designs as $design_key => $design_config) {
                            $design_keys[] = $design_key;

                            $mockup_file_path = $campaign_root_path . '/' . $product_type_variant['data']['type'] . '/' . $value_key . '/' . $design_key . '.json';

                            $mockup_configs[$mockup_file_path] = [
                                'title' => $product_type_variant['data']['design'][$design_key]['title'],
                                'product_type' => $product_type_variant['data']['type'],
                                'image_mode' => true,
                                'design_key' => $design_key,
                                'design' => $design_config,
                                'mockup_config' => $product_type_variant['data']['mockup_config'][$design_key],
                                'design_area' => $product_type_variant['data']['design'][$design_key]['area'],
                                'option' => $value_key
                            ];
                        }
                    }

                    $design_keys = array_diff($main_design_keys, $design_keys);

                    foreach ($design_keys as $design_key) {
                        $mockup_file_path = $campaign_root_path . '/' . $product_type_variant['data']['type'] . '/' . $value_key . '/' . $design_key . '.json';

                        $mockup_configs[$mockup_file_path] = $mockup_configs[$mockup_file_path] = [
                            'title' => $product_type_variant['data']['design'][$design_key]['title'],
                            'product_type' => $product_type_variant['data']['type'],
                            'image_mode' => true,
                            'design_key' => $design_key,
                            'design' => $product_type_variant['config']['design'][$design_key],
                            'mockup_config' => $product_type_variant['data']['mockup_config'][$design_key],
                            'design_area' => $product_type_variant['data']['design'][$design_key]['area'],
                            'option' => $value_key
                        ];
                    }
                }
            }
        }

        foreach ($mockup_configs as $file_path => $mockup_config) {
            OSC::writeToFile($file_path, OSC::encode($mockup_config));
        }
    }

    public function syncAfterDelete($product_id){
        try {
            OSC::model('catalog/product_bulkQueue')->loadByUKey('campaign/deleteAllRenderMockup:' . $product_id);
        }catch (Exception $ex) {
            if ($ex->getCode() != 404){
                throw new Exception($ex->getMessage());
            }

            OSC::model('catalog/product_bulkQueue')->setData([
                'ukey' => 'campaign/deleteAllRenderMockup:' . $product_id,
                'member_id' => OSC::helper('user/authentication')->getMember()->getId(),
                'action' => 'v2DeleteRenderCampaignMockup',
                'queue_data' => [
                    'campaign_id' => $product_id
                ]
            ])->save();

            OSC::core('cron')->addQueue('catalog/campaign_deleteRenderMockup', null, ['ukey' => 'catalog/deleteRenderCampaignMockup', 'requeue_limit' => -1, 'estimate_time' => 60*2]);
        }
    }

    public function mapPrintTemplate(array $print_template_ids = []) {
        if (count($print_template_ids) < 1) {
            return [];
        }
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $print_template_query_ids = implode(',', $print_template_ids);

        $DB->select('*', 'print_template_map', "print_template_id in ({$print_template_query_ids}) or print_template_map_id in ({$print_template_query_ids})" , null, null, 'fetch_print_template');

        $value_maps = [];
        $remove = [];
        while ($row = $DB->fetchArray('fetch_print_template')) {
            if (in_array($row['print_template_map_id'], $print_template_ids) && in_array($row['print_template_map_id'], $print_template_ids)) {
                $remove[] =  $row['print_template_map_id'];
            }
        }
        $result = [];
        foreach ($print_template_ids as $print_template_id) {
            if (in_array($print_template_id, $remove)) {
                continue;
            }
            $result[] = $print_template_id;
        }

        $DB->free('fetch_print_template');

        return $result;
    }

    public function replaceLayerUrl(&$list_preview_config, $options) {
        $replace_key = []; $replace_value = [];
        $options = explode('|', $options);

        foreach ($options as $option) {
            $item = explode(':', $option);
            if (isset($item[0]) && !empty($item[0]) && isset($item[1]) && !empty($item[1])) {
                $replace_key[] = '{opt.' . $item[0] . '}';
                $replace_value[] = str_replace($item[0] . '/', '', $item[1]);
            }
        }

        foreach ($list_preview_config as &$preview_config) {
            if (isset($preview_config['layer']) && !empty($preview_config['layer'])) {
                foreach ($preview_config['layer'] as &$layer) {
                    if ($layer !== 'main') {
                        $layer = str_replace($replace_key, $replace_value, $layer);
                    }
                }
            }
        }
    }

    //Find replaceable print_template
    public function findReplaceablePrintTemplate(array $list_print_template_ids = [], $useCache = false) {
        if (count($list_print_template_ids) < 1) {
            return [];
        }

        $print_template_ids = implode(',', $list_print_template_ids);

        $cache_key = __FUNCTION__ . "|helper.catalog.campaign|list_print_template_ids:,{$print_template_ids},|";
        if ($useCache && ($cache = OSC::core('cache')->get($cache_key)) !== false) {
            return $cache;
        }

        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->select('*', 'print_template_map', "print_template_id IN ({$print_template_ids}) OR print_template_map_id IN ({$print_template_ids})" , null, null, 'fetch_print_template');

        $value_maps = [];
        while ($row = $DB->fetchArray('fetch_print_template')) {
            foreach ($list_print_template_ids as $print_template_id) {
                if ($print_template_id == $row['print_template_map_id'] && !in_array($row['print_template_id'], $value_maps[$print_template_id])) {
                    $value_maps[$print_template_id][] = intval($row['print_template_id']);
                }

                if ($print_template_id == $row['print_template_id'] && !in_array($row['print_template_map_id'], $value_maps[$print_template_id])) {
                    $value_maps[$print_template_id][] = intval($row['print_template_map_id']);
                }
            }
        }

        $DB->free('fetch_print_template');

        if (!empty($value_maps)) {
            foreach ($value_maps as $key => &$value_map) {
                $value_map[] = $key;
                $value_map = array_filter($value_map);
            }
        }

        OSC::core('cache')->set($cache_key, $value_maps, OSC_CACHE_TIME);

        return $value_maps;
    }

    public function getPrintTemplate($print_template_ids) {
        $str_print_template_ids = is_array($print_template_ids) ? implode(',', $print_template_ids) : $print_template_ids;
        $cache_key = "getPrintTemplate|helper.catalog.campaign|print_template_ids:,{$str_print_template_ids},|";
        $cache = OSC::core('cache')->get($cache_key);
        if ($cache !== false) {
            return $cache;
        }

        $result = OSC::model('catalog/printTemplate')->getCollection()->load($print_template_ids)->toArray();
        OSC::core('cache')->set($cache_key, $result, OSC_CACHE_TIME);

        return $result;
    }

    public function getProductTypeOption($option_ids) {
        $str_option_ids = is_array($option_ids) ? implode(',', $option_ids) : $option_ids;
        $cache_key = "getProductTypeOption|helper.catalog.campaign|option_ids:,{$str_option_ids},|";
        $cache = OSC::core('cache')->get($cache_key);
        if ($cache !== false) {
            return $cache;
        }

        $result = OSC::model('catalog/productType_option')->getCollection()->load($option_ids)->toArray();
        OSC::core('cache')->set($cache_key, $result, OSC_CACHE_TIME);

        return $result;
    }

    public function getProductTypeOptionValue($option_value_ids) {
        $str_option_value_ids = is_array($option_value_ids) ? implode(',', $option_value_ids) : $option_value_ids;
        $cache_key = "getProductTypeOptionValue|helper.catalog.campaign|option_value_ids:,{$str_option_value_ids},|";
        $cache = OSC::core('cache')->get($cache_key);
        if ($cache !== false) {
            return $cache;
        }

        $result = OSC::model('catalog/productType_optionValue')->getCollection()->load($option_value_ids)->toArray();
        OSC::core('cache')->set($cache_key, $result, OSC_CACHE_TIME);

        return $result;
    }

    /**
     * @param $product_type_ids
     * @return array
     * @throws OSC_Exception_Runtime
     */
    public function getPreloadPacksByProductTypes($product_type_ids) {
        $result = [];
        $packs = OSC::model('catalog/product_pack')
            ->getCollection()
            ->addCondition('product_type_id', $product_type_ids, OSC_Database::OPERATOR_IN)
            ->load()
            ->toArray();

        foreach ($packs as $pack) {
            $result[$pack['product_type_id']][] = $pack;
        }

        return $result;
    }

    protected $_list_product_type_variants = null;

    public function getPreloadProductTypeVariant($product_type_variant_ids) {
        $str_product_type_variant_ids = is_array($product_type_variant_ids) ?
            implode(',', $product_type_variant_ids) :
            $product_type_variant_ids;

        $this->_list_product_type_variants = OSC::model('catalog/productType_variant')->getCollection();

        $cache_key = "getProductTypeVariant|helper.catalog.campaign|product_type_variant_ids:,{$str_product_type_variant_ids},|";
        $cache = OSC::core('cache')->get($cache_key);
        if ($cache !== false) {
            foreach ($cache as $item) {
                $this->_list_product_type_variants->addItem(OSC::model('catalog/productType_variant')->bind($item));
            }
        } else {
            $preload_data = $this->getPreloadProductTypeVariants($product_type_variant_ids);
            $after_product_type_variant_ids = [];
            foreach ($product_type_variant_ids as $product_type_variant_id) {
                $item = $preload_data[$product_type_variant_id];
                if (!empty($item)) {
                    $this->_list_product_type_variants->addItem(OSC::model('catalog/productType_variant')->bind($item));
                } else {
                    $after_product_type_variant_ids[] = $product_type_variant_id;
                }
            }

            if (count($after_product_type_variant_ids) > 0) {
                $after_load_product_type_variants = OSC::model('catalog/productType_variant')
                    ->getCollection()
                    ->load($after_product_type_variant_ids);

                foreach ($after_load_product_type_variants as $item) {
                    $this->_list_product_type_variants->addItem($item);
                }
            }

            OSC::core('cache')->set($cache_key, $this->_list_product_type_variants->toArray(), OSC_CACHE_TIME);
        }

        return $this->_list_product_type_variants;
    }

    protected $_list_product_type = null;

    public function getPreloadProductType($product_type_ids) {
        $str_product_type_ids = is_array($product_type_ids) ?
            implode(',', $product_type_ids) :
            $product_type_ids;

        $this->_list_product_type = OSC::model('catalog/productType')->getCollection();

        $cache_key = "getProductType|helper.catalog.campaign|product_type_ids:,{$str_product_type_ids},|";
        $cache = OSC::core('cache')->get($cache_key);
        if ($cache !== false) {
            foreach ($cache as $item) {
                $this->_list_product_type->addItem(OSC::model('catalog/productType')->bind($item));
            }
        } else {
            $preload_data = $this->getPreloadProductTypes($product_type_ids);
            $after_load_product_type_ids = [];

            foreach ($product_type_ids as $product_type_id) {
                $item = $preload_data[$product_type_id];
                if (!empty($item)) {
                    $this->_list_product_type->addItem(OSC::model('catalog/productType')->bind($item));
                } else {
                    $after_load_product_type_ids[] = $product_type_id;
                }
            }

            if (count($after_load_product_type_ids) > 0) {
                $after_load_product_types = OSC::model('catalog/productType')
                    ->getCollection()
                    ->load($after_load_product_type_ids);

                foreach ($after_load_product_types as $item) {
                    $this->_list_product_type->addItem($item);
                }
            }

            OSC::core('cache')->set($cache_key, $this->_list_product_type->toArray(), OSC_CACHE_TIME);
        }

        return $this->_list_product_type;
    }

    protected $_list_product_type_description = null;

    /**
     * @throws OSC_Exception_Runtime
     */
    public function getPreloadProductTypeDescription($product_type_description_ids) {
        $str_product_type_description_ids = is_array($product_type_description_ids) ?
            implode(',', $product_type_description_ids) :
            $product_type_description_ids;

        $this->_list_product_type_description = OSC::model('catalog/productTypeDescription')->getCollection();

        $cache_key = "getPreloadProductTypeDescription|helper.catalog.campaign|product_type_description_ids:,{$str_product_type_description_ids},|";
        $cache = OSC::core('cache')->get($cache_key);
        if ($cache !== false) {
            foreach ($cache as $item) {
                $model = OSC::model('catalog/productTypeDescription')->bind($item);
                $this->_list_product_type_description->addItem($model);
            }
        } else {
            $this->_list_product_type_description = OSC::model('catalog/productTypeDescription')
                ->getCollection()
                ->load($product_type_description_ids);

            OSC::core('cache')->set($cache_key, $this->_list_product_type_description->toArray(), OSC_CACHE_TIME);
        }

        return $this->_list_product_type_description;
    }

    public function convertPrintTemplate($segments, $print_template_id_old, $print_template_id_new) {
        try {
            if(count($segments) < 1) {
                throw new Exception('Segments source is empty');
            }

            $print_template_old = OSC::model('catalog/printTemplate')->load($print_template_id_old);
            $print_template_new = OSC::model('catalog/printTemplate')->load($print_template_id_new);

            if(count($print_template_old->data['config']['segments']) != count($print_template_new->data['config']['segments'])) {
                throw new Exception('Segments counter is not matched between #' . $print_template_id_old . ' and #' . $print_template_id_new);
            }

            $from_config = [];
            $to_config = [];

            foreach($print_template_old->data['config']['segments'] as $k => $v) {
                if(! isset($print_template_new->data['config']['segments'][$k])) {
                    throw new Exception('Missing segment #' . $k);
                }

                if(! isset($segments[$k])) {
                    continue;
                }

                $from_config[$k] = $v;
                $to_config[$k] = $print_template_new->data['config']['segments'][$k];

                $from_config[$k]['safe_box'] = $from_config[$k]['builder_config']['safe_box'];
                $to_config[$k]['safe_box'] = $to_config[$k]['builder_config']['safe_box'];

                if(! isset($from_config[$k]['safe_box']) ||! $from_config[$k]['safe_box']) {
                    $from_config[$k]['safe_box'] = [
                        'dimension' => $from_config[$k]['dimension'],
                        'position' => ['x' => 0, 'y' => 0]
                    ];
                } else {
                    $from_config[$k]['safe_box']['position']['x'] -= $from_config[$k]['builder_config']['segment_place_config']['position']['x'];
                    $from_config[$k]['safe_box']['position']['y'] -= $from_config[$k]['builder_config']['segment_place_config']['position']['y'];

                    $ratio_x = $from_config[$k]['dimension']['width']/$from_config[$k]['builder_config']['segment_place_config']['dimension']['width'];
                    $ratio_y = $from_config[$k]['dimension']['height']/$from_config[$k]['builder_config']['segment_place_config']['dimension']['height'];

                    $ratio = $ratio_x > $ratio_y ? $ratio_y : $ratio_x;

                    $from_config[$k]['safe_box']['dimension']['width'] *= $ratio;
                    $from_config[$k]['safe_box']['dimension']['height'] *= $ratio;
                    $from_config[$k]['safe_box']['position']['x'] *= $ratio;
                    $from_config[$k]['safe_box']['position']['y'] *= $ratio;
                }

                if(! isset($to_config[$k]['safe_box']) ||! $to_config[$k]['safe_box']) {
                    $to_config[$k]['safe_box'] = [
                        'dimension' => $to_config[$k]['dimension'],
                        'position' => ['x' => 0, 'y' => 0]
                    ];
                } else {
                    $to_config[$k]['safe_box']['position']['x'] -= $to_config[$k]['builder_config']['segment_place_config']['position']['x'];
                    $to_config[$k]['safe_box']['position']['y'] -= $to_config[$k]['builder_config']['segment_place_config']['position']['y'];

                    $ratio_x = $to_config[$k]['dimension']['width']/$to_config[$k]['builder_config']['segment_place_config']['dimension']['width'];
                    $ratio_y = $to_config[$k]['dimension']['height']/$to_config[$k]['builder_config']['segment_place_config']['dimension']['height'];

                    $ratio = $ratio_x > $ratio_y ? $ratio_y : $ratio_x;

                    $to_config[$k]['safe_box']['dimension']['width'] *= $ratio;
                    $to_config[$k]['safe_box']['dimension']['height'] *= $ratio;
                    $to_config[$k]['safe_box']['position']['x'] *= $ratio;
                    $to_config[$k]['safe_box']['position']['y'] *= $ratio;
                }
            }

            foreach($segments as $k => $v) {
                $safe_box_ratio_x = $to_config[$k]['safe_box']['dimension']['width']/$from_config[$k]['safe_box']['dimension']['width'];
                $safe_box_ratio_y = $to_config[$k]['safe_box']['dimension']['height']/$from_config[$k]['safe_box']['dimension']['height'];

                $safe_box_ratio = $safe_box_ratio_x > $safe_box_ratio_y ? $safe_box_ratio_y : $safe_box_ratio_x;

                $from_config[$k]['dimension']['width'] *= $safe_box_ratio;
                $from_config[$k]['dimension']['height'] *= $safe_box_ratio;
                $from_config[$k]['safe_box']['dimension']['width'] *= $safe_box_ratio;
                $from_config[$k]['safe_box']['dimension']['height'] *= $safe_box_ratio;
                $from_config[$k]['safe_box']['position']['x'] *= $safe_box_ratio;
                $from_config[$k]['safe_box']['position']['y'] *= $safe_box_ratio;

                $segments[$k]['source']['dimension']['width'] *= $safe_box_ratio;
                $segments[$k]['source']['dimension']['height'] *= $safe_box_ratio;
                $segments[$k]['source']['position']['x'] *= $safe_box_ratio;
                $segments[$k]['source']['position']['y'] *= $safe_box_ratio;

                $segments[$k]['source']['position']['x'] += $to_config[$k]['safe_box']['position']['x'] - $from_config[$k]['safe_box']['position']['x'] + ($to_config[$k]['safe_box']['dimension']['width']-$from_config[$k]['safe_box']['dimension']['width'])/2;
                $segments[$k]['source']['position']['y'] += $to_config[$k]['safe_box']['position']['y'] - $from_config[$k]['safe_box']['position']['y'] + ($to_config[$k]['safe_box']['dimension']['height']-$from_config[$k]['safe_box']['dimension']['height'])/2;
            }

            return $segments;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getVariantsPrintTemplatesMap(Model_Catalog_Product $product, $reload_cache = false) {
        $variants = $product->getVariants($reload_cache);

        $printTemplatesVariantsMap = [];

        foreach ($variants as $variant) {
            $productTypeVariant = $variant->getProductTypeVariant();

            $printTemplatesRel = $productTypeVariant->getPrintsTemplate();

            foreach ($printTemplatesRel as $printTemplateRel) {
                $printTemplatesVariantsMap[] = [
                    'variant_id' => $variant->getId(),
                    'print_template_id' => $printTemplateRel->data['print_template_id']
                ];
            }
        }

        $printTemplatesConfig = $product->getPrintTemplates();

        $result = [];

        foreach ($printTemplatesVariantsMap as $template_map_variant) {
            if (in_array($template_map_variant['print_template_id'],$printTemplatesConfig)) {
                $result[$template_map_variant['variant_id'] . '_' . $template_map_variant['print_template_id']] = [
                    'variant_id' => $template_map_variant['variant_id'],
                    'print_template_id' => $template_map_variant['print_template_id']
                ];
            }
        }

        return array_values($result);
    }

    /**
     * Get variant print template map
     *
     * @param $product
     * @param int $variant_id
     * @throws Exception
     * @return array
     * */

    public function getVariantPrintTemplatesMap(Model_Catalog_Product $product, $variant_id) {
        try {
            $variant = OSC::model('catalog/product_variant')->load($variant_id);

            if ($variant->data['product_id'] != $product->getId()) {
                return [];
            }

            $product_type_variant = $variant->getProductTypeVariant();

            $print_templates_rel = $product_type_variant->getPrintsTemplate();

            $print_templates_variants_map = [];

            foreach ($print_templates_rel as $print_templates) {
                $print_templates_variants_map[] = [
                    'variant_id' => $variant->getId(),
                    'print_template_id' => $print_templates->data['print_template_id']
                ];
            }


            $print_templates_config_in_product = $product->getPrintTemplates();

            $result = [];

            foreach ($print_templates_variants_map as $template_map_variant) {
                if (in_array($template_map_variant['print_template_id'], $print_templates_config_in_product)) {
                    $result[$template_map_variant['variant_id'] . '_' . $template_map_variant['print_template_id']] = [
                        'variant_id' => $template_map_variant['variant_id'],
                        'print_template_id' => $template_map_variant['print_template_id']
                    ];
                }
            }

            return $result;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function prepareCampaignConfig(Model_Catalog_Product $product) {
        if (!$product->isCampaignMode() || !$product->data['meta_data']['campaign_config']['print_template_config']) {
            return [];
        }

        $image_ids = [];
        $campaign_config = $product->data['meta_data']['campaign_config']['print_template_config'];

        foreach ($campaign_config as $print_template_config) {
            $campaign_segments = $print_template_config['segments'] ?? [];

            if (!empty($campaign_segments)) {
                foreach ($campaign_segments as $design_key => $design_data) {
                    switch ($design_data['source']['type']) {
                        case 'image':
                            if (!in_array($design_data['source']['image_id'], $image_ids)) {
                                $image_ids[] = $design_data['source']['image_id'];
                            }

                            break;
                        default:
                            break;
                    }
                }
            }
        }

        if (count($image_ids) > 0) {
            try {
                $image_collection = OSC::model('catalog/campaign_imageLib_item')->getCollection()->load($image_ids);

                foreach ($campaign_config as &$print_template_config) {
                    if (!empty($print_template_config['segments'])) {
                        foreach ($print_template_config['segments'] as $segment_key => &$segment_source) {
                            switch ($segment_source['source']['type']) {
                                case 'image':
                                    $image = $image_collection->getItemByPK($segment_source['source']['image_id']);

                                    if ($image instanceof Model_Catalog_Campaign_ImageLib_Item) {
                                        $segment_source['source']['url'] = OSC::wrapCDN($image->getFileThumbUrl());
                                    }

                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                }
            } catch (Exception $exception) { }
        }

        return array_values($campaign_config);
    }

    /**
     * @param $productId
     * @return array|bool
     * @throws OSC_Database_Model_Exception
     * @throws OSC_Exception_Runtime
     */
    public function getCustomMockupConfigs($productId, array $appendProductTypeVariants = []) {
        $data = [];
        $product_types = [];

        if ($productId > 0) {
            $product = OSC::model('catalog/product')->load($productId);

            if (!$product->isCampaignMode()) {
                throw new Exception('Support Only Campaign');
            }

            $variants = $product->getVariants();

            if (count($variants) < 1) {
                throw new Exception("This campaign don't have any variant!");
            }

            $options = [];

            foreach ($variants as $variant) {
                $images = [];
                $_images = $variant->getImages();
                foreach ($_images as $image) {
                    if ($image->data['is_static_mockup'] == 2) {
                        $images[] = ['id' => $image->getId(), 'url' => OSC::core('aws_s3')->getStorageUrl($image->data['filename'])];
                    }
                }

                $product_type_variant = $variant->getProductTypeVariant();

                if (!($product_type_variant instanceof Model_Catalog_ProductType_Variant)) {
                    throw new Exception("Load product type variants error!");
                }

                $option = [];

                foreach ($product_type_variant->getOptionValues()['items'] as $_option) {
                    $options[strtolower($_option['title'])] = $_option['title'];
                    $option[strtolower($_option['title'])] = ['value' => $_option['value'], 'meta_data' => $_option['meta_data']];
                }

                array_push($product_types, $product_type_variant->data['product_type_id']);

                $variant_videos = $variant->getVideos()->toArray();
                $video_positions = $variant->data['meta_data']['video_config']['position'] ?? [];
                $variant_videos = array_map(function($video) use($video_positions) {
                    return [
                        'id' => $video['id'],
                        'url' => $video['url'],
                        'thumbnail' => $video['thumbnail'],
                        'duration' => $video['duration'],
                        'position' => $video_positions[$video['id']] ?? 1,
                    ];
                }, $variant_videos);

                $data[$variant->data['product_type_variant_id']] = [
                    'product_type_id' => $product_type_variant->data['product_type_id'],
                    'data' => [
                        'title' => $product_type_variant->data['title'],
                        'images' => $images,
                        'videos' => $variant_videos,
                        'option' => $option,
                        'product_type_variant_id' => $variant->data['product_type_variant_id']
                    ]
                ];
            }
        }

        if (!empty($appendProductTypeVariants)) {
            if (count($data) > 0) {
                foreach ($appendProductTypeVariants as $item) {
                    if (in_array($item, array_keys($data))) {
                        unset($appendProductTypeVariants[$item]);
                    }
                }
            }

            $productTypeVariantCollection = OSC::model('catalog/productType_variant')->getCollection()->load($appendProductTypeVariants);
            foreach ($productTypeVariantCollection as $product_type_variant) {
                $option = [];
                foreach ($product_type_variant->getOptionValues()['items'] as $_option) {
                    $options[strtolower($_option['title'])] = $_option['title'];
                    $option[strtolower($_option['title'])] = ['value' => $_option['value'], 'meta_data' => $_option['meta_data']];
                }

                array_push($product_types, $product_type_variant->data['product_type_id']);

                $data[$product_type_variant->data['id']] = [
                    'product_type_id' => $product_type_variant->data['product_type_id'],
                    'data' => [
                        'title' => $product_type_variant->data['title'],
                        'images' => [],
                        'option' => $option,
                        'product_type_variant_id' => $product_type_variant->data['id']
                    ]
                ];
            }
        }

        if (count($data) < 1) {
            throw new Exception("Campaign data error!");
        }

        $collection_product_type = OSC::model('catalog/productType')->getCollection()->addField('id', 'ukey', 'title', 'product_type_option_ids')->setLimit(count(array_unique($product_types)))->load(array_unique($product_types));

        if ($collection_product_type->length() < 1) {
            throw new Exception("Campaign data error!");
        }

        $result = [];

        foreach ($collection_product_type as $product_type) {
            $product_type_title = $product_type->data['title'];
            $product_type_key = $product_type->data['ukey'];

            $variants = [];
            $map = [];
            $map['auto_options'] = $this->getAutoSelectOptionData($product_type);

            foreach ($data as $product_type_variant_id => $_data) {
                if ($_data['product_type_id'] == $product_type->getId()) {
                    $variants[$_data['data']['product_type_variant_id']] = $_data['data'];
                }
            }

            $map['product_type_title'] = $product_type_title;
            $map['key'] = $product_type_key;
            $map['option_title'] = array_unique($options);
            $map['variants'] = $variants;
            $map['selected_all'] = [];

            $result[$product_type_key] = $map;
        }

        return $result;
    }

    public function getAutoSelectOptionData(Model_Catalog_ProductType $product_type) {
        $result = [];
        $optionIds = [];
        foreach (explode(',', $product_type->data['product_type_option_ids']) as $option_type_id) {
            $optionIds[] = intval($option_type_id);
        }

        $manualOption = [];
        $hasAutoSelectOption = false;

        $optionData = [];

        $optionCollection = OSC::model('catalog/productType_option')
            ->getCollection()
            ->load($optionIds);
        foreach ($optionCollection as $option) {
            $optionData[$option->getId()] = [
                'id' => $option->getId(),
                'title' => $option->data['title']
            ];

            if ($option->data['auto_select'] === 1) {
                $hasAutoSelectOption = true;
            } else {
                $manualOption[] = $option->getId();
            }
        }

        if ($hasAutoSelectOption && count($manualOption) > 0) {
            $productTypeVatiantCollection = OSC::model('catalog/productType_variant')
                ->getCollection()
                ->addField('id' ,'ukey', 'product_type_id')
                ->addCondition('status', 1)
                ->addCondition('product_type_id', $product_type->getId())
                ->load()
                ->toArray();

            foreach ($productTypeVatiantCollection as $variant) {
                $_manual = [];
                $_auto = [];
                foreach (explode('_', explode('/', $variant['ukey'])[1]) as $optionTypeValuePair) {
                    $option_value = explode(':', $optionTypeValuePair);
                    if (in_array($option_value[0], $manualOption)) {
                        $_optionValue = OSC::model('catalog/productType_optionValue')->load($option_value[1]);

                        $_manual['key'][] = $option_value[0] . '_' . $option_value[1];
                        $_manual['title'][] = $optionData[$option_value[0]]['title'] . ' ' . $_optionValue->data['title'];
                    }
                }

                if (count($_manual) > 0) {
                    $_manualKey = implode('_', $_manual['key']);
                    $_manualTitle = implode(' - ', $_manual['title']);
                }
                $result[$_manualKey]['title'] = $_manualTitle;
                $result[$_manualKey]['variants'][] = $variant['id'];
            }
        }

        return $result;
    }

    protected $_preload_product_types = null;
    /**
     * @throws OSC_Exception_Runtime
     */
    public function getPreloadProductTypes($product_type_ids = []) {
        if ($this->_preload_product_types === null) {
            $this->_preload_product_types = [];

            if (count($product_type_ids) < 1) {
                return $this->_preload_product_types;
            }

            $collection = OSC::model('catalog/productType')->getCollection()->load($product_type_ids);
            foreach ($collection as $product_type) {
                $this->_preload_product_types[$product_type->getId()] = $product_type->data;
            }
        }

        return $this->_preload_product_types;
    }

    protected $_preload_product_type_ids = null;

    public function getPreloadProductTypeIds(Model_Catalog_Product $product) {
        if ($this->_preload_product_type_ids === null) {
            $this->_preload_product_type_ids = [];

            if (!$product->data['product_type']) {
                return $this->_preload_product_type_ids;
            }

            $product_types = array_map(function($item) {
                return trim($item);
            }, explode(',', $product->data['product_type']));

            if (count($product_types) === 0) {
                return $this->_preload_product_type_ids;
            }

            $collection = OSC::model('catalog/productType')->getCollection()->loadByUkey($product_types);
            foreach ($collection as $product_type) {
                $this->_preload_product_type_ids[] = $product_type->getId();
            }
        }

        return $this->_preload_product_type_ids;
    }

    public function setPreloadProductTypes($preload_product_types) {
        foreach ($preload_product_types as $product_type) {
            $this->_preload_product_types[$product_type->getId()] = $product_type->data;
        }

        return $this;
    }

    protected $_preload_product_type_variants = null;
    /**
     * @throws OSC_Exception_Runtime
     */
    public function getPreloadProductTypeVariants($product_type_variant_ids = []) {
        if ($this->_preload_product_type_variants === null) {
            $this->_preload_product_type_variants = [];

            if (count($product_type_variant_ids) < 1) {
                return $this->_preload_product_type_variants;
            }

            $collection = OSC::model('catalog/productType_variant')->getCollection()->load($product_type_variant_ids);
            foreach ($collection as $product_type_variant) {
                $this->_preload_product_type_variants[$product_type_variant->getId()] = $product_type_variant->data;
            }
        }

        return $this->_preload_product_type_variants;
    }

    public function setPreloadProductTypeVariants($preload_product_type_variants) {
        foreach ($preload_product_type_variants as $product_type_variant) {
            $this->_preload_product_type_variants[$product_type_variant->getId()] = $product_type_variant->data;
        }

        return $this;
    }

    protected $_preload_upload_mode = null;
    /**
     */
    public function getPreloadUploadMode() {
        return $this->_preload_upload_mode;
    }

    public function setPreloadUploadMode($preload_upload_mode) {
        foreach ($preload_upload_mode as $key => $value) {
            $this->_preload_upload_mode[$key] = $value;
        }

        return $this;
    }

    public function updateDesign(Model_Catalog_Order_Item $line_item, $campaign_data, $options) {
        $campaign_data_idx = $line_item->getCampaignDataIdx();

        if ($campaign_data_idx === null) {
            throw new Exception('The line item is not campaign');
        }

        if (!is_array($options)) {
            $options = [];
        }

        $modifier = isset($options['modifier']) ? $options['modifier'] : null;

        $log_data = [];

        foreach ($campaign_data['data']['print_template']['segment_source'] as $segment_source) {
            if ($segment_source['source']['type'] != 'personalizedDesign') {
                continue;
            }

            $log_data[$segment_source['source']['design_id']] = ['change' => [], 'add' => [], 'remove' => []];
        }

        $campaign_data['data']['design_timestamp'] = time();

        $order_item_meta = $line_item->getOrderItemMeta();

        $custom_data_entries = $order_item_meta->data['custom_data'];

        $custom_data_entries[$campaign_data_idx] = $campaign_data;

        if (isset($options['print_template_id_new']) && intval($options['print_template_id_new']) > 0) {
            //changed product type: print_template_id = $options['print_template_id_new'];
            $print_template_id = intval($options['print_template_id_new']);
        } else {
            $print_template_id = array_key_first($line_item->data['design_url']);
        }

        try {
            $line_item->setData(['design_url' => []])->save();

            $order_item_meta->setData(['custom_data' => $custom_data_entries])->save();

            if ($modifier && $options['skip_log'] == null) {
                $message_log = isset($options['type']) && $options['type'] == 'rerender_design' ? 'rerender design' : 'edit design personalized';

                $type_log = isset($options['type']) && $options['type'] == 'rerender_design' ? 'RERENDER_DESIGN_PERSONALIZED' : 'EDIT_DESIGN_PERSONALIZED';

                $line_item->getOrder()->addLog($type_log, ($modifier ? trim($modifier) : ucfirst(OSC::helper('user/authentication')->getMember()->data['username'])) . ' : ' . $message_log, $log_data);
            }

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        try {
            if ($print_template_id > 0 and $campaign_data['print_template']['print_template_id'] != $print_template_id) {
                $this->renderDesignUrlByTemplateId($line_item, $print_template_id);
            } else {
                $this->campaignRerenderDesign($line_item);
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function renderDesignUrlByTemplateId(Model_Catalog_Order_Item $line_item, $print_template_id_new, $prints_template_id_old = 0) {
        try {
            $campaign_data_idx = $line_item->getCampaignDataIdx();

            if ($campaign_data_idx === null) {
                throw new Exception('Unable to get campaign data idx');
            }

            try {
                $print_template_new = OSC::model('catalog/printTemplate')->load($print_template_id_new);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            $order_item_meta = $line_item->getOrderItemMeta();

            $order_item_meta_data = $order_item_meta->data;

            $campaign_data = $order_item_meta_data['custom_data'][$campaign_data_idx];

            if ($prints_template_id_old > 0) {
                try {
                    OSC::model('catalog/printTemplate')->load($prints_template_id_old);
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }
            } else {
                $prints_template_id_old = intval($campaign_data['data']['print_template']['print_template_id']);
            }

            try {
                $campaign_data = $this->getDataCampaignByTemplate($line_item->data['product_id'], $print_template_id_new, $prints_template_id_old, $campaign_data);
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                } else {
                    try {
                        /* @var $DB OSC_Database_Adapter */
                        $DB = OSC::core('database')->getWriteAdapter();
                        $DB->select('*', 'print_template_map', "print_template_id = '{$prints_template_id_old}' and print_template_map_id = {$print_template_id_new}", 'id ASC', 1, 'fetch_print_template');

                        $row = $DB->fetchArray('fetch_print_template');

                        $DB->free('fetch_print_template');

                        if (!$row) {
                            $DB->select('*', 'print_template_map', "print_template_id = '{$print_template_id_new}' and print_template_map_id = {$prints_template_id_old}", 'id ASC', 1, 'fetch_print_template_map');

                            $row_map = $DB->fetchArray('fetch_print_template_map');

                            $DB->free('fetch_print_template_map');
                            if (!$row_map) {
                                throw new Exception('Print template not matched');
                            }
                        }

                        $campaign_data['data']['print_template'] = [
                            'print_template_id' => $print_template_id_new,
                            'preview_config' => $print_template_new->data['config']['preview_config'],
                            'segments' => $print_template_new->data['config']['segments'],
                            'segment_source' => $this->convertPrintTemplate($campaign_data['data']['print_template']['segment_source'], $prints_template_id_old, $print_template_id_new),
                            'print_file' => $print_template_new->data['config']['print_file']
                        ];

                    } catch (Exception $exception) {
                        throw new Exception($exception->getMessage());
                    }
                }

            }

            $order_item_meta_data['custom_data'][$campaign_data_idx] = $campaign_data;
            $order_item_meta->setData(['custom_data' => $order_item_meta_data['custom_data']])->lock();

            $this->campaignRerenderDesign($line_item);

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getDataCampaignByTemplate($product_id, $print_template_id_new, $print_template_id_old, $campaign_data) {
        try {
            if ($product_id < 1 || $print_template_id_old < 1 || $print_template_id_new < 1 || !is_array($campaign_data)) {
                throw new Exception('Data is incorrect');
            }

            $product = OSC::model('catalog/product')->load($product_id);
            $print_template_new = OSC::model('catalog/printTemplate')->load($print_template_id_new);
            $print_template_old = OSC::model('catalog/printTemplate')->load($print_template_id_old);

            if (count(array_diff_key($print_template_old->data['config']['segments'], $print_template_new->data['config']['segments'])) > 0 || count(array_diff_key($print_template_new->data['config']['segments'], $print_template_old->data['config']['segments'])) > 0) {
                throw new Exception('Not matched key segments between #' . $print_template_id_old . ' and #' . $print_template_id_new);
            }

            $segments = null;
            $segmentsOld = null;
            foreach ($product->data['meta_data']['campaign_config']['print_template_config'] as $print_template_config) {
                if ($print_template_config['print_template_id'] == $print_template_id_old) {
                    $segmentsOld = $print_template_config['segments'];
                }
                if ($print_template_config['print_template_id'] == $print_template_id_new) {
                    $segments = $print_template_config['segments'];
                }
            }

            if ($segmentsOld == null) {
                throw new Exception('Not found print template id ' . $print_template_id_old . ' in product id ' . $product_id);
            }

            if ($segments == null) {
                throw new Exception('Not found print template id ' . $print_template_id_new . ' in product id ' . $product_id);
            }
            $type_segment_old = [];

            foreach ($segmentsOld as $segment_key => $segment) {
                $type_segment_old[$segment_key] = $segment['source'];
            }

            $map_segment = true;

            foreach ($segments as $segment_key => $segment) {
                if (!array_key_exists($segment_key, $type_segment_old) || $type_segment_old[$segment_key]['design_id'] != $segment['source']['design_id']) {
                    $map_segment = false;
                    break;
                }
            }

            if ($map_segment == false) {
                throw new Exception('Not map segment ' . $print_template_id_old . ' and ' . $print_template_id_new . ' in product id ' . $product_id);
            }

            $campaign_config = [];
            $personalized_design_ids = [];
            $image_ids = [];

            foreach ($campaign_data['data']['print_template']['segment_source'] as $segment_key => $segment_source) {
                if ($segment_source['source']['type'] == 'personalizedDesign') {
                    $campaign_config[$segment_key] = $segment_source['source']['config'];

                    $personalized_design_ids[] = $segment_source['source']['design_id'];
                } elseif ($segment_source['source']['type'] == 'image') {
                    $image_ids[] = $segment_source['source']['image_id'];
                }
            }
            if (count($personalized_design_ids) > 0) {
                $personalized_design_collection = OSC::model('personalizedDesign/design')->getCollection()->load($personalized_design_ids);
            }

            if (count($image_ids) > 0) {
                $image_collection = OSC::model('catalog/campaign_imageLib_item')->getCollection()->load($image_ids);
            }

            foreach ($segments as $segment_key => $segment_source) {
                $campaign_data['data']['print_template']['segment_source'][$segment_key] = $segment_source;
                if ($segment_source['source']['type'] == 'personalizedDesign') {

                    $personalized_design = $personalized_design_collection->getItemByPK($segment_source['source']['design_id']);

                    if (!($personalized_design instanceof Model_PersonalizedDesign_Design)) {
                        throw new Exception('Cannot load personalized design [' . $segment_source['source']['design_id'] . ']');
                    }

                    $personalizedDesign = Observer_Catalog_Campaign::validatePersonalizedDesign($personalized_design, $campaign_config[$segment_key]);

                    OSC::helper('personalizedDesign/common')->verifyCustomConfig($personalized_design, $campaign_config[$segment_key]);

                    foreach ($personalizedDesign as $k => $v) {
                        $campaign_data['data']['print_template']['segment_source'][$segment_key]['source'][$k] = $v;
                    }
                } elseif ($segment_source['source']['type'] == 'image') {
                    $image = $image_collection->getItemByPK($segment_source['source']['image_id']);

                    if (!($image instanceof Model_Catalog_Campaign_ImageLib_Item)) {
                        throw new Exception('Cannot load image item [' . $segment_source['source']['image_id'] . ']');
                    }

                    $campaign_data['data']['print_template']['segment_source'][$segment_key]['source']['file_name'] = $image->data['filename'];
                }
            }

            $campaign_data['data']['print_template']['print_template_id'] = $print_template_id_new;
            $campaign_data['data']['print_template']['preview_config'] = $print_template_new->data['config']['preview_config'];
            $campaign_data['data']['print_template']['segments'] = $print_template_new->data['config']['segments'];
            $campaign_data['data']['print_template']['print_file'] = $print_template_new->data['config']['print_file'];

            return $campaign_data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), 404);
        }
    }

    public function campaignRerenderDesign(Model_Catalog_Order_Item  $line_item) {
        try {
            OSC::helper('personalizedDesign/common')->checkOverflowPersonalizedItem($line_item);
        } catch (Exception $ex) {

        }

        try {
            Observer_Catalog_Campaign::addRenderDesignQueue($line_item);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function rerenderDesign(Model_Catalog_Order_Item $line_item, $user_name = null){
        $campaign_data_idx = $line_item->getCampaignDataIdx();

        if(!$campaign_data_idx === null) {
            throw new Exception('Data is incorrect');
        }

        $new_config = [];

        foreach ($line_item->getCampaignData()['print_template']['segment_source'] as $key => $segment) {
            if ($segment['source']['type'] != 'personalizedDesign') {
                continue;
            }

            $new_config[$key] = $segment['source']['config'];
        }

        try {
            $campaign_data = $this->orderLineItemVerifyNewDesignData($line_item->getOrderItemMeta()->data['custom_data'][$campaign_data_idx], $new_config, true, $line_item->data['product_id'], true);

            $this->updateDesign($line_item, $campaign_data, ['modifier' => $user_name,'type' => 'rerender_design']);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function campaignRerenderDesignByOrderDesk(Model_Catalog_Order_Item $line_item, $print_template_id_new, $print_template_id_old) {
        try {
            $this->renderDesignUrlByTemplateId($line_item, $print_template_id_new, $print_template_id_old);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function campaignRerenderDesignCrossSellByOrderDesk(Model_Catalog_Order_Item $line_item, $print_template_id_new, $segments) {
        try {
            try {
                $model_sync = OSC::model('personalizedDesign/sync')->loadByUKey('v2campaigndesign/' . $line_item->getId());
                $model_sync->delete();
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }

            OSC::model('personalizedDesign/sync')->setData([
                'ukey' => 'v2campaigndesign/' . $line_item->getId(),
                'sync_type' => 'v2campaigndesign',
                'sync_data' => [
                    'order_id' => $line_item->getOrder()->data['order_id'],
                    'order_code' => $line_item->getOrder()->data['code'],
                    'item_title' => $line_item->data['title'],
                    'item_id' => $line_item->data['item_id'],
                    'product_type' => $line_item->data['product_type'],
                    'product_id' => $line_item->data['product_id'],
                    'member_email' => '',
                    'vendor_email' => '',
                    'print_template_id' => $print_template_id_new,
                    'design_timestamp' => isset($campaign_data['design_timestamp']) ? $campaign_data['design_timestamp'] : $line_item->data['added_timestamp'],
                    'order_added_timestamp' => $line_item->data['added_timestamp'],
                    'design_data' => $segments
                ]
            ])->save();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function validateDataEditDesign(Model_Catalog_Product_Variant $variant, $print_template_id, array $config_option) {
        /* @var $product Model_Catalog_Product */
        $product = $variant->getProduct();

        if (!($product instanceof Model_Catalog_Product)) {
            return null;
        }

        if (!$product->isCampaignMode()) {
            return null;
        }

        if (!isset($config_option) || !is_array($config_option)) {
            throw new Exception('config is error');
        }

        $print_template_id = intval($print_template_id);
        if ($print_template_id < 1) {
            $print_template_id = 0;
        }

        try {
            $collection_supplier_rel = OSC::model("catalog/supplierVariantRel")->getCollection()->getSuppliersByProductTypeAndPrintTemplate($variant->data['meta_data']['campaign_config']['product_type_variant_id'], $print_template_id);
            if ($collection_supplier_rel->length() == 0) {
                throw new Exception("Some option has not been chosen. Please go back and select all of the required options");
            }
        } catch (Exception $ex) {
            throw new Exception("Some option has not been chosen. Please go back and select all of the required options");
        }

        try {
            $print_template = OSC::model('catalog/printTemplate')->load($print_template_id);
        } catch (Exception $exception) {
            throw new Exception('No print template found');
        }

        try {
            $product_type_variants = OSC::model('catalog/productType_variant')->getCollection()->load([$variant->data['meta_data']['campaign_config']['product_type_variant_id']]);
            $product_type_variants->preLoadProductTypes();

            /* @var $product_type_variant Model_Catalog_ProductType_Variant */
            $product_type_variant = $product_type_variants->first();
            $product_type = $product_type_variant->getProductType();

            //Parse product_type_variant ukey to string like "mug_color:white|mug_size:11oz"
            $options = $product_type_variant->getOptionValues();
        } catch (Exception $exception) {
            throw new Exception('No product type variant found');
        }

        $campaign_data = [
            'product_type_variant_id' => $variant->data['meta_data']['campaign_config']['product_type_variant_id'],
            'product_type' => [
                'title' => $product_type_variant->data['title'],
                'ukey' => $product_type->data['ukey'],
                'options' => $options
            ],
            'print_template' => [
                'print_template_id' => $print_template_id,
                'preview_config' => $print_template->data['config']['preview_config'],
                'segments' => $print_template->data['config']['segments'],
                'segment_source' => [],
                'print_file' => $print_template->data['config']['print_file']
            ]
        ];

        foreach($campaign_data['print_template']['segments'] as $segment_key => $segment) {
            unset($campaign_data['print_template']['segments'][$segment_key]['builder_config']);
        }

        $personalized_design_ids = [];

        $campaign_segments = $product->data['meta_data']['campaign_config']['print_template_config'][array_search($print_template_id, array_column($product->data['meta_data']['campaign_config']['print_template_config'], 'print_template_id'))];
        $campaign_segments = $campaign_segments['segments'] ?? [];

        if (!empty($campaign_segments)) {
            foreach ($campaign_segments as $design_key => $design_data) {
                switch ($design_data['source']['type']) {
                    case 'personalizedDesign':
                        $personalized_design_ids[] = $design_data['source']['design_id'];
                        break;
                    default:
                        throw new Exception('Type of design is incorrect');
                }
            }
        }

        if (count($personalized_design_ids) > 0) {
            $personalized_design_collection = OSC::helper('catalog/campaign_design')->loadPersonalizedDesigns($personalized_design_ids);
        }

        if (!empty($campaign_segments)) {
            foreach ($campaign_segments as $segment_key => $segment_source) {
                $campaign_data['print_template']['segment_source'][$segment_key] = $segment_source;

                switch ($segment_source['source']['type']) {
                    case 'personalizedDesign':
                        $personalized_design = $personalized_design_collection->getItemByPK($segment_source['source']['design_id']);

                        if (!($personalized_design instanceof Model_PersonalizedDesign_Design)) {
                            throw new Exception('Cannot load personalized design [' . $segment_source['source']['design_id'] . ']');
                        }

                        $personalizedDesign = Observer_Catalog_Campaign::validatePersonalizedDesign($personalized_design, $config_option[$segment_key]);

                        foreach($personalizedDesign as $k => $v) {
                            $campaign_data['print_template']['segment_source'][$segment_key]['source'][$k] = $v;
                        }

                        break;
                    default:
                        throw new Exception('Type of design is incorrect');
                }
            }
        }

        return [
            'key' => 'campaign',
            'data' => $campaign_data
        ];
    }

    public function getDataPrice($line_item) {
        $order = $line_item->getOrder();

        if(!($order instanceof Model_Catalog_Order)) {
            throw new Exception("Error order");
        }

        $additional_data = $line_item->data['additional_data'];

        $cart_item_id = $additional_data['cart_item_id'];

        $shipping_line = $order->data['shipping_line'];

        $price_shipping_line_item = 0;

        foreach ($shipping_line['carrier']['rates'] as $rate) {
            if (is_array($rate['items_shipping_info']) && count($rate['items_shipping_info']) >0) {
                foreach ($rate['items_shipping_info'] as $item_id => $price_shipping) {
                    if ($item_id == $cart_item_id) {
                        $price_shipping_line_item += $price_shipping;
                    }
                }
            }
        }

        $price_base_cost = $line_item->data['price'];
        $quantity = $line_item->data['quantity'];
        $tax_value = $line_item->data['tax_value'];

        return[
            'price_base_cost' => $price_base_cost,
            'quantity' => $quantity,
            'tax_value' => $tax_value,
            'price_shipping_line_item' => $price_shipping_line_item
        ];
    }

    public function getItemBaseCodePrice($variant, $country_code, $pack_data) {
        $prices = $variant->getPriceForCustomer($country_code, false, true);
        $price = $prices['price'] ?? 0;

        if (!is_array($pack_data)) {
            $pack_data = null;
        }

        if ($pack_data != null) {
            $pack_quantity = $pack_data['quantity'];
            $discount_type = $pack_data['discount_type'];

            $discount_price = $discount_type === Model_Catalog_Product_Pack::PERCENTAGE ?
                round($prices['price'] * $pack_quantity * $pack_data['discount_value'] / 100) :
                OSC::helper('catalog/common')->floatToInteger($pack_data['discount_value']);

            $price = $prices['price'] * $pack_quantity - intval($discount_price);
        }

        return $price;
    }

    public function getTaxPercentage($variant, $country_code = '', $province_code = '') {
        if (!($variant instanceof Model_Catalog_Product_Variant) || !isset($variant)) {
            throw new Exception(' varaint error');
        }

        $product = $variant->getProduct();
        $product_type = $variant->getProductType();
        $product_type_id = $product->isCampaignMode() ? $product_type->getId() : 0;

        $tax_percentage = OSC::helper('core/common')->getTaxValueByLocation(
            $product_type_id,
            $country_code,
            $province_code
        );

        return $tax_percentage ?? 0;
    }

    public function getShipping($variant, $country_code, $province_code, $pack_data, $line_item, $shipping) {
        $product_type_variant = $variant->getProductTypeVariant();

        try {
            $country = OSC::helper('core/country')->getCountry($country_code);

            if ($country->getId() < 1) {
                throw new Exception('Not found country');
            }

            $country_id = 'c' . $country->getId();

            try {
                $province = OSC::helper('core/country')->getCountryProvince($country_code, $province_code);
                $province_id = 'p' . $province->getId();
            } catch (Exception $ex) {
                $province_id = '*';
            }
        } catch (Exception $ex) {
            $country_id = '*';
            $province_id = '*';
        }

        $product_type_variant_ids = [$product_type_variant->getId()];
        $product_type_ids = [$product_type_variant->data['product_type_id']];

        $rate_setting_data = OSC::model('shipping/rate')->getRatePriceByLocationData($product_type_ids, $product_type_variant_ids, $country_id, $province_id);

        $delivery_setting_data = OSC::model('shipping/deliveryTime')->getDeliveryTimeByLocationData($product_type_ids, $product_type_variant_ids, $country_id, $province_id);

        $pack_setting_data = OSC::model('shipping/pack')->getShippingPackByLocationData($product_type_ids, $product_type_variant_ids, $country_id, $province_id);

        $shipping_data = OSC::helper('shipping/common')->groupSettingShipping($rate_setting_data, $delivery_setting_data, $pack_setting_data, $country_id, $province_id);

        $grouped_quantity_pack = [];
        $grouped_quantity = [];
        $grouped_delivery = [];

        if (is_array($pack_data) && $pack_data['id'] !== 0 && count($pack_data['shipping_values']) !== 0) {
            $grouped_quantity_pack[$product_type_variant->getId() . '_' . 0] = [
                'quantity' => $line_item->data['quantity'],
                'product_type_id' => $product_type_variant->data['product_type_id'],
                'tax_value' => $line_item->data['tax_value'],
                'pack_key' => 'pack' . $pack_data['quantity'],
                'item_id' => 0
            ];
        } else {
            $grouped_quantity[$product_type_variant->getId() . '_' . 0] = [
                'quantity' => $line_item->data['quantity'],
                'product_type_id' => $product_type_variant->data['product_type_id'],
                'tax_value' => $line_item->data['tax_value'],
                'item_id' => 0
            ];
        }

        $grouped_delivery[$product_type_variant->getId() . '_' . 0] = [
            'quantity' => $line_item->data['quantity'],
            'product_type_id' => $product_type_variant->data['product_type_id'],
            'item_id' => 0
        ];

        $rates = OSC::helper('shipping/common')->calculateRates($shipping_data, $grouped_quantity_pack, $grouped_quantity, $grouped_delivery, $country_id, $province_id, null, false);

        /* shipping key system old */
        if ($shipping['shipping_key'] == 'standard') {
            $shipping_default = OSC::model('shipping/methods')->getCollection()->getShippingMethodDefault();

            if (!$shipping_default) {
                return 0;
            }

            $shipping['shipping_key'] = $shipping_default->data['shipping_key'];
        }

        $rate = null;

        foreach ($rates as $_rate) {
            if ($_rate['key'] == $shipping['shipping_key']) {
                $rate = $_rate;
            }
        }

        if (!$rate) {
            throw new Exception("The shipping method " . $shipping['shipping_name'] . " does not support " . $product_type_variant->data['title']);
        }

        return $rate['amount'] ?? 0;
    }

    public function getDataEditDesignChangeProductType($variant_id, $print_template_id, $line_item, $new_config) {
        if (!isset($line_item) || !($line_item instanceof Model_Catalog_Order_Item)) {
            throw new Exception("line item is not found");
        }

        $order = $line_item->getOrder();

        $shipping_key = $order->getShippingMethodKey();

        $shipping_name = $order->getShippingMethodTitle();

        $country_code = $order->data['shipping_country_code'];

        $province_code = $order->data['shipping_province_code'];

        $variant_id_old = $line_item->data['variant_id'];

        //change custom data item and set price
        try {
            $collection_variant = OSC::model('catalog/product_variant')->getCollection()->load([$variant_id, $variant_id_old]);

            $variant = $collection_variant->getItemByPK($variant_id);

            $variant_old = $collection_variant->getItemByPK($variant_id_old);

            if (!isset($variant) || !($variant instanceof Model_Catalog_Product_Variant)) {
                throw new Exception('', 404);
            }

            $product_type_variant_new = $variant->getProductTypeVariant();

            $product_type_variant_old = null;

            if (isset($variant_old) && ($variant_old instanceof Model_Catalog_Product_Variant)) {
                $product_type_variant_old = $variant_old->getProductTypeVariant();
            }

        } catch (Exception $ex) {
            throw new Exception($ex->getCode() == 404 ? 'The product is not exists or has been deleted' : 'We got an error in processing your request, please try again later.');
        }

        unset($new_config['variant_id']);
        unset($new_config['print_template_id']);

        //get custom data new with variant new and option new
        $custom_data = $this->validateDataEditDesign($variant, $print_template_id, $new_config);

        if (!isset($custom_data['key'])) {
            throw new Exception('We got an error in processing your request, please try again later');
        }

        // get data price item old
        $data_price_item_old = $this->getDataPrice($line_item);

        //pack data old
        $pack_data = $line_item->getPackData();

        $pack_data_new = null;

        if ($pack_data != null) {
            if ($variant->getProductType()->getProductPacks()->length() < 1) {
                throw new Exception('new product type does not support pack');
            }

            $flag_regular = false;

            if ($pack_data['quantity'] === 1) {
                $pack_data_new = [
                    'id' => 0,
                    'title' => 'Pack 1',
                    'quantity' => 1,
                    'discount_type' => 0,
                    'discount_value' => 0,
                    'marketing_point_rate' => 10000,
                    'note' => ''
                ];

                $flag_regular = true;
            } else{
                foreach ($variant->getProductType()->getProductPacks() as $pack) {
                    if ($pack->data['quantity'] == $pack_data['quantity']) {
                        $pack_data_new = [
                            'id' => $pack->getId(),
                            'title' => $pack->data['title'],
                            'quantity' => $pack->data['quantity'],
                            'discount_type' => $pack->data['discount_type'],
                            'discount_value' => $pack->data['discount_value'],
                            'marketing_point_rate' => OSC::helper('catalog/common')->floatToInteger(floatval($pack->data['marketing_point_rate'])),
                            'shipping_values' => []
                        ];

                        $flag_regular = true;
                    }
                }
            }

            if ($flag_regular == false) {
                throw new Exception('The new product type has the same pack data as the old product type');
            }
        }

        // get data price item new
        $item_base_cost_price_new = $this->getItemBaseCodePrice($variant, $country_code, $pack_data_new);
        $tax_value_new = $this->getTaxPercentage($variant, $country_code, $province_code);
        $shipping_price_new = $this->getShipping($variant, $country_code, $province_code, $pack_data_new, $line_item, ['shipping_key' => $shipping_key, 'shipping_name' => $shipping_name]);

        $discount_code_percent = 0;
        $discount_code_fixed_amount = 0;
        $discount_code_item_flag = false;

        if (is_array($line_item->data['discount']) && count($line_item->data['discount']) > 0) {
            $discount_code_item_flag = true;

            $discount_type = $line_item->data['discount']['discount_type'];
            if ($discount_type == 'fixed_amount') {
                //fixed_amount for item
                $discount_code_fixed_amount = $line_item->data['discount']['discount_price'];
            }
        }

        if (is_array($order->data['discount_codes']) && count($order->data['discount_codes']) > 0) {
            foreach ($order->data['discount_codes'] as $discount_value) {
                if ($discount_value['apply_type'] == 'entire_order' || $discount_code_item_flag == true) {
                    $discount_type = $discount_value['discount_type'];

                    if ($discount_type == 'percent') {
                        $discount_code_percent = $discount_value['discount_value'];
                    }

                    if ($discount_value['apply_type'] == 'entire_order' && $discount_type == 'fixed_amount') {
                        //fixed_amount for order
                        $discount_code_fixed_amount = $discount_value['discount_price'];
                    }
                }
            }
        }

        if ($discount_code_fixed_amount > 0 && $discount_code_percent > 0) {
            throw new Exception('discount error');
        }

        if ($tax_value_new != $data_price_item_old['tax_value']) {
            throw new Exception('New products have different tax than old products');
        }

        return [
            'data_price_item_old' => $data_price_item_old,
            'data_price_item_new' => [
                'price_base_cost' => $item_base_cost_price_new,
                'tax_value' => $tax_value_new,
                'price_shipping_line_item' => $shipping_price_new
            ],
            'config' => $new_config,
            'discount_code' => [
                'percent' => $discount_code_percent,
                'type' => $discount_code_item_flag ? 'item' : 'entire_order',
                'fixed_amount' => $discount_code_fixed_amount,
            ],
            'pack_data' => $pack_data_new == null ? [] : $pack_data_new,
            'product_type_variant_old' => ['id' => $product_type_variant_old != null ? $product_type_variant_old->getId() : 0, 'title' => $product_type_variant_old != null ? $product_type_variant_old->data['title'] : ''],
            'product_type_variant_new' => ['id' => $product_type_variant_new->getId(), 'title' => $product_type_variant_new->data['title']],
        ];
    }

    public function addEditDesignChangeProductType($line_item,$member_id_edit_design, $user_name_edit_design, $data_price_item_old, $data_price_item_new, $variant_id_new, $print_template_id, $config_option, $product_type_variant_old, $product_type_variant_new, $discount_code, $pack_data) {
        try {
            $order = $line_item->getOrder();

            $model = OSC::model('catalog/order_editDesignChangeProductType')->setData([
                    'order_master_record_id' => $order->getId(),
                    'ukey' => OSC::makeUniqid(),
                    'shop_id' => $order->getShop()->getId(),
                    'order_id' => $order->data['order_id'],
                    'order_master_item_id' => $line_item->getId(),
                    'item_id' => $line_item->data['item_id'],
                    'order_code' => $order->data['code'],
                    'member_id_edit_design' => $member_id_edit_design,
                    'member_id_confirm' => 0,
                    'quantity' => $line_item->data['quantity'],
                    'other_quantity' => $line_item->data['other_quantity'],
                    'price_base_cost_old' => $data_price_item_old['price_base_cost'],
                    'price_shipping_old' => $data_price_item_old['price_shipping_line_item'],
                    'tax_value_old' => $data_price_item_old['tax_value'],
                    'price_base_cost_new' => $data_price_item_new['price_base_cost'],
                    'price_shipping_new' => $data_price_item_new['price_shipping_line_item'],
                    'tax_value_new' => $data_price_item_new['tax_value'],
                    'variant_id_new' => $variant_id_new,
                    'print_template_id_new' => $print_template_id,
                    'config_option' => $config_option,
                    'product_type_id_old' => $product_type_variant_old['id'],
                    'product_type_title_old' => $product_type_variant_old['title'],
                    'product_type_id_new' => $product_type_variant_new['id'],
                    'product_type_title_new' => $product_type_variant_new['title'],
                    'discount_code_percent' => intval($discount_code['percent']) > 0 ? intval($discount_code['percent']) : 0,
                    'discount_code_fixed_amount' => intval($discount_code['fixed_amount']) > 0 ? intval($discount_code['fixed_amount']) : 0,
                    'additional_data' => ['user_name_edit_design' => $user_name_edit_design, 'type_discount_code' => $discount_code['type'], 'pack_data' => $pack_data],
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ]
            )->save();

            //save additional_data item
            $additional_data_item = $line_item->data['additional_data'];

            $additional_data_item['edit_design_change_product_type'] = $model->getId();

            $line_item->setData([
                'additional_data' => $additional_data_item
            ])->save();

            //save additional_data order
            $additional_data_order = $order->data['additional_data'];

            if (!is_array($additional_data_order['edit_design_change_product_type']) || count($additional_data_order['edit_design_change_product_type']) < 1) {
                $additional_data_order['edit_design_change_product_type'] = [];
            }

            $additional_data_order['edit_design_change_product_type'][$line_item->getId()] = $line_item->data['quantity'];

            $order->setData([
                'additional_data' => $additional_data_order
            ])->save();

            //hold order where status = unprocess
            if ($order->data['process_status'] == 'unprocess') {
                $order->setData('member_hold', $member_id_edit_design)->save();

                $reasons = 'Edit design and edit product type';

                $order->addLog('HOLD_ORDER', $user_name_edit_design . ': has enabled the hold order status', $reasons ? ['reason_hold' => $reasons] : []);
            }

            return $model->getId();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function createNewDesignDataEditDesign(Model_Catalog_Order_Item $line_item, $variant_id, $print_template_id, $config_option) {
        try {
            $variant_new = OSC::model('catalog/product_variant')->load($variant_id);

            $custom_data = $this->validateDataEditDesign($variant_new, $print_template_id, $config_option);

            if (!isset($custom_data['key'])) {
                throw new Exception('We got an error in processing your request, please try again later');
            }

            $campaign_data = $this->orderLineItemVerifyNewDesignData($custom_data, $config_option, true, $line_item->data['product_id'], true);

            //get campaign_sku_new
            $product = $line_item->getProduct();

            $sku = implode('/', array_filter([
                $product->data['sku'],
                $campaign_data['data']['product_type']['ukey'] ?? '',
                $campaign_data['data']['product_type']['options']['keys'] ?? '',
            ]));

            return [
                'campaign_data' => $campaign_data,
                'sku' => $sku,
                'options' => [['title' => 'Product type', 'value' => $campaign_data['data']['product_type']['title']]],
                'product_type' => $custom_data['data']['product_type']['ukey']
            ];

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function orderLineItemGetDesignEditFrmDataByCart(Model_Catalog_Cart_Item $line_item, $campaign_data) {
        $personalized_design_configs = [];

        $temp_array = [];
        $same_design = false;

        $cart_option_config = null;
        $product_config = null;
        $current_option = null;

        if (isset($campaign_data['product_type_variant_id']) && !empty($campaign_data['product_type_variant_id'])) {
            $product_type_variant = OSC::model('catalog/productType_variant')->load($campaign_data['product_type_variant_id']);
            $current_option['product_type_id'] = $product_type_variant->data['product_type_id'];
        }

        if (isset($campaign_data['product_type']) && !empty($campaign_data['product_type'])) {
            $current_option['product_type'] = $campaign_data['product_type'];
        }

        if (isset($campaign_data['print_template']['segment_source']) && !empty($campaign_data['print_template']['segment_source'])) {
            $print_template = $campaign_data['print_template'] ?? [];

            try {
                $product = OSC::model('catalog/product')->load($line_item->data['product_id']);
            }catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }

            $cart = $line_item->getCart();

            $shipping_country_code = $cart->data['shipping_country_code'];

            if (!isset($shipping_country_code)) {
                $shipping_country_code = '';
            }

            $shipping_province_code = $cart->data['shipping_province_code'];

            if (!isset($shipping_province_code)) {
                $shipping_province_code = '';
            }

            if (isset($product) && $product instanceof Model_Catalog_Product) {
                $product_config = OSC::helper('catalog/campaign')->prepareCampaignConfig($product);
                $cart_option_config = $product->getCartFrmOptionConfig($shipping_country_code, $shipping_province_code, ['skip_auth' => 1]);

                if (!isset($cart_option_config['product_types']) || empty($cart_option_config['product_types'])) {
                    throw new Exception('Product is not exists');
                }

                $current_option['product_id'] = $product->getId();
            }

            $campaign_config = $product->data['meta_data']['campaign_config']['print_template_config'];

            OSC::helper('catalog/campaign')->replaceLayerUrl($print_template['preview_config'], $campaign_data['product_type']['options']['keys']);

            $map_print_template_design_campaign = [];

            if (is_array($campaign_config) && count($campaign_config) > 0) {
                foreach ($campaign_config as $value) {
                    if ($value['print_template_id'] != $print_template['print_template_id']) {
                        continue;
                    }

                    if (is_array($value['segments']) && count($value['segments']) > 0) {
                        foreach ($value['segments'] as $key => $segment_source_campaign) {
                            if ($segment_source_campaign['source']['type'] == 'personalizedDesign' && isset($segment_source_campaign['source']['design_id']) && !empty($segment_source_campaign['source']['design_id'])) {
                                $map_print_template_design_campaign[$key] = $segment_source_campaign['source']['design_id'];
                            }
                        }
                    }
                }
            }

            $segment_sources = $campaign_config[array_search($print_template['print_template_id'], array_column($campaign_config, 'print_template_id'))]['segments'];

            if (is_array($segment_sources) && count($segment_sources) < 1) {
                $segment_sources = [];
                foreach ($print_template['segment_source'] as $key => $segment_sources_item) {
                    unset($segment_sources_item['source']['svg']);
                    unset($segment_sources_item['source']['config']);
                    unset($segment_sources_item['source']['config_preview']);
                    $segment_sources[$key] = $segment_sources_item;
                }
            }

            foreach ($campaign_data['print_template']['segment_source'] as $segment_key => $segment_source) {
                if ($segment_source['source']['type'] == 'personalizedDesign' && isset($segment_source['source']['design_id']) && !empty($segment_source['source']['design_id'])) {
                    $preview_config = [];

                    foreach ($print_template['preview_config'] as $config_item) {
                        if (isset($config_item['config'][$segment_key]) && !empty($config_item['config'][$segment_key])) {
                            $preview_config = $config_item;
                            break;
                        }
                    }

                    $design_id = $segment_source['source']['design_id'];

                    $flag_use_design_in_campaign = true;

                    if ($design_id != $map_print_template_design_campaign[$segment_key] && intval($map_print_template_design_campaign[$segment_key]) > 0) {
                        $design_id = $map_print_template_design_campaign[$segment_key];
                        $flag_use_design_in_campaign = false;
                    }

                    if (array_key_exists($design_id, $personalized_design_configs)) {
                        $same_design = $design_id;
                        $temp_array[$design_id] = [
                            'title' => $preview_config['title'] ?? '',
                            'key' => $segment_key,
                            'personalized_design_id' => $design_id,
                            'config' => $flag_use_design_in_campaign == true ? $segment_source['source']['config'] : [],
                            'document_type' => '',
                            'components' => '',
                            'mockup_config' => [
                                'title' => $preview_config['title'] ?? '',
                                'product_type' => $segment_key,
                                'design_key' => $segment_key,
                                'design' => [
                                    'svg' => ''
                                ],
                                'preview_config' => $preview_config,
                                'segment_configs' => $print_template['segments'] ?? [],
                                'segment_sources' => $segment_sources
                            ]
                        ];
                    } else {
                        $personalized_design_configs[$design_id] = [
                            'title' => $preview_config['title'] ?? '',
                            'key' => $segment_key,
                            'personalized_design_id' => $design_id,
                            'config' => $flag_use_design_in_campaign == true ? $segment_source['source']['config'] : [],
                            'document_type' => '',
                            'components' => '',
                            'mockup_config' => [
                                'title' => $preview_config['title'] ?? '',
                                'product_type' => $segment_key,
                                'design_key' => $segment_key,
                                'design' => [
                                    'svg' => ''
                                ],
                                'preview_config' => $preview_config,
                                'segment_configs' => $print_template['segments'] ?? [],
                                'segment_sources' => $segment_sources
                            ]
                        ];
                    }
                }
            }

            if (count($personalized_design_configs) < 1) {
                throw new Exception('No personalized config was found');
            }

            $design_collection = OSC::model('personalizedDesign/design')->getCollection()->load(array_keys($personalized_design_configs));

            if ($design_collection->length() != count($personalized_design_configs)) {
                throw new Exception('Personalized design is not exists');
            }

            foreach ($design_collection as $design) {
                $form_data = $design->extractPersonalizedFormData();
                $design_id = $design->getId();

                $personalized_design_configs[$design_id]['components'] = $form_data['components'];
                $personalized_design_configs[$design_id]['image_data'] = $form_data['image_data'];
                $personalized_design_configs[$design_id]['document_type'] = $design->data['design_data']['document'];
            }

            if ($same_design) {
                $temp_array[$same_design]['components'] = $personalized_design_configs[$same_design]['components'];
                $temp_array[$same_design]['image_data'] = $personalized_design_configs[$same_design]['image_data'];
                $temp_array[$same_design]['document_type'] = $personalized_design_configs[$same_design]['document_type'];

                $personalized_design_configs[] = $temp_array[$same_design];
            }
        }

        $pack = $line_item->getPackData();
        $pack_data = null;

        if ($pack) {
            $pack_data = $pack;
        }

        $current_option['pack_data'] = $pack_data;

        return [
            'current_option' => $current_option,
            'cart_form_config' => $cart_option_config,
            'product_config' => $product_config,
            'designs' => array_values($personalized_design_configs),
            'flag_show_edit_product_type' => 1
        ];
    }

    public function addQueueRenderDesignBeta(Model_Catalog_Order_Item $line_item, $service = '') {
        $result = [
            'design_ids' => null,
            'message' => 'Design ids is not found',
            'status' => false
        ];

        if (!$line_item->isSemitestMode()) {
            $result['message'] = 'Order is not semitest mode';
            return $result;
        }

        $semitest_data = $line_item->getSemitestData();

        if (!$semitest_data) {
            $result['message'] = 'Semitest data is not found';
            return $result;
        }

        $variant_id = $line_item->data['variant_id'];

        try {
            $variant = OSC::model('catalog/product_variant')->load($variant_id);
        } catch (Exception $ex) {
            $result['message'] = 'Variant is not exist';
            return $result;
        }

        $variant_config = $variant->data['meta_data']['variant_config'];

        if (count($variant_config) < 1) {
            $result['message'] = 'Print template configs are not installed';
            return $result;
        }

        $variant_config_default = null;

        foreach ($variant_config as $_variant_config) {
            if (isset($service) && in_array(trim($service), $_variant_config['supplier'])) {
                $variant_config_default = $_variant_config;
                break;
            }

            if ($_variant_config['is_default'] == true) {
                $variant_config_default = $_variant_config;
            }
        }

        if ($variant_config_default == null) {
            $result['message'] = 'variant config default not found';
            return $result;
        }

        $supplier_available = $variant_config_default['supplier'];

        $design_ids_in_line_item = [];
        $personalized_design_options = [];

        foreach ($semitest_data as $design_id => $design) {
            $design_ids_in_line_item[] = $design_id;
            $personalized_design_options[$design_id] = $design['config'];
        }

        $personalized_design_ids_in_variant_config = [];
        foreach ($variant_config_default['print_template_config'] as $config_id => $config) {
            foreach ($config['segments']['source'] as $source) {
                if ($source['type'] == 'personalizedDesign') {
                    $personalized_design_ids_in_variant_config[] = $source['design_id'];
                }
            }
        }

        $design_id_variant_setting = $variant->data['design_id'];

        foreach ($design_id_variant_setting as $design_id) {
            if (!in_array($design_id, $personalized_design_ids_in_variant_config)) {
                $result['message'] = 'design_ids in variant setting # variant config';
                return $result;
            }
        }

        foreach ($design_id_variant_setting as $design_id) {
            if (!in_array($design_id, $design_ids_in_line_item)) {
                $result['message'] = 'design_ids in variant setting # line item';
                return $result;
            }
        }

        $segment_sources['key'] = 'beta';
        $segment_sources['supplier'] = $supplier_available;
        $print_template_image = [];

        foreach ($variant_config_default['print_template_config'] as $config_id => $config) {
            $_segment = OSC::helper('catalog/campaign_design')->getSegmentSourcesSemitest($config['segments']['source'], $personalized_design_options, ['render_design']);

            if (isset($_segment['message']) || (isset($_segment['status']) && $_segment['status'] == false)) {
                $result['message'] = $_segment['message'];
                return $result;
            }

            $segment_sources['config'][$config_id]['segments'] = $_segment;
            $print_template_image[$config_id] = $config['print_template_beta_id'];
        }

        $print_template_beta_collection = OSC::model('catalog/printTemplate_beta')->getCollection()->load(array_values($print_template_image));

        foreach ($variant_config_default['print_template_config'] as $config_id => $config) {
            $print_template_beta = $print_template_beta_collection->getItemByPK($config['print_template_beta_id']);
            $print_file = $print_template_beta->data['config']['print_file'];
            $print_file['print_file_name'] = $print_template_beta->getId() . '_' . OSC::core('aws_s3')->getLastModifiedFile($print_file['print_file_url']);
            $print_file['print_file_url'] = OSC::core('aws_s3')->getStorageUrl($print_file['print_file_url']);
            $print_file['print_file_url_thumb'] =  OSC::core('aws_s3')->getStorageUrl($print_file['print_file_url_thumb']);
            $segment_sources['config'][$config_id]['print_file'] = $print_file;

            if (isset($variant_config_default['custom_shape']['is_enable']) && $variant_config_default['custom_shape']['is_enable'] == Model_Catalog_Product_Variant::STATE_CUSTOM_SHAPE['ON']) {
                $segment_sources['config'][$config_id]['custom_shape'] = $variant_config_default['custom_shape'];
            }
        }

        try {
            $model_sync = OSC::model('personalizedDesign/sync')->loadByUKey('v2campaigndesignBeta/' . $line_item->getId());
            $model_sync->delete();
        } catch (Exception $ex) {
            if ($ex->getCode() != 404) {
                throw new Exception($ex->getMessage());
            }
        }

        $line_item->setData(['design_url' => []])->save();

        $member_data = $this->getVendorAndMemberInLineItem($line_item);

        OSC::model('personalizedDesign/sync')->setData([
            'ukey' => 'v2campaigndesignBeta/' . $line_item->getId(),
            'sync_type' => 'v2campaigndesign',
            'sync_data' => [
                'order_id' => $line_item->getOrder()->data['order_id'],
                'order_code' => $line_item->getOrder()->data['code'],
                'item_title' => $line_item->data['title'],
                'item_id' => $line_item->data['item_id'],
                'product_type' => 0,
                'product_id' => $line_item->data['product_id'],
                'member_email' => $member_data['member_email'],
                'vendor_email' => $member_data['vendor_email'],
                'print_template_id' => 0,
                'design_timestamp' => $line_item->data['added_timestamp'],
                'order_added_timestamp' => $line_item->data['added_timestamp'],
                'design_data' => $segment_sources
            ]
        ])->save();

        $result['design_ids'] = $design_ids_in_line_item;
        $result['status'] = true;

        return $result;
    }

    public function getVendorAndMemberInLineItem(Model_Catalog_Order_Item $line_item) {
        $vendor_email = '';
        $member_email = '';
        try {
            $product = $line_item->getProduct();
            if (!($product instanceof Model_Catalog_Product)) {
                throw new Exception('Product not found');
            }

            $member_collections = OSC::model('user/member')->getCollection()
                ->addCondition('username', $product->data['vendor'], OSC_Database::OPERATOR_EQUAL)
                ->addCondition('member_id', $product->data['member_id'], OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_OR)
                ->setLimit(2)
                ->load();

            if ($member_collections->length() > 0) {
                foreach ($member_collections as $member) {
                    if ($member->getId() == $product->data['member_id']) {
                        $member_email = $member->data['email'];
                    }
                    if (strtolower($member->data['username']) == strtolower($product->data['vendor'])) {
                        $vendor_email = $member->data['email'];
                    }
                }
            }

        } catch (Exception $ex) {

        }

        return [
            'vendor_email' => $vendor_email,
            'member_email' => $member_email
        ];
    }

    public function reRenderAfterEditDesignSemitest($line_item) {
        try {
            $sync_queue = [];

            try {
                $sync_queue = OSC::model('personalizedDesign/sync')->loadByUkey('v2campaigndesignBeta/' . $line_item->getId());
            } catch (Exception $ex) {}

            if (!empty($sync_queue->data) && $sync_queue->getSyncFlagCode() != 'Running') {
                $sync_queue->delete();
            }

            OSC::helper('catalog/campaign')->addQueueRenderDesignBeta($line_item);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function bulkUploadProductCampaign($product_type_variant_ids, $product_type_ukeys, $design_ids, $product_data, $images_data, $member_id) {
        $product_type_configs = OSC::helper('catalog/productType')->getCampaignConfigs($product_type_ukeys);

        $product_type_data = [];

        foreach ($product_type_configs as $ukey => $product_type) {
            $product_type_variant = [];

            $position = 0;

            foreach ($product_type['product_type_variant'] as $_product_type_variant) {
                if (in_array($_product_type_variant['id'], $product_type_variant_ids)) {
                    $product_type_variant[] = [
                        'id' => $_product_type_variant['id'],
                        'title' => $_product_type_variant['title'],
                        'price' => $_product_type_variant['price'],
                        'compare_at_price' => $_product_type_variant['compare_at_price'],
                        'ukey' => $_product_type_variant['ukey'],
                        'option' => $_product_type_variant['option'],
                        'position' => $position++
                    ];
                }
            }

            if (count($product_type_variant) < 1) {
                throw new Exception('product type variant id is null in ' . $product_type['name']);
            }

            $product_type_data[$ukey] = [
                'id' => $product_type['id'],
                'name' => $product_type['name'],
                'key' => $product_type['key'],
                'image' => $product_type['image'],
                'sort_option' => $product_type['sort_option'],
                'product_variant' => $product_type_variant
            ];
        }

        $print_templates = [];

        $collection_supplier_rel = OSC::model("catalog/supplierVariantRel")->getCollection()
            ->addField('print_template_id')
            ->addCondition('product_type_variant_id', $product_type_variant_ids, OSC_Database::OPERATOR_IN)
            ->load();

        if ($collection_supplier_rel->length() < 1) {
            throw new Exception('print template null');
        }

        foreach ($collection_supplier_rel as $rel) {
            $print_templates[] = $rel->data['print_template_id'];
        }

        $print_templates = array_unique($print_templates);

        $print_templates = OSC::helper('catalog/campaign')->mapPrintTemplate($print_templates);

        $collection_print_template = OSC::model('catalog/printTemplate')->getCollection()->load($print_templates);

        $campaign_config = [];

        $campaign_config['is_reorder'] = 0;
        $campaign_config['apply_reorder'] = [];
        $campaign_config['print_template_config'] = [];

        list($usec, $sec) = explode(' ', microtime());
        $timestamp = (int) ((int) $sec * 1000 + ((float) $usec * 1000));

        if ($collection_print_template->length() > 0) {
            foreach ($collection_print_template as $item) {
                $segments = [];

                $count = 0;

                $selected_design = key(array_reverse($item->data['config']['segments']));

                foreach ($item->data['config']['segments'] as $key => $value) {
                    if (!isset($design_ids[$count])) {
                        continue;
                    }

                    $segments[$key]['source'] = [
                        'type' => 'personalizedDesign',
                        'design_id' => intval($design_ids[$count]),
                        'position' => [
                            'x' => 0,
                            'y' => 0
                        ],
                        'dimension' => [
                            'width' => $value['dimension']['width'],
                            'height' => $value['dimension']['height'],
                        ],
                        'rotation' => 0,
                        'timestamp' => $timestamp,
                        'orig_size' => [
                            'width' => $value['dimension']['width'],
                            'height' => $value['dimension']['height'],
                        ]
                    ];

                    $selected_design = $key;

                    $count++;
                }

                if (count($segments) < 1) {
                    throw new Exception('segments is incorrect');
                }

                $campaign_config['print_template_config'][] = [
                    'selected_design' => $selected_design,
                    'print_template_id' => $item->data['id'],
                    'title' => OSC::safeString($item->data['title']),
                    'apply_other_face' => 0,
                    'segments' => $segments
                ];
            }
        }

        $tab_flag = 0;

        $check_design_tab_collection = OSC::model('personalizedDesign/design')->getCollection()
            ->addField('design_id', 'tab_flag')
            ->addCondition('tab_flag', 1, OSC_Database::OPERATOR_EQUAL)
            ->addCondition('design_id', $design_ids, OSC_Database::OPERATOR_IN)
            ->load();

        if ($check_design_tab_collection->length() > 0) {
            $tab_flag = 1;
        }

        $campaign_config['tab_flag'] = $tab_flag;

        $default_product_price = 0;
        $default_product_compare_at_price = 0;

        $product_types = [];
        $product_variants = [];

        foreach ($product_type_data as $product_key => $config) {
            $product_types[] = $product_key;
            if (count($config['product_variant']) > 0) {
                foreach ($config['product_variant'] as $item) {
                    $product_variants[] = $item;

                    /* Save the lowest price to price of product table */
                    if (($default_product_price === 0 || $item['price'] < $default_product_price) && $item['price'] !== null && $item['price'] > 0) {
                        $default_product_price = $item['price'];
                        $default_product_compare_at_price = $item['compare_at_price'];
                    }
                }
            }
        }

        $product_data['product_type'] = implode(', ', $product_types);
        $product_data['price'] = $default_product_price;
        $product_data['compare_at_price'] = $default_product_compare_at_price;
        $product_data['options'] = [];

        $product_data['meta_data']['campaign_config'] = $campaign_config;
        $product_data['meta_data']['marketing_point'] = '';
        $product_data['meta_data']['buy_design'] = [
            'is_buy_design' => 0,
            'buy_design_price' => 0.0
        ];

        $product = OSC::model('catalog/product');

        $product->setData($product_data)->save();

        OSC::core('observer')->dispatchEvent('catalog/product/postFrmSaveData', ['model' => $product]);


        OSC::helper('filter/tagProductRel')->saveTagProductRel([], $product, $member_id);

        try {
            $this->processPostVariants($product, $product_variants);
            $product->reload();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }


        $variant_image = [];
        $image_collection = $product->getImages();

        if (count($images_data) > 0) {
            foreach ($images_data as $key => $images) {
                $images = explode(',', $images);

                $count = count($images);

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


                    $filename = 'product/' . $product->getId() . '/' . OSC::makeUniqid() . '.' . $ext;
                    $filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);

                    try {
                        OSC::core('aws_s3')->copy($tmp_file_path_s3, $filename_s3);
                    } catch (Exception $ex) {
                        continue;
                    }

                    $image_model = $image_collection->getNullModel();

                    $mockup_ukey = $product->getId() . '_' . md5($filename);

                    try {
                        $image = OSC::model('catalog/product_image')->loadByUKey($mockup_ukey);

                        $image_collection->addItem($image);

                        $variant_image[$key][] = $image->getId();
                    }catch (Exception $ex) {
                        if ($ex->getCode() != 404) {
                            continue;
                        }

                        $image_model->setData([
                            'product_id' => $product->getId(),
                            'position' => -$count - 1 + $_key,
                            'filename' => $filename,
                            'ukey' => $mockup_ukey,
                            'is_static_mockup' => 2
                        ])->save();

                        $image_collection->addItem($image_model);

                        $variant_image[$key][] = $image_model->getId();
                    }
                }
            }
        }

        $variants_print_templates_map = OSC::helper('catalog/campaign')->getVariantsPrintTemplatesMap($product);

        foreach ($product->getVariants() as $key => $variant) {
            if (count($variant_image) == 1) {
                $meta_data_variant = $variant->data['meta_data'];
                $image_ids = [];

                foreach ($variants_print_templates_map as $variant_print_templates) {
                    if ($variant_print_templates['variant_id'] == $variant->getId()) {
                        $image_ids[] = [
                            'print_template_id' => $variant_print_templates['print_template_id'],
                            'image_ids' => $variant_image[0],
                            'image_ids_customer' => $variant_image[0],
                            'version' => 0
                        ];
                    }
                }

                $meta_data_variant['campaign_config']['image_ids'] = $image_ids;

                $variant->setData(['meta_data' => $meta_data_variant])->save();
            } else if (count($variant_image[$key]) > 1) {
                if (isset($variant_image[$key])) {
                    $meta_data_variant = $variant->data['meta_data'];
                    $image_ids = [];

                    foreach ($variants_print_templates_map as $variant_print_templates) {
                        if ($variant_print_templates['variant_id'] == $variant->getId()) {
                            $image_ids[] = [
                                'print_template_id' => $variant_print_templates['print_template_id'],
                                'image_ids' => $variant_image[$key],
                                'image_ids_customer' => $variant_image[$key],
                                'version' => 0
                            ];
                        }
                    }

                    $meta_data_variant['campaign_config']['image_ids'] = $image_ids;

                    $variant->setData(['meta_data' => $meta_data_variant])->save();
                }
            }
        }
    }

    public function processPostVariants($product, $variants) {
        $variant_collection = $product->getVariants();
        $variant_count = $variant_collection->length();

        if (!$variant_count) {
            $variant_count = 0;
        }

        $product_type_variant_ids = [];
        foreach ($variants as $variant) {
            $product_type_variant_ids[$variant['id']] = $variant;
        }

        if ($variant_count > 0 && count($variants) > 0) {
            foreach ($variant_collection as $variant_model) {
                $variant_id = $variant_model->getId();
                $metaData = $variant_model->data['meta_data'];
                $product_type_variant_id = $metaData['campaign_config']['product_type_variant_id'];

                if(!in_array($product_type_variant_id, array_keys($product_type_variant_ids), true)) {
                    try {
                        $variant_collection->removeItemByKey($variant_id);
                        $variant_model->delete();

                        $variant_count--;
                    } catch (Exception $ex) {
                        throw new Exception($ex->getMessage());
                    }

                    continue;
                }

                $this->_processPostVariant($product, $variant_model, $product_type_variant_ids[$product_type_variant_id]);

                unset($product_type_variant_ids[$product_type_variant_id]);
            }
        }

        foreach ($product_type_variant_ids as $variant_input) {
            if (!empty($variant_input['id']) && $variant_input['id'] > 0) {
                if ($this->_processPostVariant($product, $variant_collection->getNullModel(), $variant_input) === true) {
                    $variant_count++;
                }
            }
        }

//        if ($variant_count < 1 || ($variant_collection->getItem() && $variant_collection->getItem()->isDefaultVariant())) {
//            $this->_processPostVariant($product, $variant_count > 0 ? $variant_collection->getItem() : $variant_collection->getNullModel(), $this->_request->get('variant_default'), true);
//        }
    }

    protected function _processPostVariant($product, $variant_model, $variant_input) {
        $key_map = [
            'image_id' => 'image_id',
            'video_id' => 'video_id',
            'sku' => 'sku',
            'price' => 'price',
            'compare_at_price' => 'compare_at_price',
            'cost' => 'cost',
            'track_quantity' => 'track_quantity',
            'overselling' => 'overselling',
            'quantity' => 'quantity',
            'require_shipping' => 'require_shipping',
            'require_packing' => 'require_packing',
            'keep_flat' => 'keep_flat',
            'weight' => 'weight',
            'weight_unit' => 'weight_unit',
            'dimension_width' => 'dimension_width',
            'dimension_height' => 'dimension_height',
            'dimension_length' => 'dimension_length',
            'position' => 'position'
        ];

        if (!is_array($variant_input)) {
            $variant_input = [];
        }

        $variant_data = [];

        foreach ($key_map as $k1 => $k2) {
            if (!isset($variant_input[$k1])) {
                if (in_array($k1, ['track_quantity', 'require_shipping', 'require_packing', 'overselling'])) {
                    $variant_input[$k1] = 1;
                }
                if (in_array($k1, ['keep_flat', 'weight'])) {
                    $variant_input[$k1] = 0;
                }

                if ($k1 == 'weight_unit') {
                    $variant_input[$k1] = 'kg';
                }
            }

            if ($k1 == 'image_id') {
                $variant_input[$k1] = null;
            }

            if ($k1 == 'video_id') {
                $variant_input[$k1] = null;
            }

            if ($k1 == 'quantity') {
                $quantity = 0;
                if ($variant_model->getId() > 0) {
                    $quantity = $variant_model->data['quantity'];
                }
                $variant_input[$k1] = $quantity;
            }

            $variant_data[$k2] = $variant_input[$k1];
        }

        if (count($variant_data) < 1 && $variant_model->getId() > 0) {
            return false;
        }

        if ($variant_model->getId() > 0) {
            $variant_data['meta_data'] = $variant_model->data['meta_data'];
        } else {
            $variant_data['meta_data'] = ["campaign_config" => ["product_type_variant_id" => $variant_input['id']]];
        }

        $variant_data['product_id'] = $product->getId();

        if (!isset($variant_data['sku']) || !$variant_data['sku']) {
            $variant_data['sku'] = $product->getUkey() . '-' . strtoupper(uniqid(null, false) . OSC::randKey(2, 7));
        }

        try {
            $variant_model->setData($variant_data)->save();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return true;
    }
}