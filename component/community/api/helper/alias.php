<?php

class Helper_Api_Alias extends Abstract_Core_Controller_Alias
{
    protected function _process(&$request_string, &$router, &$action)
    {
        if (preg_match('/^callback-apple-api$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'callbackAppleApi';
        }
    }
}

