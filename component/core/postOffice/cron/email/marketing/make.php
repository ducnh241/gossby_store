<?php

class Cron_PostOffice_Email_Marketing_Make extends OSC_Cron_Abstract {

    public function process($data, $queue_added_timestamp) {
        $DB = OSC::core('database');

        if (!file_exists($data['file'])) {
            throw new Exception('Unable to read data file');
        }

        $draft_data = file_get_contents($data['file']);

        if ($draft_data === false) {
            throw new Exception('Unable to read data file');
        }

        $draft_data = OSC::decode($draft_data, true);

        if (!is_array($draft_data)) {
            throw new Exception('Data file is incorrect');
        }

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        foreach ($draft_data as $idx => $row) {
            try {
                OSC::helper('postOffice/email')->create([
                    'priority' => $data['priority'],
                    'running_timestamp' => time() + 10,
                    'email_key' => 'marketing:' . $data['unique'] . ':' . md5($row['email']),
                    'subject' => str_replace(['{{first_name}}', '{{last_name}}', '{{full_name}}'], [$row['first_name'], $row['last_name'], trim($row['first_name'] . ' ' . $row['last_name'])], $data['subject']),
                    'receiver_email' => $row['email'],
                    'receiver_name' => trim($row['first_name'] . ' ' . $row['last_name']),
                    'html_content' => OSC::core('template')->build($data['template'], ['first_name' => $row['first_name'], 'last_name' => $row['last_name']])
                ]);
            } catch (Exception $ex) {
                if (strpos($ex->getMessage(), '1062 Duplicate entry') === false) {
                    continue;
                }
            }

            unset($draft_data[$idx]);

            if (file_put_contents($data['file'], OSC::encode($draft_data)) === false) {
                throw new Exception('Unable to write to data file');
            }
        }

        OSC::core('cron')->addScheduler('postOffice/email_marketing_send', ['processor' => 1, 'priority' => $data['priority']], '* * * * *');
        OSC::core('cron')->addScheduler('postOffice/email_marketing_send', ['processor' => 2, 'priority' => $data['priority']], '* * * * *');

        if (count($draft_data) > 0) {
            throw new Exception('Unable to process all email');
        } else {
            unlink($data['file']);
        }
    }

}
