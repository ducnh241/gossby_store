<?php

class Cron_PostOffice_Email_Marketing_Send extends OSC_Cron_Abstract {

    public function process($data, $queue_added_timestamp) {
        $DB = OSC::core('database');

        $counter = 0;
        
        while ($counter < 1500) {
            $queue = OSC::model('postOffice/email_queue');

            $DB->select('*', $queue->getTableName(), "`state` = 'queue' AND `priority` = " . $data['priority'], '`added_timestamp` ASC', 1, 'fetch_email');

            $row = $DB->fetchArray('fetch_email');

            $DB->free('fetch_email');

            if (!$row) {
                break;
            }

            $queue->bind($row);

            OSC::helper('postOffice/email')->executeQueue($queue);
            
            $counter ++;
        }
    }

}
