<?php

class Cron_Catalog_Campaign_UpdateImageVariant extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 200;

        $counter = 0;

        $count_error = 0;

        $reset_cache_product_ids = [];

        while ($counter < $limit && $count_error < 4) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'updateImageVariant'", '`added_timestamp` ASC, `queue_id` ASC', 1, 'fetch_queue');

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

                $variant_id = $data['variant_id'];

                $print_template_id = $data['print_template_id'];

                $image_id = $data['image_id'];

                $version = intval($data['version']);

                try {
                    $variant = OSC::model('catalog/product_variant')->load($variant_id);
                    OSC::helper('catalog/campaign_mockup')->insertImageMockup($variant, $print_template_id, $image_id, $version);
                    $reset_cache_product_ids[] = $variant->data['product_id'];
                } catch (Exception $ex) {
                    if ($ex->getCode() != 404) {
                        throw new Exception($ex->getMessage());
                    }
                }

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 1, 'added_timestamp' => time()])->save();
                $count_error++;
            }
        }

        try {
            $products = OSC::model('catalog/product')->load($reset_cache_product_ids);

            foreach ($products as $product) {
                OSC::core('observer')->dispatchEvent('model__catalog_product__after_save', [
                    'model' => $product,
                    'columns' => 'upc'
                ]);
            }
        } catch (Exception $ex) {
            //
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}
