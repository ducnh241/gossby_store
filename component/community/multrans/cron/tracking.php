<?php

class Cron_Multrans_Tracking extends OSC_Cron_Abstract {

    const CRON_SCHEDULER_FLAG = 0;
    
    protected function _processTracking($row) {
        $DB = OSC::core('database')->getWriteAdapter();

        try {
            $DB->update(Helper_Multrans_Common::TBL_PRE_FULFILLMENT_NAME, ['queue_flag' => 0], 'record_id=' . $row['record_id'], 1, 'update_pre_tracking');

            if ($DB->getNumAffected('update_pre_tracking') != 1) {
                throw new Exception('Not save queue flag');
            }

            $access_token = OSC::helper('multrans/common')->getAccessToken();
            if (!$access_token || !is_array($access_token) || $access_token['result'] == 0 ) { 
                throw new Exception($access_token['message']);
            }

            $order = OSC::model('catalog/order')->load($row['order_id']);

            if ($order->checkMasterLock()){
                throw new Exception('You do not have the right to perform this function');
            }

            $weight = 0;
            $length = 0;
            $data = [];
            $items = [];
            $order_data = [];

            $lines = OSC::decode($row['line_items']);
            foreach ($lines as $key => $line) {
                $line_item = $order->getLineItemByItemId($line['order_line_id']);
                // check don hang la cốc, them prefix MUG
                $is_mug = 0;
                foreach ($line_item->data['options'] as $key => $value) {
                    if (preg_match('/^.*?(\d+)\s*oz.*?$/i', strtolower($value['value']), $matches)) {
                        $is_mug = 1;
                        break;
                    }
                }
                $itemDescription = $line_item->getVariantTitle();
                $sku = $line_item->data['sku'] != "" ? $line_item->data['sku'] : $itemDescription;
                $items[] = ['itemDescription' => $is_mug === 1 ? 'MUG'.$itemDescription : $itemDescription,
                            'packagedQuantity' => $line['quantity'],
                            'skuNumber' => $is_mug === 1 ? 'MUG'.$sku : $sku
                            ];

                $options = OSC::helper('multrans/common')->getInfoOptionsByOrderItem($line_item);

                if (!$options) {
                    throw new Exception('Not support options order');
                }
                $weight += $options['weight']*$line['quantity'];
                if ($length < $options['length']) {
                    $length = $options['length'];
                }

                $order_data[] = ['code' => $order->data['code'], 'order_line_id' =>  $line['order_line_id'], 'quantity' => $line['quantity']];
            }
            if ($row['quantity'] < 1) {
                throw new Exception('Quantity error');
            }
            $dimension = OSC::helper('multrans/common')->calculateDimensionValue($row['quantity']);

            $data['name'] = $order->data['shipping_full_name'];
            $data['address1'] = $order->data['shipping_address1'];
            $data['address2'] = $order->data['shipping_address2'];
            $data['city'] = $order->data['shipping_city'];
            $data['state'] = $order->data['shipping_province_code'];
            $data['postalCode'] = $order->data['shipping_zip'];
            $data['orderNumber'] = $order->data['code'];
            $data['country'] = $order->data['shipping_country_code'];
            $data['phone'] = $order->data['shipping_phone'];

            $data['weight'] = $weight;

            $data['height'] = $dimension[0] * ($is_mug == 0 ? 35 : 148);
            $data['width'] = $dimension[1] * ($is_mug == 0 ? 35 : 145);
            $data['length'] = $length;
            $data['is_max'] = $row['shipping_method'];
            $data['items'] = $items;

            $labelDetails = OSC::helper('multrans/common')->pushDataToGetLabel($data, $access_token['data']['access_token']);

            if (!$labelDetails || !is_array($labelDetails) || $labelDetails['result'] == 0 ) { 
                throw new Exception($labelDetails['message']);
            }

            return array('labelDetails' => $labelDetails, 'order_data' => $order_data);

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function process($data, $queue_added_timestamp) {
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 100;
        $counter = 0;

        while ($counter < $limit) {
            $DB->select('*', Helper_Multrans_Common::TBL_PRE_FULFILLMENT_NAME, 'queue_flag = 1', '`requeue_counter` ASC, `added_timestamp` ASC, record_id ASC', 1, 'pre_tracking');

            $row = $DB->fetchArray('pre_tracking');

            $DB->free('pre_tracking');

            if (!$row) {
                break;
            }

            try {
                $trackings = $this->_processTracking($row);

                $labelDetails = $trackings['labelDetails'];

                $DB->update(Helper_Multrans_Common::TBL_PRE_FULFILLMENT_NAME, ['error_message' => '', 'tracking_number' => $labelDetails['data']['partnerTrackingNumber'], 'tracking_url' => $labelDetails['data']['url'], 'shipping_carrier' => Helper_Multrans_Common::MULTRANS_SHIPPING_CARRIER], 'record_id=' . $row['record_id'], 1, 'update_pre_tracking');
            } catch (Exception $ex) {
                $DB->update(Helper_Multrans_Common::TBL_PRE_FULFILLMENT_NAME, ['error_message' => $ex->getMessage(), 'requeue_counter' => $row['requeue_counter'] + 1, 'queue_flag' => $row['requeue_counter'] <= 4 ? 1 : 0], 'record_id=' . $row['record_id'], 1, 'update_pre_tracking');
                continue;
            }

            // sync master database
            $sync_data['tracking_number'] = $labelDetails['data']['partnerTrackingNumber'];
            $sync_data['tracking_url'] = Helper_Multrans_Common::MULTRANS_TRACKING_URL.'/?trackingnumber='.$labelDetails['data']['partnerTrackingNumber'];
            $sync_data['label_url'] = $labelDetails['data']['url'];
            $sync_data['shipping_carrier'] = Helper_Multrans_Common::MULTRANS_SHIPPING_CARRIER;
            $sync_data['order_data'] = OSC::encode($trackings['order_data']);

            OSC::core('observer')->dispatchEvent('supplier/syncTracking', $sync_data);

            $counter ++;

            if ($counter == $limit) {
                OSC::core('cron')->addQueue('multrans/tracking', null, ['requeue_limit' => -1]);
                return;
            }

        }

        OSC::helper('multrans/common')->exportExcelAndSendMail();
    }

}
