<?php

class Cron_PersonalizedDesign_DeleteRerenderLog extends OSC_Cron_Abstract {

    const CRON_TIMER = '0 0 * * 0'; // one time/week
    const CRON_SCHEDULER_FLAG = 1;
    /**
     * @param $params
     * @param $queue_added_timestamp
     * @return void
     */
    public function process($params, $queue_added_timestamp)
    {
        $logs = OSC::model('personalizedDesign/rerenderLog')->getCollection()
            ->addCondition('added_timestamp', strtotime('-60 day', time()), OSC_Database::OPERATOR_LESS_THAN)
            ->load();

        if ($logs->length()) {
            $logs->delete();
        }
    }
}