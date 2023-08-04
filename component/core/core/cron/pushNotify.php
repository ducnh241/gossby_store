<?php

class Cron_Core_PushNotify extends OSC_Cron_Abstract
{
    const CRON_TIMER = '* * * * *';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 100;
        $delete_queue_ids = [];

        try {
            $DB->select('*', 'core_notify_queue', "`queue_flag` = 1", '`added_timestamp` ASC', $limit, 'fetch_queue');

            $rows = $DB->fetchArrayAll('fetch_queue');

            $DB->free('fetch_queue');

            foreach ($rows as $row) {
                $queue_id = intval($row['queue_id']);
                $queue_data = OSC::decode($row['queue_data']);

                try {
                    OSC::helper('core/telegram')->sendDirectMessage(
                        $queue_data['message'],
                        $queue_data['telegram_group_id'],
                        $queue_data['token_bot']
                    );
                } catch (Exception $ex) {
                    $DB->update(
                        'core_notify_queue', [
                            'queue_flag' => 1,
                            'error_message' => $ex->getMessage()
                        ],
                        'queue_id=' . $queue_id,
                        1,
                        'update_notify_queue'
                    );

                    continue;
                }

                $delete_queue_ids[] = $queue_id;
            }

            $delete_queue_ids = implode(',', $delete_queue_ids);
            $DB->delete('core_notify_queue', 'queue_id IN (' . $delete_queue_ids . ')', $limit, 'delete_notify_queue');
        } catch (Exception $ex) {
            //
        }
    }
}
