<?php

class Helper_AutoAb_AbProduct extends OSC_Object {

    /**
     * @param int $config_id
     * @return array
     */
    public function getProductDistribution($config_id) {
        /* @var OSC_Database_Adapter $DB */

        try {
            $DB = OSC::core('database')->getAdapter();
            $DB->select('*', OSC::model('autoAb/abProduct_map')->getTableName(), "config_id = {$config_id}", '`acquisition` ASC, `id` ASC', 1, 'fetch_ab_map');

            $row = $DB->fetchArray('fetch_ab_map');
            $DB->free('fetch_ab_map');

            if (!$row) {
                throw new Exception('Product is not exist', 404);
            }

            return $row;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param $product_map_id
     * @param $acquisition
     * @return void
     * @throws Exception
     */
    public function incrementAcquisitionProduct($product_map_id, $acquisition) {
        /* @var OSC_Database_Adapter $DB */

        try {
            $DB = OSC::core('database')->getWriteAdapter();
            $DB->update(OSC::model('autoAb/abProduct_map')->getTableName(), ['acquisition' => $acquisition], "id = {$product_map_id}", 1, 'update_ab_map');
            $DB->free('update_ab_map');
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function getAutoAbProductKey() {

        return OSC_Controller::makeRequestChecksum('_auto_ab_product', OSC_SITE_KEY);
    }

    /**
     * @param Model_Frontend_Tracking $track_model
     * @return void
     * @throws Exception
     */
    public function trackingView($track_model, $req_url) {

        if (empty($req_url) || !preg_match('/product\/[A-Z0-9]{15}/', $req_url, $matchs)) {
            return;
        }

        try {
            $date_current = date('Ymd');
            $cookie_key_ab_product = $this->getAutoAbProductKey();
            $ab_product_data = OSC::cookieGet($cookie_key_ab_product) ? OSC::decode(OSC::cookieGet($cookie_key_ab_product), true) : [];

            if (empty($ab_product_data)) {
                return;
            }

            $hub_ukey = $ab_product_data['hub_ukey'] ?? '';

            try {
                $config = OSC::model('autoAb/abProduct_config')->loadByUKey($hub_ukey);
            } catch (Exception $ex) {
                throw new Exception('Hub config is not exist with sku: ' . $hub_ukey);
            }

            if (!$config->isBegin()) {
                return;
            }

            $product_sku = explode('/', $matchs[0])[1];
            try {
                $model_product = OSC::model('catalog/product')->loadByUKey($product_sku);
            } catch (Exception $ex) {
                throw new Exception('Product is not exist with sku: ' . $product_sku);
            }

            if($ab_product_data['product_id'] != $model_product->getId()) {
                return;
            }

            $report_data = [
                'unique_visitor' => 0,
                'page_view' => 0,
                'config_id' => $config->getId(),
                'product_id' => $model_product->getId(),
                'date' => $date_current
            ];

            $ab_product_report = OSC::model('autoAb/abProduct_report')->getCollection()
                ->addCondition('config_id', $config->getId())
                ->addCondition('product_id',$model_product->getId())
                ->addCondition('date', $date_current)
                ->load()->first();

            if ($ab_product_report) {
                $report_data['unique_visitor'] = $ab_product_report->data['unique_visitor'];
                $report_data['page_view'] = $ab_product_report->data['page_view'];
            }

            try {

                OSC::model('autoAb/abProduct_viewTracking')->setData([
                    'config_id' => $config->getId(),
                    'track_ukey' => $track_model->getUkey(),
                    'product_id' => $model_product->getId(),
                    'date' => $date_current
                ])->save();

                // increment 1 unique and page view because agent have view product AB Test new
                $report_data['unique_visitor'] += 1;
                $report_data['page_view'] += 1;

            } catch (Exception $ex) {
                if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                    // if had viewed product AB Test in date then increment page_view not unique_visitor
                    $report_data['page_view'] += 1;
                } else {
                    throw new Exception($ex->getMessage());
                }
            }

            $this->recordReport($ab_product_report ?? OSC::model('autoAb/abProduct_report'), $report_data);
        } catch (Exception $ex) {
            OSC::logFile('TrackingView: ' . $req_url . '___Error: ' . $ex->getMessage(), 'AbProductTrackingViewLog');
        }
    }

    /**
     * @param Model_Catalog_Order $order
     * @return void
     * @throws Exception
     */
    public function trackingOrder($order) {
        if ($order->getId() < 1) {
            return;
        }

        try {

            $cookie_key_ab_product = $this->getAutoAbProductKey();
            $ab_product_data = OSC::cookieGet($cookie_key_ab_product) ? OSC::decode(OSC::cookieGet($cookie_key_ab_product), true) : [];

            if (empty($ab_product_data)) {
                return;
            }

            $hub_ukey = $ab_product_data['hub_ukey'] ?? '';

            try {
                $config = OSC::model('autoAb/abProduct_config')->loadByUKey($hub_ukey);
            } catch (Exception $ex) {
                throw new Exception('Hub config is not exist with sku: ' . $hub_ukey);
            }

            if (!$config->isBegin()) {
                return;
            }

            $ab_product_id = $ab_product_data['product_id'] ?? 0;

            $items_shipping_info = $order->getItemsShippingInfo();

            $ab_product_report = OSC::model('autoAb/abProduct_report')->getCollection()
                ->addCondition('config_id', $config->getId())
                ->addCondition('product_id',$ab_product_id)
                ->addCondition('date', date('Ymd', $order->data['added_timestamp']))
                ->load()->first();

            $report_data = [
                'config_id' => $config->getId(),
                'product_id' => $ab_product_id,
                'date' => date('Ymd', $order->data['added_timestamp']),
                'quantity' => $ab_product_report ? $ab_product_report->data['quantity'] : 0,
                'revenue' => $ab_product_report ? $ab_product_report->data['revenue'] : 0,
                'total_order' => $ab_product_report ? $ab_product_report->data['total_order'] : 0
            ];

            $increment_total_order = false;

            /* @var $line_item Model_Catalog_Order_Item */
            foreach ($order->getLineItems() as $line_item) {
                if ($line_item->data['product_id'] == $ab_product_id) {
                    $product_variant = $line_item->getVariant();

                    $quantity = $line_item->data['other_quantity'] > 1 ?
                        $line_item->data['quantity'] * $line_item->data['other_quantity'] :
                        $line_item->data['quantity'];

                    $revenue = $line_item->getRevenue($product_variant->getPriceForCustomer()['price'], $items_shipping_info);

                    try {

                        OSC::model('autoAb/abProduct_orderTracking')->setData([
                            'config_id' => $config->getId(),
                            'product_type_variant_id' => $product_variant->data['product_type_variant_id'] ?? 0,
                            'product_variant_id' => $product_variant->getId(),
                            'product_id' => $ab_product_id,
                            'order_item_id' => $line_item->getId(),
                            'order_id' => $order->getId(),
                            'quantity' => $quantity,
                            'revenue' => $revenue
                        ])->save();

                    } catch (Exception $ex) {
                        throw new Exception('OrderID: ' . $order->getId() . '___LineItemID: ' . $line_item->getId() . '___Error: ' . $ex->getMessage());
                    }

                    $report_data['quantity'] += $quantity;
                    $report_data['revenue'] += $revenue;

                    $increment_total_order = true;

                }
            }

            if ($increment_total_order) {
                $report_data['total_order'] += 1;
                $this->recordReport($ab_product_report ?? OSC::model('autoAb/abProduct_report'), $report_data);
            }

        } catch (Exception $ex) {

            OSC::logFile('___Error Order: ' . $order->getId() . ' __Message: ' . $ex->getMessage(), 'AbProductTrackingOrderLog');
        }

    }

    /**
     * @param Model_AutoAb_AbProduct_Report $model
     * @param $data
     * @return void
     * @throws OSC_Database_Model_Exception
     */
    public function recordReport($model, $data) {
        $model->setData($data)->save();
    }

}
