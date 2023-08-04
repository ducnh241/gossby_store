<?php

class Controller_Developer_Es extends Abstract_Core_Controller {
    public function actionResync() {
        $resync = $this->_request->get('resync');
        OSC::helper('catalog/search_product')->resync($resync ? true : false);
    }

    public function actionTest() {
        $keyword = $this->_request->get('keyword');
        //$result = OSC::helper('catalog/search_product')->getSuggestion($keyword, 1000);
        $result = OSC::helper('catalog/search_product')->fetchSuggest($keyword);
        //$result = OSC::helper('catalog/search_product')->fetchAutocomplete($keyword);
        //$result = OSC::helper('catalog/search_product')->searchProduct($keyword, ['location_code' => ',VN_']);
        dd($result);
    }
}