<?php

class Cron_Catalog_Cache extends OSC_Cron_Abstract
{
    const CRON_TIMER = '* * * * *';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($data, $queue_added_timestamp)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            OSC::helper('core/cache')->resetCacheQueue();
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('resetCacheQueue exception: ' . $ex->getMessage());
        }
    }
}