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
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Model_Catalog_Cart extends Abstract_Core_Model {

    protected $_table_name = 'catalog_cart';
    protected $_pk_field = 'cart_id';

    /**
     *
     * @var Model_Catalog_Cart_Item_Collection
     */
    protected $_line_item_collection = null;

    /**
     *
     * @var Helper_Catalog_Shipping_Carrier
     */
    protected $_carrier = null;

    /**
     *
     * @var Model_Catalog_Customer
     */
    protected $_customer = null;

    const SECRET_KEY = 'asdr345f23312452352345';

    const CUSTOM_PRICE_DATA_KEYS = [
      'buy_design',
      'mug_size',
      'addon_services'
    ];

    public function getCheckoutBtn() {
        $buttons = OSC::core('observer')->dispatchEvent('catalog/collect_checkout_btn', $this);

        if (!is_array($buttons)) {
            $buttons = [];
        }

        $buttons = array_map(function($button) {
            return trim(strval($button));
        }, $buttons);
        $buttons = array_filter($buttons, function($button) {
            return $button !== '';
        });

        array_unshift($buttons, OSC::helper('frontend/template')->build('catalog/cart/checkout_btn', ['cart' => $this]));

        return $buttons;
    }

    public function getCheckoutPayPalBtn() {
        $buttons = OSC::core('observer')->dispatchEvent('catalog/collect_checkout_btn', $this);

        if (!is_array($buttons)) {
            $buttons = [];
        }

        $buttons = array_map(function($button) {
            return trim(strval($button));
        }, $buttons);
        $buttons = array_filter($buttons, function($button) {
            return $button !== '';
        });

        return $buttons;
    }

    public function getDetailUrl() {
        return OSC::helper('catalog/common')->getCartDetailUrl();
    }

    public function getRecoveryUrl($discount_code = null) {
        return OSC_FRONTEND_BASE_URL . '/cart-' . $this->getUkey() . (($discount_code instanceof Model_Catalog_Discount_Code) ? ('.' . $discount_code->getUkey()) : '');
    }

    public function placeOrder(Abstract_Catalog_Payment $payment_method, array $extra_data = []): Model_Catalog_Order {
        $this->verifyShippingLine();

        if ($this->getLineItems()->length() < 1) {
            throw new Exception('Cannot create order without line items');
        }

        $discount_codes = $this->getDiscountCodes();

        if (count($discount_codes) > 0) {
            /* @var $discount_code_model Model_Catalog_Discount_Code_Collection */
            $discount_code_collection = OSC::model('catalog/discount_code')->getCollection()->loadByUkey(array_keys($discount_codes));

            foreach ($discount_codes as $code => $entry) {
                /* @var $discount_code_model Model_Catalog_Discount_Code */
                $discount_code_model = $discount_code_collection->getItemByUkey($code);

                if ($discount_code_model) {
                    $entry['info'] = $discount_code_model->getInfo();
                    $discount_codes[$code] = $entry;
                }
            }
        }

        $line_items = [];

        /* @var $line_item Model_Catalog_Cart_Item */
        $need_update_cart = false;
        foreach ($this->getLineItems() as $line_item) {
            if ($line_item->isCrossSellMode()) {
                $line_items[$line_item->getId()] = OSC::helper('crossSell/common')->getDataToSaveOrderItem($line_item);
                continue;
            }

            $line_item->_validateAddonService();

            $item_data = [
                'title' => $line_item->getProduct()->getProductTitle(),
                'product_type' => $line_item->isCampaignMode() ? $line_item->getCampaignData()['product_type'] : $line_item->getProduct()->data['product_type'],
                'product_type_variant_id' => $line_item->getProductTypeVariantId(),
                'options' => $line_item->getVariant()->getOptions(),
                'tax_value' => $this->getTaxValue(),
                'discount' => $line_item->getDiscount(),
                'additional_data' => $line_item->data['additional_data'],
                'fulfilled_quantity' => 0,
                'refunded_quantity' => 0,
                'other_quantity' => 0,
            ];

            $columns = [
                'product_id',
                'variant_id',
                'sku',
                'vendor',
                'price',
                'tax_value',
                'cost',
                'quantity',
                'require_shipping',
                'require_packing',
                'weight',
                'weight_unit',
                'keep_flat',
                'dimension_width',
                'dimension_height',
                'dimension_length'
            ];

            foreach ($columns as $key) {
                $item_data[$key] = $line_item->data[$key];
            }

            $campaign_data = $line_item->getCampaignData();

            if ($campaign_data) {
                $item_data['options'] = [['title' => 'Product type', 'value' => $campaign_data['product_type']['title']]];
                foreach ($campaign_data['options'] as $option) {
                    $item_data['options'][] = [
                        'title' => $option['title'],
                        'value' => $option['value']['title'],
                        'key' => $option['value']['key']
                    ];
                }
            }

            $pack_data = $line_item->getPackData();
            if ($pack_data) {
                $item_data['other_quantity'] = $pack_data['quantity'];
            }
            $item_data['additional_data'] = $line_item->data['additional_data'];

            $custom_price_data = $line_item->data['custom_price_data'];

            if (isset($custom_price_data['buy_design']) && !empty($custom_price_data['buy_design'])) {
                $custom_price_data['buy_design']['ukey'] = $line_item->data['ukey'];
            }

            $item_data['custom_price_data'] = $custom_price_data;
            $item_data['additional_data']['cart_item_id'] = $line_item->getId();
            $item_data['additional_data']['collections'] = Observer_Klaviyo_Common::_getListCollectionTitle($line_item->getProduct());
            $item_data['additional_data']['product_tags'] = $line_item->getProduct()->getListProductTagsWithoutRootTag(true, true);

            $line_items[$line_item->getId()] = $item_data;
        }

//        $this->calculateCustomerId();

        $ab_test = OSC::getABTestKey(true);

        $new_referer = OSC::helper('report/common')->getReferer();

        if ($new_referer) {
            $client_info = $this->data['client_info'];
            $client_info['referer'] = $new_referer;

            $this->setData('client_info', $client_info);
        }

        $client_referer = isset($this->data['client_info']['referer']) && is_array($this->data['client_info']['referer']) ? $this->data['client_info']['referer']['host'] : null;
        $client_country = isset($this->data['client_info']['location']) && is_array($this->data['client_info']['location']) ? $this->data['client_info']['location']['country_code'] : null;
        $client_device_type = $this->data['client_info']['device_type'];
        $client_browser = $this->data['client_info']['browser'];
        $sref_id = isset($this->data['client_info']['DLS_SALE_REF']) && is_array($this->data['client_info']['DLS_SALE_REF']) ? intval($this->data['client_info']['DLS_SALE_REF']['id']) : null;

        if ($sref_id < 1) {
            $sref_id = null;
        }

        $additional_data = [];
        if (OSC::cookieGet('currency_code') !== 'USD') {
            $additional_data['estimated_price_by_customer_currency'] = OSC::helper('catalog/common')->formatPrice(OSC::helper('catalog/common')->integerToFloat($this->getTotal()), 'html_with_currency', true);
        }
        $additional_data['cart_added_timestamp'] = $this->data['added_timestamp'];

        $cartCustomPriceData = OSC::helper('addon/service')->updateCartCustomPriceData($this);
        $this->setData([
            'custom_price_data' => $cartCustomPriceData
        ])->save();

        // Because Express Shipping dont't support Order Addon (Delete if Express Ship support)
        $shipping_rates = [];
        $rates = OSC::helper('dls/catalog_checkout')->getShippingEstimate();
        foreach($rates as $rate) {
            $shipping_price_data = [
                'price' => $rate->getAmount(),
                'price_tax' => $rate->getAmountTax()
            ];
            $total = $this->getTotal(true, false, true, false, $shipping_price_data);
            $tax_price = $total - $this->getTotal(true, false, false, false, $shipping_price_data);
            $tax_price = $tax_price > 0 ? $tax_price : 0;

            $shipping_rates[] = [
                'key' => $rate->getKey(),
                'title' => $rate->getTitle(),
                'shipping_price' => $rate->getAmount(),
                'tax_price' => $tax_price,
                'total' => $total
            ];
        }
        ////////////////

        $order = OSC::helper('catalog/order')->place(
            $this->getId(),
            $this->getUkey(),
            $this->data['email'],
            $this->data['currency_code'],
            $this->getShippingAddress(),
            $this->getBillingAddress(),
            $line_items,
            $this->getCarrier()->getRate(),
            ['taxes' => $this->data['taxes'], 'tax_price' => $this->getTaxPrice()],
            $discount_codes,
            0,
            0,
            $payment_method,
            'open', // order_status
            !empty($this->data['custom_price_data']) ? $this->data['custom_price_data'] : [],
            $this->data['client_info'],
            $sref_id,
            $ab_test,
            $client_referer,
            $client_country,
            $client_device_type,
            $client_browser,
            true,
            [$this, 'placeOrderCallbackBeforeCommit'],
            $additional_data,
            0,
            $extra_data,
            $shipping_rates
        );

        if (OSC::registry(Helper_Catalog_Payment::$_payment_order_id)) {
            return $order;
        }

        $this->delete();

        return $order;
    }

    public function placeOrderCallbackBeforeCommit(Model_Catalog_Order $order) {
        $line_items = [];

        /* @var $line_item Model_Catalog_Order_Item */
        foreach ($order->getLineItems() as $line_item) {
            $line_items[] = [
                'title' => $line_item->data['title'],
                'options' => $line_item->getVariantOptionsText(),
                'sku' => $line_item->data['sku'],
                'price' => $line_item->getFloatPrice(),
                'amount' => $line_item->getFloatAmount(),
                'quantity' => $line_item->data['quantity']
            ];
        }

        //Calculate discount
        $discountPrice = $order->getFloatTotalDiscountPrice();
        $discountCode = $order->getDiscountCodes();

        //Calculate buy design as extra item
        $listBuyDesign = $order->getBuyDesign();
        $buyDesignPrice = $order->getFloatBuyDesignPrice();
        if (!empty($listBuyDesign)) {
            $quantity = is_array($listBuyDesign) && !empty($listBuyDesign) ? count($listBuyDesign) : 0;
            $line_items[] = [
                'title' => "Buy $quantity Design",
                'options' => '',
                'sku' => '',
                'price' => $buyDesignPrice,
                'amount' => $buyDesignPrice,
                'quantity' => 1,
            ];
        }

        if ($order->getTipPrice()) {
            $line_items[] = [
                'title' => "Tip",
                'options' => '',
                'sku' => 'tip',
                'price' => $order->getFloatTipPrice(),
                'amount' => $order->getFloatTipPrice(),
                'quantity' => 1,
            ];
        }

        //Total price has been calculated buy design price
        $total_price = $order->getFloatTotalPrice();

        $extra_data = [
            'remember_account' => $order->data['payment_method']['remember_account'] ?? 0
        ];

        if (!empty($order->data['payment_data']['payment_intent_id'])) {
            $extra_data['payment_intent_id'] = $order->data['payment_data']['payment_intent_id'];
        }

        if (!empty($order->data['payment_data']['payment_method'])) {
            $extra_data['payment_method'] = $order->data['payment_data']['payment_method'];
        }

        $payment_info = OSC::helper('catalog/payment')->makeTransaction(
            $order->getPayment(),
            $order->data['currency_code'],
            $total_price,
            $line_items,
            [
                'price' => $order->getFloatShippingPriceWithoutDiscount(),
                'title' => $order->getShippingMethodTitle()
            ],
            [['title' => 'Tax', 'price' => $order->getFloatTaxPrice()]],
            [
                'price' => $discountPrice,
                'codes' => $discountCode
            ],
            [
                'currency_code' => $order->data['currency_code'],
                'subtotal' => $order->getFloatSubtotalWithoutDiscount() + $buyDesignPrice + $order->getFloatTipPrice(),
                'discount' => $discountPrice,
                'shipping' => $order->getFloatShippingPriceWithoutDiscount(),
                'tax' => $order->getFloatTaxPrice(),
                'total' => $total_price
            ],
            $order->getShippingAddress(true),
            $order->getBillingAddress(true, true),
            'CART-' . $this->getUkey() . '/' . time(), // $invoice_number
            $order->data['email'],
            $extra_data
        );

        if (OSC::registry(Helper_Catalog_Payment::$_payment_order_id)) {
            return;
        }

        if (is_array($payment_info) && isset($payment_info['payment_data'])) {
            try {
                OSC::core('database')->getAdapter('payment_transaction_recheck')->insert(Cron_Catalog_RecheckTransaction::TBL_NAME, [
                    'order_id' => $order->getId(),
                    'cart_ukey' => $order->data['cart_ukey'],
                    'transaction_data' => OSC::encode([
                        'payment_method' => $order->data['payment_method'],
                        'payment_data' => $payment_info['payment_data'],
                        'currency_code' => $order->data['currency_code'],
                        'total_price' => OSC::helper('catalog/common')->integerToFloat(intval($order->data['total_price']))
                    ]),
                    'state_code' => 0,
                    'requeue_counter' => 0,
                    'error_message' => null,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ]);
            } catch (Exception $ex) {

            }
        }

        foreach (['shipping_' => 'shipping_address', 'billing_' => 'billing_address'] as $data_prefix => $data_key) {
            if (is_array($payment_info[$data_key])) {
                foreach ($payment_info[$data_key] as $key => $value) {
                    if ($value) {
                        $order->setData($data_prefix . $key, $value);
                    }
                }
            }
        }

        if ($payment_info['email']) {
            $order->setData('email', $payment_info['email']);
        }

        if ($payment_info['payment_status'] == 'authorized') {
            $order->setData('payment_status', 'authorized');
        } else {
            $order->setData([
                'payment_status' => 'paid',
                'paid' => $order->data['total_price']
            ]);
        }

        $order->setData([
            'payment_data' => isset($payment_info['payment_data']) ? $payment_info['payment_data'] : [],
            'fraud_data' => isset($payment_info['fraud_data']) ? $payment_info['fraud_data'] : null
        ]);

        try {
            $order->save();
        } catch (Exception $ex) {
            //Notify when deadlock sql
            if (strpos($ex->getMessage(), 'Deadlock') !== false) {
                OSC::helper('core/telegram')->send(OSC::$base_url . '::place::406::deadlock::' . time() . '::' . OSC::encode($order->data));
            }
            throw new Exception($ex->getMessage());
        }
    }

    public function getShippingLineVerifyKey() {
        $keys = [];

        $line_items = [];

        foreach ($this->getLineItems() as $line_item) {
            $data = [];

            foreach (['quantity', 'price', 'require_shipping', 'require_packing', 'keep_flat', 'weight', 'weight_unit', 'dimension_width', 'dimension_height', 'dimension_length'] as $key) {
                $data[] = $line_item->data[$key];
            }

            $line_items[$line_item->getId()] = implode('|', $data);
        }

        ksort($keys);
        ksort($line_items);

        $keys['line_items'] = $line_items;
        $keys['address1'] = preg_replace('/[^\p{L}\d]/', '', $this->data['shipping_address1']);
        $keys['address2'] = preg_replace('/[^\p{L}\d]/', '', $this->data['shipping_address2']);
        $keys['city'] = preg_replace('/[^\p{L}\d]/', '', $this->data['shipping_city']);
        $keys['province'] = preg_replace('/[^\p{L}\d]/', '', $this->data['shipping_province']);
        $keys['country_code'] = preg_replace('/[^\p{L}\d]/', '', $this->data['shipping_country_code']);

        return md5(OSC::encode($keys));
    }

    public function removeDiscountCode()
    {
        $this->register('discount_codes', []);
        foreach ($this->getLineItems() as $line_item) {
            $line_item->removeDiscount();
        }
        return $this;
    }

    /**
     * @param $discount_code
     * @param $discount_value
     * @param $discount_price
     * @param $apply_type
     * @param $discount_type
     * @param $combine
     * @return $this
     */
    public function addDiscountCode($discount_code, $discount_value, $discount_price, $apply_type = null, $discount_type = null, $combine = false, $discount_shipping_price = 0, $prerequisite_product_id = null, $prerequisite_collection_id = null, $shipping_key = null, $campaign = null) {
        $discount_codes = $this->registry('discount_codes');

        if (!is_array($discount_codes)) {
            $discount_codes = [];
        }

        $apply_type = strtolower(trim($apply_type));

        if (!in_array($apply_type, ['shipping', 'line_item', 'entire_order', 'entire_order_include_shipping'])) {
            $apply_type = 'entire_order';
        }

        if ($apply_type != 'shipping') {
            $combine = false;

            foreach ($discount_codes as $data) {
                if (!$data['combine']) {
                    unset($discount_codes[$data['discount_code']]);
                }
            }
        } else {
            foreach ($discount_codes as $data) {
                if ($data['discount_type'] == 'shipping') {
                    unset($discount_codes[$data['discount_code']]);
                }
            }
        }
        $discount_codes = [];
        $discount_codes[$discount_code] = [
            'discount_code' => $discount_code,
            'discount_value' => intval($discount_value),
            'discount_price' => intval($discount_price),
            'discount_shipping_price' => intval($discount_shipping_price),
            'discount_shipping_key' => $shipping_key,
            'description' => $this->getDescriptionDiscountCode($discount_type, $discount_value, $apply_type, $prerequisite_product_id, $prerequisite_collection_id),
            'apply_type' => $apply_type,
            'discount_type' => $discount_type,
            'combine' => $combine,
            'campaign' => $campaign
        ];

        $this->register('discount_codes', $discount_codes);

        return $this;
    }

    private function getDescriptionDiscountCode($discount_type, $discount_value, $apply_type, $prerequisite_product_id, $prerequisite_collection_id = null)
    {
        $desc = '';
        switch ($apply_type) {
            case 'shipping':
                $desc = ' on shipping fee';
                break;
            case 'entire_order':
                $desc = ' on subtotal';
                break;
            case 'entire_order_include_shipping':
                $desc = ' on subtotal and shipping fee';
                break;
            case 'line_item':
                if ($prerequisite_product_id) {
                    $desc = ' on specific product';
                }

                if ($prerequisite_collection_id) {
                    if ($prerequisite_product_id) {
                        $desc = ' and';
                    }
                    $desc .= ' on specific collection';
                }
                break;
            default:
                $desc = '';
                break;
        }
        return ($discount_type === 'percent' ? $discount_value : OSC::helper('catalog/common')->integerToFloat($discount_value)) . ($discount_type === 'percent' ? '%' : '$') . $desc;
    }

    public function getFloatSubtotalDiscountPrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->getSubtotalDiscountPrice());
    }

    public function getItemsDiscountPrice() {
        $cart_subtotal_discount = 0;

        foreach ($this->getDiscountCodes() as $discount_info) {
            if ($discount_info['apply_type'] == 'line_item') {
                $cart_subtotal_discount += $discount_info['discount_price'];
            }
        }

        return $cart_subtotal_discount;
    }

    public function getSubtotalDiscountPrice() {
        $cart_subtotal_discount = 0;

        foreach ($this->getDiscountCodes() as $discount_info) {
            if ($discount_info['discount_type'] != 'free_shipping') {
                $cart_subtotal_discount += $discount_info['discount_price'];
            }
        }

        return $cart_subtotal_discount;
    }

    public function getBuyDesign() {
        $cartCustomPriceData = $this->data['custom_price_data'];
        $cartCustomPriceData = is_array($cartCustomPriceData) && !empty($cartCustomPriceData) ? $cartCustomPriceData : [];
        $cartCustomPriceData['buy_design'] = isset($cartCustomPriceData['buy_design']) && is_array($cartCustomPriceData['buy_design']) && !empty($cartCustomPriceData['buy_design']) ? $cartCustomPriceData['buy_design'] : [];

        return $cartCustomPriceData['buy_design'];
    }

    public function getFloatBuyDesignPrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->getBuyDesignPrice());
    }

    public function getTipPrice() {
        return isset($this->data['custom_price_data']['tip']) && intval($this->data['custom_price_data']['tip']) > 0 ?
            intval($this->data['custom_price_data']['tip']) : 0;
    }

    public function getFloatTipPrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->getTipPrice());
    }

    public function getFloatAddonServicePrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->getAddonServicePrice());
    }

    public function getBuyDesignPrice($reload = false) {
        $buyDesignPrice = 0;

        foreach ($this->getLineItems($reload) as $line_item) {
            $custom_price_data = $line_item->data['custom_price_data'];
            if (isset($custom_price_data['buy_design']['is_buy_design']) && $custom_price_data['buy_design']['is_buy_design'] == 1) {
                $buyDesignPrice += isset($custom_price_data['buy_design']['buy_design_price']) && !empty($custom_price_data['buy_design']['buy_design_price']) ? intval($custom_price_data['buy_design']['buy_design_price']) : 0;
            }
        }

        return $buyDesignPrice;
    }

    public function getAddonServicePrice($reload = false) {
        $addon_service_price = 0;

        foreach ($this->getLineItems($reload) as $line_item) {
            $addon_service_price += $line_item->getAddonServicePrice();
        }

        return $addon_service_price;
    }

    public function getFloatDiscountPrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->getDiscountPrice());
    }

    public function getEntireOrderDiscountPrice() {
        $discount = 0;

        foreach ($this->getDiscountCodes() as $discount_info) {
            if (!in_array($discount_info['apply_type'], ['entire_order', 'entire_order_include_shipping'])) {
                continue;
            }

            $discount += $discount_info['discount_price'];
        }

        return $discount;
    }

    /**
     * Get tax price of discount price (case type discount code is entire order)
     * @param boolean $reload
     * @return int
     */
    public function getTaxDiscountPrice(bool $reload, $shipping_price_data) {
        $discount_value = 0;
        $discount_shipping_value = 0;
        $tax_discount_price = 0;
        $last_discount = [];

        foreach ($this->getDiscountCodes() as $discount_info) {
            if (in_array($discount_info['apply_type'], ['entire_order', 'entire_order_include_shipping'])) {
                $discount_value = $discount_info['discount_value'];
            }
            if (in_array($discount_info['apply_type'], ['shipping', 'entire_order_include_shipping'])) {
                $discount_shipping_value = $discount_info['discount_value'];
            }
            $last_discount = $discount_info;
        }
        if ($shipping_price_data['price'] > 0) {
            $tax_discount_price += intval(ceil($shipping_price_data['price_tax'] * $discount_shipping_value / 100));
        }

        if ($last_discount && $last_discount['discount_type'] == 'percent') {
            /* @var $line_item Model_Catalog_Cart_Item */
            foreach ($this->getLineItems($reload) as $line_item) {
                $tax_value = $line_item->getTaxValue() ?? 0;
                $line_item_amount = $line_item->getAmount() + $line_item->getAddonServicePrice();
                $tax_discount_price += intval(ceil($discount_value * $line_item_amount * $tax_value / 10000));
            }
        } elseif ($last_discount && $last_discount['discount_type'] == 'fixed_amount') {
            $line_items = [];
            if ($last_discount['apply_type'] == 'entire_order') {
                $discount_value = min($last_discount['discount_value'], $this->getSubtotal());

                $total_discount = 0;
                $total_quantity = 0;

                $cart_line_items = $this->getLineItems($reload);
                $counter = 0;
                foreach ($cart_line_items as $idx => $line_item) {
                    if ($last_discount->data['max_item_allow'] && $counter == $last_discount->data['max_item_allow']) {
                        unset($cart_line_items[$idx]);
                        continue;
                    }
                    $total_quantity += intval($line_item->data['quantity']);
                    ++$counter;
                }
                $avg_discount_price = OSC::helper('catalog/common')->floatToInteger(round(OSC::helper('catalog/common')->integerToFloat($discount_value) / $total_quantity, 2,PHP_ROUND_HALF_DOWN));

                $counter = 0;
                foreach ($cart_line_items as $idx => $line_item) {
                    $line_item_amount = $line_item->getAmount() + $line_item->getAddonServicePrice();
                    $line_item_discount = min($avg_discount_price * $line_item->data['quantity'], $line_item_amount);
                    if ($counter + 1 == $cart_line_items->length()) {
                        $line_item_discount = $discount_value - ($total_discount + $line_item_amount) >= 0 ? $line_item_amount : $discount_value - $total_discount;
                    }

                    $total_discount += $line_item_discount;

                    $line_items[$idx] = ['model' => $line_item, 'discount_value' => $line_item_discount];
                    ++$counter;
                }
            }

            /* @var $line_item Model_Catalog_Cart_Item */
            foreach ($line_items as $line_item) {
                $tax_value = $line_item['model']->getTaxValue() ?? 0;
                $discount_on_line_item = $line_item['discount_value'];

                $tax_discount_price += intval(ceil($discount_on_line_item * $tax_value / 100));
            }
        }

        return $tax_discount_price;
    }

    /**
     *
     * @return array
     */
    public function getDiscountCodes() {
        $discount_codes = $this->registry('discount_codes');

        if (!is_array($discount_codes)) {
            $discount_codes = [];
        }

        return $discount_codes;
    }

    public function showDiscount() {
        foreach ($this->getDiscountCodes() as $discount_info) {
            if ($discount_info['discount_type'] === 'fixed_amount') {
                if ($discount_info['discount_shipping_key'] && $discount_info['discount_shipping_key'] != $this->getShippingPriceData()['key']) {
                    return 1;
                }
            } else if ($discount_info['discount_type'] === 'percent') {
                $is_default_rate = $this->getCarrier()->getRate()->isRateDefault();
                if ($is_default_rate === false) {
                    if (in_array($discount_info['apply_type'], ['shipping'])) {
                        return 1;
                    } elseif (in_array($discount_info['apply_type'], ['entire_order_include_shipping'])) {
                        return 2;
                    }
                }
            }
        }
        return 0;
    }

    /**
     *
     * @return array
     */
    public function getDiscountCodesCollection() {
        $collection = OSC::model('catalog/discount_code')->getCollection();

        $applied_discount_codes = array_keys($this->getDiscountCodes());

        if (count($applied_discount_codes) < 1) {
            return $collection;
        }

        return $collection->loadByUKey($applied_discount_codes);
    }

    public function getFloatTotal($get_shipping_price = true, $get_tax_price = true, $verify_shipping_line = true) {
        $total = $this->getTotal($get_shipping_price, false, $get_tax_price, $verify_shipping_line);

        return OSC::helper('catalog/common')->integerToFloat($total);
    }

    public function getTotal($get_shipping_price = true, $reload = false, $get_tax_price = true, $verify_shipping_line = true, $other_shipping_data = []) {
        $total = $this->getSubtotal($reload);
        $tax_sub_total = $this->getTaxSubtotal($reload);
        $shipping_price_data = empty($other_shipping_data) ? $this->getShippingPriceData($verify_shipping_line) : $other_shipping_data;

        $total -= $this->getEntireOrderDiscountPrice();
        $tax_sub_total -= $this->getTaxDiscountPrice($reload, $shipping_price_data);

        //Check if all discount make price negative
        $total = $total > 0 ? $total : 0;
        $tax_total = $tax_sub_total > 0 ? $tax_sub_total : 0.0;

        //Calculate buy_design price
        $total += $this->getBuyDesignPrice();
        $total += $this->getTipPrice();

        //Calculate shipping price
        if ($get_shipping_price) {

            if ($shipping_price_data['price'] > 0) {
                //Calculate tax shipping price
                $tax_total += $shipping_price_data['price_tax'];
                $total += $shipping_price_data['price'];
            }
        }

        if ($get_tax_price) {
            $total += intval(round($tax_total));
        }

        if ($get_shipping_price) {
            $total = $total - $this->getShippingDiscountPrice();
        }

        return $total > 0 ? $total : 0;
    }

    public function setCarrier($carrier) {
        if ($carrier instanceof Helper_Catalog_Shipping_Carrier) {
            $shipping_line = ['carrier' => $carrier->toArray()];
        } else {
            $carrier = false;
            $shipping_line = [];
        }

        $this->setData('shipping_line', $shipping_line)->save();

        $this->_carrier = $carrier;

        return $this;
    }

    /**
     *
     * @return $this->_carrier
     */
    public function getCarrier() {
        if ($this->_carrier === null) {
            if (!isset($this->data['shipping_line']) ||
                !is_array($this->data['shipping_line']) ||
                !isset($this->data['shipping_line']['carrier'])) {
                $this->_carrier = false;
            } else {
                $this->_carrier = new Helper_Catalog_Shipping_Carrier(
                    $this->data['shipping_line']['carrier']['key'],
                    $this->data['shipping_line']['carrier']['title'],
                    $this->data['shipping_line']['carrier']['ship_from'],
                    $this->data['shipping_line']['carrier']['rates']
                );
            }
        }

        return $this->_carrier;
    }

    public function getFloatShippingPrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->getShippingPrice());
    }

    public function getFloatTaxPrice($verify_shipping_line = true) {
        return OSC::helper('catalog/common')->integerToFloat($this->getTaxPrice($verify_shipping_line));
    }

    public function getTaxValue() {
        $tax_data = $this->getTaxData();

        return ($tax_data['tax_value'] > 0) ? intval($tax_data['tax_value']) : 0;
    }

    public function getTaxPrice($verify_shipping_line = true) {
        $tax_price = $this->getTotal(true, false, true, $verify_shipping_line) - $this->getTotal(true, false, false, $verify_shipping_line);

        return ($tax_price > 0) ? $tax_price : 0;
    }

    public function getTaxData() {
        return $this->data['taxes'];
    }

    public function getShippingPrice() {
        $price_data = $this->getShippingPriceData();

        return $price_data['price'] <= 0 ? 0 : $price_data['price'];
    }

    public function getFloatShippingDiscountPrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->getShippingDiscountPrice());
    }

    public function getShippingDiscountPrice() {
        $shipping_discount = 0;

        foreach ($this->getDiscountCodes() as $discount_info) {
            if (in_array($discount_info['apply_type'], ['free_shipping', 'shipping', 'entire_order_include_shipping'])) {
                $shipping_discount += $discount_info['discount_shipping_price'];
            }
        }

        return $shipping_discount;
    }

    public function getShippingPriceData($verify_shipping_line = true) {
        $data = [
            'key' => '',
            'title' => '',
            'service_key' => '',
            'method_key' => '',
            'price' => -1,
            'price_semitest' => -1,
            'compare_at_price' => -1,
            'discount' => [],
            'packages' => []
        ];

        try {
            if ($verify_shipping_line) {
                $this->verifyShippingLine();
            }
            $carrier = $this->getCarrier();
            if ($carrier) {
                $data['key'] = $carrier->getRate()->getKey();
                $data['title'] = $carrier->getRate()->getTitleWithCarrier();
                $data['price'] = $carrier->getRate()->getAmount();
                $data['price_tax'] = $carrier->getRate()->getAmountTax();
                $data['price_semitest'] = $carrier->getRate()->getAmountSemitest();
                $data['compare_at_price'] = $carrier->getRate()->getAmount();
                $data['packages'] = $this->data['shipping_line']['packages'];
            }
        } catch (Exception $ex) { }

        return $data;
    }

    public function calculateDiscount($throw_exception = false) {
        $this->removeDiscountCode();

        if (isset($this->data['discount_codes']) && is_array($this->data['discount_codes'])) {
            $discount_codes = $this->data['discount_codes'];

            if (count($discount_codes) > 0) {
                try {
                    $discount_codes = OSC::model('catalog/discount_code')->getCollection()->loadByUkey($discount_codes);

                    foreach ($discount_codes as $discount_code) {
                        try {
                            OSC::helper('catalog/discountCode')->apply($discount_code, $this);
                        } catch (Exception $ex) {
                            if ($throw_exception && $ex->getCode() == Model_Catalog_Discount_Code::DISCOUNT_CODE_ERROR) {
                                throw new Exception($ex->getMessage(), $ex->getCode());
                            }
                        }
                    }
                } catch (Exception $ex) {
                    if ($throw_exception && $ex->getCode() == Model_Catalog_Discount_Code::DISCOUNT_CODE_ERROR) {
                        throw new Exception($ex->getMessage(), $ex->getCode());
                    }
                }
            }
        }
    }

    public function getBillingAddress() {
        $address_data = [];

        foreach (array_keys(Helper_Core_Country::ADDRESS_FIELDS) as $address_field) {
            if (isset($this->data['billing_' . $address_field]) && $this->data['billing_' . $address_field] !== null && $this->data['billing_' . $address_field] !== false && $this->data['billing_' . $address_field] !== '') {
                $address_data[$address_field] = $this->data['billing_' . $address_field];
            }
        }

        return $address_data;
    }

    public function getShippingAddress() {
        $address_data = [];

        foreach (array_keys(Helper_Core_Country::ADDRESS_FIELDS) as $address_field) {
            if (isset($this->data['shipping_' . $address_field]) && $this->data['shipping_' . $address_field] !== null && $this->data['shipping_' . $address_field] !== false && $this->data['shipping_' . $address_field] !== '') {
                $address_data[$address_field] = $this->data['shipping_' . $address_field];
            }
        }

        return $address_data;
    }

    public function verifyShippingLine() {
        if (!isset($this->data['shipping_line']) || !is_array($this->data['shipping_line']) || !isset($this->data['shipping_line']['carrier'])) {
            throw new Exception('Please choose a shipping method');
        }

        if ($this->data['shipping_line']['verify_key'] != $this->getShippingLineVerifyKey()) {
            $this->setData('shipping_line', '')->save();
            throw new Exception('Your cart or shipping address has changed, please choose a new shipping method');
        }
    }

    public function getFloatTotalPriceWithoutDiscount(): float {
        return OSC::helper('catalog/common')->integerToFloat($this->getTotalPriceWithoutDiscount());
    }

    public function getTotalPriceWithoutDiscount($reload = false): int {
        $total = 0;

        foreach ($this->getLineItems($reload) as $line_item) {
            $total += intval($line_item->data['price']) * intval($line_item->data['quantity']);
        }

        if ($this->getCarrier()) {
            $total += $this->getCarrier()->getRate()->getAmount();
        }

        return $total;
    }

    /**
     *
     * @return integer
     */
    public function getQuantity() {
        $quantity = 0;

        foreach ($this->getLineItems() as $item) {
            $quantity += $item->data['quantity'];
        }

        return $quantity;
    }

    public function getFloatCompareAtSubtotal($reload = false) {
        return OSC::helper('catalog/common')->integerToFloat($this->getCompareAtSubtotal($reload));
    }

    public function getCompareAtSubtotal($reload = false) {
        $compare_at_subtotal = 0;

        /* @var $line_item Model_Catalog_Cart_Item */
        foreach ($this->getLineItems($reload) as $line_item) {
            $compare_at_subtotal += $line_item->data['quantity'] * ($line_item->data['compare_at_price'] > 0 ? $line_item->data['compare_at_price'] : $line_item->data['price']);
        }

        return $compare_at_subtotal;
    }

    public function getFloatSubtotal($reload = false) {
        return OSC::helper('catalog/common')->integerToFloat($this->getSubtotal($reload));
    }

    public function getSubtotal($reload = false) {
        $subtotal = 0;

        /* @var $line_item Model_Catalog_Cart_Item */
        foreach ($this->getLineItems($reload) as $line_item) {
            $subtotal += $line_item->getAmountWithDiscount() + $line_item->getAddonServicePrice();
        }

        return $subtotal;
    }

    public function getTaxSubtotal($reload = false) {
        $tax_subtotal = 0.0;

        /* @var $line_item Model_Catalog_Cart_Item */
        foreach ($this->getLineItems($reload) as $line_item) {
            $tax_subtotal += $line_item->getTaxAmountWithDiscount() + $line_item->getTaxAmountAddonService();
        }

        return $tax_subtotal;
    }

    public function getFloatSubtotalWithoutDiscount($reload = false) {
        return OSC::helper('catalog/common')->integerToFloat($this->getSubtotalWithoutDiscount($reload));
    }

    public function getSubtotalWithoutDiscount($reload = false) {
        return OSC::helper('catalog/cart')->getSubtotalWithoutDiscountOfCart($this, $reload);
    }

    public function getTaxSubtotalWithoutDiscount($reload = false) {
        $tax_subtotal = 0.0;

        /* @var $line_item Model_Catalog_Cart_Item */
        foreach ($this->getLineItems($reload) as $line_item) {
            $tax_subtotal += $line_item->getTaxAmount();
        }

        return $tax_subtotal;
    }

    /**
     *
     * @param boolean $reload
     * @return $this->_line_item_collection
     */
    public function getLineItems($reload = false) {
        if ($this->_line_item_collection === null || $reload) {
            $this->_line_item_collection = OSC::model('catalog/cart_item')->getCollection();

            if ($this->getId() > 0) {
                $this->_line_item_collection->addCondition('cart_id', $this->getId())->load();
                $this->_line_item_collection->preLoadModelData();

                foreach ($this->_line_item_collection as $line_item) {
                    $line_item->setCart($this);
                }
            }
        }

        return $this->_line_item_collection;
    }

    public function getBillingFullName() {
        return $this->data['billing_full_name'];
    }

    public function getBillingFirstName() {
        $full_name_segments = explode(' ', $this->data['billing_full_name'], 2);
        return isset($full_name_segments[0]) ? $full_name_segments[0] : 'null';
    }

    public function getBillingLastName() {
        $full_name_segments = explode(' ', $this->data['billing_full_name'], 2);
        return isset($full_name_segments[1]) ? $full_name_segments[1] : '';
    }

    public function getShippingFullName() {
        return $this->data['shipping_full_name'];
    }

    public function getShippingFirstName() {
        $full_name_segments = explode(' ', $this->data['shipping_full_name'], 2);
        return isset($full_name_segments[0]) ? $full_name_segments[0] : '';
    }

    public function getShippingLastName() {
        $full_name_segments = explode(' ', $this->data['shipping_full_name'], 2);
        return isset($full_name_segments[1]) ? $full_name_segments[1] : '';
    }

    public function getFirstName()
    {
        return $this->getBillingFirstName() ? $this->getBillingFirstName() : $this->getShippingFirstName();
    }

    public function getLastName()
    {
        return $this->getBillingLastName() ? $this->getBillingLastName() : $this->getShippingLastName();
    }

    public function getFullName()
    {
        return $this->data['billing_full_name'] ? $this->data['billing_full_name'] : $this->data['shipping_full_name'];
    }

    /**
     *
     * @param mixed $cart_items
     * @return $this
     */
    public function setCartItems($cart_items = null) {
        $this->_line_item_collection = $cart_items;
        return $this;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['shipping_line'])) {
            try {
                if (!is_array($data['shipping_line']) || !isset($data['shipping_line']['carrier'])) {
                    $data['shipping_line'] = [];
                } else {
                    if (!isset($data['shipping_country_code']) && !$this->getData('shipping_country_code', true)) {
                        $errors[] = 'Shipping address need setted before select a shipping method';
                    } else {
                        OSC::helper('catalog/checkout')->verifyShippingMethod($data['shipping_line']);

                        $data['shipping_line']['verify_key'] = $this->getShippingLineVerifyKey();
                        $data['shipping_line']['expire_timestamp'] = time() + (60 * 60);

                        $package_items = [];

                        foreach ($this->getLineItems() as $line_item) {
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

                        $data['shipping_line']['packages'] = OSC::helper('catalog/checkout')->calculatePackages($package_items);
                    }
                }
            } catch (Exception $ex) {
                $errors[] = $ex->getMessage();
            }
        }

        $skip_valid_contact_info = $this->registry('valid_contact_info');

        if (!$skip_valid_contact_info && $skip_valid_contact_info != 1) {
            if (isset($data['email'])) {
                $data['email'] = trim(strval($data['email']));

                try {
                    OSC::core('validate')->validEmail($data['email']);
                } catch (Exception $ex) {
                    $errors[] = $ex->getMessage();
                }
            }

            foreach (['shipping_' => 'Shipping address', 'billing_' => 'Billing address'] as $address_prefix => $address_title) {
                $address_data = [];

                foreach (array_keys(Helper_Core_Country::ADDRESS_FIELDS) as $address_field) {
                    if (isset($data[$address_prefix . $address_field])) {
                        $address_data[$address_field] = $data[$address_prefix . $address_field];
                    }
                }

                if ($address_prefix == 'billing_') {
                    $not_empty = false;

                    foreach (array_keys(Helper_Core_Country::ADDRESS_FIELDS) as $address_field) {
                        if (isset($address_data[$address_field])) {
                            if ($address_data[$address_field]) {
                                $not_empty = true;
                                break;
                            }
                        } else if ($this->getData($address_prefix . $address_field, true)) {
                            $not_empty = true;
                            break;
                        }
                    }

                    if (!$not_empty) {
                        continue;
                    }
                }

                if (count($address_data) > 0 && ((isset($address_data['country']) && $address_data['country']) || $this->getData($address_prefix . 'country', true))) {
                    try {
                        $address_data = OSC::helper('core/country')->verifyAddress($address_data, $this->getData($address_prefix . 'country_code', true));

                        foreach ($address_data as $address_field => $address_value) {
                            $data[$address_prefix . $address_field] = $address_value;
                        }

                        if ($address_prefix == 'shipping_') {
                            $data['shipping_line'] = [];
                        }
                    } catch (Exception $ex) {
                        $errors[] = $address_title . ' :: ' . $ex->getMessage();
                    }
                }
            }

        }

        foreach (array('added_timestamp', 'modified_timestamp', 'abandoned_email_sents') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (isset($data['custom_price_data'])) {
            if (!is_array($data['custom_price_data'])) {
                $data['custom_price_data'] = [];
            }

            if (isset($data['custom_price_data']['mug_size']) && is_array($data['custom_price_data']['mug_size']) && !empty($data['custom_price_data']['mug_size'])) {
                $custom_price_data = [];
                foreach ($data['custom_price_data']['mug_size'] as &$item) {
                    if (isset($item['price'])) {
                        $item['price'] = intval($item['price']);
                    }

                    array_push($custom_price_data, $item);
                }

                if (!empty($custom_price_data)) {
                    $data['custom_price_data']['mug_size'] = $custom_price_data;
                }
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $client_info = [
                    'ip' => OSC::getClientIP(),
                    'tracking_key' => Abstract_Frontend_Controller::getTrackingKey(),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'referer' => OSC::helper('report/common')->getReferer(),
                    'device_type' => OSC::getBrowser()->device->type,
                    'os' => OSC::getBrowser()->os->name ? trim(OSC::getBrowser()->os->name) : '',
                    'browser' => OSC::getBrowser()->browser->name ? trim(OSC::getBrowser()->browser->name) : '',
                    'location' => OSC::helper('core/common')->getClientLocation()
                ];

                OSC::core('observer')->dispatchEvent('collect_client_info', ['client_info' => &$client_info]);
                OSC::core('observer')->dispatchEvent('catalog/cart_collect_client_info', ['client_info' => &$client_info]);

                $default_fields = array(
                    'customer_id' => null,
                    'member_id' => null,
                    'email' => '',
                    'currency_code' => 'USD',
                    'discount_codes' => [],
                    'shipping_line' => [],
                    'taxes' => [],
                    'shipping_full_name' => '',
                    'shipping_phone' => '',
                    'shipping_company' => '',
                    'shipping_address1' => '',
                    'shipping_address2' => '',
                    'shipping_city' => '',
                    'shipping_province' => '',
                    'shipping_province_code' => '',
                    'shipping_country' => '',
                    'shipping_country_code' => '',
                    'shipping_zip' => '',
                    'billing_full_name' => '',
                    'billing_phone' => '',
                    'billing_company' => '',
                    'billing_address1' => '',
                    'billing_address2' => '',
                    'billing_city' => '',
                    'billing_province' => '',
                    'billing_province_code' => '',
                    'billing_country' => '',
                    'billing_country_code' => '',
                    'billing_zip' => '',
                    'client_info' => $client_info,
                    'abandoned_email_sents' => 0,
                    'added_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                $data['ukey'] = OSC::randKey(3, 7) . time();
            } else {
                unset($data['ukey']);
            }
        }

        $data['modified_timestamp'] = time();
        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _afterDelete() {
        parent::_afterDelete();

        $this->getLineItems()->delete();
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['discount_codes', 'shipping_line', 'taxes', 'client_info', 'custom_price_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['discount_codes', 'shipping_line', 'taxes', 'client_info', 'custom_price_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

    public function isAvailableToOrder() {
        foreach ($this->getLineItems() as $line_item) {
            if (!$line_item->isAvailableToOrder()) {
                return false;
            }
        }

        return true;
    }

    public function getUnavailableCartItems() {
        $unavailable_cart_items = [];
        $used_supplier_variant = [];

        foreach ($this->getLineItems() as $cart_item) {
            if ($cart_item->isAvailableToOrder($used_supplier_variant)) {
                continue;
            }

            $unavailable_cart_items[] = $cart_item;
        }

        return $unavailable_cart_items;
    }

    public function isDeignIdInProduct() {
        foreach ($this->getLineItems() as $line_item) {
            if (!$line_item->checkDeignIdInProduct()) {
                return false;
            }
        }

        return true;
    }


    /**
     *
     * @var Model_Catalog_Cart_Item_Collection
     */
    protected $_line_item_collection_sort = null;

    public function getSortItemErrorDesign(){
        if ($this->_line_item_collection_sort == null) {
            $ids_item_error_design = [];
            $ids_item_normal = [];

            $this->_line_item_collection_sort = OSC::model('catalog/cart_item')->getCollection();

            foreach ($this->getLineItems() as $line_item) {
                if (!$line_item->checkDeignIdInProduct() || !$line_item->isAvailableToOrder()) {
                    array_push($ids_item_error_design,$line_item->getId());
                }else{
                    array_push($ids_item_normal,$line_item->getId());
                }
            }

            if (count($ids_item_error_design) > 0) {
                foreach ($ids_item_error_design as $id) {
                    $this->_line_item_collection_sort->addItem($this->getLineItems()->getItemByPK($id));
                }
            }

            if (count($ids_item_normal) > 0) {
                foreach ($ids_item_normal as $id) {
                    $this->_line_item_collection_sort->addItem($this->getLineItems()->getItemByPK($id));
                }
            }
        }

        return $this->_line_item_collection_sort;
    }

    protected $_preload_product_type_ids = [];
    /**
     * @return array
     * @throws OSC_Exception_Runtime
     */
    public function preloadProductTypeIdsOfItem() {
        if (!empty($this->_preload_product_type_ids)) {
            return $this->_preload_product_type_ids;
        }

        $product_type_variant_ids = [];

        foreach ($this->getLineItems() as $item) {
            $product_type_variant_ids[] = $item->getProductTypeVariantId();
        }

        $variants = OSC::model('catalog/productType_variant')
            ->getCollection()
            ->addField('product_type_id')
            ->addCondition('id', $product_type_variant_ids, OSC_Database::OPERATOR_IN)
            ->load();

        foreach ($variants as $variant) {
            $this->_preload_product_type_ids[$variant->getId()] = $variant->data['product_type_id'];
        }

        return $this->_preload_product_type_ids;
    }

    protected $_tax_settings = null;
    /**
     * @throws OSC_Exception_Runtime
     */
    public function preloadTaxSettings() {
        if ($this->_tax_settings) {
            return $this->_tax_settings;
        }

        $product_type_ids = $this->preloadProductTypeIdsOfItem();
        $product_type_ids[] = 0;

        $this->_tax_settings = OSC::model('catalog/tax')->getCollection()
            ->addCondition('product_type_id', $product_type_ids, OSC_Database::OPERATOR_IN)
            ->load();

        return $this->_tax_settings;
    }

    public function getProductTypeVariantIds() {
        return array_keys($this->_preload_product_type_ids);
    }

}
