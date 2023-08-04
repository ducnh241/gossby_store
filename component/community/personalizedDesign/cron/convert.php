<?php

class Cron_PersonalizedDesign_Convert extends OSC_Cron_Abstract {
    public function process($data, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        OSC::helper('personalizedDesign/convert')->parseConfigListPersonalizedUrl();
    }
}