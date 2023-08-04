<?php

class Cron_D2_SyncAirtableOrderResend extends OSC_Cron_Abstract {

    /**
     * @param $params
     * @param $queue_added_timestamp
     * @return void
     */
    public function process($params, $queue_added_timestamp)
    {
        /* @var $DB_MASTER OSC_Database_Adapter */
        $DB_MASTER = OSC::core('database')->getAdapter('db_master');
        $limit = 20;
        $counter = 0;

        while ($counter < $limit) {
            $order_bulkQueue = OSC::model('catalog/order_bulkQueue');

            $DB_MASTER->select('*', $order_bulkQueue->getTableName(),"queue_flag = 1 AND action = 'sync_airtable_order_resend'", 'added_timestamp ASC', 1, 'fetch_queue');
            $row_order_item = $DB_MASTER->fetchArray('fetch_queue');

            $DB_MASTER->free('fetch_queue');

            if (!$row_order_item) {
                break;
            }

            $counter++;
            $order_bulkQueue->bind($row_order_item);
            $order_bulkQueue->setData('queue_flag', Model_Catalog_Order_BulkQueue::QUEUE_FLAG['running'])->save();

            try {
                // only check order of gossby
                if ($row_order_item['shop_id'] == OSC::getShop()->getId()) {
                    $line_item = OSC::model('catalog/order_item')->load($order_bulkQueue->data['secondary_key']);
                    $order_items = OSC::model('catalog/order_item')->getCollection();
                    $order_items->addItem($line_item);
                    OSC::helper('catalog/orderItem')->addToOrderAirtableBulkQueue($order_items, [$line_item->data['product_id']]);
                }

                $order_bulkQueue->delete();
            } catch (Exception $ex) {
                $order_bulkQueue->setData(['error' => $ex->getMessage(), 'queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['error'], 'modified_timestamp' => time()])->save();
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}