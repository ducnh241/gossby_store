<?php

class Cron_Marketing_UpdateOldOrderPoint extends OSC_Cron_Abstract
{

    public function process($params, $queue_added_timestamp)
    {
        $logs = [];
        $DB = OSC::core('database')->getAdapter();

        $start_time = strtotime('2023-05-01 00:00:00');
        $end_time = strtotime('2023-06-03 23:59:59');

        $shop_id = OSC::getShop()->getId();

        if ($shop_id < 1) {
            return;
        }

        $limit = 200; // set limit = 200 when live
        $last_record = isset($params['last_id']) ? $params['last_id'] : null;

        if(!$last_record) {
            $DB->select('order_id', 'marketing_point', "`convert_status` = '1'", '`modified_timestamp` DESC', 1, 'fetch_log');
            $row = $DB->fetchArray('fetch_log');
            $DB->free('fetch_log');

            if (!$row) {
                $last_record = 0;
            } else {
                $last_record = $row['order_id'];
            }
        }

        $n = 0;

        $order_collections = OSC::model('catalog/order')->getCollection()
            ->addCondition('master_record_id', $last_record, OSC_Database::OPERATOR_GREATER_THAN)
            ->addCondition('shop_id', $shop_id, OSC_Database::OPERATOR_EQUAL)
            ->addCondition('added_timestamp', $start_time, OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL)
            ->addCondition('added_timestamp', $end_time, OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL)
            ->sort('master_record_id', OSC_Database::ORDER_ASC)
            ->setLimit($limit)
            ->load();

        $counter = $order_collections->length();

        if ($counter === 0) { // Stop and delete cron
            return true;
        }

        $DB->begin();
        $locked_key = OSC::makeUniqid();
        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            foreach ($order_collections as $order) {
                $last_record = $order->getId();
                if (!empty($order->data['sref_id'])) {
                    foreach ($order->getLineItems() as $line_item) {
                        if ($line_item->isCrossSellMode()) {
                            continue;
                        }

                        $point_data = OSC::helper('marketing/common')->calculatePoint($line_item, true);

                        if (!empty($point_data['marketing_point']['sref']) || !empty($point_data['marketing_point']['vendor'])) {
                            // Search order_item in marketing_point table to update record, if none, insert new record
                            try {
                                $marketing_point_model = OSC::model('marketing/point')
                                    ->setCondition(['field' => 'order_line_item_id', 'value' => $line_item->getId(), 'operator' => OSC_Database::OPERATOR_EQUAL])
                                    ->load();
                                $marketing_point_model->setData([
                                    'point' => $point_data['marketing_point']['sref'] ?? 0,
                                    'vendor' => $line_item->data['vendor'],
                                    'vendor_point' => $point_data['marketing_point']['vendor'] ?? 0,
                                    'meta_data' => [
                                        'note' => 'Re-update point for old order',
                                        'day_after_product_created' => $point_data['day_after_product_created'] ?? 0,
                                        'quantity' => $point_data['quantity'],
                                        'quantity_of_pack' => $point_data['quantity_of_pack'],
                                        'point_config' => $point_data['point_config'],
                                        'point_setting' => $point_data['point_setting']
                                    ],
                                    'convert_status' => 1,
                                    'modified_timestamp' => time()
                                ])->save();
                            } catch (Exception $ex) {
                                if ($ex->getCode() == 404) {
                                    $marketing_point_model = OSC::model('marketing/point');
                                    $marketing_point_model->setData([
                                        'order_id' => $order->getId(),
                                        'order_line_item_id' => $line_item->getId(),
                                        'product_id' => $line_item->data['product_id'],
                                        'variant_id' => $line_item->data['variant_id'],
                                        'member_id' => $order->data['sref_id'],
                                        'point' => $point_data['marketing_point']['sref'] ?? 0,
                                        'vendor' => $line_item->data['vendor'],
                                        'vendor_point' => $point_data['marketing_point']['vendor'] ?? 0,
                                        'meta_data' => [
                                            'note' => 'Re-insert point for old order',
                                            'day_after_product_created' => $point_data['day_after_product_created'] ?? 0,
                                            'quantity' => $point_data['quantity'],
                                            'quantity_of_pack' => $point_data['quantity_of_pack'],
                                            'point_config' => $point_data['point_config'],
                                            'point_setting' => $point_data['point_setting']
                                        ],
                                        'convert_status' => 1,
                                        'added_timestamp' => $order->data['added_timestamp'],
                                        'modified_timestamp' => time()
                                    ])->save();
                                }
                            }
                            $n++;
                        }
                    }
                }
            }

            $DB->commit();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);
        } catch (Exception $ex) {

            $DB->rollback();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            //insert log error
            $logs = $ex->getMessage();
        }

        if (count($logs) > 0) {
            $this->_log($logs);
        }

        if ($n === 0 || $counter == $limit) { // All page don't have any record has marketing point
            OSC::core('cron')->addQueue('marketing/updateOldOrderPoint', ['last_id' => $last_record], [
                'requeue_limit' => -1,
                'estimate_time' => 60 * 10
            ]);
            return true;
        }
    }
}
