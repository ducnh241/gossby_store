<?php

class Controller_Account_Api_Product extends Abstract_Core_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function actionGetWishList() {
        try {
            $product_ids = $this->_request->get('product_ids');

            if (!is_array($product_ids)) {
                $product_ids = OSC::decode($product_ids, true);
            }

            if (count($product_ids) < 1) {
                throw new Exception('Not have product ids');
            }

            $collection_products = OSC::model('catalog/product')->getCollection()->load($product_ids);

            $products = OSC::helper('catalog/product')->formatProductApi($collection_products);

            $this->_ajaxResponse($products);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }
}