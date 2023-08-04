<?php

class Cron_Klaviyo_Abandoned extends OSC_Cron_Abstract {

    const CRON_TIMER = '*/5 * * * *';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($data, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        //$this->_cleanOldData();
        $klaviyo_api_key = OSC::helper('klaviyo/common')->getApiKey();

        if (!$klaviyo_api_key) {
            return;
        }

        $collection = OSC::model('catalog/cart')->getCollection()
            ->setCondition('abandoned_email_sents = 0 AND modified_timestamp <= ' . (time() - (5 * 60)) . " AND email != '' AND (shipping_full_name != '' OR billing_full_name != '')")
            ->setLimit(1000)
            ->load();

        foreach ($collection as $cart) {
            try {
                $counter = 0;

                $line_items = $cart->getLineItems();

                foreach ($line_items as $line_item) {
                    if (!$line_item->isCrossSellMode() && (!$line_item->getVariant() || !$line_item->getProduct())) {
                        $line_items->removeItemByKey($line_item->getId());
                        try {
                            $line_item->delete();
                        } catch (Exception $ex) { }
                    } else {
                        $counter ++;
                    }
                }

                if ($counter < 1) {
                    $cart->delete();
                    continue;
                }

                try {
                    $data_discount_a = [
                        'auto_generated' => 1,
                        'discount_type' => 'percent',
                        'discount_value' => 10,
                        'usage_limit' => 1,
                        'deactive_timestamp' => time() + (60 * 60 * 24 * 7),
                        'note'  => 'Mail Abandoned'
                    ];
                    $data_discount_b = $data_discount_a;
                    $data_discount_b['discount_value'] = 15;

                    $data_discount_c = $data_discount_a;
                    $data_discount_c['discount_value'] = 20;

                    $data_discounts = [
                        'data_discount_a' => $data_discount_a,
                        'data_discount_b' => $data_discount_b,
                        'data_discount_c' => $data_discount_c,
                    ];

                    $results = OSC::helper('catalog/discountCode')->genDiscountCodes($data_discounts);

                    $discount_code_a = $results['data_discount_a'];
                    $discount_code_b = $results['data_discount_b'];
                    $discount_code_c = $results['data_discount_c'];

                } catch (Exception $ex) {
                    throw new Exception('Abandoned:DiscountCode:' . $ex->getMessage());
                }

                try {
                    $dataCart = $this->_getDataCart($cart, $discount_code_a, $discount_code_b, $discount_code_c);

                    $cart->calculateDiscount();

                    $total_price_cart_with_tax = $cart->getTotalPriceWithoutDiscount() + $dataCart['tax']['price'];

                    $data = [
                        'token' => $klaviyo_api_key,
                        'event' => 'Abandonment',
                        'customer_properties' => [
                            '$email' => $cart->data['email']
                        ],
                        'properties' => array(
                            '$event_id' => $cart->getId(),
                            '$value' => OSC::helper('catalog/common')->integerToFloat($cart->getTotalPriceWithoutDiscount()),
                            'Categories' => $dataCart['Categories'],
                            'Collections' => $dataCart['Collections'],
                            'ItemNames' => $dataCart['ItemNames'],
                            'Brands' => $dataCart['Brands'],
                            'DiscountCode' => count($cart->data['discount_codes']) > 0 ? implode('_', array_keys($cart->data['discount_codes'])) : '',
                            'Items' => $dataCart['Items'],
                            'BillingAddress' => $dataCart['BillingAddress'],
                            'ShippingAddress' => $dataCart['ShippingAddress'],
                            'FirstName' => $cart->getFirstName(),
                            'SubTotalPrice' => OSC::helper('catalog/common')->formatPriceByInteger(OSC::helper('catalog/cart')->getSubtotalWithoutDiscountOfCart($cart), 'email_with_currency'),
                            'ShippingPrice' => $cart->getShippingPrice() ? OSC::helper('catalog/common')->formatPriceByInteger($cart->getShippingPrice(), 'email_with_currency') : 0,
                            'TotalPrice' => OSC::helper('catalog/common')->formatPriceByInteger($total_price_cart_with_tax - $dataCart['discount']['price_without_format'], 'email_with_currency'),
                            'DiscountData' => $dataCart['discount'],
                            'BuyDesign' => $dataCart['buyDesign'],
                            'CartUrl' => OSC::helper('klaviyo/common')->addParamUrl($cart->getRecoveryUrl(), Helper_Klaviyo_Common::TYPE_ABANDON),
                            'Tax' => $dataCart['tax'],

                            'DisCountCodeAutoApply' => [
                                'discount_a' => [
                                    'total_price' => OSC::helper('catalog/common')->formatPriceByInteger($total_price_cart_with_tax - $dataCart['discount']['auto_apply_a']['price_without_format'], 'email_with_currency'),
                                    'cart_url' => OSC::helper('klaviyo/common')->addParamUrl($cart->getRecoveryUrl($discount_code_a), Helper_Klaviyo_Common::TYPE_ABANDON),
                                    'value' => $discount_code_a->data['discount_value'] . '%',
                                    'code' => $this->_formatDiscountCode($discount_code_a->data['discount_code']),
                                    'expire_time' => date('F d, Y, h:i A', $discount_code_a->data['deactive_timestamp']),
                                    'expire_time_format' => date('M d, Y', time() + 24 * 60 * 60)
                                ],
                                'discount_b' => [
                                    'total_price' => OSC::helper('catalog/common')->formatPriceByInteger($total_price_cart_with_tax - $dataCart['discount']['auto_apply_b']['price_without_format'], 'email_with_currency'),
                                    'cart_url' => OSC::helper('klaviyo/common')->addParamUrl($cart->getRecoveryUrl($discount_code_b), Helper_Klaviyo_Common::TYPE_ABANDON),
                                    'value' => $discount_code_b->data['discount_value'] . '%',
                                    'code' => $this->_formatDiscountCode($discount_code_b->data['discount_code']),
                                    'expire_time' => date('F d, Y, h:i A', $discount_code_b->data['deactive_timestamp']),
                                    'expire_time_format' => date('M d, Y', time() + 24 * 60 * 60)
                                ],
                                'discount_c' => [
                                    'total_price' => OSC::helper('catalog/common')->formatPriceByInteger($total_price_cart_with_tax - $dataCart['discount']['auto_apply_c']['price_without_format'], 'email_with_currency'),
                                    'cart_url' => OSC::helper('klaviyo/common')->addParamUrl($cart->getRecoveryUrl($discount_code_c), Helper_Klaviyo_Common::TYPE_ABANDON),
                                    'value' => $discount_code_c->data['discount_value'] . '%',
                                    'code' => $this->_formatDiscountCode($discount_code_c->data['discount_code']),
                                    'expire_time' => date('F d, Y, h:i A', $discount_code_c->data['deactive_timestamp']),
                                    'expire_time_format' => date('M d, Y', time() + 24 * 60 * 60)
                                ]
                            ]
                        ),
                        'time' => time(),
                    ];
                    OSC::helper('klaviyo/common')->create($data, Helper_Klaviyo_Common::TYPE_ABANDON);
                } catch (Exception $ex) {
                    throw new Exception('Abandoned:SendMailKlaviyo:' . $ex->getMessage());
                }

                $cart->increment('abandoned_email_sents');

                $items = [];

                foreach ($line_items as $cart_item) {
                    if ($cart_item->isCrossSellMode()) {
                        $crossSellData = $cart_item->getCrossSellData();
                        if ($crossSellData === null) {
                            continue;
                        }
                        $items[] = [
                            'id' => $cart_item->getId(),
                            'price' => $cart_item->data['price'],
                            'product_type_variant_id' => $crossSellData['product_type_variant_id'],
                            'data_design' => $crossSellData['print_template']['segment_source'],
                        ];
                        continue;
                    }

                    $product = $cart_item->getProduct();
                    if (!$product instanceof Model_Catalog_Product || $product->getId() < 1){
                        continue;
                    }

                    $items[] = [
                        'id' => $cart_item->getId(),
                        'variant_id' => $cart_item->data['variant_id'],
                        'price' => $cart_item->data['price'],
                        'product' => [
                            'id' => $product->getId(),
                            'title' => $product->getProductTitle()
                        ]
                    ];
                }

                $data_cart = [
                    'cart' => ['id' => $cart->getId(), 'shipping' => $dataCart['ShippingAddress'], 'time' => $cart->data['modified_timestamp']],
                    'items' => $items
                ];

                OSC::helper('postOffice/subscriber')->saveEmailSubscriber($cart->data['email'], $cart->getFullName() , 'abandon', $data_cart);
            } catch (Exception $ex) {
                continue;
            }
        }

    }

    protected function _formatDiscountCode($discount_code)
    {
        return preg_replace('/^(.{4})(.{4})(.{4})$/', '\\1-\\2-\\3', $discount_code);
    }

    protected function _cleanOldData() {
        return;
        try {
            $DB = OSC::core('database')->getWriteAdapter();

            $DB->select('*', 'catalog_cart', "(`abandoned_email_sents` = 1 OR email = '' OR (shipping_full_name = '' AND billing_full_name = '')) AND `modified_timestamp` < " . (time() - (60 * 60 * 24 * 7)) , 'cart_id ASC', null, 'fetch_cart');

            $rows = $DB->fetchArrayAll('fetch_cart');

            $DB->free('fetch_cart');

            if (count($rows) < 1) {
                return;
            }
            foreach ($rows as $row) {
                $DB->delete('catalog_cart_item', "cart_id = ".$row['cart_id'] , null, 'delete_cart_item');
                $DB->delete('catalog_cart', "cart_id = ".$row['cart_id'] , 1, 'delete_cart');
            }
        } catch (Exception $ex) {
        }
    }
    protected function _getDataCart($cart, $discount_code_a, $discount_code_b, $discount_code_c) {
        $data = [];
        $shippingAddress = $cart->getShippingAddress();
        $nameShippingAddress = explode(' ', $shippingAddress['full_name']);
        $data['ShippingAddress'] = [
            'FirstName' => $nameShippingAddress[0],
            'LastName' =>  $nameShippingAddress[1],
            'Company' => '',
            'Address1' => $shippingAddress['address1'],
            'Address2' => $shippingAddress['address2'],
            'City' => $shippingAddress['city'],
            'Region' => $shippingAddress['province'],
            'Region_code' =>  $shippingAddress['province_code'],
            'Country' => $shippingAddress['country'],
            'Country_code' => $shippingAddress['country_code'],
            'Zip' => $shippingAddress['zip'],
            'Phone' => $shippingAddress['phone'],
        ];

        $billingAddress = $cart->getBillingAddress(true);
        $nameBillingAddress = explode(' ', $billingAddress['full_name']);
        $data['BillingAddress'] = [
            'FirstName' => $nameBillingAddress[0],
            'LastName' =>  $nameBillingAddress[1],
            'Company' => '',
            'Address1' => $billingAddress['address1'],
            'Address2' => $billingAddress['address2'],
            'City' => $billingAddress['city'],
            'Region' => $billingAddress['province'],
            'Region_code' =>  $billingAddress['province_code'],
            'Country' => $billingAddress['country'],
            'Country_code' => $billingAddress['country_code'],
            'Zip' => $billingAddress['zip'],
            'Phone' => $billingAddress['phone'],
        ];

        $listBuyDesign = $cart->getBuyDesign();
        $data['buyDesign'] = ['count' => count($listBuyDesign), 'price' => 0];
        if (!empty($listBuyDesign)){
            $buyDesignPrice = $cart->getBuyDesignPrice();
            $data['buyDesign']['price'] = OSC::helper('catalog/common')->formatPriceByInteger($buyDesignPrice, 'email_with_currency');
        }

        foreach ($cart->getLineItems() as $lineItem) {
            if ($lineItem->isCrossSellMode()) {
                continue;
            }

            $product = $lineItem->getProduct();

            $personalized_idx = OSC::helper('personalizedDesign/common')->fetchCustomDataIndex($lineItem->data['custom_data']);
            $data['ItemNames'][] = $product->getProductTitle();
            $data['Categories'][] = explode(', ', $product->data['product_type']);
            $data['Categories'][] = $product->getListProductTagsWithoutRootTag(false, true);
            $data['Collections'][] = array_values(self::_getListCollectionTitle($product, false));
            $data['Brands'][] = $product->data['vendor'];
            $data['Items'][] = [
                'ProductID' => $lineItem->data['variant_id'],
                'SKU' => $lineItem->data['sku'],
                'ProductName' => $product->getProductTitle(),
                'Quantity' => $lineItem->data['quantity'],
                'ItemPrice' => OSC::helper('catalog/common')->formatPriceByInteger($lineItem->data['price'], 'email_with_currency'),
                'ProductURL' => OSC::helper('klaviyo/common')->addParamUrl($lineItem->getVariant()->getDetailUrl(), Helper_Klaviyo_Common::TYPE_ABANDON),
                'ImageURL' => OSC::helper('klaviyo/common')->addParamUrl($lineItem->getVariant()->getImageUrl(), Helper_Klaviyo_Common::TYPE_ABANDON),
                'ProductCategories' => explode(', ', $product->data['product_type']),
                'Categories' => explode(', ', $product->data['product_type']),
                'ProductTags' => $product->getListProductTagsWithoutRootTag(),
                'Brand' => $product->data['vendor'],
                'PersonalizedIdx' => $personalized_idx ? 1 : 0
            ];
        }
        $data['Categories'] = is_array($data['Categories']) ? array_unique(array_merge(...$data['Categories'])) : [];
        $data['Collections'] = is_array($data['Collections']) ? array_unique(array_merge(...$data['Collections'])) : [];
        $data_discount_code = $this->_getDataDiscount($cart);
        $discount_price_auto_apply_a = intval(OSC::helper('catalog/discountCode')->fetchDiscountValue($discount_code_a, $cart));
        $discount_price_auto_apply_b = intval(OSC::helper('catalog/discountCode')->fetchDiscountValue($discount_code_b, $cart));
        $discount_price_auto_apply_c = intval(OSC::helper('catalog/discountCode')->fetchDiscountValue($discount_code_c, $cart));
        $data['discount'] = [
            'code' => $data_discount_code['code'],
            'price' => $data_discount_code['price'],
            'value' => $data_discount_code['value'],
            'price_without_format' => $data_discount_code['price_without_format'],
            'auto_apply_a' => [
                'code' => $discount_code_a->data['discount_code'],
                'price' => $discount_price_auto_apply_a > 0 ? OSC::helper('catalog/common')->formatPriceByInteger($discount_price_auto_apply_a, 'email_with_currency') : 0,
                'price_without_format' => $discount_price_auto_apply_a
            ],
            'auto_apply_b' => [
                'code' => $discount_code_b->data['discount_code'],
                'price' => $discount_price_auto_apply_b > 0 ? OSC::helper('catalog/common')->formatPriceByInteger($discount_price_auto_apply_b, 'email_with_currency') : 0,
                'price_without_format' => $discount_price_auto_apply_b
            ],
            'auto_apply_c' => [
                'code' => $discount_code_c->data['discount_code'],
                'price' => $discount_price_auto_apply_c > 0 ? OSC::helper('catalog/common')->formatPriceByInteger($discount_price_auto_apply_c, 'email_with_currency') : 0,
                'price_without_format' => $discount_price_auto_apply_c
            ]
        ];
        $data['tax'] = [
            'price' => $cart->getTaxPrice(),
            'value' => OSC::helper('catalog/common')->formatPrice($cart->getFloatTaxPrice(), 'email_with_currency')
        ];

        return $data;
    }

    protected function _getDataDiscount($cart)
    {
        $data['code'] = '';
        $data['price'] = '';
        $data['value'] = '';
        $data['price_without_format'] = 0;

        $discount_codes = $cart->data['discount_codes'];
        if (is_array($discount_codes) && count($discount_codes) > 0) {
            try {
                $discount_code = OSC::model('catalog/discount_code')->getCollection()->loadByUkey($discount_codes)->first();
                $discount_price = intval(OSC::helper('catalog/discountCode')->fetchDiscountValue($discount_code, $cart));
                if ($discount_code->data['discount_type'] == 'percent') {
                    $discount_code_value = $discount_code ? $discount_code->data['discount_value'] . '%' : '';
                } else {
                    $discount_code_value = $discount_code ? OSC::helper('catalog/common')->formatPriceByInteger($discount_code->data['discount_value'], 'email_with_currency') : '';
                }
                $data['code'] = $discount_code->data['discount_code'];
                $data['price'] = $discount_price > 0 ? OSC::helper('catalog/common')->formatPriceByInteger($discount_price, 'email_with_currency') : '';
                $data['value'] = $discount_code_value;
                $data['price_without_format'] = $discount_price;
            } catch (Exception $ex) {

            }
        }
        return $data;
    }

    protected static function _getListCollectionTitle(Model_Catalog_Product $product, $return_string = true)
    {
        $collections = $product->getCollections();
        $list_collection_title = [];
        foreach ($collections as $collection) {
            $collection_title = str_replace('-', '_', $collection->data['title']);
            $list_collection_title[$collection->getId()] = OSC::core('string')->cleanAliasKey($collection_title, '_');
        }
        if ($return_string) {
            return implode(', ', $list_collection_title);
        }
        return $list_collection_title;
    }
}

