<?php

/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
use DVDoug\BoxPacker\InfalliblePacker;
use DVDoug\BoxPacker\Test\TestBox;
use DVDoug\BoxPacker\Test\TestItem;

class Helper_Catalog_Checkout extends OSC_Object {

    /**
     * dimension in cm, weight in gram
     */
    const BOXES = [
//        'box1' => [
//            'outer_width' => 30,
//            'outer_height' => 10,
//            'outer_length' => 30,
//            'box_weight' => 1,
//            'inner_width' => 30,
//            'inner_height' => 10,
//            'inner_length' => 30,
//            'max_weight' => 1000
//        ],
//        'box2' => [
//            'outer_width' => 30,
//            'outer_height' => 30,
//            'outer_length' => 30,
//            'box_weight' => 1,
//            'inner_width' => 30,
//            'inner_height' => 30,
//            'inner_length' => 30,
//            'max_weight' => 3000
//        ],
//        'box3' => [
//            'outer_width' => 1000,
//            'outer_height' => 500,
//            'outer_length' => 1000,
//            'box_weight' => 100,
//            'inner_width' => 1000,
//            'inner_height' => 500,
//            'inner_length' => 1000,
//            'max_weight' => 15000
//        ],
//        'box3' => [
//            'outer_width' => 1000,
//            'outer_height' => 1000,
//            'outer_length' => 1000,
//            'box_weight' => 200,
//            'inner_width' => 1000,
//            'inner_height' => 1000,
//            'inner_length' => 1000,
//            'max_weight' => 30000
//        ]
    ];

    public static function getFootprintKey() {
        $footprint_key = OSC::sessionGet('payment_footprint');

        if (!$footprint_key) {
            $footprint_key = OSC::makeUniqid();

            OSC::sessionSet('payment_footprint', $footprint_key);
        }

        return $footprint_key;
    }

    public function collectPaymentMethods(Model_Catalog_Cart $cart)
    {
        $countryCode = isset($cart->data['shipping_country_code']) ? $cart->data['shipping_country_code'] : null;

        $event_results = OSC::core('observer')->dispatchEvent(
            'catalog/collect_payment_method', [
                'cart_id' => $cart->getId(),
                'country_code' => $countryCode
            ]
        );

        $payment_methods = [];

        foreach ($event_results as $event_result) {
            if (!is_array($event_result)) {
                $event_result = [$event_result];
            }

            foreach ($event_result as $payment_method) {
                if ($payment_method instanceof Abstract_Catalog_Payment) {
                    $payment_methods[$payment_method->getKey()] = $payment_method;
                }
            }
        }

        usort($payment_methods, function ($a, $b) {
            $a_priority = $a->getPriority();
            $b_priority = $b->getPriority();

            if ($a_priority == $b_priority) {
                return 0;
            }

            return ($a_priority < $b_priority) ? 1 : -1;
        });

        return $payment_methods;
    }

    public function collectShippingCarriers(Model_Catalog_Cart $cart) {
        $store_address = OSC::helper('core/setting')->get('catalog/store/address');

        if (!$store_address) {
            throw new Exception('Cannot get store address to calculate shipping rates');
        }

        $package_items = [];

        foreach ($cart->getLineItems() as $line_item) {
            $package_items[$line_item->getId()] = [
                'quantity' => $line_item->data['quantity'],
                'require_packing' => $line_item->data['require_packing'],
                'keep_flat' => $line_item->data['keep_flat'],
                'weight' => $line_item->getWeightInGram(),
                'width' => $line_item->data['dimension_width'],
                'height' => $line_item->data['dimension_height'],
                'length' => $line_item->data['dimension_length'],
                'info' => [
                    'variant_id' => $line_item->data['variant_id'],
                    'ukey' => $line_item->data['ukey']
                ]
            ];
        }

        $carriers = OSC::core('observer')->dispatchEvent(
            'catalog/collect_shipping_carrier', [
                'cart_id' => $cart->getId(),
                'total_price' => $cart->getFloatSubtotal(),
                'packages' => $this->calculatePackages($package_items),
                'currency_code' => $cart->data['currency_code'],
                'shipping_address' => $cart->getShippingAddress(),
                'ship_from' => $store_address
            ]
        );

        foreach ($carriers as $idx => $carrier) {
            if (!($carrier instanceof Helper_Catalog_Shipping_Carrier)) {
                unset($carriers[$idx]);
            }
        }

        if (count($carriers) < 1) {
            throw new Exception('No carrier was found');
        }

        return $carriers;
    }

    public function verifyShippingMethod($shipping_method) {}

    public function calculatePackages(array $line_items) {
        $packages = [];

        $packer = new InfalliblePacker();

        foreach (static::BOXES as $box_type => $box_config) {
            $packer->addBox(new TestBox($box_type, $box_config['outer_width'], $box_config['outer_length'], $box_config['outer_height'], $box_config['box_weight'], $box_config['inner_width'], $box_config['inner_length'], $box_config['inner_height'], $box_config['max_weight']));
        }

        foreach ($line_items as $line_id => $line_item) {
            if (!$line_item['require_packing']) {
                $packages[] = [
                    'line_id' => $line_id,
                    'quantity' => $line_item['quantity'],
                    'weight' => [
                        'value' => $line_item['weight'] * $line_item['quantity'],
                        'unit' => 'g'
                    ],
                    'dimension' => [
                        'width' => $line_item['width'],
                        'height' => $line_item['height'],
                        'length' => $line_item['length'],
                        'unit' => 'cm'
                    ]
                ];

                continue;
            }

            for ($i = 0; $i < $line_item['quantity']; $i ++) {
                $packer->addItem(new TestItem($line_id, $line_item['width'], $line_item['length'], $line_item['height'], $line_item['weight'], $line_item['keep_flat']));
            }
        }

        try {
            $packed_boxes = $packer->pack();

            $unpacked_items = $packer->getUnpackedItems();

            foreach ($packed_boxes as $packed_box) {
                $box_line_ids = [];

                foreach ($packed_box->getItems() as $packed_item) {
                    if (!isset($box_line_ids[$packed_item->getItem()->getDescription()])) {
                        $box_line_ids[$packed_item->getItem()->getDescription()] = 1;
                    } else {
                        $box_line_ids[$packed_item->getItem()->getDescription()] ++;
                    }
                }

                $box_type = $packed_box->getBox();

                $packages[] = [
                    'line_id' => $box_line_ids,
                    'quantity' => 1,
                    'weight' => [
                        'value' => $packed_box->getWeight(),
                        'unit' => 'g'
                    ],
                    'dimension' => [
                        'width' => $box_type->getOuterWidth(),
                        'height' => $box_type->getOuterDepth(),
                        'length' => $box_type->getOuterLength(),
                        'unit' => 'cm'
                    ]
                ];
            }

            foreach ($unpacked_items as $unpacked_item) {
                $line_item = $line_items[$unpacked_item->getDescription()];

                $packages[] = [
                    'line_id' => $unpacked_item->getDescription(),
                    'quantity' => 1,
                    'weight' => [
                        'value' => $line_item['weight'],
                        'unit' => 'g'
                    ],
                    'dimension' => [
                        'width' => $line_item['width'],
                        'height' => $line_item['height'],
                        'length' => $line_item['length'],
                        'unit' => 'cm'
                    ]
                ];
            }
        } catch (Exception $ex) {}

        return $packages;
    }

    public function insertFootprint($log_key, $exception) {
        static $uniq_key = null;
        
        if(! $uniq_key) {
            $uniq_key = OSC::makeUniqid();
        }
        
        $cart = OSC::helper('catalog/common')->getCart(false);

        if (!$cart) {
            return;
        }

        $request_data = $_REQUEST;
        if (isset($_REQUEST['card']['number']) && $log_key === 'PAYMENT') {
            $number = str_replace(' ', '', $_REQUEST['card']['number']);
            if (strlen($number) > 4) {
                $request_data['card']['number'] = str_repeat("*", strlen($number) - 4) . substr($number, -4);
            }

            $exception = preg_replace_callback('~&ACCT=(\d{4})*~', function($m) {
                if (strlen($m[0]) > 10) {
                    return substr($m[0], 0, 6) . str_repeat('*', strlen($m[0]) - 10) . $m[1];
                } else {
                    return $m[0];
                }
            }, $exception);
        }

        try {
            OSC::core('database')->insert(
                'catalog_checkout_footprint', [
                'cart_id' => $cart->getId(),
                'log_key' => $uniq_key . ':' . $log_key,
                'log_info' => OSC::encode([
                    'payment_account' => OSC::helper('multiPaymentAccount/common')->getAccountProvided(),
                    'server_info' => $_SERVER,
                    'request_data' => $request_data,
                    'exception' => (array)$exception,
                    'database' => OSC::core('database')->getWriteAdapter()->getQueryLog()
                ]),
                'added_timestamp' => time()
            ], 'insert_checkout_footprint');
        } catch (Exception $ex) {}
        
        return $uniq_key;
    }

    /**
     * This function will return a temporary payment method to build default credit card form
     * @param array $payment_methods
     * @return array|bool
     */
    public function collectPaymentBuildForm(array $payment_methods) {
        if (empty($payment_methods)) {
            return false;
        }

        foreach ($payment_methods as $key => $val) {
            if (in_array($val->getKey(), ['stripe', 'paypalPro'])) {
                unset($payment_methods[$key]);
            }
        }

        array_unshift($payment_methods, OSC::helper('creditCard/payment'));

        // Add Apple Pay to payment methods
        $payment_methods[] = OSC::helper('applePay/payment');

        return $payment_methods;
    }

    /**
     * This function will return an appropriate payment method for credit card usage by user in a country has texes
     * @param Model_Catalog_Cart $cart
     * @return string
     */
    public function setCreditCardPaymentMethod(Model_Catalog_Cart $cart, $ccNumber) {
        /** $countryHasTax = OSC::helper('multiPaymentAccount/common')->getCountryHasTax();
         * Not use client Country Code
         * Use Shipping country Code
         * $clientCountryCode = isset($cart->data['client_info']['location']) && is_array($cart->data['client_info']['location']) ? $cart->data['client_info']['location']['country_code'] : null;
         * $shippingCountryCode = isset($cart->data['shipping_country_code']) ? $cart->data['shipping_country_code'] : null;
         * */

        $countryCode = isset($cart->data['shipping_country_code']) ? $cart->data['shipping_country_code'] : null;

        $paypalProAcc = OSC::helper('multiPaymentAccount/common')->getAccount('paypalPro', $countryCode);

        $CardTypeValidator = Helper_Core_CreditCardValidator::validCreditCard($ccNumber, [Helper_Core_CreditCardValidator::TYPE_VISA, Helper_Core_CreditCardValidator::TYPE_MASTERCARD]);

        if ($CardTypeValidator['valid'] && $paypalProAcc) {
            return 'paypalPro';
        } else {
            return 'stripe';
        }
    }

    public function getMessageNotAvailableToShip() {
        return 'We are sorry, some item(s) in your cart are not available to ship to your provided address. Please remove them from the cart before going to check out.';
    }

    public function getMessageDesignUpdated() {
        return '<span class="error-design-updated">We are sorry! The design of this item has been updated during your personalization process. Please remove the item from the cart and personalize it again to make sure you\'ll have the product with up-to-date design</span>';
    }

    public function getCheckoutMessage() {
        $session_key = 'osc_template_message';

        $message_arr = OSC::sessionGet($session_key);

        if (!is_array($message_arr)) {
            return '';
        }

        $messages = array();
        foreach ($message_arr as $type => $_messages) {
            if (!is_array($_messages) || count($_messages) < 1) {
                continue;
            }

            $_messages = array_unique($_messages);

            foreach ($_messages as $__message) {
                $messages[] = OSC::controller()->getTemplate()->build(
                    "/checkout/summary/" . $type,
                    [
                        "messages" => $__message ,
                        "title" => "Error notification"
                    ]
                );
            }
        }

        $messages = OSC::controller()->getTemplate()->build('core/message', array('messages' => $messages));

        OSC::sessionSet($session_key, null);

        return $messages;
    }

    public function createPaymentIntentOfCart(Model_Catalog_Cart $cart) {
        try {
            $amount = $cart->getTotal(true, false, true, false);

            if (!$amount) {
                throw new Exception('Amount must be greater than 0');
            }

            $invoice_number = OSC::helper('core/setting')->get('theme/site_name') .
                ' :: CART-' . $cart->getUkey() . '/' . time();
            $currency = OSC::helper('stripe/paymentIntent')->getDefaultCurrency();
            $payment_methods = OSC::helper('catalog/checkout')->collectPaymentMethods($cart);

            $payment = null;
            foreach ($payment_methods as $method) {
                if ($method->getKey() === 'stripe') {
                    $payment = $method;
                    break;
                }
            }

            $payment_account = $payment->getAccount();
            $secret_key = $payment->getSecretKey(false);
            $public_key = !empty($payment) ? $payment->getPublicKey() : '';
            $payment_account_id = $payment_account['id'];

            // create payment intent
            $payment_intent = OSC::helper('stripe/paymentIntent')->create(
                $secret_key,
                [
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'invoice_number' => $invoice_number
                ]
            );
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return [
            'public_key' => $public_key,
            'payment_account_id' => $payment_account_id,
            'payment_intent' => $payment_intent,
        ];
    }

    public function updatePaymentIntentOfCart(Model_Catalog_Cart $cart, $paymentIntentData) {
        try {
            if (empty($paymentIntentData['payment_account_id']) || empty($paymentIntentData['payment_intent_id'])) {
                throw new Exception('payment_account_id or payment_intent_id is not found');
            }

            $payment_account_id = $paymentIntentData['payment_account_id'];
            $payment_intent_id = $paymentIntentData['payment_intent_id'];

            $amount = $cart->getTotal(true, false, true, false);

            if (!$amount) {
                throw new Exception('Amount must be greater than 0');
            }

            $invoice_number = OSC::helper('core/setting')->get('theme/site_name') . ' :: CART-' . $cart->getUkey() . '/' . time();

            // get payment account
            $payment_account = OSC::model('multiPaymentAccount/account')->load($payment_account_id);

            $account_info = $payment_account->getData('account_info');
            $secret_key = $account_info['secret_key'];
            $public_key = $account_info['public_key'];
            $payment_account_id = $payment_account->getId();

            // get payment intent
            $payment_intent = OSC::helper('stripe/paymentIntent')->retrieve($secret_key, $payment_intent_id);

            // update payment_intent
            $payment_intent = OSC::helper('stripe/paymentIntent')->update(
                $secret_key,
                $payment_intent_id,
                [
                    'amount' => $amount,
                    'description' => $invoice_number
                ]
            );
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return [
            'public_key' => $public_key,
            'payment_account_id' => $payment_account_id,
            'payment_intent' => $payment_intent,
        ];
    }

    public function getPlaceOrderCart() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);
            if (empty($cart) || !$cart instanceof Model_Catalog_Cart || $cart->getId() < 1) {
                throw new Exception('Not have cart.');
            }

            if ($cart->getLineItems()->length() < 1) {
                throw new Exception('Not have item cart to checkout.');
            }

            // check unavailable cart items
            if (count($cart->getUnavailableCartItems())) {
                throw new Exception($this->getMessageNotAvailableToShip());
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $cart;
    }

    public function updateCart(Model_Catalog_Cart $cart, &$updated_fields = [], $request_data = []) {
        $address_fields = Helper_Core_Country::ADDRESS_FIELDS;

        $email = trim($request_data['email'] ?? null);
        $shipping_address_id = intval($request_data['shipping_address_id'] ?? 0);
        $shipping_address_fields = $request_data['shipping_address'] ?? null;
        $billing_option_input = trim($request_data['billing_option_input'] ?? null);
        $billing_address_id = intval($request_data['billing_address_id'] ?? null);
        $billing_address_fields = $request_data['billing_address'] ?? null;

        // check email
        if ($cart->data['email'] !== $email) {
            $updated_fields['email'] = $email;
        }

        // check shipping address id
        if ($shipping_address_id && $shipping_address_id != $cart->data['shipping_address_id']) {
            $updated_fields['shipping_address_id'] = $shipping_address_id;
        }

        // check billing address id
        if ($billing_address_id && $billing_address_id != $cart->data['billing_address_id']) {
            $updated_fields['billing_address_id'] = $billing_address_id;
        }

        // check shipping address and billing address
        foreach ($address_fields as $address_field_key => $address_field_name) {
            // check shipping address
            if (isset($shipping_address_fields[$address_field_key]) &&
                $shipping_address_fields[$address_field_key] !== $cart->data["shipping_{$address_field_key}"]) {
                $updated_fields["shipping_{$address_field_key}"] = $shipping_address_fields[$address_field_key];
            }

            // check billing address
            if ($billing_option_input === 'same') {
                if (isset($shipping_address_fields[$address_field_key]) &&
                    $shipping_address_fields[$address_field_key] != $cart->data["billing_{$address_field_key}"]) {
                    $updated_fields["billing_{$address_field_key}"] = $shipping_address_fields[$address_field_key];
                }
            } else {
                if (empty($billing_address_fields)) {
                    continue;
                }

                if (isset($billing_address_fields[$address_field_key]) &&
                    $billing_address_fields[$address_field_key] != $cart->data["billing_{$address_field_key}"]) {
                    $updated_fields["billing_{$address_field_key}"] = $billing_address_fields[$address_field_key];
                }
            }
        }

        if (!empty($updated_fields)) {
            $cart->setData($updated_fields)->save();
        }
    }

    public function calculateShippingMethod(array $request_data, Model_Catalog_Cart $cart) {
        $shipping_method_key = trim($request_data['shipping_method'] ?? null);

        if ($shipping_method_key) {
            OSC::helper('catalog/react_checkout')->calculatorShippingMethod($cart, true, $shipping_method_key);
        }
    }

    public function validateFormAndUpdateCart($request_data = []) {
        $updated_fields = [];

        try {
            // get cart
            $cart = $this->getPlaceOrderCart();

            // validate checkout form
            OSC::helper('catalog/validation_checkout')->validateCheckoutForm($request_data, $cart, $updated_fields);

            // update cart
            $this->updateCart($cart, $updated_fields, $request_data);

            $this->calculateShippingMethod($request_data, $cart);

            OSC::helper('catalog/react_checkout')->calculatorTax($cart);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
