<?php

class Cron_PostOffice_Email_Queue_Send extends OSC_Cron_Abstract {

    public function process($data, $queue_added_timestamp) {
        /* @var $queue Model_PostOffice_Email_Queue */
        try {
            $queue = OSC::model('postOffice/email_queue')->load($data['id']);

            OSC::helper('postOffice/email')->executeQueue($queue);
        } catch (Exception $ex) {
            if ($ex->getCode() == 404) {
                return;
            }

            throw new Exception($ex->getMessage());
        }
    }

}
