<?php

class Helper_Catalog_Alias extends Abstract_Core_Controller_Alias {
    protected function _process(&$request_string, &$router, &$action) {
        if (!$request_string || preg_match('/^products(\/([^\/].*))?$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'index';

            if (isset($matched)) {
                $request_string = $matched[2];
            }
        } else if (preg_match('/^product\/(\d+)(\/[^\/]+(\/(.+))?)?$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'detail';

            $this->_request->set('id', $matched[1], null, true);

            $request_string = $matched[4];
        } else if (preg_match('/^product\/([A-Z0-9]{15})(\/[^\/]+(\/(.+))?)?$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'detail';

            $this->_request->set('ukey', $matched[1], null, true);

            $request_string = $matched[4];
        } else if (preg_match('/^collection\/(\d+)\/product\/(\d+)(\/[^\/]+(\/(.+))?)?$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'detail';

            $this->_request->set('collection_id', $matched[1], null, true);
            $this->_request->set('id', $matched[2], null, true);

            $request_string = $matched[5];
        } else if (preg_match('/^collection\/(\d+)\/product\/([A-Z0-9]{15})(\/[^\/]+(\/(.+))?)?$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'detail';

            $this->_request->set('collection_id', $matched[1], null, true);
            $this->_request->set('ukey', $matched[2], null, true);

            $request_string = $matched[5];
        } else if (preg_match('/^collection\/(\d+)(\/[^\/]+(\/(.+))?)?$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'index';

            $this->_request->set('id', $matched[1], null, true);

            $request_string = $matched[4];
        } else if (preg_match('/^lookupOrder(\/[^\/]+(\/(.+))?)?$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'lookupOrder';

            $request_string = $matched[3];
        } else if (preg_match('/^collections(\/[^\/]+(\/(.+))?)?$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'index';

            $request_string = $matched[5];
        } else if (preg_match('/^reviews$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'review';
        } else if (preg_match('/^reviews\/integrate-facebook$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'reviewIntegrateFacebook';
        } else if (preg_match('/^(review)\/([0-9]+)$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'reviewDetail';
            $this->_request->set('id', $matched[2], null, true);
        } else if (preg_match('/^confirm-late-production?$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'lateProduction';
        }
    }
}