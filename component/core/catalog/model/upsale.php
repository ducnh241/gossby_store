<?php

class Model_Catalog_Upsale extends OSC_Database_Model_Virtual {
    const UPSALE_TIME = 12 * 60 * 60;

    const STATUS_UPSALE_UNAVAILABLE = 0;
    const STATUS_UPSALE_AVAILABLE = 1;
    const STATUS_UPSALE_EXPIRE = 2;

    const MESSAGE_PRODUCT_NOT_FOUND = 'We are sorry! The product item(s) are now no longer available. Please contact our team as you need further support.';
    const MESSAGE_UPSALE_UNAVAILABLE = 'We are sorry! Your order is now not available for adding new items. Please contact our team as you need further support.';

    const CODE_UPSALE_UNAVAILABLE = 405;

    protected $_variant = null;
    protected $_order = null;
    protected $_location = null;
    protected $_quantity = null;
    protected $_pack_id = null;

    protected $_pack_data = null;
    protected $_product = null;
    protected $_product_type = null;

    public function setVariant(Model_Catalog_Product_Variant $variant) {
        $this->_variant = $variant;
        $this->_product = $variant->getProduct();
        $this->_product_type = $variant->getProductType();
        return $this;
    }

    public function getVariant() {
        return $this->_variant;
    }

    public function setQuantity(int $quantity) {
        $this->_quantity = $quantity;
        return $this;
    }

    public function getQuantity() {
        return $this->_quantity;
    }

    public function setOrder(Model_Catalog_Order $order) {
        $this->_order = $order;
        return $this;
    }

    public function getOrder() {
        return $this->_order;
    }

    public function setLocation(array $location) {
        $this->_location = $location;
        return $this;
    }

    public function getLocation() {
        return $this->_location;
    }

    public function setPack(int $pack_id) {
        $this->_pack_id = intval($pack_id);
        return $this;
    }

    public function getPack() {
        return intval($this->_pack_id);
    }

    public function getPackData() {
        if ($this->_pack_data === null) {
            $pack_id = $this->getPack();
            $product_type = $this->getProductType();

            $list_pack_available = [];

            if ($product_type->getProductPacks()->length() > 0) {
                $flag_regular = true;
                foreach ($product_type->getProductPacks() as $pack) {
                    if ($pack->data['quantity'] === 1) {
                        $flag_regular = false;
                    }

                    $list_pack_available[$pack->getId()] = [
                        'id' => $pack->getId(),
                        'title' => $pack->data['title'],
                        'quantity' => $pack->data['quantity'],
                        'discount_type' => $pack->data['discount_type'],
                        'discount_value' => $pack->data['discount_value'],
                        'marketing_point_rate' => OSC::helper('catalog/common')->floatToInteger(floatval($pack->data['marketing_point_rate'])),
                        'shipping_values' => []
                    ];
                }

                if ($flag_regular) {
                    $list_pack_available[0] = [
                        'id' => 0,
                        'title' => 'Pack 1',
                        'quantity' => 1,
                        'discount_type' => 0,
                        'discount_value' => 0,
                        'marketing_point_rate' => 10000,
                        'note' => ''
                    ];
                }
            }

            if (isset($list_pack_available[$pack_id])) {
                $this->_pack_data = $list_pack_available[$pack_id];
            }
        }

        return $this->_pack_data;
    }

    public function getProduct() {
        if ($this->_product === null) {
            $this->_product = $this->getVariant()->getProduct();
        }

        return $this->_product;
    }

    public function getProductType() {
        if ($this->_product_type === null) {
            $this->_product_type = $this->getVariant()->getProductType();
        }

        return $this->_product_type;
    }

    protected $_price = null;
    public function getItemPrice() {
        if ($this->_price === null) {
            $variant = $this->getVariant();
            $location = $this->getLocation();

            $prices = $variant->getPriceForCustomer($location['country_code'], false, true);
            $price = $prices['price'] ?? 0;

            $pack_data = $this->getPackData();
            if (!is_null($pack_data)) {
                $pack_quantity = $pack_data['quantity'];
                $discount_type = $pack_data['discount_type'];

                $discount_price = $discount_type === Model_Catalog_Product_Pack::PERCENTAGE ?
                    round($prices['price'] * $pack_quantity * $pack_data['discount_value'] / 100) :
                    OSC::helper('catalog/common')->floatToInteger($pack_data['discount_value']);

                $price = $prices['price'] * $pack_quantity - intval($discount_price);
            }

            $this->_price = $price;
        }

        return $this->_price;
    }

    protected $_subtotal = null;
    public function getSubtotal() {
        if ($this->_subtotal === null) {
            $quantity = $this->getQuantity();

            $this->_subtotal = $this->getItemPrice() * $quantity;
        }

        return $this->_subtotal;
    }

    protected $_discount = null;
    public function getDiscount() {
        if ($this->_discount === null) {
            $this->_discount = 0;
        }

        return $this->_discount;
    }

    protected $_tax_percentage = null;
    public function getTaxPercentage() {
        if ($this->_tax_percentage === null) {
            $product = $this->getProduct();
            $product_type = $this->getProductType();
            $location = $this->getLocation();
            $product_type_id = $product->isCampaignMode() ? $product_type->getId() : 0;

            $this->_tax_percentage = OSC::helper('core/common')->getTaxValueByLocation(
                $product_type_id,
                $location['country_code'] ?? '',
                $location['province_code'] ?? ''
            );

            $this->_tax_percentage = $this->_tax_percentage ?? 0;
        }

        return $this->_tax_percentage;
    }

    protected $_tax_price = null;
    public function getTaxPrice() {
        if ($this->_tax_price === null) {
            $tax_percentage = !empty($this->getTaxPercentage()) ? $this->getTaxPercentage() : 0;
            $this->_tax_price = intval(round(($this->getSubtotal() + $this->getShipping()) * $tax_percentage / 100));
        }

        return $this->_tax_price;
    }

    protected $_shipping = null;
    public function getShipping() {
        if ($this->_shipping === null) {
            $product_type = $this->getProductType();
            $location = $this->getLocation();
            $quantity = $this->getQuantity();
            $pack_data = $this->getPackData();

            $this->_shipping = OSC::helper('catalog/referenceTransaction')->getShippingPrice($product_type->getId(), $location['country_code'] ?? '', $location['province_code'] ?? '', intval($quantity), $pack_data);
            $this->_shipping = $this->_shipping ?? 0;
        }
        
        return $this->_shipping;
    }

    protected $_total = null;
    public function getTotal() {
        if ($this->_total === null) {
            $this->_total = $this->getSubtotal() + $this->getTaxPrice() + $this->getShipping();
        }
        
        return $this->_total;
    }

    public function placeUpsale($item) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $order = $this->getOrder();
        $product = $this->getProduct();
        $variant = $this->getVariant();

        if (!$variant->ableToOrder()) {
            throw new Exception('The product is unable to order');
        }

        $custom_data = $item['custom'];

        if (is_array($custom_data) && count($custom_data) > 0) {
            try {
                //Config custom entry
                $return = OSC::core('observer')->dispatchEvent('catalog/cart_lineItem_customize', [
                    'variant' => $variant,
                    'custom_data' => $custom_data,
                    'quantity' => $this->getQuantity()
                ]);

                $custom_data = [];
                foreach ($return as $custom_entry) {
                    if (!is_array($custom_entry) || !isset($custom_entry['key']) || !isset($custom_entry['data'])) {
                        continue;
                    }
                    $custom_data = $custom_entry;
                }
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
        } else {
            if (isset($product->data['meta_data']['campaign_config']) && !empty($product->data['meta_data']['campaign_config'])) {
                throw new Exception('Please complete your personalized design. Some required options have been left empty');
            }
        }

        $additional_data = [];
        try {
            $product_type = $variant->getProductType();

            if ($product_type->getProductPacks()->length() > 0) {
                $pack_data = $this->getPackData();

                if (isset($pack_data)) {
                    $additional_data['pack'] = $pack_data;
                }
            }

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        $pack_data = $this->getPackData();

        $DB = OSC::core('database')->getAdapter('db_master');
        $DB->begin();

        try {
            $line_item = OSC::model('catalog/order_item')->setData([
                'order_master_record_id' => $order->getId(),
                'order_id' => $order->data['order_id'],
                'order_item_meta_id' => 0,
                'product_id' => $product->getId(),
                'variant_id' => $variant->getId(),
                'sku' => $this->_getCampaignSku($product, $custom_data) ?? $variant->data['sku'],
                'vendor' => $product->data['vendor'],
                'title' => $product->getProductTitle(),
                'product_type' => $product->isCampaignMode() ? $custom_data['data']['product_type']['ukey'] : $product->data['product_type'],
                'options' => [['title' => 'Product type', 'value' => $custom_data['data']['product_type']['title']]],
                'price' => $this->getItemPrice(),
                'reference_total_price' => $this->getTotal(),
                'tax_value' => $this->getTaxPercentage(),
                'cost' => $variant->data['cost'],
                'quantity' => $this->getQuantity(),
                'refunded_quantity' => 0,
                'process_quantity' => 0,
                'fulfilled_quantity' => 0,
                'other_quantity' => $pack_data['quantity'] ?? 0,
                'require_shipping' => $variant->data['require_shipping'],
                'require_packing' => $variant->data['require_packing'],
                'weight' => $variant->data['weight'],
                'weight_unit' => $variant->data['weight_unit'],
                'keep_flat' => $variant->data['keep_flat'],
                'dimension_width' => $variant->data['dimension_width'],
                'dimension_height' => $variant->data['dimension_height'],
                'dimension_length' => $variant->data['dimension_length'],
                'design_url' => '',
                'design_alert_flag' => 0,
                'custom_price_data' => [],
                'additional_data' => $additional_data,
                'payment_data' => [],
                'payment_status' => 'authorized',
                'discount' => []
            ]);
            OSC::core('observer')->dispatchEvent('catalog/orderVerifyUpsaleItemToCreate', ['order' => $order, 'line_item' => $line_item, 'custom_data' => $custom_data]);
            $line_item->save();

            $line_item->setOrder($order);

            $shipping = OSC::helper('catalog/common')->integerToFloat($this->getShipping());
            $tax = OSC::helper('catalog/common')->integerToFloat($this->getTaxPrice());
            $discount = OSC::helper('catalog/common')->integerToFloat($this->getDiscount());
            $subtotal = OSC::helper('catalog/common')->integerToFloat($this->getSubtotal());
            $total = OSC::helper('catalog/common')->integerToFloat($this->getTotal());

            $payment_info = OSC::helper('catalog/payment')->referenceTransaction($line_item, $subtotal, $discount, $shipping, $tax, $total, ['uid' => $item['uid']]);
            $payment_data = isset($payment_info['payment_data']) ? $payment_info['payment_data'] : [];
            if (!empty($payment_data) && !empty($item['uid'])) {
                $payment_data['uid'] = $item['uid'];
            }

            $item_upsale_price_data = [
                'shipping_price' => $this->getShipping(),
                'tax_price' => $this->getTaxPrice(),
                'subtotal_price' => $this->getSubtotal(),
                'discount_value' => 0, /* TODO */
                'discount_price' => $this->getDiscount(),
                'total_price' => $this->getTotal()
            ];

            $line_item->setData([
                'payment_data' => $payment_data,
                'upsale_data' => [
                    'price_data' => $item_upsale_price_data
                ],
                'fraud_data' => isset($payment_info['fraud_data']) ? $payment_info['fraud_data'] : null
            ])->save();

            $request_data = [
                'order_item' => $line_item->toArray(),
                'order_price_plus_data' => [
                    'shipping_price_plus' => $this->getShipping(),
                    'tax_price_plus' => $this->getTaxPrice(),
                    'subtotal_price_plus' => $this->getSubtotal(),
                    'total_price_plus' => $this->getTotal()
                ]
            ];

            $order_data_update = $this->_handleUpdateDataOfOrder($order, $request_data);

            /* If order fulfilled, update fulfillment status to partially fulfilled, else not update */
            if ($order->data['fulfillment_status'] === 'fulfilled') {
                $order_data_update['fulfillment_status'] = 'partially_fulfilled';
            }
            /* If order processed, update processes status to partially processed, else not update */
            if ($order->data['process_status'] === 'processed') {
                $order_data_update['process_status'] = 'partially_process';
            }

            /* If order fulfilled, update fulfillment status to partially fulfilled, else not update */
            if ($order->data['fulfillment_status'] === 'fulfilled') {
                $order_data_update['fulfillment_status'] = 'partially_fulfilled';
            }
            /* If order processed, update processes status to partially processed, else not update */
            if ($order->data['process_status'] === 'processed') {
                $order_data_update['process_status'] = 'partially_process';
            }

            /* Update order and add data to sync order from master to store */
            $old_total_price = $order->data['total_price'];

            $order->setData($order_data_update)->save();

            /* If order captured, captured transaction order */
            if ($order->data['payment_status'] === 'paid') {
                $total_price_plus = intval($order->data['total_price'] - $old_total_price);
            }

            $this->_callApiItemUpsale($order, $line_item, $total_price_plus ?? 0);

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        OSC::core('observer')->dispatchEvent('catalog/upsaleCreated', $line_item);

        return $payment_info;
    }

    protected function _getCampaignSku($product = null, $custom_data = null) {
        if (!($product instanceof Model_Catalog_Product)) {
            $product = $this->getPreLoadedModel('catalog/product', $this->getData('product_id'));
        }

        return implode('/', array_filter([
            $product->data['sku'],
            $custom_data['data']['product_type']['ukey'] ?? '',
            $custom_data['data']['product_type']['options']['keys'] ?? '',
        ]));
    }

    protected function _callApiItemUpsale($order, $line_item, $total_price_plus) {
        $json = [
            'order_id' => $order->data['order_id'],
            'item_id' => $line_item->data['item_id'],
            'total_price_plus' => $total_price_plus
        ];

        $store_info = OSC::getStoreInfo();

        $response = OSC::core('network')->curl(trim($store_info['master_store_url']) . '/catalog/api_order/afterUpsale', [
            'headers' => ['Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum(OSC::encode($json), $store_info['secret_key'])],
            'json' => $json,
            'timeout' => 500
        ]);

        if ($response['content']['result'] != 'OK') {
            throw new Exception($response['content']['message'], intval($response['content']['code']));
        }
    }


    protected function _handleUpdateDataOfOrder($order, $request_data): array {
        $item_tax_value = intval($request_data['order_item']['tax_value']);
        $order_price_plus_data = $request_data['order_price_plus_data'];
        $order_data['subtotal_price'] = $order->data['subtotal_price'] + $order_price_plus_data['subtotal_price_plus'];
        $order_data['shipping_price'] = $order->data['shipping_price'] + $order_price_plus_data['shipping_price_plus'];
        $order_data['tax_price'] = $order->data['tax_price'] + $order_price_plus_data['tax_price_plus'];
        $order_data['total_price'] = $order->data['total_price'] + $order_price_plus_data['total_price_plus'];

        foreach ($order->getLineItems(true) as $idx => $line_item) {
            $package_items[$line_item->data['item_id']] = [
                'quantity' => $line_item->data['quantity'],
                'require_packing' => $line_item->data['require_packing'],
                'keep_flat' => $line_item->data['keep_flat'],
                'weight' => $line_item->getWeightInGram(),
                'width' => $line_item->data['dimension_width'],
                'height' => $line_item->data['dimension_height'],
                'length' => $line_item->data['dimension_length'],
                'info' => [
                    'variant_id' => $line_item->data['item_id'],
                    'ukey' => $line_item->data['ukey']
                ]
            ];
        }

        $carrier = $order->data['shipping_line']['carrier'];
        $carrier['rates'][0]['amount'] = $order_data['shipping_price'];
        $carrier['rates'][0]['amount_tax'] += intval($order_price_plus_data['shipping_price_plus'] * $item_tax_value / 100);
        $order_data['shipping_line'] = [
            'carrier' => $carrier,
            'packages' => OSC::helper('catalog/checkout')->calculatePackages($package_items)
        ];

        return $order_data;
    }
}