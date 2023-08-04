<?php

class Cron_Catalog_Algolia_SyncProduct extends OSC_Cron_Abstract {

    /**
     * @param $params
     * @param $queue_added_timestamp
     * @return void
     */
    public function process($params, $queue_added_timestamp) {

        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getAdapter();
        $limit = 10;

        $table = OSC::model('catalog/product_bulkQueue')->getTableName();

        $DB->select('*', $table, "`queue_flag` = 1 AND `action` = 'algolia_sync_product'", '`added_timestamp` ASC', $limit, 'fetch_queue');

        $rows = $DB->fetchArrayAll('fetch_queue');

        if (empty($rows)) {
            return true;
        }
        $DB->free('fetch_queue');

        $queue_ids = array_column($rows, 'queue_id');

        $DB->update($table, ['queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['running']], 'queue_id IN (' . implode(',', $queue_ids) . ')');

        foreach ($rows as $row) {
            try {
                $bulk_queue = OSC::model('catalog/product_bulkQueue');
                $bulk_queue->bind($row);

                $queue_data = $bulk_queue->data['queue_data'];
                $product_id = $queue_data['product_id'];
                $sync_type = $queue_data['sync_type'];

                if ($sync_type === Helper_Catalog_Algolia_Product::SYNC_TYPE_DELETE_PRODUCT) {

                    OSC::helper('catalog/algolia_product')->deleteProduct(intval($product_id));
                } elseif ($sync_type === Helper_Catalog_Algolia_Product::SYNC_TYPE_UPDATE_PRODUCT) {
                    $product = OSC::model('catalog/product')->load($product_id);
                    OSC::helper('catalog/algolia_product')->addProduct($product);
                }

                $bulk_queue->delete();
            } catch (Exception $ex) {
                $bulk_queue->setData(['error' => $ex->getMessage(), 'queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['error'], 'modified_timestamp' => time()])->save();
            }

        }

        return false;
    }
}
