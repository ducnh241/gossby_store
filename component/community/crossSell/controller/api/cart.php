<?php

class Controller_CrossSell_Api_Cart extends Abstract_Frontend_ReactApiController {

    public function __construct() {
        parent::__construct();
    }

    protected function _addProductToCart(Model_Catalog_Cart $cart, $item = []) {
        try {
            if (empty($item)) {
                throw new Exception('No item added');
            }

            $product_type_variant_id = intval($item['product_type_variant_id']);
            if ($product_type_variant_id < 1) {
                throw new Exception('Product type variant ID is empty');
            }

            $design_id = intval($item['design_id']);
            if ($design_id < 1) {
                throw new Exception('Design ID is empty');
            }

            $type_page = trim($item['type_page']);
            if (!in_array($type_page, ['cart','thankyou'])) {
                throw new Exception('Type page is incorrect');
            }

            if ($type_page == 'thankyou') {
                try {
                    OSC::helper('crossSell/common')->updateCartByThankyouPage($cart, $item);
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }
            }

            $quantity = intval($item['quantity']);

            if ($quantity < 1) {
                throw new Exception('Quantity is empty');
            }

            if ($quantity > 1000) {
                throw new Exception('Quantity does not exceed 1000');
            }

            try {
                $product_type_variant = OSC::model('catalog/productType_variant')->load($product_type_variant_id);

                $product_type = $product_type_variant->getProductType();

                //Parse product_type_variant ukey to string like "mug_color:white|mug_size:11oz"
                $options = $product_type_variant->getOptionValues();
            } catch (Exception $exception) {
                throw new Exception('No product type variant found');
            }

            try {
                $data = [
                    'cart_id' => $cart->getId(),
                    'quantity' => $quantity,
                    'product_type_variant' => $product_type_variant,
                    'type_page' => $type_page,
                    'additional_data' => [
                        'is_cross_sell' => 1
                    ]
                ];

                $data_add_cart = OSC::helper('crossSell/common')->getDataCartItemDefault($cart, $data);

                try {
                    $response = OSC::helper('crossSell/common')->callApi(Helper_CrossSell_Common::GET_MOCKUP_LAYER_CONFIG, [
                        'design_id' => $design_id,
                        'product_type_id' => $product_type->getId(),
                    ]);
                } catch (Exception $ex) {
                    throw new Exception('Our service encountered an error in processing your added items. Please contact customer support, our agents will be there to help right away.');
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

                OSC::helper('catalog/campaign')->replaceLayerUrl($preview_config, $options['keys']);
                foreach ($preview_config as $k => $side) {
                    $preview_config[$k]['layer'][0] = OSC::core('template')->getImage($side['layer'][0]);
                }

                $custom_data = [
                    [
                        'key' => '2dcrosssell',
                        'data' => [
                            'product_type_variant_id' => $product_type_variant_id,
                            'product_type' => [
                                'title' => $product_type_variant->data['title'],
                                'ukey' => $product_type->data['ukey'],
                                'options' => $options,
                                'id' => $product_type->getId()
                            ],
                            'preview_config' => $preview_config,
                            'print_template' => [
                                'segment_source' => [
                                    'front' => [
                                        'source' => [
                                            'type' => 'image',
                                            'design_id' => $design_id,
                                            'title' => $design['title'],
                                            'mockup_url' => $design['mockup_url'],
                                            'design_url' => $design['design_url'],
                                            'path_folder_s3' => $design['path_folder_s3'],
                                        ]
                                    ],
                                    'back' => null
                                ]
                            ],
                            'type_page' => $type_page
                        ]
                    ]
                ];

                $line_item = OSC::model('catalog/cart_item');

                $line_item_ukey = $line_item->makeUkey($cart->getId(), 0, $custom_data, [], $data['additional_data']);

                try {
                    $line_item->loadByUKey($line_item_ukey);
                } catch (Exception $ex) { }

                if ($line_item->getId() < 1) {
                    $data_add_cart['custom_data'] = $custom_data;

                    $line_item->setData($data_add_cart)->save();

                    $cart->getLineItems()->addItem($line_item);
                } else {
                    $quantity = $line_item->data['quantity'] + $quantity;
                    $data_update_cart = [
                        'quantity' => $quantity
                    ];

                    $line_item->setData($data_update_cart)->save();

                    $line_item->reload();
                }
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            //ADD TRACKING AD
            try {
                OSC::helper('report/adTracking')->trackAddToCart('cs_' . $design_id, $quantity);
            } catch (Exception $ex) {
            }

            OSC::helper('catalog/common')->updateCartQuantity($cart);

            $_SESSION['cart_new_item'] = $line_item->getId();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $this::CODE_2DCROSSSELL_ADD_TO_CART_ERROR);
        }
    }

    public function actionAddCart() {
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
                        'Cart ID #' . $cart->getId() . ' 2dcrosssell get error: ' . $ex->getMessage() . "\n" .
                        'IP Customer: ' . $client_ip . '. Sref ID: ' . intval($_REQUEST['sref']) . "\n" .
                        'Request URI: ' . $_SERVER['REQUEST_URI'] . "\n" .
                        'Referer: ' . $_SERVER['HTTP_REFERER'];

                    OSC::helper('core/telegram')->sendMessage($message, $telegram_group_id);
                }
            } catch (Exception $exception) {

            }

            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

}