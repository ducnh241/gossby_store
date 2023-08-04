<?php

class Cron_Catalog_Cart_Backup extends OSC_Cron_Abstract {

    const CRON_SCHEDULER_FLAG = 1;

    public function process($params, $queue_added_timestamp) {
        exec('python3 ' . dirname(__FILE__) . '/backup.py -r ' . OSC_SITE_PATH, $output, $return);
        var_dump($output);
        if ($return > 0) {
            throw new Exception('Something went wrong: ' . OSC::encode($output));
        }
    }
}
