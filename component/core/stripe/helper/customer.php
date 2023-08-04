<?php

class Helper_Stripe_Customer {

    public function createStripeCustomer(array $request) {
        try {
            $response = \Stripe\Customer::create($request);

            return $response;
        } catch (\Exception $ex) {
            return null;
        }
    }

    public function retrieveStripeCustomer(string $id) {
        try {
            $response = \Stripe\Customer::retrieve($id);

            if (!$response || !is_object($response)) {
                throw new Exception('Not have response data');
            }

            return $response;
        } catch (\Exception $ex) {
            return null;
        }
    }

    public function createOrUpdateStripeCustomer(array $customer, array $address = [], array $shipping = []) {
        try {
            $request_data = [
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'name' => $customer['name'],
                'address' => $address,
                'shipping' => $shipping,
                'metadata' => [
                    'shop_id' => OSC_SITE_KEY,
                    'shop_name' => OSC::helper('core/setting')->get('theme/site_name'),
                    'customer_id' => $customer['customer_id'],
                    'email' => $customer['email']
                ],
            ];
            $isExist = false;
            $customer_id = null;
            if ($customer['stripe_id'] && is_array($customer['stripe_id'])) {
                foreach ($customer['stripe_id'] as $account => $id) {
                    if (substr($account, 2) === OSC::helper('stripe/payment')->getSecretKey()) {
                        $isExist = true;
                        $customer_id = $id;
                        break;
                    }
                }
            }
            
            if (!$isExist) {
                $response = $this->createStripeCustomer($request_data);
            } else {
                $check_customer = $this->retrieveStripeCustomer($customer_id);
                if ($check_customer && is_object($check_customer) && !isset($check_customer->deleted)) {
                   $response = \Stripe\Customer::update($customer_id, $request_data);
                } else {
                    $response = $this->createStripeCustomer($request_data);
                }
            }
            return $response;
        } catch (\Exception $ex) {
            return null;
        }
    }

    public function updateStripeCustomer(string $id, array $data) {
        try {
            $check_customer = $this->retrieveStripeCustomer($id);
            if ($check_customer && is_object($check_customer) && !isset($check_customer->deleted)) {
                $response = \Stripe\Customer::update($id, $data);
             } else {
                $response = null;
             }
             return $response;
        } catch (\Exception $ex) {
            return null;
        }
    }

    public function updateStripeCustomerByOrder(Model_Catalog_Order $order) {
        $customer = OSC::helper('stripe/common')->getCustomerInfoFromOrder($order);
        $address = OSC::helper('stripe/common')->getBillingAddressFromOrder($order);
        $shipping = OSC::helper('stripe/common')->getShippingAddressFromOrder($order);
        $stripe_customer = $this->createOrUpdateStripeCustomer($customer, $address, $shipping);
        return $stripe_customer;
    }

    public function deleteStripeCustomer(string $sk,string $id) {
        try {
            $stripe = new \Stripe\StripeClient($sk);
            $stripe->customers->delete($id);
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

}