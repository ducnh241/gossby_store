<?php

class Helper_Account_Common extends OSC_Object
{
    // customer
    const CUSTOMER_GET_URL = 'api/v1/customer/get';
    const CREATE_CUSTOMER_URL = 'api/v1/customer/create';
    const UPDATE_CUSTOMER_URL = 'api/v1/customer/update';
    // address
    const CREATE_CUSTOMER_ADDRESS_URL = 'api/v1/customer/address/create';
    const FIND_OR_CREATE_CUSTOMER_ADDRESS_URL = 'api/v1/customer/address/findOrCreate';
    const UPDATE_CUSTOMER_ADDRESS_URL = 'api/v1/customer/address/update';
    const CUSTOMER_ADDRESS_GET_URL = 'api/v1/customer/address/get';
    const CUSTOMER_ADDRESS_GET_LIST_URL = 'api/v1/customer/address/list';
    // payment
    const PAYMENT_TYPE_STRIPE = 1;
    const CREATE_CUSTOMER_PAYMENT_URL = 'api/v1/customer/payment/create';
    const LIST_CUSTOMER_PAYMENT_URL = 'api/v1/customer/payment/list';
    
    //social
    const CUSTOMER_SOCIAL_HANDLE_FACEBOOK_DELETION = 'api/v1/customer/social_account/facebook-deletion';
    const CUSTOMER_SOCIAL_GET_FACEBOOK_DELETION = 'api/v1/customer/social_account/facebook-deletion';

    const EMAIL_ACTIVE_ACCOUNT = 5;
    const EMAIL_CREATED_ACCOUNT = 0;
    const EMAIL_RESET_PASSWORD = 1;
    const EMAIL_CHANGE_EMAIL = 2;
    const EMAIL_CHANGED_EMAIL = 3;
    const EMAIL_CHANGED_PASSWORD = 4;

    const CACHE_PREFIX_API_UPDATE_ACCOUNT = 'account_crm_order_';

    const LIST_ERROR_CODE_CRM = [1, 400, 401, 403, 404, 500];


    protected $_token = '';

    public function __construct()
    {
        $token = $this->getToken();
        $this->_token = isset($token['access']) ? $token['access'] : '';
    }

    protected $_token_app = null;

    public function callApi($request_path, $request_data, $options = [])
    {
        try {
            $header = [
                'Content-Type' => 'application/json'
            ];

            if (isset($options['app_token']) && $options['app_token'] === 1) {
                $header['Request-Type'] = 'Service';
                $header['Osc-Service'] = CRM_KEY;
                $header['OSC-Request-Signature'] = OSC_Controller::makeRequestChecksum(OSC::encode($request_data), CRM_SECRET);                
            } else {
                $header['Request-Type'] = 'Customer';
                $header['Authorization'] = 'Bearer ' . $this->_token;
            }

            $_options = [
                'timeout' => 60,
                'headers' => $header,
                'json' => $request_data,
            ];

            if (isset($options['request_method'])) $_options['request_method'] = $options['request_method'];

            $response = OSC::core('network')->curl(OSC::getServiceUrlCrm() . '/' . $request_path, $_options);

            $response_data = $response['content'];

            if (!is_array($response_data)) {
                $response_data = OSC::decode($response_data, true);
            }

            if (!is_array($response_data) || !isset($response_data['errorCode']) || $response_data['errorCode'] != 0) {
                if (isset($options['only_show_message']) && $options['only_show_message'] === 1 && in_array($response_data['errorCode'], static::LIST_ERROR_CODE_CRM)) {
                    throw new Exception(substr($response_data['message'], 0, 1000), intval($response_data['errorCode']));
                }
                throw new Exception(substr('Response data is incorrect: ' . print_r($response_data, 1), 0, 1000), intval($response['response_code']));
            }

            return $response_data['data'];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public function getToken()
    {
        $token = OSC::cookieGet('TOKEN');
        $token = OSC::decode($token, true);
        if (!is_array($token)) {
            $token = [];
        }
        return $token;
    }
}
