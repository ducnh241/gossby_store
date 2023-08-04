<?php

class Cron_D2_ScanAirtableDesign extends OSC_Cron_Abstract
{
    const CRON_TIMER = '*/10 * * * *';
    const CRON_SCHEDULER_FLAG = 1;

    /**
     * document filter https://support.airtable.com/docs/formula-field-reference
     * https://codepen.io/airtable/full/MeXqOg
     * @param $params
     * @param $queue_added_timestamp
     * @return void
     */
    public function process($params, $queue_added_timestamp)
    {
        $fields = [
            'Status Biz',
            'design.full_front',
            'design.full_back',
            'Order Line ID',
            'Order Code',
            'Order ID'
        ];

        $filter = '{Sync Design Result} = ""';

        $sorts = [[
            'field' => 'Added Queue',
            'direction' => 'asc'
        ]];

        $offset = $params['offset'] ?? null;

        $response = OSC::core('airtable')->filterData($fields, $filter, $sorts, OSC_AIRTABLE_QUEUE_SYNC_DESIGN_TABLE, 100, $offset);

        if (isset($response['content']['error'])) {
            throw new Exception($response['content']['error']['message']);
        }

        $data = $response['content']['records'] ?? [];

        $cron = OSC::model('core/cron_queue')->setCondition('cron_name = "d2/scanAirtableDesign"')->load();
        $cron->setData([
            'queue_data' => [
                'offset' => $response['content']['offset'] ?? null
            ]
        ])->save();

        $ukeys = [];

        foreach ($data as $order_item_airtable) {
            $ukey = 'sync_design:' . $order_item_airtable['id'];
            $ukeys[] = $ukey;

            $order_bulk_queues[] = [
                'ukey' => $ukey,
                'member_id' => 1,
                'action' => 'sync_design_airtable',
                'queue_data' => $order_item_airtable
            ];
        }

        if (!empty($ukeys)) {
            try {

                $collection = OSC::model('catalog/product_bulkQueue')->getCollection()
                    ->addCondition('queue_flag', Model_Catalog_Product_BulkQueue::QUEUE_FLAG['error'])
                    ->addCondition('ukey', $ukeys, OSC_Database::OPERATOR_IN)->load();

                if ($collection->length() > 0) {
                    $collection->delete();
                }

                OSC::model('catalog/product_bulkQueue')->insertMulti($order_bulk_queues);

                OSC::core('cron')->addQueue('d2/syncDesign', null, ['ukey' => 'd2/syncDesign', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);

            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
        }

    }
}
