<?php

class Controller_CrossSell_Api_Product extends Abstract_Frontend_ReactApiController {

    public function __construct() {
        parent::__construct();
    }

    public function actionGetProductDetail() {
        try {
            $default_product_type_variant_id = intval($this->_request->get('product_type_variant_id', 0));
            $design_id = intval($this->_request->get('design_id'));
            $type_page = strval($this->_request->get('type_page', null));

            if (!in_array($type_page, ['cart', 'thankyou'])) {
                throw new Exception('Not have type page');
            }

            $product_types = OSC::helper('crossSell/common')->getProductTypes();

            if (!($product_types instanceof Model_Catalog_ProductType_Collection) || $product_types->length() < 1) {
                throw new Exception('Not have product type');
            }

            $product_type_variants = [];
            $product_type_ids = [];

            foreach ($product_types as $product_type) {
                foreach ($product_type->getProductTypeVariants() as $productTypeVariant) {
                    $product_type_variants[$productTypeVariant->getId()] = $productTypeVariant;
                }
                $product_type_ids[] = $product_type->data['id'];
            }

            $location = OSC::helper('catalog/common')->getCustomerShippingLocation();
            $country_code = $location['country_code'];
            $province_code = $location['province_code'];

            $list_filter_product_type_variant_id = OSC::helper('supplier/location')->getPrintTemplateForCustomer(array_keys($product_type_variants), $country_code, $province_code, false);

            $product_type_variant_ids = array_column($list_filter_product_type_variant_id, 'product_type_variant_id');

            $data_request = [
                'design_id' => $design_id,
                'product_type_ids' => $product_type_ids,
            ];

            try {
                $response = OSC::helper('crossSell/common')->callApi(Helper_CrossSell_Common::GET_MOCKUP_LAYER_CONFIGS, $data_request);
            } catch (Exception $ex) {

            }

            if (!is_array($response) || count($response) < 1) {
                throw new Exception('Not have item cross sell');
            }

            $design = $response['design'];
            if (!$design || !is_array($design) || count($design) < 1) {
                throw new Exception('Not have design cross sell');
            }
            
            $preview_config = $response['preview_config'];
            if (!$preview_config || !is_array($preview_config) || count($preview_config) < 1) {
                throw new Exception('Not have config cross sell');
            }

            $type_design = isset($design['brightness']) && intval($design['brightness']) == 0 ? 'dark' : 'light' ;

            $data_config = OSC::helper('crossSell/common')->getProductTypeVariantIdsByTypeDesign($type_design);

            foreach ($product_type_variants as $product_type_variant_id => $model) {
                if (!in_array($product_type_variant_id, $product_type_variant_ids) || !in_array($product_type_variant_id, $data_config['list_product_type_variant_ids'])) {
                    unset($product_type_variants[$product_type_variant_id]);
                }
            }

            $result = [];

            $options = [
                'product_types' => $product_types, 
                'product_type_variants' => $product_type_variants ,
                'design_id' => $design_id, 
                'response' => $design, 
                'type_design' => $type_design, 
                'type_page' => $type_page, 
                'default_product_type_variant_id' => $default_product_type_variant_id
            ];

            $cart_option_config = OSC::helper('crossSell/common')->getCartFrmOptionConfig($country_code, $province_code, $options);

            foreach ($cart_option_config['product_variants'] as $k => $product_variant) {
                $_config = $preview_config[$product_variant['product_type']];
                OSC::helper('catalog/campaign')->replaceLayerUrl($_config, $product_variant['product_variant_ukey']);
                foreach ($_config as $_k => $config) {
                    $_config[$_k]['layer'][0] = OSC::core('template')->getImage($config['layer'][0]);
                }
                $cart_option_config['product_variants'][$k]['preview_config'] = $_config;
            }

            $result['product'] = [
                'id' => $design_id,
                'title' => $design['title'],
                'link' => $design['link'],
                'link_thumbnail' => $design['link_thumbnail'],
                'cart_options_config' => $cart_option_config
            ];

            OSC::helper('report/adTracking')->trackProductView('cs_' . $design_id);

            $this->sendSuccess($result);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }


    public function actionGetRelatedProducts() {
        $result = [];
        try {
            $cart = OSC::helper('catalog/common')->getCart();

            if (!($cart instanceof Model_Catalog_Cart)) {
                throw new Exception('Data is incorrect');
            }

            $type_page = trim(strval($this->_request->get('type_page')));

            if (!in_array($type_page, ['cart','thankyou'])) {
                throw new Exception('Data Page is incorrect');
            }

            $enable = OSC::helper('crossSell/common')->isEnableRecommend($type_page);

            if ($enable !== true) {
                throw new Exception('Not enable cross sell with type page '. $type_page);
            }

            $size = OSC::helper('crossSell/common')->getSizeRecommend($type_page);

            // get list product type variant ids config
            $list_default_data_ids = OSC::helper('crossSell/common')->getDefaultData();
            // loai bo cac product type variant chan ban
            $location = OSC::helper('catalog/common')->getCustomerShippingLocation();
            $country_code = $location['country_code'];
            $province_code = $location['province_code'];
            $list_filter_product_type_variant_id = OSC::helper('supplier/location')->getPrintTemplateForCustomer($list_default_data_ids['list_default_ids'], $country_code, $province_code, false);

            $product_type_variant_ids = array_column($list_filter_product_type_variant_id, 'product_type_variant_id');

            // start: sap xep product type variant theo price tot nhat
            $product_type_variants = OSC::helper('catalog/campaign')->getPreloadProductTypeVariant($product_type_variant_ids);

            $data_variants = [];
            $list_product_type_default = [];
            foreach ($product_type_variants as $product_type_variant) {
                $product_type = $product_type_variant->getProductType();
                $price = OSC::helper('crossSell/common')->getPriceData(true, $product_type_variant, $type_page);
                $data_variant = [
                    'product_type_id' => $product_type->getId(),
                    'product_type_variant_id' => $product_type_variant->getId(),
                    'price' => $price['price'],
                    'compare_at_price' => $price['compare_at_price'],
                    'percent' => 100 - number_format($price['price'] / $price['compare_at_price'] * 100, 0),
                    'ukey' => $product_type_variant->getOptionValues()['keys'],
                ];
                $list_product_type_default[$product_type->getId()][] = $data_variant;
                $data_variants[] = $data_variant;
            }

            $product_type_ids = [];
            foreach ($list_product_type_default as $product_type_id => $data_variant) {
                usort($data_variant, function($v1, $v2) {
                    return $v1['price'] <=> $v2['price'];
                });
                $list_product_type_default[$product_type_id] = $data_variant;
                $product_type_ids[] = $product_type_id;
            }

            // end: sap xep product type variant theo price tot nhat
            $default_data = [
                'default_dark' => 0,
                'default_light' => 0
            ];


            foreach ($list_product_type_default as $product_type_id => $datas) {
                foreach ($datas as $data) {
                    if ($default_data['default_dark'] < 1 &&
                        in_array($data['product_type_variant_id'], $list_default_data_ids['list_default_dark'])
                    ) {
                        $default_data['default_dark'] = $data['product_type_variant_id'];
                    }
                    if ($default_data['default_light'] < 1 &&
                        in_array($data['product_type_variant_id'], $list_default_data_ids['list_default_light'])
                    ) {
                        $default_data['default_light'] = $data['product_type_variant_id'];
                    }
                    if ($default_data['default_dark'] > 0 && $default_data['default_light'] > 0) {
                        break;
                    }
                }
            }

            if ($default_data['default_dark'] < 1 && $default_data['default_light'] < 1) {
                throw new Exception('Not have data product type variant to get design');
            }

            // Todo 2dcrosssell: xu ly lay du lieu theo tag
            $request_data = [
                'limit' => $size,
                'tags' => ["Trending"],
                'product_type_ids' => $product_type_ids
            ];

            $response = OSC::helper('crossSell/common')->callApi(Helper_CrossSell_Common::GET_DATA_RECOMMEND_CART,$request_data);

            $preview_config = $response['preview_config'];
            if (!$preview_config || !is_array($preview_config) || count($preview_config) < 1) {
                throw new Exception('Not have config cross sell');
            }

            $result = [
                'type' => '2dcrosssell'
            ];

            foreach (['recommend', 'best_selling'] as $key) {
                if (isset($response[$key])) {
                    foreach ($response[$key] as $item) {
                        $type_design = isset($item['brightness']) && intval($item['brightness']) == 0 ? 'dark' : 'light' ;
                        $data_variant = $data_variants[array_search($type_design == 'dark'? $default_data['default_dark'] : $default_data['default_light'], array_column($data_variants, 'product_type_variant_id'))];
                        $_config = $preview_config[$data_variant['product_type_id']];
                        OSC::helper('catalog/campaign')->replaceLayerUrl($_config, $data_variant['ukey']);
                        foreach ($_config as $_k => $config) {
                            $_config[$_k]['layer'][0] = OSC::core('template')->getImage($config['layer'][0]);
                        }        
                        $result['products'][$key][] = [
                            'id' => $item['id'],
                            'product_type_variant_id' => $data_variant['product_type_variant_id'],
                            'title' => $item['title'],
                            'link' => $item['link'],
                            'link_thumbnail' => $item['link_thumbnail'],
                            'price' => $data_variant['price'],
                            'compare_at_price' => $data_variant['compare_at_price'],
                            'percent' => $data_variant['percent'],
                            'product_variant_ukey' => $data_variant['ukey'],
                            'product_type_id' => $data_variant['product_type_id'],
                            'preview_config' => $_config,
                            'is_enable_sale_off_tag' => $type_page == 'cart' ? OSC::helper('core/setting')->get('cross_sell/cart/is_enable_sale_off_tag') == 1 : OSC::helper('core/setting')->get('cross_sell/thank_you/is_enable_sale_off_tag') == 1,
                        ];
                    }
                }
            }

        } catch (Exception $ex) {

        }
        $this->sendSuccess($result);
    }


}