<?php

class Helper_Account_Alias extends Abstract_Core_Controller_Alias
{
    protected function _process(&$request_string, &$router, &$action)
    {
        if (preg_match('/^setting$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'setting';
        } elseif (preg_match('/^wishlist$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'wishlist';
        } elseif (preg_match('/^purchase$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'purchase';
        } elseif (preg_match('/^profile$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'profile';
        } elseif (preg_match('/^new-address$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'newAddress';
        } elseif (preg_match('/^address$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'addressDetail';
        } elseif (preg_match('/^address\/([0-9]+)$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'addressDetail';
        } elseif (preg_match('/^created-account$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'createdAccount';
        } elseif (preg_match('/^change-email$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'changeEmail';
        } elseif (preg_match('/^change-password$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'changePassWord';
        } elseif (preg_match('/^reset-password$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'resetPassword';
        } elseif (preg_match('/^active-account$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'activeAccount';
        } elseif (preg_match('/^facebook-deletion$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'facebookDeletion';
        } elseif (preg_match('/^callback-apple$/i', $request_string, $matched)) {
            $router = 'frontend';
            $action = 'callbackApple';
        }
    }
}
