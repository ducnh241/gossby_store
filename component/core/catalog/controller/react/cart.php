<?php

class Controller_Catalog_React_Cart extends Abstract_Frontend_ReactApiController
{
    /**
     * @param Model_Catalog_Cart $cart
     * @param array $item - Post data from client
     * @throws Exception
     */
    protected function _addProductToCart(Model_Catalog_Cart $cart, $item = [])
    {
        try {
            if (empty($item)) {
                throw new Exception('No item added');
            }

            $variant_id = intval($item['variant_id']);
            if ($variant_id < 1) {
                throw new Exception('Variant ID is empty');
            }

            try {
                $variant = OSC::model('catalog/product_variant')->load($variant_id);
            } catch (Exception $ex) {
                throw new Exception($ex->getCode() == 404 ? 'The product is not exists or has been deleted' : 'We got an error in processing your request, please try again later.');
            }

            if (!$variant->ableToOrder()) {
                throw new Exception('The product is unable to order');
            }

            $product = $variant->getProduct();

            $custom_data = $item['custom'];

            if (is_array($custom_data) && count($custom_data) > 0) {
                try {
                    //Config custom entry
                    $return = OSC::core('observer')->dispatchEvent('catalog/cart_lineItem_customize', [
                        'variant' => $variant,
                        'custom_data' => $custom_data,
                        'quantity' => $this->_request->get('quantity'),
                        'options' => ['render_design']
                    ]);

                    $custom_data = [];
                    foreach ($return as $custom_entry) {
                        if (!is_array($custom_entry) || !isset($custom_entry['key']) || !isset($custom_entry['data'])) {
                            continue;
                        }
                        $custom_data[] = $custom_entry;
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
            $additional_data['variant_sku'] = $variant->data['sku'];

            try {
                $product_type = $variant->getProductType();

                if ($product_type->getProductPacks()->length() > 0) {
                    $list_pack_available = [];
                    $pack_id = $item['pack'];
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

                    if (count($list_pack_available) && !isset($list_pack_available[$pack_id])) {
                        throw new Exception('No pack of variant found');
                    }

                    if (isset($list_pack_available[$pack_id])) {
                        $additional_data['pack'] = $list_pack_available[$pack_id];
                    }
                }
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            // Cart line_item's custom_price_data
            $custom_price_data = [];
            $addon_services = $item['addon_services'] ?? [];

            try {
                $quantity = intval($item['quantity']);

                if ($quantity < 1) {
                    throw new Exception('Quantity is empty');
                }

                if ($quantity > 1000) {
                    throw new Exception('Quantity does not exceed 1000');
                }

                $line_item = OSC::model('catalog/cart_item');

                if (isset($item['atp']) && intval($item['atp']) === 1) {
                    $additional_data['atp'] = 1;
                }

                if (!empty($addon_services)) {
                    $custom_price_data = OSC::helper('addon/service')->updateCartItemAddonServices($line_item, $addon_services, $product);
                }

                $line_item_ukey = $line_item->makeUkey($cart->getId(), $variant->getId(), $custom_data, $custom_price_data, $additional_data);

                try {
                    $line_item->loadByUKey($line_item_ukey);
                } catch (Exception $ex) { }

                if ($line_item->getId() < 1) {
                    $item_product_type_id = $product_type instanceof Model_Catalog_ProductType ?
                        $product_type->getId() :
                        0;

                    $tax_value = OSC::helper('core/common')->getTaxValueByLocation(
                        $item_product_type_id,
                        $cart->data['shipping_country_code'],
                        $cart->data['shipping_province_code']
                    );

                    $line_item->setData([
                        'cart_id' => $cart->getId(),
                        'variant_id' => $variant_id,
                        'quantity' => $quantity,
                        'tax_value' => $tax_value,
                        'custom_data' => $custom_data,
                        'additional_data' => $additional_data,
                        'custom_price_data' => $custom_price_data
                    ])->save();

                    $cart->getLineItems()->addItem($line_item);
                } else {
                    $line_item->incrementQuantity($quantity);
                }

                //Update cart custom_price_data
                $cartCustomPriceData = OSC::helper('addon/service')->updateCartCustomPriceData($cart);

                $cart->setData([
                    'custom_price_data' => $cartCustomPriceData
                ])->save();

                $cart->reload();
                //ADD TRACKING AD
                try {
                    OSC::helper('report/adTracking')->trackAddToCart($product->getId(), $quantity);
                } catch (Exception $ex) {
                    //throw $th;
                }
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            OSC::helper('catalog/common')->updateCartQuantity($cart);

            $_SESSION['cart_new_item'] = $line_item->getId();

            $this->_autoApplyDiscountCode($cart);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function actionAddCart()
    {
        try {
            $cart = OSC::helper('catalog/common')->getCart();
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        try {
            $this->_addProductToCart($cart, $this->_request->getAll());

            $this->sendSuccess(OSC::helper('catalog/react_common')->getDataCart(true));
        } catch (Exception $ex) {
            try {
                $telegram_group_id  = PRODUCT_HIDDEN_TELEGRAM_GROUP_ID;

                if ($telegram_group_id) {
                    $client_ip = OSC::getClientIP();

                    $message = OSC::helper('core/setting')->get('theme/site_name') . "\n" .
                        'Cart ID #' . $cart->getId() . ' get error: ' . $ex->getMessage() . "\n" .
                        'IP Customer: ' . $client_ip . '. Sref ID: ' . intval($_REQUEST['sref']) . '. Adref ID:' . intval($_REQUEST['adref']) . "\n" .
                        'Request URI: ' . $_SERVER['REQUEST_URI'] . "\n" .
                        'Referer: ' . $_SERVER['HTTP_REFERER'];

                    OSC::helper('core/telegram')->sendMessage($message, $telegram_group_id);
                }
            } catch (Exception $exception) {
            }

            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetRelatedProducts()
    {
        try {
            $size = intval($this->_request->get('size', 4));

            $cart = OSC::helper('catalog/common')->getCart();

            if (!($cart instanceof Model_Catalog_Cart) || !OSC::helper('core/setting')->get('catalog/cart/enable_related_product')) {
                throw new Exception('Data is incorrect');
            }

            $product_items = OSC::model('catalog/product')->getCollection()->loadSameProductByCart($cart, $size);

            $result = [
                'products' => OSC::helper('catalog/product')->formatProductApi($product_items),
            ];

            $this->sendSuccess($result);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetCartDetail()
    {
        try {
            $results = OSC::helper('catalog/react_common')->getDataCart();
            $this->sendSuccess($results);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }
    }

    //API get cart ukey from param and return cart's information
    public function actionCheckCartValid()
    {
        $list_ukey = $this->_request->get('ukey', '');

        $results = [];
        try {
            if (!empty($list_ukey)) {
                $list_ukey = explode(',', $list_ukey);
                if (count($list_ukey) != 2) {
                    $this->sendError('Must post 2 cart ukey only');
                }

                foreach ($list_ukey as $ukey) {
                    $ukey = trim($ukey);
                    try {
                        $cart = OSC::model('catalog/cart')->loadByUKey($ukey);

                        $results[] = [
                            'ukey' => $ukey,
                            'items' => count($cart->getLineItems()),
                            'added_timestamp' => $cart->data['added_timestamp']
                        ];
                    } catch (Exception $exception) { }
                }

                if (empty($results)) {
                    $this->sendError('All carts not exist');
                }

                if (count($results) == 1) {
                    $this->sendSuccess([
                        'ukey' => $results[0]['ukey']
                    ]);
                }

                if (count($results) == 2) {
                    usort($results, function ($a, $b) {
                        return $a['added_timestamp'] > $b['added_timestamp'];
                    });

                    $cart_old = $results[0];
                    $cart_new = $results[1];

                    //Check if cart new not empty, or both carts are empty, return ukey of cart new. Else return ukey of cart old
                    if ((empty($cart_new['items']) && empty($cart_old['items'])) || !empty($cart_new['items'])) {
                        $this->sendSuccess([
                            'ukey' => $cart_new['ukey']
                        ]);
                    }

                    $this->sendSuccess([
                        'ukey' => $cart_old['ukey']
                    ]);
                }
            }

            $this->sendError('Something went wrong');
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }
    }

    public function actionPostEditCartItem()
    {
        /* @var $cart Model_Catalog_Cart */
        /* @var $line_item Model_Catalog_Cart_Item */

        $line_item_id = intval($this->_request->get('item_id'));

        if ($line_item_id < 1) {
            $this->sendError('Line Item ID is empty', $this::CODE_NOT_FOUND);
        }

        try {
            $cart = OSC::helper('catalog/common')->getCart();
            try {
                $line_item = OSC::model('catalog/cart_item')->load($line_item_id);
            } catch (Exception $ex) {
                throw new Exception($ex->getCode() == 404 ? 'The line item is not exists or has been deleted' : 'We got an error in processing your request, please try again later.');
            }
            if ($line_item->data['cart_id'] != $cart->getId()) {
                throw new Exception('Line item is not belong your cart');
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        $quantity = $this->_request->get('quantity') !== null ? intval($this->_request->get('quantity')) : $line_item->data['quantity'];

        if (!$line_item->isCrossSellMode()) {
            if (!($line_item->getVariant() instanceof Model_Catalog_Product_Variant)) {
                $quantity = 0;
            } else if (!$line_item->getVariant()->ableToOrder() && $quantity > $line_item->data['quantity']) {
                $this->sendError('Line item is not able to order more quantity', $this::CODE_NOT_FOUND);
            }
        }

        $product_tracking_key = $line_item->isCrossSellMode() ? 'cs_' . $line_item->getCrossSellDesignId() : $line_item->data['product_id'];

        // Cart line_item's custom_price_data
        $update_custom_price = false;
        $custom_price_data = [];
        $addon_services = $this->_request->get('addon_services') ?? [];
        if (!empty($addon_services)) {
            $custom_price_data = OSC::helper('addon/service')->updateCartItemAddonServices($line_item, $addon_services);
            $update_custom_price = true;
        }

        try {
            if ($quantity > 0) {
                if ($quantity > 1000) {
                    $this->sendError('Quantity does not exceed 1000', $this::CODE_NOT_FOUND);
                }

                $additional_data = $line_item->data['additional_data'];
                $data_update = [
                    'quantity' => $quantity,
                    'custom_data' => $line_item->data['custom_data']
                ];

                if ($update_custom_price) {
                    $additional_data = isset($additional_data) ? $additional_data : [];
                    $additional_data['custom_price_changed'][] = $custom_price_data;
                    $line_item_ukey = $line_item->makeUkey($cart->getId(), $line_item->data['variant_id'], $line_item->data['custom_data'], $custom_price_data, $additional_data);

                    $data_update['custom_price_data'] = $custom_price_data;
                    $data_update['ukey'] = $line_item_ukey;
                }

                $data_update['additional_data'] = $additional_data;

                if (!$line_item->isCrossSellMode()) {
                    $data_update['variant_id'] = $line_item->data['variant_id'];
                }

                $line_item->setData($data_update)->save();
                $line_item->reload();
            } else {
                OSC::helper('report/common')->addRecordEvent('catalog/remove_from_cart',
                    [
                        'product' => $line_item->getProduct(),
                        'cart_item_price' => $line_item->getPrice(),
                        'variant_id' => $line_item->data['variant_id'],
                        'quantity' => $line_item->data['quantity'],
                    ]
                );

                $line_item->delete();
            }

            //Update cart custom_price_data
            if ($update_custom_price) {
                $cartCustomPriceData = OSC::helper('addon/service')->updateCartCustomPriceData($cart);
                $cart->setData([
                    'custom_price_data' => $cartCustomPriceData
                ])->save();
            }

            //ADD TRACKING AD
            try {
                OSC::helper('report/adTracking')->trackEditCartItem($product_tracking_key, $quantity);
            } catch (Exception $ex) {
            }

            $cart->getLineItems(true);

            $this->_autoApplyDiscountCode($cart);

            OSC::helper('catalog/common')->updateCartQuantity($cart);

            $cart->calculateDiscount();

            $this->sendSuccess(OSC::helper('catalog/react_common')->getDataCart());
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    protected function _autoApplyDiscountCode(Model_Catalog_Cart $cart)
    {
        try {
            $discount_codes = OSC::model('catalog/discount_code')->getCollection()->loadAutoApplyCode();

            if ($discount_codes->length() < 1) {
                return;
            }

            $cart->calculateDiscount();

            try {
                foreach ($cart->getDiscountCodesCollection() as $applied_discount_code) {
                    if ($applied_discount_code->data['discount_type'] != 'free_shipping' && $applied_discount_code->data['auto_apply'] != 1) {
                        return;
                    }
                }
            } catch (Exception $ex) {
                return;
            }

            foreach ($cart->getDiscountCodes() as $discount_info) {
                try {
                    $discount_code = OSC::model('catalog/discount_code')->loadByUKey($discount_info['discount_code']);

                    if ($discount_code->data['discount_type'] != 'free_shipping' && $discount_code->data['auto_apply'] != 1) {
                        return;
                    }
                } catch (Exception $ex) {
                    if ($ex->getCode() != 404) {
                        return;
                    }
                }
            }

            $discount_code_to_apply = null;
            $discount_value_to_apply = 0;

            //Get the highest discount code to apply
            foreach ($discount_codes as $discount_code) {
                try {
                    $discount_code_value = OSC::helper('catalog/discountCode')->fetchDiscountValue($discount_code, $cart);

                    if ($discount_code_value > $discount_value_to_apply) {
                        $discount_code_to_apply = $discount_code;
                        $discount_value_to_apply = $discount_code_value;
                    }
                } catch (Exception $ex) {
                }
            }

            if ($discount_code_to_apply) {
                try {
                    OSC::helper('catalog/discountCode')->apply($discount_code_to_apply, $cart);

                    $discount_codes = [];

                    foreach ($cart->getDiscountCodes() as $discount_data) {
                        $discount_codes[] = $discount_data['discount_code'];
                    }

                    $cart->setData('discount_codes', $discount_codes)->save();
                } catch (Exception $ex) {
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function actionPostApplyDiscountCode()
    {
        try {
            $cart = OSC::helper('catalog/common')->getCart();
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        try {
            $code = $this->_request->get('code');

            OSC::helper('catalog/common')->validateDiscountCode($code);

            try {
                OSC::helper('catalog/discountCode')->apply($code, $cart);
            } catch (Exception $ex) {
                $discount_codes = $cart->getDiscountCodes();

                if ($ex->getCode() != 404 || count($discount_codes) > 0) {
                    if (
                        $ex->getCode() == Model_Catalog_Discount_Code::DISCOUNT_CODE_ERROR ||
                        $ex->getMessage() === 'Please fill contact info and shipping info before applying discount code'
                    ) {
                        OSC::helper('catalog/checkout')->insertFootprint('APPLY_DISCOUNT_CODE_NO_INFO', $ex);
                        $this->sendError('This code will be applied at checkout after entering your shipping information.', $this::CODE_APPLY_DISCOUNT_CODE_NO_INFO);
                    }
                    throw new Exception($ex->getMessage(), $ex->getCode());
                }

                try {
                    $discount_code = OSC::model('catalog/discount_code');
                    $discount_code->setData([
                        'discount_code' => $code,
                        'discount_type' => 'percent',
                        'discount_value' => 5,
                        'auto_generated' => 1,
                        'deactive_timestamp' => time() + (60 * 60 * 24),
                        'note'  => 'Apply Checkout'
                    ])->save();
                } catch (Exception $ex) {
                    OSC::helper('catalog/checkout')->insertFootprint('AUTO_GENERATED_DISCOUNT_CODE', $ex);
                    throw new Exception('This promo code is invalid or has expired. Please check again or contact us for immediate assistance.');
                }

                OSC::helper('catalog/discountCode')->apply($discount_code, $cart);
            }

            $discount_codes = [];

            foreach ($cart->getDiscountCodes() as $discount_data) {
                $discount_codes[] = $discount_data['discount_code'];
            }

            $cart->setData('discount_codes', $discount_codes)->save();
        } catch (Exception $ex) {
            OSC::helper('catalog/checkout')->insertFootprint('APPLY_DISCOUNT_CODE', $ex);
            $this->sendError($ex->getCode() == 404 ? 'This promo code is invalid or has expired. Please check again or contact us for immediate assistance.' : $ex->getMessage(), $ex->getCode() == 404 ? $this::CODE_APPLY_DISCOUNT_CODE_BUT_NOT_FOUND : $ex->getCode());
        }

        $this->sendSuccess(OSC::helper('catalog/react_common')->getDataCart());
    }

    public function actionPostRemoveDiscountCode()
    {
        try {
            $cart = OSC::helper('catalog/common')->getCart();
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        try {
            $code = $this->_request->get('code');
            $discount_code = Model_Catalog_Discount_Code::cleanCode($code);
            $discount_codes = $cart->getDiscountCodes();

            if (count($discount_codes) > 0) {
                unset($discount_codes[$discount_code]);

                $discount_codes = array_keys($discount_codes);

                $cart->setData('discount_codes', $discount_codes)->save();
                $cart->calculateDiscount();
            }
        } catch (Exception $ex) {
            OSC::helper('catalog/checkout')->insertFootprint('REMOVE_DISCOUNT_CODE', $ex);
            $this->sendError($ex->getMessage(), $ex->getCode());
        }

        $this->sendSuccess(OSC::helper('catalog/react_common')->getDataCart());
    }

    public function actionGetFormEditDesignCart()
    {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false);

            if (!($cart instanceof Model_Catalog_Cart) || $cart->getId() < 1) {
                throw new Exception('Cart is incorrect', $this::CODE_NOT_MODIFIED);
            }

            $cart_item_id = $this->_request->get('cart_item_id');

            if ($cart_item_id < 1) {
                throw new Exception('Line item ID is incorrect', $this::CODE_NOT_MODIFIED);
            }

            $line_item = $cart->getLineItems(true)->getItemByPK($cart_item_id);

            if (!($line_item instanceof Model_Catalog_Cart_Item) || $line_item->getId() < 1) {
                throw new Exception('Cart item is incorrect', $this::CODE_NOT_MODIFIED);
            }

            if (!$line_item->isCampaignMode()) {
                throw new Exception('Cart item is not a campaign', $this::CODE_NOT_MODIFIED);
            }

            $this->sendSuccess(OSC::helper('catalog/campaign')->orderLineItemGetDesignEditFrmDataByCart($line_item, $line_item->getCampaignData()));
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetFormEditDesignCartByProductBeta() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false);

            if (!($cart instanceof Model_Catalog_Cart) || $cart->getId() < 1) {
                throw new Exception('Cart is incorrect', $this::CODE_NOT_MODIFIED);
            }

            $cart_item_id = $this->_request->get('cart_item_id');

            if ($cart_item_id < 1) {
                throw new Exception('Line item ID is incorrect', $this::CODE_NOT_MODIFIED);
            }

            $line_item = $cart->getLineItems(true)->getItemByPK($cart_item_id);

            if (!($line_item instanceof Model_Catalog_Cart_Item) || $line_item->getId() < 1) {
                throw new Exception('Cart item is incorrect', $this::CODE_NOT_MODIFIED);
            }

            $variant = $line_item->getVariant();

            if (empty($variant->data)) {
                throw new Exception('Variant not found');
            }

            $matched_entry_idx = false;

            foreach ($line_item->data['custom_data'] as $entry_idx => $custom_entry) {
                if ($custom_entry['key'] == 'personalized_design' && $custom_entry['type'] == 'semitest') {
                    $matched_entry_idx = $entry_idx;
                    break;
                }
            }

            if ($matched_entry_idx === false) {
                throw new Exception('No personalized config was found to edit');
            }

            $form_edit_design_data = OSC::helper('catalog/product')->getFormEditDesignSemitest($line_item->data['custom_data'][$matched_entry_idx], $variant);

            $selected_product_variant_id = $variant->data['id'];

            $product = OSC::model('catalog/product')->load($line_item->data['product_id']);

            $cart_option_config = $product->getCartFrmOptionConfigSemitest(['atp' => 1]);

            $list_product_variants = $cart_option_config['product_variants'];

            $selected_product_variant_ukey = array_column($list_product_variants, 'option_value')[array_search($selected_product_variant_id, array_column($list_product_variants, 'product_variant_id'))];

            $cart_form_config = [
                'product_variant_id' => $selected_product_variant_id,
                'product_variant_ukey' => $selected_product_variant_ukey,
                'cart_option_config' => $cart_option_config,
                'is_disable_preview' => isset($product->data['meta_data']['is_disable_preview']) ? intval($product->data['meta_data']['is_disable_preview']) : 0
            ];

        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }

        $this->sendSuccess(['designs' => $form_edit_design_data['designs'], 'cart_form_config' => $cart_form_config, 'flag_show_edit_product_type' => 1]);
    }

    public function actionEditCartItemSemitest() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false);

            if (!($cart instanceof Model_Catalog_Cart) || $cart->getId() < 1) {
                throw new Exception('Cart is incorrect', $this::CODE_NOT_MODIFIED);
            }

            $line_item_id = intval($this->_request->get('item_id'));

            if ($line_item_id < 1) {
                throw new Exception('Line item ID is incorrect', $this::CODE_METHOD_NOT_ALLOWED);
            }

            $variant_id = intval($this->_request->get('variant_id'));

            /* @var $line_item Model_Catalog_Cart_Item */

            $line_item = $cart->getLineItems(true)->getItemByPK($line_item_id);

            if ($variant_id < 1) {
                throw new Exception('Variant ID is empty');
            }

            try {
                $variant = OSC::model('catalog/product_variant')->load($variant_id);
            } catch (Exception $ex) {
                throw new Exception($ex->getCode() == 404 ? 'The product is not exists or has been deleted' : 'We got an error in processing your request, please try again later.');
            }

            $design_ids = $variant->data['design_id'];

            $design_collection = OSC::model('personalizedDesign/design')->getCollection()->load($design_ids);

            $config = $this->_request->get('config');

            $designs = [];
            $new_config = null;

            foreach ($design_ids as $design_id) {
                $designs[$design_id] = $design_collection->getItemByPK($design_id);
                $new_config[$design_id] = $config[$design_id];
            }

            $matched_entry_idx = false;

            foreach ($line_item->data['custom_data'] as $entry_idx => $custom_entry) {
                if ($custom_entry['key'] == 'personalized_design' && $custom_entry['type'] == 'semitest') {
                    $matched_entry_idx = $entry_idx;
                    break;
                }
            }

            if ($matched_entry_idx === false) {
                throw new Exception('No personalized config was found to render');
            }

            if (is_array($new_config) && count($new_config) > 0) {
                try {
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

                    $new_custom_data = $line_item->data['custom_data'];

                    $new_custom_data[$matched_entry_idx] = $custom_data;

                    $quantity = intval($this->_request->get('quantity'));

                    if ($quantity < 1) {
                        throw new Exception('Quantity is empty');
                    }

                    if ($quantity > 1000) {
                        throw new Exception('Quantity does not exceed 1000');
                    }

                    $line_item->setData([
                        'custom_data' => $new_custom_data,
                        'quantity' => $quantity,
                        'variant_id' => $variant_id
                    ])->save();

                    OSC::helper('catalog/common')->updateCartQuantity($cart);
                } catch (Exception $ex) {
                    $this->sendError($ex->getMessage(), $ex->getCode());
                }

                $this->sendSuccess(OSC::helper('catalog/react_common')->getDataCart(true));
            }

        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionEditDesignCart()
    {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false);
            
            if (!($cart instanceof Model_Catalog_Cart) || $cart->getId() < 1) {
                throw new Exception('Cart is incorrect', $this::CODE_NOT_MODIFIED);
            }

            $cart_item_id = $this->_request->get('cart_item_id');

            if ($cart_item_id < 1) {
                throw new Exception('Line item ID is incorrect', $this::CODE_NOT_MODIFIED);
            }

            $line_item = $cart->getLineItems(true)->getItemByPK($cart_item_id);
            $current_variant_id = $line_item->data['variant_id'];

            if (!($line_item instanceof Model_Catalog_Cart_Item) || $line_item->getId() < 1) {
                throw new Exception('Cart item is incorrect', $this::CODE_NOT_MODIFIED);
            }

            if (!$line_item->isCampaignMode()) {
                throw new Exception('Line item is not a campaign', $this::CODE_NOT_MODIFIED);
            }

            try {
                $item = $this->_request->getAll();

                if (empty($item)) {
                    throw new Exception('No item added');
                }

                $variant_id = intval($item['variant_id']);
                if ($variant_id < 1) {
                    throw new Exception('Variant ID is empty');
                }

                try {
                    $variant = OSC::model('catalog/product_variant')->load($variant_id);
                } catch (Exception $ex) {
                    throw new Exception($ex->getCode() == 404 ? 'The product is not exists or has been deleted' : 'We got an error in processing your request, please try again later.');
                }

                if (!$variant->ableToOrder()) {
                    throw new Exception('The product is unable to order');
                }

                $product = $variant->getProduct();

                $custom_data = $item['custom'];

                if (is_array($custom_data) && count($custom_data) > 0) {
                    try {
                        //Config custom entry
                        $return = OSC::core('observer')->dispatchEvent('catalog/cart_lineItem_customize', [
                            'variant' => $variant,
                            'custom_data' => $custom_data,
                            'quantity' => $this->_request->get('quantity')
                        ]);

                        $custom_data = [];

                        foreach ($return as $custom_entry) {
                            if (!is_array($custom_entry) || !isset($custom_entry['key']) || !isset($custom_entry['data'])) {
                                continue;
                            }

                            $custom_data[] = $custom_entry;
                        }
                    } catch (Exception $ex) {
                        throw new Exception($ex->getMessage());
                    }
                } else {
                    if (isset($product->data['meta_data']['campaign_config']) && !empty($product->data['meta_data']['campaign_config'])) {
                        throw new Exception('Please complete your personalized design. Some required options have been left empty');
                    }
                }

                $additional_data = isset($line_item->data['additional_data']) ? $line_item->data['additional_data'] : [];

                $additional_data['variant_sku'] = $variant->data['sku'];

                if ($variant_id !== $current_variant_id) {
                    $additional_data['variant_changed'][] = [
                        $current_variant_id => $variant_id
                    ];
                }

                try {
                    $product_type = $variant->getProductType();

                    if ($product_type->getProductPacks()->length() > 0) {
                        $list_pack_available = [];
                        $pack_id = $item['pack'];
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

                        if (count($list_pack_available) && !isset($list_pack_available[$pack_id])) {
                            throw new Exception('No pack of variant found');
                        }

                        if (isset($list_pack_available[$pack_id])) {
                            $additional_data['pack'] = $list_pack_available[$pack_id];
                        }
                    }
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }

                // Cart line_item's custom_price_data
                /* @var $line_item Model_Catalog_Cart_Item */
                $product_type_id = $variant->getProductType()->getId();
                $check_update = false;

                if ($variant->data['product_type_variant_id'] && ($line_item->getProductTypeVariantId() != $variant->data['product_type_variant_id'])) {
                    $check_update = $line_item->checkUpdateAddonServices([
                        'product_type_variant_id' => $variant->data['product_type_variant_id'],
                        'product_type_id' => $product_type_id
                    ]);
                }

                if ($check_update) {
                    $line_item->reload();

                    // Calculate cart addon service and update here
                    $cartCustomPriceData = OSC::helper('addon/service')->updateCartCustomPriceData($cart);
                    $cart->setData([
                        'custom_price_data' => $cartCustomPriceData
                    ])->save();

                    $cart->reload();
                }

                $custom_price_data = $line_item->data['custom_price_data'];

                try {
                    $quantity = intval($item['quantity']);

                    if ($quantity < 1) {
                        throw new Exception('Quantity is empty');
                    }

                    if ($quantity > 1000) {
                        throw new Exception('Quantity does not exceed 1000');
                    }

                    $line_item_ukey = $line_item->makeUkey($cart->getId(), $variant->getId(), $custom_data, $custom_price_data, $additional_data);

                    $line_item->setData([
                        'ukey' => $line_item_ukey,
                        'variant_id' => $variant_id,
                        'quantity' => $quantity,
                        'custom_data' => $custom_data,
                        'additional_data' => $additional_data
                    ])->save();
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }

                OSC::helper('catalog/common')->updateCartQuantity($cart);

                $this->_autoApplyDiscountCode($cart);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            $this->sendSuccess(OSC::helper('catalog/react_common')->getDataCart(true));
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionCheckShippingByCountryCode()
    {
        try {
            $cart = OSC::helper('catalog/common')->getCart();

            $country_code = trim($this->_request->get('country_code'));

            if ($country_code == '') {
                throw new Exception('Country code is incorrect', $this::CODE_METHOD_NOT_ALLOWED);
            }
            $province_code = trim($this->_request->get('province_code'));

            $line_items = $cart->getLineItems(true);

            $result = [
                'country_code' => $country_code,
                'province_code' => $province_code,
                'error' => 0
            ];

            foreach ($line_items as $line_item) {
                if (!$line_item->checkShippingByCountry($country_code, $province_code)) {
                    $result = [
                        'country_code' => $country_code,
                        'province_code' => $province_code,
                        'error' => 1
                    ];

                    break;
                }
            }

            $this->sendSuccess($result);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionRecovery()
    {
        try {
            /* @var $cart Model_Catalog_Cart */

            try {
                $ukey = $this->_request->get('ukey');
                if (!isset($ukey)) {
                    $this->sendError('Cart is not found', static::CODE_NOT_FOUND);
                }

                $cart = OSC::model('catalog/cart');
                $cart->loadByUkey($ukey);
            } catch (Exception $ex) {
            }

            if ($cart->getId() < 1) {
                $this->sendError('Your cart is removed', static::CODE_NOT_FOUND);
            }

            $discount_code = $this->_request->get('discountCode');
            if (isset($discount_code)) {
                try {
                    $discount_data = OSC::model('catalog/discount_code')->loadByUKey($discount_code);
                    $cart->setData('discount_codes', [$discount_data->data['discount_code']])->save();
                } catch (Exception $ex) {
                }
            }

            OSC::helper('catalog/common')->setCart($cart);
            $this->sendSuccess([
                'cart' => $ukey
            ]);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), static::CODE_LOAD_CART_ERROR);
        }
    }

    public function actionSetPlaceOrderLog() {
        try {
            $cart = OSC::helper('catalog/common')->getCart(false, false);
            $insert_data = [
                'added_timestamp' => time()
            ];

            $tracking_key = Abstract_Frontend_Controller::getTrackingKey();

            if (!empty($tracking_key)) {
                $insert_data['tracking_key'] = $tracking_key;
            }

            if (!empty($cart)) {
                $insert_data['cart_ukey'] = $cart->getUkey();
            }

            $insert_keys = [
                // flag value: 1 - request to stripe
                //             2 - response of stripe request
                //             3 - request using place order api
                //             4 - response of place order api
                'flag',

                // payment_type value: stripe, apple_pay
                'payment_type',

                'action',
                'payment_intent_id',
                'payment_intent_data',
                'place_order_api_response',
                'stripe_error_message',
                'error_message',
            ];

            foreach ($insert_keys as $insert_key) {
                if (!empty($this->_request->get($insert_key))) {
                    $insert_data[$insert_key] = $this->_request->get($insert_key);
                }
            }

            OSC::core('mongodb')->insert('place_order_log', $insert_data, 'product');
        } catch (Exception $exception) {}

        $this->_ajaxResponse(['success' => true]);
    }
}
