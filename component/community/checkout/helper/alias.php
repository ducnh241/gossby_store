<?php

class Helper_Checkout_Alias extends Abstract_Core_Controller_Alias {

    protected function _process(&$request_string, &$router, &$action) {
        if (!$request_string) {
            $router = 'index';
            $action = 'index';
        }
    }

}
