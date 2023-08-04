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
 * @copyright	Copyright (C) 2011 by SNETSER JSC (http://www.snetser.com). All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Le Tuan Sang - batsatla@gmail.com
 */
class Observer_AutoAb_Tracking {

    /**
     * @throws Exception
     */
    public static function orderCreate(Model_Catalog_Order $order) {
        $location = $order->getLocationOfClient() ?? OSC::helper('core/common')->getClientLocation();
        $country_code = $location['country_code'] ?? $order->data['shipping_country_code'];
        $items_shipping_info = $order->getItemsShippingInfo();

        $first_ab_test_config_id = intval(OSC::cookieGet(OSC_Controller::makeRequestChecksum('first_ab_test_price_config', OSC_SITE_KEY)));
        $first_product_ab_test_id = intval(OSC::cookieGet(OSC_Controller::makeRequestChecksum('first_product_ab_test_price', OSC_SITE_KEY)));
        $first_product_view_id = intval(OSC::cookieGet(OSC_Controller::makeRequestChecksum('first_product_view', OSC_SITE_KEY)));

        $order_items_information = [];

        foreach ($order->getLineItems() as $order_item) {
            $order_item_id = $order_item->getId();
            $order_items_information[$order_item_id] = [
                'order_item_id' => $order_item_id,
                'order_item' => $order_item,
                'order_item_meta_id' => $order_item->getData('order_item_meta_id'),
                'product_id' => $order_item->getData('product_id'),
                'variant_id' => $order_item->getData('variant_id')
            ];
        }

        $lock_key = OSC_Database_Model::getPreLoadedModelLockKey();
        OSC_Database_Model::unlockPreLoadedModel($lock_key);

        $order_item_metas = OSC::helper('catalog/orderItemMeta')->getOrderItemMetaByIds(
            array_column($order_items_information, 'order_item_meta_id')
        );
        OSC::helper('catalog/orderItem')->updateOrderItemMetaOfList($order_items_information, $order_item_metas);

        OSC::helper('catalog/orderItem')->updateProductOfList(
            $order_items_information,
            array_column($order_items_information, 'product_id')
        );

        OSC::helper('catalog/orderItem')->updateProductVariantOfList(
            $order_items_information,
            array_column($order_items_information, 'variant_id')
        );

        OSC_Database_Model::lockPreLoadedModel($lock_key);
        foreach ($order_items_information as $order_item_information) {
            $order_item = $order_item_information['order_item'];

            if (!empty($order_item_information['is_order_item_cross_sell_mode']) ||
                (isset($order_item->data['additional_data']['atp']) && $order_item->data['additional_data']['atp'] == 1)) {
                continue;
            }

            try {
                /* Check if variant referer from Google, skip tracking ab test */
                if ($order->isGoogleReferer()) {
                    continue;
                }

                $product = $order_item_information['product'];
                $product_variant = $order_item_information['product_variant'];
                $is_product_campaign_mode = $product->isCampaignMode();
                $product_type_variant = $is_product_campaign_mode ? $order_item_information['product_type_variant'] : null;

                if (!($product instanceof Model_Catalog_Product) ||
                    !($product_variant instanceof Model_Catalog_Product_Variant) ||
                    $product_variant->hasBestPriceInCountry($country_code) ||
                    ($is_product_campaign_mode && $product_type_variant->hasBestPriceInCountryByStore($country_code))
                ) {
                    continue;
                }

                if ($product_variant->hasFixedPriceData()) {
                    continue;
                }

                $product_id = $product->getId();
                $product_variant_id = $product_variant->getId();
                $flag_add_tracking[$product_variant_id] = true;
                $product_type_variant_id = $product_variant->data['product_type_variant_id'];

                $auto_ab_config = $product->isSemitestMode() ?
                    OSC::helper('autoAb/productPrice')->getProductPriceConfigSemitest($product_id, $country_code) :
                    OSC::helper('autoAb/productPrice')->getProductPriceConfigByProduct($country_code, $product_id, $product_type_variant_id);

                if (!($auto_ab_config instanceof Model_AutoAb_ProductPrice_Config)) {
                    try {
                        $auto_ab_config = OSC_Database_Model::getPreLoadedModel(
                            'autoAb/productPrice_config',
                            $first_ab_test_config_id
                        );
                    } catch (Exception $ex) {}

                    // Handle tracking rev when first view product is not ab test and product of order not ab test
                    if ($first_product_view_id !== $first_product_ab_test_id &&
                        $product_id !== $first_product_ab_test_id
                    ) {
                        $auto_ab_config = null;
                    }

                    $product_id = $first_product_ab_test_id;
                    $flag_add_tracking[$product_variant_id] = 1;
                }

                if (!($auto_ab_config instanceof Model_AutoAb_ProductPrice_Config) ||
                    $auto_ab_config->data['status'] != Model_AutoAb_ProductPrice_Config::STATUS_ALLOW) {
                    continue;
                }

                if ($auto_ab_config->isFixedForAnyProducts() &&
                    !in_array($product_id, $auto_ab_config->data['fixed_product_ids']) &&
                    ($flag_add_tracking[$product_variant_id] !== 1)
                ) {
                    continue;
                }

                // Set best price for product when auto ab finish
                if ($auto_ab_config->isFinish($product_id)) {
                    OSC::helper('autoAb/productPrice')->setBestPriceInCountry($auto_ab_config, $product_id);
                    continue;
                }

                $is_tracking_abtest_cookie_key = OSC_Controller::makeRequestChecksum(
                    'is_tracking_abtest_' . $auto_ab_config->getId() . '_' . $product_id,
                    OSC_SITE_KEY
                );

                $able_to_tracking = intval(OSC::cookieGet($is_tracking_abtest_cookie_key));
                if (!is_null(OSC::cookieGet($is_tracking_abtest_cookie_key)) && !$able_to_tracking) {
                    continue;
                }

                // Add data to tracking
                $revenue = 0;
                $base_cost = 0;
                $price_ab_test = 0;
                $quantity = $order_item->data['other_quantity'] > 1 ?
                    $order_item->data['quantity'] * $order_item->data['other_quantity'] :
                    $order_item->data['quantity'];

                if ($auto_ab_config->isBegin($product_id)) {
                    $product_type_variant = $is_product_campaign_mode ? $order_item_information['product_type_variant'] : null;
                    $product_type_variant_price = $is_product_campaign_mode ? $product_type_variant->data['price'] : 0;
                    $base_cost_configs = $is_product_campaign_mode ?
                        OSC::helper('core/common')->getBaseCostConfigs($product_type_variant, $country_code) :
                        [];
                    $price_ab_test = OSC::helper('autoAb/productPrice')->getABTestPricePlus($auto_ab_config, $product_id);

                    // Calculate subtotal item - If item is pack, get origin price variant (not have discount)
                    $variant_price = $order_item->data['other_quantity'] > 1 ?
                        $product_variant->getPriceForCustomer()['price'] :
                        $order_item->data['price'];

                    if ($is_product_campaign_mode &&
                        $flag_add_tracking[$product_variant_id] !== 1 &&
                        ($variant_price - $product_type_variant_price) !== $price_ab_test
                    ) {
                        $flag_add_tracking[$product_variant_id] = false;
                        continue;
                    }

                    $revenue = $order_item->getRevenue($variant_price, $items_shipping_info);

                    /* Calculate Base Cost */
                    $base_cost = $base_cost_configs[$quantity] ?? $quantity * $base_cost_configs[1];
                    $base_cost = OSC::helper('catalog/common')->floatToInteger(floatval($base_cost));
                    /* End Calculate Base Cost */

                    /* Comment because another product type variant not set base cost */
                    /*if ($base_cost === 0) {
                        $flag_add_tracking[$product_variant_id] = false;
                        OSC::model('autoAb/productPrice_tracking')->getCollection()
                            ->addCondition('config_id', $auto_ab_config->getId())
                            ->addCondition('product_variant_id', $product_variant_id)
                            ->load()
                            ->delete();

                        if (BOX_TELEGRAM_TELEGRAM_GROUP_ID) {
                            $message = 'AB Test Campaign #' . $auto_ab_config->data['title'] .
                                ' not set base cost for ' . $product_type_variant->data['title'];

                            OSC::helper('core/telegram')->sendMessage($message, BOX_TELEGRAM_TELEGRAM_GROUP_ID);
                        }
                    }*/
                }

                if ($flag_add_tracking[$product_variant_id]) {
                    OSC::model('autoAb/productPrice_tracking')->setData([
                        'config_id' => $auto_ab_config->getId(),
                        'product_type_variant_id' => $product_type_variant_id,
                        'product_variant_id' => $product_variant_id,
                        'product_id' => $product_id,
                        'order_item_id' => $order_item->getId(),
                        'order_id' => $order->getId(),
                        'price_ab_test' => $price_ab_test,
                        'base_cost' => $base_cost,
                        'revenue' => $revenue,
                        'quantity' => $quantity
                    ])->save();
                }
            } catch (Exception $ex) {
                OSC::logFile('OrderID: ' . $order->getId() . '___LineItemID: ' . $order_item->getId(), 'logABTestError');
                OSC::helper('core/telegram')->sendMessage('AutoAB Error: ' . $ex->getMessage(), '-409036884');
            }
        }

        try {
            //Todo ToanLV đưa vô observer catalog/orderCreate
            OSC::helper('autoAb/abProduct')->trackingOrder($order);
        } catch (Exception $ex) {}
    }
}
