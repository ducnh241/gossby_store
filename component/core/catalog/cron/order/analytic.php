<?php

class Cron_Catalog_Order_Analytic extends OSC_Cron_Abstract
{
    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $file_url = $this->__renderExcel($params['export_key'], $params['shop_domain'], $params['start_date'], $params['end_date'], $params['list_product_id']);

        $expired_time = date('d/m/Y H:i:s', time() + (60 * 60 * 24));

        $email_content = <<<EOF
Your report has been generated,
Click the URL below to download:
<a href="{$file_url}">{$file_url}</a>

The URL will be expired at {$expired_time}
EOF;

        OSC::helper('postOffice/email')->create([
            'priority' => 1000,
            'subject' => 'Analytic orders from ' . OSC::helper('core/setting')->get('theme/site_name') . ' [' . $params['range_text'] . ']' . ': ' . date('d/m/Y H:i:s'),
            'receiver_email' => $params['receiver']['email'],
            'receiver_name' => $params['receiver']['name'],
            'html_content' => implode('<br />', explode("\n", $email_content)),
            'text_content' => strip_tags($email_content, '<br>')
        ]);
    }

    private function __renderExcel($report_key, $shop_domain, $start_date, $end_date, $list_product_id) {
        $export_file_name = 'catalog/order/export/' . $report_key . '.' . date('d-m-Y') . '.xlsx';
        $excel_file_path = OSC_Storage::preDirForSaveFile($export_file_name);
        $command = "python3 " . dirname(__FILE__) . "/analyticOrder.py -f {$excel_file_path} -d {$shop_domain} -r " . OSC_SITE_PATH . " -s {$start_date} -e {$end_date} -l {$list_product_id} 2>&1";
        exec($command, $output, $return);

        if ($return > 0) {
            OSC::writeToFile($excel_file_path . '.output', OSC::encode($output) . "\n", ['append' => true]);
            throw new Exception('Something went wrong: ' . $command);
        }

        while (true) {
            if (file_exists($excel_file_path)) {
                return OSC_Storage::tmpGetFileUrl($export_file_name);
            } else if (file_exists($excel_file_path . '.error')) {
                throw new Exception(file_get_contents($excel_file_path . '.error'));
            }

            sleep(1);
        }
    }
}