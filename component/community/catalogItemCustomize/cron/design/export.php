<?php

class Cron_CatalogItemCustomize_Design_Export extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $collection = OSC::model('catalogItemCustomize/design')->getCollection()->load($params['record_ids']);

        $file_url = static::renderExcel($collection);

        $expired_time = date('d/m/Y H:i:s', time() + (60 * 60 * 24));

        $email_content = <<<EOF
Your customize design data has been exported,
Click the URL below to download:
<a href="{$file_url}">{$file_url}</a>

The URL will be expired at {$expired_time}
EOF;


        $klaviyo_api_key = OSC::helper('klaviyo/common')->getApiKey();

        if ($klaviyo_api_key != '') {
            OSC::helper('klaviyo/common')->create([
                'token' => $klaviyo_api_key,
                'event' => 'System office',
                'customer_properties' => [
                    '$email' => $params['receiver']['email']
                ],
                'properties' => [
                    'receiver_email' => $params['receiver']['email'],
                    'receiver_name' => $params['receiver']['name'],
                    'title' => 'Exported customize design data from ' . OSC::helper('core/setting')->get('theme/site_name') . ' at ' . date('d/m/Y H:i:s'),
                    'message' => implode('<br />', explode("\n", $email_content)),
                    'text' => strip_tags($email_content, '<br>')
                ]
            ]);

        }

        $skip_amazon = intval(OSC::helper('core/setting')->get('tracking/klaviyo/skip_amazon'));
        if ($skip_amazon != 1) {
            OSC::helper('postOffice/email')->create([
                'priority' => 1000,
                'subject' => 'Exported customize design data from ' . OSC::helper('core/setting')->get('theme/site_name') . ' at ' . date('d/m/Y H:i:s'),
                'receiver_email' => $params['receiver']['email'],
                'receiver_name' => $params['receiver']['name'],
                'html_content' => implode('<br />', explode("\n", $email_content)),
                'text_content' => strip_tags($email_content, '<br>')
            ]);
        }
    }

    protected static function _fetchSheetHeaders(&$headers, $customize_data, $path = '') {
        foreach ($customize_data as $entry) {
            if (isset($entry['layer_key']) && $entry['layer_key']) {
                $header_name = $entry['layer_key'];
            } else {
                $header_name = ($path == '' ? '' : $path . ' | ') . $entry['title'];
            }

            if (!in_array($header_name, $headers)) {
                $headers[] = $header_name;
            }

            if (isset($entry['value']['components']) && is_array($entry['value']['components']) && count($entry['value']['components']) > 0) {
                static::_fetchSheetHeaders($headers, $entry['value']['components'], $header_name);
            }
        }
    }

    protected static function _fetchRowData(&$row, $headers, $customize_data, $path = '') {
        foreach ($customize_data as $entry) {
            if (isset($entry['layer_key']) && $entry['layer_key']) {
                $header_name = $entry['layer_key'];
            } else {
                $header_name = ($path == '' ? '' : $path . ' | ') . $entry['title'];
            }

            $value = '';

            if (isset($entry['value']['selected'])) {
                $value = $entry['value']['selected'];
            } else {
                $value = $entry['value'];
            }

            if (is_array($value)) {
                if (isset($value['url'])) {
                    $value = $value['title'];
                } else {
                    $value = implode(', ', $value);
                }
            }

            $column_idx = array_search($header_name, $headers, true);

            if ($column_idx >= 0) {
                $row[$column_idx] = $value;
            }

            if (isset($entry['value']['components']) && is_array($entry['value']['components']) && count($entry['value']['components']) > 0) {
                static::_fetchRowData($row, $headers, $entry['value']['components'], $header_name);
            }
        }
    }

    public static function renderExcel($collection) {
        /* @var $design Model_CatalogItemCustomize_Design */

        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();

        $sheets = [];
        $sheet_idx = 0;

        foreach ($collection as $design) {
            $sheet_key = $design->data['customize_id'] . ':' . $design->data['product_id'];
            
            if (!isset($sheets[$sheet_key])) {
                $sheets[$sheet_key] = [
                    'row_idx' => 2,
                    'title' => $design->data['customize_title'] . ' [' . $design->data['product_title'] . ']',
                    'sheet' => $spreadsheet->createSheet($sheet_idx++),
                    'headers' => ['Order id', 'Design key', 'Printer Design URL']
                ];

                $sheets[$sheet_key]['sheet']->setTitle($design->data['customize_id'] . ' - ' . $design->data['product_id']);
            }

            static::_fetchSheetHeaders($sheets[$sheet_key]['headers'], $design->data['customize_data']);

            $row = [];
            $row[] =  $design->data['order_id'];
            $row[] = [
                'value' => $design->getUkey(),
                'style' => [
                    'bgcolor' => $design->data['state'] == 3 ? '00CE83' : 'E58989',
                    'color' => 'FFFFFF'
                ]
            ];

            $row[] = $design && $design->data['printer_image_url'] ? $design->data['printer_image_url'] : '';

            static::_fetchRowData($row, $sheets[$sheet_key]['headers'], $design->data['customize_data']);

            $sheets[$sheet_key]['row_idx'] ++;

            foreach ($row as $i => $value) {
                $sheets[$sheet_key]['sheet']->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . $sheets[$sheet_key]['row_idx'], is_array($value) ? $value['value'] : $value);

                if (is_array($value) && isset($value['style']) && is_array($value['style'])) {
                    $cell_key = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . $sheets[$sheet_key]['row_idx'] . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . $sheets[$sheet_key]['row_idx'];

                    if (isset($value['style']['bgcolor'])) {
                        $sheets[$sheet_key]['sheet']->getStyle($cell_key)->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($value['style']['bgcolor']);
                    }

                    if (isset($value['style']['color'])) {
                        $sheets[$sheet_key]['sheet']->getStyle($cell_key)->getFont()->getColor()->setARGB($value['style']['color']);
                    }
                }
            }
        }

        $collection->destruct();

        foreach ($sheets as $sheet) {
            $sheet['sheet']->setCellValue('A1', $sheet['title']);
            $sheet['sheet']->mergeCells("A1:" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($sheet['headers'])) . 1);
            $sheet['sheet']->getStyle("A1:" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($sheet['headers'])) . 1)->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('6610F2');
            $sheet['sheet']->getStyle("A1:" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($sheet['headers'])) . 1)->getFont()->getColor()->setARGB('FFFFFF');
            $sheet['sheet']->getStyle("A1:" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($sheet['headers'])) . 1)->getFont()->setBold(true);
            $sheet['sheet']->getStyle("A1:" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($sheet['headers'])) . 1)->getFont()->setSize(18);

            foreach ($sheet['headers'] as $i => $title) {
                $sheet['sheet']->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . 2, $title);
            }

            $sheet['sheet']->getStyle('A2:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($sheet['headers'])) . 2)->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('89B7E5');
            $sheet['sheet']->getStyle('A2:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($sheet['headers'])) . 2)->getFont()->getColor()->setARGB('FFFFFF');
        }

        $file_name = 'export/catalogItemCustomize/designData/' . OSC::makeUniqid() . '.' . date('d-m-Y') . '.xlsx';
        $file_path = OSC_Storage::preDirForSaveFile($file_name);

        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $writer->save($file_path);

        return OSC_Storage::tmpGetFileUrl($file_name);
    }

}
