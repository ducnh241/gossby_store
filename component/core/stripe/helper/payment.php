<?php

use Stripe\StripeClient;

//https://www.kuemerle.com/accepting-credit-card-payments-in-php-with-stripe/
//https://medium.com/@Keithweaver_/using-stripe-with-php-c341fcc6b68b
class Helper_Stripe_Payment extends Abstract_Catalog_Payment {

    public function __construct() {
        parent::__construct();
    }

    public function setAccount($account) {
        parent::setAccount($account);

        \Stripe\Stripe::setApiKey($account['account_info']['secret_key']);

        // register apple pay domain
        OSC::helper('stripe/paymentIntent')->registerApplePayDomain();

        return $this;
    }

    public function getSecretKey($encode = true) {
        $sk_key = $this->getAccount()['account_info']['secret_key'];
        return $encode ? md5($sk_key) : $sk_key;
    }

    public function getPublicKey() {
        return $this->getAccount()['account_info']['public_key'] ?? '';
    }

    public function getPriority() {
        return 100;
    }

    public function getKey() {
        return 'stripe';
    }

    public function getTextTitle() {
        return 'Credit card';
    }

    /**
     * 
     * @param Model_Catalog_Order $order
     * @return string
     */
    public function getTextTitleWithInfo(Model_Catalog_Order $order): string {
        return $this->getTextTitle() . ' - ' . $order->data['payment_data']['card_info']['brand'] . '/' . $order->data['payment_data']['card_info']['funding'] . '/' . $order->data['payment_data']['card_info']['last_digits'];
    }

    public function getHtmlTitle() {
        return OSC::helper('frontend/template')->build('stripe/title');
    }

    public function getPaymentForm() {
        return OSC::helper('frontend/template')->build('stripe/form');
    }

    public function _getToken(array $payment_info) {

        $request = OSC::core('request');

        $stripe_cc = $request->get('card');
        $stripe_cc['expire_date'] = [$stripe_cc['expiry_date_month'], $stripe_cc['expiry_date_year']];

        $token = \Stripe\Token::create([
            'card' => [
                'number' => $stripe_cc['number'],
                // 'name' => $stripe_cc['name'],
                'exp_month' => intval($stripe_cc['expire_date'][0]),
                'exp_year' => intval($stripe_cc['expire_date'][1]),
                'cvc' => $stripe_cc['cvc'],
                'address_line1' => $payment_info['billing_address']['address1'],
                'address_line2' => $payment_info['billing_address']['address2'],
                'address_city' => $payment_info['billing_address']['city'],
                'address_state' => $payment_info['billing_address']['province'],
                'address_zip' => $payment_info['billing_address']['zip'],
                'address_country' => $payment_info['billing_address']['country']
            ]
        ]);
        
        return [
            'token' => $token->id, 
            'card_info' => [
                'brand' => $token->card->brand, 
                'funding' => $token->card->funding, 
                'last_digits' => $token->card->last4
            ]
        ];
    }

    public function charge(array $payment_info) {
        $payment_intent_id = $payment_info['payment_intent_id'] ?? null;

        if ($payment_intent_id) {
            // handle using stripe payment intent
            $secret_key = $this->getSecretKey(false);

            // get payment intent
            $payment_intent = OSC::helper('stripe/paymentIntent')->retrieve($secret_key, $payment_intent_id);

            // capture payment intent
            OSC::helper('stripe/paymentIntent')->capture($secret_key, $payment_intent_id);

            return OSC::helper('stripe/paymentIntent')->getGatewayReturnData($payment_intent);
        }

        $payment_data = $this->_getToken($payment_info);

        $charge = \Stripe\Charge::create([
            'amount' => OSC::helper('currency/common')->convertToMinimumUnit($payment_info['total_price'], $payment_info['currency_code']),
            'currency' => $payment_info['currency_code'],
            'source' => $payment_data['token'],
            'capture' => true,
            'description' => $payment_info['description'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'ip' => OSC::getClientIP(),
            'payment_user_agent' => 'Stripe/v1 ActiveMerchantBindings/1.93.0',
            'metadata' => [
                'shop_id' => OSC_SITE_KEY,
                'shop_name' => OSC::helper('core/setting')->get('theme/site_name'),
                'email' => $payment_info['email']
            ],
            'shipping' => [
                'name' => $payment_info['shipping_info']['address']['full_name'],
                'phone' => $payment_info['shipping_info']['address']['phone'],
                'carrier' => $payment_info['shipping_info']['shipping_method'],
                'address' => [
                    'line1' => $payment_info['shipping_info']['address']['address1'],
                    'line2' => $payment_info['shipping_info']['address']['address2'],
                    'city' => $payment_info['shipping_info']['address']['city'],
                    'postal_code' => $payment_info['shipping_info']['address']['zip'],
                    'state' => $payment_info['shipping_info']['address']['province'],
                    'country' => $payment_info['shipping_info']['address']['country']
                ]
            ],
        ]);

        $payment_data['charge_id'] = $charge->id;

        return [
            'payment_data' => $payment_data,
            'fraud_data' => [
                'score' => $charge->outcome->risk_score ? $charge->outcome->risk_score : Model_Catalog_Order::FRAUD_RISK_LEVEL[$charge->outcome->risk_level]['score'],
                'info' => $charge->outcome->seller_message
            ]
        ];
    }

    public function authorize(array $payment_info) {
        $payment_intent_id = $payment_info['payment_intent_id'] ?? null;

        if ($payment_intent_id) {
            // handle using stripe payment intent
            $secret_key = $this->getSecretKey(false);

            // get payment intent
            $payment_intent = OSC::helper('stripe/paymentIntent')->retrieve($secret_key, $payment_intent_id);

            return OSC::helper('stripe/paymentIntent')->getGatewayReturnData($payment_intent);
        }

        $payment_data = $this->_getToken($payment_info);

        $charge = \Stripe\Charge::create([
            'amount' => OSC::helper('currency/common')->convertToMinimumUnit($payment_info['total_price'], $payment_info['currency_code']),
            'currency' => $payment_info['currency_code'],
            'source' => $payment_data['token'],
            'capture' => false,
            'description' => $payment_info['description'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'ip' => OSC::getClientIP(),
            'payment_user_agent' => 'Stripe/v1 ActiveMerchantBindings/1.93.0',
            'metadata' => [
                'shop_id' => OSC_SITE_KEY,
                'shop_name' => OSC::helper('core/setting')->get('theme/site_name'),
                'email' => $payment_info['email']
            ],
            'shipping' => [
                'name' => $payment_info['shipping_info']['address']['full_name'],
                'phone' => $payment_info['shipping_info']['address']['phone'],
                'carrier' => $payment_info['shipping_info']['shipping_method'],
                'address' => [
                    'line1' => $payment_info['shipping_info']['address']['address1'],
                    'line2' => $payment_info['shipping_info']['address']['address2'],
                    'city' => $payment_info['shipping_info']['address']['city'],
                    'postal_code' => $payment_info['shipping_info']['address']['zip'],
                    'state' => $payment_info['shipping_info']['address']['province'],
                    'country' => $payment_info['shipping_info']['address']['country']
                ]
            ],
        ]);

        $payment_data['charge_id'] = $charge->id;

        return [
            'payment_data' => $payment_data,
            'fraud_data' => [
                'score' => $charge->outcome->risk_score ? $charge->outcome->risk_score : Model_Catalog_Order::FRAUD_RISK_LEVEL[$charge->outcome->risk_level]['score'],
                'info' => $charge->outcome->seller_message
            ]
        ];
    }

    public function referenceTransaction(array $payment_info) {

    }

    public function update(array $payment_info, array $payment_data) {
        try {
            $charge = \Stripe\Charge::create(
                [
                    'amount' => OSC::helper('currency/common')->convertToMinimumUnit($payment_info['total_price'], $payment_info['currency_code']),
                    'currency' => $payment_info['currency_code'],
                    'source' => $payment_data['token'],
                ]
            );

            $payment_data['charge_id'] = $charge->id;

            return [
                'payment_data' => $payment_data,
                'fraud_data' => [
                    'score' => $charge->outcome->risk_score ? $charge->outcome->risk_score : Model_Catalog_Order::FRAUD_RISK_LEVEL[$charge->outcome->risk_level]['score'],
                    'info' => $charge->outcome->seller_message
                ]
            ];
        } catch (Exception $exception) {

        }
    }

    public function generateClientToken($customer_id) {
        return '';
    }

    public function void($payment_data, float $amount, string $currency_code, int $added_timestamp) {
        $payment_info = $payment_data;

        if (empty($payment_info['payment_data']) || empty($payment_info['payment_method'])) {
            return $payment_data;
        }

        $payment_data = $payment_info['payment_data'];
        $payment_method = $payment_info['payment_method'];

        // flag if order is handled by stripe payment intent
        $is_payment_intent = !empty($payment_method['payment_intent']);

        if ($added_timestamp < (time() - (60 * 60 * 24 * 7))) {
            $payment_data['void_note'] = 'Expired transaction';
            return $payment_data;
        }

        if ($is_payment_intent) {
            // order is handled by stripe payment intent
            if (empty($payment_method['account']['account_info']['secret_key']) ||
                empty($payment_data['payment_intent_id'])) {
                return $payment_data;
            }

            // refund payment intent
            OSC::helper('stripe/paymentIntent')->refund(
                $payment_method['account']['account_info']['secret_key'],
                $payment_data['payment_intent_id']
            );

            return $payment_data;
        }

        return $this->refund(
            $payment_data,
            $amount,
            $currency_code,
            OSC::helper('core/setting')->get('theme/site_name') . ' :: Cancel order'
        );
    }

    public function capture($payment_data, float $amount, string $currency_code) {
        $payment_info = $payment_data;

        if (empty($payment_info['payment_data']) || empty($payment_info['payment_method'])) {
            return $payment_data;
        }

        $payment_data = $payment_info['payment_data'];
        $payment_method = $payment_info['payment_method'];

        // flag if order is handled by stripe payment intent
        $is_payment_intent = !empty($payment_method['payment_intent']);

        try {
            if ($is_payment_intent) {
                // order is handled by stripe payment intent
                // check existed account's secret key and payment intent id
                if (empty($payment_method['account']['account_info']['secret_key']) ||
                    empty($payment_data['payment_intent_id'])) {
                    return $payment_data;
                }

                // capture payment intent
                OSC::helper('stripe/paymentIntent')->capture(
                    $payment_method['account']['account_info']['secret_key'],
                    $payment_data['payment_intent_id']
                );
            } else {
                $ch = \Stripe\Charge::retrieve($payment_data['charge_id']);
                $ch->capture(['amount' => OSC::helper('currency/common')->convertToMinimumUnit($amount, $currency_code)]);
            }
        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), 'has already been captured') === false) {
                throw new Exception($ex->getMessage());
            }
        }

        return $payment_data;
    }

    public function refund($payment_data, float $amount, string $currency_code, string $description, string $reason = '') {
        $payment_info = $payment_data;

        if (empty($payment_info['payment_data']) || empty($payment_info['payment_method'])) {
            return $payment_data;
        }

        $payment_data = $payment_info['payment_data'];
        $payment_method = $payment_info['payment_method'];

        // flag if order is handled by stripe payment intent
        $is_payment_intent = !empty($payment_method['payment_intent']);

        try {
            if ($is_payment_intent) {
                // order is handled by stripe payment intent
                // check existed account's secret key and payment intent id
                if (empty($payment_method['account']['account_info']['secret_key']) ||
                    empty($payment_data['payment_intent_id'])) {
                    return $payment_data;
                }

                // refund payment intent
                $refund = OSC::helper('stripe/paymentIntent')->refund(
                    $payment_method['account']['account_info']['secret_key'],
                    $payment_data['payment_intent_id'],
                    OSC::helper('currency/common')->convertToMinimumUnit($amount, $currency_code)
                );
            } else {
                $refund = \Stripe\Refund::create([
                    'charge' => $payment_data['charge_id'],
                    'amount' => OSC::helper('currency/common')->convertToMinimumUnit($amount, $currency_code),
                    'reason' => $reason ? $reason : null
                ]);
            }

            if (!isset($payment_data['refund_ids'])) {
                $payment_data['refund_ids'] = [];
            }

            $payment_data['refund_ids'][] = $refund->id;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $payment_data;
    }

    public function compareTransaction($a, $b) {
        return is_array($a) && is_array($b) && isset($a['charge_id']) && isset($b['charge_id']) && $a['charge_id'] == $b['charge_id'];
    }

    public function retrieveStripeCharge(string $id) {
        try {
            $response = \Stripe\Charge::retrieve($id);
            if ($response && is_object($response)) {
                return $response;
            } else {
                return null;
            }
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * @param string $id
     * @param array $data
     * @return object|null
     */
    public function updateStripeCharge(string $id, array $data) {
        try {
            $response = null;
            if ($this->retrieveStripeCharge($id) && count($data) > 0) {
                $response = \Stripe\Charge::update($id, $data);
            }
            return $response;
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
