<?php

class Cron_Feed_BulkBlock extends OSC_Cron_Abstract
{

    public function process($params, $queue_added_timestamp)
    {
        /* @var OSC_Database_Adapter $DB */
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 200;
        $counter = 0;

        while ($counter < $limit) {
            $DB->select('*', OSC::model('catalog/product_bulkQueue')->getTableName(), "queue_flag = 1 AND action = 'feedBulkBlock'", 'queue_id ASC', 1, 'fetch_queue');

            $bulk_block_queues = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$bulk_block_queues) {
                break ;
            }

            /* @var Model_Catalog_Product_BulkQueue $model_bulk_queue */
            $model_bulk_queue = OSC::model('catalog/product_bulkQueue');
            $model_bulk_queue->bind($bulk_block_queues);

            $queue_data = $model_bulk_queue->data['queue_data'];

            $counter++;

            $product_sku = $queue_data['block']['sku'];
            $collection_id = $queue_data['block']['collection_id'];
            $country_code = $queue_data['block']['country_code'];
            $category = $queue_data['block']['category'];

            $feed_block = OSC::model('feed/block');

            $model_bulk_queue->setData('queue_flag', Model_Catalog_Product_BulkQueue::QUEUE_FLAG['running'])->save();

            try {

                if (!$category || !in_array($category, ['google', 'bing'])) {
                    throw new Exception('Category is invalid!');
                }

                try {
                    $model_product = OSC::model('catalog/product')->loadByUKey($product_sku);
                } catch (Exception $ex) {
                    throw new Exception('Product is not exist with sku: ' . $product_sku);
                }
                if ($collection_id != '*') {
                    try {
                        OSC::model('catalog/collection')->load($collection_id);
                    } catch (Exception $ex) {
                        throw new Exception('Collection is not exit with collection_id: ' . $collection_id);
                    }
                }

                if ($country_code != '*') {
                    try {
                        /* @var Model_Catalog_Product $model_product */
                        OSC::model('core/country_country')->loadByUKey($country_code);
                    } catch (Exception $ex) {
                        throw new Exception('Country is not exit with country_code: ' . $country_code);
                    }
                }

                $queue_data['block']['product_id'] = $model_product->data['product_id'];
                $queue_data['block']['collection_id'] = $collection_id == '*' ? 0 : $collection_id;
                $queue_data['block']['country_code'] = $country_code;
                $feed_block->setData($queue_data['block'])->save();
                $model_bulk_queue->delete();

            } catch (Exception $ex) {
                if (strpos($ex->getMessage(), '1062 Duplicate entry')) {
                    $model_bulk_queue->delete();
                } else {
                    $model_bulk_queue->setData([
                        'error' => $ex->getMessage(),
                        'queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['error']
                    ])->save();
                }
            }
        }
        if ($counter == $limit) {
            return false;
        }
    }
}