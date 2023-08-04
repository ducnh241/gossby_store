<?php

class Cron_Core_GenerateRobotTxt extends OSC_Cron_Abstract
{
    const CRON_SCHEDULER_FLAG = 0;

    public function process($params, $queue_added_timestamp)
    {
        OSC::writeToFile(OSC_SITE_PATH . '/robots.txt', OSC::helper('core/setting')->get('theme/metadata/robots'));
    }

}