<?php

class Cron_Catalog_Collection_ResetCacheProductQueue extends OSC_Cron_Abstract
{
    const CRON_SCHEDULER_FLAG = 0;

    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        if (array_key_exists('collection_id', $params) && intval($params['collection_id']) > 0) {
            $collection = OSC::model('catalog/collection')->load(intval($params['collection_id']));

            if ($collection->data['best_selling_start'] && $collection->data['best_selling_end']) {
                $cache_key = OSC::helper('catalog/common')->getAbsoluteCacheKey($collection->data['best_selling_start'], $collection->data['best_selling_end']);
                OSC::helper('catalog/product')->setCacheProductByCatalogCollection($cache_key, $collection->data['best_selling_start'], $collection->data['best_selling_end']);
            }
        }
    }
}
