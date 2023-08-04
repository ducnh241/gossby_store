<?php

class Cron_Catalog_Product_BulkSetTag extends OSC_Cron_Abstract {
    public function process($params, $queue_added_timestamp) {
        $logs = [];

        $collection = OSC::model('catalog/product')->getCollection()->load($params['ids']);

        $method = $params['method'];

        foreach ($collection as $product) {
            try {
                if ($product->checkMasterLock()) {
                    throw new Exception('You do not have the right to perform this function');
                }

                $tags = $product->data['tags'];

                if (isset($tags) && count($tags) > 0){
                    $tags_lower = array_map('strtolower', $tags);
                }else{
                    $tags = [];
                    $tags_lower = [];
                }

                foreach ($params['list_tags'] as $tag){
                    if (in_array(strtolower($tag),$tags_lower)){
                        if ($method == 'add_tag'){
                            continue;
                        }elseif($method == 'remove_tag'){
                            unset($tags[array_search(strtolower($tag),$tags_lower)]);
                        }
                    }else{
                        if ($method == 'add_tag'){
                            $tags[] = $tag;
                        }elseif($method == 'remove_tag'){
                            continue;
                        }
                    }
                }

                $this->_log("Catalog :: ".$method == 'add_tag' ? 'Add' : 'Remove'." tags [" . implode(',', $params['list_tags']) . "] from product [{$product->getId()}] \"{$product->getProductTitle()}\" ");

                $product->setData('tags', $tags)->save();

            } catch (Exception $ex) {
                $logs[] = $ex->getMessage();
            }
        }

        if (count($logs) > 0) {
            $this->_log($logs);
        }
    }

}




