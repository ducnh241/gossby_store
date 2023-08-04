<?php

class Cron_PostOffice_Email_Queue_Resend extends OSC_Cron_Abstract {

    const CRON_TIMER = '@daily';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($data, $queue_added_timestamp) {
        $DB = OSC::core('database');

        while (true) {
            $queue = OSC::model('postOffice/email_queue');

            $DB->select('*', $queue->getTableName(), "`state` = 'queue' AND `running_timestamp` <= " . time(), '`priority` DESC, `added_timestamp` ASC', 1, 'fetch_email');

            $row = $DB->fetchArray('fetch_email');

            $DB->free('fetch_email');

            if (!$row) {
                break;
            }

            $queue->bind($row);

            OSC::helper('postOffice/email')->executeQueue($queue);
        }
    }

}
