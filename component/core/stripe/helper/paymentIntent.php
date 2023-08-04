<?php

use Stripe\ApplePayDomain;
use Stripe\Charge;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\StripeObject;
use Stripe\Refund;

class Helper_Stripe_PaymentIntent {
    private const CURRENCY = 'USD';

    public function getDefaultCurrency() {
        return self::CURRENCY;
    }

    /**
     * @param $secret_key
     * @return StripeClient
     */
    private function __getStripeClient($secret_key) {
        return new StripeClient($secret_key);
    }

    /**
     * @param PaymentIntent $payment_intent
     * @return array
     */
    public function getGatewayReturnData(PaymentIntent $payment_intent) {
        $charge_id = null;

        $card_response_data = [
            'brand' => null,
            'funding' => null,
            'last_digits' => null
        ];

        $fraud_data = [
            'score' => null,
            'info' => null
        ];

        // get charges of $payment_intent
        $charges = $payment_intent->charges;

        // Get payment_data:
        // validate $charges is existed and contain data
        if (!empty($charges) && $charges->count()) {
            // get the latest charge (@see https://stripe.com/docs/api/payment_intents/object#payment_intent_object-charges)
            $charge = $charges->first();

            if (!empty($charge) && $charge instanceof Charge) {
                // get charge id
                $charge_id = $charge->id;

                // get payment detail
                $payment_method_details = $charge->payment_method_details;

                // get outcome
                $outcome = $charge->outcome;

                // validate $payment_method_details and $card
                if ($payment_method_details instanceof StripeObject &&
                    !empty($payment_method_details->card) &&
                    $payment_method_details->card instanceof StripeObject) {
                    // get card
                    $card = $payment_method_details->card;

                    $card_response_data = [
                        'brand' => $card->brand ?? null,
                        'funding' => $card->funding ?? null,
                        'last_digits' => $card->last4 ?? null
                    ];
                }

                // get fraud_data
                if (!empty($outcome) && $outcome instanceof StripeObject) {
                    $fraud_data = [
                        'score' => $outcome->risk_score ?? Model_Catalog_Order::FRAUD_RISK_LEVEL[$outcome->risk_level]['score'],
                        'info' => $outcome->seller_message
                    ];
                }
            }

        }

        return [
            'payment_data' => [
                'card_info' => $card_response_data,
                'charge_id' => $charge_id,
                'payment_intent_id' => $payment_intent->id
            ],
            'fraud_data' => $fraud_data
        ];
    }

    /**
     * @param $secret_key
     * @param array $create_data
     * array (
     *     'amount' => 1000,
     *     'currency_code' => 'USD',
     *     'invoice_number' => 'Cart-xxx'
     * )
     * @return string|null
     */
    public function create($secret_key, array $create_data) {
        $payment_intent = null;

        try {
            $stripe_client = $this->__getStripeClient($secret_key);

            $payment_intent = $stripe_client->paymentIntents->create(
                [
                    'amount' => $create_data['amount'],
                    'currency' => $create_data['currency_code'],
                    'automatic_payment_methods' => ['enabled' => true],
                    'capture_method' => 'manual',
                    'description' => $create_data['invoice_number']
                ]
            );
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $payment_intent;
    }

    /**
     * @param $secret_key
     * @param $payment_intent_id
     * @return PaymentIntent|null
     */
    public function retrieve($secret_key, $payment_intent_id) {
        $payment_intent = null;

        try {
            $stripe_client = $this->__getStripeClient($secret_key);

            $payment_intent = $stripe_client->paymentIntents->retrieve($payment_intent_id);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $payment_intent;
    }

    /**
     * @param $secret_key
     * @param $payment_intent_id
     * @return PaymentIntent|null
     */
    public function confirm($secret_key, $payment_intent_id) {
        $payment_intent = null;

        try {
            $stripe_client = $this->__getStripeClient($secret_key);

            $payment_intent = $stripe_client->paymentIntents->confirm($payment_intent_id);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $payment_intent;
    }

    /**
     * @param $secret_key
     * @param $payment_intent_id
     * @return PaymentIntent|null
     */
    public function capture($secret_key, $payment_intent_id) {
        $payment_intent = null;

        try {
            $stripe_client = $this->__getStripeClient($secret_key);

            $payment_intent = $stripe_client->paymentIntents->capture($payment_intent_id);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $payment_intent;
    }

    /**
     * @param $secret_key
     * @param $payment_intent_id
     * @param $amount
     * @return Refund|null
     */
    public function refund($secret_key, $payment_intent_id, $amount = null) {
        $refund = null;

        try {
            $stripe_client = $this->__getStripeClient($secret_key);
            $create_refund_parameters = [
                'payment_intent' => $payment_intent_id,
            ];

            if ($amount) {
                $create_refund_parameters['amount'] = $amount;
            }

            $refund = $stripe_client->refunds->create($create_refund_parameters);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $refund;
    }

    /**
     * @param $secret_key
     * @param $payment_intent_id
     * @return PaymentIntent|null
     */
    public function void($secret_key, $payment_intent_id) {
        $payment_intent = null;

        try {
            $stripe_client = $this->__getStripeClient($secret_key);

            $payment_intent = $stripe_client->paymentIntents->cancel($payment_intent_id);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $payment_intent;
    }

    /**
     * @param $secret_key
     * @param $payment_intent_id
     * @param array $update_data
     * @return PaymentIntent|null
     */
    public function update($secret_key, $payment_intent_id, array $update_data) {
        $payment_intent = null;

        try {
            $stripe_client = $this->__getStripeClient($secret_key);

            $payment_intent = $stripe_client->paymentIntents->update($payment_intent_id, $update_data);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $payment_intent;
    }

    /**
     * Register current domain to Apple
     * @return void
     */
    public function registerApplePayDomain() {
        try {
            ApplePayDomain::create([
                'domain_name' => OSC::$domain
            ]);
        } catch (Exception $exception) {}
    }
}
