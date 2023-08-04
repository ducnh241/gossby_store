<?php

class Helper_Shipping_Common extends OSC_Object {
    public function groupSettingShipping($rate_setting_data, $delivery_setting_data, $pack_setting_data, $country_id, $province_id) {
        $shipping_data = [];

        foreach ($rate_setting_data as $rate_data) {
            $location_parsed = OSC::decode($rate_data['location_parsed'], true);

            if (in_array($country_id . '_' . $province_id, $location_parsed)) {
                $location = $country_id . '_' . $province_id;
            } elseif (in_array($country_id . '_*', $location_parsed)) {
                $location = $country_id . '_*';
            }else {
                $location = '*_*';
            }

            if ($rate_data['product_type_variant_id'] == 0) {
                $rate_data['product_type_variant_id'] = '*';
            }

            if ($rate_data['product_type_id'] == 0) {
                $rate_data['product_type_id'] = '*';
            }

            $shipping_data[$rate_data['shipping_method_id']][$location][$rate_data['product_type_variant_id']][$rate_data['product_type_id']]['rate']  = [
                'rate_type' => $rate_data['rate_type'],
                'quantity_rate' => OSC::decode($rate_data['quantity_rate'], true),
                'dynamic_rate' => OSC::decode($rate_data['dynamic_rate'], true),
            ];
        }


        foreach ($delivery_setting_data as $delivery_data) {
            $location_parsed = OSC::decode($delivery_data['location_parsed'], true);

            if (in_array($country_id . '_' . $province_id, $location_parsed)) {
                $location = $country_id . '_' . $province_id;
            } elseif (in_array($country_id . '_*', $location_parsed)) {
                $location = $country_id . '_*';
            }else {
                $location = '*_*';
            }

            if ($delivery_data['product_type_variant_id'] == 0) {
                $delivery_data['product_type_variant_id'] = '*';
            }

            if ($delivery_data['product_type_id'] == 0) {
                $delivery_data['product_type_id'] = '*';
            }

            $shipping_data[$delivery_data['shipping_method_id']][$location][$delivery_data['product_type_variant_id']][$delivery_data['product_type_id']]['delivery'] = [
                'estimate_time' => $delivery_data['estimate_time'],
                'process_time' => $delivery_data['process_time'],
            ];
        }

        foreach ($pack_setting_data as $pack_data) {
            $location_parsed = OSC::decode($pack_data['location_parsed'], true);

            if (in_array($country_id . '_' . $province_id, $location_parsed)) {
                $location = $country_id . '_' . $province_id;
            } elseif (in_array($country_id . '_*', $location_parsed)) {
                $location = $country_id . '_*';
            }else {
                $location = '*_*';
            }

            if ($pack_data['product_type_variant_id'] == 0) {
                $pack_data['product_type_variant_id'] = '*';
            }

            if ($pack_data['product_type_id'] == 0) {
                $pack_data['product_type_id'] = '*';
            }

            $shipping_data[$pack_data['shipping_method_id']][$location][$pack_data['product_type_variant_id']][$pack_data['product_type_id']][$pack_data['pack_key']] = [
                'rate_type' => $pack_data['rate_type'],
                'quantity_rate' => OSC::decode($pack_data['quantity_rate'], true),
                'dynamic_rate' => OSC::decode($pack_data['dynamic_rate'], true),
            ];
        }

        $shipping_method_deactive = OSC::model('shipping/methods')->getCollection()->getShippingMethodDeactive();

        if (!is_array($shipping_method_deactive) || count($shipping_method_deactive) < 1) {
            return $shipping_data;
        }

        foreach ($shipping_method_deactive as $shipping_method) {
            if (isset($shipping_data[$shipping_method['id']])) {
                unset($shipping_data[$shipping_method['id']]);
            }
        }

        return $shipping_data;
    }

    public function calculateRates($shipping_setting_data, $grouped_quantity_pack, $grouped_quantity, $grouped_delivery, $country_id, $province_id, $cart_items, $flag_semi_test = false) {
        $estimate = [];

        $shipping_method_best = [];

        $shipping_method_fail = [];

        $priority_set_shipping_method = $this->_prioritySetMethodShipping($country_id, $province_id);

        /*Process pack */
        if (count($grouped_quantity_pack) > 0) {
            foreach ($shipping_setting_data as $shipping_method_id => $shipping_data) {
                foreach ($grouped_quantity_pack as $group_key => $data) {

                    $product_type_variant_id = explode('_', $group_key)[0];
                    $item_id = explode('_', $group_key)[1];

                    $flag_suitable = false;
                    /*check priority location->product_type_variant_id->product_type_id*/
                    foreach ($priority_set_shipping_method as $key => $priority) {
                        if ($priority['product_type_variant_id'] == 'original') {
                            $priority['product_type_variant_id'] = $product_type_variant_id;
                        }

                        if ($priority['product_type_id'] == 'original') {
                            $priority['product_type_id'] = $data['product_type_id'];
                        }

                        if (isset($shipping_data[$priority['location']][$priority['product_type_variant_id']][$priority['product_type_id']][$data['pack_key']])) {
                            $shipping_configs_pack = $shipping_data[$priority['location']][$priority['product_type_variant_id']][$priority['product_type_id']][$data['pack_key']];

                            $this->_calculatePriceShipping($shipping_configs_pack, $data['quantity'], $estimate, $shipping_method_id, $product_type_variant_id, $flag_suitable, $data['tax_value'], $item_id, $shipping_method_best);

                            if ($flag_suitable) {
                                break;
                            }
                        }
                    }

                    if (!$flag_suitable && !isset($grouped_quantity[$product_type_variant_id . '_' . $data['item_id']])) {
                        $grouped_quantity[$product_type_variant_id . '_' . $data['item_id']] = [
                            'quantity' => intval($data['quantity']) * intval(preg_replace('/[^0-9.]+/', '', $data['pack_key'])),
                            'product_type_id' => $data['product_type_id'],
                            'tax_value' => $data['tax_value'],
                            'item_id' => $data['item_id']
                        ];
                    }
                }
            }
        }

        /*Process product normal */
        if (count($grouped_quantity) > 0) {
            foreach ($shipping_setting_data as $shipping_method_id => $shipping_data) {
                foreach ($grouped_quantity as $group_key => $data) {

                    $product_type_variant_id = explode('_', $group_key)[0];
                    $item_id = explode('_', $group_key)[1];

                    if (isset($shipping_method_best[$shipping_method_id][$product_type_variant_id][$item_id])) {
                        continue;
                    }

                    $flag_suitable = false;

                    /*check priority location->product_type_variant_id->product_type_id*/
                    foreach ($priority_set_shipping_method as $key => $priority) {
                        if ($priority['product_type_variant_id'] == 'original') {
                            $priority['product_type_variant_id'] = $product_type_variant_id;
                        }

                        if ($priority['product_type_id'] == 'original') {
                            $priority['product_type_id'] = $data['product_type_id'];
                        }

                        if (isset($shipping_data[$priority['location']][$priority['product_type_variant_id']][$priority['product_type_id']]['rate'])) {
                            $shipping_configs_rate = $shipping_data[$priority['location']][$priority['product_type_variant_id']][$priority['product_type_id']]['rate'];

                            $this->_calculatePriceShipping($shipping_configs_rate, $data['quantity'], $estimate, $shipping_method_id, $product_type_variant_id, $flag_suitable, $data['tax_value'], $item_id, $shipping_method_best);

                            if ($flag_suitable) {
                                break;
                            }
                        }
                    }

                    if (!$flag_suitable) {
                        $shipping_method_fail[] = $shipping_method_id;
                    }
                }
            }
        }

        /* process delivery time */
        foreach ($shipping_setting_data as $shipping_method_id => $shipping_data) {
            if(count($grouped_delivery) < 1) {
                $shipping_method_fail[] = $shipping_method_id;
                continue;
            }

            foreach ($grouped_delivery as $group_key => $data) {
                $product_type_variant_id = explode('_', $group_key)[0];

                $flag_suitable_delivery = false;
                /*check priority location->product_type_variant_id->product_type_id*/
                foreach ($priority_set_shipping_method as $key => $priority) {
                    if ($priority['product_type_variant_id'] == 'original') {
                        $priority['product_type_variant_id'] = $product_type_variant_id;
                    }

                    if ($priority['product_type_id'] == 'original') {
                        $priority['product_type_id'] = $data['product_type_id'];
                    }

                    if (isset($shipping_data[$priority['location']][$priority['product_type_variant_id']][$priority['product_type_id']]['delivery'])) {
                        $shipping_configs_delivery = $shipping_data[$priority['location']][$priority['product_type_variant_id']][$priority['product_type_id']]['delivery'];
                        $this->_calculateDeliveryShipping($shipping_configs_delivery, $estimate, $shipping_method_id, $flag_suitable_delivery);

                        if ($flag_suitable_delivery) {
                            break;
                        }
                    }
                }

                if (!$flag_suitable_delivery) {
                    $shipping_method_fail[] = $shipping_method_id;
                }
            }
        }

        foreach ($shipping_method_best as $shipping_method_id => $shipping_method) {
            if (in_array($shipping_method_id, $shipping_method_fail)) {
                unset($shipping_method_best[$shipping_method_id]);
            }
        }

        $amount_semitest = 0;
        $amount_tax_semitest = 0;
        $price_semitest_by_item = [];

        if ($flag_semi_test && isset($cart_items)) {
            foreach ($cart_items as $item) {
                if ($item->isSemitest()) {
                    $data_shipping_semitest = OSC::helper('catalog/product')->getPriceShippingSemitest();
                    $shipping_price = $data_shipping_semitest['shipping_price'] ?? 0;
                    $shipping_plus_price = $data_shipping_semitest['shipping_plus_price'] ?? 0;
                    $amount_item_semitest_shipping = OSC::helper('catalog/common')->floatToInteger($shipping_price) + OSC::helper('catalog/common')->floatToInteger($shipping_plus_price) * ($item->data['quantity'] - 1);
                    $price_semitest_by_item[array_key_first($shipping_setting_data)][$item->getId()] = $amount_item_semitest_shipping;
                    $amount_semitest += $amount_item_semitest_shipping;
                    $amount_tax_semitest += !empty($item->data['tax_value']) ?
                        intval(round($amount_item_semitest_shipping * $item->data['tax_value'] / 100)) :
                        0;
                }
            }
        }

        $shipping_methods = OSC::model('shipping/methods')->getCollection()->load();

        $items_shipping_info = $this->_calculateItemsShippingFee($shipping_method_best, $price_semitest_by_item);

        $rates = [];

        foreach ($estimate as $shipping_method_id => $_estimate) {
            if (in_array($shipping_method_id, $shipping_method_fail)) {
                continue;
            }

            $shipping_method = $shipping_methods->getItemByPK($shipping_method_id);

            $rates[] = [
                'key' => $shipping_method->data['shipping_key'],
                'title' => $shipping_method->data['shipping_name'],
                'amount' => $_estimate['price'] + $amount_semitest,
                'amount_tax' => $_estimate['price_tax'] + $amount_tax_semitest,
                'amount_semitest' => $amount_semitest,
                'amount_tax_semitest' => $amount_tax_semitest,
                'items_shipping_info' => $items_shipping_info[$shipping_method_id],
                'estimate_timestamp' => OSC::helper('catalog/common')->fetchEstimateTimeExceptWeekendDays($_estimate['total_time']),
                'processing_timestamp' => OSC::helper('catalog/common')->fetchEstimateTimeExceptWeekendDays($_estimate['process']),
                'is_default' => $shipping_method->data['is_default']
            ];
        }

        /* cart only product semi test*/
        if (count($rates) < 1 && $flag_semi_test) {
            $shipping_method_default = null;

            foreach ($shipping_methods as $shipping_method) {
                if ($shipping_method->isShippingMethodDefault()) {
                    $shipping_method_default = $shipping_method;
                    break;
                }
            }

            if (!isset($shipping_method_default)) {
                return [];
            }

            $rates[] = [
                'key' => $shipping_method_default->data['shipping_key'],
                'title' => $shipping_method_default->data['shipping_name'],
                'amount' => $amount_semitest,
                'amount_tax' => $amount_tax_semitest,
                'amount_semitest' => $amount_semitest,
                'items_shipping_info' => $items_shipping_info[array_key_first($shipping_setting_data)],
                'estimate_timestamp' => 0,
                'processing_timestamp' => 0,
                'is_default' => $shipping_method_default->data['is_default']
            ];
        }
        /* end */

        return $rates;
    }

    protected function _calculatePriceShipping($shipping_configs_rate, $quantity, &$estimate, $shipping_method_id, $product_type_variant_id, &$flag_suitable, $tax_value, $item_id, &$shipping_method_best){
        $price_max = 0;

        $buff = OSC::helper('catalog/common')->getBuffShipping($shipping_configs_rate, $quantity);
        $price = OSC::helper('catalog/common')->floatToInteger(floatval($buff['price']));

        if ($price_max < $price) {
            $price_max = $price;
        }

        $estimate[$shipping_method_id]['price'] += $price_max;
        $estimate[$shipping_method_id]['price_tax'] += !is_null($tax_value) ? $price_max * $tax_value / 100 : 0.0;

        $flag_suitable = true;

        if ($item_id != 0) {
            $shipping_method_best[$shipping_method_id][$product_type_variant_id][$item_id] = $price_max;
        }
    }

    protected function _calculateDeliveryShipping($shipping_configs_delivery, &$estimate, $shipping_method_id, &$flag_suitable_delivery) {
        $estimate_day = $shipping_configs_delivery['estimate_time'] ?? 0;
        $processing_day = $shipping_configs_delivery['process_time'] ?? 0;


        if ($estimate[$shipping_method_id]['total_time'] < ($estimate_day + $processing_day)) {
            $estimate[$shipping_method_id]['total_time'] = $estimate_day + $processing_day;
            $estimate[$shipping_method_id]['process'] = $processing_day;
            $estimate[$shipping_method_id]['estimate'] = $estimate_day;
        }

        $flag_suitable_delivery = true;

    }

    protected function _prioritySetMethodShipping($country_id, $province_id) {
        return
            [
                [
                    'location' => $country_id . '_' . $province_id,
                    'product_type_variant_id' => 'original',
                    'product_type_id' => 'original'
                ],
                [
                    'location' => $country_id . '_' . $province_id,
                    'product_type_variant_id' => '*',
                    'product_type_id' => 'original'
                ],
                [
                    'location' => $country_id . '_' . $province_id,
                    'product_type_variant_id' => '*',
                    'product_type_id' => '*'
                ],
                [
                    'location' => $country_id . '_*',
                    'product_type_variant_id' => 'original',
                    'product_type_id' => 'original'
                ],
                [
                    'location' => $country_id . '_*',
                    'product_type_variant_id' => '*',
                    'product_type_id' => 'original'
                ],
                [
                    'location' => $country_id . '_*',
                    'product_type_variant_id' => '*',
                    'product_type_id' => '*'
                ],
                [
                    'location' => '*_*',
                    'product_type_variant_id' => 'original',
                    'product_type_id' => 'original'
                ],
                [
                    'location' => '*_*',
                    'product_type_variant_id' => '*',
                    'product_type_id' => 'original'
                ],
                [
                    'location' => '*_*',
                    'product_type_variant_id' => '*',
                    'product_type_id' => '*'
                ],
            ];
    }

    protected function _calculateItemsShippingFee($shipping_method_best, $shipping_semitest) {
        $result = [];

        /* Shipping normal price per item */
        foreach ($shipping_method_best as $shipping_method_id => $items) {
            foreach ($items as $product_type_variant_id => $item) {
                foreach ($item as $_item_id => $price) {
                    $result[$shipping_method_id][$_item_id] = $price;
                }
            }
        }

        foreach ($shipping_semitest as $shipping_method_id => $items) {
            foreach ($items as $_item_id => $price) {
                $result[$shipping_method_id][$_item_id] = $price;
            }
        }

        return $result;
    }
}