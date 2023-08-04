<?php

class Controller_Catalog_Api extends Abstract_Core_Controller_Api {

    public function actionCampaignRerenderDesign() {
        /* @var $DB OSC_Database_Adapter */

        $line_item_id = intval($this->_request->get('line_item_id'));

        if ($line_item_id < 1) {
            $this->_ajaxError('Syncing data is incorrect');
        }

        $campaign_data = $this->_request->get('campaign_data');

        try {
            $line_item = OSC::model('catalog/order_item')->load($line_item_id);

            $campaign_data_idx = $line_item->getCampaignDataIdx();

            if ($campaign_data_idx === null) {
                throw new Exception('The line item is not campaign');
            }
            $line_item_meta = $line_item->getOrderItemMeta();

            $custom_data_entries = $line_item_meta->data['custom_data'];

            $custom_data_entries[$campaign_data_idx] = $campaign_data;

            $line_item_meta->setData(['custom_data' => $custom_data_entries])->lock();

            $line_item->setData(['design_url' => []])->save();

            try {
                OSC::helper('personalizedDesign/common')->checkOverflowPersonalizedItem($line_item);
            } catch (Exception $ex) {

            }

            Observer_Catalog_Campaign::addRenderDesignQueue($line_item);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse();
    }


    public function actionResyncCampaignDesign() {
        /* @var $DB OSC_Database_Adapter */

        $line_item_id = intval($this->_request->get('line_item_id'));

        if ($line_item_id < 1) {
            $this->_ajaxError('Syncing data is incorrect');
        }

        try {
            $line_item = OSC::model('catalog/order_item')->load($line_item_id);

            Observer_Catalog_Campaign::addRenderDesignQueue($line_item);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse();
    }

    public function actionCampaignGetEditDesignFrmData() {
        /* @var $line_item Model_Catalog_Order_Item */

        $line_item_id = intval($this->_request->get('line_item_id'));

        if ($line_item_id < 1) {
            $this->_ajaxError('Syncing data is incorrect');
        }

        $campaign_data = $this->_request->get('campaign_data');

        try {
            $line_item = OSC::model('catalog/order_item')->load($line_item_id);

            $this->_ajaxResponse(OSC::helper('catalog/campaign')->orderLineItemGetDesignEditFrmData($line_item, $campaign_data));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionGetDesignFrmData() {
        /* @var $line_item Model_Catalog_Order_Item */

        $design_id = intval($this->_request->get('design_id'));

        if ($design_id < 1) {
            $this->_ajaxError('Syncing data is incorrect');
        }

        try {
            $model = OSC::model('personalizedDesign/design')->load($design_id);
            $form_data = $model->extractPersonalizedFormData();

            $this->_ajaxResponse([
                'id' => $design_id,
                'document_type' => $model->data['design_data']['document'],
                'components' => $form_data['components'],
                'image_data' => $form_data['image_data'],
            ]);

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getCode() == 404 ? 'Design is not exist' : $ex->getMessage(), $ex->getCode());
        }
    }

    public function actionCampaignVerifyNewDesignData() {
        /* @var $line_item Model_Catalog_Order_Item */

        $line_item_id = intval($this->_request->get('line_item_id'));

        if ($line_item_id < 1) {
            $this->_ajaxError('Syncing data is incorrect');
        }

        try {
            $this->_ajaxResponse(OSC::helper('catalog/campaign')->orderLineItemVerifyNewDesignData($this->_request->get('campaign_data'), $this->_request->get('new_config'), $this->_request->get('reset_design_area') == 1, $this->_request->get('product_id'), true));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionGetDiscountCode() {
        try {
            $discount_value = $this->_request->get('discount_value');
            $discount_type = $this->_request->get('discount_type');
            $usage_limit = $this->_request->get('usage_limit');
            $deactive_timestamp = $this->_request->get('deactive_timestamp');
            $prerequisite_subtotal = $this->_request->get('prerequisite_subtotal', 0);
            $data_discount = OSC::helper('catalog/discountCode')->genDiscountCodesByMaster([[
                'discount_value' => $discount_value,
                'discount_type' => $discount_type,
                'usage_limit' => $usage_limit,
                'deactive_timestamp' => $deactive_timestamp,
                'prerequisite_subtotal' => $prerequisite_subtotal ?? 0
            ]]);

            $this->_ajaxResponse($data_discount[0]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionGetMultiDiscountCode() {
        try {
            $discount_codes = $this->_request->get('discount_codes');
            if (empty($discount_codes)) {
                throw new Exception('Discount codes are not empty');
            }
            $results = OSC::helper('catalog/discountCode')->genDiscountCodesByMaster($discount_codes);

            $this->_ajaxResponse($results);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionV2UpdateCampaignMockup() {
        /* @var $DB OSC_Database_Adapter */
        /* @var $product Model_Catalog_Product */

        $store_info = OSC::getStoreInfo();

        $product_id = intval($this->_request->get('product_id'));
        $print_template_id = intval($this->_request->get('print_template_id'));
//        $mockup_url = $this->_request->get('mockup_url');
        $mockup_ukey = $this->_request->get('mockup_ukey');
        $callback_data = $this->_request->get('callback_data');

        if ($product_id < 1 || !$print_template_id || !$mockup_ukey) {
            $this->_ajaxError('Syncing data is incorrect');
        }

        $mockup_file_name = 'catalog/campaign/' . $product_id . '/' . $print_template_id . '/' . $mockup_ukey . '.png';
//        $mockup_file_name_s3 = OSC::core('aws_s3')->getStoragePath($mockup_file_name);
//        $tmp_file_name = 'mockup/' . md5($mockup_url) . '.png';
//        $tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($tmp_file_name);

        try {
            try {
                $product = OSC::model('catalog/product')->load($product_id);
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }

                $this->_ajaxResponse();
            }

            if (!$product->isCampaignMode()) {
                throw new Exception('The product is not campaign');
            }

            try {
                $image = OSC::model('catalog/product_image')->loadByUKey($mockup_ukey);
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }

//                OSC_Storage::tmpSaveFile($mockup_url, $tmp_file_name);
//
//                try {
//                    OSC::imageIsNotCorrupt(OSC_Storage::tmpGetFilePath($tmp_file_name));
//                } catch (Exception $ex) {
//                    @unlink(OSC_Storage::tmpGetFilePath($tmp_file_name));
//                    throw new Exception($ex->getMessage());
//                }
//
//                if (!OSC::core('aws_s3')->doesObjectExist($tmp_file_name_s3)) {
//
//                    $options = [
//                        'overwrite' => false,
//                        'permission_access_file' => 'public-read'
//                    ];
//
//                    OSC::core('aws_s3')->upload(OSC_Storage::tmpGetFilePath($tmp_file_name), $tmp_file_name_s3, $options);
//                }
//
//                OSC::core('aws_s3')->copy($tmp_file_name_s3, $mockup_file_name_s3);

                /**
                 * Save to image table
                 */

                $image = OSC::model('catalog/product_image')->setData([
                    'product_id' => $product->getId(),
                    'ukey' => $mockup_ukey,
                    'position' => isset($callback_data['position']) ? $callback_data['position'] : 0,
                    'flag_main' => isset($callback_data['flag_main']) ? $callback_data['flag_main'] : 0,
                    'is_upload_mockup_amazon' => isset($callback_data['is_upload_mockup_amazon']) ? $callback_data['is_upload_mockup_amazon'] : 0,
                    'is_show_product_type_variant_image' => isset($callback_data['is_show_product_type_variant_image']) ? $callback_data['is_show_product_type_variant_image'] : 0,
                    'alt' => '',
                    'filename' => $mockup_file_name,
                    'is_static_mockup' => 0
                ])->save();
            }

            try {
                $variant = OSC::model('catalog/product_variant')->load($callback_data['variant_id']);
            }catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }

                $this->_ajaxResponse();
            }

            $matched = false;

            $print_templates_variants_map = OSC::helper('catalog/campaign')->getVariantPrintTemplatesMap($product, $callback_data['variant_id']);

            foreach ($print_templates_variants_map as $item) {
                if ($item['print_template_id'] == $print_template_id && $item['variant_id'] == $callback_data['variant_id']) {
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                $ukey = 'updateImageVariant' .'_'. $store_info['id'] .'_'. $variant->getId() .'_'. $print_template_id .'_'. $image->getId();
                try {
                    OSC::model('catalog/product_bulkQueue')->loadByUKey($ukey);
                }catch (Exception $ex) {
                    if ($ex->getCode() == 404) {
                        OSC::model('catalog/product_bulkQueue')->setData([
                            'member_id' => 1,
                            'ukey' => $ukey,
                            'action' => 'updateImageVariant',
                            'queue_data' => [
                                'variant_id' => $variant->getId(),
                                'print_template_id' => $print_template_id,
                                'image_id' => $image->getId(),
                                'version' => $callback_data['version'],
                                'flag_convert_mug' => $callback_data['flag_convert_mug'] == 1 ? $callback_data['flag_convert_mug'] : 0
                            ]
                        ])->save();

                        OSC::core('cron')->addQueue('catalog/campaign_updateImageVariant', null, ['ukey' => 'catalog/updateImageVariant' , 'requeue_limit' => -1, 'skip_realtime','estimate_time' => 60 * 20]);

                    }else{
                        throw new Exception($ex->getMessage());
                    }
                }

            }

//            @unlink(OSC_Storage::tmpGetFilePath($tmp_file_name));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage() . "\n\n" . print_r($this->_request->getAll(), 1) . "\n\n" . $ex->getTraceAsString());
        }

        $this->_ajaxResponse();
    }

    public function actionV2UpdateCampaignMockupCrossSell() {
        /* @var $DB OSC_Database_Adapter */
        /* @var $product Model_Catalog_Product */

        $design_id = intval($this->_request->get('design_id'));
        $print_template_id = intval($this->_request->get('print_template_id'));
        $mockup_url = $this->_request->get('mockup_url');
        $mockup_ukey = $this->_request->get('mockup_ukey');
        $callback_data = $this->_request->get('callback_data');

        if ($design_id < 1 || !isset($mockup_url) || !$print_template_id || !$mockup_ukey) {
            $this->_ajaxError('Syncing data is incorrect');
        }

        $mockup_file_name = $callback_data['folder'] . $mockup_ukey . '.png';
        $tmp_file_name = 'mockup/' . md5($mockup_url) . '.png';

        try {
            try {
                $image = OSC::model('crossSell/image')->loadByUKey($mockup_ukey);

                if (isset($image->data['filename_s3'])) {
                    $this->_ajaxResponse();
                }

            }catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }

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

                $image = OSC::model('crossSell/image')->setData([
                    'design_id' => $design_id,
                    'ukey' => $mockup_ukey,
                    'position' => $callback_data['position'],
                    'flag_main' => $callback_data['flag_main'],
                    'filename' => $mockup_file_name
                ])->save();
            }

            $ukey = 'updateImageVariantCrossSell' .'_'. $print_template_id .'_'. $image->getId();

            try {
                OSC::model('catalog/product_bulkQueue')->loadByUKey($ukey);
            }catch (Exception $ex) {
                if ($ex->getCode() == 404) {
                    OSC::model('catalog/product_bulkQueue')->setData([
                        'member_id' => 1,
                        'ukey' => $ukey,
                        'action' => 'updateImageVariantCrossSell',
                        'queue_data' => [
                            'print_template_id' => $print_template_id,
                            'image_id' => $image->getId(),
                            'callback' => $callback_data
                        ]
                    ])->save();

                    OSC::core('cron')->addQueue('crossSell/updateImageVariantCrossSell', null, ['ukey' => 'crossSell/updateImageVariantCrossSell' , 'requeue_limit' => -1, 'skip_realtime','estimate_time' => 60 * 20]);

                }else{
                    throw new Exception($ex->getMessage());
                }
            }

            @unlink(OSC_Storage::tmpGetFilePath($tmp_file_name));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage() . "\n\n" . print_r($this->_request->getAll(), 1) . "\n\n" . $ex->getTraceAsString());
        }

        $this->_ajaxResponse();
    }

    public function actionV2UpdateCampaignDesignUrl() {
        /* @var $DB OSC_Database_Adapter */
        /* @var $product Model_Catalog_Product */

        /* @var $DB OSC_Database_Adapter */
        /* @var $line_item Model_Catalog_Order_Item */

        $sync_data = $this->_request->get('sync_data');

        if (!is_array($sync_data) || !isset($sync_data['line_item_id']) || !isset($sync_data['print_file_urls']) || !isset($sync_data['print_template_id']) || !isset($sync_data['design_timestamp']) || !is_array($sync_data['print_file_urls']) || count($sync_data['print_file_urls']) < 1) {
            $this->_ajaxError('Syncing data is incorrect.' . OSC::encode($sync_data));
        }

        $sync_data['print_template_id'] = intval($sync_data['print_template_id']);

        $sync_data['line_item_id'] = intval($sync_data['line_item_id']);

        $sync_data['design_timestamp'] = intval($sync_data['design_timestamp']);

        if ($sync_data['line_item_id'] < 1 || $sync_data['design_timestamp'] < 1) {
            $this->_ajaxError('Syncing data is incorrect');
        }

        if ($sync_data['key'] != 'beta' && $sync_data['print_template_id'] < 1) {
            $this->_ajaxError('print template id is incorrect');
        }

        try {
            $line_item = OSC::model('catalog/order_item')->loadByItemId($sync_data['line_item_id']);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        if ($line_item->isCampaignMode()) {
            $campaign_data = $line_item->getCampaignData();

            if (!$campaign_data) {
                $this->_ajaxError('Line Item is not campaign');
            }

            if (!isset($campaign_data['design_timestamp'])) {
                $campaign_data['design_timestamp'] = intval($line_item->data['added_timestamp']);
            }

            if ($campaign_data['design_timestamp'] < $sync_data['design_timestamp']) {
                $this->_ajaxError('Render API Error: Design timestamp is not correct');
            }

            if ($campaign_data['design_timestamp'] > $sync_data['design_timestamp']) {
                try {
                    if ($sync_data['print_template_id'] > 0 and $campaign_data['print_template']['print_template_id'] != $sync_data['print_template_id']) {
                        OSC::helper('catalog/campaign')->renderDesignUrlByTemplateId($line_item, $sync_data['print_template_id']);
                    } else {
                        $line_item_meta = $line_item->getOrderItemMeta();

                        $campaign_data_idx = $line_item->getCampaignDataIdx();

                        $line_item_meta->setData(['custom_data' => $line_item_meta->data['custom_data'][$campaign_data_idx]])->lock();

                        $line_item->setData(['design_url' => []])->save();

                        OSC::helper('catalog/campaign')->campaignRerenderDesign($line_item);
                    }

                    $this->_ajaxResponse();

                } catch (Exception $ex) {
                    $this->_ajaxError('Rerender API Error: ' . $ex->getMessage());
                }
            }
        }

        try {
            if ($sync_data['key'] == 'beta') {
                $line_item->setData(['design_url' => ['beta' => $sync_data['print_file_urls']]])->save();
            } else {
                $line_item->setData(['design_url' => [$sync_data['print_template_id'] => $sync_data['print_file_urls']]])->save();
            }

            if ($sync_data['key'] == 'beta') {
                $additional_data = $line_item->data['additional_data'];

                $additional_data['supplier_design_beta'] = implode(",", $sync_data['supplier']);

                $line_item->setData(['additional_data' => $additional_data])->save();
            }

            $collection = OSC::model('orderdesk/queueV2')->getCollection()
                ->addCondition('shop_id', $line_item->data['shop_id'], OSC_Database::OPERATOR_EQUAL)
                ->addCondition('order_record_id', $line_item->data['order_master_record_id'], OSC_Database::OPERATOR_EQUAL)
                ->addCondition('queue_flag', 11, OSC_Database::OPERATOR_EQUAL)
                ->addCondition('line_items', '"' . $line_item->data['item_id'] . '":', OSC_Database::OPERATOR_LIKE)
                ->load();

            if ($collection->length() > 0) {
                foreach ($collection as $model) {
                    $model->setData(['queue_flag' => 0])->save();
                }

                //call api chạy queue add process orderdesk
                OSC::helper('master/common')->callApi('/personalizedDesign/api/updateCampaignDesign', ['id' => OSC::makeUniqid()]);
            }

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse();
    }

    public function actionAddQueueRenderDesign() {
        $action = $this->_request->get('action');

        try {
            if (isset($action) && $action == 'campaign_rerender_design_url') {
                OSC::core('cron')->addQueue('catalog/campaign_rerenderDesignUrl', null, ['ukey' => 'catalog/campaign_rerenderDesignUrl', 'requeue_limit' => -1, 'estimate_time' => 60]);
            } else {
                for ($i = 1; $i <= 7; $i ++) {
                    OSC::core('cron')->addQueue('catalog/campaign_rerenderDesignV2', null, ['ukey' => 'catalog/campaign_rerenderDesignV2:' . $i, 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60, 'running_time' => 30*$i]);
                    OSC::core('cron')->addQueue('catalog/campaign_renderDesignOrderBeta', null, ['ukey' => 'catalog/campaign_rerenderDesignOrderBeta:' . $i, 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60, 'running_time' => 30*$i]);
                }
            }
        } catch (Exception $ex) {

        }

        $this->_ajaxResponse();
    }

    public function actionAddQueueRenderDesignAfterOrderCreated() {
        try {
            OSC::core('cron')->addQueue('catalog/order_renderDesignAfterOrderCreated', null, ['ukey' => 'catalog/order_renderDesignAfterOrderCreated', 'skip_realtime', 'requeue_limit' => -1, 'estimate_time' => 60]);
        } catch (Exception $ex) {

        }

        $this->_ajaxResponse();
    }

    public function actionGetAccessTokenEditDesign() {
        $item_id_master = intval($this->_request->get('item_id_master'));

        $member_id = intval($this->_request->get('member_id'));

        if ($item_id_master < 1) {
            $this->_ajaxError('item_id_master is incorrect.' . $item_id_master);
        }

        if ($member_id < 1) {
            $this->_ajaxError('member_id is incorrect.' . $member_id);
        }

        try {
            $line_item = OSC::model('catalog/order_item')->load($item_id_master);

            $order = $line_item->getOrder();

            $cache_value = $line_item->getId() . '_' . $order->getId() . '_' . $member_id;

            $cache_key = md5($cache_value);

            if (!OSC::core('cache')->get($cache_key)) {
                OSC::core('cache')->set($cache_key, $cache_value, 60*60);
            }

            $this->_ajaxResponse(['token' => $cache_key]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionUpdateDesignSvgBetaOrderItem() {
        $url_design_svg_beta = $this->_request->get('url');
        $design_url_clip_art_beta = $this->_request->get('url_clip_art_content');
        $design_url_preview_beta = $this->_request->get('url_preview_content');

        $line_item_id = intval($this->_request->get('line_item_id'));

        $design_id = intval($this->_request->get('design_id'));

        if (!$url_design_svg_beta || $line_item_id < 1 || $design_id < 1) {
            $this->_ajaxError("Data is incorrect, URL: {$url_design_svg_beta}, Line Item ID: {$line_item_id}, Design ID: {$design_id}");
        }

        try {
            $line_item = OSC::model('catalog/order_item')->load($line_item_id);
            $additional_data = $line_item->data['additional_data'];
            $additional_data['design_url_beta'][$design_id] = $url_design_svg_beta;
            $additional_data['design_url_clip_art_beta'][$design_id] = $design_url_clip_art_beta;
            $additional_data['design_url_preview_beta'][$design_id] = $design_url_preview_beta;
            $line_item->setData([
                'additional_data' => $additional_data
            ])->save();

            $product_d2 = OSC::model('d2/product')->getCollection()->addCondition('product_id', $line_item->data['product_id'])->load()->first();

            if (!$product_d2) {
                $this->_ajaxResponse();
            }

            if (!isset($additional_data['sync_airtable_id'])) {
                throw new Exception('Order Item not sync airtable');
            }

            // Update order item Airtable
            $ps_clipart = [];

            $design_url_beta = $additional_data['design_url_beta'];
            $design_url_clip_art_beta = $additional_data['design_url_clip_art_beta'];
            $design_url_preview_beta = $additional_data['design_url_preview_beta'];

            if (isset($design_url_clip_art_beta[$design_id]) && count($design_url_clip_art_beta[$design_id]) > 0) {
                foreach ($design_url_clip_art_beta[$design_id] as $clip_art_name => $clip_art_url) {
                    $ps_clipart[] = $clip_art_name . ': ' . $clip_art_url;
                }
            }

            if (isset($design_url_beta[$design_id])) {
                $ps_clipart[] = 'all_clipart: ' . $design_url_beta[$design_id];
            }

            if (isset($design_url_preview_beta[$design_id])) {
                $ps_clipart[] = 'all_preview: ' . $design_url_preview_beta[$design_id];
            }

            $record = [
                'id' => $additional_data['sync_airtable_id'],
                'fields' => [
                    'ps_clipart' => implode("\n", $ps_clipart)
                ]
            ];

            OSC::model('catalog/product_bulkQueue')->setData([
                'ukey' => "updateAirtable_{$line_item_id}_:" . OSC::makeUniqid(),
                'member_id' => 1,
                'action' => 'update_raw_airtable',
                'queue_data' => $record,
                'queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['queue']
            ])->save();

            OSC::core('cron')->addQueue('d2/updateRawAirtable', null, ['ukey'=> 'd2/updateRawAirtable','requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60]);

            $this->_ajaxResponse();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionGetS3Url() {
        try {
            $s3_bucket_url = OSC::core('aws_s3')->getS3BucketUrl();
            $cdn_config = OSC::systemRegistry('CDN_CONFIG');
            $shop_id = OSC::core('aws_s3')->getObjectPrefix();
            if ($cdn_config['enable'] && $cdn_config['imagekit_url']) {
                $url = $cdn_config['imagekit_url'] . '/' . $shop_id;
            } else {
                $url = $s3_bucket_url . '/' . $shop_id;
            }
            $this->_ajaxResponse($url);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }
}
