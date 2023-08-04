<?php

class Helper_Order_Alias extends Abstract_Core_Controller_Alias
{
    protected function _process(&$request_string, &$router, &$action)
    {
        if (preg_match('/^keep-or-cancel$/', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'keepOrCancel';
        }
    }
}
