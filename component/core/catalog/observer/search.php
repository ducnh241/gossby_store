<?php

class Observer_Catalog_Search
{
    public function addSearch($params) {
        try {
            $product = $params['model'];

            if (isset($params['product_id']) && !empty($params['product_id'])) {
                $product = OSC::model('catalog/product')->load($params['product_id']);
            }

            OSC::helper('catalog/search_product')->addProduct($product);
        } catch (Exception $exception) {
            OSC::helper('core/common')->writeLog("addSearch exception id {$product->getId()}", $exception->getMessage());
        }
    }

    public function deleteSearch($params) {
        try {
            $product = $params['model'];

            OSC::helper('catalog/search_product')->deleteProduct($product);
        } catch (Exception $exception) {
            OSC::helper('core/common')->writeLog("deleteSearch exception id {$product->getId()}", $exception);
        }
    }
}