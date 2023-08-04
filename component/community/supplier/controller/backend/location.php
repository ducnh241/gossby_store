<?php

class Controller_Supplier_Backend_Location extends Abstract_Backend_Controller {

    protected $_product_type_variants = null;

    public function actionRenderLocationSetting() {
        try {
            $supplier_id = intval($this->_request->get('supplier_id'));

            $this->_ajaxResponse([
                'html' => $this->getTemplate()->build('supplier/backend/location/item', [
                    'supplier_id' => $supplier_id,
                    'edit' => 1,
                    'variant_selector_params' => OSC::helper('product/productTypeVariant')->getVariantSelectorParams($supplier_id)
                ]),
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionRenderCutOffTimeGroup() {
        try {
            $product_types = $this->_request->get('product_types');
            $key = $this->_request->get('key');
            $count = $this->_request->get('count');

            $params = [
                'product_types' => $product_types,
                'key' => $key,
                'uniqid' => OSC::makeUniqid(),
                'count' => $count,
            ];

            $result = $this->getTemplate()->build('catalog/setting_type/shipping/cut_off_time_group', $params);

            $this->_ajaxResponse([
                'html' => $result,
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionRenderCutOffTimeItem() {
        try {
            $key = $this->_request->get('key');
            $uniqid = $this->_request->get('uniqid');

            $params = [
                'key' => $key,
                'uniqid' => $uniqid,
            ];

            $result = $this->getTemplate()->build('catalog/setting_type/shipping/cut_off_time_item', $params);

            $this->_ajaxResponse([
                'html' => $result,
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionPreviewLocation() {
        try {
            $location_data = $this->_request->get('location_data');

            $data_preview = OSC::helper('core/country')->dataPreview2(OSC::decode($location_data));

            $html =  $this->getTemplate()->build('country/group/preview', ['data_preview' => $data_preview]);

            $this->_ajaxResponse($html);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }
}
