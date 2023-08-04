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
 * @copyright    Copyright (C) 2011 by SNETSER JSC (http://www.snetser.com). All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Le Tuan Sang - batsatla@gmail.com
 */
class Observer_Klaviyo_Common
{
    protected static $_group_product_mode_on = 0;

    public function __construct()
    {
        self::$_group_product_mode_on = intval(OSC::helper('core/setting')->get('catalog/klaviyo_feed/group_product_mode_on')) == 1 ? 1 : 0;
    }

    public static function initialize($tracking_events)
    {
        $enable_klaviyo_api = intval(OSC::helper('core/setting')->get('tracking/klaviyo/enable_4_onsite_behaviors')) === 1;

        if (!$enable_klaviyo_api) {
            return;
        }

        $html_content = [];
        $tracking_init_codes = [
            'var _learnq = _learnq || [];'
        ];

        $api_key = OSC::helper('klaviyo/common')->getApiKey();
        $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
        $checkout_url = OSC_FRONTEND_BASE_URL . '/' . $current_lang_key . '/checkout';

        $flag_react = defined('OSC_REACTJS') && OSC_REACTJS == 1;
        $klaviyo_events = [];

        if ($api_key != '') {
            foreach ($tracking_events as $event => $event_data) {
                switch ($event) {
                    case "catalog/product_view":
                        $variant = OSC_Database_Model::getPreLoadedModel('catalog/product_variant', $event_data['variant_id']);
                        if (!($variant instanceof Model_Catalog_Product_Variant)) {
                            continue;
                        }
                        $klaviyo_events['Viewed_Product'] = static::_ecommerceProductDataPageView($variant);
                        $klaviyo_events['Recently_Viewed_Items'] = static::_ecommerceProductDataRecentlyView($klaviyo_events['Viewed_Product']);
                        $tracking_init_codes[] = '_learnq.push(["track", "Viewed Product", ' . $klaviyo_events['Viewed_Product'] . ']);';
                        break;
                    case "catalog/add_to_cart":
                        $cart_item = OSC_Database_Model::getPreLoadedModel('catalog/cart_item', $event_data['line_item_id']);

                        if (!($cart_item instanceof Model_Catalog_Cart_Item) || $cart_item->isCrossSellMode()) {
                            continue;
                        }

                        $cart = $cart_item->getCart();
                        $variant = $cart_item->getVariant();
                        $items = static::_ecommerceProductData($cart);

                        $imageUrl = '';
                        try {
                            if ($variant instanceof Model_Catalog_Product_Variant &&
                                $variant->getImage() instanceof Model_Catalog_Product_Image
                            ) {
                                $imageUrl = $variant->getImage()->getUrl();
                            }
                        } catch (Exception $ex) {
                        }
                        $product = $variant->getProduct();
                        if (!$product instanceof Model_Catalog_Product) {
                            continue;
                        }

                        $categories = [
                            explode(', ', $product->data['product_type']),
                            $product->getListProductTagsWithoutRootTag(false, true)
                        ]; 

                        $klaviyo_events['Added_to_Cart'] = [
                            'event_name' => 'Added to Cart',
                            '$value' => $cart_item->getFloatAmount(),
                            'AddedItemProductName' => $product->getProductTitle(),
                            'AddedItemProductID' => self::$_group_product_mode_on === 1 ? $product->data['sku'] : $variant->data['sku'],
                            'AddedItemSKU' => $variant->data['ukey'],
                            'AddedItemCategories' => array_unique(array_merge(...$categories)),
                            'AddedItemCollections' => array_values(self::_getListCollectionTitle($product, false)),
                            'AddedItemImageURL' => $imageUrl,
                            'AddedItemURL' => $variant->getDetailUrl(),
                            'AddedItemPrice' => $cart_item->getFloatPrice(),
                            'AddedItemQuantity' => $cart_item->data['quantity'],
                            'ItemNames' => $items['name'],
                            'CheckoutURL' => $checkout_url,
                            'Items' => $items['data']
                        ];

                        $tracking_init_codes[] = '_learnq.push(["track", "Added to Cart", ' . OSC::encode($klaviyo_events['Added_to_Cart']) . ']);';
                        break;
                    case "catalog/checkout_initialize":
                        return;
                        try {
                            /* @var $cart Model_Catalog_Cart */
                            $cart = OSC::helper('catalog/common')->getCart(false);
                        } catch (Exception $ex) {
                            continue;
                        }

                        if (!($cart instanceof Model_Catalog_Cart)) {
                            continue;
                        }

                        $items = static::_ecommerceProductData($cart);

                        $categories = [];
                        $collections = [];
                        foreach ($items['data'] as $_itemData) {
                            $categories[] = $_itemData['ProductCategories'];
                            $collections[] = $_itemData['ProductCollections'];
                        }

                        $klaviyo_events['Started_Checkout'] = [
                            'event_name' => 'Started Checkout',
                            '$event_id' => $cart->getUkey(),
                            '$value' => $cart->getFloatTotal(),
                            'ItemNames' => $items['name'],
                            'CheckoutURL' => $checkout_url,
                            'Categories' => is_array($categories) ? array_values(array_unique(array_merge(...$categories))) : [],
                            'Collections' => is_array($collections) ? array_values(array_unique(array_merge(...$collections))) : [],
                            'Items' => $items['data']
                        ];
                        $tracking_init_codes[] = '_learnq.push(["track", "Started Checkout", ' . OSC::encode($klaviyo_events['Started_Checkout']) . ']);';
                        break;
                    default:
                        break;
                }
            }

            if ($flag_react) {
                return [
                    'social_chanel' => 'klaviyo',
                    'position' => 'header',
                    'events' => $klaviyo_events,
                    'api_key' => $api_key
                ];
            }
            
            $html_content[] = "<script type=\"application/javascript\" async src=\"//static.klaviyo.com/onsite/js/klaviyo.js?company_id=" . $api_key . "\"></script>";

            $tracking_init_codes = implode('', $tracking_init_codes);
            OSC::core('template')->push($tracking_init_codes, 'js_separate');

            return implode('', $html_content);
        }

        return null;


    }

    protected static function _ecommerceProductDataPageView($variant)
    {
        $imageUrl = '';
        try {
            if ($variant instanceof Model_Catalog_Product_Variant &&
                $variant->getImage() instanceof Model_Catalog_Product_Image
            ) {
                $imageUrl = $variant->getImage()->getUrl();
            }
        } catch (Exception $ex) {
        }

        $product = $variant->getProduct();
        if (!$product instanceof Model_Catalog_Product) {
            return [];
        }

        $categories = [
            explode(', ', $product->data['product_type']),
            $product->getListProductTagsWithoutRootTag(false, true)
        ]; 

        return [
            'event_name' => 'Viewed Product',
            'ProductName' => $product->getProductTitle(),
            'ProductID' => self::$_group_product_mode_on === 1 ? $product->data['sku'] : $variant->data['sku'],
            'SKU' => $variant->data['sku'],
            'Categories' => array_unique(array_merge(...$categories)),
            'Collections' => method_exists(Observer_Klaviyo_Common, '_getListCollectionTitle') ? array_values(self::_getListCollectionTitle($product, false)) : [],
            'ImageURL' => $imageUrl,
            'URL' => $variant->getDetailUrl(),
            'Brand' => $product->data['vendor'],
            'Price' => OSC::helper('catalog/common')->integerToFloat(intval($variant->getPriceForCustomer()['price'])),
            'CompareAtPrice' => OSC::helper('catalog/common')->integerToFloat(intval($variant->getPriceForCustomer()['compare_at_price'])),
        ];
    }

    protected static function _ecommerceProductDataRecentlyView($viewProductData) {
        return [
            'event_name' => 'Recently Viewed Items',
            'Title' => $viewProductData['ProductName'],
            'ItemId' => $viewProductData['ProductID'],
            'Categories' => $viewProductData['Categories'] ,
            'ImageUrl' => $viewProductData['ImageURL'] ,
            'Url' => $viewProductData['URL'] ,
            'Metadata' => [
                'Brand' => $viewProductData['Brand'],
                'Price' => $viewProductData['Price'],
                'CompareAtPrice' => $viewProductData['CompareAtPrice'],
            ],
        ];
    }

    protected static function _ecommerceProductData($cart)
    {
        $items = [];
        foreach ($cart->getLineItems() as $cart_item) {
            if ($cart_item->isCrossSellMode()) {
                continue;
            }
            $variant = $cart_item->getVariant();
            $product = $variant->getProduct();
            if (!$product instanceof Model_Catalog_Product) {
                return [];
            }
            $imageUrl = '';
            try {
                if ($variant instanceof Model_Catalog_Product_Variant &&
                    $variant->getImage() instanceof Model_Catalog_Product_Image
                ) {
                    $imageUrl = $variant->getImage()->getUrl();
                }
            } catch (Exception $ex) {
            }
            $items['name'][] = $product->getProductTitle();
            $items['data'][] = [
                "ProductID" => self::$_group_product_mode_on === 1 ? $variant->getProduct()->data['sku'] : $variant->data['sku'],
                "SKU" => $variant->data['sku'],
                "ProductName" => $product->getProductTitle(),
                "Quantity" => $cart_item->data['quantity'],
                "ItemPrice" => $cart_item->getFloatPrice(),
                "RowTotal" => $cart_item->getFloatPrice(),
                "ProductURL" => $variant->getDetailUrl(),
                "ImageURL" => $imageUrl,
                "ProductCategories" => explode(', ', $product->data['product_type']),
                "ProductCollections" => array_values(self::_getListCollectionTitle($product, false))
            ];
        }

        return $items;
    }

    public static function _getListCollectionTitle(Model_Catalog_Product $product, $return_string = true)
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
