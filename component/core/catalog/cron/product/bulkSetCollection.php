<?php

class Cron_Catalog_Product_BulkSetCollection extends OSC_Cron_Abstract {
    public function process($params, $queue_added_timestamp) {
        $logs = [];

        $collection = OSC::model('catalog/product')->getCollection()->load($params['ids']);

        $method = $params['method'];

        foreach ($collection as $product) {
            try {
                if ($product->checkMasterLock()) {
                    throw new Exception('You do not have the right to perform this function');
                }

                $collection_ids = $product->data['collection_ids'];

                if (!isset($collection_ids) || count($collection_ids) < 1){
                    $collection_ids = [];
                }

                foreach ($params['list_collections'] as $collection_id){
                    if (in_array($collection_id,$collection_ids)){
                        if ($method == 'add_collection'){
                            continue;
                        }elseif($method == 'remove_collection'){
                            unset($collection_ids[array_search($collection_id,$collection_ids)]);
                        }
                    }else{
                        if ($method == 'add_collection'){
                            $collection_ids[] = $collection_id;
                        }elseif($method == 'remove_collection'){
                            continue;
                        }
                    }
                }

                $this->_log("Catalog :: ".$method == 'add_collection' ? 'Add' : 'Remove'." product [{$product->getId()}] \"{$product->getProductTitle()}\" from collection [" . implode(',', $params['list_collections']) . "]");

                $product->setData('collection_ids', $collection_ids)->save();

            } catch (Exception $ex) {
                $logs[] = $ex->getMessage();
            }
        }

        if (count($logs) > 0) {
            $this->_log($logs);
        }
    }

}




