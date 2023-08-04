<?php

class Cron_PersonalizedDesign_AnalyticProcessQueue extends OSC_Cron_Abstract {

    const CRON_TIMER = '0 * * * *';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($data, $queue_added_timestamp) {
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->select('*', 'personalized_design_analytic_process_queue', "locked_key = ''", 'record_id ASC', 500, 'fetch_process_queue');

        $locked_key = OSC::makeUniqid();

        while($row = $DB->fetchArray('fetch_process_queue')) {
            if($DB->update('personalized_design_analytic_process_queue', ['locked_key' => $locked_key, 'locked_timestamp' => time()], "record_id = {$row['record_id']} AND locked_key = ''", 1, 'locked_processing_queue') < 1) {
                continue;
            }

            $row['queue_data'] = OSC::decode($row['queue_data']);

            foreach($row['queue_data'] as $item) {
                if($item['operator'] == 'increment') {
                    $operator = '+';
                    $value = '1';
                    $max_value = 0;
                } else {
                    $operator = '-';
                    $value = '0';
                    $max_value = 1;
                }

                foreach($item['data'] as $k => $v) {
                    $params = [
                        'design_id' => $item['design_id'],
                        'option_key' => $k,
                        'value_key' => $v['value'],
                        'value_hash' => $v['value_hash'],
                        'layer_name' => $v['layer'],
                        'form_name' => $v['form'],
                        'parsed_value' => $v['parsed_value']
                    ];

                    try {
                        $DB->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "personalized_design_analytic (design_id, option_key, value_key, value_hash, layer_name, form_name, parsed_value, counter) VALUES (:design_id, :option_key, :value_key, :value_hash, :layer_name, :form_name, :parsed_value, {$value}) ON DUPLICATE KEY UPDATE counter=IF(counter > {$max_value}, counter, {$max_value}) {$operator} 1", $params, 'update_report_record');
                    } catch (Exception $ex) {
                        echo $ex->getMessage() . "\n";                        
                    }
                }
            }

            try {
                $DB->delete('personalized_design_analytic_process_queue', 'record_id = ' . $row['record_id'], 1, 'remove_processing_queue');
            } catch (Exception $ex) {

            }
        }
    }
}
