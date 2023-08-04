<?php

class Helper_Catalog_Order extends OSC_Object {

    /**
     *
     * @param int $cart_id
     * @param int $cart_ukey
     * @param string $email
     * @param string $currency_code
     * @param array $shipping_address
     * @param array $billing_address
     * @param array $line_items
     * @param Helper_Catalog_Shipping_Carrier_Rate $shipping_rate
     * @param array $taxes
     * @param array $discount_codes
     * @param int $refunded
     * @param int $paid
     * @param Abstract_Catalog_Payment $payment_method
     * @param string $order_status
     * @param string $note
     * @param array $custom_price_data
     * @param array $client_info
     * @param $sref_id
     * @param $ab_test
     * @param $client_referer
     * @param $client_country
     * @param $client_device_type
     * @param $client_browser
     * @param bool $notify
     * @param mixed $callback_before_commit
     * @param array $additional_data
     * @param int $sms_campaign_id
     * @return Model_Catalog_Order
     * @throws Exception
     */
    public function place(
        $cart_id,
        $cart_ukey,
        string $email,
        string $currency_code,
        array $shipping_address,
        array $billing_address,
        array $line_items,
        Helper_Catalog_Shipping_Carrier_Rate $shipping_rate,
        array $taxes,
        array $discount_codes,
        int $refunded,
        int $paid,
        Abstract_Catalog_Payment $payment_method,
        string $order_status = 'archived',
        array $custom_price_data,
        array $client_info,
        $sref_id = null,
        $ab_test = null,
        $client_referer = null,
        $client_country = null,
        $client_device_type = null,
        $client_browser = null,
        bool $notify = false,
        $callback_before_commit = null,
        array $additional_data,
        int $sms_campaign_id = 0,
        array $extra_data = [],
        array $shipping_rates = []
    ): Model_Catalog_Order {
        $payment_account = $payment_method->getAccount();
        if (empty($payment_account)) {
            throw new Exception('Missing payment account');
        }

        $DB = OSC::core('database')->getAdapter('db_master');

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            /* @var $order Model_Catalog_Order */

            $order = OSC::model('catalog/order')->register('SKIP_UPDATE_INDEX', 1);

            $sref_id = intval($sref_id);

            if ($sref_id < 1) {
                $sref_id = null;
            }

            $customer_id = OSC::helper('account/customer')->getCustomerId();

            $order_data = [
                'cart_id' => $cart_id,
                'cart_ukey' => $cart_ukey,
                'member_id' => OSC::helper('user/authentication')->getMember()->getId(),
                'crm_customer_id' => $customer_id,
                'email' => $email,
                'currency_code' => $currency_code,
                'order_status' => $order_status,
                'discount_codes' => [],
                'taxes' => $taxes['taxes'],
                'payment_method' => [
                    'key' => $payment_method->getKey(),
                    'title' => $payment_method->getTitle(),
                    'object' => $payment_method->getOSCObjectType(),
                    'account' => $payment_account,
                    'remember_account' => $extra_data['remember_account'] ?? 0,
                    'funding_source' => $extra_data['funding_source'] ?? '',
                    'payment_intent' => intval(!empty($extra_data['payment_intent_id']))
                ],
                'is_upsale' => isset($extra_data['remember_account']) && !empty($extra_data['remember_account']) ? 1 : 0,
                'payment_data' => null,
                'client_info' => $client_info,
                'sref_id' => $sref_id,
                'ab_test' => $ab_test,
                'client_referer' => $client_referer,
                'client_country' => $client_country,
                'client_device_type' => $client_device_type,
                'client_browser' => $client_browser,
                'subtotal_price' => 0,
                'total_price' => $this->getTotal(),
                'paid' => $paid,
                'refunded' => $refunded,
                'custom_price_data' => $custom_price_data,
                'additional_data' => $additional_data,
                'sms_campaign_id' => $sms_campaign_id
            ];

            // add payment_intent_id to payment_data if $extra_data['payment_intent_id'] is existed
            if (!empty($extra_data['payment_intent_id'])) {
                $order_data['payment_data'] = [
                    'payment_intent_id' => $extra_data['payment_intent_id']
                ];
            }

            // add extra data payment method if it is existed
            if (!empty($extra_data['payment_method'])) {
                $order_data['payment_method']['payment_method'] = $extra_data['payment_method'];
            }

            if (isset($order_id) && $order_id > 0) {
                $order_data['order_id'] = $order_id;
            }

            foreach (['shipping_' => $shipping_address, 'billing_' => $billing_address] as $prefix => $address) {
                if (!isset($address['country_code']) || !$address['country_code']) {
                    continue;
                }

                foreach ($address as $key => $value) {
                    $order_data[$prefix . $key] = $value;
                }
            }

            $order_total_items = 0;
            $order_fulfilled_items = 0;
            $order_refunded_items = 0;

            $package_items = [];

            $cartCustomPriceData = is_array($custom_price_data) && !empty($custom_price_data) ? $custom_price_data : [];

            $addon_service_price = 0;

            /* @var $line_item Model_Catalog_Order_Item */
            foreach ($line_items as $idx => $line_item) {
                $line_item = OSC::model('catalog/order_item')->setData($line_item);

                $line_item->setOrder($order);

                $order_total_items += intval($line_item->data['quantity']);
                $order_fulfilled_items += intval($line_item->data['fulfilled_quantity']);
                $order_refunded_items += intval($line_item->data['refunded_quantity']);

                $line_items[$idx] = $line_item;

                $order_data['subtotal_price'] += $line_item->getAmountWithDiscount();

                $package_items[$line_item->getId()] = [
                    'quantity' => $line_item->data['quantity'],
                    'require_packing' => $line_item->data['require_packing'],
                    'keep_flat' => $line_item->data['keep_flat'],
                    'weight' => $line_item->getWeightInGram(),
                    'width' => $line_item->data['dimension_width'],
                    'height' => $line_item->data['dimension_height'],
                    'length' => $line_item->data['dimension_length'],
                    'info' => [
                        'variant_id' => $line_item->getId(),
                        'ukey' => $line_item->data['ukey']
                    ]
                ];

                // Calculate addon_service_price by line_item and quantity
                $addon_service_price += $line_item->getAddonServicePrice();
            }

            $order_data['fulfillment_status'] = $order_fulfilled_items < 1 ? 'unfulfilled' : (($order_fulfilled_items + $order_refunded_items == $order_total_items) ? 'fulfilled' : 'partially_fulfilled');

            $order_data['shipping_line'] = [
                'carrier' => $shipping_rate->getCarrier()->toArray(), 
                'packages' => OSC::helper('catalog/checkout')->calculatePackages($package_items),
                'shipping_rates' => $shipping_rates
            ];

            $order->setData($order_data);

            //Shipping price
            $order_total_price = $order_data['subtotal_price'] + $shipping_rate->getAmount();

            // Custom price
            $custom_price = 0;

            //Check apply discount code
            $order->setData('discount_codes', $discount_codes);

            foreach ($discount_codes as $discount_code) {
                if (!in_array($discount_code['apply_type'], ['entire_order', 'shipping', 'entire_order_include_shipping'], true)) {
                    continue;
                }

                $order_total_price -= ($discount_code['discount_price'] + $discount_code['discount_shipping_price']);
            }

            //Calculate buy_design_price
            if (isset($cartCustomPriceData['buy_design']) && !empty($cartCustomPriceData['buy_design']) && is_array($cartCustomPriceData['buy_design'])) {
                foreach ($cartCustomPriceData['buy_design'] as $item) {
                    $custom_price += isset($item['buy_design_price']) && !empty($item['buy_design_price']) ? intval($item['buy_design_price']) : 0;
                }
            }

            //Calculate buy_design_price
            if (isset($cartCustomPriceData['tip']) && $cartCustomPriceData['tip'] >= 0) {
                $order->setData('tip_price', intval($cartCustomPriceData['tip']));
                $order_total_price += intval($cartCustomPriceData['tip']);
                unset($cartCustomPriceData['tip']);
            }

            $order_total_price += !empty($custom_price) ? $custom_price : 0;

            //Tax price
            $tax_price = $taxes['tax_price'];
            $order_total_price = $order_total_price + $tax_price;

            $order->setData('custom_price_data', $cartCustomPriceData);
            $order->setData('custom_price', intval($custom_price));
            $order->setData('tax_price', $tax_price > 0 ? intval($tax_price) : 0);
            $order->setData('total_price', $order_total_price > 0 ? intval($order_total_price) : 0);

            // compare order price to stripe
            if ($order_total_price &&
                !empty($payment_account['type']) &&
                !empty($payment_account['account_info']['secret_key']) &&
                $payment_account['type'] === 'stripe' &&
                !empty($extra_data['payment_intent_id'])) {
                $payment_intent = OSC::helper('stripe/paymentIntent')->retrieve(
                    $payment_account['account_info']['secret_key'],
                    $extra_data['payment_intent_id']
                );

                if ($order_total_price != $payment_intent->amount_capturable ||
                    $payment_intent->status !== 'requires_capture') {
                    throw new Exception('Please refresh your browser and try again.');
                }
            }

            /* @var $cart Model_Catalog_Cart */
            $cart = OSC::helper('catalog/common')->getCart(false, false);
            $order->setData('shipping_price', $cart->getShippingPrice() - $cart->getShippingDiscountPrice());

            if ($order->data['paid'] == 0) {
                $order->setData('payment_status', 'pending');
            } else {
                if ($order->data['refunded'] > 0) {
                    $order->setData('payment_status', $order->data['paid'] > $order->data['refunded'] ? 'partially_refunded' : 'refunded');
                } else {
                    $order->setData('payment_status', $order->data['paid'] < $order->data['total_price'] ? 'partially_paid' : 'paid');
                }
            }

            $count_error = 0;

            while ($count_error < 4) {
                try {
                    $order->save();
                    break;
                } catch (Exception $ex) {
                    //Notify when deadlock sql
                    if (strpos($ex->getMessage(), 'Deadlock') !== false) {
                        $count_error++;
                        OSC::helper('core/telegram')->send(OSC::$base_url . '::place::260::deadlock::' . time() . '::' . $count_error . '::' . OSC::encode($order->data));
                    } else {
                        throw new Exception($ex->getMessage());
                    }
                }
            }

            OSC::core('observer')->dispatchEvent('catalog/orderVerifyLineItemToCreate', ['order' => $order, 'line_items' => $line_items]);

            foreach ($line_items as $line_item) {
                $line_item->setData([
                    'order_master_record_id' => $order->getId(),
                    'order_id' => $order->data['order_id']
                ])->save();
            }

            // Update custom_price_data for addon_service order
            if (isset($cartCustomPriceData['addon_services'])) {
                $order_custom_price_data = $order->data['custom_price_data'];
                unset($order_custom_price_data['addon_services']);

                $line_items = $order->getLineItems();
                $addon_services = [];
                foreach ($line_items as $item) {
                    if (isset($item->data['custom_price_data']['addon_services'])) {
                        $addon_services[$item->data['ukey']] = $item->data['custom_price_data']['addon_services'];
                    }
                }

                if (!empty($addon_services)) {
                    $order_custom_price_data['addon_services'] = $addon_services;
                    $order->setData(['custom_price_data' => $order_custom_price_data])->save();
                }
            }


            if (is_callable($callback_before_commit)) {
                call_user_func($callback_before_commit, $order);
            }

            if (!OSC::registry(Helper_Catalog_Payment::$_payment_order_id)) {
                /* @var $transaction Model_Catalog_Order_Transaction */
                $transaction = OSC::model('catalog/order_transaction');

                $transaction->setData([
                    'transaction_type' => $order->data['payment_status'] == 'authorized' ? Model_Catalog_Order_Transaction::TRANSACTION_TYPE_AUTHORIZE : Model_Catalog_Order_Transaction::TRANSACTION_TYPE_PAYMENT,
                    'order_id' => $order->data['order_id'],
                    'order_master_record_id' => $order->getId(),
                    'member_id' => OSC::helper('user/authentication')->getMember()->getId(),
                    'amount' => isset($order->data['total_price']) && !empty($order->data['total_price']) ? intval($order->data['total_price']) : 0
                ])->save();

                $transaction->setOrder($order);

                $subscribe_newsletter = OSC::core('request')->get('subscribe_newsletter') == true ? 1 : 0;

                foreach (['store_after_order_created', 'master_after_order_created'] as $action) {
                    $queue_data = [];
                    if ($action === 'master_after_order_created') {
                        $upsale_line_items = [];
                        foreach ($order->getLineItems() as $line_item) {
                            if ($line_item->isUpsaleItem()) {
                                $upsale_line_items[] = $line_item;
                            }
                        }
                        $queue_data = [
                            'order_id' => $order->getId(),
                            'upsale_line_items' => $upsale_line_items,
                            'subscribe_newsletter' => $subscribe_newsletter
                        ];
                    } elseif ($action === 'store_after_order_created') {
                        $customer_address = OSC::helper('account/address')->getList();
                        $queue_data = [
                            'transaction_id' => $transaction->getId(),
                            'billing_option_input' => OSC::core('request')->get('billing_option_input'),
                            'flag_add_new_address' => count($customer_address) < 1,
                            'subscribe_newsletter' => $subscribe_newsletter
                        ];
                    }

                    OSC::model('catalog/order_bulkQueue')->setData([
                        'member_id' => 1,
                        'shop_id' => OSC::getShop()->getId(),
                        'action' => $action,
                        'order_master_record_id' => $order->getId(),
                        'queue_flag' => 1,
                        'queue_data' => $queue_data
                    ])->save();
                }

                OSC::core('observer')->dispatchEvent('catalog/orderCreate', $order);

                foreach ($discount_codes as $discount_code) {
                    OSC::helper('catalog/discountCode')->addToUsed($discount_code['discount_code'], $order);
                }

                $order->addLog('PLACE', $order->getFullName() . ' placed this order.');

                OSC::helper('multiPaymentAccount/common')->cleanAccountCache();

                $DB->commit();
            }
        } catch (Exception $ex) {
            $DB->rollback();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            throw new Exception($ex->getMessage(),$ex->getCode());
        }

        OSC_Database_Model::unlockPreLoadedModel($locked_key);

        return $order;
    }

    public function collectCarrierTrackingNumberPatterns() {
        $carriers = [];

        $event_results = OSC::core('observer')->dispatchEvent('catalog/collect_carrier_tracking_number_pattern');

        foreach ($event_results as $event_result) {
            if (!is_array($event_result) || count($event_result) < 1) {
                continue;
            }

            foreach ($event_result as $key => $carrier) {
                if (!isset($carriers[$key])) {
                    if (!isset($carrier['title']) || !isset($carrier['tracking_url'])) {
                        continue;
                    }

                    $carriers[$key] = [
                        'title' => $carrier['title'],
                        'tracking_url' => $carrier['tracking_url'],
                        'pattern' => []
                    ];
                } else {
                    if (isset($carrier['title'])) {
                        $carriers[$key]['title'] = $carrier['title'];
                    }

                    if (isset($carrier['tracking_url'])) {
                        $carriers[$key]['tracking_url'] = $carrier['tracking_url'];
                    }
                }

                if (!isset($carrier['pattern']) || !$carrier['pattern']) {
                    continue;
                }

                if (!is_array($carrier['pattern'])) {
                    $carrier['pattern'] = [$carrier['pattern']];
                }

                $carriers[$key]['pattern'] = array_values($carrier['pattern']);
            }
        }

        return $carriers;
    }

    public function lineItemsGetByRefundable(Model_Catalog_Order $order): array {
        return $this->lineItemsGetByFulfillable($order);
    }

    public function lineItemsGetByFulfillable(Model_Catalog_Order $order): array {
        $groups = $this->lineItemsGetByGroup($order);

        if (!isset($groups['unfulfilled'])) {
            return [];
        }

        $line_items = $groups['unfulfilled']['line_items'];

        return $line_items;
    }

    public function lineItemsGetByProcess(Model_Catalog_Order $order): array {
        $groups = $this->lineItemsGetByGroup($order);

        if (!isset($groups['process'])) {
            return [];
        }

        $line_items = $groups['process']['line_items'];

        return $line_items;
    }

    public function lineItemsGetByProcessV2(Model_Catalog_Order $order,$process_ukey): array {
        $groups = $this->lineItemsGetByGroup($order);

        try{
            $model = OSC::model('catalog/order_processV2')->loadByUKey($process_ukey);

            if (!isset($groups['process/'.$model->getId()])) {
                return [];
            }

            $line_items = $groups['process/'.$model->getId()]['line_items'];

            return $line_items;
        }catch (Exception $ex){
            return [];
        }
    }

    public function lineItemsGetByGroup(Model_Catalog_Order $order): array {
        $groups = [
            'unfulfilled' => [
                'quantity' => 0,
                'timestamp' => $order->data['added_timestamp'],
                'line_items' => []
            ],
            'refunded' => [
                'quantity' => 0,
                'timestamp' => $order->data['added_timestamp'],
                'line_items' => []
            ],
            'process' => [
                'quantity' => 0,
                'timestamp' => $order->data['added_timestamp'],
                'line_items' => []
            ]
        ];

        $process_flag = false;
        $total_process_qty = 0;
        $map_item_recode = [];
        foreach ($order->getLineItems()->preLoadVariant() as $line_item) {
            $map_item_recode[$line_item->data['item_id']] = $line_item->getId();
            $quantity = intval($line_item->data['quantity']) - intval($line_item->data['refunded_quantity']) - intval($line_item->data['fulfilled_quantity']) - intval($line_item->data['process_quantity']);

            if ($line_item->data['refunded_quantity'] > 0) {
                $groups['refunded']['quantity'] += intval($line_item->data['refunded_quantity']);
                $groups['refunded']['line_items'][$line_item->data['item_id']] = ['quantity' => intval($line_item->data['refunded_quantity']), 'model' => $line_item];
            }

            if ($line_item->data['process_quantity'] > 0) {
                $process_flag = true;
                $total_process_qty += intval($line_item->data['process_quantity']);
            }

            if ($quantity > 0) {
                $groups['unfulfilled']['quantity'] += $quantity;
                $groups['unfulfilled']['line_items'][$line_item->data['item_id']] = ['quantity' => $quantity, 'model' => $line_item];
            }
        }

        if ($groups['unfulfilled']['quantity'] < 1) {
            unset($groups['unfulfilled']);
        }

        if ($groups['refunded']['quantity'] < 1) {
            unset($groups['refunded']);
        }

        if ($process_flag){
            $total_process_v2_qty = 0;
            $process_collection_v2 = $order->getProcessCollectionV2();

            if ($process_collection_v2->length() > 0){
                foreach ($process_collection_v2 as $process_v2) {
                    $_line_items = [];

                    foreach ($process_v2->data['line_items'] as $line_item_id => $quantity) {
                        $_line_items[$line_item_id] = [
                            'quantity' => $quantity['process_quantity'],
                            'model' => $order->getLineItems()->getItemByKey($map_item_recode[$line_item_id])
                        ];
                    }

                    $total_process_v2_qty +=intval($process_v2->data['quantity']);

                    $groups['process/' . $process_v2->getId()] = [
                        'process' => $process_v2,
                        'quantity' => intval($process_v2->data['quantity']),
                        'timestamp' => $process_v2->data['added_timestamp'],
                        'line_items' => $_line_items,
                        'service' => $process_v2->data['service']
                    ];
                }
            }

            if ($total_process_v2_qty < $total_process_qty){
                $process_recently = $order->getProcessRecently();

                $lineItemsProcess = $process_recently->data['line_items'];

                foreach ($lineItemsProcess as $line_item_id =>  $line_item){
                    $groups['process']['quantity'] += intval($line_item['quantity']);
                    $groups['process']['line_items'][$line_item_id] = ['quantity' => intval($line_item['quantity']), 'model' => $order->getLineItems()->getItemByKey($map_item_recode[$line_item_id])];
                }
            }

        }

        if ($groups['process']['quantity'] < 1) {
            unset($groups['process']);
        } else {
            $process_recently = $order->getProcessRecently();
            $groups['process']['service'] = $process_recently->data['service'];
        }

        $fulfillment_collection = $order->getFulfillmentCollection();

        foreach ($fulfillment_collection as $fulfillment) {
            $line_items = [];

            foreach ($fulfillment->data['line_items'] as $line_item_id => $quantity) {
                $line_items[$line_item_id] = [
                    'quantity' => $quantity['fulfill_quantity'],
                    'model' => $order->getLineItems()->getItemByKey($map_item_recode[$line_item_id])
                ];
            }

            $groups['fulfilled/' . $fulfillment->getId()] = [
                'fulfillment' => $fulfillment,
                'quantity' => intval($fulfillment->data['quantity']),
                'timestamp' => $fulfillment->data['added_timestamp'],
                'line_items' => $line_items,
                'service' => $fulfillment->data['service']
            ];
        }

        return $groups;
    }

    /**
     *
     * @param int $order_id
     * @param array $line_items
     * @return \Model_Catalog_Order_Process
     * @throws Exception
     */
    public function process(int $order_id, array $line_items, $service): Model_Catalog_Order_Process {
        if ($order_id < 1) {
            throw new Exception('Order ID is empty');
        }

        if (count($line_items) < 1) {
            throw new Exception('Process items is empty');
        }

        if (!isset($service)) {
            $service = null;
        }

        $DB = OSC::core('database')->getAdapter('db_master');

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            /* @var $order Model_Catalog_Order */
            $order = OSC::model('catalog/order')->load($order_id);

            $fulfillable_line_items = $this->lineItemsGetByFulfillable($order);

            if (count($fulfillable_line_items) < 1) {
                throw new Exception('The order is already fulfilled');
            }

            $errors = [];

            $process_line_items = [];

            foreach ($line_items as $line_item_id => $quantity) {
                $quantity = intval($quantity);

                if ($quantity < 1) {
                    $errors[] = 'Process quantity for line item ID #' . $line_item_id . ' is less than 1';
                } else if (!isset($fulfillable_line_items[$line_item_id])) {
                    $errors[] = 'Line item ID #' . $line_item_id . ' is not able to process';
                } else if ($quantity > $fulfillable_line_items[$line_item_id]['quantity']) {
                    $errors[] = 'Line item ID #' . $line_item_id . ' is not able to process more than ' . $fulfillable_line_items[$line_item_id]['quantity'] . ' item(s)';
                } else {
                    $line_items[$line_item_id] = [
                        'model' => $fulfillable_line_items[$line_item_id]['model'],
                        'quantity' => $quantity
                    ];

                    $process_line_items[$line_item_id] = [
                        'quantity' => $quantity
                    ];
                }
            }

            if (count($errors) > 0) {
                throw new Exception(implode("\n", $errors));
            }

            /* @var $process Model_Catalog_Order_Process */
            $process = OSC::model('catalog/order_process');

            $process->setData([
                'order_id' => $order->getId(),
                'member_id' => OSC::helper('user/authentication')->getMember()->getId(),
                'line_items' => $process_line_items,
                'service' => $service
            ])->save();

            $process->setOrder($order);

            /* @var $line_item['model'] Model_Catalog_Order_Item */

            foreach ($line_items as $line_item) {
                $line_item['model']->incrementProcessQuantity($line_item['quantity']);
            }

            $process_status = 'process';

            foreach ($order->getLineItems() as $line_item) {
                if ($line_item->getFulfillableQuantity() > 0) {
                    $process_status = 'partially_process';
                    break;
                }
            }

            $order->setData('process_status', $process_status)->save();

            $order->addLog('PROCESS', ($process->data['member_id'] > 0 ? (ucfirst($process->getMember()->data['username']) . ' process ') : 'Process ') . $process->data['quantity'] . ' items');

            OSC::core('observer')->dispatchEvent('catalog/orderProcess', $process);

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            throw new Exception($ex->getMessage());
        }

        OSC_Database_Model::unlockPreLoadedModel($locked_key);

        return $process;
    }

//    public function processV2(int $order_id, array $line_items , $service , $ukey, $user_name): Model_Catalog_Order_ProcessV2 {
//        if ($order_id < 1) {
//            throw new Exception('Order ID is empty');
//        }
//
//        if (count($line_items) < 1) {
//            throw new Exception('Process items is empty');
//        }
//
//        if (!isset($service)) {
//            $service = null;
//        }
//
//        $DB = OSC::core('database')->getWriteAdapter();
//
//        $DB->begin(2);
//
//        $locked_key = OSC::makeUniqid();
//
//        OSC_Database_Model::lockPreLoadedModel($locked_key);
//
//        try {
//            /* @var $order Model_Catalog_Order */
//            $order = OSC::model('catalog/order')->load($order_id);
//
//            $fulfillable_line_items = $this->lineItemsGetByFulfillable($order);
//
//            if (count($fulfillable_line_items) < 1) {
//                throw new Exception('The order is already fulfilled');
//            }
//
//            $errors = [];
//
//            $process_line_items = [];
//
//            foreach ($line_items as $line_item_id => $quantity) {
//                $quantity = intval($quantity);
//
//                if ($quantity < 1) {
//                    $errors[] = 'Process quantity for line item ID #' . $line_item_id . ' is less than 1';
//                } else if (!isset($fulfillable_line_items[$line_item_id])) {
//                    $errors[] = 'Line item ID #' . $line_item_id . ' is not able to process';
//                } else if ($quantity > $fulfillable_line_items[$line_item_id]['quantity']) {
//                    $errors[] = 'Line item ID #' . $line_item_id . ' is not able to process more than ' . $fulfillable_line_items[$line_item_id]['quantity'] . ' item(s)';
//                } else {
//                    $line_items[$line_item_id] = [
//                        'model' => $fulfillable_line_items[$line_item_id]['model'],
//                        'quantity' => $quantity
//                    ];
//
//                    $process_line_items[$line_item_id] = [
//                        'before_quantity' => $fulfillable_line_items[$line_item_id]['quantity'],
//                        'process_quantity' => $quantity
//                    ];
//                }
//            }
//
//            if (count($errors) > 0) {
//                throw new Exception(implode("\n", $errors));
//            }
//
//            /* @var $process Model_Catalog_Order_ProcessV2 */
//            $process = OSC::model('catalog/order_processV2');
//
//            $process->setData([
//                'ukey' => $ukey,
//                'order_id' => $order->getId(),
//                'member_id' => OSC::helper('user/authentication')->getMember()->getId(),
//                'line_items' => $process_line_items,
//                'service' => $service,
//                'queue_flag' => 0,
//            ])->save();
//
//            $process->setOrder($order);
//
//            /* @var $line_item['model'] Model_Catalog_Order_Item */
//
//            foreach ($line_items as $line_item) {
//                $line_item['model']->incrementProcessQuantity($line_item['quantity']);
//            }
//
//
//            $process_status = 'process';
//
//            foreach ($order->getLineItems() as $line_item) {
//                if ($line_item->getFulfillableQuantity() > 0) {
//                    $process_status = 'partially_process';
//                    break;
//                }
//            }
//
//            $order->setData('process_status', $process_status)->save();
//
//            $order->addLog('PROCESS', (isset($user_name) ? ($user_name . ' process ') : 'Process ') . $process->data['quantity'] . ' items');
//
//            OSC::core('observer')->dispatchEvent('catalog/orderProcessV2', $process);
//
//            $DB->commit();
//        } catch (Exception $ex) {
//            $DB->rollback();
//
//            OSC_Database_Model::unlockPreLoadedModel($locked_key);
//
//            throw new Exception($ex->getMessage());
//        }
//
//        OSC_Database_Model::unlockPreLoadedModel($locked_key);
//
//        return $process;
//    }

    public function getLimitSendEmailCustomer() {
        $levels = ['levela', 'levelb', 'levelc', 'unlimit'];

        $limit = 0;

        foreach ($levels as $key => $level) {
            if (OSC::controller()->checkPermission('catalog/order/send_email/' . $level, false)) {
                if ($level == 'unlimit') {
                    return -1;
                }

                $l = intval(OSC::helper('core/setting')->get('catalog/send_email_customer/' . $level));

                if ($l > $limit) {
                    $limit = $l;
                }
            }
        }

        if ($limit < 1) {
            $limit = 50;
        }
        return $limit > 0 ? $limit : 50;
    }

    public function ableToEdit($permission_key) {
        try {
            if (OSC::helper('user/authentication')->getMember()->getId() < 1) {
                return false;
            }
            $data_request = array(
                'permission_key' => $permission_key,
                'email' =>  OSC::helper('user/authentication')->getMember()->data['email']
            );

            $response = OSC::helper('master/common')->callApi('/catalog/api_order/ableToEdit', $data_request);

            if (!isset($response['result']) || $response['result'] == 0) {
                throw new Exception('Not able');
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    public function uniqueMultidimArray($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public function validateZipCode($zip, $country_code) {
        $patt = '/[^a-z0-9 ]/i';
        if (in_array($country_code, Helper_Core_Country::COUNTRY_ZIP_CODE_SPECIAL)) {
            $patt = '/[^a-z0-9- ]/i';
        }
        return preg_match($patt, $zip);
    }
}