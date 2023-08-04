<?php

class Cron_Catalog_Order_AfterOrderCreated extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $DB_MASTER = OSC::core('database')->getAdapter('db_master');
        $DB_STORE = OSC::core('database')->getAdapter();

        $shop_id = OSC::getShop()->getId();

        if ($shop_id < 1) {
            return;
        }
        $limit = 100;
        $counter = 0;

        while ($counter < $limit) {
            $model = OSC::model('catalog/order_bulkQueue');

	        $DB_MASTER->select('*', $model->getTableName(), "shop_id = ".$shop_id." AND queue_flag = 1 AND action = 'store_after_order_created'", 'added_timestamp ASC', 1, 'fetch_queue');

            $row = $DB_MASTER->fetchArray('fetch_queue');

	        $DB_MASTER->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

	        $DB_STORE->begin();

	        $locked_key = OSC::makeUniqid();

	        OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                $order = OSC::model('catalog/order')->load($model->data['order_master_record_id']);

                // update subscriber
                try {
                    $subscriber = OSC::helper('postOffice/subscriber')->saveEmailSubscriber($order->data['email'], $order->getFullName(), 'order', []);
                    $newsletter = intval($model->data['queue_data']['subscribe_newsletter']);
                    $subscriber->setData(['newsletter' => $newsletter])->save();

                    if ($newsletter) {
                        // subscribe email to klaviyo and send subscriber email
                        OSC::helper('klaviyo/common')->subscribe($subscriber);
                    }
                } catch (Exception $ex) { }

                $line_items = $order->getLineItems();
                $product_solds = [];
                $is_disable_preview = -1;
                $product_ids = [];

                foreach ($line_items as $line_item) {
                    if ($line_item->isCrossSellMode() || !$line_item->getProduct()) {
                        continue;
                    }

                    $variant = $line_item->getVariant();
                    // update product variant
                    if ($variant instanceof Model_Catalog_Product_Variant && $variant->data['track_quantity'] == 1) {
                        $variant->incrementQuantity(-$line_item->data['quantity']);
                    }

                    if (!isset($product_solds[$line_item->data['product_id']])) {
                        $product_solds[$line_item->data['product_id']] = ['model' => $line_item->getProduct(), 'solds' => 0];
                    }

                    $product_solds[$line_item->data['product_id']]['solds'] += $line_item->data['quantity'];
                }

                //update solds product
                foreach ($product_solds as $product_id => $product) {
                    $product['model']->increment('solds', $product['solds']);
                    //Sync to ES
                    try {
                        OSC::core('observer')->dispatchEvent('model_catalog_product_save', [
                            'product_id' => $product_id,
                            'columns' => 'solds'
                        ]);
                    } catch (Exception $exception) { }

                    if ($product['model'] instanceof Model_Catalog_Product && $product['model']->getId() > 0) {
                        $product_ids[] = $product['model']->getId();
                        $meta_data = $product['model']->data['meta_data'];
                        if ($is_disable_preview < 1 && is_array($meta_data) && array_key_exists('is_disable_preview', $meta_data) && $meta_data['is_disable_preview'] == 1) {
                            $is_disable_preview = 1;
                        } elseif ($is_disable_preview < 0 && is_array($meta_data) && array_key_exists('is_disable_preview', $meta_data) && $meta_data['is_disable_preview'] == 0) {
                            $is_disable_preview = 0;
                        }
                    }
                }
                // handle product semitest
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

                // send mail review
                OSC::helper('catalog/product_review')->requestReviewAfterOrderCreated($order);

                // personalized design sync after place order
                OSC::core('observer')->dispatchEvent('catalog/afterPlaceOrder', $order);

	            $model->setData([
                    'action' => 'crm_after_order_created',
                    'queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['queue']
                ])->save();

	            $DB_STORE->commit();

	            OSC_Database_Model::unlockPreLoadedModel($locked_key);

                // update crm data after place order
                OSC::core('cron')->addQueue('account/updateCrmAfterPlaceOrder', null, ['requeue_limit' => -1, 'ukey' => 'account/updateCrmAfterPlaceOrder']);
            } catch (Exception $ex) {
	            $DB_STORE->rollback();

	            OSC_Database_Model::unlockPreLoadedModel($locked_key);

	            $model->setData(['queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['error'], 'error' => $ex->getMessage()])->save();

	            OSC::helper('core/telegram')->send(OSC::$base_url.'. Error cron store_after_order_created on : '.$ex->getMessage());
            }
        }

	    if ($counter >= $limit) {
		    return false;
	    }
    }
}
