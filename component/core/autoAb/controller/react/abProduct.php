<?php

class Controller_AutoAb_React_AbProduct extends Abstract_Frontend_ReactApiController
{

    public function actionGetDetailProductUrl()
    {
        $hub_ukey = $this->_request->get('hub_ukey', null);

        try {
            $config = OSC::model('autoAb/abProduct_config')->loadByUKey($hub_ukey);
        } catch (Exception $ex) {
            $this->_ajaxError('Hub config is not exist with sku: ' . $hub_ukey, $this::CODE_NOT_FOUND);
        }

        try {
            $hub_id = $config->getId();

            if (!$config->isBegin()) {
                throw new Exception('Hub is not using: ' . $hub_ukey, $this::CODE_NOT_FOUND);
            }

            // Get product Distribution
            $product_map_distribution = OSC::helper('autoAb/abProduct')->getProductDistribution($hub_id);

            $cookie_key_ab_product = OSC::helper('autoAb/abProduct')->getAutoAbProductKey();
            $ab_product_data = OSC::cookieGet($cookie_key_ab_product) ? OSC::decode(urldecode(OSC::cookieGet($cookie_key_ab_product)), true) : [];

            $increment_acquisition = false;
            // Increment acquisition if agent not exist cookie or agent get hub config other
            /* @var $product Model_Catalog_Product */
            if (empty($ab_product_data) || !isset($ab_product_data['hub_ukey']) || $ab_product_data['hub_ukey'] != $hub_ukey) {
                $ab_product_data = [
                    'hub_ukey' => $hub_ukey,
                    'product_id' => $product_map_distribution['product_id']
                ];
                $product = OSC::model('catalog/product')->load($product_map_distribution['product_id']);
                $increment_acquisition = true;
            } else {
                $product = OSC::model('catalog/product')->load($ab_product_data['product_id']);
            }

            OSC::cookieSetCrossSite($cookie_key_ab_product, OSC::encode($ab_product_data));

            if ($increment_acquisition) {
                OSC::helper('autoAb/abProduct')->incrementAcquisitionProduct($product_map_distribution['id'], $product_map_distribution['acquisition'] + 1);
            }

            $this->sendSuccess([
                'redirect_url' => '/product/' . $product->getUkey() . '/' . $product->data['slug']
            ]);
        } catch (Exception $ex) {
            /* @var $map Model_AutoAb_AbProduct_Map */
            $product_maps = OSC::model('autoAb/abProduct_map')->getCollection()
                ->addCondition('config_id', $config->getId())
                ->load();

            if ($product_maps->length()) {
                $product = null;
                $product_map_default = null;
                try {
                    foreach ($product_maps as $map) {
                        if ($map->data['is_default']) {
                            $product_map_default = $map;
                            $product = OSC::model('catalog/product')->load($map->data['product_id']);
                            break;
                        }
                    }
                    if (!$product_map_default) {
                        $product_map_default = $product_maps->getItem();
                        $product = OSC::model('catalog/product')->load($product_maps->getItem()->data['product_id']);
                    }

                    $product_map_default->setData([
                        'is_default' => Model_AutoAb_AbProduct_Map::IS_DEFAULT['ENABLE']
                    ])->save();

                    $this->sendSuccess([
                        'redirect_url' => '/product/' . $product->getUkey() . '/' . $product->data['slug']
                    ]);

                } catch (Exception $ex) {}
            }

            $this->_ajaxError($ex->getMessage(), $ex->getCode());
        }
    }
}
