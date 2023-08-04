<?php

class Helper_Catalog_OrderItemMeta extends OSC_Object
{
    public function getOrderItemMetaByIds($order_item_meta_ids) {
        $shop_id = OSC::getShop()->getId();

        if ($shop_id < 1) {
            throw new Exception('Shop ID is empty');
        }

        $order_item_meta_master_ukeys = array_map(
            function ($order_item_id) use ($shop_id) {
                return "{$shop_id}:{$order_item_id}";
            },
            $order_item_meta_ids
        );

        if (!count($order_item_meta_master_ukeys)) {
            return [];
        }

        return OSC::model('catalog/order_item_meta')
            ->getCollection()
            ->addCondition('master_ukey', $order_item_meta_master_ukeys, OSC_Database::OPERATOR_IN)
            ->load();
    }
}
