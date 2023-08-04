<?php

class Cron_Catalog_Product_ResyncSearchIndex extends OSC_Cron_Abstract {
    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        OSC::helper('catalog/search_product')->resync(true);
    }
}