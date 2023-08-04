<?php

class Helper_Catalog_React_Common {
    public function getSemitestConfig($id, $type) {
        $semitest_config = [];

        try {
            if ($id < 1) {
                throw new Exception('Data is incorrect');
            }

            switch ($type) {
                case 'cart':
                    $line_item = OSC::model('catalog/cart_item')->load($id);

                    if ($line_item->getId() < 1) {
                        throw new Exception('Line item is not exists');
                    }

                    $custom_data_entries = $line_item->data['custom_data'];
                    $mockup_url = $line_item->getVariant()->getImageUrl();
                    break;
                case 'order':
                    $line_item = OSC::model('catalog/order_item')->load($id);

                    if ($line_item->getId() < 1) {
                        throw new Exception('Line item is not exists');
                    }

                    $custom_data_entries = $line_item->getOrderItemMeta()->data['custom_data'];
                    $mockup_url = $line_item->getImageUrl();
                    break;
                default:
                    throw new Exception('Data is incorrect');
            }

            $semitest_config['mockup_url'] = OSC::helper('core/image')->imageOptimize($mockup_url, 600, 600, false);

            if (is_array($custom_data_entries) && count($custom_data_entries) > 0) {
                foreach ($custom_data_entries as $custom_data_idx => $custom) {
                    if ($custom['key'] == 'personalized_design' && isset($custom['type']) && $custom['type'] == 'semitest') {

                        $personalized_preview = [];

                        foreach ($custom['data'] as $design_id => $data) {
                            $semitest_config['designs'][] = ['document' => ['width' => $custom['data'][$design_id]['width'], 'height' => $custom['data'][$design_id]['height'] ],'svg' => $custom['data'][$design_id]['design_svg']];
                            $personalized_preview[] = $custom['data'][$design_id]['config_preview'];
                        }

                        foreach ($personalized_preview as $personalized_preview_value) {
                            foreach ($personalized_preview_value as $key => $value) {
                                if ($key == 'document_type') {
                                    continue;
                                }
                                $form_key = $value['form'];

                                $data_value = OSC::decode($value['value'], true);
                                if (isset($data_value['file']) && $data_value['file'] != '') {
                                    $url_image = OSC::isUrl($data_value['url']) ? $data_value['url'] : OSC::core('aws_s3')->getStorageUrl($data_value['url']);
                                    $semitest_config['mockup_config'][] = ['title' => $form_key, 'value' => '<img src="'.$url_image.'" title="'.$data_value['name'].'" />'];
                                } elseif (isset($data_value['uri']) && $data_value['uri'] != '') {
                                    $semitest_config['mockup_config'][] = ['title' => $form_key, 'value' => $data_value['name']];
                                } elseif (isset($data_value['fontSize'])) {
                                    $semitest_config['mockup_config'][] = ['title' => $form_key, 'value' => $data_value['value']];
                                } else {
                                    $semitest_config['mockup_config'][] = ['title' => $form_key, 'value' => $value['value']];
                                }
                            }
                        }
                        break;
                    }
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return $semitest_config;
    }

    public function getCampaignConfig($id, $type)
    {
        try {
            if ($id < 1) {
                throw new Exception('Data is incorrect');
            }

            switch ($type) {
                case 'cart':
                    $line_item = OSC::model('catalog/cart_item')->load($id);

                    if ($line_item->getId() < 1) {
                        throw new Exception('Line item is not exists');
                    }

                    $campaign_data = $line_item->getCampaignData();
                    break;
                case 'order':
                    $line_item = OSC::model('catalog/order_item')->load($id);

                    if ($line_item->getId() < 1) {
                        throw new Exception('Line item is not exists');
                    }

                    $campaign_data = $line_item->getCampaignData();
                    break;
                default:
                    throw new Exception('Data is incorrect');
            }

            if ($campaign_data === null) {
                throw new Exception('Line item is not campaign');
            }

            try {
                $product = OSC::model('catalog/product')->load($line_item->data['product_id']);
                $campaign_config = $product->data['meta_data']['campaign_config']['print_template_config'];
            } catch (Exception $exception) {

            }

            $product_type_variant = OSC::model('catalog/productType_variant')->load($campaign_data['product_type_variant_id']);
            $product_type = OSC::model('catalog/productType')->load($product_type_variant->data['product_type_id']);

            $mockup_configs = [];

            $print_template = $campaign_data['print_template'];
            try {
                $source_print_template = OSC::model('catalog/printTemplate')->load($campaign_data['print_template']['print_template_id']);

                if (isset($source_print_template->data['config']['preview_config']) && !empty($source_print_template->data['config']['preview_config'])) {
                    $print_template['preview_config'] = $source_print_template->data['config']['preview_config'];
                }

                if (isset($source_print_template->data['config']['segments']) && !empty($source_print_template->data['config']['segments'])) {
                    $print_template['segments'] = $source_print_template->data['config']['segments'];
                }

                if (isset($source_print_template->data['config']['print_file']) && !empty($source_print_template->data['config']['print_file'])) {
                    $print_template['print_file'] = $source_print_template->data['config']['print_file'];
                }
            } catch (Exception $exception) {
                $print_template = $campaign_data['print_template'];
            }

            OSC::helper('catalog/campaign')->replaceLayerUrl($print_template['preview_config'], $campaign_data['product_type']['options']['keys']);

            foreach ($campaign_data['print_template']['segment_source'] as $segment_key => $segment_source) {
                $preview_config = [];
                foreach ($print_template['preview_config'] as $config_item) {
                    if (isset($config_item['config'][$segment_key]) && !empty($config_item['config'][$segment_key])) {
                        $preview_config = $config_item;
                        break;
                    }
                }

                $data = [
                    'title' => $preview_config['title'] ?? '',
                    'product_type' => $product_type->data['ukey'],
                    'design_key' => $segment_key,
                    'design' => '',
                    'preview_config' => $preview_config,
                    'segment_configs' => $print_template['segments'] ?? [],
                    'segment_sources' => isset($campaign_config) && !empty($campaign_config) ? $campaign_config[array_search($print_template['print_template_id'], array_column($campaign_config, 'print_template_id'))]['segments'] : $print_template['segment_source']
                ];

                switch ($segment_source['source']['type']) {
                    case 'personalizedDesign':
                        $data['design'] = [
                            'svg' => $this->replaceSvgContent($segment_source['source']['svg'])
                        ];
                        break;
                    case 'image':
                        $image = OSC::model('catalog/campaign_imageLib_item')->load($segment_source['source']['image_id']);

                        $data['design'] = [
                            'url' => $image->getFileThumbUrl()
                        ];
                        break;
                    default:
                        break;
                }

                $mockup_configs[$segment_key] = $data;
            }

            return $mockup_configs;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public function replaceSvgContent($content) {
        $search = OSC_FRONTEND_BASE_URL . '/storage';
        $replace = OSC::wrapCDN(OSC::core('aws_s3')->getStorageDirUrl());

        return str_replace($search, $replace, $content);
    }

    public function getDeliveryTime($variant) {
        $delivery_time = [];
        try {
            $product_type_ukey = null;
            if ($variant instanceof Model_Catalog_Product_Variant
                && $variant->getProduct() instanceof Model_Catalog_Product
                && $variant->getProduct()->isCampaignMode()
                && $variant->getProductType()->data['ukey'])
            {
                $product_type_ukey = $variant->getProductType()->data['ukey'];
            }

            $customer_country_code = OSC::helper('core/common')->getCustomerCountryCodeCookie();

            $rates = OSC::helper('dls/catalog_checkout')->getShippingEstimate(
                $variant,
                $product_type_ukey,
                1,
                $customer_country_code
            );

            if ($rates && count($rates) > 0) {
                $shipping_location = OSC::helper('catalog/common')->getCustomerShippingLocation();
                $country_code = $customer_country_code ?: $shipping_location['country_code'];

                $customer_province_code = OSC::helper('core/common')->getCustomerProvinceCodeCookie();
                $province_code = $customer_province_code ?: $shipping_location['province_code'];

                $delivery_time['country_code'] = mb_strtolower($country_code);
                $delivery_time['province_code'] = mb_strtolower($province_code);
                $delivery_time['country_name'] = OSC::helper('core/country')->getCountryTitle($country_code);
                $delivery_time['province_name'] = OSC::helper('core/country')->getProvinceTitle($country_code, $province_code);

                foreach ($rates as $rate) {
                    if ($rate && $rate->getEstimateTimestamp() > 0) {
                        $delivery_time['data'][$rate->getKey()] = [
                            'estimate_timestamp' => $rate->getEstimateTimestamp(),
                            'shipping_name' => $rate->getTitle()
                        ];
                    }
                }
            }
        } catch (Exception $ex) {

        }
        return $delivery_time;
    }

    public function getDeliveryTimeByCheckout(Model_Catalog_Cart $cart,$variant) {
        $delivery_time = [];
        try {
            $product_type_ukey = null;
            if ($variant instanceof Model_Catalog_Product_Variant
                && $variant->getProduct() instanceof Model_Catalog_Product
                && $variant->getProduct()->isCampaignMode()
                && $variant->getProductType()->data['ukey'])
            {
                $product_type_ukey = $variant->getProductType()->data['ukey'];
            }
            $country_code = $cart->data['shipping_country_code'];
            $province_code = $cart->data['shipping_province_code'];

            $rates = OSC::helper('dls/catalog_checkout')->getShippingEstimate($variant, $product_type_ukey, 1, $country_code, $province_code);

            if ($rates && count($rates) > 0) {
                $delivery_time['country_code'] = mb_strtolower($country_code);
                $delivery_time['province_code'] = mb_strtolower($province_code);
                $delivery_time['country_name'] = OSC::helper('core/country')->getCountryTitle($country_code);
                $delivery_time['province_name'] = OSC::helper('core/country')->getProvinceTitle($country_code, $province_code);

                foreach ($rates as $rate) {
                    if ($rate && $rate->getEstimateTimestamp() > 0) {
                        $delivery_time['data'][$rate->getKey()] = [
                            'estimate_timestamp' => $rate->getEstimateTimestamp(),
                            'shipping_name' => $rate->getTitle()
                        ];
                    }
                }
            }
        } catch (Exception $ex) {

        }
        return $delivery_time;
    }

    public function getEstimatedDeliveryTime($variant, $country_code, $province_code, $quantity = 1, $option = []) {
        $delivery_time = [];
        try {
            $quantity = intval($quantity);
            if ($quantity < 1) {
                throw new Exception('Quantity need more than 0');
            }
            $product_type_ukey = null;
            if ($variant->getProduct()->isCampaignMode() && $variant->getProductType()->data['ukey']) {
                $product_type_ukey = $variant->getProductType()->data['ukey'];
            }

            $provinces = OSC::helper('core/country')->getProvinces($country_code);

            /* province not detect */
            if (!$province_code && count($provinces) > 0) {
                $estimate_timestamp_compare = [];

                foreach (array_keys($provinces) as $province) {
                    $rates = OSC::helper('dls/catalog_checkout')->getShippingEstimate($variant, $product_type_ukey, $quantity, $country_code, $province);

                    $count_rates = count($rates);
                    /* show first and last item */
                    if (isset($option['product_detail']) && $count_rates > 2) {
                        for ($i = 1; $i < $count_rates - 1; $i++) {
                            unset($rates[$i]);
                        }
                    }

                    if ($rates && count($rates) > 0) {
                        $estimate_timestamp_compare[$province] = $rates[0]->getEstimateTimestamp();

                        foreach ($rates as $rate) {
                            if ($rate->getEstimateTimestamp() > 0) {
                                $delivery_time[$province]['data'][$rate->getKey()] = [
                                    'estimate_timestamp' => $rate->getEstimateTimestamp(),
                                    'processing_timestamp' => $rate->getProcessingTimestamp(),
                                    'shipping_name' => $rate->getTitle()
                                ];
                            }
                        }
                    }
                }

                $province_code = array_key_first($estimate_timestamp_compare);

                $delivery_time['province_code'] = $province_code;

                $delivery_time['data'] = array_reverse($delivery_time[$province_code]['data']);
            } else {
                $rates = OSC::helper('dls/catalog_checkout')->getShippingEstimate($variant, $product_type_ukey, $quantity, $country_code, $province_code);

                $count_rates = count($rates);

                /* show first and last item if count_rates > 2 */
                if (isset($option['product_detail']) && $count_rates > 2) {
                    for ($i = 1; $i < $count_rates - 1; $i++) {
                        unset($rates[$i]);
                    }
                }

                if ($rates && count($rates) > 0) {
                    $delivery_time['province_code'] = $province_code;

                    foreach ($rates as $rate) {
                        if ($rate->getEstimateTimestamp() > 0) {
                            $delivery_time['data'][$rate->getKey()] = [
                                'estimate_timestamp' => $rate->getEstimateTimestamp(),
                                'processing_timestamp' => $rate->getProcessingTimestamp(),
                                'shipping_name' => $rate->getTitle()
                            ];
                        }
                    }
                }
            }
            $delivery_time['data'] = array_reverse($delivery_time['data']);
            $delivery_time['country_code'] = mb_strtoupper($country_code);
            $delivery_time['country_name'] = OSC::helper('core/country')->getCountryTitle($country_code);

        } catch (Exception $ex) {

        }
        return $delivery_time;
    }

    protected $_alias = null;

    /**
     * @param $slug
     * @return OSC_Controller_Alias_Model|null
     */
    public function getAliasBySlug($slug) {
        if ($this->_alias === null) {
            $aliasModel = OSC::core('Controller_Alias_Model');

            $cache_key = 'getModelAliasBySlug_' . $slug;
            $cache = OSC::core('cache')->get($cache_key);
            if ($cache !== false && !empty($cache['slug'])) {
                $this->_alias = $aliasModel->bind($cache);
                return $this->_alias;
            }

            try {
                $this->_alias = $aliasModel->loadBySlug($slug);
                OSC::core('cache')->set($cache_key, $this->_alias->data, OSC_CACHE_TIME);
            } catch (Exception $ex) {

            }
        }

        return $this->_alias;
    }

    /**
     * @throws Exception
     */
    public function getDataCart($add_new_cart = false) {
        try {
            $cart = OSC::helper('catalog/common')->getCart();

            if ($cart instanceof Model_Catalog_Cart) {
                foreach ($cart->getLineItems() as $line_item) {
                    if ($line_item->isCrossSellMode()) {
                        continue;
                    }
                    Helper_Catalog_Common::displayedProductRegister($line_item->data['product_id']);
                }
            }

            $line_items = $cart->getLineItems();

            $error_cart = null;

            if (!$cart->isDeignIdInProduct()) {
                $error_cart = ["title" => "Error notification", "message" =>"Whoops, one of our busy bees updated your product(s) to make it better, but that means the item in your cart is outdated. Kindly personalize the item again by hitting the ‘Edit Item’ button. Once you do, we’ll replace the outdated item in your cart with it."];
                $line_items = $cart->getSortItemErrorDesign();
            }

            $result = [
                'quantity' => $cart->getQuantity(),
                'related_product' => ($cart instanceof Model_Catalog_Cart) && OSC::helper('core/setting')->get('catalog/cart/enable_related_product'),
                'cart_new_item_id' => $_SESSION['cart_new_item'] ?? null,
                'items' => [],
                'error_cart' => $error_cart,
                'enable_cross_sell' => 0
            ];

            if (OSC::helper('crossSell/common')->isEnableRecommend('cart')) {
                $result['enable_cross_sell'] = 1;
            }

            if (count($line_items) > 0) {
                /* @var $line_item Model_Catalog_Cart_Item*/
                foreach ($line_items as $line_item) {
                    if ($line_item->isCrossSellMode()) {
                        $result['items'][] = OSC::helper('crossSell/common')->getDataCartItemCrossSell($line_item);
                        continue;
                    }
                    $variant = $line_item->getVariant();
                    $product = $variant->getProduct();
                    $cut_off_timestamp = OSC::helper('catalog/common')->getCutOffTimestamp($variant);
                    $cut_off_title = trim(OSC::helper('core/setting')->get('shipping/cut_off_time/title'));
                    $country_code_place_of_manufacture = OSC::helper('core/country')->getCountryCodePlaceOfManufacture();
                    $is_disable_preview = Model_Catalog_Product::STATUS_PRODUCT_PREVIEW['DISABLE'];

                    $options = [];
                    foreach ($variant->getOptions() as $option) {
                        $options[$option['title']] = $option['value'];
                    }

                    $campaign_config = null;
                    try {
                        $campaign_config = OSC::helper('catalog/react_common')->getCampaignConfig($line_item->getId(), 'cart');
                    } catch (Exception $ex) {

                    }

                    $semitest_config = null;
                    if ($campaign_config == null) {
                        try {
                            $semitest_config = OSC::helper('catalog/react_common')->getSemitestConfig($line_item->getId(), 'cart');
                        } catch (Exception $ex) {

                        }
                    }

                    $shipping_semitest = null;
                    if (isset($product->data['meta_data']['shipping_price']) && count($product->data['meta_data']['shipping_price']) > 0) {
                        if (count($product->data['meta_data']['shipping_price']) > 0) {
                            foreach ($product->data['meta_data']['shipping_price'] as $shipping) {
                                if ($line_item->data['quantity'] >= $shipping['quantity']) {
                                    $shipping_semitest = $shipping;
                                }
                            }
                        }
                    }

                    if (isset($product->data['meta_data']['is_disable_preview'])) {
                        $is_disable_preview = $product->data['meta_data']['is_disable_preview'];
                    }

                    $error_cart_item = null;

                    if (!$line_item->checkDeignIdInProduct()) {
                        $error_cart_item = ["title" => "", "message" => "Personalize this item again using the Edit Item button, we’ll update it once you do."];
                    }

                    $campaign_data = $line_item->getCampaignData();

                    $product_tye_id = $variant->getProductType()->getId();
                    $result['items'][] = [
                        'id' => $line_item->getId(),
                        'quantity' => $line_item->data['quantity'],
                        'product_id' => $product->getId(),
                        'product_type_id' => $product_tye_id,
                        'product_type_variant_id' => $variant->data['product_type_variant_id'],
                        'product_title' => ($variant->getTitle() != '' ? ('('.$variant->getTitle().') ') : ''). $product->getProductTitle(),
                        'product_type_title' => $campaign_data['product_type']['title'] ?? '',
                        'product_option_keys' => !empty($campaign_data['product_type']['options']['keys'])
                            ? 'ukey:' . $campaign_data['product_type']['ukey'] . '|' . $campaign_data['product_type']['options']['keys']
                            : null,
                        'pack' => $line_item->getPackData(),
                        'price' => $line_item->getPrice(),
                        'options'=> $options,
                        'url' => $variant->getDetailUrl(),
                        'semitest_config' => $semitest_config,
                        'campaign_config' => $campaign_config,
                        'crosssell_config' => null,
                        'shipping_semitest' => $shipping_semitest,
                        'error_cart_item' => $error_cart_item,
                        'cut_off_time' => [
                            'title' => $cut_off_timestamp > 0 && $cut_off_title ? str_replace(['{{date}}', '{{tdate}}', '{{sdate}}', '{{tsdate}}', '{{date_time}}', '{{tdate_time}}', '{{sdate_time}}', '{{tsdate_time}}'], [date('d/m/Y', $cut_off_timestamp), date('M d, Y', $cut_off_timestamp), date('d/m', $cut_off_timestamp), date('M d', $cut_off_timestamp), date('H:i d/m/Y', $cut_off_timestamp), date('H:i M d, Y', $cut_off_timestamp), date('H:i d/m', $cut_off_timestamp), date('H:i M d', $cut_off_timestamp)], $cut_off_title) : '',
                            'message' => $cut_off_timestamp > 0 ? str_replace(['{{date}}', '{{tdate}}', '{{sdate}}', '{{tsdate}}', '{{date_time}}', '{{tdate_time}}', '{{sdate_time}}', '{{tsdate_time}}'], [date('d/m/Y', $cut_off_timestamp), date('M d, Y', $cut_off_timestamp), date('d/m', $cut_off_timestamp), date('M d', $cut_off_timestamp), date('H:i d/m/Y', $cut_off_timestamp), date('H:i M d, Y', $cut_off_timestamp), date('H:i d/m', $cut_off_timestamp), date('H:i M d', $cut_off_timestamp)], OSC::helper('core/setting')->get('shipping/cut_off_time/' . ($cut_off_timestamp <= time() ? 'message_after_time' : 'message_before_time'))) : '',
                            'is_reached' => $cut_off_timestamp < time()
                        ],
                        'personalized_in' => [
                            'country_code' => mb_strtolower($country_code_place_of_manufacture),
                            'country_name' => OSC::helper('core/country')->getCountryTitle($country_code_place_of_manufacture)
                        ],
                        'delivery_time' => OSC::helper('catalog/react_common')->getDeliveryTime($variant),
                        'is_available' => $line_item->isAvailableToOrder(),
                        'addon_services' => $line_item->getAddonServices(false, ['product_type_variant_id' => $variant->data['product_type_variant_id'], 'product_type_id' => $product_tye_id]),
                        'is_disable_preview' => $is_disable_preview
                    ];
                }
            }

            $result['subtotal_price'] = OSC::helper('catalog/cart')->getSubtotalWithoutDiscountOfCart($cart);

            $cart->calculateDiscount();
            $discount_codes = [];
            if (count($cart->getDiscountCodes()) > 0) {
                foreach ($cart->getDiscountCodes() as $discount_data) {
                    $discount_codes[] = $discount_data;
                }
            }
            $result['discount_code'] = $discount_codes;
            $result['total_price'] = $cart->getTotal(false, false, false) - $cart->getTipPrice();

            try {
                if (isset($_SESSION['cart_new_item'])) {
                    $line_item = $cart->getLineItems()->getItemByKey($_SESSION['cart_new_item']);
                    OSC::helper('report/common')->addRecordEvent('catalog/add_to_cart', [
                        'line_item_id' => $_SESSION['cart_new_item'],
                        'product_id' => $line_item->data['product_id'],
                        'quantity' => $line_item->data['quantity']
                    ]);
                    unset($_SESSION['cart_new_item']);
                }
            } catch (Exception $exception) { }

            return $result;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public function writeLogNextJS($params) {
        return;
        if (empty($params['osc_request_id'])) {
            return;
        }

        try {
            $mongodb = OSC::core('mongodb');
            $collection = 'osc_request_log';
            $document = [
                'request_ukey' => $params['osc_request_id'],
                'request_url' => $params['osc_request_url'],
                'start_timestamp' => $params['osc_request_start_time'],
                'end_timestamp' => $params['osc_request_end_time']
            ];

            $mongodb->insert($collection, $document, 'report');
        } catch (Exception $ex) {
            //
        }
    }

    public function autoApplyDiscountCode(Model_Catalog_Cart $cart) {
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

    public function updateDiscountCode(Model_Catalog_Cart $cart, $code) {
        try {
            try {
                OSC::helper('catalog/discountCode')->apply($code, $cart);
            } catch (Exception $ex) {
                $discount_codes = $cart->getDiscountCodes();

                if ($ex->getCode() != 404) {
                    if ($ex->getMessage() === 'Please fill contact info and shipping info before applying discount code') {
                        OSC::helper('catalog/checkout')->insertFootprint('APPLY_DISCOUNT_CODE_NO_INFO', $ex);
                        throw new Exception('Discount code will be applied on check out page after entering your shipping information', 4009);
                    }
                    throw new Exception($ex->getMessage(), $ex->getCode());
                } else {
                    if (count($discount_codes) > 0) {
                        throw new Exception('Discount code not exist', 404);
                    }
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
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public function removeDiscountCode(Model_Catalog_Cart $cart, $code) {
        try {
            $discount_codes = $cart->getDiscountCodes();

            if (count($discount_codes) < 1 || !in_array($code, array_keys($discount_codes))) {
                throw new Exception('Not have discount code to remove');
            }

            unset($discount_codes[$code]);

            $discount_codes = array_keys($discount_codes);

            $cart->setData('discount_codes', $discount_codes)
                ->save();

            return $cart;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
