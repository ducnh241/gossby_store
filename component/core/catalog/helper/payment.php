<?php

class Helper_Catalog_Payment
{
    public static $_payment_order_id = 'payment_order_id';

    public function makeTransaction(
        Abstract_Catalog_Payment $payment_method,
        string $currency_code,
        float $total_price,
        array $line_items,
        array $shipping_method,
        array $taxes,
        array $discount,
        array $price_summary,
        array $shipping_address,
        array $billing_address,
        string $invoice_number = '',
        string $email = '',
        array $extra_data = []
    )
    {
        $payment_info = [
            'invoice_number' => $invoice_number ?: OSC::makeUniqid(),
            'description' => OSC::helper('core/setting')->get('theme/site_name') . ($invoice_number ? (' :: ' . $invoice_number) : ''),
            'line_items' => $line_items,
            'currency_code' => $currency_code,
            'total_price' => $total_price,
            'shipping_info' => [
                'method' => $shipping_method['title'],
                'price' => $shipping_method['price'],
                'address' => $shipping_address
            ],
            'discount' => $discount,
            'taxes' => $taxes,
            'price_summary' => $price_summary,
            'billing_address' => $billing_address,
            'email' => $email ?: '',
            'remember_account' => $extra_data['remember_account'] ?? 0,
            'customer_stripe_id' => $extra_data['customer_stripe_id'] ?? null,
        ];

        $payment_method_tag = 'paypal';
        if (!empty($extra_data['payment_intent_id'])) {
            $payment_method_tag = 'stripe';
            $payment_info['payment_intent_id'] = $extra_data['payment_intent_id'];
        }

        try {
            if ($payment_method->isInAuthorizeMode()) {
                $gateway_return = $payment_method->authorize($payment_info);
                $payment_status = 'authorized';
            } else {
                $gateway_return = $payment_method->charge($payment_info);
                $payment_status = 'captured';
            }
        } catch (Exception $ex) {
            try {
                OSC::helper('multiPaymentAccount/common')->markAccountError($payment_method->getAccount(), $ex->getMessage());
                /* Log payment transaction */
                if (IS_ENABLE_SENTRY) {
                    \Sentry\init([
                        'dsn' => SENTRY_DSN,
                        'environment' => OSC_ENV . '_' . OSC_SITE_KEY,
                    ]);
                    \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($ex, $payment_info, $payment_method_tag): void {
                        $scope->setContext('payment_info', $payment_info);
                        $scope->setTag('type', 'payment');
                        $scope->setTag('payment_method', $payment_method_tag);

                        \Sentry\captureException($ex);
                    });
                }
            } catch (Exception $ex) {

            }

            $error_info = json_decode($ex->getMessage(), true);
            if (isset($error_info) && $error_info['name'] === 'INTERNAL_SERVER_ERROR') {
                throw new Exception(OSC::core('language')->get('payment.internal_server_error_message'));
            }

            throw new Exception($ex->getMessage() . ". Please try again or contact us to help you.");
        }

        return [
            'payment_status' => $payment_status,
            'payment_method' => ['key' => $payment_method->getKey(), 'title' => $payment_method->getTitle(), 'object' => $payment_method->getOSCObjectType()],
            'payment_data' => is_array($gateway_return) && isset($gateway_return['payment_data']) ? $gateway_return['payment_data'] : '',
            'fraud_data' => is_array($gateway_return) && isset($gateway_return['fraud_data']) ? $gateway_return['fraud_data'] : '',
            'email' => is_array($gateway_return) && isset($gateway_return['email']) ? $gateway_return['email'] : '',
            'shipping_address' => is_array($gateway_return) && isset($gateway_return['shipping_address']) ? $gateway_return['shipping_address'] : '',
            'billing_address' => is_array($gateway_return) && isset($gateway_return['billing_address']) ? $gateway_return['billing_address'] : ''
        ];
    }

    public function referenceTransaction(Model_Catalog_Order_Item $line_item, $subtotal, $discount, $shipping, $tax, $total, array $extra_data = [])
    {
        $order = $line_item->getOrder();
        $payment_method = $order->getPayment();
        $invoice_number = $order->data['cart_ukey'] ? 'CART-' . $order->data['cart_ukey'] . '/' . time() : OSC::makeUniqid();
        $payment_data = $order->data['payment_data'];

        $line_items = [
            [
                'title' => $line_item->data['title'],
                'options' => $line_item->getVariantOptionsText(),
                'sku' => $line_item->data['sku'],
                'price' => $line_item->getFloatPrice(),
                'amount' => $line_item->getFloatAmount(),
                'quantity' => $line_item->data['quantity']
            ]
        ];

        $shipping_address = $order->getShippingAddress(true);
        $billing_address = $order->getBillingAddress(true, true);
        $shipping_method = [
            'price' => $shipping,
            'title' => $order->getShippingMethodTitle()
        ];
        $email = $order->data['email'];

        $price_summary = [
            'currency_code' => $order->data['currency_code'],
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total
        ];

        $payment_info = [
            'invoice_number' => $invoice_number,
            'description' => OSC::helper('core/setting')->get('theme/site_name') . ($invoice_number ? (' :: ' . $invoice_number) : ''),
            'line_items' => $line_items,
            'currency_code' => $order->data['currency_code'],
            'total_price' => $total,
            'shipping_info' => [
                'method' => $shipping_method['title'],
                'price' => $shipping_method['price'],
                'address' => $shipping_address
            ],
            'discount' => $discount,
            'taxes' => $tax,
            'price_summary' => $price_summary,
            'billing_address' => $billing_address,
            'email' => $email ? $email : '',
            'uid' => $extra_data['uid'] ?? '',
            'ba_id' => $payment_data['ba_id'],
            'ba_token' => $payment_data['ba_token']
        ];

        try {
            $gateway_return = $payment_method->referenceTransaction($payment_info);
            $payment_status = 'authorized';
        } catch (Exception $ex) {
            try {
                OSC::helper('multiPaymentAccount/common')->markAccountError($payment_method->getAccount(), $ex->getMessage());
            } catch (Exception $ex) {
            }

            $error_info = json_decode($ex->getMessage(), true);
            if (isset($error_info) && $error_info['name'] === 'INTERNAL_SERVER_ERROR') {
                throw new Exception(OSC::core('language')->get('payment.internal_server_error_message'));
            }

            throw new Exception($ex->getMessage() . ". Please try again or contact us to help you.");
        }

        return [
            'payment_status' => $payment_status,
            'payment_method' => ['key' => $payment_method->getKey(), 'title' => $payment_method->getTitle(), 'object' => $payment_method->getOSCObjectType()],
            'payment_data' => is_array($gateway_return) && isset($gateway_return['payment_data']) ? $gateway_return['payment_data'] : '',
            'fraud_data' => is_array($gateway_return) && isset($gateway_return['fraud_data']) ? $gateway_return['fraud_data'] : '',
            'email' => is_array($gateway_return) && isset($gateway_return['email']) ? $gateway_return['email'] : '',
            'shipping_address' => is_array($gateway_return) && isset($gateway_return['shipping_address']) ? $gateway_return['shipping_address'] : '',
            'billing_address' => is_array($gateway_return) && isset($gateway_return['billing_address']) ? $gateway_return['billing_address'] : ''
        ];
    }

}