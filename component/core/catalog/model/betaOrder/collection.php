<?php

class Model_Catalog_BetaOrder_Collection extends Abstract_Core_Model_Collection {
    public function getOrders()
    {
        $order_ids = [];
        foreach ($this as $model) {
            $order_ids[] = $model->data['order_master_record_id'];
        }
        return OSC::model('catalog/order')->getCollection()->sort('added_timestamp', OSC_Database::ORDER_DESC)->load($order_ids);
    }
}
