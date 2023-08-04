<?php

class Controller_Addon_React_Service extends Abstract_Frontend_ReactApiController
{
    function actionGetAddonServices()
    {
        $product_sku = $this->_request->get('product_sku');

        try {
            if (!$product_sku) {
                throw new Exception('Data invalid');
            }

            $product = OSC::model('catalog/product')->loadByUKey($product_sku);
            $product_type_ids = OSC::helper('catalog/campaign')->getPreloadProductTypeIds($product);
            $product_type_variant_ids = array_column($product->getVariants()->toArray(), 'product_type_variant_id');
            $addon_services = OSC::helper('addon/service')->getAddonServices($product, $product_type_ids, $product_type_variant_ids);

            $this->sendSuccess($addon_services);

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    function actionTracking()
    {
        $product_id = intval($this->_request->get('product_id'));
        $product_type_id = intval($this->_request->get('product_type_id'));
        $product_type_variant_id = intval($this->_request->get('product_type_variant_id'));

        try {

            if (!$product_id) {
                throw new Exception('Data invalid');
            }

            OSC::helper('addon/report')->updateReportAddon('product_detail', $product_id, $product_type_id, $product_type_variant_id);

            $this->sendSuccess(['OK']);

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }
}
