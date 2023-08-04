<?php

class Controller_MasterSync_Index extends Abstract_Core_Controller_Api {

    public function actionReceive() {
        $sync_key = trim($this->_request->get('key'));

        //Disable sync product type and product type variant
        if (!$sync_key || in_array($sync_key, ['syncProductType', 'syncProductTypeVariant'])) {
            $this->_ajaxError('No sync key provided');
        }

        try {
            switch ($sync_key) {
                case 'syncSupplierLocation':
                    OSC::helper('catalog/masterSync')->syncCommonData($this->_request->get('data'), 'supplier_location', 'supplier_location_rel');
                    break;
                case 'syncTax':
                    OSC::helper('catalog/masterSync')->syncCommonData($this->_request->get('data'), 'tax', 'catalog_tax', 'record_id');
                    break;
                case 'syncProductType':
                    OSC::helper('catalog/masterSync')->syncCommonData($this->_request->get('data'), 'productType', 'product_type');
                    break;
                case 'syncProductTypeVariant':
                    OSC::helper('catalog/masterSync')->syncCommonData($this->_request->get('data'), 'productType_variant', 'product_type_variant');
                    break;
                case 'syncProductTypeVariantLocationPrice':
                    OSC::helper('catalog/masterSync')->syncCommonData($this->_request->get('data'), 'productType_variantLocationPrice', 'product_type_variant_location_price');
                    break;
                case 'sync/shippingStore':
                    OSC::helper('catalog/common')->setSettingShipping($this->_request->get('data'));
                    break;
                case 'syncStoreSetting':
                    OSC::helper('catalog/masterSync')->syncStoreSetting($this->_request->get('data'));
                    break;
                case 'syncMultiPaymentDefaultAccount':
                    OSC::helper('multiPaymentAccount/common')->changeDefaultAccount($this->_request->get('data'));
                    break;
                case 'syncProductConfig':
                    OSC::helper('masterSync/common')->syncProductConfig($this->_request->get('data'));
                    break;
                case 'syncProductTypeDescription':
                    OSC::helper('catalog/masterSync')->syncCommonData($this->_request->get('data'), 'syncProductTypeDescription', 'product_type_description');
                    break;
                case 'syncProductPack':
                    OSC::helper('catalog/masterSync')->syncCommonData($this->_request->get('data'), 'product_pack', 'catalog_product_pack');
                    break;
                case 'shipping_rate':
                    OSC::helper('catalog/masterSync')->syncCommonData($this->_request->get('data'), 'shipping_rate', 'shipping_rate');
                    break;
                case 'shipping_pack':
                    OSC::helper('catalog/masterSync')->syncCommonData($this->_request->get('data'), 'shipping_pack', 'shipping_pack_rate');
                    break;
                case 'shipping_delivery_time':
                    OSC::helper('catalog/masterSync')->syncCommonData($this->_request->get('data'), 'shipping_delivery_time', 'shipping_delivery_time');
                    break;
                case 'shipping_methods':
                    OSC::helper('catalog/masterSync')->syncCommonData($this->_request->get('data'), 'shipping_methods', 'shipping_methods');
                    break;
                case 'syncSupplierData':
                    OSC::helper('masterSync/common')->syncSupplierData($this->_request->get('data'));
                    break;
            }
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse();
    }

}
