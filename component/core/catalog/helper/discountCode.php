<?php

class Helper_Catalog_DiscountCode {

    protected $_used_checked = [];

    /**
     * 
     * @param string $discount_code
     * @param string $discount_type
     * @param float $discount_value
     * @param float $prerequisite_subtotal
     * @param float $prerequisite_quantity
     * @param array $prerequisite_customer_id
     * @param array $prerequisite_customer_group
     * @param array $prerequisite_product_id
     * @param array $prerequisite_variant_id
     * @param array $prerequisite_collection_id
     * @param array $entitled_product_id
     * @param array $entitled_variant_id
     * @param array $entitled_collection_id
     * @param array $prerequisite_country_code
     * @param float $prerequisite_shipping_rate
     * @param int $bxgy_prerequisite_quantity
     * @param int $bxgy_entitled_quantity
     * @param int $bxgy_discount_rate
     * @param int $bxgy_allocation_limit
     * @param bool $apply_across
     * @param bool $combine_flag
     * @param int $usage_limit
     * @param bool $once_per_customer
     * @param bool $auto_apply
     * @param int $active_timestamp
     * @param int $deactive_timestamp
     * @param bool $auto_generated
     * @return \Model_Catalog_Discount_Code
     */
    public function create(string $discount_code, string $discount_type, float $discount_value, float $prerequisite_subtotal, float $prerequisite_quantity, array $prerequisite_customer_id, array $prerequisite_customer_group, array $prerequisite_product_id, array $prerequisite_variant_id, array $prerequisite_collection_id, array $entitled_product_id, array $entitled_variant_id, array $entitled_collection_id, array $prerequisite_country_code, float $prerequisite_shipping_rate, int $bxgy_prerequisite_quantity, int $bxgy_entitled_quantity, int $bxgy_discount_rate, int $bxgy_allocation_limit, bool $apply_across, bool $combine_flag, int $usage_limit, bool $once_per_customer, bool $auto_apply, int $active_timestamp, int $deactive_timestamp, bool $auto_generated = false): Model_Catalog_Discount_Code {
        $data = [
            'auto_generated' => $auto_generated ? 1 : 0,
            'combine_flag' => $combine_flag ? 1 : 0,
            'apply_across' => $apply_across ? 1 : 0,
            'active_timestamp' => $active_timestamp,
            'deactive_timestamp' => $deactive_timestamp
        ];

        $data['discount_code'] = $discount_code;
        $data['discount_type'] = $discount_type;
        $data['usage_limit'] = $usage_limit;
        $data['once_per_customer'] = $once_per_customer ? 1 : 0;
        $data['auto_apply'] = $auto_apply ? 1 : 0;
        $data['prerequisite_customer_id'] = $prerequisite_customer_id;
        $data['prerequisite_customer_group'] = $prerequisite_customer_group;

        if (in_array($data['discount_type'], ['percent', 'fixed_amount'], true)) {
            $data['discount_value'] = $discount_value;
        }

        if (in_array($data['discount_type'], ['bxgy'], true)) {
            $data['entitled_product_id'] = $entitled_product_id;
            $data['entitled_variant_id'] = $entitled_variant_id;
            $data['entitled_collection_id'] = $entitled_collection_id;
            $data['bxgy_prerequisite_quantity'] = $bxgy_prerequisite_quantity;
            $data['bxgy_entitled_quantity'] = $bxgy_entitled_quantity;
            $data['bxgy_discount_rate'] = $bxgy_discount_rate;
            $data['bxgy_allocation_limit'] = $bxgy_allocation_limit;
        } else {
            $data['entitled_product_id'] = null;
            $data['entitled_variant_id'] = null;
            $data['entitled_collection_id'] = null;
            $data['bxgy_prerequisite_quantity'] = null;
            $data['bxgy_entitled_quantity'] = null;
            $data['bxgy_discount_rate'] = null;
            $data['bxgy_allocation_limit'] = null;
        }

        if (in_array($data['discount_type'], ['bxgy', 'percent', 'fixed_amount'], true)) {
            $data['prerequisite_product_id'] = $prerequisite_product_id;
            $data['prerequisite_variant_id'] = $prerequisite_variant_id;
            $data['prerequisite_collection_id'] = $prerequisite_collection_id;
        } else {
            $data['prerequisite_product_id'] = null;
            $data['prerequisite_variant_id'] = null;
            $data['prerequisite_collection_id'] = null;
        }

        if (in_array($data['discount_type'], ['free_shipping', 'percent', 'fixed_amount'], true)) {
            $data['prerequisite_subtotal'] = $prerequisite_subtotal;
            $data['prerequisite_quantity'] = $prerequisite_quantity;
        } else {
            $data['prerequisite_subtotal'] = null;
            $data['prerequisite_quantity'] = null;
        }

        if (in_array($data['discount_type'], ['free_shipping'], true)) {
            $data['prerequisite_country_code'] = $prerequisite_country_code;
            $data['prerequisite_shipping_rate'] = $prerequisite_shipping_rate;
        } else {
            $data['prerequisite_country_code'] = null;
            $data['prerequisite_shipping_rate'] = null;
            $data['combine_flag'] = 0;
        }

        $model = OSC::model('catalog/discount_code');

        $model->setData($data)->save();

        return $model;
    }

    public function genDiscountCodes($discount_codes = []) {
        $results = [];
        foreach ($discount_codes as $key => $discount_code) {
            if (!in_array($discount_code['discount_type'], ['percent', 'fixed_amount'], true)) {
                throw new Exception('Discount type is percent or fixed_amount');
            }
            $results[$key] = OSC::model('catalog/discount_code')->setData([
                'auto_generated' => 1,
                'discount_code' => OSC::helper('catalog/common')->genCodeUkey(),
                'discount_type' => $discount_code['discount_type'],
                'discount_value' => intval($discount_code['discount_value']) > 0 ? intval( $discount_code['discount_value']) : 1,
                'usage_limit' => intval($discount_code['usage_limit']) > 0 ? intval($discount_code['usage_limit']) : 1,
                'prerequisite_subtotal' => intval($discount_code['prerequisite_subtotal']),
                'deactive_timestamp' => $discount_code['deactive_timestamp'],
                'note' => $discount_code['note']
            ])->save();
        }

        return $results;
    }

    public function genDiscountCodesByMaster($discount_codes = []) {
        $discount_codes = $this->genDiscountCodes($discount_codes);

        $results = [];
        foreach ($discount_codes as $key => $discount_code) {
            if ($discount_code->data['discount_type'] == 'percent') {
                $discount_code_value = $discount_code->data['discount_value'] . '%';
            } else {
                $discount_code_value = OSC::helper('catalog/common')->formatPriceByInteger($discount_code->data['discount_value'], 'email_with_currency');
            }

            $results[$key] = [
                'value' => $discount_code_value,
                'code' => preg_replace('/^(.{4})(.{4})(.{4})$/', '\\1-\\2-\\3', $discount_code->data['discount_code']),
                'expire_time' => date('F d, Y, h:i A', $discount_code->data['deactive_timestamp'])
            ];
        }

        return $results;
    }

    public function isUsed(int $discount_code_id, string $email): bool {
        OSC::core('validate')->validEmail($email);

        $discount_code_id = intval($discount_code_id);

        if ($discount_code_id < 1) {
            return true;
        }

        if (!isset($this->_used_checked[$email])) {
            $this->_used_checked[$email] = true;

            try {
                $DB = OSC::core('database')->getReadAdapter();
                $DB->select('usage_id', OSC::model('catalog/discount_code_usage')->getTableName(), ['condition' => 'discount_code_id = :discount_code_id AND order_email = :email', 'params' => ['email' => $email, 'discount_code_id' => $discount_code_id]], null, 1, 'check_discount_code_used');

                $this->_used_checked[$email] = $DB->rowCount('check_discount_code_used') > 0;

                $DB->free('check_discount_code_used');
            } catch (Exception $ex) {
                
            }
        }

        return $this->_used_checked[$email];
    }

    public function addToUsed($discount_code, Model_Catalog_Order $order) {
        /* @var $discount_code Model_Catalog_Discount_Code */

        if (!($discount_code instanceof Model_Catalog_Discount_Code)) {
            $discount_code = OSC::model('catalog/discount_code')->loadByUkey($discount_code);
        }

        $usage = OSC::model('catalog/discount_code_usage');
        $usage->setData([
            'order_id' => $order->getId(),
            'code_auto_generated' => $discount_code->data['auto_generated'],
            'order_email' => $order->data['email'],
            'discount_code_id' => $discount_code->getId(),
            'discount_code' => $discount_code->data['discount_code'],
            'campaign' => $discount_code->data['campaign'],
            'added_timestamp' => time()
        ])->save();

        $discount_code->increment('usage_counter');
    }

    public function apply($discount_code, Model_Catalog_Cart $cart) {
        /* @var $discount_code Model_Catalog_Discount_Code */
        $discount_code = $this->verifyToApply($discount_code, $cart);

        $cart->removeDiscountCode();

        switch ($discount_code->data['discount_type']) {
            case 'percent':
                $this->_applyPercent($discount_code, $cart);
                break;
            case 'free_shipping':
                $this->_applyFreeShipping($discount_code, $cart);
                break;
            case 'fixed_amount':
                $this->_applyFixedAmount($discount_code, $cart);
                break;
        }
    }

    public function fetchDiscountValue($discount_code, Model_Catalog_Cart $cart) {
        /* @var $discount_code Model_Catalog_Discount_Code */

        $discount_code = $this->verifyToApply($discount_code, $cart);

        $cart->removeDiscountCode();

        switch ($discount_code->data['discount_type']) {
            case 'percent':
                return $this->_applyPercent($discount_code, $cart, true);
            case 'fixed_amount':
                return $this->_applyFixedAmount($discount_code, $cart, true);
            case 'free_shipping':
                return $this->_applyFreeShipping($discount_code, $cart, true);
        }
    }

    public function ableToApply($discount_code, Model_Catalog_Cart $cart) {
        try {
            $this->verifyToApply($discount_code, $cart);
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    public function verifyToApply($discount_code, Model_Catalog_Cart $cart) {
        /* @var $discount_code Model_Catalog_Discount_Code */

        if (!($discount_code instanceof Model_Catalog_Discount_Code)) {
            $discount_code = OSC::model('catalog/discount_code')->loadByUkey($discount_code);
        }

        if ($discount_code->data['active_timestamp'] > time() || ($discount_code->data['deactive_timestamp'] > 0 && $discount_code->data['deactive_timestamp'] < time())) {
            throw new Exception('This promo code is invalid or has expired. Please check again or contact us for immediate assistance.');
        }

        if ($discount_code->data['usage_limit'] > 0 && $discount_code->data['usage_counter'] >= $discount_code->data['usage_limit']) {
            throw new Exception('The discount code is reached to usage limit');
        }

        if ($discount_code->data['once_per_customer']) {
            if (!$cart->data['email']) {
                throw new Exception('Please fill contact info and shipping info before applying discount code');
            }

            if ($this->isUsed($discount_code->getId(), $cart->data['email'])) {
                throw new Exception('You already used the discount code before');
            }
        }

        if (count($discount_code->data['prerequisite_customer_id']) > 0) {
            if (!in_array($cart->data['customer_id'], $discount_code->data['prerequisite_customer_id'])) {
                throw new Exception('You do not allowed to use the discount code');
            }
        } else if (count($discount_code->data['prerequisite_customer_group']) > 0) {
            $groups = OSC::helper('catalog/common')->collectCustomerGroup();

            $able_to_use = false;

            foreach ($discount_code->data['prerequisite_customer_group'] as $group_key) {
                if (isset($groups[$group_key]) && call_user_func($groups[$group_key]['verifier'], []) === true) {
                    $able_to_use = true;
                    break;
                }
            }

            if (!$able_to_use) {
                throw new Exception('You do not allowed to use the discount code');
            }
        }

        switch ($discount_code->data['discount_type']) {
            case 'percent':
                $this->_verifyToApplyPercent($discount_code, $cart);
                break;
            case 'free_shipping':
                $this->_verifyToApplyFreeShipping($discount_code, $cart);
                break;
            case 'fixed_amount':
                $this->_verifyToApplyFixedAmount($discount_code, $cart);
                break;
            default:
                throw new Exception('Discount type is not supported');
        }

        return $discount_code;
    }

    protected function _verifyToApplyFreeShipping(Model_Catalog_Discount_Code $discount_code, Model_Catalog_Cart $cart) {

    }

    protected function _applyFreeShipping(Model_Catalog_Discount_Code $discount_code, Model_Catalog_Cart $cart, $fetch_discount_value_only = false) {

    }

    protected function _fetchLineItemPercent(Model_Catalog_Discount_Code $discount_code, Model_Catalog_Cart $cart) {
        $line_items = [];

        foreach ($cart->getLineItems() as $line_item) {
            if ($this->_verifyLineItemCondition($line_item, $discount_code->data['prerequisite_product_id'], $discount_code->data['prerequisite_variant_id'], $discount_code->data['prerequisite_collection_id'])) {
                $line_items[] = $line_item;
            }
        }

        return $line_items;
    }

    protected function _verifyToApplyPercent(Model_Catalog_Discount_Code $discount_code, Model_Catalog_Cart $cart) {
        $line_items = $this->_fetchLineItemPercent($discount_code, $cart);

        if (count($line_items) < 1) {
            throw new Exception("<{$discount_code->data['discount_code']}> discount code isn’t valid for the items in your cart");
        }

        if ($discount_code->data['prerequisite_subtotal'] > 0) {
            $subtotal = 0;

            foreach ($line_items as $line_item) {
                $subtotal += $line_item->data['price'] * $line_item->data['quantity'];
            }

            if ($subtotal < $discount_code->data['prerequisite_subtotal']) {
                $_prerequisite_subtotal = OSC::helper('catalog/common')->formatPriceByInteger($discount_code->data['prerequisite_subtotal'], 'email_without_currency');
                throw new Exception("The minimum total order value for the discount code <{$discount_code->data['discount_code']}> to be applied is {$_prerequisite_subtotal}");
            }
        } else if ($discount_code->data['prerequisite_quantity'] > 0) {
            $quantity = 0;

            foreach ($line_items as $line_item) {
                $quantity += $line_item->data['quantity'];
            }

            if ($quantity < $discount_code->data['prerequisite_quantity']) {
                throw new Exception("The minimum number of order's items for the discount code <{$discount_code->data['discount_code']}> to be applied is {$discount_code->data['prerequisite_quantity']}");
            }
        }
    }

    protected function _fetchLineItemFixedAmount(Model_Catalog_Discount_Code $discount_code, Model_Catalog_Cart $cart) {
        /* @var $line_item Model_Catalog_Cart_Item */

        $line_items = [];

        foreach ($cart->getLineItems() as $line_item) {
            if ($this->_verifyLineItemFixAmountCondition($line_item, $discount_code->data['prerequisite_product_id'], $discount_code->data['prerequisite_variant_id'], $discount_code->data['prerequisite_collection_id'])) {
                $line_items[] = $line_item;
            }
        }

        return $line_items;
    }

    protected function _verifyLineItemShipping(Model_Catalog_Discount_Code $discount_code, Model_Catalog_Cart $cart)
    {
        if ($discount_code->data['prerequisite_shipping'] === null) {
            return true;
        }
        $shipping_price_data = $cart->getShippingPriceData();
        return $discount_code->data['prerequisite_shipping'] === $shipping_price_data['key'];
    }

    protected function _verifyToApplyFixedAmount(Model_Catalog_Discount_Code $discount_code, Model_Catalog_Cart $cart) {
        /* @var $line_item Model_Catalog_Cart_Item */

        $line_items = $this->_fetchLineItemFixedAmount($discount_code, $cart);
        if (!$this->_verifyLineItemShipping($discount_code, $cart)) {
            foreach ($line_items as $line_item) {
                $line_item->removeDiscount();
            }
            throw new Exception("This promo code is not applicable to your chosen shipping method", Model_Catalog_Discount_Code::DISCOUNT_CODE_ERROR);
        }

        $line_item_ids = [];
        foreach ($line_items as $line_item) {
            $line_item_ids[] = $line_item->getId();
        }
        foreach ($cart->getLineItems() as $line_item) {
            if (in_array($line_item->getId(), $line_item_ids)) {
                continue;
            }
            $line_item->removeDiscount();
        }

        if (count($line_items) < 1) {
            throw new Exception("<{$discount_code->data['discount_code']}> discount code isn’t valid for the items in your cart");
        }

        if ($discount_code->data['prerequisite_subtotal'] > 0) {
            $subtotal = 0;

            foreach ($line_items as $line_item) {
                $subtotal += $line_item->data['price'] * $line_item->data['quantity'];
            }

            if ($subtotal < $discount_code->data['prerequisite_subtotal']) {
                $_prerequisite_subtotal = OSC::helper('catalog/common')->formatPriceByInteger($discount_code->data['prerequisite_subtotal'], 'email_without_currency');
                throw new Exception("The minimum total order value for the discount code <{$discount_code->data['discount_code']}> to be applied is {$_prerequisite_subtotal}");
            }
        } else if ($discount_code->data['prerequisite_quantity'] > 0) {
            $quantity = 0;

            foreach ($line_items as $line_item) {
                $quantity += $line_item->data['quantity'];
            }

            if ($quantity < $discount_code->data['prerequisite_quantity']) {
                throw new Exception("The minimum number of order's items for the discount code <{$discount_code->data['discount_code']}> to be applied is {$discount_code->data['prerequisite_quantity']}");
            }
        } else if ($discount_code->data['prerequisite_shipping']) {
            $shipping_price_data = $cart->getShippingPriceData();
            if ($discount_code->data['prerequisite_shipping'] !== $shipping_price_data['key']) {
                throw new Exception('This promo code is not applicable to your chosen shipping method', Model_Catalog_Discount_Code::DISCOUNT_CODE_ERROR);
            }
        }
    }

    /**
     * @param Model_Catalog_Discount_Code $discount_code
     * @param Model_Catalog_Cart $cart
     * @param false $fetch_discount_value_only
     * @return float|int
     *
     */
    protected function _applyPercent(
        Model_Catalog_Discount_Code $discount_code,
        Model_Catalog_Cart $cart,
        $fetch_discount_value_only = false
    ) {
        $total_discount = 0;
		$apply_type = 'entire_order';
        $line_items = [];
        $discount_shipping_price = 0;

        if (count($discount_code->data['prerequisite_product_id']) < 1 &&
            count($discount_code->data['prerequisite_variant_id']) < 1 &&
            count($discount_code->data['prerequisite_collection_id']) < 1
        ) {
            switch ($discount_code->data['prerequisite_type']) {
                case 'entire_order':
                    $apply_type = 'entire_order';
                    $total_discount = $cart->getSubtotal() * $discount_code->data['discount_value'] / 100;
                    break;
                case 'entire_order_include_shipping':
                    $apply_type = 'entire_order_include_shipping';
                    $total_discount = $cart->getSubtotal() * $discount_code->data['discount_value'] / 100;
                    if ($cart->getCarrier() && $cart->getCarrier()->getRate()->isRateDefault()) {
                        $discount_shipping_price = $cart->getShippingPrice() * $discount_code->data['discount_value'] / 100;
                    }
                    break;
                case 'shipping':
                    $apply_type = 'shipping';
                    if ($cart->getCarrier() && $cart->getCarrier()->getRate()->isRateDefault()) {
                        $discount_shipping_price = $cart->getShippingPrice() * $discount_code->data['discount_value'] / 100;
                    }
                    break;
            }
        } else {
            $apply_type = 'line_item';

            $line_items = $this->_fetchLineItemPercent($discount_code, $cart);

            foreach ($line_items as $idx => $line_item) {
                $line_item_discount = intval(($line_item->getAmount() / 100) * $discount_code->data['discount_value']);

                $total_discount += $line_item_discount;

                $line_items[$idx] = [
                    'model' => $line_item,
                    'discount_value' => $line_item_discount
                ];
            }
        }

        $maximum_amount = OSC::helper('catalog/common')->floatToInteger(floatval($discount_code->data['maximum_amount']));

        if ($maximum_amount > 0 && $total_discount > $maximum_amount) {
            $total_discount = $maximum_amount;
        }

        if ($fetch_discount_value_only) {
            return $total_discount;
        }

        $cart->addDiscountCode($discount_code->data['discount_code'], $discount_code->data['discount_value'], $total_discount, $apply_type, 'percent', false, $discount_shipping_price, $discount_code->data['prerequisite_product_id'], $discount_code->data['prerequisite_collection_id'], $discount_code->data['prerequisite_shipping'], $discount_code->data['campaign']);

        foreach ($line_items as $line_item) {
            $line_item['model']->addDiscount($discount_code->data['discount_code'], $line_item['discount_value'], 'percent');
        }
    }

    /**
     *
     * @param Model_Catalog_Cart $cart
     * @throws Exception
     */
    protected function _applyFixedAmount(Model_Catalog_Discount_Code $discount_code, Model_Catalog_Cart $cart, $fetch_discount_value_only = false) {
        $total_discount = 0;
        $apply_type = 'entire_order';
        $line_items = [];

        if (count($discount_code->data['prerequisite_product_id']) < 1 && count($discount_code->data['prerequisite_variant_id']) < 1 && count($discount_code->data['prerequisite_collection_id']) < 1) {
            $total_discount = min($discount_code->data['discount_value'], $cart->getSubtotal());
        } else {
            $apply_type = 'line_item';

            $line_items = $this->_fetchLineItemFixedAmount($discount_code, $cart);
            if (!$this->_verifyLineItemShipping($discount_code, $cart)) {
                foreach ($cart->getLineItems() as $line_item) {
                    $line_item->removeDiscount();
                }
                return $total_discount;
            }

            usort($line_items, function ($a, $b) {
                if ($a->data['price']==$b->data['price']) {
                    return 0;
                };
                return $a->data['price'] > $b->data['price'] ? -1 : 1;
            });

            $discount_value = $discount_code->data['discount_value'];

            $total_quantity = 0;
            $counter = 0;
            foreach ($line_items as $idx => $line_item) {
                if ($discount_code->data['max_item_allow'] && $counter == $discount_code->data['max_item_allow']) {
                    unset($line_items[$idx]);
                    continue;
                }
                $total_quantity += intval($line_item->data['quantity']);
                ++$counter;
            }
            $avg_discount_price = OSC::helper('catalog/common')->floatToInteger(round(OSC::helper('catalog/common')->integerToFloat($discount_value) / $total_quantity, 2,PHP_ROUND_HALF_DOWN));

            $counter = 0;
            foreach ($line_items as $idx => $line_item) {
                $line_item_discount = min($avg_discount_price * $line_item->data['quantity'], $line_item->getAmount());
                if ($counter + 1 == count($line_items)) {
                    $line_item_discount = $discount_value - ($total_discount + $line_item->getAmount()) >= 0 ? $line_item->getAmount() : $discount_value - $total_discount;
                }

                $total_discount += $line_item_discount;

                $line_items[$idx] = ['model' => $line_item, 'discount_value' => $line_item_discount];
                ++$counter;
            }
        }

        if ($fetch_discount_value_only) {
            return $total_discount;
        }

        $cart->addDiscountCode($discount_code->data['discount_code'], $discount_code->data['discount_value'], $total_discount, $apply_type, 'fixed_amount', false, 0, $discount_code->data['prerequisite_product_id'], $discount_code->data['prerequisite_collection_id'], $discount_code->data['prerequisite_shipping'], $discount_code->data['campaign']);

        $line_item_ids = [];
        foreach ($line_items as $line_item) {
            $line_item_ids[] = $line_item['model']->getId();
            $line_item['model']->addDiscount($discount_code->data['discount_code'], $line_item['discount_value'], 'fixed_amount');
        }

        foreach ($cart->getLineItems() as $line_item) {
            if (in_array($line_item->getId(), $line_item_ids)) {
                continue;
            }
            $line_item->removeDiscount();
        }
    }

    /**
     * @param $line_item
     * @param $product_ids
     * @param $variant_ids
     * @param $collection_ids
     * @return bool
     */
    protected function _verifyLineItemCondition($line_item, $product_ids, $variant_ids, $collection_ids) {
        if (count($product_ids) < 1 && count($variant_ids) < 1 && count($collection_ids) < 1) {
            return true;
        }

        if (count($collection_ids) > 0) {
            return count(array_intersect($this->_getProductCollectionIds($line_item->getProduct()), $collection_ids)) > 0;
        }

        $skip_product_ids = [];

        foreach ($variant_ids as $idx => $variant_id) {
            $variant_id = explode(':', $variant_id);
            $skip_product_ids[] = $variant_id[0];
            $variant_ids[$idx] = $variant_id[1];
        }

        if (count($variant_ids) > 0 && in_array($line_item->data['variant_id'], $variant_ids)) {
            return true;
        }

        return in_array($line_item->data['product_id'], array_diff($product_ids, array_unique($skip_product_ids)));
    }

    /**
     * @param $line_item
     * @param $product_ids
     * @param $variant_ids
     * @param $collection_ids
     * @return bool
     */
    protected function _verifyLineItemFixAmountCondition($line_item, $product_ids, $variant_ids, $collection_ids) {
        if (count($product_ids) < 1 && count($variant_ids) < 1 && count($collection_ids) < 1) {
            return true;
        }

        if (count($collection_ids) > 0) {
            $rs = count(array_intersect($this->_getProductCollectionIds($line_item->getProduct()), $collection_ids)) > 0;
            if ($rs) {
                return true;
            }
        }

        $skip_product_ids = [];

        foreach ($variant_ids as $idx => $variant_id) {
            $variant_id = explode(':', $variant_id);
            $skip_product_ids[] = $variant_id[0];
            $variant_ids[$idx] = $variant_id[1];
        }

        if (count($variant_ids) > 0 && in_array($line_item->data['variant_id'], $variant_ids)) {
            return true;
        }

        return in_array($line_item->data['product_id'], array_diff($product_ids, array_unique($skip_product_ids)));
    }

    protected function _getProductCollectionIds($product) {
        static $catalog_collections = null;

        if ($catalog_collections === null) {
            $catalog_collections = OSC::model('catalog/collection')->getCollection();
            $catalog_collections->addCondition('collect_method', Model_Catalog_Collection::COLLECT_AUTO)->load();
        }

        $collection_ids = $product->data['collection_ids'];

        foreach ($catalog_collections as $catalog_collection) {
            if ($catalog_collection->productIsInCollection($product)) {
                $collection_ids[] = $catalog_collection->getId();
            }
        }

        return array_unique($collection_ids);
    }

    public function fetchBuyMoreGetMoreData() {
        $discount_codes = OSC::model('catalog/discount_code')->getCollection()->loadAutoApplyCode();

        if ($discount_codes->length() > 0) {
            foreach ($discount_codes as $discount_code) {
                if (!in_array($discount_code->data['discount_type'], ['percent', 'fixed_amount'])) {
                    continue;
                }

                if (count($discount_code->data['prerequisite_product_id']) > 0 || count($discount_code->data['prerequisite_variant_id']) > 0 || count($discount_code->data['prerequisite_collection_id']) > 0) {
                    continue;
                }

                if ($discount_code->data['once_per_customer']) {
                    continue;
                }

                if (count($discount_code->data['prerequisite_customer_id']) > 0 || count($discount_code->data['prerequisite_customer_group']) > 0) {
                    continue;
                }

                if ($discount_code->data['prerequisite_subtotal'] <= 0 && $discount_code->data['prerequisite_quantity'] <= 0) {
                    continue;
                }

                $auto_apply_discount_code[] = [
                    'discount_code' => $discount_code->data['discount_code'],
                    'discount_type' => $discount_code->data['discount_type'],
                    'discount_value' => $discount_code->data['discount_type'] == 'fixed_amount' ? OSC::helper('catalog/common')->integerToFloat($discount_code->data['discount_value']) : $discount_code->data['discount_value'],
                    'condition_type' => $discount_code->data['prerequisite_subtotal'] > 0 ? 'subtotal' : 'quantity',
                    'condition_value' => $discount_code->data['prerequisite_subtotal'] > 0 ? OSC::helper('catalog/common')->integerToFloat($discount_code->data['prerequisite_subtotal']) : $discount_code->data['prerequisite_quantity']
                ];
            }
        }

        if (count($auto_apply_discount_code) > 0) {
            usort($auto_apply_discount_code, function($a, $b) {
                if ($a['condition_type'] != $b['condition_type']) {
                    return $a['condition_type'] == 'quantity' ? -1 : 1;
                }

                if ($a['condition_value'] != $b['condition_value']) {
                    return ($a['condition_value'] < $b['condition_value']) ? -1 : 1;
                }

                if ($a['discount_type'] != $b['discount_type']) {
                    return $a['discount_type'] == 'fixed_amount' ? 1 : -1;
                }

                if ($a['discount_value'] != $b['discount_value']) {
                    return ($a['discount_value'] < $b['discount_value']) ? -1 : 1;
                }

                return 0;
            });
        }

        return $auto_apply_discount_code;
    }

    /**
     * @param int $length
     * @return string
     */
    public function genDiscountCode(int $length = 12) {
        if ($length < 8) {
            return OSC::randKey($length,7);
        } elseif ($length >= 8 && $length <= 10) {
            $key = substr(time(), -$length);
        } else {
            $key = time() . OSC::randKey($length - 10,2);
        }
        $assign_data = [
            '0' => ['A', 'B', 'C', '0'],
            '1' => ['D', 'E', 'F', '1'],
            '2' => ['G', 'H', 'I', '2'],
            '3' => ['J', 'K', 'L', '3'],
            '4' => ['M', 'N', 'O', '4'],
            '5' => ['P', 'Q', 'R', '5'],
            '6' => ['S', 'T', 'U', '6'],
            '7' => ['V', 'W', '7'],
            '8' => ['X', 'Y', '8'],
            '9' => ['Z', '9']
        ];

        foreach ($assign_data as $key_replace => $value) {
            $r = array_rand($value,1);
            $key = str_replace($key_replace, $value[$r], $key);
        }

        return $key;
    }
}
