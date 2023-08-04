<?php

class Cron_Catalog_Product_Bulk_Delete extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $DB = OSC::core('database');

        $limit = 15;
        $counter = 0;

        while ($counter < $limit) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'delete'", '`added_timestamp` ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter ++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            try {
                OSC::helper('catalog/product')->delete($model->data['queue_data']);

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage()])->save();
            }
        }

        if ($counter == $limit) {
            OSC::core('cron')->addQueue('catalog/product_bulk_delete', null, ['requeue_limit' => -1, 'estimate_time' => 60*30]);
        }
    }

}
