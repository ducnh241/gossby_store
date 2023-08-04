<?php

class Observer_Catalog_Upsale {
    public static function upsaleCreated(Model_Catalog_Order_Item $order_item) {
        //Add to marketing point analytic
        try {
            $order = $order_item->getOrder();
            if (!empty($order->data['sref_id'])) {
                $point_data = OSC::helper('marketing/common')->calculatePoint($order_item, true);

                if (!empty($point_data['marketing_point']['sref']) || !empty($point_data['marketing_point']['vendor'])) {
                    $marketing_point_model = OSC::model('marketing/point');
                    $marketing_point_model->setData([
                        'order_id' => $order->getId(),
                        'order_line_item_id' => $order_item->getId(),
                        'product_id' => $order_item->data['product_id'],
                        'variant_id' => $order_item->data['variant_id'],
                        'member_id' => $order->data['sref_id'],
                        'point' => $point_data['marketing_point']['sref'] ?? 0,
                        'vendor' => $order_item->data['vendor'],
                        'vendor_point' => $point_data['marketing_point']['vendor'] ?? 0,
                        'meta_data' => [
                            'day_after_product_created' => $point_data['day_after_product_created'] ?? 0,
                            'quantity' => $point_data['quantity'],
                            'quantity_of_pack' => $point_data['quantity_of_pack'],
                            'point_config' => $point_data['point_config'],
                            'point_setting' => $point_data['point_setting']
                        ]
                    ])->save();
                }
            }
        } catch (Exception $ex) { }

        //Render design_url
        try {
            Observer_Catalog_Campaign::addRenderDesignQueue($order_item);
        } catch (Exception $ex) { }
    }
}