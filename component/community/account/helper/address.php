<?php

class Helper_Account_Address extends OSC_Object
{

    protected $_list_address = null;

    public function getList($customer_id = 0)
    {
        if ($this->_list_address === null) {
            try {
                $customer_id = $customer_id ? $customer_id : OSC::helper('account/customer')->getCustomerId();
                $default_address_id = OSC::helper('account/customer')->getDefaultAddressId();
                $data_request = ["customer_id" => $customer_id];
                $results = OSC::helper('account/common')->callApi(Helper_Account_Common::CUSTOMER_ADDRESS_GET_LIST_URL, $data_request);
                $address_data = [];
                if (isset($results['rows']) && is_array($results['rows'])) {
                    foreach ($results['rows'] as $key => $result) {
                        $address_data['address_' . $result['id']] = $result;
                        $address_data['address_' . $result['id']]['address_full'] = $this->getAddressFull($result);
                        $address_data['address_' . $result['id']]['is_default'] = $result['id'] == $default_address_id ? 1 : 0;
                    }
                    $this->_list_address = $address_data;
                }
            } catch (Exception $ex) {
                $this->_list_address = [];
            }
        }
        return $this->_list_address;
    }

    public function getAddressDetail($address_id, $customer_id = 0)
    {
        try {
            if ($customer_id === 0) {
                $list_address = $this->getList();
                return isset($list_address['address_' . $address_id]) ? $list_address['address_' . $address_id] : [];
            } else {
                $data_request = ["id" => $address_id, "customer_id" => $customer_id];
                return OSC::helper('account/common')->callApi(Helper_Account_Common::CUSTOMER_ADDRESS_GET_URL, $data_request);
            }
        } catch (Exception $ex) {
            return [];
        }
    }

    public function getDefaultAddress()
    {
        $list_address = $this->getList();
        $default_address_key = 'address_' . OSC::helper('account/customer')->getDefaultAddressId();
        if (isset($list_address[$default_address_key])) {
            return $list_address[$default_address_key];
        }
        return end($list_address);
    }

    public function getAddressFull($address)
    {
        $address_full = [];
        $address_full[] = $address['address1'];
        $address_full[] = $address['address2'];
        $address_full[] = $address['zip'];
        $address_full[] = $address['province'];
        $address_full[] = $address['country'];

        $address_full = array_filter($address_full, function ($value) {
            if ($value) {
                return $value;
            }
        });

        return implode(', ', $address_full);
    }

    public function create($data_request)
    {
        try {
            return OSC::helper('account/common')->callApi(Helper_Account_Common::CREATE_CUSTOMER_ADDRESS_URL, $data_request, ['app_token' => 1]);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

    }

    public function findOrCreate($data_request)
    {
        try {
            return OSC::helper('account/common')->callApi(Helper_Account_Common::FIND_OR_CREATE_CUSTOMER_ADDRESS_URL, $data_request, ['app_token' => 1]);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

    }

    public function update($data_request)
    {
        try {
            return OSC::helper('account/common')->callApi(Helper_Account_Common::UPDATE_CUSTOMER_ADDRESS_URL, $data_request, ['app_token' => 1]);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getDataAddressByOrder(Model_Catalog_Order $order, $type = 'billing', $ignore_default = 0)
    {
        try {
            if (!in_array($type, ['shipping', 'billing'])) {
                throw new Exception('not map type address');
            }

            $data_address = [
                "is_default" => $type == 'billing' && $ignore_default == 0 ? 1 : 0,
                "full_name" => $order->data[$type . '_full_name'],
                "phone" => $order->data[$type . '_phone'],
                "address1" => $order->data[$type . '_address1'],
                "address2" => $order->data[$type . '_address2'] ?? '',
                "city" => $order->data[$type . '_city'],
                "province" => $order->data[$type . '_province'] ?? '',
                "province_code" => $order->data[$type . '_province_code'] ?? '',
                "zip" => $order->data[$type . '_zip'],
                "country" => $order->data[$type . '_country'],
                "country_code" => $order->data[$type . '_country_code']
            ];

            return $data_address;
        } catch (Exception $ex) {
        }
    }
}
