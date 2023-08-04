<?php

class Cron_Report_InsertAdTracking extends OSC_Cron_Abstract
{
    const CRON_TIMER = '* * * * *';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($data, $queue_added_timestamp)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            OSC::helper('report/adTracking')->insertAdTracking();
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('insertAdTracking exception: ' . $ex->getMessage());
        }
    }
}