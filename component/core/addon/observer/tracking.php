<?php

class Observer_Addon_Tracking
{
    private $_increment_order = true;
    private $_increment_order_total_price = true;

    public function trackingView($params)
    {
        $request = $params['request'] ?? '';
        $track_model = $params['track_model'] ?? null;

        if (!$track_model instanceof Model_Frontend_Tracking || empty($request)) {
            return;
        }

        if (preg_match('/\/cart$/', parse_url($request, PHP_URL_PATH), $matches)) {
            OSC::helper('addon/report')->updateReportAddon();
        }
    }

    public function trackingOrder(Model_Catalog_Order $order)
    {
        if ($order->getId() < 1) {
            return;
        }

        try {

            $cookie_key_ab_test_addon = OSC::helper('addon/service')->getABTestAddonKey();
            $ab_test_addon = OSC::cookieGet($cookie_key_ab_test_addon) ? OSC::decode(OSC::cookieGet($cookie_key_ab_test_addon)) : [];

            if (empty($ab_test_addon)) {
                return;
            }

            $addon_ids = array_column($ab_test_addon, 'addon_id');

            $addon_services = OSC::model('addon/service')->getCollection()
                ->addCondition('id', $addon_ids, OSC_Database::OPERATOR_IN)
                ->load();

            if ($addon_services->length() < 1) {
                return;
            }

            /* @var $line_item Model_Catalog_Order_Item */

            $order_date = date('Ymd', $order->data['added_timestamp']);
            foreach ($order->getLineItems() as $line_item) {
                $product = $line_item->getProduct();
                $product_type_variant = $line_item->getVariant()->getProductTypeVariant();
                $product_type_id = 0;
                $product_type_variant_id = 0;
                if ($product_type_variant instanceof Model_Catalog_ProductType_Variant) {
                    $product_type_id = $product_type_variant->data['product_type_id'];
                    $product_type_variant_id = $product_type_variant->data['id'];
                }
                $product_type_ids = [$product_type_id];
                $product_type_variant_ids = [$product_type_variant_id];
                $addon_services = OSC::helper('addon/service')->getAddonServices($product, $product_type_ids, $product_type_variant_ids);

                if (empty($addon_services['data_addon_service'])) {
                    // Product doesn't have addon service, so no tracking
                    continue;
                }

                $quantity = $line_item->data['other_quantity'] > 1 ?
                    $line_item->data['quantity'] * $line_item->data['other_quantity'] : //for pack
                    $line_item->data['quantity'];

                foreach ($addon_services['data_addon_service'] as $addon_service) {

                    //No ab test no tracking
                    if (!$addon_service['is_running_ab_test']) {
                        continue;
                    }

                    $addon_version_id = $ab_test_addon[$addon_service['id']]['addon_version_id'] ?? 0;
                    $addon_version = OSC_Database_Model::getPreLoadedModel('addon/version', $addon_version_id);
                    if (!$addon_version instanceof Model_Addon_Version || $addon_version->getId() < 1) {
                        continue;
                    }

                    $apply_for_product_type_variants = $addon_service['apply_for_product_type_variants'] ?? null;
                    if (!OSC::helper('addon/report')->verifyAddonBelongToProduct($apply_for_product_type_variants, $product_type_id, $product_type_variant_id)) {
                        continue;
                    }

                    //Track all
                    try {
                        $product_variant = $line_item->getVariant();
                        $revenue = 0;
                        if ($this->_increment_order_total_price) {
                            $revenue = $order->data['total_price'];
                            $this->_increment_order_total_price = false;
                        }

                        OSC::model('addon/report_order')->setData([
                            'addon_id' => $addon_service['id'],
                            'addon_version_id' => $addon_service['version_id'],
                            'product_id' => $line_item->data['product_id'],
                            'product_type_variant_id' => $product_variant->data['product_type_variant_id'] ?? 0,
                            'product_variant_id' => $product_variant->getId(),
                            'order_item_id' => $line_item->getId(),
                            'order_id' => $order->getId(),
                            'sale' => $quantity,
                            'revenue' => $revenue,
                            'date' => $order_date
                        ])->save();

                    } catch (Exception $ex) {
                        throw new Exception('OrderID: ' . $order->getId() . '___LineItemID: ' . $line_item->getId() . '___Error: ' . $ex->getMessage());
                    }
                }

                //Track if order has addon
                try {
                    $addon_service_of_order_item = $line_item->data['custom_price_data']['addon_services'] ?? [];
                    foreach ($addon_service_of_order_item as $addon_id => $addon_service_of_item) {
                        foreach ($addon_service_of_item as $addon_service_item) {
                            $addon_version_id = $ab_test_addon[$addon_id]['addon_version_id'] ?? 0;
                            $addon_version = OSC_Database_Model::getPreLoadedModel('addon/version', $addon_version_id);
                            if (!$addon_version instanceof Model_Addon_Version || $addon_version->getId() < 1) {
                                continue;
                            }
                            if (intval($addon_service_item['version_id']) !== $addon_version->getId()) {
                                continue;
                            }

                            /* @var $addon_service_report Model_Addon_Report */
                            $addon_service_report = OSC::model('addon/report')->getCollection()
                                ->addCondition('addon_id', $addon_id)
                                ->addCondition('addon_version_id', $addon_version->getId())
                                ->addCondition('date', $order_date)
                                ->load()
                                ->first();

                            //Total order, Total sale, Total quantity, Revenue, AOV, CR
                            $report_data = [
                                'addon_id' => $addon_id,
                                'addon_version_id' => $addon_version->getId(),
                                'date' => $order_date,
                                'total_order' => $addon_service_report ? $addon_service_report->data['total_order'] : 0,
                                'total_sale' => $addon_service_report ? $addon_service_report->data['total_sale'] : 0,
                                'revenue' => $addon_service_report ? $addon_service_report->data['revenue'] : 0,
                            ];
                            if ($this->_increment_order) {
                                $report_data['total_order'] += 1;
                                $this->_increment_order = false;
                            }
                            $report_data['total_sale'] += $quantity;
                            $report_data['revenue'] += $addon_service_item['price'] * $quantity ?? 0;

                            $this->saveData($addon_service_report ?? OSC::model('addon/report'), $report_data);
                        }
                    }
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }
            }

        } catch (Exception $ex) {
            OSC::logFile('Log report addon: Order' . $order->getId() . ' __Message: ' . $ex->getMessage());
        }

    }

    public function saveData($model, $data)
    {
        $model->setData($data)->save();
    }
}
