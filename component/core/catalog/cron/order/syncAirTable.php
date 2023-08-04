<?php

class Cron_Catalog_Order_SyncAirTable extends OSC_Cron_Abstract
{

    /**
     * @param $params
     * @param $queue_added_timestamp
     * @return mixed
     */
    public function process($params, $queue_added_timestamp)
    {
        /* @var $DB OSC_Database_Adapter */
        $DB_STORE = OSC::core('database')->getWriteAdapter();

        $DB_MASTER = OSC::core('database')->getAdapter('db_master');
        $limit = 10;

        $bulk_queue = OSC::model('catalog/product_bulkQueue');


        $DB_STORE->select('*', $bulk_queue->getTableName(), "`queue_flag` = 1 AND `action` = 'create_record_airtable'", '`added_timestamp` ASC', $limit, 'fetch_queue');

        $row_order_items = $DB_STORE->fetchArrayAll('fetch_queue');

        $DB_STORE->free('fetch_queue');

        if (empty($row_order_items)) {
            return true;
        }

        $DB_STORE->update($bulk_queue->getTableName(), ['queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['running']], 'queue_id IN (' . implode(',', array_column($row_order_items, 'queue_id')) . ')');

        $records = [];
        $order_item_ids = [];
        $order_items_collection = OSC::model('catalog/order_item')->getCollection();

        foreach ($row_order_items as $order_item) {
            $model = OSC::model('catalog/product_bulkQueue');
            $model->bind($order_item);

            $records[$model->data['queue_data']['fields']['Order Line ID']] = $model->data['queue_data'];
            $order_item_ids[] = $model->data['queue_data']['fields']['Order Line ID'];
        }

        try {
            $order_items = OSC::model('catalog/order_item')->getCollection()->addField('additional_data,order_master_record_id')->load($order_item_ids);

            /* @var $item Model_Catalog_Order_Item */
            foreach ($order_items as $item) {
                $additional_data = $item->data['additional_data'];
                if (isset($additional_data['sync_airtable_flag'])) {
                    unset($records[$item->getId()]);
                } else {
                    $order_items_collection->addItem($item);
                }
            }

            if (!empty($records)) {
                $response = OSC::core('airtable')->createData(array_values($records), OSC_AIRTABLE_ORDER_LINE_TABLE);
                if (isset($response['content']['error'])) {
                    throw new Exception($response['content']['error']['message']);
                }

                $additional_data_query = $this->_updateOrderItemAirtable($order_items_collection, $response['content']);
                if ($additional_data_query) {
                    $DB_MASTER->query($additional_data_query, null, 'additional_data_query');
                    $DB_MASTER->free('additional_data_query');
                }
            }

            $DB_STORE->delete($bulk_queue->getTableName(), 'queue_id IN (' . implode(',', array_column($row_order_items, 'queue_id')) . ')', null, 'delete_sync');
            $DB_STORE->free('delete_sync');
        } catch (Exception $ex) {
            $DB_STORE->update($bulk_queue->getTableName(),
                [
                    'queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['error'],
                    'error' => $ex->getMessage()
                ],
                'queue_id IN (' . implode(',', array_column($row_order_items, 'queue_id')) . ')');
        }

        return false;
    }

    /**
     * @param $order_items_collection
     * @param $response
     * @return string
     * @throws OSC_Exception_Runtime
     */
    protected function _updateOrderItemAirtable($order_items_collection, $response) {

        $data = [];
        $records = $response['records'];

        /* @var $order_item Model_Catalog_Order_Item */
        foreach ($order_items_collection as $order_item) {

            $additional_data = $order_item->data['additional_data'];
            $airtable_record_item = array_filter($records, function ($record) use ($order_item) {
                return $record['fields']['Order ID'] == $order_item->data['order_master_record_id'] && $record['fields']['Order Line ID'] == $order_item->getId();
            });
            $airtable_record_item = array_values($airtable_record_item);

            if (isset($airtable_record_item[0]['id']) && $airtable_record_item[0]['id']) {
                $additional_data['sync_airtable_id'] = $airtable_record_item[0]['id'];
                $additional_data['sync_airtable_flag'] = 1;
            }
            $data[$order_item->getId()] = OSC::encode($additional_data);
        }

        $query = '';

        foreach ($data as $master_record_id => $value) {
            $query .= "UPDATE osc_catalog_order_item SET additional_data = '{$value}' WHERE master_record_id = {$master_record_id};";
        }

        return $query;
    }
}
