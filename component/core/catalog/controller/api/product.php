<?php

class Controller_Catalog_Api_Product extends Abstract_Core_Controller_Api {

    public function actionRequestProductBestSelling() {
        try {
            /* @var $product Model_Catalog_Product */

            $limit = intval($this->_request->get('limit'));
            $customer_location = trim($this->_request->get('customer_location'));
            $product_items = OSC::model('catalog/product')->getCollection()->loadBestSelling($limit, $customer_location);
            $data_product_items = [];

            foreach ($product_items as $key => $value) {
                $data_product_items[$key]['product_id'] = $value->data['product_id'];
                $data_product_items[$key]['sku'] = $value->data['sku'];
                $data_product_items[$key]['title'] = $value->getProductTitle();
                $data_product_items[$key]['product_type'] = $value->data['product_type'];
                $data_product_items[$key]['img'] = $value->getFeaturedImageUrl();
                $data_product_items[$key]['url'] = $value->getDetailUrl();
            }

            $this->_ajaxResponse(['result' => $data_product_items]);

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }
}