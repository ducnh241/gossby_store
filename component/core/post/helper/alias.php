<?php

class Helper_Post_Alias extends Abstract_Core_Controller_Alias
{
    protected function _process(&$request_string, &$router, &$action)
    {
        if (preg_match('/^collection\/(\d+)(\/[^\/]+(\/(.+))?)?$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'index';

            $this->_request->set('id', $matched[1], null, true);

            $request_string = $matched[4];
        } else if (preg_match('/^(\d+)(\/[^\/]+(\/(.+))?)?$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'detail';

            $this->_request->set('id', $matched[1], null, true);

            $request_string = $matched[4];
        }
    }
}

