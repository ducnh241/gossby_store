<?php

class Observer_Catalog_Campaign {

    public static function preCustomerEditOrder(Model_Catalog_Order $order) {
        /* @var $line_item Model_Catalog_Order_Item */

        if (!$order->ableToEdit()) {
            return;
        }

        foreach ($order->getLineItems() as $line_item) {
            $campaign_data_idx = $line_item->getCampaignDataIdx();

            if ($campaign_data_idx === null) {
                continue;
            }

            $order_item_meta = $line_item->getOrderItemMeta();

            foreach ($line_item->getCampaignData()['print_template']['segment_source'] as $segment_source) {
                if ($segment_source['source']['type'] != 'personalizedDesign') {
                    continue;
                }

                try {
                    $product = OSC::model('catalog/product')->load($line_item->data['product_id']);

                    if (isset($product->data['product_id']) && !empty($product->data['product_id'])) {
                        $custom_data_entries = $order_item_meta->data['custom_data'];
                        $custom_data_entries[$campaign_data_idx]['text'] = <<<EOF
<div class="order-personalized-design"><div class="edit-btn" data-line-item="{$line_item->getId()}" data-order="{$order->getOrderUkey()}" data-insert-cb="initOrderCampaignDesignEditBtn">Edit design</div></div>
EOF;
                        $order_item_meta->setData('custom_data', $custom_data_entries)->lock();
                    }
                } catch (Exception $exception) {

                }

                OSC::helper('frontend/template')->addComponent('cropper', 'uploader')->push(['catalog/campaign.scss', 'personalizedDesign/common.scss'], 'css')->push(['catalog/campaign.js', 'personalizedDesign/common.js', '[core]community/jquery.serialize-object.js'], 'js');
                break;
            }
        }
    }

    public static function orderVerifyLineItemToCreate($params) {
        /* @var $line_item Model_Catalog_Order_Item */
        $personalized_design_ids = [];
        $design_last_update = [];

        $cart_item_ids = array_keys($params['line_items']);

        $list_item_collection = OSC::model('catalog/cart_item')->getCollection()->load($cart_item_ids);

        foreach ($params['line_items'] as $cart_item_id  => $line_item) {
            $cart_item = $list_item_collection->getItemByPK($cart_item_id);

            if ($cart_item->isCrossSellMode()) {
                continue;
            }

            if(!$cart_item->checkDeignIdInProduct()) {
                throw new Exception("We are sorry! The design of this item has been updated during your personalization process. Please remove the item from the cart and personalize it again to make sure you’ll have the product with up-to-date design", 1000);
            }

            $campaign_data_idx = $cart_item->getCampaignDataIdx();

            if ($campaign_data_idx === null) {
                continue;
            }

            $custom_data_entries = $cart_item->data['custom_data'];

            foreach ($custom_data_entries[$campaign_data_idx]['data']['print_template']['segment_source'] as $segment_key => $segment_source) {
                if ($segment_source['source']['type'] != 'personalizedDesign') {
                    continue;
                }

                $personalized_design_ids[] = $segment_source['source']['design_id'];
                $design_last_update[$segment_source['source']['design_id']] = $segment_source['source']['design_last_update'];
            }
        }
        // check last update design to verify
        $list_design_last_update = OSC::helper('catalog/campaign_design')->checkValidateByLastUpdateDesign($personalized_design_ids, $design_last_update);

        if (!$list_design_last_update && count($personalized_design_ids) > 0) {
            $personalized_design_collection = OSC::helper('catalog/campaign_design')->loadPersonalizedDesigns($personalized_design_ids);
        }

        foreach ($params['line_items'] as $cart_item_id => $line_item) {
            $product = $line_item->getProduct();

            $campaign_config = $product->data['meta_data']['campaign_config']['print_template_config'];

            $cart_item = $list_item_collection->getItemByPK($cart_item_id);

            $campaign_data_idx = $cart_item->getCampaignDataIdx();

            if ($campaign_data_idx === null || $cart_item->getCrossSellDataIdx() !== null) {
                continue;
            }

            $custom_data_entries = $cart_item->data['custom_data'];

            if (!$list_design_last_update) {
                foreach ($custom_data_entries[$campaign_data_idx]['data']['print_template']['segment_source'] as $segment_key => $segment_source) {
                    if ($segment_source['source']['type'] != 'personalizedDesign') {
                        continue;
                    }

                    $personalized_design = $personalized_design_collection->getItemByPK($segment_source['source']['design_id']);

                    if (!($personalized_design instanceof Model_PersonalizedDesign_Design)) {
                        throw new Exception('Cannot load personalized design [' . $segment_source['source']['design_id'] . ']');
                    }

                    $print_template_config_index = array_search($custom_data_entries[$campaign_data_idx]['data']['print_template']['print_template_id'], array_column($campaign_config, 'print_template_id'));

                    $print_template_config_update = is_numeric($print_template_config_index) ? $campaign_config[$print_template_config_index] : [];

                    if (!empty($print_template_config_update) && (isset($print_template_config_update['segments'][$segment_key]) && $print_template_config_update['segments'][$segment_key]['source']['timestamp'] != $custom_data_entries[$campaign_data_idx]['data']['print_template']['segment_source'][$segment_key]['source']['timestamp'])) {
                        $custom_data_entries[$campaign_data_idx]['data']['print_template']['segment_source'][$segment_key]['source']['position'] = $print_template_config_update['segments'][$segment_key]['source']['position'];
                        $custom_data_entries[$campaign_data_idx]['data']['print_template']['segment_source'][$segment_key]['source']['dimension'] = $print_template_config_update['segments'][$segment_key]['source']['dimension'];
                        $custom_data_entries[$campaign_data_idx]['data']['print_template']['segment_source'][$segment_key]['source']['rotation'] = $print_template_config_update['segments'][$segment_key]['source']['rotation'];
                        $custom_data_entries[$campaign_data_idx]['data']['print_template']['segment_source'][$segment_key]['source']['timestamp'] = $print_template_config_update['segments'][$segment_key]['source']['timestamp'];
                    }

                    try {
                        $custom_data_entries[$campaign_data_idx]['data']['print_template']['segment_source'][$segment_key]['source']['svg'] = OSC::helper('personalizedDesign/common')->renderSvg($personalized_design, $segment_source['source']['config']);
                        $custom_data_entries[$campaign_data_idx]['data']['print_template']['segment_source'][$segment_key]['source']['design_last_update'] = $personalized_design->data['modified_timestamp'];
                    } catch (Exception $ex) {
                        $ex_message = $ex->getMessage();

                        if ($ex->getCode() === Helper_PersonalizedDesign_Common::EX_CODE_OPT_NOT_EXISTS) {
                            $ex_message = 'We are sorry! Some personalized design options of ' .
                                '"' .
                                $line_item->getProduct()->getProductTitle() .
                                '" ' .
                                "you've chosen are no longer available. Please remove this product from the cart and personalize it again before going back to checkout page.";
                        }

                        throw new Exception($ex_message);
                    }
                }
            }

            $line_item_meta = OSC::model('catalog/order_item_meta')->setData([
                'custom_data' => $custom_data_entries
            ])->save();

            $product_type = $cart_item->getVariant()->getProductTypeVariant()->getProductType()->getUkey() ?? '';

            $line_item->setData(['order_item_meta_id' => $line_item_meta->getId(),'product_type' => $product_type]);
        }
    }

    public static function orderVerifyUpsaleItemToCreate($params) {
        /* @var $line_item Model_Catalog_Order_Item */
        $personalized_design_ids = [];

        $custom_data_entries = $params['custom_data'];

        foreach ($custom_data_entries['data']['print_template']['segment_source'] as $segment_key => $segment_source) {
            if ($segment_source['source']['type'] != 'personalizedDesign') {
                continue;
            }

            $personalized_design_ids[] = $segment_source['source']['design_id'];
        }

        if (count($personalized_design_ids) > 0) {
            $personalized_design_collection = OSC::model('personalizedDesign/design')->getCollection()->load($personalized_design_ids);
        }

        $custom_data_entries = $params['custom_data'];

        foreach ($custom_data_entries['data']['print_template']['segment_source'] as $segment_key => $segment_source) {
            if ($segment_source['source']['type'] != 'personalizedDesign') {
                continue;
            }

            $personalized_design = $personalized_design_collection->getItemByPK($segment_source['source']['design_id']);

            if (!($personalized_design instanceof Model_PersonalizedDesign_Design)) {
                throw new Exception('Cannot load personalized design [' . $segment_source['source']['design_id'] . ']');
            }

            try {
                $custom_data_entries['data']['print_template']['segment_source'][$segment_key]['source']['svg'] = OSC::helper('personalizedDesign/common')->renderSvg($personalized_design, $segment_source['source']['config']);
            } catch (Exception $ex) {
                $ex_message = $ex->getMessage();

                if ($ex->getCode() === Helper_PersonalizedDesign_Common::EX_CODE_OPT_NOT_EXISTS) {
                    $ex_message = "We are sorry! Some personalized design options of " .
                        '"' .
                        $params['line_item']->getProduct()->getProductTitle() .
                        '" ' .
                        "you've chosen are no longer available. Please remove this product from the cart and personalize it again before going back to checkout page.";
                }

                throw new Exception($ex_message);
            }
        }

        $line_item_meta = OSC::model('catalog/order_item_meta')->setData([
            'custom_data' => [$custom_data_entries]
        ])->save();

        $params['line_item']->setData([
            'order_item_meta_id' => $line_item_meta->getId()
        ]);
    }

    public static function afterPlaceOrder(Model_Catalog_Order $order) {
        try {
            OSC::model('catalog/order_bulkQueue')->setData([
                'member_id' => 1,
                'shop_id' => OSC::getShop()->getId(),
                'action' => 'render_design_after_order_created',
                'order_master_record_id' => $order->getId(),
                'queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['queue'],
                'queue_data' => ['order_id' => $order->getId()]
            ])->save();

            OSC::core('cron')->addQueue('catalog/order_renderDesignAfterOrderCreated', null, ['ukey' => 'catalog/order_renderDesignAfterOrderCreated', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);
        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') === false) {
                throw new Exception($ex->getMessage());
            }
        }
    }

    /**
     * @param Model_Catalog_Order_Item $line_item
     * @return array
     * @throws OSC_Database_Model_Exception
     */
    public static function addRenderDesignQueue(Model_Catalog_Order_Item $line_item) {
        $campaign_data = $line_item->getCampaignData();

        if (!$campaign_data) {
            return [];
        }

        $design_ids = [];
        foreach ($campaign_data['print_template']['segment_source'] as $design_key => $design) {
            if (!isset($design['source']['design_id']) || intval($design['source']['design_id']) < 1 ) {
                continue;
            }

            $design_ids[] = $design['source']['design_id'];
            $personalized_design_options[$design['source']['design_id']] = $design['source']['config'];
        }

        $segment_sources = OSC::helper('catalog/campaign_design')->getSegmentSources($campaign_data['print_template']['segment_source'], $personalized_design_options, ['render_design']);

        $print_template_id = $campaign_data['print_template']['print_template_id'] ?? 0;

        try {
            $print_template = OSC::model('catalog/printTemplate')->load($print_template_id);
        } catch (Exception $exception) {
            throw new Exception('No print template found');
        }

        $member_data = OSC::helper('catalog/campaign')->getVendorAndMemberInLineItem($line_item);

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
                'member_email' => $member_data['member_email'],
                'vendor_email' => $member_data['vendor_email'],
                'print_template_id' => $print_template_id,
                'design_timestamp' => isset($campaign_data['design_timestamp']) ? $campaign_data['design_timestamp'] : $line_item->data['added_timestamp'],
                'order_added_timestamp' => $line_item->data['added_timestamp'],
                'design_data' => OSC::helper('catalog/campaign_design')->getDesignRenderData($line_item, $print_template, $segment_sources, $line_item->getCampaignData()['product_type']['options'])
            ]
        ])->save();

        return $design_ids;
    }

    public static function validateCartItem($params) {
        /* @var $product Model_Catalog_Product */
        $product_variant = $params['variant'];
        $product = $product_variant->getProduct();

        if (!($product instanceof Model_Catalog_Product)) {
            return null;
        }

        if (!$product->isCampaignMode()) {
            return null;
        }

        if (!isset($params['custom_data']['campaign']) || !is_array($params['custom_data']['campaign'])) {
            throw new Exception('Please complete form before add to cart');
        }

        $print_template_id = $params['custom_data']['campaign']['print_template_id'] ?? 0;

        try {
            $collection_supplier_rel = OSC::model("catalog/supplierVariantRel")->getCollection()->getSuppliersByProductTypeAndPrintTemplate($product_variant->data['meta_data']['campaign_config']['product_type_variant_id'], $print_template_id);
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
            $product_type_variants = OSC::model('catalog/productType_variant')->getCollection()->load([$product_variant->data['meta_data']['campaign_config']['product_type_variant_id']]);
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
            'product_type_variant_id' => $product_variant->data['meta_data']['campaign_config']['product_type_variant_id'],
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
        $image_ids = [];

        $campaign_segments = $product->data['meta_data']['campaign_config']['print_template_config'][array_search($print_template_id, array_column($product->data['meta_data']['campaign_config']['print_template_config'], 'print_template_id'))];
        $campaign_segments = $campaign_segments['segments'] ?? [];

        if (!empty($campaign_segments)) {
            foreach ($campaign_segments as $design_key => $design_data) {
                switch ($design_data['source']['type']) {
                    case 'personalizedDesign':
                        if (!isset($params['custom_data']['campaign']['personalizedDesign']) ||
                            !is_array($params['custom_data']['campaign']['personalizedDesign']) ||
                            count($params['custom_data']['campaign']['personalizedDesign']) < 1
                        ) {
                            throw new Exception('Missing personalized data for design');
                        }

                        $design_id = $design_data['source']['design_id'];
                        if (!isset($params['custom_data']['campaign']['personalizedDesign']['_' . $design_id]) ||
                            !is_array($params['custom_data']['campaign']['personalizedDesign']['_' . $design_id])
                        ) {
                            throw new Exception('Missing personalized data for design');
                        }

                        $personalized_design_ids[] = $design_id;

                        break;
                    case 'image':
                        $image_ids[] = $design_data['source']['image_id'];

                        break;
                    default:
                        throw new Exception('Type of design is incorrect');
                }
            }
        }

        if (count($image_ids) > 0) {
            $image_collection = OSC::model('catalog/campaign_imageLib_item')->getCollection()->load($image_ids);
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

                        $personalizedDesign = static::validatePersonalizedDesign($personalized_design, $params['custom_data']['campaign']['personalizedDesign']['_' . $segment_source['source']['design_id']]);

                        foreach($personalizedDesign as $k => $v) {
                            $campaign_data['print_template']['segment_source'][$segment_key]['source'][$k] = $v;
                        }

                        break;
                    case 'image':
                        $image = $image_collection->getItemByPK($segment_source['source']['image_id']);

                        if (!($image instanceof Model_Catalog_Campaign_ImageLib_Item)) {
                            throw new Exception('Cannot load image item [' . $segment_source['source']['image_id'] . ']');
                        }

                        $campaign_data['print_template']['segment_source'][$segment_key]['source']['file_name'] = $image->data['filename'];

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

    public static function validatePersonalizedDesign($personalized_design, $config) {
        $form_data = $personalized_design->extractPersonalizedFormData();
        $forms = $form_data['components'];

        /* Check if design is not personalized, skip check */
        if (count($config) > 0 && (!is_array($forms) || count($forms) < 1)) {
            throw new Exception('The design [' . $personalized_design->getId() . '] haven\'t any personalize form');
        }

        $validated_config = [];

        try {
            Observer_PersonalizedDesign_Frontend::validateConfig($forms, $config, $validated_config,'');
        } catch (Exception $ex) {
            throw new Exception('The design got error: ' . $ex->getMessage());
        }

        return [
            'svg' => OSC::helper('personalizedDesign/common')->renderSvg($personalized_design, $validated_config),
            'config' => $validated_config,
            'config_preview' => OSC::helper('personalizedDesign/common')->fetchConfigPreview($personalized_design, $validated_config),
            'design_last_update' => $personalized_design->data['modified_timestamp']
        ];
    }
}