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
 * @copyright    Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Observer_Catalog_Shipping
{

    public static function collectRates($params) {
        $country_code = $params['shipping_address']['country_code'];
        $province_code = $params['shipping_address']['province_code'];

        try {
            $country = OSC::helper('core/country')->getCountry($country_code);

            if ($country->getId() < 1) {
                throw new Exception('Not found country');
            }

            $country_id = 'c'.$country->getId();

            try {
                $province = OSC::helper('core/country')->getCountryProvince($country_code, $province_code);
                $province_id = 'p' . $province->getId();
            } catch (Exception $ex) {
                $province_id = '*';
            }
        } catch (Exception $ex) {
            $country_id = '*';
            $province_id = '*';
        }

        $product_type_variant_ids = $params['product_type_variant_ids'];
        $product_type_ids = $params['product_type_ids'];

        $rate_setting_data = OSC::model('shipping/rate')->getRatePriceByLocationData($product_type_ids, $product_type_variant_ids, $country_id, $province_id);
        $delivery_setting_data = OSC::model('shipping/deliveryTime')->getDeliveryTimeByLocationData($product_type_ids, $product_type_variant_ids, $country_id, $province_id);
        $pack_setting_data = OSC::model('shipping/pack')->getShippingPackByLocationData($product_type_ids, $product_type_variant_ids, $country_id, $province_id);

        $shipping_data = OSC::helper('shipping/common')->groupSettingShipping($rate_setting_data, $delivery_setting_data, $pack_setting_data, $country_id, $province_id);

        $rates = Observer_Catalog_Shipping::_rateByQuantity($params, $shipping_data, $country_id, $province_id);

        if (OSC::helper('core/setting')->get('shipping/table_rate/free_shipping/enable')) {
            $subtotal_to_get_free_shipping = OSC::helper('catalog/common')->floatToInteger(floatval(OSC::helper('core/setting')->get('shipping/table_rate/free_shipping')));

            if (OSC::helper('catalog/common')->floatToInteger($params['total_price']) > $subtotal_to_get_free_shipping) {
                $rates[] = [
                    'key' => 'free',
                    'title' => 'Free shipping',
                    'amount' => 0,
                    'amount_tax' => 0,
                    'amount_semitest' => 0,
                    'items_shipping_info' => [],
                    'estimate_timestamp' => 0,
                    'processing_timestamp' => 0
                ];
            }
        }

        if (!is_array($rates) || count($rates) < 1 ) {
            return [];
        }

        return new Helper_Catalog_Shipping_Carrier('table_rate', '', $params['ship_from'], $rates);
    }

    public static function collectCarrierTrackingNumberPatterns()
    {
        return [
            'royal_mail' => [
                'title' => 'Royal Mail',
                'pattern' => '^([A-Z]{2}\\d{9}GB)$',
                'tracking_url' => 'https://www.royalmail.com/portal/rm/track?trackNumber={{tracking_number}}'],
            'ups' => [
                'title' => 'UPS',
                'pattern' => '^(1Z)|^(K\\d{10}$)|^(T\\d{10}$)',
                'tracking_url' => 'https://wwwapps.ups.com/tracking/tracking.cgi?tracknum={{tracking_number}}'],
            'canada_post' => [
                'title' => 'Canada Post',
                'pattern' => '((CA)$|^\\d{16}$)',
                'tracking_url' => 'http://www.canadapost.ca/cpotools/apps/track/personal/findByTrackNumber?trackingNumber={{tracking_number}}'],
            'china_post' => [
                'title' => 'China Post',
                'pattern' => '^(R|CP|E|L)\\w+CN$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'postnord' => [
                'title' => 'PostNord',
                'pattern' => '\\d{3}5705983\\d{10}|DK$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'usps' => [
                'title' => 'USPS',
                'pattern' => '^((?:94001|92055|94073|93033|92701|94055|92088|920…{11}|[A-Z]{2}\\d{9}US|(?:EV|CX)d{9}CN|LK\\d{9}HK)$',
                'tracking_url' => 'https://tools.usps.com/go/TrackConfirmAction?tLabels={{tracking_number}}'],
            'dhl_express' => [
                'title' => 'DHL Express',
                'pattern' => '^(\\d{10,11}$)',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'dhl_ecommerce' => [
                'title' => 'DHL eCommerce',
                'pattern' => '^(GM\\d{16,18}$)|^([A-Z0-9]{14}$)|^(\\d{22}$)',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'dhl_ecommerce_asia' => [
                'title' => 'DHL eCommerce Asia',
                'pattern' => '^P[A-Z0-9]{14}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'eagle' => [
                'title' => 'Eagle',
                'pattern' => null,
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'purolator' => [
                'title' => 'Purolator',
                'pattern' => '^[A-Z]{3}\\d{9}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'australia_post' => [
                'title' => 'Australia Post',
                'pattern' => '^[A-Z]{2}\\d{9}AU$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'new_zealand_post' => [
                'title' => 'New Zealand Post',
                'pattern' => '^[A-Z]{2}\\d{9}NZ$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'correios' => [
                'title' => 'Correios',
                'pattern' => '^[A-Z]{2}\\d{9}BR$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'la_poste' => [
                'title' => 'La Poste',
                'pattern' => '^(\\d[a-z]\\d{11}|[a-z]{2}\\d{9}[a-z]{2})$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'tnt' => [
                'title' => 'TNT',
                'pattern' => '^((GE|RU|GD|CT)\\d{9}[a-z]{2}|\\d{8,9})$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'whistl' => [
                'title' => 'Whistl',
                'pattern' => '^([a-z]{1,4}\\d\\w{13,20}|\\d{15,16}|\\w\\h\\d{7})$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            '4px' => [
                'title' => '4PX',
                'pattern' => '^RF\\d{9}SG$|^RT\\d{9}HK$|^7\\d{10}$|^P0{4}\\d{8}$|^JJ\\d{9}GB$|^MS\\d{8}XSG$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'apc' => [
                'title' => 'APC',
                'pattern' => '^PF\\d{11}$|^\\d{13}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'fsc' => [
                'title' => 'FSC',
                'pattern' => '^((?:LS|LM|RW|RS|RU|RX)\\d{9}(?:CN|CH|DE)|(?:WU\\d{13})|(?:\\w{10}$|\\d{22}))$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'gls' => [
                'title' => 'GLS',
                'pattern' => '^\\d{10,14}$|^Y\\w{7}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'globegistics' => [
                'title' => 'Globegistics',
                'pattern' => '^JJ\\d{9}GB$|^(LM|CJ|LX|UM|LJ|LN)\\d{9}US$|^(GAMLABNY|BAIBRATX|SIMGLODE)\\d{10}$|^\\d{10}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'amazon_logistics_us' => [
                'title' => 'Amazon Logistics US',
                'pattern' => '^TBA\\d{12,13}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'amazon_logistics_uk' => [
                'title' => 'Amazon Logistics UK',
                'pattern' => '^Q\\d{11,13}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'bluedart' => [
                'title' => 'Bluedart',
                'pattern' => '^\\d{9,11}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'delhivery' => [
                'title' => 'Delhivery',
                'pattern' => '^\\d{11,12}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'japan_post_en_' => [
                'title' => 'Japan Post (EN)',
                'pattern' => '^[a-z]{2}\\d{9}JP|^\\d{11}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'japan_post_ja_' => [
                'title' => 'Japan Post (JA)',
                'pattern' => '^[a-z]{2}\\d{9}JP|^\\d{11}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'sagawa_en_' => [
                'title' => 'Sagawa (EN)',
                'pattern' => '^\\d{10,12}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'sagawa_ja_' => [
                'title' => 'Sagawa (JA)',
                'pattern' => '^\\d{10,12}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'singapore_post' => [
                'title' => 'Singapore Post',
                'pattern' => '^[a-z]{2}\\d{9}SG$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'yamato_en_' => [
                'title' => 'Yamato (EN)',
                'pattern' => '^\\d{12}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'yamato_ja_' => [
                'title' => 'Yamato (JA)',
                'pattern' => '^\\d{12}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'dpd' => [
                'title' => 'DPD',
                'pattern' => '^\\d{10}$|^\\d{14}\\w{0,1}$|^\\d{18}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'dpd_uk' => [
                'title' => 'DPD UK',
                'pattern' => '^\\d{10}$|^\\d{14}\\w{0,1}$|^\\d{18}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'dpd_local' => [
                'title' => 'DPD Local',
                'pattern' => '^\\d{10}$|^\\d{14}\\w{0,1}$|^\\d{18}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'newgistics' => [
                'title' => 'Newgistics',
                'pattern' => '^[a-z]{2}\\d{8}$|^\\d{16}$|^\\d{22}$|^\\d{34}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'sf_express' => [
                'title' => 'SF Express',
                'pattern' => '^\\d{12}$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}'],
            'postnl' => [
                'title' => 'PostNL',
                'pattern' => '^3S[A-Z0-9]{11,13}|(?:CC|CD|CP)[A-Z0-9]{9}NL$',
                'tracking_url' => 'https://m.17track.net/en/track#nums={{tracking_number}}']];
    }

    protected function _rateByQuantity($params, $shipping_setting_data, $country_id, $province_id) {
        try {
            $grouped_quantity = [];
            $grouped_quantity_pack = [];
            $grouped_delivery = [];

            $flag_semi_test = false;

            if (count($params['variant_detail_ids']) > 0) {
                foreach ($params['variant_detail_ids'] as $key => $product_type) {
                    if ($product_type['product_type_variant_id'] == 0) {
                        continue;
                    }

                    $grouped_quantity[$product_type['product_type_variant_id'] . '_' . 0] = [
                        'quantity' => $product_type['quantity'],
                        'product_type_id' => $product_type['product_type_id'],
                        'item_id' => 0
                    ];

                    $grouped_delivery[$product_type['product_type_variant_id'] . '_' . 0] = [
                        'quantity' => $product_type['quantity'],
                        'product_type_id' => $product_type['product_type_id'],
                        'item_id' => 0
                    ];
                }
            }

            $items = null;

            if (count($params['cart_item_ids']) > 0) {
                $items = OSC::model('catalog/cart_item')->getCollection()->load(array_keys($params['cart_item_ids']));
                foreach ($items as $item) {
                    if ($item->isSemiTest()) {
                        $flag_semi_test = true;
                        continue;
                    }

                    // Get tax value of item
                    $tax_value = $item->getTaxValue() ?? 0;

                    /* Start Calculate shipping pack if exists */
                    $pack_data = $item->getPackData();

                    $product_type_data = $params['cart_item_ids'][$item->getId()];

                    if ($pack_data !== null && $pack_data['id'] !== 0) {
                        $pack_key = 'pack' . $pack_data['quantity'];

                        $grouped_quantity_pack[$product_type_data['product_type_variant_id'] . '_' . $item->getId()] = [
                            'quantity' => $item->data['quantity'],
                            'product_type_id' => $product_type_data['product_type_id'],
                            'tax_value' => $tax_value,
                            'pack_key' => $pack_key,
                            'item_id' => $item->getId()
                        ];

                        /* End Calculate shipping pack if exists */
                    } else {
                        // Group product type to calculate shipping by regular recipe
                        $grouped_quantity[$product_type_data['product_type_variant_id'] . '_' . $item->getId()] = [
                            'quantity' => $item->data['quantity'],
                            'product_type_id' => $product_type_data['product_type_id'],
                            'tax_value' => $tax_value,
                            'item_id' => $item->getId()
                        ];
                    }

                    $grouped_delivery[$product_type_data['product_type_variant_id'] . '_' . $item->getId()] = [
                        'quantity' => $item->data['quantity'],
                        'product_type_id' => $product_type_data['product_type_id'],
                        'item_id' => $item->getId()
                    ];
                }
            }

            /* semitest remove shipping method not default */
            if ($flag_semi_test) {
                $shipping_method_not_default = OSC::model('shipping/methods')->getCollection()->getShippingMethodNotDefault();

                if (count($shipping_method_not_default) > 0) {
                    foreach ($shipping_method_not_default as $method_id) {
                        if (isset($shipping_setting_data[$method_id['id']])) {
                            unset($shipping_setting_data[$method_id['id']]);
                        }
                    }
                }
            }

           return OSC::helper('shipping/common')->calculateRates($shipping_setting_data, $grouped_quantity_pack, $grouped_quantity, $grouped_delivery,$country_id, $province_id, $items ,$flag_semi_test);
        } catch (Exception $ex) {
            return null;
        }
    }


}
