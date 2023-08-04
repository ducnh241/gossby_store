<?php

class Cron_Catalog_Campaign_CreateMockupCampaign extends OSC_Cron_Abstract {
    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '2000M');
        //render mockup
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 10;

        $counter = 0;

        $count_error = 0;

        while ($counter < $limit && $count_error < 4) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'createMockupCampaign'", '`added_timestamp` ASC, `queue_id` ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            try {
                $data = $model->data['queue_data'];

                $timestamp_old = $data['timestamp_old'];
                $timestamp_new = $data['timestamp_new'];
                $print_apply_other_face = $data['print_apply_other_face'];
                $version = $data['version'];
                $member_id = $data['member_id'];

                $product = OSC::model('catalog/product')->load($data['product_id']);

                if (!$product->isCampaignMode()) {
                    throw new Exception("Product not campaign");
                }

                $segments_render_mockup = [];

                foreach ($product->data['meta_data']['campaign_config']['print_template_config'] as $value) {
                    foreach ($value['segments'] as $key => $segment) {
                        if (is_array($segment['source']) && count($segment['source']) > 0) {
                            $segments_render_mockup[$value['print_template_id']][$key] = 'system';
                        }
                    }
                }

                $variants_printTemplates_map = OSC::helper('catalog/campaign')->getVariantsPrintTemplatesMap($product);

                if (count($variants_printTemplates_map) < 1) {
                    throw new Exception("not have data render mockup");
                }

                $variant_idx = [];
                $print_template_idx = [];

                foreach ($variants_printTemplates_map as $value) {
                    $variant_idx[] = $value['variant_id'];
                    $print_template_idx[] = $value['print_template_id'];
                }

                $variant_collection = OSC::model('catalog/product_variant')->getCollection()->load(array_unique($variant_idx));

                $print_template_collection = OSC::model('catalog/printTemplate')->getCollection()->load(array_unique($print_template_idx));

                $list_mockup_rel_collection = [];

                $list_mockup_model = [];

                $data_queue = [];

                $store_id = OSC::getStoreInfo()['store_id'];

                foreach ($variants_printTemplates_map as $variants_printTemplates) {
                    //check timestamp de render lai mockup theo print_template
                    if (count($timestamp_old) == 0 || count($timestamp_new) == 0 || md5(OSC::encode($timestamp_old[$variants_printTemplates['print_template_id']])) != md5(OSC::encode($timestamp_new[$variants_printTemplates['print_template_id']]))) {

                        if (isset($list_mockup_rel_collection[$variants_printTemplates['print_template_id']])) {
                            $mockup_rel_collection = $list_mockup_rel_collection[$variants_printTemplates['print_template_id']];
                        } else {
                            $mockup_rel_collection = OSC::model('catalog/printTemplate_mockupRel')->getCollection()->getByTemplateId($variants_printTemplates['print_template_id'], $product->data['type_flag']);
                            $list_mockup_rel_collection[$variants_printTemplates['print_template_id']] = $mockup_rel_collection;
                        }

                        $variant = $variant_collection->getItemByPK($variants_printTemplates['variant_id']);

                        if (!($variant instanceof Model_Catalog_Product_Variant)) {
                            throw new Exception("variant model error id " . $variants_printTemplates['variant_id']);
                        }

                        $print_template = $print_template_collection->getItemByPK($variants_printTemplates['print_template_id']);

                        if (!($print_template instanceof Model_Catalog_PrintTemplate)) {
                            throw new Exception("prints template model error id " . $variants_printTemplates['variant_id']);
                        }

                        $product_type_variant_id = $variant->getProductTypeVariant()->getId();

                        foreach ($mockup_rel_collection as $_model_mockup_rel) {
                            $option = [];
                            $variant_product_type_ids = $_model_mockup_rel->data['variant_product_type_ids'];

                            $additional_data = $_model_mockup_rel->data['additional_data'];

                            $is_upload_amazon = isset($additional_data['is_upload_mockup_amazon']) ? intval($additional_data['is_upload_mockup_amazon']) : 0;

                            $is_show_product_type_variant_image = isset($additional_data['is_show_product_type_variant_image']) ? intval($additional_data['is_show_product_type_variant_image']) : 0;

                            //check render mockup đúng với prodcut_type_variant duoc quy dinh san
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

                            $_data = OSC::helper('catalog/campaign_mockup')->renderMockup($variant, $print_template, $mockup, $option);

                            if (isset($option['default']) && $option['default'] == 1) {
                                OSC::helper('catalog/campaign_mockup')->uploadMockupStatic($_data[0]['source'], $variant, $product, $_model_mockup_rel, $version);
                                continue;
                            }

                            $mockup_ukey = $product->getId() . '_' . $_model_mockup_rel->getId() . '_' . md5(OSC::encode($_data['commands'])) . '_' . $version;

                            //truong hop anh 2 mat giong nhau khong hien thi mockup 2 mat
                            if (in_array($variants_printTemplates['print_template_id'], $print_apply_other_face) && !in_array($variants_printTemplates['print_template_id'], [39, 159, 160]) && $product->data['type_flag'] != Model_Catalog_Product::TYPE_PRODUCT_AWZ) {
                                if ($_model_mockup_rel->data['flag_main'] == 1 || (intval(stripos($product->data['product_type'], 'mug')) > 0 && $_model_mockup_rel->data['position'] == 0)) {
                                    continue;
                                }
                            }

                            $data_queue[] = [
                                'mockup_ukey' => $mockup_ukey,
                                'product_id' => $product->getId(),
                                'print_template_id' => $print_template->getId(),
                                'callback_data' => [
                                    'prefix_s3' => $store_id,
                                    'timestamp' => time(),
                                    'variant_id' => $variant->getId(),
                                    'mockup_id' => $mockup->getId(),
                                    'position' => $_model_mockup_rel->data['position'],
                                    'flag_main' => $_model_mockup_rel->data['flag_main'],
                                    'is_upload_mockup_amazon' => $is_upload_amazon,
                                    'is_show_product_type_variant_image'=> $is_show_product_type_variant_image,
                                    'version' => $version
                                ],
                                'mockup_data' => $_data,
                                'version' => $version,
                                'mockup_type' => 'store',
                                'mockup_category' => Helper_Catalog_Campaign_Mockup::CATEGORY_MOCKUP['render']
                            ];
                        }
                    }
                }

                if (count($data_queue) > 0) {
                    foreach ($data_queue as $queue) {
                        OSC::model('catalog/product_bulkQueue')->setData([
                            'ukey' => $product->getId() . '_renderCampaignMockup_' . OSC::makeUniqid(),
                            'member_id' => $member_id,
                            'action' => 'renderCampaignMockup',
                            'queue_data' => $queue
                        ])->save();
                    }

                    for ($i = 1; $i <= 10; $i ++) {
                        OSC::core('cron')->addQueue('catalog/campaign_renderMockup', null, ['ukey' => 'catalog/renderCampaignMockup:' . $i, 'requeue_limit' => -1, 'estimate_time' => 60 * 20]);
                    }
                }

                $model->delete();
            } catch (Exception $ex) {

                OSC::helper('core/telegram')->sendMessage("Error: " . OSC::encode($ex->getMessage()), '-482206828','1336104257:AAG03Y0v4tuB8FvtqlJtPtxUMNvoJcJ8OSU');

                $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 2, 'added_timestamp' => time()])->save();
                $count_error++;
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}
