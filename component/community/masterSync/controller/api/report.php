<?php

class Controller_MasterSync_Api_Report extends Abstract_MasterSync_Controller_Api {

    public function actionFetchDashboardData() {
        try {
            $this->_ajaxResponse(OSC::helper('report/process')->fetchDashboardData($this->_request->get('range')));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionFetchProductListData() {
        try {
            $this->_ajaxResponse(OSC::helper('report/process')->fetchProductListData($this->_request->get('range'), $this->_request->get('page')));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionFetchProductDetailData() {
        try {
            $this->_ajaxResponse(OSC::helper('report/process')->fetchProductDetailData($this->_request->get('id'), $this->_request->get('range')));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

}
