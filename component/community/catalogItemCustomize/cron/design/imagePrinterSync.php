<?php

class Cron_CatalogItemCustomize_Design_ImagePrinterSync extends OSC_Cron_Abstract {

    const CRON_SCHEDULER_FLAG = 0;


    public function process($data, $queue_added_timestamp) {
        $DB = OSC::core('database')->getWriteAdapter();

        try {
            $store_info = OSC::getStoreInfo();
        } catch (Exception $ex) {
            return;
        }
        $limit = 100;
        $counter = 0;

        while ($counter < $limit) {
            $DB->select('*', 'customize_printersync_queue' , 'syncing_flag = 0', '`added_timestamp` ASC, queue_id ASC', 1, 'printer_queue');

            $row = $DB->fetchArray('printer_queue');

            $DB->begin();

            $locked_key = OSC::makeUniqid();

            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                $DB->delete('customize_printersync_queue', 'queue_id=' . $row['queue_id'], 1, 'delete_queue');

                $DB->select('*', 'catalog_item_customize_order_map', 'order_line_id = '.$row['order_line_id'], null, 1, 'order_map_item');

                $order_map = $DB->fetchArray('order_map_item');

                if (!$order_map) {
                    throw new Exception('Not found order map with order line id = '.$row['order_line_id']);
                }

                try {
                    $design = OSC::model('catalogItemCustomize/design')->load($order_map['design_id']);
                    if (!$design) {
                        throw new Exception('Not exist model with design_id = ' . $order_map['design_id']);
                    }
                } catch (Exception $e) {
                    throw new Exception('Not exist model with design_id = ' . $order_map['design_id']);
                }

                $request_data = [
                    'design_key' => $design->getUkey(),
                    'url' => $row['url'],
                ];

                $response = OSC::core('network')->curl('https://customize' . OSC::getServiceDomainSuffix() . '.9prints.com/customizeSync/api/receive', [
                    'headers' => ['Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum(OSC::encode($request_data), $store_info['secret_key'])],
                    'json' => $request_data
                ]);

                if (!is_array($response['content']) || !isset($response['content']['result'])) {
                    throw new Exception('Response data is incorrect: ' . print_r($response['content'], 1));
                }

                if ($response['content']['result'] != 'OK') {
                    throw new Exception($response['content']['message']);
                }

                $DB->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);
            } catch (Exception $ex) {
                $DB->rollback();
                OSC_Database_Model::unlockPreLoadedModel($locked_key);
                $DB->update('customize_printersync_queue', ['syncing_flag' => 1 ,'error_message' => $ex->getMessage()], 'queue_id=' . $row['queue_id'], 1, 'update_queue');
                continue;
            }

            $counter ++;

            if ($counter >= $limit) {
                OSC::core('cron')->addQueue('catalogItemCustomize/design_imagePrinterSync', null, ['requeue_limit' => -1]);
                return;
            }

        }
    }

}
