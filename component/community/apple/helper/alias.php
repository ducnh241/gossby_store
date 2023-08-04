<?php

class Helper_Apple_Alias extends Abstract_Core_Controller_Alias
{
    protected function _process(&$request_string, &$router, &$action)
    {
        if (preg_match('/^login$/', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'login';
        }
    }
}
