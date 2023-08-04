<?php

class Cron_Catalog_Campaign_ConvertMockupMug extends OSC_Cron_Abstract {
    public function process($params, $queue_added_timestamp) {
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 100;

        $counter = 0;

        $count_error = 0;

        $map_product_type_variant = [
            '385' => ['mockup_rel_id' => 157,'print_template_id' => 1, 'mockup_id' => 107],
            '386' => ['mockup_rel_id' => 160,'print_template_id' => 2, 'mockup_id' => 110],
            '393' => ['mockup_rel_id' => 158,'print_template_id' => 4, 'mockup_id' => 108],
            '392' => ['mockup_rel_id' => 159,'print_template_id' => 5, 'mockup_id' => 109],
            '387' => ['mockup_rel_id' => 161,'print_template_id' => 3, 'mockup_id' => 111],
            '388' => ['mockup_rel_id' => 161,'print_template_id' => 3, 'mockup_id' => 111],
            '389' => ['mockup_rel_id' => 161,'print_template_id' => 3, 'mockup_id' => 111],
            '390' => ['mockup_rel_id' => 161,'print_template_id' => 3, 'mockup_id' => 111],
            '391' => ['mockup_rel_id' => 161,'print_template_id' => 3, 'mockup_id' => 111]
        ];

        $print_templates = [];
        $mockups = [];
        $mockup_rels = [];

        while ($counter < $limit && $count_error < 4) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'convertMockupMug'", '`added_timestamp` ASC, `queue_id` ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            try {
                $queue_data = $model->data['queue_data'];

                $product_model = OSC::model('catalog/product')->load($queue_data['product_id']);

                $variants = $product_model->getVariants();

                $campaign_data = $product_model->data['meta_data'];

                $print_apply_other_face = [];

                foreach ($campaign_data['campaign_config']['print_template_config'] as $product_config_new) {
                    if ($product_config_new['apply_other_face'] == 1 && count($product_config_new['segments']) > 1) {
                        $print_apply_other_face[] = $product_config_new['print_template_id'];
                    }
                }

                $data_queue = [];

                foreach ($variants as $variant) {
                    $product_type_variant_id = $variant->getProductTypeVariant()->getId();

                    $data = $map_product_type_variant[$product_type_variant_id];

                    if ($data == null) {
                        continue;
                    }

                    //truong hop anh 2 mat giong nhau khong hien thi mockup 2 mat
                    if (in_array($data['print_template_id'], $print_apply_other_face)) {
                        continue;
                    }

                    $version = $variant->data['meta_data']['campaign_config']['image_ids'][0]['version'];

                    $image_ids = $variant->data['meta_data']['campaign_config']['image_ids'][0]['image_ids'];

                    //truong hop da render anh roi
                    if (count($image_ids) > 3) {
                        continue;
                    }

                    $print_template = $print_templates[$data['print_template_id']];

                    if ($print_template == null) {
                        $print_template = OSC::model('catalog/printTemplate')->load($data['print_template_id']);
                        $print_templates[$data['print_template_id']] = $print_template;
                    }

                    $mockup = $mockups[$data['mockup_id']];

                    if ($mockup == null) {
                        $mockup = OSC::model('catalog/mockup')->load($data['mockup_id']);
                        $mockups[$data['mockup_id']] = $mockup;
                    }

                    $model_mockup_rel = $mockup_rels[$data['mockup_rel_id']];

                    if ($model_mockup_rel == null) {
                        $model_mockup_rel = OSC::model('catalog/printTemplate_mockupRel')->load($data['mockup_rel_id']);
                        $mockup_rels[$data['mockup_rel_id']] = $model_mockup_rel;
                    }

                    $_data = OSC::helper('catalog/campaign_mockup')->renderMockup($variant, $print_template, $mockup);

                    $mockup_ukey = $product_model->getId() . '_' . $model_mockup_rel->getId() . '_' . md5(OSC::encode($_data['commands'])) . '_' . $version;

                    $data_queue[] = [
                        'mockup_ukey' => $mockup_ukey,
                        'product_id' => $product_model->getId(),
                        'print_template_id' => $print_template->getId(),
                        'callback_data' => [
                            'timestamp' => time(),
                            'variant_id' => $variant->getId(),
                            'mockup_id' => $mockup->getId(),
                            'position' => $model_mockup_rel->data['position'],
                            'flag_main' => $model_mockup_rel->data['flag_main'],
                            'version' => $version,
                            'flag_convert_mug' => 1
                        ],
                        'mockup_data' => $_data
                    ];

                }

                if (count($data_queue) > 0) {
                    foreach ($data_queue as $queue) {
                        OSC::model('catalog/product_bulkQueue')->setData([
                            'member_id' => 1,
                            'action' => 'renderCampaignMockup',
                            'queue_data' => $queue
                        ])->save();
                    }

                    OSC::core('cron')->addQueue('catalog/campaign_renderMockup', null, ['ukey' => 'catalog/renderCampaignMockup:1', 'requeue_limit' => -1, 'estimate_time' => 60 * 20]);
                    OSC::core('cron')->addQueue('catalog/campaign_renderMockup', null, ['ukey' => 'catalog/renderCampaignMockup:2', 'requeue_limit' => -1, 'estimate_time' => 60 * 20]);
                    OSC::core('cron')->addQueue('catalog/campaign_renderMockup', null, ['ukey' => 'catalog/renderCampaignMockup:3', 'requeue_limit' => -1, 'estimate_time' => 60 * 20]);
                }

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 2, 'added_timestamp' => time()])->save();
                $count_error++;
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}