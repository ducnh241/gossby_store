<?php

class Cron_Account_UpdateCrmAfterPlaceOrder extends OSC_Cron_Abstract
{
    const CRON_SCHEDULER_FLAG = 0;

    public function process($params, $queue_added_timestamp)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $shop_id = OSC::getShop()->getId();
        if ($shop_id < 1) {
            return;
        }

        $DB_MASTER = OSC::core('database')->getAdapter('db_master');
        $limit = 100;
        $counter = 0;

        while ($counter < $limit) {
            $model = OSC::model('catalog/order_bulkQueue');

            $DB_MASTER->select('*', $model->getTableName(), "shop_id = " . $shop_id . " AND queue_flag = 1 AND action = 'crm_after_order_created'", 'added_timestamp ASC', 1, 'fetch_queue');
            $row = $DB_MASTER->fetchArray('fetch_queue');
            $DB_MASTER->free('fetch_queue');
            if (!$row) {
                break;
            }

            $counter++;
            $model->bind($row);
            $model->setData(['queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['running']])->save();

            try {
                $order = OSC::model('catalog/order')->load($model->data['order_master_record_id']);
                $order_customer_id = $order->data['crm_customer_id'];
                $billing_option_input = $model->data['queue_data']['billing_option_input'];
                $flag_add_new_address = $model->data['queue_data']['flag_add_new_address'];
                $newsletter = intval($model->data['queue_data']['subscribe_newsletter']);

                $crm_customer_id = OSC::helper('account/customer')->createOrUpdateCrmAccount($order, [
                    'order_customer_id' => $order_customer_id,
                    'billing_option_input' => $billing_option_input,
                    'newsletter' => $newsletter,
                    'flag_add_new_address' => $flag_add_new_address
                ]);

                if (intval($crm_customer_id) < 1) {
                    throw new Exception('Not have create or update account crm');
                }
                if ($crm_customer_id != $order_customer_id) {
                    $order->setData(['crm_customer_id' => intval($crm_customer_id)])->save();
                }

                $model->delete();

                // update payment info
                if ($order->getPayment()->getKey() !== 'stripe') {
                    continue;
                }
                try {
                    $list_payments = OSC::helper('account/payment')->list(['customer_id' => $order->data['crm_customer_id']]);
                    $data_payments = [];
                    if ($list_payments['count'] > 0) {
                        foreach ($list_payments['rows'] as $row) {
                            $data_payments[$row['payment_account']] = $row['payment_id'];
                        }
                    }
                    // get data customer
                    $customer = [
                        'customer_id' => $order->data['crm_customer_id'],
                        'email' => $order->data['email'],
                        'phone' => $order->data['billing_phone'],
                        'name' => $order->data['billing_full_name'],
                        'stripe_id' => count($data_payments) > 0 ? $data_payments : null
                    ];

                    $address = OSC::helper('stripe/common')->getBillingAddressFromOrder($order);
                    $shipping = OSC::helper('stripe/common')->getShippingAddressFromOrder($order);
                    $response = OSC::helper('stripe/customer')->createOrUpdateStripeCustomer($customer, $address, $shipping);
                    $sk = $order->getPayment()->getAccount()['account_info']['secret_key'];
                    $account = 'a_' . md5($sk);

                    if (!isset($data_payments[$account]) && is_object($response)) {
                        $data_request = [
                            "shop_id" => $order->data['shop_id'],
                            "customer_id" => $order->data['crm_customer_id'],
                            "payment_type" => Helper_Account_Common::PAYMENT_TYPE_STRIPE,
                            "payment_account" => $account,
                            "payment_id" => $response->id
                        ];
                        OSC::helper('account/payment')->create($data_request);
                    }
                } catch (Exception $ex) {
                }
            } catch (Exception $ex) {
                $model->setData(['queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['error'], 'error' => 'Error::CRM - ' . $ex->getMessage()])->save();
                OSC::helper('core/telegram')->send(OSC::$base_url . '. Error cron crm_after_order_created on : ' . $ex->getMessage());
            }
        }

        if ($counter >= $limit) {
            return false;
        }

        return true;
    }
}
