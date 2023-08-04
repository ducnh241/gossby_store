<?php

class Cron_D2_afterProductCreated extends OSC_Cron_Abstract {

    /**
     * @param $params
     * @param $queue_added_timestamp
     * @return void
     */
    public function process($params, $queue_added_timestamp)
    {
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();
        $limit = 100;
        $counter = 0;

        while ($counter < $limit) {
            $bulk_queue = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $bulk_queue->getTableName(), "queue_flag = 1 AND action = 'd2CreateOrUpdateProduct'", 'added_timestamp ASC', 1, 'fetch_queue');
            $row_product = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row_product) {
                break;
            }

            $counter++;
            $bulk_queue->bind($row_product);
            $bulk_queue->setData('queue_flag', Model_Catalog_Product_BulkQueue::QUEUE_FLAG['running'])->save();
            try {
                OSC::helper('catalog/orderItem')->putOrderItemToAirtableByProductId([$bulk_queue->data['queue_data']['product_id']]);
                $bulk_queue->delete();
            } catch (Exception $ex) {
                $bulk_queue->setData(['error' => $ex->getMessage(), 'queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['error'], 'modified_timestamp' => time()])->save();
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}