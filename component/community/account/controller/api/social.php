<?php

class Controller_Account_Api_Social extends Abstract_Core_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function actionHandleFacebookDeletion()
    {
        $signed_request = $this->_request->get('signed_request');
        if (!isset($signed_request)) {
            $this->_ajaxError('Bad request', 400);
        }
        try {
            $shop = OSC::getShop();
            if ($shop->getId() < 1) {
                throw new Exception('Not have shop', 404);
            }
            $result = OSC::helper('account/common')->callApi(
                Helper_Account_Common::CUSTOMER_SOCIAL_HANDLE_FACEBOOK_DELETION,
                [
                    'signed_request' => $signed_request,
                    'shop_id' => $shop->getId()
                ],
                [
                    'app_token' => 1,
                    'only_show_message' => 1
                ]
            );
            header('Content-Type: application/json');
            echo OSC::encode([
                'url' => 'https://' . $shop->data['shop_domain'] . '/account/facebook-deletion?code=' . $result['code'],
                'confirmation_code' => $result['code']
            ]);
            die;
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetFacebookDeletion()
    {
        try {
            $confirmation_code = $this->_request->get('confirmation_code');
            if (!isset($confirmation_code)) {
                $this->_ajaxError('Bad request', 400);
            }
            $result = OSC::helper('account/common')->callApi(
                Helper_Account_Common::CUSTOMER_SOCIAL_HANDLE_FACEBOOK_DELETION . '/' . $confirmation_code,
                [],
                [
                    'app_token' => 1,
                    'only_show_message' => 1,
                    'request_method' => 'get'
                ]
            );
            return $this->_ajaxResponse($result);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), $ex->getCode());
        }
    }
}
