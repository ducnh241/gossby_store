<?php

class Cron_Filter_SettingFilterCollection extends OSC_Cron_Abstract {

    const CRON_TIMER = '0 10 * * *';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($params, $queue_added_timestamp) {
        $collections = OSC::model('catalog/collection')->getCollection()->load();

        $ukey_list = [];
        $collection_ids = [];

        foreach ($collections as $key => $collection) {
            $ukey_list[] = 'tag/buildFilter:' . $collection->getId();
            $collection_ids[] = $collection->getId();
        }

        array_push($ukey_list, 0);
        array_push($collection_ids, 0);

        $collection_bulk_queue = OSC::model('catalog/product_bulkQueue')->getCollection()
            ->addCondition('ukey', $ukey_list, OSC_Database::OPERATOR_IN)
            ->load();

        if ($collection_bulk_queue->length() > 0) {
            $collection_bulk_queue->delete();
        }

        $order_bulk_queues = [];

        foreach ($collection_ids as $id) {
            $order_bulk_queues[] = [
                'ukey' => 'tag/buildFilter:' . $id,
                'member_id' => 1,
                'action' => 'buildFilter',
                'queue_data' => [
                    'collection_id' => $id
                ]
            ];
        }

        if (OSC::model('catalog/product_bulkQueue')->insertMulti($order_bulk_queues) > 0) {
            OSC::core('cron')->addQueue('filter/setting', null, ['ukey' => 'filter/setting', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);
        }
    }
}