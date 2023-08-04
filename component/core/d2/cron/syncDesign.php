<?php

class Cron_D2_SyncDesign extends OSC_Cron_Abstract
{
    /**
     * @param $params
     * @param $queue_added_timestamp
     * @return bool
     */
    public function process($params, $queue_added_timestamp)
    {
        /* @var $DB OSC_Database_Adapter */
        $DB_STORE = OSC::core('database')->getWriteAdapter();

        $limit = 10;

        $bulk_queue = OSC::model('catalog/product_bulkQueue');


        $DB_STORE->select('*', $bulk_queue->getTableName(), "`queue_flag` = 1 AND `action` = 'sync_design_airtable'", '`added_timestamp` ASC', $limit, 'fetch_queue');

        $airtable_order_items = $DB_STORE->fetchArrayAll('fetch_queue');

        $DB_STORE->free('fetch_queue');

        if (empty($airtable_order_items)) {
            return true;
        }

        $DB_STORE->update($bulk_queue->getTableName(), ['queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['running']], 'queue_id IN (' . implode(',', array_column($airtable_order_items, 'queue_id')) . ')');

        $order_sync_designs = [];

        foreach ($airtable_order_items as $airtable_item) {
            $queue = OSC::model('catalog/product_bulkQueue');
            $queue->bind($airtable_item);

            $queue_data = $queue->data['queue_data'];
            $order_sync_designs[] = $queue_data['fields']['Order ID'];
        }

        $record_deletes = [];

        /* @var Model_Catalog_Order_Collection $order_collection */
        $order_collection = OSC::model('catalog/order')->getCollection()
            ->addCondition('master_record_id', $order_sync_designs, OSC_Database::OPERATOR_IN)
            ->load()->preLoadLineItems();

        $record_logs = [];

        foreach ($airtable_order_items as $airtable_item) {
            try {
                $queue = OSC::model('catalog/product_bulkQueue');
                $queue->bind($airtable_item);

                $queue_data = $queue->data['queue_data'];
                $fields = $queue_data['fields'];

                /* @var Model_Catalog_Order $order */
                $order = $order_collection->getItemByPK($fields['Order ID']);

                $record_deletes[] = $queue_data['id'];

                $record_logs[$queue_data['id']] = [
                    'fields' => [
                        'Order Code' => $fields['Order Code'],
                        'Order ID' => intval($fields['Order ID']),
                        'Order Line ID' => $fields['Order Line ID'],
                        'Status Biz' => $fields['Status Biz'],
                        'design.full_front' => $fields['design.full_front'] ?? '',
                        'design.full_back' => $fields['design.full_back'] ?? ''
                    ]
                ];

                // Validate order
                if (!$order || $order->data['code'] != $fields['Order Code']) {

                    $record_logs[$queue_data['id']]['fields']['Sync Design Result'] = 'Error: Order is not Exist!';

                    continue;
                }

                // Validate order line on airtable
                $order_line_items = $order->getLineItems();
                /* @var Model_Catalog_Order_Item $order_line_id_by_pk */
                $order_line_id_by_pk = $order_line_items->getItemByPK($fields['Order Line ID']);

                if (!$order_line_id_by_pk || !isset($order_line_id_by_pk->data['additional_data']['sync_airtable_flag']) || $order_line_id_by_pk->data['additional_data']['sync_airtable_flag'] != 1) {

                    $record_logs[$queue_data['id']]['fields']['Sync Design Result'] = 'Error: Order Line Item is not sync Airtable!';

                    continue;
                }

                // Validate printer design url
                $design_front = trim($fields['design.full_front']) ?? '';
                $design_back = trim($fields['design.full_back']) ?? '';
                if ( ($design_front && !$this->_isExistUrl($design_front)) || ($design_back && !$this->_isExistUrl($design_back)) ) {

                    $record_logs[$queue_data['id']]['fields']['Sync Design Result'] = 'Error: Printer Design Url is not exist!';

                    continue;
                }

                $design_url = $order_line_id_by_pk->data['design_url'];
                if ($design_front) {
                    $design_url['beta']['front'] = $design_front;
                } else {
                    unset($design_url['beta']['front']);
                }
                if ($design_back) {
                    $design_url['beta']['back'] = $design_back;
                } else {
                    unset($design_url['beta']['back']);
                }

                $order_line_id_by_pk->setData([
                    'design_url' => $design_url
                ])->save();

                $record_logs[$queue_data['id']]['fields']['Sync Design Result'] = 'Success';

            } catch (Exception $ex) {
                $queue->setData([
                    'queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['error'],
                    'error' => $ex->getMessage()
                ])->save();
            }
        }

        if (!empty($record_logs)) {
            try {
                $response = OSC::core('airtable')->createData(array_values($record_logs), OSC_AIRTABLE_LOG_SYNC_DESIGN_TABLE);
                if (isset($response['content']['error'])) {
                    throw new Exception($response['content']['error']['message']);
                }

                $res_delete = OSC::core('airtable')->deleteData($record_deletes, OSC_AIRTABLE_QUEUE_SYNC_DESIGN_TABLE);
                if (isset($res_delete['content']['error'])) {
                    throw new Exception('Error delete airtable: ' . $res_delete['content']['error']['message']);
                }

                $DB_STORE->delete($bulk_queue->getTableName(), 'queue_id IN (' . implode(',', array_column($airtable_order_items, 'queue_id')) . ') AND queue_flag = ' . Model_Catalog_Order_BulkQueue::QUEUE_FLAG['running'], null, 'delete_sync');
                $DB_STORE->free('delete_sync');

            } catch (Exception $ex) {
                $DB_STORE->update($bulk_queue->getTableName(),
                    [
                        'queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['error'],
                        'error' => $ex->getMessage()
                    ],
                    'queue_id IN (' . implode(',', array_column($airtable_order_items, 'queue_id')) . ') AND queue_flag = ' . Model_Catalog_Order_BulkQueue::QUEUE_FLAG['running']);
            }
        }
        return false;
    }

    protected function _isExistUrl(string $url): bool
    {
        $url = trim($url);
        $header = @get_headers($url);
        return (!empty($header) && is_array($header) && !strpos($header[0], '404'));
    }
}
