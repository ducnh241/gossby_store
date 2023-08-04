<?php

class Cron_Developer_ConvertHeartPlaqueCart extends OSC_Cron_Abstract {

    public function process($data, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $product_type_variant_map = Helper_Developer_ConvertHeartPlaque::PRODUCT_TYPE_VARIANT_MAP;
        $product_size_dimension = Helper_Developer_ConvertHeartPlaque::PRODUCT_SIZE_DIMENSION;

        $map_location_print_template = $product_type_variant_map['print_template_id'];
        $map_product_type_variant = $product_type_variant_map['product_type_variant'];

        $product_type = 'desktop_plaque';

        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 50;

        $counter = 0;

        $count_error = 0;

        $personalized_designs = [];

        while ($counter < $limit && $count_error < 5) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'convertCartItemHeartPlaque'", '`added_timestamp` ASC, `queue_id` ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            $locked_key = OSC::makeUniqid();

            OSC_Database_Model::lockPreLoadedModel($locked_key);

            $DB->begin();

            try {

                $data = $model->data['queue_data'];

                $item_id = $data['item_id'];

                if (empty($item_id)) {
                    throw new Exception('Item id not found');
                }

                $line_item = OSC::model('catalog/cart_item')->load($item_id);

                if ($line_item->isCampaignMode()) {
                    throw new Exception('Cart item is campaign');
                }

                $variant = $line_item->getVariant();

                if (empty($variant)) {
                    throw new Exception('Variant not found');
                }

                $size = Helper_Developer_ConvertHeartPlaque::getSize($variant->data['options']);

                if (!$size) {
                    throw new Exception('Can not get size');
                }

                $additional_data = $line_item->data['additional_data'];

                $print_template_id = isset($map_location_print_template[$size]) ? $map_location_print_template[$size] : 0;

                if ($print_template_id < 1) {
                    throw new Exception('Not map print template item ' . $line_item->getId());
                }

                $print_template = OSC::model('catalog/printTemplate')->load($print_template_id);

                $product_type_variant_id = isset($map_product_type_variant[$size]) ? $map_product_type_variant[$size] : 0;

                if ($product_type_variant_id < 1) {
                    throw new Exception('Not have product type variant ' . $line_item->getId());
                }

                $product_type_variant = OSC::model('catalog/productType_variant')->load($product_type_variant_id);

                if ($product_type_variant->getId() < 1) {
                    throw new Exception('Not have product type variant ' . $line_item->getId());
                }

                $current_custom_data = $line_item->data['custom_data'][0];

                $segments = $print_template->data['config']['segments'];

                $campaign_data = [
                    'key' => 'campaign',
                    'data' => [
                        'product_type_variant_id' => $product_type_variant->getId(),
                        'product_type' => [
                            'title' => $product_type_variant->data['title'],
                            'ukey' => $product_type,
                            'options' => $product_type_variant->getOptionValues()
                        ],
                        'print_template' => [
                            'print_template_id' => $print_template_id,
                            'preview_config' => $print_template->data['config']['preview_config'],
                            'segments' => [],
                            'segment_source' => [],
                            'print_file' => $print_template->data['config']['print_file']
                        ]
                    ]
                ];

                $segment_source = [];

                $config_sides = [
                    'desktop_plaque' => 0
                ];

                foreach ($segments as $segment_key => $source) {
                    unset($segments[$segment_key]['builder_config']);

                    $design_id = intval(array_keys($current_custom_data['data'])[$config_sides[$segment_key]]);

                    $config = $current_custom_data['data'][$design_id]['config'];

                    if (isset($personalized_designs[$design_id])) {
                        $personalized_design = $personalized_designs[$design_id];
                    } else {
                        $personalized_design = OSC::model('personalizedDesign/design')->load($design_id);
                        $personalized_designs[$design_id] = $personalized_design;
                    }

                    $personalized_design_config = Observer_Catalog_Campaign::validatePersonalizedDesign($personalized_design, $config);

                    $segment_source[$segment_key]['source'] = [
                        'type' => 'personalizedDesign',
                        'design_id' => intval($personalized_design->getId()),
                        'position' => [
                            'x' => 0,
                            'y' => 0
                        ],
                        'dimension' => [
                            'width' => $product_size_dimension['width'],
                            'height' => $product_size_dimension['height'],
                        ],
                        'rotation' => 0,
                        'timestamp' => 0,
                        'orig_size' => ['width' => $personalized_design->data['design_data']['document']['width'], 'height' => $personalized_design->data['design_data']['document']['height']]
                    ];

                    $segment_source[$segment_key]['source']['svg'] = $personalized_design_config['svg'] ?? '';
                    $segment_source[$segment_key]['source']['config'] = $personalized_design_config['config'] ?? [];
                    $segment_source[$segment_key]['source']['config_preview'] = $personalized_design_config['config_preview'] ?? [];
                }

                $campaign_data['data']['print_template']['segment_source'] = $segment_source;
                $campaign_data['data']['print_template']['segments'] = $segments;

                $new_custom_data = [$campaign_data];

                $DB->update(
                    'catalog_cart_item',
                    [
                        'custom_data' => OSC::encode($new_custom_data),
                        'additional_data' => OSC::encode($additional_data)
                    ],
                    'item_id = ' . $line_item->data['item_id'],
                    1,
                    'update_cart_item'
                );

                $DB->free('update_cart_item');

                $model->delete();

                $DB->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);
            } catch (Exception $ex) {
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                $model->setData(['error' => "ERROR Cart Item {$item_id}: " . $ex->getMessage(), 'queue_flag' => 2, 'added_timestamp' => time()])->save();

                if (!str_contains($ex->getMessage(), 'The design got error:')) {
                    $count_error++;
                }
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}
