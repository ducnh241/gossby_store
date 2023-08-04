<?php

class Cron_Catalog_Campaign_DeleteVariantCanvas extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 100;

        $counter = 0;

        $count_error = 0;

        $map_product_type_variant = [2,4,8,10,12,16];

        while ($counter < $limit && $count_error < 4) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'deleteVariantCanvas'", '`added_timestamp` ASC, `queue_id` ASC', 1, 'fetch_queue');

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

                $count_check = $variants->length();

                $image_ids_delete = [];

                $variant_ids_delete = [];

                foreach ($variants as $variant) {
                    $product_type_variant_id = $variant->getProductTypeVariant()->getId();

                    if (!in_array($product_type_variant_id,$map_product_type_variant)) {
                        continue;
                    }

                    $meta_data = $variant->data['meta_data'];

                    $image_ids = $meta_data['campaign_config']['image_ids'][0]['image_ids'];

                    $image_ids_delete = array_merge($image_ids_delete,$image_ids);

                    $variant_ids_delete[] = $variant->getId();
                }

                if (count($variant_ids_delete) > 0) {
                    if ($count_check == count($variant_ids_delete)) {
                        OSC::helper('catalog/product')->delete($queue_data['product_id']);
                    }else{
                        $variant_collection = OSC::model('catalog/product_variant')->getCollection()->load(array_unique($variant_ids_delete));

                        foreach ($variant_collection as $variant) {
                            $variant->delete();
                        }

                        if (count($image_ids_delete) > 0) {
                            $images_collection = OSC::model('catalog/product_image')->getCollection()->load(array_unique($image_ids_delete));

                            foreach ($images_collection as $model_image) {
                                if (file_exists(OSC_Storage::getStoragePath($model_image->data['filename'])) && @unlink(OSC_Storage::getStoragePath($model_image->data['filename'])) === false) {
                                    throw new Exception('Unable to remove file [' . OSC_Storage::getStoragePath($model_image->data['filename']) . ']');
                                }

                                $model_image->delete();
                            }
                        }
                    }
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
