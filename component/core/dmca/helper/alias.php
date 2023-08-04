<?php

class Helper_Dmca_Alias extends Abstract_Core_Controller_Alias {

    protected function _process(&$request_string, &$router, &$action) {
     if (preg_match('/^report\/([a-zA-Z0-9]+)(\/[^\/]+(\/(.+))?)?$/i', $request_string, $matched)) {
         $router = 'frontend';
         $action = 'viewReport';

         $this->_request->set('key', $matched[1], null, true);

         $request_string = $matched[4];
     }
    }

}

