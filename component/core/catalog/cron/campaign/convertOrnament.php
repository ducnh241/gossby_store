<?php

class Cron_Catalog_Campaign_ConvertOrnament extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '2000M');

        if (!is_array($params['product_ids']) || count($params['product_ids']) < 1) {
            return;
        }

        $product_type_variant_id_mdf = 444;

        $product_type_variant_id_aluminium = 404;

        $print_template_id = 60;

        $store_info = OSC::getStoreInfo();

        $DB = OSC::core('database');

        $collection_product = OSC::model('catalog/product')->getCollection()->load($params['product_ids']);

        $mockup_rel_collection = OSC::model('catalog/printTemplate_mockupRel')->getCollection()->getByTemplateId($print_template_id);

        $print_template = OSC::model('catalog/printTemplate')->load($print_template_id);

        foreach ($collection_product as $model) {

            $images_ids_aluminium = null;

            $check_variant_mdf_flag = false;

            foreach ($model->getVariants() as $product_variant) {
                $_product_type_variant_id = $product_variant->data['meta_data']['campaign_config']['product_type_variant_id'];

                //check xem variant mdf da ton tai trong product đó chưa
                if ($_product_type_variant_id == $product_type_variant_id_mdf) {
                    $check_variant_mdf_flag = true;
                }

                //check xem variant aluminium có tồn tại trong product đó không
                if ($_product_type_variant_id == $product_type_variant_id_aluminium) {
                    $images_ids_aluminium = $product_variant->data['meta_data']['campaign_config']['image_ids'];
                }
            }

            if ($images_ids_aluminium === null|| $check_variant_mdf_flag) {
                continue;
            }

            $DB->begin();
            $locked_key = OSC::makeUniqid();
            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                $model_variant = OSC::model('catalog/product_variant')->setData([
                    'sku' => $model->getUkey() . '-' . strtoupper(uniqid(null, false) . OSC::randKey(2, 7)),
                    'product_id' => $model->getId(),
                    'price' => 1795,
                    'compare_at_price' => 2499,
                    'cost' => 0,
                    'track_quantity' => 1,
                    'overselling' => 1,
                    'quantity' => 0,
                    'require_shipping' => 1,
                    'require_packing' => 1,
                    'keep_flat' => 0,
                    'weight' => 0,
                    'weight_unit' => 'kg',
                    'dimension_width' => 0,
                    'dimension_height' => 0,
                    'dimension_length' => 0,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time(),
                    'meta_data' => [
                        "campaign_config" => [
                            "product_type_variant_id" => $product_type_variant_id_mdf,
                            'image_ids' => $images_ids_aluminium,
                            "convert_ornament_medallion_mdf" => 1
                        ],
                    ]
                ])->save();

                foreach ($mockup_rel_collection as $_model_mockup_rel) {
                    $variant_product_type_ids = $_model_mockup_rel->data['variant_product_type_ids'];

                    //check render mockup đúng với prodcut_type_variant được quy định sẵn
                    if (isset($variant_product_type_ids) && is_array($variant_product_type_ids) && count($variant_product_type_ids) > 0 && !in_array($product_type_variant_id_mdf,$variant_product_type_ids)) {
                        continue;
                    }

                    $mockup = OSC::model('catalog/mockup')->load($_model_mockup_rel->data['mockup_id']);

                    $data = OSC::helper('catalog/campaign_mockup')->renderMockup($model_variant, $print_template, $mockup);

                    $mockup_ukey = $model->getId() . '_' . $_model_mockup_rel->getId() . '_' . md5(OSC::encode($data['commands']));

                    $_data = [
                        'mockup_ukey' => $mockup_ukey,
                        'product_id' => $model->getId(),
                        'print_template_id' => $print_template->getId(),
                        'callback_data' => [
                            'timestamp' => time(),
                            'variant_id' => $model_variant->getId(),
                            'mockup_id' => $mockup->getId(),
                            'position' => $_model_mockup_rel->data['position'],
                            'flag_main' => $_model_mockup_rel->data['flag_main']
                        ],
                        'mockup_data' => $data
                    ];

                    if (count($_data) > 0) {
                        OSC::model('catalog/product_bulkQueue')->setData([
                            'member_id' => 1,
                            'action' => 'renderCampaignMockup',
                            'queue_data' => $_data
                        ])->save();

                        OSC::core('cron')->addQueue('catalog/campaign_renderMockup', null, ['ukey' => 'catalog/renderCampaignMockup:' . rand(1, 3), 'requeue_limit' => -1, 'estimate_time' => 60 * 20]);
                    }
                }

                $DB->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);
            }catch (Exception $ex) {
                $DB->rollback();
                OSC_Database_Model::unlockPreLoadedModel($locked_key);
                OSC::helper('core/telegram')->sendMessage($store_info['id'] . " Convert error product id: " . $model->getId() . ' with error : ' . $ex->getMessage(), '-482206828','1336104257:AAG03Y0v4tuB8FvtqlJtPtxUMNvoJcJ8OSU');
            }
        }

        return;
    }
}
