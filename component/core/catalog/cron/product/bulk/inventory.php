<?php

class Cron_Catalog_Product_Bulk_Inventory extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 150;
        $counter = 0;

        while ($counter < $limit) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'inventory'", '`added_timestamp` ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter ++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            $DB->begin();

            $locked_key = OSC::makeUniqid();

            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                $variant = OSC::model('catalog/product_variant')->load($model->data['queue_data'][0]);

                $product = $variant->getProduct();

                if ($product->checkMasterLock()) {
                    throw new Exception('You do not have the right to perform this function');
                } else {
                    if ($variant->data['track_quantity'] == 1) {
                        $variant->incrementQuantity($model->data['queue_data'][1]);
                    }
                    $model->delete();
                }

                $DB->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);
            } catch (Exception $ex) {
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                $model->setData(['error' => $ex->getMessage()])->save();
            }
        }

        if ($counter == $limit) {
            OSC::core('cron')->addQueue('catalog/product_bulk_delete', null, ['requeue_limit' => -1, 'estimate_time' => 60*5]);
        }
    }

}
