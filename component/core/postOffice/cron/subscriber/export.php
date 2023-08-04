<?php

class Cron_PostOffice_Subscriber_Export extends OSC_Cron_Abstract
{

    public function process($params, $queue_added_timestamp)
    {
        $file_url = static::renderExcel($params['export_key'], $params['subscriber_ids']);

        $expired_time = date('d/m/Y H:i:s', time() + (60 * 60 * 24));

        $email_content = <<<EOF
        Subscriber has been exported,
        Click the URL below to download:
        <a href="{$file_url}">{$file_url}</a>
        
        The URL will be expired at {$expired_time}
EOF;

        $klaviyo_api_key = OSC::helper('klaviyo/common')->getApiKey();

        if ($klaviyo_api_key != '') {
            $data = [
                'token' => $klaviyo_api_key,
                'event' => 'System office',
                'customer_properties' => [
                    '$email' =>  $params['receiver']['email'],
                ],
                'properties' => [
                    'title' => 'Exported subscribers from ' . OSC::helper('core/setting')->get('theme/site_name') . ' at ' . date('d/m/Y H:i:s'),
                    'message' => implode('<br />', explode("\n", $email_content)),
                    'text' => strip_tags($email_content, '<br>')
                ],
                'time' => time(),
            ];
            OSC::helper('klaviyo/common')->create($data);

        }

        $skip_amazon = intval(OSC::helper('core/setting')->get('tracking/klaviyo/skip_amazon'));
        if ($skip_amazon != 1) {
            OSC::helper('postOffice/email')->create([
                'priority' => 2000,
                'subject' => 'Exported subscribers from ' . OSC::helper('core/setting')->get('theme/site_name') . ' at ' . date('d/m/Y H:i:s'),
                'receiver_email' => $params['receiver']['email'],
                'receiver_name' => $params['receiver']['name'],
                'html_content' => implode('<br />', explode("\n", $email_content)),
                'text_content' => strip_tags($email_content, '<br>')
            ]);
        }
    }

    protected function _exportColumnData($column_export, $_value)
    {

        $data = [];
        foreach ($column_export as $key => $value) {
            switch ($key) {
                case "subscriber_id":
                    $data['subscriber_id'] = $_value['subscriber_id'];
                    break;
                case "email":
                    $data['email'] = $_value['email'];
                    break;
                case "added_timestamp":
                    $data['added_timestamp'] = date(' H:i:s_d/m/Y', $_value['added_timestamp']);
                    break;
                case "flag_action":
                    if ($_value['flag_action'] == 3){
                        $data['flag_action'] = 'Order';
                    }elseif ($_value['flag_action'] == 2){
                        $data['flag_action'] = 'Abandon';
                    }else{
                        $data['flag_action'] = 'Subscriber';
                    }
                    break;
                case "newsletter":
                    $data['newsletter'] = $_value['newsletter'] == 1 ? 'Subscriber' : 'UnSubscriber';
                    break;
                case "confirm":
                    $data['confirm'] = $_value['confirm'] == 1 ? 'Confirm' : 'UnConfirm';
                    break;
            }
        }

        return $data;
    }

    public static function renderExcel($export_key, $subscriber_ids)
    {
        $draft_data = [
            'subscriber_ids' => $subscriber_ids,
            'headers' => []
        ];
        sort($draft_data['subscriber_ids']);
        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        $header_standard_title = [
            "subscriber_id" => "Subscriber ID",
            "email" => "Email",
            "added_timestamp" => 'Added Timestamp',
            "flag_action" => 'Status',
            "newsletter" => 'Sub/Unsub',
            "confirm" => 'Confirm'
        ];

        while (count($draft_data['subscriber_ids']) > 0) {
            $_subscriber_ids = implode(',', array_splice($draft_data['subscriber_ids'], 0, 500));

            $DB->select('*', 'postOffice/subscriber', 'subscriber_id IN (' . $_subscriber_ids . ')', 'subscriber_id ASC', null, 'fetch_subscriber_info');
            $counter = 0;
            while ($row = $DB->fetchArray('fetch_subscriber_info')) {
                $counter++;
                $row_data = static::_exportColumnData($header_standard_title, $row);
                try {
                    OSC::core('database')->insert('subscribers_export_draft', [
                        'export_key' => $export_key,
                        'subscriber_id' => $row['subscriber_id'],
                        'export_data' => OSC::encode($row_data),
                        'added_timestamp' => time()
                    ]);

                } catch (Exception $ex) {
                    if (strpos($ex->getMessage(), '1062 Duplicate entry') === false) {
                        throw new Exception($ex->getMessage());
                    }
                }
            }

            $DB->free('fetch_subscriber_info');

        }
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $file_name = 'export/subscribers/' . $export_key . '.' . date('d-m-Y') . '.xlsx';

        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $cell_idx = 0;

        foreach ($header_standard_title as $k => $v) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(++$cell_idx) . 1, $v);
        }

        $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($header_standard_title)) . 1)->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('89B7E5');
        $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($header_standard_title)) . 1)->getFont()->getColor()->setARGB('FFFFFF');

        /* @var $DB OSC_Database */
        $DB = OSC::core('database');
        while (true) {
            $DB->select('record_id,export_data', 'subscribers_export_draft', "export_key = '{$export_key}'", 'record_id ASC', 1000, 'fetch_export_draft_item');

            $sheet_row_index = 2;

            while ($row = $DB->fetchArray('fetch_export_draft_item')) {
                $counter++;
                $last_record_id = $row['record_id'];
                $data = OSC::decode($row['export_data'], true);

                $u = 0;
                foreach ($data as $i => $value) {
                    $sheet->setCellValueExplicit(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($u + 1) . $sheet_row_index, $value, PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $u++;
                }
                $sheet_row_index++;

            }
            if ($counter > 0) {
                $DB->delete('subscribers_export_draft', "export_key = '{$export_key}' AND record_id <= {$last_record_id}", $counter, 'remove_export_draft_item');
            }
            $file_path = OSC_Storage::preDirForSaveFile($file_name);

            $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $writer->save($file_path);
            return OSC_Storage::tmpGetFileUrl($file_name);
        }
    }

}