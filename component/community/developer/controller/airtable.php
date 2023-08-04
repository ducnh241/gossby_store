<?php

class Controller_Developer_Airtable extends Abstract_Core_Controller {

    public function actionSyncOrderItem() {

        $order_item_id = $this->_request->get('order_item_id');

        if(!empty($order_item_id)) {
            $order_item = OSC::model('catalog/order_item')->load($order_item_id);

            if ($order_item->data['shop_id'] == OSC::getShop()->getId()) {

                $order_items = OSC::model('catalog/order_item')->getCollection();
                $order_items->addItem($order_item);
                OSC::helper('catalog/orderItem')->addToOrderAirtableBulkQueue($order_items, [$order_item->data['product_id']]);
            } else {
                throw new Exception('Shop is not sync to Airtable');
            }

            echo "DONE {$order_item->getId()}: " . time() . "<br>";
        } else {
            echo 'Empty order';
        }
    }

    public function actionAddRetryFlowQueue() {
        try {
            OSC::core('cron')->addQueue('d2/retrySyncFlowReply', ['running_time' => 600], ['ukey' => 'd2/retrySyncFlowReply', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60, 'running_time' => 600]);
            echo 'DONE: ' . time() . "<br>";
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

}
