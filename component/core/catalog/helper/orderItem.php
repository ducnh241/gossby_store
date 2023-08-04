<?php

class Helper_Catalog_OrderItem extends OSC_Object
{
    const CREATE_RECORD_AIRTABLE = 'create_record_airtable';

    /**
     * @param $product_ids
     * @return void
     * @throws OSC_Exception_Runtime
     */
    public function putOrderItemToAirtableByProductId($product_ids)
    {

        try {

            $order_items = OSC::model('catalog/order_item')->getCollection()
                ->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN)
                ->addCondition('shop_id', OSC::getShop()->getId())
                ->addCondition('additional_data', '%"sync_airtable_flag":1%', OSC_Database::OPERATOR_NOT_LIKE)
                ->addCondition('added_timestamp', '1660165200', OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL) // >= '2022-08-11 00:00:00'
                ->load();

            $this->addToOrderAirtableBulkQueue($order_items, $product_ids);

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

    }

    /**
     * @param OSC_Database_Model_Collection $order_items
     * @param $product_ids
     * @return void
     * @throws OSC_Exception_Runtime
     */
    public function addToOrderAirtableBulkQueue(OSC_Database_Model_Collection $order_items, $product_ids) {
        $order_ids = [];
        $sub_record_item = [];

        $d2_products = OSC::model('d2/product')->getCollection()
            ->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN)
            ->load()->preLoadCampaignProduct();

        $campaign_product_links = [];

        /* @var $product Model_D2_Product */
        foreach ($d2_products as $product) {
            if ($product->getCampaignProduct()) {
                $campaign_product_links[$product->getCampaignProduct()->getId()] = $product->getCampaignProduct()->getDetailUrl();
            } else {
                $campaign_product_links[$product->data['product_id']] = '';
            }
        }

        /* @var $order_item Model_Catalog_Order_Item
         * @var $DB_MASTER OSC_Database_Adapter
         */

        $DB_MASTER = OSC::core('database')->getAdapter('db_master');
        $DB_MASTER->query('SELECT * FROM osc_core_setting where setting_key = "tag/reason_resend"', null, 'fetch_queue');
        $reason = $DB_MASTER->fetchArray('fetch_queue');
        $reason_values = OSC::decode($reason['setting_value'] ?? []);
        $DB_MASTER->free('fetch_queue');

        foreach ($order_items as $order_item) {

            try {
                if ($order_item->isCrossSellMode() ||
                    !array_key_exists($order_item->data['product_id'], $campaign_product_links) ||
                    $order_item->data['shop_id'] != OSC::getShop()->getId() ||
                    $order_item->data['process_quantity'] != 0 ||
                    $order_item->data['fulfilled_quantity'] != 0 ||
                    $order_item->data['refunded_quantity'] != 0
                ) {
                    continue;
                }

                $additional_data = $order_item->data['additional_data'];

                // validate order_item synced
                if (isset($additional_data['sync_airtable_flag'])) {
                    continue;
                }

                // validate order_item resend
                if (isset($additional_data['resend']['resend'])) {

                    $reason_group = $additional_data['resend']['reason_group'] ?? '';

                    $tag_reason = array_filter($reason_values, function ($reason) use ($reason_group) {
                        return $reason['name'] == $reason_group;
                    });
                    $tags_edit_design = array_values($tag_reason)[0]['tags_edit_design'] ?? [];

                    if (!in_array($additional_data['resend']['reason'] ?? '', $tags_edit_design)) { // back when reason not change design
                        continue;
                    }
                }

                $additional_data['is_order_d2'] = 1;
                $order_item_data = [
                    'additional_data' => $additional_data
                ];

                if (isset($additional_data['resend']['resend'])) {
                    $order_item_data['design_url'] = [];
                }

                $order_item->setData($order_item_data)->save();

                $total_quantity = $order_item->data['other_quantity'] ?
                    ($order_item->data['quantity'] * $order_item->data['other_quantity']) : $order_item->data['quantity'];

                try {
                    $product_type_model = OSC::model('catalog/product_bulkQueue')->loadByUKey($order_item->data['product_type']);
                    $product_type = $product_type_model->data['title'];
                }catch (Exception $ex) {
                    $product_type = $order_item->data['product_type'];
                }

                $sub_record_item[$order_item->getId()] = [
                    'fields' => [
                        'Shop ID' => $order_item->data['shop_id'],
                        'Order ID' => intval($order_item->data['order_master_record_id']),
                        'Product ID' => $order_item->data['product_id'],
                        'Variant ID' => $order_item->data['variant_id'],
                        'Product Name' => $order_item->data['title'],
                        'Product Link' => $campaign_product_links[$order_item->data['product_id']] ?? '',
                        'Variant Title' => $order_item->getVariantOptionsText() ?? '',
                        'Product Type' => $product_type,
                        'SKU' => $order_item->data['sku'],
                        'Quantity' => $order_item->data['quantity'],
                        'Pack' => "{$order_item->data['other_quantity']}",
                        'Total Quantity' => $total_quantity,
                        'Order Line ID' => "{$order_item->getId()}",
                        'Verify Address' => ($order_item->data['additional_data']['verify_address'] ?? 0) ? 'Good' : '',
                        'Vendor Name' => $order_item->data['vendor'],
                        'Has special character' => $order_item->hasSpecialCharacter() ? 'YES' : 'NO',
                        'Added By' => 'AUTO',
                        'Order Type' => isset($additional_data['resend']['resend']) ? 'RESEND' : 'NEW',
                        '9p_photo#1' => $additional_data['9p_photo#1'] ?? '',
                        '9p_photo#2' => $additional_data['9p_photo#2'] ?? '',
                        '9p_photo#3' => $additional_data['9p_photo#3'] ?? '',
                        '9p_photo#4' => $additional_data['9p_photo#4'] ?? '',
                        '9p_photo#5' => $additional_data['9p_photo#5'] ?? '',
                        '9p_photo#others' => $additional_data['9p_photo#others'] ?? '',
                        '9p_photo_flows' => implode("\n", $additional_data['flow_name'] ?? [])
                    ]
                ];

                $option_personalized = OSC::helper('d2/common')->exportOptionPersonalized($order_item);

                foreach ($option_personalized as $key => $options) {
                    if (in_array($key, ['design_id'])) {
                        $sub_record_item[$order_item->getId()]['fields'][$key] = implode(',', $options);
                    } else if (in_array($key, ['ps_photo_others', 'ps_clipart'])) {
                        $sub_record_item[$order_item->getId()]['fields'][$key] = implode("\n", $options);
                    } else {
                        foreach ($options as $field => $values) {
                            if ($key == 'ps_photo_opt') {
                                $sub_record_item[$order_item->getId()]['fields'][$field] = implode("\n", $values);
                            } else {
                                $sub_record_item[$order_item->getId()]['fields'][$field] = implode(',', $values);
                            }
                        }
                    }
                }

                $order_ids[$order_item->data['order_master_record_id']][] = $order_item->getId();
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

        }

        $orders = [];
        if (!empty($order_ids)) {
            $orders = OSC::model('catalog/order')->getCollection()->load(array_unique(array_keys($order_ids)));
        }

        $order_bulk_queues = [];
        $ukeys = [];

        /* @var $order Model_Catalog_Order */
        foreach ($orders as $order) {

            // verify order, not sync order item when order_status != open
            if (strtolower($order->data['order_status']) != strtolower(Model_Catalog_Order::ORDER_STATUS['open'])) {
                foreach ($order_ids[$order->getId()] as $order_item_id) {
                    unset($sub_record_item[$order_item_id]);
                }
                continue;
            }

            foreach ($order_ids[$order->getId()] as $key => $order_item_id) {
                $sub_record_item[$order_item_id]['fields']['Total Price'] = $key == 0 ? $order->getFloatTotalPrice() : 0;
                $sub_record_item[$order_item_id]['fields']['Order Date'] = gmdate('Y-m-d\TH:i:s.000\Z', $order->data['added_timestamp']);
                $sub_record_item[$order_item_id]['fields']['Order Detail Link'] = $order->getDetailUrl();
                $sub_record_item[$order_item_id]['fields']['Order Code'] = $order->data['code'];
                $sub_record_item[$order_item_id]['fields']['Shipping Fullname'] = $order->data['shipping_full_name'];
                $sub_record_item[$order_item_id]['fields']['Phone'] = $order->data['shipping_phone'];
                $sub_record_item[$order_item_id]['fields']['Email'] = $order->data['email'];
                $sub_record_item[$order_item_id]['fields']['Address1'] = $order->data['shipping_address1'];
                $sub_record_item[$order_item_id]['fields']['Address2'] = $order->data['shipping_address2'];
                $sub_record_item[$order_item_id]['fields']['City'] = $order->data['shipping_city'];
                $sub_record_item[$order_item_id]['fields']['Province'] = $order->data['shipping_province'];
                $sub_record_item[$order_item_id]['fields']['Province Code'] = $order->data['shipping_province_code'];
                $sub_record_item[$order_item_id]['fields']['Zip'] = $order->data['shipping_zip'];
                $sub_record_item[$order_item_id]['fields']['Country'] = $order->data['shipping_country'];
                $sub_record_item[$order_item_id]['fields']['Order Status'] = $order->data['order_status'];

                $ukey = static::CREATE_RECORD_AIRTABLE . ':' . $order_item_id;
                $ukeys[] = $ukey;

                $order_bulk_queues[] = [
                    'ukey' => $ukey,
                    'member_id' => 1,
                    'action' => static::CREATE_RECORD_AIRTABLE,
                    'queue_data' => $sub_record_item[$order_item_id]
                ];
            }
        }

        if (!empty($ukeys)) {
            try {

                $collection = OSC::model('catalog/product_bulkQueue')->getCollection()
                    ->addCondition('queue_flag', Model_Catalog_Product_BulkQueue::QUEUE_FLAG['error'])
                    ->addCondition('ukey', $ukeys, OSC_Database::OPERATOR_IN)->load();

                if ($collection->length() > 0) {
                    $collection->delete();
                }

                if (OSC::model('catalog/product_bulkQueue')->insertMulti($order_bulk_queues) > 0) {
                    OSC::core('cron')->addQueue('catalog/order_syncAirTable', null, ['ukey'=> 'catalog/order_syncAirTable','requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60*60]);
                }
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
        }
    }

    /**
     * @param Model_Catalog_Order $order
     * @return void
     * @throws OSC_Database_Model_Exception
     */
    public function renderDesignSvgBeta(Model_Catalog_Order $order, $skip_push_d2 = false) {
        $url_personalized = OSC_ENV == 'production' ? 'https://personalizeddesign.9prints.com/storage' : 'https://personalizeddesign-v2.9prints.com/storage';

        $options = ['render_design', 'remove_pattern_layer'];

        try {
            $product_ids = [];
            foreach ($order->getLineItems() as $line_item) {
                if (!$line_item->isSemitestMode()) {
                    continue;
                }

                $order_id = $order->getId();
                $order_item_id = $line_item->getId();
                $product_ids[] = $line_item->data['product_id'];
                try {
                    $order_item_meta = $line_item->getOrderItemMeta();
                    if (!empty($order_item_meta->data['custom_data'])) {
                        foreach ($order_item_meta->data['custom_data'] as $custom_data) {
                            if ($custom_data['key'] == 'personalized_design' && $custom_data['type'] == 'semitest') {
                                foreach ($custom_data['data'] as $data) {
                                    if (!empty($data['design_svg_beta'])) {
                                        $design = OSC_Database_Model::getPreLoadedModel('personalizedDesign/design', $data['design_id']);

                                        if (!($design instanceof Model_PersonalizedDesign_Design)) {
                                            throw new Exception('Cannot load personalized design');
                                        }

                                        $data_clip_art_svg = [
                                            'design' => $design,
                                            'custom_config' => $data['config'],
                                            'options' => $options
                                        ];

                                        $queue_data = [
                                            'design_id' => $data['design_id'],
                                            'line_item_id' => $order_item_id,
                                            'svg_content' => $data['design_svg_beta'],
                                            'svg_clip_art_content' => OSC::helper('personalizedDesign/common')->renderClipArtSvg($data_clip_art_svg),
                                            'svg_preview_content' => $data['design_svg'],
                                        ];

                                        if (!empty($data['width']) && !empty($data['height'])) {
                                            $queue_data['width'] = $data['width'];
                                            $queue_data['height'] = $data['height'];
                                        }

                                        $ukey = 'personalizedDesign/renderDesignSvgBeta:' . md5(OSC::encode($queue_data));

                                        try {
                                            $model_bulk_queue = OSC::model('catalog/product_bulkQueue')->loadByUKey($ukey);
                                            $model_bulk_queue->delete();
                                        } catch (Exception $ex) {
                                            if ($ex->getCode() != 404) {
                                                throw new Exception($ex->getMessage());
                                            }
                                        }

                                        OSC::model('catalog/product_bulkQueue')->setData([
                                            'member_id' => 1,
                                            'ukey' => $ukey,
                                            'action' => 'renderDesignSvgBeta',
                                            'queue_data' => $queue_data
                                        ])->save();
                                    }

                                    if (isset($data['config_preview']) && !$skip_push_d2) {
                                        $layers = [];
                                        foreach ($data['config_preview'] as $key => $item) {
                                            if (($item['type'] == 'imageUploader') && !empty($item['flow_id'])) {
                                                $value = OSC::decode($item['value']);
                                                $layers[] = [
                                                    'key' => $key,
                                                    'name' => $item['layer'],
                                                    'value' => $url_personalized . '/' . $value['file'],
                                                    'flowId' => $item['flow_id']
                                                ];
                                            }
                                        }

                                        if (!empty($layers)) {
                                            $queue_data = [
                                                'layers' => $layers,
                                                'order_id' => $order_id,
                                                'order_code' => $order->data['code'],
                                                'order_item_id' => $order_item_id
                                            ];

                                            $key = md5(OSC::encode($layers) . ':' . time());
                                            OSC::core('cron')->addQueue('d2/flows',
                                                $queue_data,
                                                [
                                                    'ukey' => 'catalog/order_analytic:' . $key,
                                                    'requeue_limit' => -1,
                                                    'skip_realtime',
                                                    'estimate_time' => 60 * 60,
                                                    'running_time' => 120
                                                ]
                                            );

                                            OSC::helper('d2/common')->writeLog($order_id, $order_item_id, OSC::encode($queue_data), 'Add queue successfully');
                                        } else {
                                            OSC::helper('d2/common')->writeLog($order_id, $order_item_id, '', 'Flow is not existed');
                                        }
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $exception) {
                    OSC::helper('d2/common')->writeLog($order_id, $order_item_id, '', $exception->getMessage());
                }
            }


            OSC::helper('catalog/orderItem')->addToOrderAirtableBulkQueue($order->getLineItems(), $product_ids);

            OSC::core('cron')->addQueue('personalizedDesign/renderDesignSvgBeta', null, [
                'ukey' => 'personalizedDesign/renderDesignSvgBeta', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 20
            ]);
        } catch (Exception $exception) {
            OSC::helper('core/common')->writeLog('renderDesignSvgBeta error', $exception->getMessage());
        }
    }

    public function updateOrderItemMetaOfList(&$order_items_information, $order_item_metas) {
        foreach ($order_items_information as &$order_item_information) {
            if (empty($order_item_information['order_item_meta_id'])) {
                continue;
            }

            $order_item_meta_id = $order_item_information['order_item_meta_id'];

            foreach ($order_item_metas as $order_item_meta) {
                if ($order_item_meta_id == $order_item_meta->getData('meta_id')) {
                    $order_item_information['order_item_meta'] = $order_item_meta;
                    $order_item_information['order_item']->setOrderItemMeta($order_item_meta);
                    $order_item_information['is_order_item_cross_sell_mode'] = $order_item_information['order_item']->isCrossSellMode();

                    break;
                }
            }
        }
    }

    public function updateProductOfList(&$order_items_information, $product_ids) {
        $product_model_key = 'catalog/product';
        OSC_Database_Model::preLoadModelData($product_model_key, $product_ids);

        foreach ($order_items_information as &$order_item_information) {
            if (empty($order_item_information['product_id'])) {
                continue;
            }

            $order_item_information['product'] = OSC_Database_Model::getPreLoadedModel(
                $product_model_key,
                $order_item_information['product_id']
            );
        }
    }

    public function updateProductVariantOfList(&$order_items_information, $product_ids) {
        $product_variant_model_key = 'catalog/product_variant';
        $product_type_variant_model_key = 'catalog/productType_variant';
        $product_type_variant_ids = [];

        OSC_Database_Model::preLoadModelData($product_variant_model_key, $product_ids);

        foreach ($order_items_information as &$order_item_information) {
            if (empty($order_item_information['variant_id'])) {
                continue;
            }

            $order_item_information['product_variant'] = OSC_Database_Model::getPreLoadedModel(
                $product_variant_model_key,
                $order_item_information['variant_id']
            );

            $product_type_variant_ids[] = $order_item_information['product_variant']->getData('product_type_variant_id');
        }

        if (!$product_type_variant_ids) {
            return;
        }

        OSC_Database_Model::preLoadModelData($product_type_variant_model_key, $product_type_variant_ids);

        foreach ($order_items_information as &$order_item_information) {
            $order_item_information['product_variant'] = OSC_Database_Model::getPreLoadedModel(
                $product_variant_model_key,
                $order_item_information['variant_id']
            );

            $product_type_variant_id = $order_item_information['product_variant']->getData('product_type_variant_id');

            if (empty($product_type_variant_id)) {
                continue;
            }

            $order_item_information['product_type_variant'] = OSC_Database_Model::getPreLoadedModel(
                $product_type_variant_model_key,
                $product_type_variant_id
            );
        }
    }
}
