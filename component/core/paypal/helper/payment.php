<?php

//https://developer.paypal.com/docs/api/quickstart/install/
//https://developer.paypal.com/docs/api/quickstart/capture-payment/#
//https://developer.paypal.com/docs/api/quickstart/payments/#define-payment

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;

use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersAuthorizeRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

use PayPalCheckoutSdk\Payments\AuthorizationsCaptureRequest;
use PayPalCheckoutSdk\Payments\AuthorizationsGetRequest;
use PayPalCheckoutSdk\Payments\AuthorizationsVoidRequest;
use PayPalCheckoutSdk\Payments\CapturesRefundRequest;

use BraintreeHttp\HttpException;

class Helper_Paypal_Payment extends Abstract_Catalog_Payment {
    protected $_callback_url = 'checkout/index/place';
    protected $_cancel_url = 'checkout/index/place';
    protected $_notification_url = 'checkout/index/place';
    /**
     *
     * @var PayPalCheckoutSdk\Core\PayPalHttpClient
     */
    protected $_client = null;

    /**
     *
     * @return PayPalCheckoutSdk\Core\PayPalHttpClient
     */
    protected function _getClient() {
        if ($this->_client === null) {
            if (OSC_ENV != 'production') {
                $env = new SandboxEnvironment($this->_account['account_info']['client_id'], $this->_account['account_info']['client_secret']);
            } else {
                $env = new ProductionEnvironment($this->_account['account_info']['client_id'], $this->_account['account_info']['client_secret']);
            }

            $this->_client = new PayPalHttpClient($env);
        }

        return $this->_client;
    }

    protected function _buildRequestBody(array $payment_info) {
        $items = [];

        if (isset($payment_info['line_items']) && !empty($payment_info['line_items'])) {
            foreach ($payment_info['line_items'] as $line_item) {
                $items[] = [
                    'name' => substr($line_item['title'], 0, 127),
                    'description' => substr($line_item['options'], 0, 127),
                    'unit_amount' => [
                        'currency_code' => $payment_info['currency_code'],
                        'value' => strval($line_item['price']),
                    ],
                    'quantity' => $line_item['quantity'],
                    'category' => 'PHYSICAL_GOODS',
                ];
            }
        }

        $subtotal = $payment_info['price_summary']['subtotal'];

        $amount = [
            'currency_code' => $payment_info['currency_code'],
            'value' => strval($payment_info['total_price']),
            'breakdown' => [
                'item_total' => [
                    'currency_code' => $payment_info['currency_code'],
                    'value' => strval($subtotal),
                ],
                'shipping' => [
                    'currency_code' => $payment_info['currency_code'],
                    'value' => strval($payment_info['price_summary']['shipping']),
                ],
                'tax_total' => [
                    'currency_code' => $payment_info['currency_code'],
                    'value' => strval($payment_info['price_summary']['tax']),
                ],
                'handling' => [
                    'currency_code' => $payment_info['currency_code'],
                    'value' => '0',
                ],
                'shipping_discount' => [
                    'currency_code' => $payment_info['currency_code'],
                    'value' => '0',
                ],
                'discount' => [
                    'currency_code' => $payment_info['currency_code'],
                    'value' => strval(abs($payment_info['price_summary']['discount'])),
                ]
            ],
        ];

        $shipping = [
            'name' => [
                'full_name' => $payment_info['shipping_info']['address']['full_name'],
            ],
            'address' => [
                'address_line_1' => substr($payment_info['shipping_info']['address']['address1'], 0, 300),
                'address_line_2' => substr($payment_info['shipping_info']['address']['address2'], 0, 300),
                'admin_area_2' => substr($payment_info['shipping_info']['address']['city'], 0, 120),
                'admin_area_1' => ($payment_info['shipping_info']['address']['province_code'] && preg_match('/[^0-9]/', $payment_info['shipping_info']['address']['province_code'])) ? substr($payment_info['shipping_info']['address']['province_code'], 0, 300) : substr($payment_info['shipping_info']['address']['province'], 0, 300),
                'postal_code' => substr($payment_info['shipping_info']['address']['zip'], 0, 60),
                'country_code' => $payment_info['shipping_info']['address']['country_code'],
            ],
        ];

        return [
            'intent' => 'AUTHORIZE',
            'application_context' => [
                'return_url' => OSC::getUrl($this->_callback_url, ['paypal_flag' => 'success', 'payment_method' => $this->getKey()]),
                'cancel_url' => OSC::getUrl($this->_cancel_url, ['paypal_flag' => 'cancel', 'payment_method' => $this->getKey()]),
                'user_action' => 'PAY_NOW'
            ],
            'purchase_units' => [
                [
                    'reference_id' => OSC::randKey(8, 5),
                    'invoice_id' => $payment_info['invoice_number'],
                    'description' => substr($payment_info['description'], 0, 127),
                    'amount' => $amount,
                    'items' => $items,
                    'shipping' => $shipping
                ],
            ]
        ];
    }

    protected function _buildBillingAgreementBody(array $payment_info) {
        return [
            'description' => 'Billing Agreement',
            'shipping_address' => [
                'line1' => substr($payment_info['shipping_info']['address']['address1'], 0, 300),
                'city' => substr($payment_info['shipping_info']['address']['city'], 0, 120),
                'state' => ($payment_info['shipping_info']['address']['province_code'] && preg_match('/[^0-9]/', $payment_info['shipping_info']['address']['province_code'])) ? substr($payment_info['shipping_info']['address']['province_code'], 0, 300) : substr($payment_info['shipping_info']['address']['province'], 0, 300),
                'postal_code' => substr($payment_info['shipping_info']['address']['zip'], 0, 60),
                'country_code' => $payment_info['shipping_info']['address']['country_code'],
                'recipient_name' => $payment_info['shipping_info']['address']['full_name']
            ],
            'payer' => [
                'payment_method' => 'PAYPAL'
            ],
            'plan' => [
                'type' => 'MERCHANT_INITIATED_BILLING',
                'merchant_preferences' =>
                    [
                        'return_url' => OSC::getUrl($this->_callback_url, ['paypal_flag' => 'ba-success', 'payment_method' => $this->getKey()]),
                        'cancel_url' => OSC::getUrl($this->_cancel_url, ['paypal_flag' => 'ba-cancel', 'payment_method' => $this->getKey()]),
                        'notify_url' => OSC::getUrl($this->_notification_url, ['paypal_flag' => 'ba-notify', 'payment_method' => $this->getKey()]),
                        'accepted_pymt_type' => 'INSTANT',
                        'skip_shipping_address' => false,
                        'immutable_shipping_address' => true
                    ]
            ]
        ];
    }

    //https://github.com/paypal/Checkout-PHP-SDK/blob/develop/samples/CaptureIntentExamples/CreateOrder.php
    protected function _createTransaction(array $payment_info, bool $capture_flag = false) {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = $this->_buildRequestBody($payment_info);

        try {
            // Call API with your client and get a response for your call
            $response = $this->_getClient()->execute($request);

            if ($response->statusCode != 201) {
                throw new Exception('Paypal response code is incorrect');
            }

            OSC::register(Helper_Catalog_Payment::$_payment_order_id, $response->result->id);
        } catch (Exception $ex) {
            if (method_exists($ex, 'getData')) {
                $message = json_decode($ex->getData(), true);
            } else {
                $message = ['message' => $ex->getMessage()];
            }

            if (isset($message['error_description']))
                $message['message'] = $message['error_description'];

            OSC::helper('catalog/checkout')->insertFootprint('PAYPAL', [$ex, $message]);
            throw new Exception($message['message']);
        }
    }

    protected function _createBillingAgreementTransaction(array $payment_info) {
        try {
            $response = OSC::helper('paypalVault/referenceTransaction')->createBillingAgreementToken($this->_getClient(), $this->_buildBillingAgreementBody($payment_info));

            $approval_link = '';
            foreach ($response->result->links as $link) {
                if ($link->rel === 'approval_url') {
                    $approval_link = $link->href . '&useraction=commit';
                    break;
                }
            }
        } catch (Exception $exception) {
            if (method_exists($exception, 'getData')) {
                $message = json_decode($exception->getData(), true);
            } else {
                $message = ['message' => $exception->getMessage()];
            }

            if (isset($message['error_description']))
                $message['message'] = $message['error_description'];

            OSC::helper('catalog/checkout')->insertFootprint('PAYPAL', [$exception, $message]);
            throw new Exception($message['message']);
        }
        OSC::register('redirect_by_payment', $approval_link);
    }

    public function getKey() {
        return 'paypal';
    }

    public function getTextTitle() {
        return 'Paypal';
    }

    public function getHtmlTitle() {
        return OSC::helper('frontend/template')->build('paypal/title');
    }

    public function getPaymentForm() {
        return OSC::helper('frontend/template')->build('paypal/form');
    }

    protected function _expressCheckoutCharge($order_id, $payment_info) {
        $order_info = $this->_getClient()->execute(new OrdersGetRequest($order_id));

        if ($order_info->statusCode != 200) {
            throw new Exception('Paypal response code is incorrect');
        }

        if ($order_info->result->purchase_units[0]->amount->value != $payment_info['total_price']) {
            throw new Exception('Please refresh your browser and try again.');
        }

        $request = new OrdersCaptureRequest($order_id);

        try {
            $response = $this->_getClient()->execute($request);
        } catch (HttpException $ex) {
            $message = json_decode($ex->getMessage(), true);
            OSC::helper('catalog/checkout')->insertFootprint('PAYPAL_EXPRESS_CHECKOUT', [$ex, $message]);
            throw new Exception($message['details'][0]['description']);
        }

        if ($response->statusCode != 201) {
            throw new Exception('Paypal response code is incorrect');
        }

        $capture_id = $response->result->purchase_units[0]->payments->captures[0]->id;

        if (!$capture_id) {
            throw new Exception('Cannot fetch capture ID from paypal response');
        }

        $province_code = $response->result->purchase_units[0]->shipping->address->admin_area_1;
        $province = OSC::helper('core/country')->getProvinceTitle($response->result->purchase_units[0]->shipping->address->country_code, $response->result->purchase_units[0]->shipping->address->admin_area_1);

        if (!$province) {
            $province = $province_code;
            $province_code = '';
        }

        return [
            'payment_data' => [
                'capture_id' => $capture_id,
                'oder_id' => $order_id
            ],
            'email' => $response->result->payer->email_address,
            'shipping_address' => [
                'full_name' => $response->result->purchase_units[0]->shipping->name->full_name,
                'phone' => $response->result->payer->phone->phone_number->national_number,
                'address1' => $response->result->purchase_units[0]->shipping->address->address_line_1,
                'address2' => $response->result->purchase_units[0]->shipping->address->address_line_2,
                'city' => $response->result->purchase_units[0]->shipping->address->admin_area_2,
                'province' => $province,
                'province_code' => $province_code,
                'zip' => $response->result->purchase_units[0]->shipping->address->postal_code,
                'country' => OSC::helper('core/country')->getCountryTitle($response->result->purchase_units[0]->shipping->address->country_code),
                'country_code' => $response->result->purchase_units[0]->shipping->address->country_code
            ]
        ];
    }

    public function charge(array $payment_info) {
        /* @var $request OSC_Request */
        $request = OSC::core('request');

        if (in_array($request->get('paypal_flag'), ['success', 'cancel'], true)) {
            if ($request->get('paypal_flag') == 'cancel') {
                throw new Exception('Your payment cancelled');
            }

            if ($request->get('paypal_token')) {
                return $this->_expressCheckoutCharge($request->get('paypal_token'), $payment_info);
            }

            $order_id = $request->get('token');

            try {
                $order_info = $this->_getClient()->execute(new OrdersGetRequest($order_id));

                if ($order_info->statusCode != 200) {
                    throw new Exception('Paypal response code is incorrect');
                }

                if ($order_info->result->purchase_units[0]->amount->value != $payment_info['total_price']) {
                    throw new Exception('Please refresh your browser and try again.');
                }

                $request = new OrdersCaptureRequest($order_id);

                try {
                    $response = $this->_getClient()->execute($request);
                } catch (HttpException $ex) {
                    $message = json_decode($ex->getMessage(), true);
                    OSC::helper('catalog/checkout')->insertFootprint('PAYPAL_EXPRESS_CHECKOUT', [$ex, $message]);
                    throw new Exception($message['details'][0]['description']);
                }

                if ($response->statusCode != 201) {
                    throw new Exception('Paypal response code is incorrect');
                }

                $capture_id = $response->result->purchase_units[0]->payments->captures[0]->id;

                if (!$capture_id) {
                    throw new Exception('Cannot fetch capture ID from paypal response');
                }
            } catch (Exception $ex) {
                if (method_exists($ex, 'getData')) {
                    $message = json_decode($ex->getData(), true);
                } else {
                    $message = ['message' => $ex->getMessage()];
                }

                OSC::helper('catalog/checkout')->insertFootprint('PAYPAL', [$ex, $message]);
                throw new Exception($message['message']);
            }

            return [
                'payment_data' => ['capture_id' => $capture_id]
            ];
        }

        //$this->_createTransaction($payment_info, true);
        $this->_createBillingAgreementTransaction($payment_info);
    }

    protected function _expressCheckoutAuthorize($order_id, $payment_info) {
        $order_info = $this->_getClient()->execute(new OrdersGetRequest($order_id));

        if ($order_info->statusCode != 200) {
            throw new Exception('Paypal response code is incorrect');
        }

        if ($order_info->result->purchase_units[0]->amount->value != $payment_info['total_price']) {
            throw new Exception('Please refresh your browser and try again.');
        }

        $request = new OrdersAuthorizeRequest($order_id);
        $request->body = '{}';

        try {
            $response = $this->_getClient()->execute($request);
        } catch (HttpException $ex) {
            $message = json_decode($ex->getMessage(), true);
            OSC::helper('catalog/checkout')->insertFootprint('PAYPAL_EXPRESS_CHECKOUT', [$ex, $message]);
            throw new Exception($message['details'][0]['description']);
        }

        if ($response->statusCode != 201) {
            throw new Exception('Paypal response code is incorrect');
        }

        $province_code = $response->result->purchase_units[0]->shipping->address->admin_area_1;
        $province = OSC::helper('core/country')->getProvinceTitle($response->result->purchase_units[0]->shipping->address->country_code, $response->result->purchase_units[0]->shipping->address->admin_area_1);

        if (!$province) {
            $province = $province_code;
            $province_code = '';
        }

        return [
            'payment_data' => [
                'authid' => $response->result->purchase_units[0]->payments->authorizations[0]->id,
                'order_id' => $order_id
            ],
            'email' => $response->result->payer->email_address,
            'shipping_address' => [
                'full_name' => $response->result->purchase_units[0]->shipping->name->full_name,
                'phone' => $response->result->payer->phone->phone_number->national_number,
                'address1' => $response->result->purchase_units[0]->shipping->address->address_line_1,
                'address2' => $response->result->purchase_units[0]->shipping->address->address_line_2,
                'city' => $response->result->purchase_units[0]->shipping->address->admin_area_2,
                'province' => $province,
                'province_code' => $province_code,
                'zip' => $response->result->purchase_units[0]->shipping->address->postal_code,
                'country' => OSC::helper('core/country')->getCountryTitle($response->result->purchase_units[0]->shipping->address->country_code),
                'country_code' => $response->result->purchase_units[0]->shipping->address->country_code
            ]
        ];
    }

    public function authorize(array $payment_info) {
        /* @var $request OSC_Request */
        $request = OSC::core('request');

        if (in_array($request->get('paypal_flag'), ['success', 'cancel'], true)) {
            if ($request->get('paypal_flag') == 'cancel') {
                throw new Exception('Your payment cancelled');
            }

            if ($request->get('paypal_token')) {
                return $this->_expressCheckoutAuthorize($request->get('paypal_token'), $payment_info);
            }

            $order_id = $request->get('token');

            try {
                $order_info = $this->_getClient()->execute(new OrdersGetRequest($order_id));

                if ($order_info->statusCode != 200) {
                    throw new Exception('Paypal response code is incorrect');
                }

                if ($order_info->result->purchase_units[0]->amount->value != $payment_info['total_price']) {
                    throw new Exception('Please refresh your browser and try again.');
                }

                $request = new OrdersAuthorizeRequest($order_id);
                $request->body = '{}';

                try {
                    $response = $this->_getClient()->execute($request);
                } catch (HttpException $ex) {
                    $message = json_decode($ex->getMessage(), true);
                    OSC::helper('catalog/checkout')->insertFootprint('PAYPAL_EXPRESS_CHECKOUT', [$ex, $message]);
                    throw new Exception($message['details'][0]['description']);
                }

                if ($response->statusCode != 201) {
                    throw new Exception('Paypal response code is incorrect');
                }
            } catch (Exception $ex) {
                if (method_exists($ex, 'getData')) {
                    $message = json_decode($ex->getData(), true);
                } else {
                    $message = ['message' => $ex->getMessage()];
                }

                OSC::helper('catalog/checkout')->insertFootprint('PAYPAL', [$ex, $message, $payment_info]);

                //Notify when payment gateway return error AMOUNT MISMATCH
                if (strpos(OSC::encode($message), 'AMOUNT_MISMATCH') !== false) {
                    $content = OSC_FRONTEND_BASE_URL . ': Checkout PAYPAL error:' . OSC::encode([$ex, $message, $payment_info]);
                    OSC::helper('core/telegram')->sendMessage($content, OSC::helper('core/setting')->get('error_payment_notifications/telegram_group_id'));
                }

                throw new Exception($message['message']);
            }

            return [
                'payment_data' => [
                    'authid' => $response->result->purchase_units[0]->payments->authorizations[0]->id,
                    'order_id' => $response->result->id
                ]
            ];
        }

        if (in_array($request->get('paypal_flag'), ['ba-success', 'ba-cancel'], true)) {
            if ($request->get('paypal_flag') == 'ba-cancel') {
                throw new Exception('Your billing agreement cancelled');
            }

            $ba_token = $request->get('ba_token');

            try {
                $billingAgreement = OSC::helper('paypalVault/referenceTransaction')->createBillingAgreement($this->_getClient(), $ba_token);
                $ba_id = $billingAgreement->result->id;

                if ($billingAgreement->statusCode != 201) {
                    throw new Exception('Paypal response code is incorrect');
                }

                $request = new OrdersCreateRequest();
                $request->prefer('return=representation');
                $request->body = $this->_buildRequestBody($payment_info);

                $response = $this->_getClient()->execute($request);

                if ($response->statusCode != 201) {
                    throw new Exception('Paypal response code is incorrect');
                }

                $order_id = $response->result->id;

                $order_info = $this->_getClient()->execute(new OrdersGetRequest($order_id));

                if ($order_info->statusCode != 200) {
                    throw new Exception('Paypal response code is incorrect');
                }

                if ($order_info->result->purchase_units[0]->amount->value != $payment_info['total_price']) {
                    throw new Exception('Please refresh your browser and try again.');
                }

                $request = new OrdersAuthorizeRequest($order_id);
                $request->body = [
                    "payment_source" => [
                        "token" => [
                            "id" => $ba_id,
                            "type" => "BILLING_AGREEMENT"
                        ]
                    ]
                ];

                $response = $this->_getClient()->execute($request);

                if ($response->statusCode != 201) {
                    throw new Exception('Paypal response code is incorrect');
                }
            } catch (Exception $ex) {
                if (method_exists($ex, 'getData')) {
                    $message = json_decode($ex->getData(), true);
                } else {
                    $message = ['message' => $ex->getMessage()];
                }

                OSC::helper('catalog/checkout')->insertFootprint('PAYPAL', [$ex, $message, $payment_info]);

                //Notify when payment gateway return error AMOUNT MISMATCH
                if (strpos(OSC::encode($message), 'AMOUNT_MISMATCH') !== false) {
                    $content = OSC_FRONTEND_BASE_URL . ': Checkout PAYPAL error:' . OSC::encode([$ex, $message, $payment_info]);
                    OSC::helper('core/telegram')->sendMessage($content, OSC::helper('core/setting')->get('error_payment_notifications/telegram_group_id'));
                }

                throw new Exception($message['message']);
            }

            return [
                'payment_data' => [
                    'authid' => $response->result->purchase_units[0]->payments->authorizations[0]->id,
                    'order_id' => $response->result->id,
                    'ba_id' => $ba_id,
                    'ba_token' => $ba_token
                ]
            ];
        }

        if (isset($payment_info['remember_account']) && !empty($payment_info['remember_account'])) {
            $this->_createBillingAgreementTransaction($payment_info);
        } else {
            $this->_createTransaction($payment_info, true);
        }
    }

    public function referenceTransaction(array $payment_info) {
        $uid = $payment_info['uid'];
        $ba_id = $payment_info['ba_id'];
        $ba_token = $payment_info['ba_token'];

        try {
            $request = new OrdersCreateRequest();
            $request->prefer('return=representation');
            $request->body = $this->_buildRequestBody($payment_info);

            if (!empty($uid)) {
                $request->headers = array_merge($request->headers, [
                    'PAYPAL-CLIENT-METADATA-ID' => $uid
                ]);
            }

            $response = $this->_getClient()->execute($request);

            if ($response->statusCode != 201) {
                throw new Exception('Paypal response code is incorrect');
            }

            $order_id = $response->result->id;

            $order_info = $this->_getClient()->execute(new OrdersGetRequest($order_id));

            if ($order_info->statusCode != 200) {
                throw new Exception('Paypal response code is incorrect');
            }

            if ($order_info->result->purchase_units[0]->amount->value != $payment_info['total_price']) {
                throw new Exception('Please refresh your browser and try again.');
            }

            $request = new OrdersAuthorizeRequest($order_id);
            $request->body = [
                "payment_source" => [
                    "token" => [
                        "id" => $ba_id,
                        "type" => "BILLING_AGREEMENT"
                    ]
                ]
            ];

            if (!empty($uid)) {
                $request->headers = array_merge($request->headers, [
                    'PAYPAL-CLIENT-METADATA-ID' => $uid
                ]);
            }

            $response = $this->_getClient()->execute($request);

            if ($response->statusCode != 201) {
                throw new Exception('Paypal response code is incorrect');
            }
        } catch (Exception $exception) {
            if (method_exists($exception, 'getData')) {
                $message = json_decode($exception->getData(), true);
            } else {
                $message = ['message' => $exception->getMessage()];
            }

            OSC::helper('catalog/checkout')->insertFootprint('PAYPAL', [$exception, $message]);
            throw new Exception($message['message']);
        }

        return [
            'payment_data' => [
                'authid' => $response->result->purchase_units[0]->payments->authorizations[0]->id,
                'order_id' => $response->result->id,
                'ba_id' => $ba_id,
                'ba_token' => $ba_token
            ]
        ];
    }

    public function void($payment_data, float $amount, string $currency_code, int $added_timestamp) {
        $payment_info = $payment_data;

        if (empty($payment_info['payment_data'])) {
            return $payment_data;
        }

        $payment_data = $payment_info['payment_data'];

        if ($added_timestamp < (time() - (60 * 60 * 24 * 7))) {
            $payment_data['void_note'] = 'Expired transaction';
            return $payment_data;
        }

        try {
            $payment_id = $payment_data['authid'];
            $request = new AuthorizationsVoidRequest($payment_id);
            //Success return $response->statusCode 204
            $response = $this->_getClient()->execute($request);
        } catch (Exception $ex) {
            $message = json_decode($ex->getMessage(), true);
            throw new Exception($message['details'][0]['description']);
        }

        return $payment_data;
    }

    public function capture($payment_data, float $amount, string $currency_code) {
        $payment_info = $payment_data;

        if (empty($payment_info['payment_data'])) {
            return $payment_data;
        }

        $payment_data = $payment_info['payment_data'];

        try {
            $payment_id = $payment_data['authid'];
            $request = new AuthorizationsGetRequest($payment_id);
            $response = $this->_getClient()->execute($request);

            if ($response->result->status == 'CAPTURED') {
                $payment_data['captured_before_flag'] = true;
                return $payment_data;
            }

            $request = new AuthorizationsCaptureRequest($payment_id);
            $request->body = [
                'amount' => [
                    'currency_code' => $currency_code,
                    'value' => strval($amount),
                ]
            ];
            $response = $this->_getClient()->execute($request);
        } catch (Exception $ex) {
            if (method_exists($ex, 'getData')) {
                $message = json_decode($ex->getData(), true);

                if (!isset($message['message'])) {
                    $message['message'] = $ex->getMessage();
                }

                if (isset($message['information_link']) && isset($message['name'])) {
                    $message['message'] .= "\nMore information: " . $message['information_link'] . '-' . $message['name'];
                }
            } else {
                $message = ['message' => $ex->getMessage()];
            }

            throw new Exception($message['message']);
        }

        $payment_data['capture_id'] = $response->result->id;

        return $payment_data;
    }

    public function update(array $payment_info, array $payment_data) {

    }

    public function refund($payment_data, float $amount, string $currency_code, string $description, string $reason = '') {
        $payment_info = $payment_data;

        if (empty($payment_info['payment_data'])) {
            return $payment_data;
        }

        $payment_data = $payment_info['payment_data'];

        if (!isset($payment_data['refund_ids'])) {
            $payment_data['refund_ids'] = [];
        }

        try {
            $capture_id = $payment_data['capture_id'];

            $request = new CapturesRefundRequest($capture_id);
            $request->body = [
                'amount' => [
                    'currency_code' => $currency_code,
                    'value' => strval($amount),
                ]
            ];

            $response = $this->_getClient()->execute($request);
        } catch (Exception $ex) {
            if (method_exists($ex, 'getData')) {
                $message = json_decode($ex->getData(), true);
            } else {
                $message = ['message' => $ex->getMessage()];
            }

            if (in_array($message['message'], ['Refund refused. Refund was already issued for transaction.'])) {
                $payment_data['refund_ids'][] = 'ready-' . time();
            } else {
                throw new Exception($message['message']);
            }
        }

        $payment_data['refund_ids'][] = $response->result->id;

        return $payment_data;
    }

    public function compareTransaction($a, $b) {
        return is_array($a) && is_array($b) && isset($a['authid']) && isset($b['authid']) && $a['authid'] == $b['authid'];
    }
}