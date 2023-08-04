<?php

class Cron_Catalog_Order_renderDesignAfterOrderCreated extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $DB_MASTER = OSC::core('database')->getAdapter('db_master');
        $DB_STORE = OSC::core('database')->getAdapter();

        $shop_id = OSC::getShop()->getId();

        if ($shop_id < 1) {
            return;
        }

        $limit = 30;
        $counter = 0;

        while ($counter < $limit) {
            $model = OSC::model('catalog/order_bulkQueue');

            $DB_MASTER->select('*', $model->getTableName(), "shop_id = " . $shop_id . " AND queue_flag = 1 AND action = 'render_design_after_order_created'", 'added_timestamp ASC', 1, 'fetch_queue');

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
                $order = OSC::model('catalog/order')->load($model->data['queue_data']['order_id']);

                /* @var $line_item Model_Catalog_Order_Item */
                $design_ids = [];

                foreach ($order->getLineItems() as $line_item) {
                    if ($line_item->isCampaignMode()) {
                        $design_ids = array_merge($design_ids, Observer_Catalog_Campaign::addRenderDesignQueue($line_item));

                        /* TODO Handle order item not supply
                        throw new Exception("Product '{$line_item->getVariantTitle()}' is out of stock");
                        */
                    }

                    if ($line_item->isSemitestMode()) {
                        $response_queue_render = OSC::helper('catalog/campaign')->addQueueRenderDesignBeta($line_item);

                        $_design_ids = $response_queue_render['design_ids'];

                        if (!is_array($_design_ids)) {
                            continue;
                        }

                        $design_ids = array_merge($design_ids, $_design_ids);
                    }
                }

                if (!empty($design_ids)) {
                    $design_ids = array_unique($design_ids);

                    OSC::helper('personalizedDesign/common')->lockDesignByIds($design_ids);
                }

                OSC::helper('catalog/orderItem')->renderDesignSvgBeta($order);

                $model->delete();

                $DB_STORE->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);
            } catch (Exception $ex) {
                $DB_STORE->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                $error = 'Error::' . $ex->getMessage();

                $model->setData(['queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['error'], 'error' => $error])->save();

                OSC::helper('core/telegram')->send(OSC::$base_url . '. Error cron render_design_after_order_created on : ' . $error);
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}
