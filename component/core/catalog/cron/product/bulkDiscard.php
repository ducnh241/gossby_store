<?php

class Cron_Catalog_Product_BulkDiscard extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $logs = [];

        $collection = OSC::model('catalog/product')->getCollection()->load($params['ids']);

        foreach ($collection as $product) {
            try {
                if ($product->checkMasterLock()) {
                    throw new Exception('You do not have the right to perform this function');
                } else {
                    $product->setData('discarded', 1)->save();
                }
            } catch (Exception $ex) {
                $logs[] = $ex->getMessage();
            }
        }

        if (count($logs) > 0) {
            $this->_log($logs);
        }
    }

}
