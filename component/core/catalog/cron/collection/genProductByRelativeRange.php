<?php

class Cron_Catalog_Collection_GenProductByRelativeRange extends OSC_Cron_Abstract
{
    const CRON_SCHEDULER_FLAG = 1;

    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        /* @var $DB OSC_Database */
        $DB = OSC::core('database');
        $DB->query("SELECT relative_range FROM osc_catalog_collection WHERE relative_range IS NOT NULL AND relative_range != '' GROUP BY relative_range;");

        while ($row = $DB->fetchArray()) {
            $relative_range = $row['relative_range'];

            $cache_key = OSC::helper('catalog/common')->getRelativeCacheKey($relative_range);

            $range_start = OSC::helper('catalog/common')->startOfDays("-{$relative_range}");
            $range_end = OSC::helper('catalog/common')->endOfDays("-1");

            OSC::helper('catalog/product')->setCacheProductByCatalogCollection($cache_key, $range_start, $range_end);
        }
    }
}
