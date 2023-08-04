<?php
class Cron_Catalog_Product_UpdateBetaOrder extends OSC_Cron_Abstract
{
    public function process($data, $queue_added_timestamp)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            /** @var $product Model_Catalog_Product */
            $product_id = $data['product_id'];

            try {
                $product = OSC::model('catalog/product')->load($product_id);
                $meta_data = $product->data['meta_data'];

                if (!array_key_exists('is_disable_preview', $meta_data)) {
                    return;
                }
            } catch (Exception $ex) {
                return;
            }

            $order_line_items = OSC::model('catalog/order_item')
                ->getCollection()
                ->addField('order_id')
                ->addCondition('product_id', $product_id)
                ->addCondition('shop_id', OSC::getShop()->getId())
                ->load();

            $order_ids = array_column($order_line_items->toArray(), 'order_id');

            $orders = OSC::model('catalog/order')->getCollection()->load($order_ids);

            foreach ($orders as $order) {
                /** @var $order Model_Catalog_Order */
                $lineItems = $order->getLineItems();
                $is_disable_preview = -1;
                $product_ids = [];
                foreach ($lineItems as $line_item) {
                    $product = $line_item->getProduct();
                    if ($product !== null) {
                        $meta_data = $product->data['meta_data'];
                        if ($is_disable_preview < 1 && is_array($meta_data) && array_key_exists('is_disable_preview', $meta_data) && $meta_data['is_disable_preview'] == 1) {
                            $is_disable_preview = 1;
                        } elseif ($is_disable_preview < 0 && is_array($meta_data) && array_key_exists('is_disable_preview', $meta_data) && $meta_data['is_disable_preview'] == 0) {
                            $is_disable_preview = 0;
                        }
                        $product_ids[] = $product->getId();
                    }
                }

                if ($is_disable_preview != -1) {
                    try {
                        $betaOrder = OSC::model('catalog/betaOrder')->loadByUKey($order->data['code']);
                        $betaOrder->setData([
                            'is_disable_preview' => $is_disable_preview
                        ])->save();
                    } catch (Exception $ex) {
                        OSC::model('catalog/betaOrder')->setData([
                            'order_master_record_id' => $order->getId(),
                            'shop_id' => $order->data['shop_id'],
                            'order_code' => $order->data['code'],
                            'product_ids' => array_unique($product_ids),
                            'is_disable_preview' => $is_disable_preview,
                            'added_timestamp' => $order->data['added_timestamp'],
                        ])->save();
                    }
                }
            }
            return;
        } catch (Exception $ex) {
        }
    }
}
