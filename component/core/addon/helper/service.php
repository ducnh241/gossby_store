<?php

class Helper_Addon_Service extends OSC_Object
{
    /**
     * @var mixed|null
     */
    protected $_addon_services = null;

    /**
     * @throws OSC_Exception_Runtime
     */
    public function report($addon_service_id): array
    {
        /* @var $DB OSC_Database_Adapter */

        $list_product_id = $this->getListProductUsingAddonService([$addon_service_id]);

        if (!count($list_product_id)) {
            return [];
        }

        $DB = OSC::core('database')->getWriteAdapter();
        $DB->select('*', 'addon_service_report', 'product_id IN (' . implode(',', $list_product_id) . ')', 'product_id ASC');
        $reports = $DB->fetchArrayAll();

        $list_product = OSC::model('catalog/product')
            ->getCollection()
            ->addField('title', 'topic')
            ->addCondition('product_id', $list_product_id, OSC_Database::OPERATOR_IN)
            ->load();

        $data_product = [];
        foreach ($list_product as $product) {
            $data_product[$product->getId()] = ($product->data['topic'] ? $product->data['topic'] . ' - ' : '') . $product->data['title'];
        }

        $results = [];
        foreach ($list_product_id as $product_id) {
            $results[$product_id]['product_id'] = $product_id;
            $results[$product_id]['title'] = $data_product[$product_id] ?? 'Unknown name';
            $results[$product_id]['addon_sold'] = 0;
            $results[$product_id]['order_sold'] = 0;
            foreach ($reports as $report) {
                if ($report['addon_service_id'] == $addon_service_id) {
                    $results[$product_id]['addon_sold'] += 1;
                    $results['total']['addon_sold'] += 1;
                }
                if ($report['product_id'] == $product_id) {
                    $results[$product_id]['order_sold'] += 1;
                    $results['total']['order_sold'] += 1;
                }
            }
        }
        return $results;
    }

    public function getProductTypeData(): array
    {
        $data_product_type = [];
        $data_product_type_variant = [];
        $results = [];

        $pack_product_type_ids = OSC::model('catalog/product/pack')->getCollection()
            ->addField('product_type_id')
            ->load()
            ->toArray();
        $pack_product_type_ids = array_unique(array_column($pack_product_type_ids, 'product_type_id'));

        $list_product_type = OSC::model('catalog/productType')
            ->getCollection()
            ->addField('ukey', 'title')
            ->sort('title')
            ->load();
        foreach ($list_product_type as $product_type) {
            $data_product_type[$product_type->getId()] = $product_type->toArray();
        }
        $list_product_type_variant = OSC::model('catalog/productType_variant')
            ->getCollection()
            ->addField('product_type_id', 'title', 'price', 'compare_at_price', 'best_price')
            ->addCondition('product_type_id', $pack_product_type_ids, OSC_Database::OPERATOR_NOT_IN)
            ->sort('title')
            ->load();
        foreach ($list_product_type_variant as $product_type_variant) {
            $data_product_type_variant[$product_type_variant->data['product_type_id']]['product_type_variants'][$product_type_variant->getId()] = $product_type_variant->toArray();
        }
        foreach ($data_product_type_variant as $product_type_id => $value) {
            $results[$product_type_id]['product_type_variants'] = $value['product_type_variants'];
            $results[$product_type_id]['title'] = $data_product_type[$product_type_id]['title'] ?? '';
            $results[$product_type_id]['max_price'] = max(array_values(array_column($value['product_type_variants'], 'price')));
            $results[$product_type_id]['max_compare_at_price'] = max(array_values(array_column($value['product_type_variants'], 'compare_at_price')));
        }
        return $results;
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    public function getListProductUsingAddonService($addon_service_ids = []): array
    {
        $addon_service_collection = OSC::model('addon/service')->getCollection();
        if (count($addon_service_ids)) {
            $addon_service_collection->addCondition('id', $addon_service_ids, OSC_Database::OPERATOR_IN);
        }
        $list_addon_service = $addon_service_collection->sort('id')->load();

        $list_product = OSC::model('catalog/product')
            ->getCollection()
            ->addField('product_id', 'addon_service_data');
        foreach ($list_addon_service as $addon_service) {
            $list_product->addCondition('addon_service_data', 'addon_service_ids%,' . $addon_service->getId() . ',', OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_OR);
        }
        $list_product = $list_product->load()->toArray();
        return array_column($list_product, 'product_id');
    }

    /**
     * @param Model_Catalog_Cart_Item $cart_item
     * @param array $post_data
     * @param null $product
     * @return array
     */
    public function updateCartItemAddonServices(Model_Catalog_Cart_Item $cart_item, $post_data = [], $product = null)
    {
        $custom_price_data = is_array($cart_item->data['custom_price_data']) ? $cart_item->data['custom_price_data'] : [];
        $addon_service_expired_ids = [];
        $addon_service_unavailable_ids = [];

        if ($product instanceof Model_Catalog_Product) {
            $product_type_variant = $cart_item->getProductTypeVariant();
            $product_type_ids = [$product_type_variant->data['product_type_id']];
            $product_type_variant_ids = [$product_type_variant->data['id']];
            $product_addon_services = OSC::helper('addon/service')->getAddonServices($product, $product_type_ids, $product_type_variant_ids);
            $addon_service_expired_ids = array_column($product_addon_services['addon_service_expired'] ?? [], 'id');
            $addon_service_unavailable_ids = array_column($product_addon_services['addon_service_unavailable'] ?? [], 'id');
        }

        foreach ($post_data as $addon_id => $post_item) {
            try {
                if (in_array($addon_id, $addon_service_expired_ids) || in_array($addon_id, $addon_service_unavailable_ids)) {
                    unset($custom_price_data['addon_services'][$addon_id]);
                    continue;
                }

                $addon = OSC::model('addon/service')->load($addon_id);
                $addon_version = OSC::helper('addon/service')->getVersionDistribution($addon_id);
                $options = $addon_version['data']['options'] ?? [];
                foreach ($post_item as $ukey => $value) {
                    if (isset($custom_price_data['addon_services'])) {
                        foreach ($custom_price_data['addon_services'] as $key => $addon_item) {
                            if ($key == $addon->data['id']) {
                                // Unset this $key and re-update below if $value['check'] is true
                                unset($custom_price_data['addon_services'][$key]);
                            }
                        }
                    }

                    $add_service = $value['check'];

                    // Insert Addon to item when: Isset ukey, check = true, and is_hide version = 0
                    if (isset($options[$ukey]) && $add_service && $addon_version['is_hide'] == 0) {
                        $custom_price_data['addon_services'][$addon_id][$ukey] = $options[$ukey];
                        $custom_price_data['addon_services'][$addon_id][$ukey]['campaign_title'] = $addon->getAddonServiceTitle();
                        $custom_price_data['addon_services'][$addon_id][$ukey]['message'] = $value['message'];
                        $custom_price_data['addon_services'][$addon_id][$ukey]['type'] = $addon->data['type'];
                        $custom_price_data['addon_services'][$addon_id][$ukey]['version_id'] = $addon_version['id'];
                        $custom_price_data['addon_services'][$addon_id][$ukey]['is_hide'] = $addon_version['is_hide'];
                    }

                    // If all addon_service has unavailable and addon_service data has empty, remove param addon_services
                    if (empty($custom_price_data['addon_services'])) {
                        unset($custom_price_data['addon_services']);
                    }
                }
            } catch (Exception $exception) {
            }
        }

        return $custom_price_data;
    }

    public function updateCartCustomPriceData(Model_Catalog_Cart $cart)
    {
        $cartCustomPriceData = $cart->data['custom_price_data'];
        $cartCustomPriceData = is_array($cartCustomPriceData) && !empty($cartCustomPriceData) ? $cartCustomPriceData : [];

        $line_items = $cart->getLineItems(true);
        if ($line_items) {
            if (count($cartCustomPriceData) > 0) {
                foreach ($cart::CUSTOM_PRICE_DATA_KEYS as $price_key) {
                    unset($cartCustomPriceData[$price_key]);
                }
            }

            foreach ($line_items as $item) {
                if (isset($item->data['custom_price_data'])) {
                    foreach ($cart::CUSTOM_PRICE_DATA_KEYS as $price_key) {
                        if (isset($item->data['custom_price_data'][$price_key]) && count($item->data['custom_price_data'][$price_key]) > 0) {
                            $cartCustomPriceData[$price_key][$item->data['ukey']] = $item->data['custom_price_data'][$price_key];
                        }
                    }
                }
            }
        }

        return $cartCustomPriceData;
    }

    public function getTypeNameById($ids)
    {
        $is_array = is_array($ids);

        if (!$is_array) {
            $ids = [$ids];
        }

        $model = OSC::model('addon/service');

        $data = $model->getCollection()
            ->addField('id', 'type', 'title')
            ->addCondition('id', $ids, OSC_Database::OPERATOR_IN)
            ->load()
            ->toArray();

        $result = [];

        foreach ($data as $item) {
            $result[$item['id']] = [
                'type_name' => Model_Addon_Service::TYPE_NAME_ARR[$item['type']],
                'title' => $item['title'],
            ];
        }

        if ($is_array) {
            return $result;
        }

        return $result[$ids[0]];
    }

    public function formatData($addon_service_data)
    {
        $addon_services = [];
        $addon_type_names = $this->getTypeNameById(explode(',', trim($addon_service_data['addon_service_ids'], ',')));

        foreach ($addon_service_data['addon_services'] as $service) {
            $start_date = date('d/m/Y', $service['start_timestamp']);
            $end_date = date('d/m/Y', $service['end_timestamp']);

            $addon_services[] = [
                'addon_service_id' => $service['addon_service_id'],
                'title' => $addon_type_names[$service['addon_service_id']]['title'],
                'type' => $addon_type_names[$service['addon_service_id']]['type_name'],
                'product_type_id' => intval($service['product_type_id']),
                'date_range' => $start_date . ' - ' . $end_date,
                'start_timestamp' => $service['start_timestamp'],
                'end_timestamp' => $service['end_timestamp'],
            ];
        }

        return $addon_services;
    }

    public function verifyAddonServiceData($addon_services, $enable)
    {
        $addon_services = OSC::decode($addon_services);
        $addon_service_ids = [];

        foreach ($addon_services as $key => $service) {
            $addon_service_ids[] = $service['addon_service_id'];
            $date_range = explode(' - ', $service['date_range']);

            preg_match("/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})\s*$/", $date_range[0], $start_date);
            preg_match("/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})\s*$/", $date_range[1], $end_date);

            if (count($start_date) < 4 || count($end_date) < 4) {
                throw new Exception('Date format is invalid: ' . $service['date_range']);
            }

            $addon_services[$key]['addon_service_id'] = intval($service['addon_service_id']);
            $addon_services[$key]['start_timestamp'] = mktime(0, 0, 1, $start_date[2], $start_date[1], $start_date[3]);
            $addon_services[$key]['end_timestamp'] = mktime(23, 59, 59, $end_date[2], $end_date[1], $end_date[3]);
            unset($addon_services[$key]['type']);
            unset($addon_services[$key]['not_available']);
            unset($addon_services[$key]['date_range']);

            $product_type = [];
            if ($addon_services[$key]['product_type_id'] !== 0) {
                $product_type[] = $addon_services[$key]['product_type_id'];
            } else if (is_array($addon_services[$key]['auto_apply_for_product_type']) && count($addon_services[$key]['auto_apply_for_product_type']) > 0) {
                $product_type = $addon_services[$key]['auto_apply_for_product_type'];
            }

            $addon_services[$key]['product_type_id'] = $product_type;
        }

        return [
            'addon_services' => $addon_services,
            'addon_service_ids' => count($addon_service_ids) ? ',' . implode(',', array_unique($addon_service_ids)) . ',' : '',
            'enable' => $enable,
        ];
    }

    public function getAddonServiceList($product_type_ids = [], $current_addon_ids = [])
    {
        $model = OSC::model('addon/service')->getCollection()->addField('id', 'title', 'type', 'product_type_id', 'auto_apply_for_product_type_variants', 'start_timestamp', 'end_timestamp');

        if (is_array($product_type_ids)) {
            $ids = implode(',', $product_type_ids);
            $condition = 'type <> ' . Model_Addon_Service::TYPE_VARIANT;

            if (count($product_type_ids)) {
                $condition .= ' OR type = ' . Model_Addon_Service::TYPE_VARIANT . " AND product_type_id IN (${ids})";
            }

            if (count($current_addon_ids)) {
                $condition .= ' OR id IN (' . implode(',', $current_addon_ids) . ')';
            }

            $model->setCondition('status = 1 AND (' . $condition . ')');
        }

        $addon_list = $model->load()->toArray();

        if (is_array($addon_list)) {
            foreach ($addon_list as $index => $item) {
                $addon_list[$index]['type'] = Model_Addon_Service::TYPE_NAME_ARR[$item['type']];
                if ($item['type'] === Model_Addon_Service::TYPE_VARIANT && count($product_type_ids) && !in_array($item['product_type_id'], $product_type_ids)) {
                    $addon_list[$index]['disabled'] = true;
                }
            }
        }

        return $addon_list;
    }

    public function isAvailableInCustomerLocation($type): bool
    {
        if ($type == Model_Addon_Service::TYPE_VARIANT) {
            return true;
        }

        $country_code = OSC::helper('core/common')->getCustomerCountryCodeCookie();
        $province_code = OSC::helper('core/common')->getCustomerProvinceCodeCookie();

        return
            in_array(
                $country_code,
                Model_Addon_Service::LIST_COUNTRY_AVAILABLE
            ) &&
            !in_array(
                $province_code,
                Model_Addon_Service::LIST_EXCLUDE_PROVINCE
            );
    }

    /**
     * @throws OSC_Exception_Runtime
     * @throws Exception
     */
    public function checkAddonServiceIsUsing($addon_id)
    {
        $products = $this->getListProductUsingAddonService([$addon_id]);
        if (count($products)) {
            throw new Exception('Unable to delete! Add-on service ID #' . $addon_id . ' is using in campaign: #' . implode(', #', $products));
        }

        $filter_value = '"addon_services"%"' . $addon_id . '"';
        $cart_items = OSC::model('catalog/cart_item')->getCollection()
            ->addField('custom_price_data')
            ->addCondition('custom_price_data', $filter_value, OSC_Database::OPERATOR_LIKE)
            ->setLimit(1)
            ->load();
        if ($cart_items->length() > 0) {
            throw new Exception('Cannot delete addon because it has already been added to a customer’s cart!');
        }

        $order_items = OSC::model('catalog/order_item')->getCollection()
            ->addField('custom_price_data')
            ->addCondition('custom_price_data', $filter_value, OSC_Database::OPERATOR_LIKE)
            ->setLimit(1)
            ->load();
        if ($order_items->length() > 0) {
            throw new Exception('Cannot delete addon because it has already been added to an existing order!');
        }
    }

    public function getMockups($addon_version)
    {
        return $this->_handleMockups($addon_version);
    }

    protected function _handleMockups($addon_version): array
    {
        $mockup_images = $addon_version['images'];
        $mockup_videos = $addon_version['videos'];

        if (is_array($mockup_images) && !empty($mockup_images)) {
            foreach ($mockup_images as $key => $image) {
                if (!preg_match('/http(|s):\/\//', $image['url'])) {
                    $mockup_images[$key]['url'] = OSC::core('aws_s3')->getStorageUrl($image['url']);
                }
                $mockup_images[$key]['position'] = $image['position'] ?: 100;
            }

            usort($mockup_images, function ($a, $b) {
                return $a['position'] <=> $b['position'];
            });
        }

        if (is_array($mockup_videos) && !empty($mockup_videos)) {
            foreach ($mockup_videos as $key => $video) {
                if (!preg_match('/http(|s):\/\//', $video['url'])) {
                    $mockup_videos[$key]['url'] = OSC::core('aws_s3')->getStorageUrl($video['url']);
                }
                if (!preg_match('/http(|s):\/\//', $video['thumbnail'])) {
                    $mockup_videos[$key]['thumbnail'] = OSC::core('aws_s3')->getStorageUrl($video['thumbnail']);
                }
                $mockup_videos[$key]['position'] = $video['position'] ?: 100;
            }

            usort($mockup_videos, function ($a, $b) {
                return $a['position'] <=> $b['position'];
            });
        }

        return [
            'images' => $mockup_images,
            'videos' => $mockup_videos,
        ];
    }

    public function getABTestAddonKey()
    {
        return '_ab_ao_' . OSC_Controller::makeRequestChecksum('_ab_addon_service', OSC_SITE_KEY);
    }

    /**
     * if browser has cookies version of the current addon, get version from cookies,
     * else get from DB and set cookies
     * @param $addon_id
     * @return mixed
     * @throws Exception
     */
    public function getVersionDistribution($addon_id)
    {
        $cookie_key = $this->getABTestAddonKey();
        $cookie_data = OSC::cookieGet($cookie_key) ? OSC::decode(OSC::cookieGet($cookie_key), true) : [];
        $version_id = isset($cookie_data[$addon_id]['addon_version_id']) ? intval($cookie_data[$addon_id]['addon_version_id']) : null;

        static $version_collection_arr = [];
        $cache_key = 'versions_of_' . '_' . $addon_id;

        if (!$addon_id) {
            throw new Exception('No addon ID');
        }

        try {
            $addon_service = OSC_Database_Model::getPreLoadedModel('addon/service', $addon_id);

            if (!($addon_service instanceof Model_Addon_Service)) {
                return false;
            }

            if (isset($version_collection_arr[$cache_key])) {
                $version_collection = $version_collection_arr[$cache_key];
            } else {
                $version_collection_arr[$cache_key] = null;

                $version_collection = OSC::model('addon/version')->getCollection()
                    ->addCondition('addon_id', $addon_id)
                    ->sort('traffic', 'ASC')
                    ->load()->toArray();

                $version_collection_arr[$cache_key] = $version_collection;
            }

            $available_location = OSC::helper('addon/service')->isAvailableInCustomerLocation($addon_service->getData('type'));
            $_need_set_cookies = false;

            if (!$addon_service->isRunningAbTest() || !$available_location) {
                $version = $version_collection[array_search(1, array_column($version_collection, 'is_default_version'))];
                $version_id = $version['id'];

                if (isset($cookie_data[$addon_id])) {
                    unset($cookie_data[$addon_id]);
                    $_need_set_cookies = true;
                }

            } else {
                if ($version_id) {
                    $version = $version_collection[array_search($version_id, array_column($version_collection, 'id'))];
                } else {
                    //Get version by traffic ASC
                    $version = $version_collection[0];
                }
            }

            if (!$version_id) {
                $_need_set_cookies = true;
                $cookie_data[$addon_id] = [
                    'addon_id' => $version['addon_id'],
                    'addon_version_id' => $version['id']
                ];
            }

            if ($_need_set_cookies) {
                if (empty($cookie_data)) {
                    OSC::cookieRemove($cookie_key);
                } else {
                    OSC::cookieSetCrossSite($cookie_key, OSC::encode($cookie_data));
                }
            }

            return $version;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public function getAllVersions($addon_id)
    {
        $collection = OSC::model('addon/version')->getCollection()->addCondition('addon_id', $addon_id, OSC_Database::OPERATOR_EQUAL)->load()->toArray();

        $versions = [];

        foreach ($collection as $version) {
            if ($version['data']['group_price']) {
                $version['data']['group_price'] = $version['data']['group_price'] / 100;
            }

            foreach ($version['data']['options'] as $option_key => $option) {
                if ($option['price']) {
                    $version['data']['options'][$option_key]['price'] = $option['price'] / 100;
                }
            }

            $versions[$version['id']] = $version;
        }

        return $versions;
    }

    public function formatDateRange($start_timestamp, $end_timestamp, $default_value = '')
    {
        if (empty($start_timestamp) || empty($end_timestamp)) return $default_value;

        return date('d/m/Y', $start_timestamp) . ' - ' . date('d/m/Y', $end_timestamp);
    }

    public function getAddonServices(Model_Catalog_Product $product, $product_type_ids, $product_type_variant_ids, array $options = [])
    {
        if ($this->_addon_services === null) {
            // Get Auto addon service
            $auto_addon_ids = [];
            if ($product->isCampaignMode()) {
                $addon_service_collection = OSC::model('addon/service')
                    ->getCollection()
                    ->addField('id', 'title', 'type', 'product_type_id', 'auto_apply_for_product_type_variants')
                    ->addCondition(
                        'status',
                        '1',
                        OSC_Database::OPERATOR_EQUAL,
                        OSC_Database::RELATION_AND
                    )->addClause('get_addon_service_auto')->addCondition(
                        'auto_apply_for_product_type_variants',
                        '["*"]',
                        OSC_Database::OPERATOR_EQUAL,
                        OSC_Database::RELATION_OR, 'get_addon_service_auto'
                    );

                foreach ($product_type_ids as $product_type_id) {
                    $addon_service_collection->addCondition(
                        'auto_apply_for_product_type_variants',
                        "\"{$product_type_id}\"",
                        OSC_Database::OPERATOR_LIKE,
                        OSC_Database::RELATION_OR, 'get_addon_service_auto'
                    );
                }

                $auto_addon_services = $addon_service_collection->load()->toArray();

                foreach ($auto_addon_services as $auto_addon) {
                    if (!is_array($auto_addon['auto_apply_for_product_type_variants'])) continue;

                    if (in_array('*', $auto_addon['auto_apply_for_product_type_variants'])) {
                        $auto_addon_ids[] = $auto_addon['id'];
                        continue;
                    }

                    $is_auto_addon = false;

                    foreach ($auto_addon['auto_apply_for_product_type_variants'] as $auto_type_id => $auto_type_variant_ids) {
                        if (!in_array($auto_type_id, $product_type_ids)) {
                            continue;
                        }

                        if (
                            in_array('*', $auto_type_variant_ids) ||
                            array_intersect($product_type_variant_ids, $auto_type_variant_ids)
                        ) {
                            $is_auto_addon = true;
                        }
                    }

                    if ($is_auto_addon) {
                        $auto_addon_ids[] = $auto_addon['id'];
                    }
                }
            }

            $product_addon_services = $product->data['addon_service_data']['addon_services'] ?? [];
            if(!empty($product_addon_services) && empty($product->data['addon_service_data']['enable'])) {
                $product_addon_services = [];
            }
            $addon_filter = [];
            $addon_services = [];
            $manual_addon_mapping = [];

            $addon_service_ids = array_column($product_addon_services, 'addon_service_id');
            $addon_service_ids = array_merge($addon_service_ids, $auto_addon_ids);
            $addon_service_ids = array_unique($addon_service_ids);

            OSC_Database_Model::preLoadModelData('addon/service', $addon_service_ids);

            foreach ($addon_service_ids as $addon_service_id) {
                $addon_services[$addon_service_id] = OSC_Database_Model::getPreLoadedModel(
                    'addon/service',
                    $addon_service_id
                );
            }

            foreach ($product_addon_services as $addon) {
                $manual_addon_mapping[$addon['addon_service_id']] = $addon;
            }

            if (!empty($addon_services)) {
                foreach ($addon_services as $addon) {
                    if ($addon instanceof Model_Addon_Service) {
                        $addon_id = $addon->getId();
                        if (in_array($addon_id, $auto_addon_ids)) {
                            $addon_tmp = [
                                'addon_service_id' => $addon_id,
                                'start_timestamp' => $addon->data['start_timestamp'],
                                'end_timestamp' => $addon->data['end_timestamp'],
                                'addon_service' => $addon
                            ];

                            if ($manual_addon_mapping[$addon_id]) {
                                $addon_tmp['start_timestamp'] = min($addon_tmp['start_timestamp'], $manual_addon_mapping[$addon_id]['start_timestamp']);
                                $addon_tmp['end_timestamp'] = max($addon_tmp['end_timestamp'], $manual_addon_mapping[$addon_id]['end_timestamp']);
                            }

                            $addon_filter['addon_services']['auto'][$addon_id] = $addon_tmp;
                        } else {
                            $addon_filter['addon_services']['manual'][$addon_id] = [
                                'addon_service_id' => $addon_id,
                                'start_timestamp' => $manual_addon_mapping[$addon_id]['start_timestamp'],
                                'end_timestamp' => $manual_addon_mapping[$addon_id]['end_timestamp'],
                                'addon_service' => $addon,
                            ];
                        }
                    }
                }
            }

            $this->_addon_services = OSC::helper('catalog/cart')->filterAddonServices($addon_filter['addon_services'], false, $options);
        }
        return $this->_addon_services;
    }

    public function getConflictAbTestTimeAddon($addon_id, $start_timestamp, $end_timestamp) {
        $condition = <<<EOF
id != $addon_id AND status = 1 AND ab_test_enable = 1 AND
(
    ab_test_start_timestamp <= $start_timestamp AND ab_test_end_timestamp >= $start_timestamp OR
    ab_test_start_timestamp <= $end_timestamp AND ab_test_end_timestamp >= $end_timestamp OR
    ab_test_start_timestamp >= $start_timestamp AND ab_test_end_timestamp <= $end_timestamp
)
EOF;

        return OSC::model('addon/service')->getCollection()
            ->setCondition($condition)
            ->setLimit(1)
            ->load()
            ->first();
    }
}
