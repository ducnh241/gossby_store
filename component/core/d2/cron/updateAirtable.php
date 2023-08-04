<?php

class Cron_D2_UpdateAirtable extends OSC_Cron_Abstract
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
        /* @var $DB_MASTER OSC_Database_Adapter */
        $DB_MASTER = OSC::core('database')->getAdapter('db_master');

        $limit = 100;
        $counter = 0;

        $updateRawData = [];


        while ($counter < $limit) {
            $model = OSC::model('catalog/order_bulkQueue');

            $DB_MASTER->select('*', $model->getTableName(), "queue_flag = 1 AND action = 'update_airtable'", 'added_timestamp ASC', 1, 'fetch_queue');
            $row = $DB_MASTER->fetchArray('fetch_queue');
            $DB_MASTER->free('fetch_queue');
            if (!$row) {
                break;
            }

            $counter++;
            $model->bind($row);
            $model->setData(['queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['running']])->save();

            try {
                $data = $model->data['queue_data'];

                $product_id = $data['product_id'];
                $order_id = $model->data['order_master_record_id'];
                $order_line_id = explode('_', $model->data['secondary_key'])[0];

                $d2_product = OSC::model('d2/product')->getCollection()
                    ->addCondition('product_id', $product_id, OSC_Database::OPERATOR_EQUAL)
                    ->load();

                if ($d2_product->length()) {

                    /* @var $order_line_item Model_Catalog_Order_Item */
                    $order_line_item = OSC::model('catalog/order_item')->load($order_line_id);

                    if ($data['key'] == 'order_update_status') {

                        $record = OSC::helper('d2/common')->getRecordUpdateOrderStatus($data, $order_id, $order_line_item);

                        $updateRawData[] = [
                            'ukey' => "updateAirtable_{$order_line_id}_:" . OSC::makeUniqid(),
                            'member_id' => 1,
                            'action' => 'update_raw_airtable',
                            'queue_data' => $record
                        ];
                    }
                }

                $model->delete();

            } catch (Exception $ex) {
                $model->setData(['queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['error'], 'error' => 'Error:: ' . $ex->getMessage()])->save();
            }
        }

        if (!empty($updateRawData)) {
            OSC::model('catalog/product_bulkQueue')->insertMulti($updateRawData);
            OSC::core('cron')->addQueue('d2/updateRawAirtable', null, ['ukey'=> 'd2/updateRawAirtable','requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60]);
        }

        if ($counter >= $limit) {
            return false;
        }

        return true;
    }

}
