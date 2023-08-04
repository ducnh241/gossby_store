<?php

class Cron_Catalog_Marketing_Email_Send extends OSC_Cron_Abstract {

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
