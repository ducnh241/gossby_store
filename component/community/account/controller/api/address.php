<?php

class Controller_Account_Api_Address extends Abstract_Core_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function actionUpdate()
    {
        try {
            $data['customer_id'] = OSC::helper('account/customer')->getCustomerId();
            $data['is_default'] = intval($this->_request->get('is_default'));
            $data['address']['full_name'] = trim($this->_request->get('full_name'));
            $data['address']['phone'] = trim($this->_request->get('phone'));
            $data['address']['address1'] = trim($this->_request->get('address1'));
            $data['address']['address2'] = trim($this->_request->get('address2'));
            $data['address']['city'] = trim($this->_request->get('city'));
            $data['address']['province'] = trim($this->_request->get('province'));
            $data['address']['country_code'] = trim($this->_request->get('country_code'));
            $data['address']['province_code'] = OSC::helper('core/country')->getProvinceCode($data['address']['country_code'], $data['address']['province']);
            $data['address']['country'] = OSC::helper('core/country')->getCountryTitle($data['address']['country_code']);
            $data['address']['zip'] = trim($this->_request->get('zip'));

            foreach ($data['address'] as $key => $address) {
                $data['address'][$key] = trim(OSC::core('string')->removeEmoji(OSC::core('string')->removeInvalidCharacter($address)));
            }

            //validate
            if (!$data['customer_id']) {
                throw new Exception('Customer not found');
            }

            $shop = OSC::getShop();
            $shop_id = intval($shop->data['shop_id']);
            if (!$shop_id) {
                throw new Exception('Shop not found');
            }

            $required_input = [
                [
                    'field' => 'full_name',
                    'title' => 'full name',
                    'message' => 'Please enter your name',
                ],
                [
                    'field' => 'phone',
                    'title' => 'phone',
                    'message' => 'Please enter your phone',
                ],
                [
                    'field' => 'address1',
                    'title' => 'address',
                    'message' => 'Please enter your address',
                ],
                [
                    'field' => 'city',
                    'title' => 'city',
                    'message' => 'Please enter your city',
                ],
                [
                    'field' => 'country',
                    'title' => 'country',
                    'message' => 'Please enter your country',
                ],
                [
                    'field' => 'zip',
                    'title' => 'Zip',
                    'message' => 'Please enter your zip code',
                ]
            ];

            foreach ($required_input as $input) {
                if (!$data['address'][$input['field']]) {
                    throw new Exception($input['message']);
                }
            }

            $data['shop_id'] = $shop_id;
            $response = OSC::helper('account/common')->callApi(Helper_Account_Common::CREATE_CUSTOMER_ADDRESS_URL, $data, ['only_show_message' => 1]);
            $customer_address = OSC::helper('account/address')->getList();

            $this->_ajaxResponse($customer_address);

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

}