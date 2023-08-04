<?php

class Cron_D2_UpdateRawAirtable extends OSC_Cron_Abstract
{

    /**
     * document filter https://support.airtable.com/docs/formula-field-reference
     * https://codepen.io/airtable/full/MeXqOg
     * @param $params
     * @param $queue_added_timestamp
     * @return void
     */
    public function process($params, $queue_added_timestamp)
    {

        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getAdapter();
        $limit = 10;

        $table = OSC::model('catalog/product_bulkQueue')->getTableName();

        $DB->select('*', $table, "`queue_flag` = 1 AND `action` = 'update_raw_airtable'", '`added_timestamp` ASC', $limit, 'fetch_queue');

        $rows = $DB->fetchArrayAll('fetch_queue');
        if (empty($rows)) {
            return true;
        }
        $DB->free('fetch_queue');

        $queue_ids = array_column($rows, 'queue_id');

        $DB->update($table, ['queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['running']], 'queue_id IN (' . implode(',', $queue_ids) . ')');

        $records = [];
        $order_line_ids = [];
        $bulk_queue_collection = OSC::model('catalog/product_bulkQueue')->getCollection();
        $order_line_items = OSC::model('catalog/order_item')->getCollection();

        foreach ($rows as $row) {

            $bulk_queue = OSC::model('catalog/product_bulkQueue');
            $bulk_queue->bind($row);
            $bulk_queue_collection->addItem($bulk_queue);

            $record = $bulk_queue->data['queue_data'];
            $order_line_id = explode('_', $bulk_queue->data['ukey'])[1] ?? '';
            if (intval($order_line_id)) {
                $order_line_ids[$order_line_id] = $order_line_id;
            }

            foreach ($record['fields'] as $field => $value) {
                $records[$record['id']]['id'] = $record['id'];
                $records[$record['id']]['fields'][$field] = $value;
            }
            OSC::logFile('Order Line ID: ' . (explode('_', $bulk_queue->data['ukey'])[1] ?? '') . ': ' . OSC::encode($record), 'update_raw_airtable' . date('Ymd'));
        }

        if (!empty($records)) {

            $order_line_items->load($order_line_ids);

            try {
                OSC::logFile('update_raw_airtable: ' . OSC::encode($records), 'update_raw_airtable' . date('Ymd'));
                $res_upd = OSC::core('airtable')->updateData(array_values($records), OSC_AIRTABLE_ORDER_LINE_TABLE);
                if (isset($res_upd['content']['error'])) {
                    throw new Exception('Error update airtable: ' . $res_upd['content']['error']['message']);
                }

                $DB->delete($table, 'queue_id IN (' . implode(',', $queue_ids) . ')', null, 'delete_sync');
                $DB->free('delete_sync');

            } catch (Exception $ex) {

                $message_error = $ex->getMessage();
                if (strpos($message_error, 'does not exist in this table') !== false) {
                    /* @var $queue Model_Catalog_Product_BulkQueue  */
                    foreach ($bulk_queue_collection as $queue) {
                        $queue_data = $queue->data['queue_data'];
                        $airtable_id = $queue_data['id'];

                        if (strpos($message_error,  "{$airtable_id} does not exist in this table") === false) {
                            $queue->setData([
                                'queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['queue']
                            ])->save();
                            if (($key = array_search($queue->getId(), $queue_ids)) !== false) {
                                unset($queue_ids[$key]);
                            }
                        } else {

                            try {
                                $order_line_id = explode('_', $queue->data['ukey'])[1] ?? '';
                                $airtable_id_filter = OSC::helper('d2/common')->filterOrderItemAirtableId("{Order Line ID} = {$order_line_id}");
                                if ($airtable_id_filter) {
                                    $queue_data['id'] = $airtable_id_filter;

                                    $order_item = $order_line_items->getItemByPK($order_line_id);

                                    $addition_data = $order_item->data['additional_data'];
                                    $addition_data['sync_airtable_id'] = $airtable_id_filter;
                                    $order_item->setData([
                                        'additional_data' => $addition_data
                                    ])->save();

                                    $queue->setData([
                                        'queue_data' => $queue_data,
                                        'queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['queue']
                                    ])->save();

                                    if (($key = array_search($queue->getId(), $queue_ids)) !== false) {
                                        unset($queue_ids[$key]);
                                    }
                                }
                            } catch (Exception $ex) {}
                        }
                    }
                }

                if (!empty($queue_ids)) {
                    $DB->update($table,
                        [
                            'queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['error'],
                            'error' => $message_error
                        ],
                        'queue_id IN (' . implode(',', $queue_ids) . ')');
                }
            }
        }

        return false;

    }
}
