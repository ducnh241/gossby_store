<?php

class Cron_Filter_ExportKeyword extends OSC_Cron_Abstract
{
    const CRON_TIMER = '0 2 */3 * *';
    const CRON_SCHEDULER_FLAG = 1;

    /**
     * @throws Exception
     */
    public function process($params, $queue_added_timestamp)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $start_date = time() - 60 * 60 * 24 * 7 * 2;
        $end_date = time();

        exec("python3 " . dirname(__FILE__) . "/export.py -r " . OSC_SITE_PATH . " -s {$start_date} -e {$end_date} 2>&1", $output, $return);

        $data = $output[0] ?? '';
        $data = OSC::decode($data);
        if ($data) {
            $results = [];
            foreach ($data as $key => $value) {
                $results[urldecode($key)] = $value;
            }
            $results = array_slice($results, 0, 20);

            OSC::helper('core/setting')->set('search/trending_keywords', $results);
        } else {
            throw new Exception('Can not export keyword');
        }
    }

}