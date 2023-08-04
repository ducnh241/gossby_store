<?php

class Helper_Catalog_Cart extends OSC_Object {
    private function __updateManualAddonServiceOfCartItem(&$cart_item, $addon_service_data) {
        $enable = $addon_service_data['enable'] ?? false;

        if (!$enable ||
            !is_array($addon_service_data['addon_services']) ||
            !count($addon_service_data['addon_services'])) {
            return;
        }

        foreach ($addon_service_data['addon_services'] as $addon_service) {
            $cart_item['addon_services']['manual'][$addon_service['addon_service_id']] = [
                'addon_service_id' => $addon_service['addon_service_id'],
                'start_timestamp' => $addon_service['start_timestamp'] ?? 0,
                'end_timestamp' => $addon_service['end_timestamp'] ?? 0,
            ];
        }
    }

    private function __updateProductTypeOfCartItems(&$cart_items, $options = []) {
        $product_model_key = 'catalog/product';

        $filtered_cart_items = array_filter($cart_items, function ($cart_item) {
            return !empty($cart_item['has_addon_service']);
        });

        if (!count($filtered_cart_items)) {
            return;
        }

        $product_ids = array_column($filtered_cart_items, 'product_id');
        $product_ids = array_unique($product_ids);

        OSC_Database_Model::preLoadModelData($product_model_key, $product_ids);

        foreach ($cart_items as &$cart_item) {
            if (empty($cart_item['has_addon_service'])) {
                continue;
            }

            foreach ($product_ids as $product_id) {
                $product = OSC_Database_Model::getPreLoadedModel($product_model_key, $product_id);

                if ($product->isSemitestMode()) {
                    $this->__updateManualAddonServiceOfCartItem($cart_item, $product->getData('addon_service_data'));
                    continue;
                }

                if (empty($product) ||
                    (empty($product->getData('product_type')) && empty($options['product_type']))) {
                    continue;
                }

                $product_type_list = !empty($options['product_type']) ?
                    explode(',', $options['product_type']) :
                    explode(',', $product->getData('product_type'));

                // trim product type
                $product_type_list = array_map(function ($product_type) {
                    return trim($product_type);
                }, $product_type_list);

                if ($cart_item['product_id'] === $product_id) {
                    $cart_item['product_type'] = $product_type_list;
                    $this->__updateManualAddonServiceOfCartItem($cart_item, $product->getData('addon_service_data'));
                    break;
                }
            }
        }
    }

    private function __updateProductTypeDetailOfCartItems(&$cart_items) {
        $filtered_cart_items = array_filter($cart_items, function ($cart_item) {
            return !empty($cart_item['has_addon_service']);
        });

        if (!count($filtered_cart_items)) {
            return;
        }

        $product_types = array_reduce(array_column($filtered_cart_items, 'product_type'), 'array_merge', []);
        $product_types = array_unique($product_types);

        $product_types = OSC::model('catalog/productType')->getCollection()
            ->addField('id', 'ukey', 'title')
            ->addCondition(
                'status',
                Model_Catalog_ProductType::STATUS['can_create_product'],
                OSC_Database::OPERATOR_EQUAL
            )
            ->addCondition(
                'ukey',
                $product_types,
                OSC_Database::OPERATOR_IN
            )
            ->load();

        foreach ($cart_items as &$cart_item) {
            if (empty($cart_item['has_addon_service'])) {
                continue;
            }

            foreach ($product_types as $product_type) {
                if (!empty($cart_item['product_type']) &&
                    is_array($cart_item['product_type']) &&
                    in_array($product_type->getData('ukey'), $cart_item['product_type'])) {
                    $cart_item['product_type_detail'][] = $product_type->toArray();
                }
            }
        }
    }

    private function __updateAutoAddonServiceOfCartItems(&$cart_items) {
        $filtered_cart_items = array_filter($cart_items, function ($cart_item) {
            return !empty($cart_item['has_addon_service']);
        });

        if (!count($filtered_cart_items)) {
            return;
        }

        $product_type_details = array_reduce(array_column($filtered_cart_items, 'product_type_detail'), 'array_merge', []);
        $product_type_ids = array_column($product_type_details, 'id');
        $product_type_ids = array_unique($product_type_ids);

        $addon_service_collection = OSC::model('addon/service')
            ->getCollection()
            ->addField('id', 'title', 'type', 'product_type_id', 'auto_apply_for_product_type_variants', 'start_timestamp', 'end_timestamp')
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

        $addon_services = $addon_service_collection->load();

        foreach ($cart_items as &$cart_item) {
            if (empty($cart_item['has_addon_service'])) {
                continue;
            }

            $cart_item_product_type_details = $cart_item['product_type_detail'];

            foreach ($cart_item_product_type_details as $cart_item_product_type_detail) {
                foreach ($addon_services as $addon_service) {
                    $auto_apply_for_product_type_variant_json = json_encode($addon_service->getData('auto_apply_for_product_type_variants'));

                    if (str_contains($auto_apply_for_product_type_variant_json, "\"{$cart_item_product_type_detail['id']}\"") ||
                        $auto_apply_for_product_type_variant_json === '["*"]') {
                        $addon_service_id = $addon_service->getId();
                        $cart_item['addon_services']['auto'][$addon_service_id] = [
                            'addon_service_id' => $addon_service_id,
                            'start_timestamp' => $addon_service->data['start_timestamp'] ?? 0,
                            'end_timestamp' => $addon_service->data['end_timestamp'] ?? 0,
                        ];
                    }
                }
            }
        }
    }

    private function __updateAddonServiceOfCartItems(&$cart_items) {
        $filtered_cart_items = array_filter($cart_items, function ($cart_item) {
            return !empty($cart_item['has_addon_service']);
        });

        if (!count($filtered_cart_items)) {
            return;
        }

        // get addon_service_ids from $filtered_cart_items
        $filtered_addon_services = array_column($filtered_cart_items, 'addon_services');
        $filtered_addon_services = array_reduce($filtered_addon_services, 'array_merge', []);
        $addon_services = array_reduce($filtered_addon_services, 'array_merge', []);
        $addon_service_ids = array_column($addon_services, 'addon_service_id');
        $addon_service_ids = array_unique($addon_service_ids);

        $addon_service_model_key = 'addon/service';
        $addon_services = [];
        $cart_item_addon_service_ids = [];

        foreach ($cart_items as $cart_item) {
            $cart_item_addon_services = $cart_item['cart_item']->getData('custom_price_data')['addon_services'] ?? [];

            if (!$cart_item_addon_services) {
                continue;
            }

            foreach ($cart_item_addon_services as $cart_item_addon_service_id => $cart_item_addon_service) {
                $cart_item_addon_service_ids[] = $cart_item_addon_service_id;
            }
        }

        $cart_item_addon_service_ids = array_unique($cart_item_addon_service_ids);

        OSC_Database_Model::preLoadModelData(
            $addon_service_model_key,
            array_unique(array_merge($addon_service_ids, $cart_item_addon_service_ids))
        );

        foreach ($addon_service_ids as $addon_service_id) {
            $addon_services[$addon_service_id] = OSC_Database_Model::getPreLoadedModel(
                $addon_service_model_key,
                $addon_service_id
            );
        }

        foreach ($cart_items as &$cart_item) {
            // update $cart_item_addon_services
            foreach ($cart_item_addon_service_ids as $cart_item_addon_service_id) {
                $cart_item['cart_item_addon_services'][$cart_item_addon_service_id] = OSC_Database_Model::getPreLoadedModel(
                    $addon_service_model_key,
                    $cart_item_addon_service_id
                );;
            }

            if (empty($cart_item['addon_services']) || empty($cart_item['has_addon_service'])) {
                continue;
            }

            // update manual addon services
            if (!empty($cart_item['addon_services']['manual']) && is_array($cart_item['addon_services']['manual'])) {
                foreach ($cart_item['addon_services']['manual'] as &$manual_addon_service) {
                    if (empty($manual_addon_service['addon_service_id']) ||
                        empty($addon_services[$manual_addon_service['addon_service_id']])) {
                        continue;
                    }

                    $manual_addon_service['addon_service'] = $addon_services[$manual_addon_service['addon_service_id']];
                }
            }

            // update auto addon services
            if (!empty($cart_item['addon_services']['auto']) && is_array($cart_item['addon_services']['auto'])) {
                foreach ($cart_item['addon_services']['auto'] as &$auto_addon_service) {
                    if (empty($auto_addon_service['addon_service_id']) ||
                        empty($addon_services[$auto_addon_service['addon_service_id']])) {
                        continue;
                    }

                    $auto_addon_service['addon_service'] = $addon_services[$auto_addon_service['addon_service_id']];
                }
            }
        }
    }

    public function filterAddonServices($addon_services, $get_only_invalid_addon_services = false, $filter_options = []) {
        if (!empty($filter_options['has_pack'])) {
            return [
                'data_addon_service' => [],
                'addon_service_expired' => [],
                'addon_service_unavailable' => [],
            ];
        }

        // expired addon services
        $expired_addon_services = [];
        // unavailable addon services
        $unavailable_addon_services = [];
        // valid addon services
        $valid_addon_services = [];
        if (!$get_only_invalid_addon_services) {
            $s3_bucket_url = OSC::core('aws_s3')->getS3BucketUrl();
            $cdn_config = OSC::systemRegistry('CDN_CONFIG');
            $distinct_product_type_ids = OSC::model('catalog/product_pack')->getDistinctProductTypeId();
        }
        foreach ($addon_services as $addon_services_by_type) {
            foreach ($addon_services_by_type as $addon_service_data) {
                if (empty($addon_service_data['addon_service'])) {
                    continue;
                }

                $addon_service = $addon_service_data['addon_service'];

                if ($addon_service->data['status'] !== 1) {
                    continue;
                }

                $addon_service_key = "_{$addon_service->getId()}";
                $current_timestamp = time();

                if (!OSC::helper('addon/service')->isAvailableInCustomerLocation($addon_service->getData('type'))) {
                    $unavailable_addon_services[$addon_service_key] = [
                        'id' => $addon_service->getId(),
                        'title' => $addon_service->data['title']
                    ];

                    continue;
                }

                if ($current_timestamp < $addon_service_data['start_timestamp'] ||
                    $current_timestamp > $addon_service_data['end_timestamp']) {
                    $expired_addon_services[$addon_service_key] = [
                        'id' => $addon_service->getId(),
                        'title' => $addon_service->data['title']
                    ];

                    continue;
                }

                if ($get_only_invalid_addon_services) {
                    continue;
                }

                $group_price = 0;
                $addon_version = OSC::helper('addon/service')->getVersionDistribution($addon_service->getId());
                $options = $addon_version['data']['options'] ?? [];

                if (count($options) == 0) {
                    continue;
                }

                $addon_service_options = [];
                $default_data_option_key = null;
                $apply_for_product_type_variants = [];

                if ($addon_service->getData('type') == Model_Addon_Service::TYPE_ADDON &&
                    !empty($addon_version['data']['enable_same_price']) &&
                    !empty($addon_version['data']['group_price'])) {
                    $group_price = $addon_version['data']['group_price'];
                }

                foreach ($options as $option_key => $option) {
                    $is_default_option = !empty($option['is_default']);

                    $option_image = !empty($option['image']) ? OSC::core('aws_s3')->getStorageUrl($option['image']) : '';

                    if ($option_image && !empty($cdn_config['enable']) && !empty($cdn_config['imagekit_url'])) {
                        $option_image = str_replace($s3_bucket_url, $cdn_config['imagekit_url'], $option_image);
                    }

                    $addon_service_options[$option_key] = [
                        'title' => $option['title'] ?? '',
                        'price' => $group_price ?: ($option['price'] ?? 0),
                        'image' => $option_image,
                        'is_default' => $is_default_option
                    ];

                    if ($is_default_option) {
                        $default_data_option_key = $option_key;
                    }
                }

                if (!$default_data_option_key) {
                    $default_data_option_key = array_key_first($options);
                }

                if ($addon_service->getData('product_type_id') != 0) {
                    $apply_for_product_type_variants = [$addon_service->getData('product_type_id') => ['*']];
                } elseif (is_array($addon_service->getData('auto_apply_for_product_type_variants')) &&
                    count($addon_service->getData('auto_apply_for_product_type_variants'))) {
                    foreach ($addon_service->getData('auto_apply_for_product_type_variants') as
                             $auto_apply_product_type_id => $auto_apply_product_type_variants) {
                        // Ignore product type have pack
                        if (!empty($distinct_product_type_ids) && !in_array($auto_apply_product_type_id, $distinct_product_type_ids)) {
                            $apply_for_product_type_variants = $addon_service->getData('auto_apply_for_product_type_variants');
                        }
                    }
                }

                // Filter by product_type_variant_id when get addon services by variant
                $filter_option_product_type_variant_id = $filter_options['product_type_variant_id'] ?? null;
                $filter_option_product_type_id = $filter_options['product_type_id'] ?? null;
                if ($filter_option_product_type_variant_id) {
                    $flag_skip_addon = true;
                    foreach ($apply_for_product_type_variants as $apply_for_product_product_type_id => $apply_for_product_product_type_variant_ids) {
                        // Only return addon belong to product type variant
                        if ($apply_for_product_product_type_variant_ids === '*' ||
                            ($apply_for_product_product_type_id == $filter_option_product_type_id &&
                                is_array($apply_for_product_product_type_variant_ids) &&
                                (in_array($filter_option_product_type_variant_id, $apply_for_product_product_type_variant_ids) ||
                                    in_array('*', $apply_for_product_product_type_variant_ids)))
                        ) {
                            $flag_skip_addon = false;
                        }

                        // Skip item with pack
                        if (is_array($apply_for_product_product_type_variant_ids) &&
                            in_array($filter_option_product_type_variant_id, $apply_for_product_product_type_variant_ids) &&
                            !empty($distinct_product_type_ids) &&
                            !in_array($apply_for_product_product_type_id, $distinct_product_type_ids)) {
                            $flag_skip_addon = false;
                        }
                    }

                    if ($flag_skip_addon) {
                        continue;
                    }
                }

                $valid_addon_services[$addon_service_key] = [
                    'id' => $addon_service->getId(),
                    'version_id' => $addon_version['id'],
                    'is_hide' => $addon_version['is_hide'],
                    'apply_for_product_type_variants' => !empty($apply_for_product_type_variants) ? $apply_for_product_type_variants : null,
                    'type' => $addon_service->getTypeName(true),
                    'title' => $addon_service->getAddonServiceTitle(),
                    'options' => $addon_service_options,
                    'show_in_detail' => $addon_service->isDisplayAtProductDetail(),
                    'is_running_ab_test' => $addon_service->isRunningAbTest(),
                    'mockups' => OSC::helper('addon/service')->getMockups($addon_version)
                ];

                if ($addon_service->getData('type') == Model_Addon_Service::TYPE_ADDON) {
                    $valid_addon_services[$addon_service_key]['show_message'] = !empty($addon_version['data']['show_message']);
                    $valid_addon_services[$addon_service_key]['placeholder'] = $addon_version['data']['placeholder'] ?? '';
                    $valid_addon_services[$addon_service_key]['description'] = $addon_version['data']['description'] ?? '';
                    $valid_addon_services[$addon_service_key]['same_price'] = !empty($addon_version['data']['enable_same_price']);
                }

                if (!empty($addon_version['data']['auto_select'])) {
                    $valid_addon_services[$addon_service_key]['auto_select'] = $default_data_option_key;
                }
            }
        }

        $invalid_addon_service_keys = array_merge(array_keys($expired_addon_services), array_keys($unavailable_addon_services));

        // remove invalid addon services from valid addon services
        $valid_addon_services = array_filter(
            $valid_addon_services,
            function ($addon_service_key) use ($invalid_addon_service_keys) {
                return !in_array($addon_service_key, $invalid_addon_service_keys);
            },
            ARRAY_FILTER_USE_KEY
        );

        return [
            'data_addon_service' => $valid_addon_services,
            'addon_service_expired' => $expired_addon_services,
            'addon_service_unavailable' => $unavailable_addon_services,
        ];
    }

    private function __getSubTotalPrice($cart_items, $filter_options = []) {
        $subtotal = 0;

        // $cart_item = [
        //      'cart_item_id' => int,
        //      'cart_item' => Model_Catalog_Cart_Item ,
        //      'product_id' => int,
        //      'has_addon_service' => (>= 1) || 0,
        //      'product_type' => array,
        //      'addon_services' => [
        //          'manual' => [
        //              addon_service_id_1 => [
        //                  'addon_service_id' => addon_service_id_1,
        //                  'start_timestamp' => int,
        //                  'end_timestamp' => int,
        //                  'addon_service' => Model_Addon_Service ,
        //              ],
        //              addon_service_id_2 => ...
        //          ]
        //      ],
        //      'product_type_detail' => [
        //          [
        //              'id' => int,
        //              'ukey' => string,
        //              'title' => string,
        //          ]
        //      ],
        // ]
        foreach ($cart_items as $cart_item) {
            $subtotal += $cart_item['cart_item']->getAmount();

            if (empty($cart_item['has_addon_service'])) {
                continue;
            }

            $pack = $cart_item['cart_item']->getPackData();
            $cart_item_addon_services = $cart_item['cart_item']->getData('custom_price_data')['addon_services'] ?? [];
            $addon_service_price = 0;

            $filtered_addon_services = $this->filterAddonServices($cart_item['addon_services'],true, $filter_options);

            foreach ($cart_item_addon_services as $addon_service_id => $addon_service_options) {
                if (!is_array($filtered_addon_services['addon_service_expired']) ||
                    !is_array($filtered_addon_services['addon_service_unavailable']) ||
                    in_array($addon_service_id, array_column($filtered_addon_services['addon_service_expired'], 'id')) ||
                    in_array($addon_service_id, array_column($filtered_addon_services['addon_service_unavailable'], 'id')) ||
                    !count($addon_service_options) ||
                    empty($cart_item['cart_item_addon_services'][$addon_service_id])) {
                    continue;
                }

                $addon_service = $cart_item['cart_item_addon_services'][$addon_service_id];

                foreach ($addon_service_options as $option) {
                    $option_price = !empty($option['price']) ? intval($option['price']) : 0;

                    if ($addon_service->getData('type') == Model_Addon_Service::TYPE_VARIANT) {
                        if (!empty($pack)) {
                            //calculator discount pack
                            if ($pack['discount_type'] == Model_Catalog_Product_Pack::FIXED_AMOUNT) {
                                $option_price = max(0, $option_price * $pack['quantity'] - $pack['discount_value'] * 100);
                            } elseif ($pack['discount_type'] == Model_Catalog_Product_Pack::PERCENTAGE) {
                                $option_price = max(0, $option_price * $pack['quantity'] - ($option_price * $pack['quantity'] * $pack['discount_value'] / 100));
                            }
                        }

                        $option_price = $option_price - $cart_item['cart_item']->getPrice();
                    }

                    $addon_service_price += $option_price * $cart_item['cart_item']->getData('quantity');
                }
            }

            $subtotal += $addon_service_price;
        }

        return $subtotal;
    }

    private function __updateDataOfCartItems(&$cart_items, $options = []) {
        $this->__updateProductTypeOfCartItems($cart_items, $options);
        $this->__updateProductTypeDetailOfCartItems($cart_items);
        $this->__updateAutoAddonServiceOfCartItems($cart_items);
        $this->__updateAddonServiceOfCartItems($cart_items);
    }

    /**
     * @param Model_Catalog_Cart $cart
     * @param $reload
     * @param array $options
     * @return float|int
     */
    public function getSubtotalWithoutDiscountOfCart(Model_Catalog_Cart $cart, $reload = false, array $options = []) {
        // array = [
        //  $cart_item = [
        //      'cart_item_id' => int,
        //      'cart_item' => Model_Catalog_Cart_Item ,
        //      'product_id' => int,
        //      'has_addon_service' => (>= 1) || 0,
        //      'product_type' => array,
        //      'addon_services' => [
        //          'manual' => [
        //              addon_service_id_1 => [
        //                  'addon_service_id' => addon_service_id_1,
        //                  'start_timestamp' => int,
        //                  'end_timestamp' => int,
        //                  'addon_service' => Model_Addon_Service ,
        //              ],
        //              addon_service_id_2 => ...
        //          ]
        //      ],
        //      'product_type_detail' => [
        //          [
        //              'id' => int,
        //              'ukey' => string,
        //              'title' => string,
        //          ]
        //      ],
        //  ]
        // ]
        $cart_items = [];

        /* @var $cart_item Model_Catalog_Cart_Item */
        foreach ($cart->getLineItems($reload) as $cart_item) {
            $cart_item_id = $cart_item->getId();

            $cart_items[$cart_item_id] = [
                'cart_item_id' => $cart_item_id,
                'cart_item' => $cart_item,
                'product_id' => $cart_item->getData('product_id'),
                'has_addon_service' => count($cart_item->getData('custom_price_data')['addon_services'] ?? [])
            ];
        }

        $this->__updateDataOfCartItems($cart_items, $options);

        return $this->__getSubTotalPrice($cart_items, $options);
    }

    /**
     * @param Model_Catalog_Cart_Item $cart_item
     * @param array $options
     * @return float|int
     */
    public function getSubtotalWithoutDiscountOfCartItem(Model_Catalog_Cart_Item $cart_item, array $options = []) {
        $cart_item_id = $cart_item->getId();

        $cart_items[$cart_item_id] = [
            'cart_item_id' => $cart_item_id,
            'cart_item' => $cart_item,
            'product_id' => $cart_item->getData('product_id'),
            'has_addon_service' => count($cart_item->getData('custom_price_data')['addon_services'] ?? [])
        ];

        $this->__updateDataOfCartItems($cart_items, $options);

        return $this->__getSubTotalPrice($cart_items, $options);
    }

    /**
     * @param Model_Catalog_Cart_Item $cart_item
     * @param array $options
     * @return array|array[]
     */
    public function getAddonServiceDataOfCartItem(Model_Catalog_Cart_Item $cart_item, array $options = []) {
        $addon_service_data = [];
        $cart_item_id = $cart_item->getId();

        $cart_items[$cart_item_id] = [
            'cart_item_id' => $cart_item_id,
            'cart_item' => $cart_item,
            'product_id' => $cart_item->getData('product_id'),
            'has_addon_service' => true
        ];

        $this->__updateDataOfCartItems($cart_items, $options);

        foreach ($cart_items as $cart_item) {
            $addon_service_data = $this->filterAddonServices($cart_item['addon_services'],false, $options);
            break;
        }

        return $addon_service_data;
    }
}
