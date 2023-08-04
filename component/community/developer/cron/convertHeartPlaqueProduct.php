<?php

class Cron_Developer_ConvertHeartPlaqueProduct extends OSC_Cron_Abstract {
    private $__product_size_dimension = Helper_Developer_ConvertHeartPlaque::PRODUCT_SIZE_DIMENSION;
    private $__product_type_variant_map = Helper_Developer_ConvertHeartPlaque::PRODUCT_TYPE_VARIANT_MAP;
    private $__product_type = 'desktop_plaque';
    private $__selected_design = 'desktop_plaque';

    public function process($data, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $map_location_print_template = $this->__product_type_variant_map['print_template_id'];
        $map_product_type_variant = $this->__product_type_variant_map['product_type_variant'];

        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 10;

        $counter = 0;

        $count_error = 0;

        while ($counter < $limit && $count_error < 5) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'convertHeartPlaqueProduct'", '`added_timestamp` ASC, `queue_id` ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            try {

                $counter++;

                $model->bind($row);

                $model->setData('queue_flag', 0)->save();

                $product_id = $model->data['queue_data']['product_id'];

                $product = OSC::model('catalog/product')->load($product_id);

                $collection_variants = OSC::model('catalog/product_variant')->getCollection()->addCondition('product_id', $product_id)->load();

                $collection_images = OSC::model('catalog/product_image')->getCollection()->addCondition('product_id', $product_id)->sort('position', OSC_Database::ORDER_DESC)->load();

                if ($collection_variants->length() < 1) {
                    throw new Exception("Product {$product_id} not exist variant");
                }

                $designs = OSC::model('personalizedDesign/design')->getCollection()->load($collection_variants->getItem()->data['design_id']);

                $DB->begin();

                $locked_key = OSC::makeUniqid();

                OSC_Database_Model::lockPreLoadedModel($locked_key);
                $this->_convertDataImageProduct($product, $collection_images);

                $this->_convertDataProductVariant($product, $collection_variants, $map_product_type_variant, $map_location_print_template);

                $this->_convertDataProduct($product, $designs, $map_location_print_template);

                $model->delete();

                $DB->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);
            } catch (Exception $ex) {
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                $model->setData([
                    'queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['error'],
                    'error' => $model->data['product_id'] . '::' . $ex->getMessage()
                ])->save();
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }

    /**
     * @throws Exception
     */
    protected function _convertDataProductVariant($product, $variants, $map_product_type_variant, $map_location_print_template) {
        if (!($product instanceof Model_Catalog_Product) ||
            count($map_product_type_variant) < 1 ||
            count($map_location_print_template) < 1) {
            throw new Exception('Data convert product is incorrect');
        }

        $product_size = Helper_Developer_ConvertHeartPlaque::PRODUCT_SIZE;

        $position = 0;

        $product_type_variant_id_processed = [];

        foreach ($variants as $variant) {

            $size = Helper_Developer_ConvertHeartPlaque::getSize($variant->data['options']);

            if (!$size) {
                throw new Exception('Can not get size');
            }

            $position += 1;

            $print_template_id = isset($map_location_print_template[$size]) ? intval($map_location_print_template[$size]) : 0;

            $config = strtolower($size);

            OSC::logFile('config :' . OSC::encode($config), 'convertDataProductVariant' . date('Ymd'));

            $product_type_variant_id = isset($map_product_type_variant[$config]) ? intval($map_product_type_variant[$config]) : 0;

            if ($product_type_variant_id < 1) {
                throw new Exception('Not map product type variant');
            }

            if (in_array($product_type_variant_id, $product_type_variant_id_processed)) {

                $variant->delete();

                continue;
            }

            $new_variant_data = $variant->data;

            $image_ids = [];

            foreach ($variant->data['image_id'] as $image_id) {
                $image_ids[] = intval($image_id);
            }

            $new_variant_data['meta_data']['campaign_config'] = [
                'product_type_variant_id' => $product_type_variant_id,
                'image_ids' => [
                    [
                        'print_template_id' => intval($print_template_id),
                        'image_ids' => $image_ids,
                        'image_ids_customer' => $image_ids,
                        'version' => 0
                    ]
                ]
            ];

            if (isset($new_variant_data['meta_data']['semitest_config'])) {
                unset($new_variant_data['meta_data']['semitest_config']);
            }

            if (isset($new_variant_data['meta_data']['variant_config'])) {
                unset($new_variant_data['meta_data']['variant_config']);
            }

            unset($new_variant_data['id']);
            $new_variant_data['design_id'] = '';
            $new_variant_data['image_id'] = '';
            $new_variant_data['options'] = null;
            $new_variant_data['position'] = $position;
            $variant->setData($new_variant_data)->save();

            $product_type_variant_id_processed[] = $product_type_variant_id;
        }
    }

    protected function _convertDataProduct($product, $designs, $map_location_print_template) {
        if (!($product instanceof Model_Catalog_Product) ||
            empty($designs) ||
            count($map_location_print_template) < 1) {
            throw new Exception('Data convert product is incorrect');
        }

        $print_template_config = [];

        foreach ($designs as $design) {
            $segments['desktop_plaque'] = [
                'source' => [
                    'type' => 'personalizedDesign',
                    'design_id' => intval($design->getId()),
                    'position' => [
                        'x' => 0,
                        'y' => 0
                    ],
                    'dimension' => [
                        'width' => $this->__product_size_dimension['width'],
                        'height' => $this->__product_size_dimension['height'],
                    ],
                    'rotation' => 0,
                    'timestamp' => 0,
                    'orig_size' => ['width' => $design->data['design_data']['document']['width'], 'height' => $design->data['design_data']['document']['height']]
                ]
            ];
        }

        if (empty($segments)) {
            throw new Exception('Cannot get segments in print_template_config');
        }

        $print_template_id = 177;

        $print_template = OSC::model('catalog/printTemplate')->load($print_template_id);

        $print_template_config[] = [
            'selected_design' => $this->__selected_design,
            'print_template_id' => intval($print_template->getId()),
            'title' => $print_template->data['title'],
            'apply_other_face' => 0,
            'segments' => $segments
        ];

        $new_product_data = $product->data;
        $new_product_data['product_type'] = $this->__product_type;
        $new_product_data['meta_data'] = [
            'campaign_config' => [
                'print_template_config' => $print_template_config,
                'is_reorder' => 0,
                "apply_reorder" => [],
                "tab_flag" => 0
            ],
            "marketing_point" => "0",
            'buy_design' => [
                'is_buy_design' => 0,
                'buy_design_price' => 0
            ]
        ];

        unset($new_product_data['product_id']);
        $new_product_data['selling_type'] = Model_Catalog_Product::TYPE_CAMPAIGN;
        $new_product_data['options']['option1'] = false;
        $new_product_data['options']['option2'] = false;
        $new_product_data['options']['option3'] = false;

        $product->setData($new_product_data)->save();
    }

    protected function _convertDataImageProduct($product, $images) {
        if (!($product instanceof Model_Catalog_Product) || count($images) < 1) {
            return;
        }

        $position = 0;

        foreach ($images as $key => $image_data) {
            $filename = $image_data->data['filename'];

            if (!OSC::core('aws_s3')->doesStorageObjectExist($filename)) {
                $this->_ajaxError('Image url error ' . $filename);
            }

            $tmp_file_name = OSC::core('aws_s3')->getStoragePath($filename);

            $ext = pathinfo($tmp_file_name, PATHINFO_EXTENSION);

            // Hoi lai
            $mockup_file_name = 'catalog/customer_mockup/' . $product->getId() . '/' . md5($filename) . '.' . $ext;

            if (!OSC::core('aws_s3')->doesStorageObjectExist($mockup_file_name)) {
                OSC::core('aws_s3')->copy($tmp_file_name, OSC::core('aws_s3')->getStoragePath($mockup_file_name));
            }

            $mockup_ukey = $product->getId() . '_' . md5($mockup_file_name);

            OSC::logFile('mockup_ukey: ' . $mockup_ukey, 'convertDataProduct');

            $position -= 1;

            if ($image_data->data['ukey'] != $mockup_ukey) {
                $DB = OSC::core('database')->getAdapter();
                $data_save = [
                    'ukey' => $mockup_ukey,
                    'flag_main' => 0,
                    'alt' => '',
                    'filename' => $mockup_file_name,
                    'is_static_mockup' => 2,
                    'position' => $position
                ];

                $DB->update('catalog_product_image', $data_save, 'image_id = ' . $image_data->getId(), 1, 'update_image');

                if ($DB->getNumAffected('update_image') != 1) {
                    throw new Exception('Not update image width image_id = ' . $image_data->getId());
                }
            }
        }
    }
}
