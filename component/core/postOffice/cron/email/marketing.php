<?php

class Cron_PostOffice_Email_Marketing extends OSC_Cron_Abstract {

    public function process($data, $queue_added_timestamp) {
        $tmp_file_path = OSC_Storage::tmpGetFilePath($data['file']);

        if (!$tmp_file_path) {
            throw new Exception('File is not exists or removed');
        }

        $draft_data = OSC::decode(file_get_contents($tmp_file_path), true);

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        foreach ($draft_data as $idx => $row) {
            try {
                OSC::helper('postOffice/email')->create([
                    'priority' => 10,
                    'running_timestamp' => time() + 10,
                    'subject' => $data['subject'],
                    'sender_email' => $data['sender_email'],
                    'sender_name' => $data['sender_name'],
                    'receiver_email' => $idx,
                    'receiver_name' => $row['first_name'],
                    'html_content' => OSC::core('template')->build('postOffice/email/mothersDay', ['receiver_name' => $row['first_name']]),
                    'text_content' => ''
                ]);
            } catch (Exception $ex) {
                if (strpos($ex->getMessage(), '1062 Duplicate entry') === false) {
                    continue;
                }
            }

            unset($draft_data[$idx]);

            if (file_put_contents($tmp_file_path, OSC::encode($draft_data)) === false) {
                throw new Exception('Unable to write to data file');
            }
        }

        OSC::core('cron')->addScheduler('postOffice/email_marketing_send', ['processor' => 1, 'priority' => 10], '* * * * *');
        OSC::core('cron')->addScheduler('postOffice/email_marketing_send', ['processor' => 2, 'priority' => 10], '* * * * *');
        OSC::core('cron')->addScheduler('postOffice/email_marketing_send', ['processor' => 3, 'priority' => 10], '* * * * *');
        OSC::core('cron')->addScheduler('postOffice/email_marketing_send', ['processor' => 4, 'priority' => 10], '* * * * *');
        OSC::core('cron')->addScheduler('postOffice/email_marketing_send', ['processor' => 5, 'priority' => 10], '* * * * *');
        OSC::core('cron')->addScheduler('postOffice/email_marketing_send', ['processor' => 6, 'priority' => 10], '* * * * *');

        if (count($draft_data) > 0) {
            throw new Exception('Unable to process all email');
        } else {
            unlink($tmp_file_path);
        }
    }

}
