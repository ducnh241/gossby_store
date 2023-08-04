<?php

class Controller_Catalog_React_Order extends Abstract_Frontend_ReactApiController {

    protected function _getOrderData(Model_Catalog_Order $order, array $messages = []) {
        try {
            if ($order->getId() < 1) {
                throw new Exception('Order is not exits');
            }

            $cancel_message = '';
            if (isset($messages['cancel_message'])) {
                $cancel_message = $messages['cancel_message'];
            }

            $result = [
                'shipping_address' => $order->getShippingAddress(),
                'billing_address' => $order->getBillingAddress(true),
                'order_ukey' => $order->getOrderUkey(),
                'email' => $order->data['email'],
                'order_master_record_id' => $order->getId(),
                'order_id' => $order->data['order_id'],
                'order_code' => $order->getCode(),
                'added_timestamp' => $order->data['added_timestamp'],
                'order_status' => $order->data['order_status'],
                'payment_status' => $order->data['payment_status'],
                'fulfillment_status' => $order->data['fulfillment_status'],
                'payment_method' => $order->getPaymentMethod(),
                'shipping_method' => $order->getCarrier()->getRate()->getTitleWithCarrier(),
                'subtotal_price' => $order->data['subtotal_price'],
                'shipping_price' => $order->getShippingPrice(),
                'total_price' => $order->data['total_price'],
                'tax_price' => $order->getTaxPrice(),
                'tip_price' => $order->getTipPrice(),
                'custom_price' => $order->data['custom_price'],
                'discount_codes' => [],
                'time_to_edit' => $order->getTimeToEditOrCancelImmediate(false),
                'is_editable' => $order->ableToEdit(),
                'billing_option_input' => ($order->checkBillingSameShipping() === true) ? 'same' : 'another',
                'is_cancellable' => $order->ableToCancel(),
                'url_contact_us' => OSC::helper('frontend/template')->getContactUrl(),
                'timelines' => [],
                'order_items' => [],
                'hasSemitest' => false,
                'cancel_message' => $cancel_message,
                'list_tab_purchase' => [],
                'list_tab_others' => [],
                'list_sort_order' => [],
                'list_map_item_order' => [],
                'enable_cross_sell' => 0
            ];

            if (!empty($order->data['discount_codes'])) {
                foreach ($order->data['discount_codes'] as $discount_code) {
                    if (!in_array($discount_code['apply_type'], ['entire_order', 'shipping', 'entire_order_include_shipping'])) {
                        continue;
                    }
                    $result['discount_codes'][] = [
                        'discount_code' => $discount_code['discount_code'],
                        'discount_price' => $discount_code['discount_price'],
                        'discount_shipping_price' => isset($discount_code['discount_shipping_price']) ? intval($discount_code['discount_shipping_price']) : 0,
                        'description' => $discount_code['description'] ?? '',
                        'info' => isset($discount_code['info']) && is_array($discount_code['info']) && count($discount_code['info']) > 0 ? $discount_code['info'][0] : '',
                    ];
                }
            }
            $map_line_item_ids = [];
            $line_items_data = [];
            foreach ($order->getLineItems() as $line_item) {
                $map_line_item_ids[$line_item->data['item_id']] = $line_item->getId();
                $line_items_data[$line_item->data['item_id']] = $line_item;
            }

            $transactions = $order->getTransactionCollection()->getItems();

            foreach ($transactions as $transaction) {
                $transaction_type = $transaction->data['transaction_type'];

                if (!in_array($transaction_type, ['cancel', 'refund'])) {
                    continue;
                }
                $data_transactions = [
                    'type' => ($transaction_type == 'cancel') ? 'cancelled' : 'refunded',
                    'timestamp' => $transaction->data['added_timestamp'],
                    'amount' => $transaction->data['amount'],
                ];

                if (isset($transaction->data['transaction_data']['shipping_price'])) {
                    $data_transactions['shipping_price'] = $transaction->data['transaction_data']['shipping_price'];
                }

                if (isset($transaction->data['transaction_data']['tax_price'])) {
                    $data_transactions['tax_price'] = $transaction->data['transaction_data']['tax_price'];
                }
                if (isset($transaction->data['transaction_data']['line_items'])  && count($transaction->data['transaction_data']['line_items']) > 0) {
                    foreach ($transaction->data['transaction_data']['line_items'] as $item_id => $value) {
                        $map_item_id = explode('_', $item_id)[0];
                        $line_item = $line_items_data[$map_item_id];
                        $transaction_addon_service = [];

                        if (isset($value['addon_services']) && !empty($value['addon_services']) && is_array($value['addon_services'])) {
                            ksort($value['addon_services']);
                            foreach ($value['addon_services'] as $addon_id => $addons) {
                                try {
                                    $addon_service = OSC::model('addon/service')->load($addon_id);
                                    $addon_option = $addon_service->data['data']['options'];
                                    foreach ($addons as $key => $option_key) {
                                        $addon_price = $addon_option[$option_key]['price'];
                                        if ($addon_service->data['type'] == Model_Catalog_Order::ADDON_SERVICE_TYPE_VARIANT) {
                                            $addon_price = intval($addon_price) - $line_item->getPrice();
                                        }
                                        $refunded_addon_quantity = ($value['refund_quantity'] > 0) ? $value['refund_quantity'] : $value['before_quantity'];

                                        $transaction_addon_service[$option_key] = [
                                            'title' => $addon_option[$option_key]['title'],
                                            'type' => $addon_service->data['type'],
                                            'price' => $addon_price,
                                            'image' => isset($addon_option[$option_key]['image']) && $addon_option[$option_key]['image'] ? $addon_option[$option_key]['image'] : '',
                                            'quantity' => $refunded_addon_quantity
                                        ];
                                    }
                                } catch (Exception $exception) {

                                }
                            }
                        }

                        $data_transactions['items'][] = [
                            'item_id' => $map_line_item_ids[$map_item_id],
                            'quantity' => $value['refund_quantity'],
                            'addon_services' => $transaction_addon_service
                        ];
                        $result['list_tab_purchase'][$data_transactions['type']][] = $map_line_item_ids[$map_item_id];
                    }
                } else {
                    $result['list_tab_others'][$data_transactions['type']][] = $data_transactions;
                }
                $result['list_sort_order'][$data_transactions['type']] = $data_transactions['timestamp'];
                $result['timelines'][] = $data_transactions;
            }

            $group_line_items = OSC::helper('catalog/order')->lineItemsGetByGroup($order);

            foreach ($group_line_items as $group_key => $group) {
                if (in_array($group_key, ['cancelled', 'refunded'])) {
                    continue;
                }

                $datas = ['type' => $group_key, 'timestamp' => $group['timestamp']];

                $additional_data = $group['fulfillment']->data['additional_data'];
                if (substr($group_key, 0, 9) == 'fulfilled') {
                    $datas['type'] = 'fulfilled';
                    $datas['tracking_number'] = $group['fulfillment']->data['tracking_number'];
                    $datas['tracking_url'] = $group['fulfillment']->data['tracking_url'];
                    $datas['shipping_carrier'] = $group['fulfillment']->data['shipping_carrier'];
                    $datas['service'] = $group['fulfillment']->data['service'];
                    $datas['additional_data'] = $additional_data;
                    $datas['timestamp'] = $group['fulfillment']->data['added_timestamp'];
                } elseif (substr($group_key, 0, 7) == 'process') {
                    $datas['type'] = 'processed';
                    $datas['timestamp'] = $group['process']->data['added_timestamp'];
                }

                foreach ($group['line_items'] as $line_item) {
                    $item = [
                        'item_id' => $line_item['model']->getId(),
                        'quantity' => $line_item['model']->data['quantity'],
                        'shipment' => [
                            'tracking_number' => $group['fulfillment']->data['tracking_number'],
                            'tracking_url' => $group['fulfillment']->data['tracking_url'],
                            'shipping_carrier' => $group['fulfillment']->data['shipping_carrier'],
                        ],
                        'addon_services' => []
                    ];
                    if (
                        isset($additional_data['shipments']) &&
                        count($additional_data['shipments']) > 0 &&
                        $line_item['model']->data['fulfilled_quantity'] > 0 &&
                        $group_key !== 'unfulfilled'
                    ) {
                        $quantity_main = $line_item['quantity'];
                        $quantity_check = 0;
                        foreach (array_reverse($additional_data['shipments']) as $shipment) {
                            if (isset($shipment['order_items'][$line_item['model']->getId()])) {
                                $quantity_check = $shipment['order_items'][$line_item['model']->getId()];
                                if ($quantity_main < $quantity_check) {
                                    break;
                                }
                                $quantity_main = $quantity_main - $quantity_check;
                                $item['shipment'] = [
                                    'tracking_number' => $shipment['tracking_number'],
                                    'tracking_url' => $shipment['tracking_url'],
                                    'shipping_carrier' => $shipment['shipping_carrier'],
                                ];
                            }
                        }
                    }
                    $datas['items'][] = $item;

                    $result['list_tab_purchase'][$datas['type']][] = $line_item['model']->getId();
                }
                $result['list_sort_order'][$datas['type']] = $datas['timestamp'];
                $result['timelines'][] = $datas;
            }

            usort($result['timelines'], function ($timeline1, $timeline2) {
                return $timeline2['timestamp'] <=> $timeline1['timestamp'];
            });

            /* @var $line_item Model_Catalog_Order_Item*/
            foreach ($order->getLineItems() as $line_item) {
                $result['list_map_item_order'][$line_item->getId()] = $line_item->data['order_master_record_id'];
                if ($line_item->isCrossSellMode()) {
                    $result['order_items'][$line_item->getId()] = OSC::helper('crossSell/common')->getDataOrderItem($line_item);
                    continue;
                }
                $campaign_config = null;
                try {
                    $campaign_config = OSC::helper('catalog/react_common')->getCampaignConfig($line_item->getId(), 'order');
                } catch (Exception $ex) {
                }

                $product = $line_item->getProduct();
                $is_disable_preview = Model_Catalog_Product::STATUS_PRODUCT_PREVIEW['DISABLE'];
                $semitest_config = null;

                if (count($campaign_config) < 1) {
                    try {
                        $semitest_config = OSC::helper('catalog/react_common')->getSemitestConfig($line_item->getId(), 'order');
                        $result['hasSemitest'] = true;
                    } catch (Exception $ex) {
                    }
                }

                $flag_pcs = 0;

                if (count($campaign_config) > 0 || count($semitest_config) > 0) {
                    $flag_pcs = 1;
                }

                if (isset($product->data['meta_data']['is_disable_preview'])) {
                    $is_disable_preview = $product->data['meta_data']['is_disable_preview'];
                }

                $line_item_id = $line_item->getId();
                $additional_data = $line_item->data['additional_data'];

                $result['order_items'][$line_item_id] = [
                    'id' => $line_item_id,
                    'quantity' => $line_item->data['quantity'],
                    'other_quantity' => $line_item->data['other_quantity'],
                    'variant_id' => $line_item->data['variant_id'],
                    'product_id' => $line_item->data['product_id'],
                    'product_title' => $line_item->data['title'],
                    'product_type_title' => count($line_item->data['options']) > 0 ? $line_item->getVariantOptionsText() : '',
                    'pack' => $line_item->getPackData(),
                    'price' => $line_item->getPrice(),
                    'price_addon_service' => $line_item->getAddonServicePrice(false),
                    'options' => $line_item->getVariantOptionsText(),
                    'image_url' => $line_item->isCampaignMode() ? $line_item->getCampaignOrderLineItemMockupUrl() : $line_item->getImageUrl(),
                    'url' => ($line_item->getProduct() instanceof Model_Catalog_Product) ? $line_item->getProduct()->getDetailUrl() : '',
                    'semitest_config' => $semitest_config,
                    'campaign_config' => $campaign_config,
                    'crosssell_config' => null,
                    'flag_pcs' => $flag_pcs,
                    'item_resend' => isset($additional_data['resend']['resend']) ? true : false,
                    'show_edit_design' => ($order->ableToEdit() == true && $line_item->checkItemWaitDesign() == false) ? 1 : 0,
                    'discount' => $line_item->data['discount'],
                    // TODO: fix n+1 query
                    'addon_services' => $line_item->getAddonServices(),
                    'is_disable_preview' => $is_disable_preview
                ];
            }

            return $result;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function actionPostTrackingOrder() {
        $order = OSC::model('catalog/order');
        try {
            $email = trim($this->_request->get('email'));
            $code = trim($this->_request->get('code'));

            if ($code == '') {
                throw new Exception('Order is not exists');
            }

            OSC::core('validate')->validEmail($email);

            $order->loadByCode($code);

            if (strtolower($email) != strtolower($order->data['email'])) {
                throw new Exception('Order is not exists');
            }

            $this->sendSuccess($this->_getOrderData($order));
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }
    }

    public function actionGetOrderDetail() {
        try {
            $order = static::_preCheckDataOrder();

            foreach ($order->getLineItems() as $line_item) {
                $line_item->lock();
            }

            $token = $this->_request->get('token');

            $data = $this->_getOrderData($order);

            $data['is_editable_by_token'] = 0;
            $data['show_discount_not_support_shipping'] = $order->showDiscount();
            $estimate_timestamp = $order->data['shipping_line']['carrier']['rates'][0]['estimate_timestamp'] ?? 0;
            $data['estimate_delivery_time'] = $estimate_timestamp != 0 ?
                date('l, F d, Y', ($estimate_timestamp)) : '';
            if ($token) {
                try {
                    $confirmShipping = OSC::model('catalog/order_confirmShipping')->loadByUKey($token);

                    if ($confirmShipping->data['queue_flag'] == Model_Catalog_Order_ConfirmShipping::CONFIRM_SHIPPING_WAIT_TO_USE) {
                        $data['is_editable_by_token'] = 1;
                    }
                } catch (Exception $ex) {

                }
            }

            $this->sendSuccess($data);

        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

    }

    protected function _preCheckDataOrder() : Model_Catalog_Order {
        try {
            $order_ukey = $this->_request->get('order_ukey');

            if (!$order_ukey) {
                throw new Exception('Order ID is not empty');
            }

            $order = OSC::model('catalog/order');
            try {
                $order->loadByOrderUKey($order_ukey);
            } catch (Exception $ex) {

            }
            if ($order->getId() < 1) {
                throw new Exception('Order is not exists');
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
        return $order;
    }

    public function actionPostCancelOrder()
    {
        $reason = $this->_request->get('reason');
        $solution = $this->_request->get('solution');
        $reason = OSC::safeString(trim($reason));
        $solution = OSC::safeString(trim($solution));

        try {
            /**
             * @var $order Model_Catalog_Order
             */
            $order = static::_preCheckDataOrder();
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        if (!$order->ableToCancel()) {
            $this->sendError('The order is not able to cancel. Thank You !', $this::CODE_NOT_FOUND);
        }

        $cache = OSC::core('cache');

        $current_time = time();

        $payment_status = $order->data['payment_status'];

        if ((($current_time - ($order->data['added_timestamp'])) <= $order->getTimeToEditOrCancelImmediate()) && $payment_status == 'authorized') {
            try {
                $data_request = array(
                    'order_id' => $order->data['order_id'],
                    'reason' => $reason,
                    'solution' => $solution
                );
                try {
                    OSC::helper('master/common')->callApi('/catalog/api_customer/cancelOrder', $data_request);

                    $cache_cancellation = $cache->get($order->data['code'] . '-cancellation');

                    if (!$cache_cancellation) {
                        $cache->set($order->data['code'] . '-cancellation', $order->data['code'], $order->getTimeToEditOrCancelImmediate() - ($current_time - ($order->data['added_timestamp'])));
                    }
                    $order->reload();
                    $this->sendSuccess($this->_getOrderData($order, ['cancel_message' => 'Your order has been successfully cancelled.']));
                } catch (Exception $ex) {
                    $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
                }

            } catch (Exception $ex) {

                $data_request = array(
                    'order_id' => $order->data['order_id']
                );
                OSC::helper('master/common')->callApi('/catalog/api_customer/addQueueCancelOrder', $data_request);

                $cache_cancellation = $cache->get($order->data['code'] . '-cancellation');

                if (!$cache_cancellation) {
                    $cache->set($order->data['code'] . '-cancellation', $order->data['code'], $order->getTimeToEditOrCancelImmediate() - ($current_time - ($order->data['added_timestamp'])));
                }

                $order->reload();
                $this->sendSuccess($this->_getOrderData($order, ['cancel_message' => 'Your order has been successfully cancelled.']));
            }
        } elseif (($order->getTimeToEditOrCancelImmediate() < ($current_time - ($order->data['added_timestamp'])) &&  ($current_time - ($order->data['added_timestamp'])) <= $order->getTimeToCancelMediate()) || ($payment_status == 'paid' && (($current_time - ($order->data['added_timestamp'])) <= ($order->getTimeToEditOrCancelImmediate())))) {
            $customer_comments = $this->_request->get('with_reason_customer');

            if (isset($customer_comments)) {
                $customer_comments = OSC::safeString(trim($customer_comments));

                if (strlen($customer_comments) < 10 || strlen($customer_comments) > 255) {
                    $this->sendError('The customer comments for order cancellation is between 10 and 255 characters!', $this::CODE_NOT_FOUND);
                }
            }

            try {
                $data_request = [
                    'order_id' => $order->data['order_id'],
                    'reason' => $reason,
                    'solution' => $solution,
                    'customer_comments' => $customer_comments
                ];

                try {
                    OSC::helper('master/common')->callApi('/catalog/api_customer/cancelOrderAfter2Hour', $data_request);

                    $cache_cancellation = $cache->get($order->data['code'] . '-cancellation');

                    if (!$cache_cancellation) {
                        $cache->set($order->data['code'] . '-cancellation', $order->data['code'], $order->getTimeToCancelMediate() - ($current_time - ($order->data['added_timestamp'])));
                    }
                    $order->reload();
                    $this->sendSuccess($this->_getOrderData($order, ['cancel_message' => 'Your order has been temporarily held. Our team will reach back within 24 hours to assist. Thank you!']));
                } catch (Exception $ex) {
                    $this->sendError($ex->getMessage());
                }
            } catch (Exception $ex) {
                $this->sendError('System error. Please try again. Thank You!', $ex->getMessage());
            }
        } else {
            $this->sendError('The order is not able to cancel. Thank You!', $this::CODE_NOT_FOUND);
        }
    }

    public function actionPostEditOrderAddress() {
        try {
            $order = static::_preCheckDataOrder();
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_LOAD_ORDER_ERROR);
        }

        $token = trim($this->_request->get('token', null));

        $enable_popup_edit_order = 0;
        if ($token) {
            try {
                $confirmShipping = OSC::model('catalog/order_confirmShipping')->loadByUKey($token);

                if ($confirmShipping->data['queue_flag'] == Model_Catalog_Order_ConfirmShipping::CONFIRM_SHIPPING_WAIT_TO_USE) {
                    $enable_popup_edit_order = 1;
                }
            } catch (Exception $ex) {
                $this->sendError($ex->getMessage(), $this::CODE_EDIT_ORDER_BY_TOKEN_ERROR);
            }
        }

        if (!$order->ableToEdit() && $enable_popup_edit_order == 0) {
            $this->sendError('The order is not able to edit . Thank You !!!', $this::CODE_NOT_FOUND);
        }

        $data_update = [];
        $log_content = [];

        $email = $this->_request->get('email');
        $current_email = $order->data['email'];

        if ($email != $current_email) {
            if ($email == '') {
                $this->sendError('Email is not empty !!!', $this::CODE_NOT_FOUND);
            }

            if (OSC::helper('personalizedDesign/common')->hasEmoji($this->_request->get('email'))) {
                $this->sendError('Please do not include special characters in the Email field', $this::CODE_NOT_FOUND);
            }
            $log_content['Email'] = 'from "' . $order->data['email'] . '" to "' . $email . '"';
            $data_update['email'] = $email;
        }


        $shipping_address = OSC::helper('checkout/common')->getRequestAddress($this->_request->getAll(), 'shipping_address');

        $billing_address = OSC::helper('checkout/common')->getRequestAddress($this->_request->getAll(), 'billing_address');

        if (OSC::helper('core/country')->checkCountryDeactive($billing_address['country'])) {
            $this->sendError('We are sorry! ' . $billing_address['country'] . ' is not supported, please select another country', $this::CODE_NOT_FOUND);
        }

        $billing_option_input = $this->_request->get('billing_option_input');

        foreach (Helper_Core_Country::ADDRESS_FIELDS as $key => $name) {
            if (isset($shipping_address[$key])) {
                $shipping_address[$key] = OSC::helper('personalizedDesign/common')->escapeString($shipping_address[$key]);
                if (OSC::helper('personalizedDesign/common')->hasEmoji($shipping_address[$key])) {
                    $this->sendError('Please do not include special characters in the ' . $name . ' field', $this::CODE_NOT_FOUND);
                }
                if ($key == 'zip') {
                    $country_code = OSC::helper('core/country')->getCountryCode($shipping_address['country']);
                    if (OSC::helper('catalog/order')->validateZipCode($shipping_address[$key], $country_code)) {
                        $this->sendError('Please remove invalid char(s) at shipping ' . $name, $this::CODE_NOT_FOUND);
                    }
                }
                $data_update['shipping'][$key] = $order->data['shipping_' . $key];
                if ($order->data['shipping_' . $key] != $shipping_address[$key]) {
                    $data_update['shipping'][$key] = $shipping_address[$key];
                }
            }

            if (isset($billing_address[$key])) {
                $billing_address[$key] = OSC::helper('personalizedDesign/common')->escapeString($billing_address[$key]);
                if (OSC::helper('personalizedDesign/common')->hasEmoji($billing_address[$key])) {
                    $this->sendError('Please do not include special characters in the ' . $name . ' field', $this::CODE_NOT_FOUND);
                }
                if ($key == 'zip') {
                    $country_code = OSC::helper('core/country')->getCountryCode($billing_address['country']);
                    if (OSC::helper('catalog/order')->validateZipCode($billing_address[$key], $country_code)) {
                        $this->sendError('Please remove invalid char(s) at billing ' . $name, $this::CODE_NOT_FOUND);
                    }
                }
                $data_update['billing'][$key] = $order->data['billing_' . $key];
                if ($order->data['billing_' . $key] != $billing_address[$key]) {
                    $data_update['billing'][$key] = $billing_address[$key];
                }
            }

            if ($billing_option_input == 'same') {
                $key_val = isset($shipping_address[$key]) ? $shipping_address[$key] : $order->data['shipping_' . $key];
                if ($order->data['billing_' . $key] != $key_val) {
                    $data_update['billing'][$key] = $key_val;
                }
            }
        }

        if (count($data_update) > 0) {
            $data_request = array(
                'data_update' => ['shipping' => $data_update['shipping'], 'billing' => $data_update['billing'], 'email' => $email],
                'order_id' => $order->data['order_id'],
                'token' => $token,
                'modified_name' => OSC::helper('user/authentication')->getMember()->getId() > 0 ? (ucfirst(OSC::helper('user/authentication')->getMember()->data['username'])) : $order->data['shipping_full_name']
            );

            try {
                OSC::helper('master/common')->callApi('/catalog/api_customer/editAddress', $data_request);
                $order->reload();

                $this->sendSuccess($this->_getOrderData($order));
            } catch (Exception $ex) {
                $this->sendError($ex->getMessage(), $ex->getCode());
            }
        } else {
            $this->sendSuccess('Not change. Thank You !!!');
        }

        $order->reload();
        $this->sendSuccess($this->_getOrderData($order));
    }

    public function actionGetOrderCancelReasonList () {
        try {
            /**
             * @var $order Model_Catalog_Order
             */
            $order = static::_preCheckDataOrder();
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        try {
            $reason_cancel_orders = OSC::helper('core/setting')->get('catalog/reason_cancel_order');

            $this->sendSuccess($reason_cancel_orders);
        } catch (Exception $ex){
            $this->sendError($ex->getMessage(), $ex->getCode());
        }

    }

    public function actionThankyou() {
        try {
            /**
             * @var $order Model_Catalog_Order
             */
            $order = static::_preCheckDataOrder();
            $this->sendSuccess($this->_getOrderData($order));
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }
    }

    public function actionEditDesign() {
        /* @var $line_item Model_Catalog_Order_Item */
        /* @var $line_item Model_Catalog_Order_Item */

        try {
            $line_item_id = intval($this->_request->get('item_id'));

            if ($line_item_id < 1) {
                throw new Exception('Line item ID is incorrect', $this::CODE_METHOD_NOT_ALLOWED);
            }

            $line_item = OSC::model('catalog/order_item')->load($line_item_id);

            $order = $line_item->getOrder();

            if ($order->getOrderUkey() != $this->_request->get('order_ukey')) {
                throw new Exception('You dont have permission to perform this action', $this::CODE_METHOD_NOT_ALLOWED);
            }

            if (!$line_item->getOrder()->ableToEdit()) {
                throw new Exception('You dont have permission to perform this action', $this::CODE_METHOD_NOT_ALLOWED);
            }

            if ($line_item->checkItemWaitDesign()) {
                throw new Exception('Item’s design is waiting for approval');
            }

            $campaign_data_idx = $line_item->getCampaignDataIdx();

            if ($campaign_data_idx === null) {
                throw new Exception('Line item is not a campaign', $this::CODE_NOT_MODIFIED);
            }

        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }

        $new_config = $this->_request->get('config');

        if (is_array($new_config) && count($new_config) > 0) {
            try {
                $variant_id = intval($new_config['variant_id']);

                try {
                    $variant = OSC::model('catalog/product_variant')->load($variant_id);
                } catch (Exception $ex) {
                    throw new Exception($ex->getCode() == 404 ? 'Product variant is not available' : $ex->getMessage());
                }

                $product_type_variant_id_old = $line_item->getProductTypeVariantId();

                $product_type_variant_id_new = $variant->getProductTypeVariant()->getId();

                $print_template_id = intval($new_config['print_template_id']);

                if ($print_template_id < 1) {
                    throw new Exception('Print template is not available');
                }

                //edit product and size and color
                if($product_type_variant_id_old != $product_type_variant_id_new) {
                    //check author
                    if (OSC::helper('user/authentication')->getMember()->getId() < 1) {
                        throw new Exception('You do not have permission to use this function');
                    }

                    //item resend no support
                    if (isset($line_item->data['additional_data']['resend']['resend'])) {
                        throw new Exception('Changing product type is not allowed for resend items');
                    }

                    $request_data = OSC::helper('catalog/campaign')->getDataEditDesignChangeProductType($variant_id, $print_template_id, $line_item, $new_config);

                    $data_price_item_old = $request_data['data_price_item_old'];

                    $data_price_item_new = $request_data['data_price_item_new'];

                    $config_option = $request_data['config'];

                    $product_type_variant_old = $request_data['product_type_variant_old'];

                    $product_type_variant_new = $request_data['product_type_variant_new'];

                    $discount_code = $request_data['discount_code'];

                    $pack_data = $request_data['pack_data'];

                    OSC::helper('catalog/campaign')->addEditDesignChangeProductType($line_item, OSC::helper('user/authentication')->getMember()->getId(), OSC::helper('user/authentication')->getMember()->data['username'], $data_price_item_old, $data_price_item_new, $variant_id, $print_template_id, $config_option, $product_type_variant_old, $product_type_variant_new, $discount_code, $pack_data);

                    $this->sendSuccess('Design changes have been added to queue, waiting for the authorized person’s approval');
                }
            }catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    $this->sendError($ex->getMessage(), $ex->getCode());
                }
            }

            try {
                $campaign_data = OSC::helper('catalog/campaign')->orderLineItemVerifyNewDesignData($line_item->getOrderItemMeta()->data['custom_data'][$campaign_data_idx], $new_config, true, $line_item->data['product_id'], true);

                $modifier = $this->getAccount()->getId() > 0 ? (ucfirst($this->getAccount()->data['username'])) : $line_item->getOrder()->data['shipping_full_name'];

                OSC::helper('catalog/campaign')->updateDesign($line_item, $campaign_data, ['modifier' => $modifier, 'type' => 'edit_design']);

                static::_preCheckDataOrder();

                $order_detail = static::_getOrderData($order);

                $this->sendSuccess($order_detail);

            } catch (Exception $ex) {
                $this->sendError($ex->getMessage(), $ex->getCode());
            }
        }

        $this->sendSuccess(OSC::helper('catalog/campaign')->orderLineItemGetDesignEditFrmData($line_item, $line_item->getOrderItemMeta()->data['custom_data'][$campaign_data_idx]));
    }

    public function actionEditDesignByMaster() {
        /* @var $line_item Model_Catalog_Order_Item */
        /* @var $line_item Model_Catalog_Order_Item */

        $member_edit_design = 0;

        try {
            $token = trim($this->_request->get('token'));

            $data = OSC::core('cache')->get($token);

            if (!isset($data) || trim($data) == '') {
                throw new Exception('Data incorrect');
            }

            $data = explode('_', $data);

            $line_item_id = intval($this->_request->get('item_id'));

            if ($line_item_id < 1) {
                throw new Exception('Line item ID is incorrect');
            }

            if ($line_item_id != $data[0]) {
                throw new Exception('You are not allowed to access this function');
            }

            $line_item = OSC::model('catalog/order_item')->load($line_item_id);

            $member_edit_design = $data[2];

            $order = $line_item->getOrder();

            if ($order->getOrderUkey() != $this->_request->get('order_ukey')) {
                throw new Exception('Key incorrect', $this::CODE_METHOD_NOT_ALLOWED);
            }

            $campaign_data_idx = $line_item->getCampaignDataIdx();

            if ($campaign_data_idx === null) {
                throw new Exception('Line item is not a campaign', $this::CODE_NOT_MODIFIED);
            }

        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }

        $new_config = $this->_request->get('config');

        $user_name = $this->_request->get('username');

        if (!isset($user_name) || trim($user_name) == '') {
            $user_name = '';
        }

        if (is_array($new_config) && count($new_config) > 0) {
            try {
                $variant_id = intval($new_config['variant_id']);

                try {
                    $variant = OSC::model('catalog/product_variant')->load($variant_id);
                } catch (Exception $ex) {
                    throw new Exception($ex->getCode() == 404 ? 'Product variant is not available' : $ex->getMessage());
                }

                $product_type_variant_id_old = $line_item->getProductTypeVariantId();

                $product_type_variant_id_new = $variant->getProductTypeVariant()->getId();

                $print_template_id = intval($new_config['print_template_id']);

                if ($print_template_id < 1) {
                    throw new Exception('Print template is not available');
                }

                //edit product and size and color
                if($product_type_variant_id_old != $product_type_variant_id_new) {
                    //item resend no support
                    if (isset($line_item->data['additional_data']['resend']['resend'])) {
                        throw new Exception('Changing product type is not allowed for resend items');
                    }

                    $request_data = OSC::helper('catalog/campaign')->getDataEditDesignChangeProductType($variant_id, $print_template_id, $line_item, $new_config);

                    $data_price_item_old = $request_data['data_price_item_old'];

                    $data_price_item_new = $request_data['data_price_item_new'];

                    $config_option = $request_data['config'];

                    $product_type_variant_old = $request_data['product_type_variant_old'];

                    $product_type_variant_new = $request_data['product_type_variant_new'];

                    $discount_code = $request_data['discount_code'];

                    $pack_data = $request_data['pack_data'];

                    OSC::helper('catalog/campaign')->addEditDesignChangeProductType($line_item, $member_edit_design, $user_name, $data_price_item_old, $data_price_item_new, $variant_id, $print_template_id, $config_option, $product_type_variant_old, $product_type_variant_new, $discount_code, $pack_data);

                    $this->sendSuccess('Design changes have been added to the queue. waiting to accept the authorized person');
                }
            }catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    $this->sendError($ex->getMessage(), $ex->getCode());
                }
            }

            try {
                $campaign_data = OSC::helper('catalog/campaign')->orderLineItemVerifyNewDesignData($line_item->getOrderItemMeta()->data['custom_data'][$campaign_data_idx], $new_config, true, $line_item->data['product_id'], true);

                OSC::helper('catalog/campaign')->updateDesign($line_item, $campaign_data, ['modifier' => $user_name, 'type' => 'edit_design']);

                static::_preCheckDataOrder();

                $order_detail = static::_getOrderData($order);

                $this->sendSuccess($order_detail);

            } catch (Exception $ex) {
                $this->sendError($ex->getMessage(), $ex->getCode());
            }
        }

        $this->sendSuccess(OSC::helper('catalog/campaign')->orderLineItemGetDesignEditFrmData($line_item, $line_item->getOrderItemMeta()->data['custom_data'][$campaign_data_idx]));
    }

    public function actionEditDesignSemiTest() {
        try {
            $line_item_id = intval($this->_request->get('item_id'));

            if ($line_item_id < 1) {
                throw new Exception('Line item ID is incorrect', $this::CODE_METHOD_NOT_ALLOWED);
            }

            $request_by_master = boolval($this->_request->get('request_by_master'));

            if ($request_by_master) {
                $token = trim($this->_request->get('token'));

                $data = OSC::core('cache')->get($token);

                if (!isset($data) || trim($data) == '') {
                    throw new Exception('Data incorrect');
                }

                $token_data = explode('_', $data);

                if ($line_item_id != intval($token_data[0])) {
                    throw new Exception('You are not allowed to access this function');
                }

                /* @var $line_item Model_Catalog_Order_Item */

                $line_item = OSC::model('catalog/order_item')->load($line_item_id);
            } else {
                /* @var $line_item Model_Catalog_Order_Item */

                $line_item = OSC::model('catalog/order_item')->load($line_item_id);

                if (!$line_item->getOrder()->ableToEdit()) {
                    throw new Exception('You dont have permission to perform this action', $this::CODE_METHOD_NOT_ALLOWED);
                }
            }

            $order = $line_item->getOrder();

            if ($order->getOrderUkey() != $this->_request->get('order_ukey')) {
                throw new Exception('You dont have permission to perform this action', $this::CODE_METHOD_NOT_ALLOWED);
            }

            $variant = $line_item->getVariant();

            if (empty($variant->data)) {
                throw new Exception('Variant not found');
            }

            $is_render_design = count($variant->data['meta_data']['variant_config']) > 0 ? true : false;

            $matched_entry_idx = false;

            $line_item_meta = $line_item->getOrderItemMeta();

            foreach ($line_item_meta->data['custom_data'] as $entry_idx => $custom_entry) {
                if ($custom_entry['key'] == 'personalized_design' && $custom_entry['type'] == 'semitest') {
                    $matched_entry_idx = $entry_idx;
                    break;
                }
            }

            if ($matched_entry_idx === false) {
                throw new Exception('No personalized config was found to render');
            }

            $old_custom_data = $line_item_meta->data['custom_data'][$matched_entry_idx];

            $form_edit_design_data = OSC::helper('catalog/product')->getFormEditDesignSemitest($old_custom_data, $variant);

            $designs = $form_edit_design_data['design_data'];

            /* @var $line_item Model_Catalog_Order_Item */
            $config = $this->_request->get('config');

            $new_config = null;

            if (count($config)) {
                foreach (array_keys($designs) as $design_id) {
                    $new_config[$design_id] = $config[$design_id];
                }
            }

            if (is_array($new_config) && count($new_config) > 0) {
                try {
                    $user_name = !empty(trim($this->_request->get('username'))) ? trim($this->_request->get('username')) : '';

                    foreach ($new_config as $design_id => $config) {
                        if (!isset($designs[$design_id])) {
                            throw new Exception('Personalized design is not exists');
                        }

                        OSC::helper('personalizedDesign/common')->verifyCustomConfig($designs[$design_id], $new_config[$design_id]);
                    }

                    $custom_data = Observer_PersonalizedDesign_Frontend::validate([
                        'custom_data' => [
                            'personalized_design' => array_keys($new_config),
                            'personalized_config' => $new_config
                        ]
                    ]);

                    $new_custom_data = $line_item_meta->data['custom_data'];

                    $new_custom_data[$matched_entry_idx] = $custom_data;

                    $line_item->getOrderItemMeta()->setData('custom_data', $new_custom_data)->save();

                    $this->__addLogEditDesignSemitest($order, ['new_config' => $new_config, 'old_config' => $old_custom_data], $designs, $user_name);

                    $order_detail = static::_getOrderData($order);

                    if ($is_render_design) {
                        OSC::helper('catalog/campaign')->reRenderAfterEditDesignSemitest($line_item);
                    }

                    $this->sendSuccess($order_detail);
                } catch (Exception $ex) {
                    $this->sendError($ex->getMessage(), $ex->getCode());
                }
            }

            $this->sendSuccess(['designs' => $form_edit_design_data['designs']]);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetListByCustomerId() {
        $page = 1;
        $page_size = 10000;

        try {
            $customer_id = OSC::helper('account/customer')->getCustomerId();
            if (intval($customer_id) < 1) {
                $customer_id = intval($this->_request->get('customer_id'));
            }

            if ($customer_id < 1) {
                throw new Exception('Data customer is incorrect');
            }

            // fetch orders from elasticsearch
            $order_data = $this->_getOrdersFromElasticsearch(intval($customer_id), $page, $page_size);

            $orders = $order_data['orders'];
            $result = [
                'orders' => [],
                'list_enable_reviews' => [],
                'list_tab_others' => [],
                'list_tab_purchase' => [],
                'list_sort_order' => [],
                'list_map_item_order' => [],
            ];

            $list_sort = [];

            if ($orders->length() > 0) {
                foreach (['fulfilled','processed','cancelled','refunded','placed','confirmed'] as $status) {
                    if (!isset($result['list_tab_purchase'][$status])) {
                        $result['list_tab_purchase'][$status] = [];
                    }
                }
                $list_order_id_reviews = [];
                foreach ($orders as $order) {
                    $data_order = $this->_getOrderData($order);
                    $list_tab_others = $data_order['list_tab_others'];
                    $list_tab_purchase = $data_order['list_tab_purchase'];
                    $list_map_item_order = $data_order['list_map_item_order'];
                    $list_sort_order = $data_order['list_sort_order'];

                    foreach ($list_tab_others as $key => $data) {
                        $current_others = $result['list_tab_others'][$order->getId()][$key] ?? [];
                        $result['list_tab_others'][$order->getId()][$key] = array_merge($current_others, $data);
                    }
                    foreach ($list_map_item_order as $item_id => $order_id) {
                        $result['list_map_item_order'][$item_id] = $order_id;
                    }
                    foreach ($list_tab_purchase as $key => $list_item_id) {
                        if (!in_array($key, ['unfulfilled','processed','fulfilled','refunded','cancelled'])) {
                            continue;
                        }
                        if ($key === 'unfulfilled') {
                            if ($order->data['payment_status'] === 'authorized') {
                                $result['list_tab_purchase']['placed'] = array_merge($result['list_tab_purchase']['placed'], $list_item_id);;
                            } else {
                                $result['list_tab_purchase']['confirmed'] = array_merge($result['list_tab_purchase']['confirmed'], $list_item_id);;
                            }
                        } else {
                            $result['list_tab_purchase'][$key] = array_merge($result['list_tab_purchase'][$key], $list_item_id);
                        }
                    }

                    foreach ($list_sort_order as $key => $time) {
                        if (!in_array($key, ['unfulfilled','processed','fulfilled','refunded','cancelled'])) {
                            continue;
                        }
                        if ($key === 'unfulfilled') {
                            if ($order->data['payment_status'] === 'authorized') {
                                $list_sort['placed'][$order->getId()] = $time;
                            } else {
                                $list_sort['confirmed'][$order->getId()] = $time;
                            }
                        } else {
                            $list_sort[$key][$order->getId()] = $time;
                        }
                        if ($key != 'cancelled' && $key != 'refunded') {
                            $list_order_id_reviews[] = $order->getId();
                        }
                    }

                    $result['orders'][$order->getId()] = $data_order;

                    $result['list_tab_status'][$order->getId()] = $this->getOrderStatus($order, $list_tab_purchase);
                }

                foreach ($list_sort as $key => $data_sort) {
                    if (!in_array($key, ['placed', 'confirmed','processed','fulfilled','refunded','cancelled'])) {
                        continue;
                    }
                    arsort($data_sort);
                    $result['list_sort_order'][$key] = array_keys($data_sort);
                }

                krsort($result['orders']);

                $list_order_id_reviews = array_unique($list_order_id_reviews);

                if (count($list_order_id_reviews) > 0) {
                    $DB = OSC::core('database');

                    $DB->select('order_id, ukey, product_id', 'catalog_product_review_request' , 'order_id in ('.implode(',', $list_order_id_reviews).')', null, null, 'list_product_reviews');

                    $rows = $DB->fetchArrayAll('list_product_reviews');

                    $DB->free('list_product_reviews');

                    $list_enable_reviews = [];

                    foreach ($rows as $row) {
                        if ($row['product_id'] < 1) {
                            continue;
                        }
                        $list_enable_reviews[intval($row['order_id'])][] = [intval($row['product_id']) => $row['ukey']];
                    }

                    $result['list_enable_reviews'] = $list_enable_reviews;
                }

                $result['list_tab_purchase']['shipped'] = $result['list_tab_purchase']['fulfilled'];
                unset($result['list_tab_purchase']['fulfilled']);
            }
            $this->sendSuccess($result);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionBuyAgain() {
        try {
            /**
             * @var $order Model_Catalog_Order
             */
            $order = static::_preCheckDataOrder();
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        try {
            $cart = OSC::helper('catalog/common')->getCart(true);

            foreach ($order->getLineItems() as $order_item) {
                if ($order_item->isCrossSellMode()) {
                    OSC::helper('crossSell/common')->_addProductToCartBuyAgain($cart, $order_item);
                    continue;
                }

                $product = $order_item->getProduct();
                $variant = $order_item->getVariant();
                if (!($variant instanceof Model_Catalog_Product_Variant) ||
                    $variant->getId() < 1 ||
                    !($product instanceof Model_Catalog_Product) ||
                    $product->getId() < 1
                ) {
                    continue;
                }

                $data_item = $order_item->data;
                $data_item['custom_data'] = $order_item->getOrderItemMeta()->data['custom_data'];
                try {
                    $this->_addProductToCart($cart, $data_item);
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }
            }
            //Update cart custom_price_data
            $cartCustomPriceData = $cart->data['custom_price_data'];
            $cartCustomPriceData = is_array($cartCustomPriceData) && !empty($cartCustomPriceData) ? $cartCustomPriceData : [];

            $option_billing = 'same';
            $data_update = [
                'custom_price_data' => $cartCustomPriceData
            ];

            foreach ($order->getShippingAddress() as $key => $value) {
                $data_update['shipping_'.$key] = $value;
            }

            foreach ($order->getBillingAddress() as $key => $value) {
                $data_update['billing_'.$key] = $value;
                if ($option_billing == 'same' && $data_update['shipping_'.$key] != $value) {
                    $option_billing = 'another';
                }
            }

            // create address account crm

            try {
                $address_shipping = [
                    'shop_id' => $order->data['shop_id'],
                    'customer_id' => $order->data['crm_customer_id'],
                    'address' => OSC::helper('account/address')->getDataAddressByOrder($order, 'shipping'),
                ];
                $customer_shipping = OSC::helper('account/address')->findOrCreate($address_shipping);

                $data_update['shipping_address_id'] = $customer_shipping['id'];
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
            if ($option_billing != 'same') {
                try {
                    $address_billing = [
                        'shop_id' => $order->data['shop_id'],
                        'customer_id' => $order->data['crm_customer_id'],
                        'address' => OSC::helper('account/address')->getDataAddressByOrder($order, 'billing', 1),
                    ];

                    $customer_billing = OSC::helper('account/address')->findOrCreate($address_billing);

                    $data_update['billing_address_id'] = $customer_billing['id'];
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }
            } else {
                $data_update['billing_address_id'] = $data_update['shipping_address_id'];
            }


            $cart->setData($data_update)->save();

            OSC::helper('catalog/common')->updateCartQuantity($cart);

            $this->_autoApplyDiscountCode($cart);

            $this->sendSuccess();
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }
    }

    protected function _addProductToCart(Model_Catalog_Cart $cart, $item) {
        try {
            if (empty($item)) {
                throw new Exception('No item added');
            }

            $custom_data = $item['custom_data'];

            $additional_data = [];

            foreach ($item['additional_data'] as $key => $value) {
                if ($key !== 'pack') {
                    continue;
                }
                $additional_data[$key] = $value;
            }
            //Update cart custom_price_data
            $itemCustomPriceData = $item['custom_price_data'];
            $itemCustomPriceData = is_array($itemCustomPriceData) && !empty($itemCustomPriceData) ? $itemCustomPriceData : [];

            $variant_id = intval($item['variant_id']);
            $quantity = intval($item['quantity']);

            $line_item = OSC::model('catalog/cart_item');

            $line_item_ukey = $line_item->makeUkey($cart->getId(), $variant_id, $custom_data, $itemCustomPriceData, $additional_data);

            try {
                $line_item->loadByUKey($line_item_ukey);
            } catch (Exception $ex) {

            }

            if ($line_item->getId() < 1) {
                $line_item->setData([
                    'cart_id' => $cart->getId(),
                    'product_id' => intval($item['product_id']),
                    'variant_id' => $variant_id,
                    'quantity' => $quantity,
                    'custom_data' => $custom_data,
                    'additional_data' => $additional_data,
                    'custom_price_data' => $itemCustomPriceData
                ])->save();

                $cart->getLineItems()->addItem($line_item);
            } else {
                try {
                    $line_item->incrementQuantity($quantity);
                } catch (Exception $ex) {

                }
            }

            $_SESSION['cart_new_item'] = $line_item->getId();

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getOrderStatus(Model_Catalog_Order $order,$datas) {
        if (count($datas['unfulfilled']) > 0 && $order->data['payment_status'] === 'authorized') {
            return 'Placed';
        }

        if (count($datas['unfulfilled']) > 0 && $order->data['payment_status'] !== 'authorized') {
            return 'Confirmed';
        }

        if (count($datas['processed']) > 0) {
            return 'Processing';
        }

        if (count($datas['fulfilled']) > 0) {
            return 'Shipped';
        }

        if (count($datas['cancelled']) > 0) {
            return 'Cancelled';
        }

        if (count($datas['refunded']) > 0) {
            return 'Refunded';
        }

    }

    public function actionCheckConfirmLateProduction()
    {
        $token = $this->_request->get('token');
        $token = OSC::decode(base64_decode($token));
        $type = isset($token['type']) ? $token['type'] : '';
        $delay_email_ukey = isset($token['ukey']) ? $token['ukey'] : '';
        if (!in_array($type, ['cancel', 'accept'])) {
            $this->sendError('Not Found', $this::CODE_NOT_FOUND);
        }

        $flag_success = false;
        try {
            $data_request = [
                'delay_email_ukey' => $delay_email_ukey
            ];
            $check_status = OSC::helper('master/common')->callApi('/catalog/api_order/checkLateProduction', $data_request);
            $order_status = isset($check_status['order_status']) ? $check_status['order_status'] : 'other';
            if ($order_status != 'default') {
                $flag_success = true;
                $type = $order_status;
            }
        } catch (Exception $ex) {
            $flag_success = true;
            $type = 'error';
        }
        $token_accept = base64_encode(OSC::encode(['type' => 'accept', 'ukey' => $delay_email_ukey]));
        $result = [
            'delay_email_ukey' => $delay_email_ukey,
            'type' => $type,
            'token_accept' => $token_accept,
            'flag_success' => $flag_success,
            'email_contact' => OSC::helper('core/setting')->get('theme/contact/email')
        ];
        $this->sendSuccess($result);
    }

    public function actionConfirmLateProduction()
    {
        $delay_email_ukey = $this->_request->get('delay_email_ukey');
        $action_type = $this->_request->get('action_type');
        $email_contact = OSC::helper('core/setting')->get('theme/contact/email');
        $error_message = "We are sorry! The system cannot proceed the cancellation at this moment. <br>Please contact our support team via " . $email_contact . " for further helps.";
        if (!in_array($action_type, ['cancel', 'accept'])) {
            $this->sendError($error_message);
        }

        $data_request = [
            'delay_email_ukey' => $delay_email_ukey,
            'action_type' => $action_type,
            'email_contact' => $email_contact
        ];

        try {
            OSC::helper('master/common')->callApi('/catalog/api_order/ConfirmLateProduction', $data_request);

            $this->sendSuccess([
                'result' => 200
            ]);
        } catch (Exception $ex) {
            $this->sendError($error_message);
        }
    }

    /**
     * @param $customer_id
     * @param int $page_index
     * @param int $page_size
     * @return array
     * @throws OSC_Exception_Runtime
     */
    protected function _getOrdersFromElasticsearch($customer_id, int $page_index = 1, int $page_size = 20): array
    {
        $search_data = [
            'shop_id' => OSC::getShop()->getId(),
            'keywords' => $customer_id,
            'field' => [
                'crm_customer_id',
            ],
        ];

        $elasticsearch_orders = OSC::helper('catalog/search_order')->getSearchOrder($search_data, $page_index, $page_size, true);

        $catalog_order_collection = OSC::model('catalog/order')->getCollection();

        if (empty($elasticsearch_orders['list_id']) || !is_array($elasticsearch_orders['list_id'])) {
            return [
                'total_count' => $elasticsearch_orders['total_item'],
                'orders' => $catalog_order_collection,
            ];
        }

        return [
            'total_count' => $elasticsearch_orders['total_item'],
            'orders' => $catalog_order_collection->sort('master_record_id', OSC_Database::ORDER_DESC)
                ->load($elasticsearch_orders['list_id']),
        ];
    }

    public function actionThankyouEmailClick() {
        try {
            $order_ukey = $this->_request->get('order_ukey');
            if (!isset($order_ukey)) {
                $this->sendError('Order ukey is missing', static::CODE_BAD_REQUEST);
            }
            
            $order = OSC::model('catalog/order')->loadByOrderUKey($order_ukey);
        } catch (Exception $ex) {

        }
        if (!isset($order) || !$order instanceOf Model_Catalog_Order || $order->getId() < 1) {
            $this->sendError('Order is not found', static::CODE_LOAD_ORDER_ERROR);
        }

        try {
            /* @var $cart Model_Catalog_Cart */
            $cart = OSC::helper('catalog/common')->getCart(true);

            if (!$cart->data['customer_id']) {
                $cart->setData([
                    'customer_id' => $order->data['crm_customer_id'],
                    'email' => $order->data['email'],
                ]);
            }

            if (!$cart->data['shipping_country_code']) {
                $shipping_address = $order->getShippingAddress();

                foreach ($shipping_address as $k => $v) {
                    $cart->setData('shipping_' . $k, $v);
                }

                $billing_address = $order->getBillingAddress();

                if ($billing_address) {
                    foreach ($billing_address as $k => $v) {
                        $cart->setData('billing_' . $k, $v);
                    }
                }
            }

            if (!$cart->data['billing_country_code']) {
                $billing_address = $order->getBillingAddress();

                if ($billing_address) {
                    foreach ($billing_address as $k => $v) {
                        $cart->setData('billing_' . $k, $v);
                    }
                }
            }

            if (count($cart->data['discount_codes']) < 1) {
                try {
                    $discount_code = OSC::model('catalog/discount_code')->loadByUKey($this->_request->get('discount_code'));
                    $cart->setData('discount_codes', [$discount_code->data['discount_code']]);
                } catch (Exception $ex) {
                }
            }

            $cart->save();
        } catch (Exception $ex) {
        }

        $product = $this->_request->get('product_id');

        if (isset($product)) {
            try {
                $product = OSC::model('catalog/product')->load($product);
                $this->sendSuccess([
                    'url' => $product->getDetailUrl(null, false)
                ]);
            } catch (Exception $ex) {
            }
        }

        $this->sendSuccess([
            'url' => '/'
        ]);
    }

    /**
     * @param Model_Catalog_Order $order
     * @param array $config
     * @param $designs
     * @param string $user_name
     * @throws Exception
     */
    private function __addLogEditDesignSemitest(Model_Catalog_Order $order, array $config, $designs, string $user_name) {
        $remove = [];
        $add = [];
        $from = [];
        $to = [];
        $flag_change = 0;

        $new_config = $config['new_config'];
        $old_custom_data = $config['old_config'];

        try {
            foreach ($new_config as $design_id => $config) {
                foreach ($config as $key => $value) {
                    if (!isset($old_custom_data['data'][$design_id]['config'][$key])) {
                        $flag_change++;
                        $add[$design_id][$key] = $new_config[$design_id][$key];
                    } elseif ($value != $old_custom_data['data'][$design_id]['config'][$key]) {
                        $flag_change++;
                        $from[$design_id][$key] = $old_custom_data['data'][$design_id]['config'][$key];
                        $to[$design_id][$key] = $value;
                    }
                }

                foreach ($old_custom_data['data'][$design_id]['config'] as $key => $value) {
                    if (!isset($config[$key])) {
                        $flag_change++;
                        $remove[$design_id][$key] = $value;
                    }
                }

                $log_data = null;

                if ($flag_change > 0) {
                    if (count($remove[$design_id]) > 0) {
                        $change['remove'] = OSC::helper('personalizedDesign/common')->fetchConfigPreview($designs[$design_id], $remove[$design_id]);
                        unset($change['remove']['document_type']);
                    }

                    if (count($add[$design_id]) > 0) {
                        $change['add'] = OSC::helper('personalizedDesign/common')->fetchConfigPreview($designs[$design_id], $add[$design_id]);
                        unset($change['add']['document_type']);
                    }

                    if (count($from[$design_id]) > 0) {
                        $change['change']['from'] = OSC::helper('personalizedDesign/common')->fetchConfigPreview($designs[$design_id], $from[$design_id]);
                        unset($change['change']['from']['document_type']);
                    }

                    if (count($to[$design_id]) > 0) {
                        $change['change']['to'] = OSC::helper('personalizedDesign/common')->fetchConfigPreview($designs[$design_id], $to[$design_id]);
                        unset($change['change']['to']['document_type']);
                    }
                }
            }

            if(!empty($user_name)) {
                $user_change = $user_name;
            } else {
                $user_change = OSC::helper('user/authentication')->getMember()->getId() > 0 ? (ucfirst(OSC::helper('user/authentication')->getMember()->data['username'])) : $order->getFullName();
            }

            $log_data = [
                'action_key' => 'EDIT_DESIGN_PERSONALIZED',
                'title' => $user_change . ' edit design personalized',
                'data' => [['change' => $change['change'], 'add' => $change['add'], 'remove' => $change['remove']]]
            ];

            if (is_array($log_data)) {
                $order->addLog($log_data['action_key'], $log_data['title'], $log_data['data']);
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
