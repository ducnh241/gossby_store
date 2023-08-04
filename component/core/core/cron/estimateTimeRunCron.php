<?php

class Cron_Core_EstimateTimeRunCron extends OSC_Cron_Abstract {

    const CRON_TIMER = '@hourly';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($params, $queue_added_timestamp) {

        $collection = OSC::model('core/cron_queue')->getCollection()
            ->setCondition('( `running_timestamp` + `estimate_timestamp` ) < '. time())
            ->setLimit(1000)
            ->load();

        if ($collection->length() < 1) {
            return;
        }

        $notification = [];

        foreach ($collection as $model) {
            $notification[] = $model->data['cron_name'];
            if ($model->data['error_flag'] == 1 && strpos($model->data['error_message'],'Deadlock') != False) {
                $model->setData(['error_flag' => 0, 'error_message' => ''])->save();
            }
        }

        $message = OSC::$base_url.' have cron name: '.implode(',', $notification). ' exceeded time allowed';

        OSC::helper('core/telegram')->send($message);
    }

}
