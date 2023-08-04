<?php

class Helper_Account_Payment extends OSC_Object
{

    public function create($data_request) {
        try {
            return OSC::helper('account/common')->callApi(Helper_Account_Common::CREATE_CUSTOMER_PAYMENT_URL, $data_request,['app_token' => 1]);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

    }

    public function list($data_request) {
        try {
            return OSC::helper('account/common')->callApi(Helper_Account_Common::LIST_CUSTOMER_PAYMENT_URL, $data_request, ['app_token' => 1]);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
