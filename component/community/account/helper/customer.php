<?php

class Helper_Account_Customer extends OSC_Object
{
    protected $_customer = null;

    public function __construct()
    {
        $this->setCustomer();
    }

    public function setCustomer()
    {
        if ($this->_customer === null) {
            $token = OSC::helper('account/common')->getToken();
            $data_request = [];
            if (isset($token['access'])) {
                $access_token = explode('.', $token['access']);
                if (isset($access_token[1])) {
                    $access_token = OSC::decode(base64_decode(strtr($access_token[1], '-_', '+/')), true);
                    $data_request = [
                        'customer_id' => isset($access_token['customer_info']['id']) ? intval($access_token['customer_info']['id']) : 0,
                        'shop_id' => isset($access_token['customer_info']['shop_id']) ? intval($access_token['customer_info']['shop_id']) : 0,
                        'email' => isset($access_token['customer_info']['email']) ? $access_token['customer_info']['email'] : '',
                    ];
                }
            }
            try {
                $this->_customer = OSC::helper('account/common')->callApi(Helper_Account_Common::CUSTOMER_GET_URL, $data_request, ['app_token' => 1]);
            } catch (Exception $ex) {}
        }
    }

    public function getCustomer()
    {
        return $this->_customer;
    }

    public function getCustomerId()
    {
        return isset($this->_customer['id']) && $this->_customer['id'] ? $this->_customer['id'] : 0;
    }

    public function getCustomerEmail()
    {
        return isset($this->_customer['email']) && $this->_customer['email'] ? $this->_customer['email'] : '';
    }

    public function getDefaultAddressId()
    {
        return isset($this->_customer['default_address']) && $this->_customer['default_address'] ? $this->_customer['default_address'] : 0;
    }

    public function create($data_request)
    {
        try {
            $request = array_merge(
                $data_request,
                [
                    'host' => OSC::getShop()->data['shop_domain'],
                    'from' => 1
                ]
            );
            return OSC::helper('account/common')->callApi(Helper_Account_Common::CREATE_CUSTOMER_URL, $request, ['app_token' => 1]);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function update($data_request)
    {
        try {
            return OSC::helper('account/common')->callApi(Helper_Account_Common::UPDATE_CUSTOMER_URL, $data_request, ['app_token' => 1]);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function get($data_request)
    {
        try {
            return OSC::helper('account/common')->callApi(Helper_Account_Common::CUSTOMER_GET_URL, $data_request, ['app_token' => 1]);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function createOrUpdateCrmAccount(Model_Catalog_Order $order, $options = [])
    {
        $customer_id = isset($options['order_customer_id']) ? $options['order_customer_id'] : 0;
        $billing_option_input = isset($options['billing_option_input']) ? $options['billing_option_input'] : 'same';
        $newsletter = isset($options['newsletter']) ? intval($options['newsletter']) : 0;
        $flag_add_new_address = isset($options['flag_add_new_address']) ? $options['flag_add_new_address'] : false;
        try {
            if ($customer_id < 1) {
                try {
                    // check crm account exist
                    $customer = $this->get([
                        'shop_id' => $order->data['shop_id'],
                        'email' => $order->data['email']
                    ]);
                } catch (Exception $ex) {
                    // create crm account
                    $data_account = $this->_preDataAccountCreate($order, $newsletter);
                    $customer = $this->create($data_account);
                }
                $customer_id = isset($customer['id']) && intval($customer['id']) > 0 ? intval($customer['id']) : intval($customer['customer_id']);
            }
            if ($flag_add_new_address) {
                try {
                    // create address account crm
                    $data_address = $this->_preDataAddress($order, $customer_id, $billing_option_input);
                    foreach ($data_address as $address) {
                        try {
                            OSC::helper('account/address')->create($address);
                        } catch (Exception $ex) {
                        }
                    }
                } catch (Exception $ex) {
                }
            }

            // update crm account
            try {
                $cache_value = OSC::core('cache')->get(Helper_Account_Common::CACHE_PREFIX_API_UPDATE_ACCOUNT . $order->getId());
                $cache_value = $cache_value && intval($cache_value) === 1 ? 1 : 0;
                if ($cache_value === 0) {
                    $this->update([
                        "order_created" => 1,
                        "customer_id" => $customer_id,
                        "orders" => intval($customer['orders']) + 1,
                        "spent" => $order->data['paid'] > 0 ? (intval($customer['spent']) + $order->data['paid']) : $customer['spent'],
                        "subscribe_newsletter" => intval($newsletter),
                        "last_order_express_shipping" => !in_array($order->getShippingMethodKey(), ['standard', 'standard_shipping']) ? $order->data['added_timestamp'] : 0
                    ]);
                    OSC::core('cache')->set(Helper_Account_Common::CACHE_PREFIX_API_UPDATE_ACCOUNT . $order->getId(), 1, 60 * 60 * 24 * 7);
                }
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            return $customer_id;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _preDataAddress(Model_Catalog_Order $order, $customer_id, $billing_option_input)
    {
        try {
            $address = [];

            $address[] = [
                'shop_id' => $order->data['shop_id'],
                'customer_id' => $customer_id,
                'address' => OSC::helper('account/address')->getDataAddressByOrder($order, 'shipping'),
            ];

            if ($billing_option_input != 'same') {
                $address[] = [
                    'shop_id' => $order->data['shop_id'],
                    'customer_id' => $customer_id,
                    'address' => OSC::helper('account/address')->getDataAddressByOrder($order, 'billing'),
                ];
            }

            return $address;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _preDataAccountCreate(Model_Catalog_Order $order, $newsletter)
    {
        try {
            $data_request = [
                "shop_id" => $order->data['shop_id'],
                "email" => $order->data['email'],
                "name" => $order->data['billing_full_name'],
                "subscribe_newsletter" => $newsletter,
                "phone" => $order->data['billing_phone'],
                "added_timestamp" => $order->data['added_timestamp'],
            ];

            return $data_request;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
