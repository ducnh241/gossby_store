<?php

class Helper_Stripe_Common {

    public function getShippingAddressFromOrder(Model_Catalog_Order $order) {
        if ($order->getId() < 1) {
            return [];
        }
        return [
            'name' => $order->data['shipping_full_name'],
            'phone' => $order->data['shipping_phone'],
            'address' => [
                'city' => $order->data['shipping_city'],
                'country' => $order->data['shipping_country'],
                'line1' => $order->data['shipping_address1'],
                'line2' => $order->data['shipping_address2'],
                'postal_code' => $order->data['shipping_zip'],
                'state' => $order->data['shipping_province'],
            ]
        ];
    }

    public function getBillingAddressFromOrder(Model_Catalog_Order $order) {
        if ($order->getId() < 1) {
            return [];
        }
        return [
            'city' => $order->data['billing_city'],
            'country' => $order->data['billing_country'],
            'line1' => $order->data['billing_address1'],
            'line2' => $order->data['billing_address2'],
            'postal_code' => $order->data['billing_zip'],
            'state' => $order->data['billing_province'],
        ];
    }

    public function cancelPaymentIntentByAccount($payment_account_id, $payment_intent_id) {
        if (empty($payment_account_id) || empty($payment_intent_id)) {
            return;
        }

        // get $payment_account by $payment_account_id
        $payment_account = OSC::model('multiPaymentAccount/account')->load($payment_account_id);

        if (!empty($payment_account->data['account_info']['secret_key'])) {
            $payment_intent = OSC::helper('stripe/paymentIntent')->retrieve(
                $payment_account->data['account_info']['secret_key'],
                $payment_intent_id
            );

            if ($payment_intent->status !== 'canceled') {
                OSC::helper('stripe/paymentIntent')->void(
                    $payment_account->data['account_info']['secret_key'],
                    $payment_intent_id
                );
            } else {
                // save tracking log
                OSC::core('mongodb')->insert(
                    'place_order_log',
                    [
                        'flag' => 5,
                        'payment_intent_id' => $payment_intent_id,
                        'payment_intent_data' => $payment_intent,
                        'added_timestamp' => time()
                    ],
                    'product'
                );
            }
        }
    }
}
