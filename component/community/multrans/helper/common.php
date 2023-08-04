<?php

class Helper_Multrans_Common extends OSC_Object
{
    const MULTRANS_USER_NAME = 'multransdls';
    const MULTRANS_SHIPPING_CARRIER = 'Standard Shipping';
    const MULTRANS_PASSWORD = 'GJDCTDLSRRK';
    const MULTRANS_HOST = 'https://api.multranslogistics.com';
    const MULTRANS_TRACKING_URL = 'https://webtrack.dhlglobalmail.com';
    const TBL_PRE_FULFILLMENT_NAME = 'catalog_order_pre_fulfillment';
    const KEY_CACHE_ACCESS_TOKEN_20H = 'multrans_token_20h';

    public static function options()
    {
        return array('24x36' => array('length' => 610, 'width' => 35, 'height' => 35, 'weight' => 200),
                    '36x24' => array('length' => 610, 'width' => 35, 'height' => 35, 'weight' => 200),
                    '16x24' => array('length' => 410, 'width' => 35, 'height' => 35, 'weight' => 120),
                    '24x16' => array('length' => 410, 'width' => 35, 'height' => 35, 'weight' => 120),
                    '11x17' => array('length' => 310, 'width' => 35, 'height' => 35, 'weight' => 80),
                    '17x11' => array('length' => 310, 'width' => 35, 'height' => 35, 'weight' => 80),
                    '11oz' => array('length' => 145, 'width' => 145, 'height' => 148, 'weight' => 354),
                    '15oz' => array('length' => 145, 'width' => 145, 'height' => 148, 'weight' => 518)
                        );
    }

    public function getAccessToken()
    {
        $url = static::MULTRANS_HOST. '/v1/auth/access_token?username='.static::MULTRANS_USER_NAME.'&password='.static::MULTRANS_PASSWORD;

        try {
            $response = OSC::core('network')->curl($url);
            if (!$response['content']) {
                return null;
            }
            $result = 0;
            $message = 'success';
            $data = null;
            if (isset($response['content']) && isset($response['content']['meta'])) {
                if ($response['content']['meta']['code'] == 200) {
                    $result = 1;
                    $data = $response['content']['data'];
                } else {
                    $message = $response['content']['error']['error_message'];
                }
            } else {
                $document = OSC::makeDomFromContent($response['content']);

                $xpath = new DOMXPath($document);
                $message =  $xpath->query('.//*[contains(@class,"exception_message")]', $parent_node)->item(0)->textContent;
            }

        } catch (Exception $ex) {
            $message =  $ex->getMessage();
        }

        return array('result'=> $result, 'data' => $data, 'message' => $message);
    }

    public function pushDataToGetLabel($data, $access_token)
    {
        $url = static::MULTRANS_HOST. '/v1/label?client_id='.static::MULTRANS_USER_NAME.'&access_token='.$access_token;

        if (!is_array($data)) {
            return null;
        }
        $data = OSC::encode($data);
        $file_path = OSC_Storage::preDirForSaveFile('multrans/data');
        OSC::writeToFile(dirname($file_path) . '/post.txt', $data."\n", array('append' => true));

        try {
            $response = OSC::core('network')->curl($url, array(
                                                        'request_method' => 'POST', 
                                                        'headers' => array( 'Content-Type' => "application/json"),
                                                        'data' => $data
                                                    )
                                                );
            $result = 0;
            $message = 'success';
            $data = null;
            if (isset($response['content']) && isset($response['content']['meta'])) {
                if ($response['content']['meta']['code'] == 200) {
                    $result = 1;
                    $data = $response['content']['data']['labelDetails'];               
                } else {
                    $message = isset($response['content']['error']['errorMessage']) ? $response['content']['error']['errorMessage'] : $response['content']['error']['error_message'];
                }
            } else {
                $document = OSC::makeDomFromContent($response['content']);

                $xpath = new DOMXPath($document);
                $message =  $xpath->query('.//*[contains(@class,"exception_message")]', $parent_node)->item(0)->textContent;

            }
            $message = $message != '' ? $message : 'Error html';

        } catch (Exception $ex) {
            $message =  $ex->getMessage();
        }

        OSC::writeToFile(dirname($file_path) . '/post.txt', $data."\n", array('append' => true));

        return array('result'=> $result, 'data' => $data, 'message' => $message);
    }

    public function getInfoOptionsByOrderItem(Model_Catalog_Order_Item $line_item)
    {
        $size = '';
        foreach ($line_item->data['options'] as $key => $value) {
            if(preg_match('/^(.*[\s+\_\-])?(\d+)[\s+\_\-]*(\'\'|\"|inch|in|cm|mm|m)?[\s+\_\-]*x[\s+\_\-]*(\d+)[\s+\_\-]*(\'\'|\"|inch|in|cm|mm|m)?([\s+\_\-]+.*)?$/', strtolower($value['value']), $matches)) {
                $size = $matches[2] . 'x' . $matches[4];
				break;
            } elseif (preg_match('/^.*?(\d+)\s*oz.*?$/i', strtolower($value['value']), $matches)) {
                $size = $matches[1].'oz';
				break;
            }
        }
       
        $options = static::options();

        if (array_key_exists($size, $options )) {
            return $options[$size];
        }
        return array();
    }

    public function calculateDimensionValue($quantity){ 
        $sqrt = sqrt($quantity);

        $dimension = [floor($sqrt), floor($sqrt)]; 

        $modulo = fmod($sqrt, $dimension[0]);

        if($modulo <= 0) { 
            return $dimension; 
        } else if($modulo > 0.5) { 
            $dimension[0] ++;  $dimension[1] ++; 
        } else { 
            $dimension[0] ++; 
        } 
        return $dimension;  
    }

    protected function _exportColumnData($column_export, $row, $order, $line , $counter) {      

        $data_ex = [];
        foreach ($column_export as $value) {
            switch ($value) {
                case "order_id":
                    array_push($data_ex, $order->getId());
                    break;
                case "order_line_id":
                    array_push($data_ex, $line['order_line_id']);
                    break;
                case "code":
                    array_push($data_ex, $order->getCode());
                    break;
                case "quantity":
                    array_push($data_ex, $line['quantity']);
                    break;
                case "tracking_number":
                    array_push($data_ex, $row['tracking_number']);
                    break;
                case "shipping_carrier":
                    array_push($data_ex, static::MULTRANS_SHIPPING_CARRIER);
                    break;
                case "tracking_url":
                    array_push($data_ex, $row['tracking_url']);
                    break;
                case "result":
                    array_push($data_ex, $row['queue_flag'] != 0 ? 'error' : 'success');
                    break;
                case "error_message":
                    array_push($data_ex, $row['error_message'] ? $row['error_message'] : '');
                    break;
            }
        }

        return $data_ex;
    }

    protected function getAttachments($email_receiver, $rows)
    {
        try {
            $DB = OSC::core('database')->getWriteAdapter();

            $DB->begin();

            $locked_key = OSC::makeUniqid();

            OSC_Database_Model::lockPreLoadedModel($locked_key);

            $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers = [
                "order_id" =>"Order ID" ,
                "order_line_id" =>"Order Line ID" ,
                "code" => "Code",
                "quantity" => "Quantity",
                "tracking_number" => "Tracking Number",
                "shipping_carrier" => "Shipping Carrier",
                "tracking_url" => "Tracking Url",
                "result" => "Result",
                "error_message" => "Error Message"
            ];
            $sheet_row_header = 0;
            foreach ($headers as $title) {

                $sheet->setCellValueExplicit(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($sheet_row_header + 1) . 1, $title, PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                $sheet_row_header ++;
            }

            $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . 1)->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('89B7E5');
            $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . 1)->getFont()->getColor()->setARGB('FFFFFF');

            /* @var $order Model_Catalog_Order */
            /* @var $line_item Model_Catalog_Order_Item */
            /* @var $variant Model_Catalog_Product_Variant */

            $sheet_row_index = 2;
            $tracking_urls = [];
            $attachments = [];

            foreach ($rows as $row) {
                if ($row['tracking_url'] || $row['tracking_url'] != null || $row['tracking_url'] != '') {
                    $tracking_urls[] = $row['tracking_url'];
                }

                $DB->delete(Helper_Multrans_Common::TBL_PRE_FULFILLMENT_NAME, 'record_id=' . $row['record_id'], 1, 'delete_queue');

                $counter = 0;
                $order = OSC::model('catalog/order')->load($row['order_id']);

                foreach (OSC::decode($row['line_items']) as  $line) {
                    $row_data = $this->_exportColumnData(array_keys($headers), $row, $order, $line, $counter);

                    foreach ($row_data as $j => $value) {
                        $sheet->setCellValueExplicit(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($j + 1) . $sheet_row_index, $value, PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }

                    $counter ++;

                    $sheet_row_index ++;
                }                    

            }

            $file_name = 'export/catalog/getLabelTracking/sendemail/' . OSC::makeUniqid() . '.' . date('d-m-Y') . '.xlsx';

            $file_path = OSC_Storage::preDirForSaveFile($file_name);

            $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);           


            $writer->save($file_path);
        
            $html = "Tracking Code - get Label From Multrans". "<br />".OSC_Storage::tmpGetFileUrl($file_name);

            OSC::helper('postOffice/email')->create([
                'priority' => 1000,
                'subject' => 'Send email automatic from system dls',
                'receiver_email' => $email_receiver,
                'receiver_name' => 'DLS Mailer',
                'html_content' => $html,
                'attachments' => ''
            ]);
           
            $DB->commit();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

        } catch (Exception $ex) {
            $DB->rollback();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            throw new Exception($ex->getMessage());            
        }

    }

    public function exportExcelAndSendMail() {

        try {
            set_time_limit(0);
            ini_set('memory_limit', '1024M');
            $DB = OSC::core('database')->getWriteAdapter();

            $DB->select('*', Helper_Multrans_Common::TBL_PRE_FULFILLMENT_NAME, null , '`added_timestamp` ASC, record_id ASC', null, 'tracking_finished');

            $rows = $DB->fetchArrayAll('tracking_finished');

            if (count($rows) < 1) {
                return;
            }
            $datas = array();

            foreach ($rows as $key => $row) {
                $email_receiver = $row['email_receiver'];
                $datas[$email_receiver][] = $row;
            }

            foreach ($datas as $key => $data) {
                try {
                    $this->getAttachments($key, $data);
                    
                } catch (Exception $ex) {
                    $path = OSC_Storage::preDirForSaveFile('multrans/data');
                    OSC::writeToFile(dirname($path) . '/post.txt', $ex->getMessage()."\n", array('append' => true));
                    continue;
                }
            }
        } catch (Exception $ex) {
            $path = OSC_Storage::preDirForSaveFile('multrans/data');
            OSC::writeToFile(dirname($path) . '/post.txt', $ex->getMessage()."\n", array('append' => true));           
            return ;
        }
    }
   
}
