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
class Observer_Catalog_Backend {

    const MAX_COLLECTION_ID_IN_CUSTOM_LABEL = 10;

    public static function collectSettingSection() {
        return [
            [
                'key' => 'shipping',
                'priority' => 2,
                'icon' => 'truck-regular',
                'title' => 'Shipping',
                'description' => 'Manage shipping rates and delivery date'
            ],
            [
                'key' => 'catalog',
                'priority' => 2,
                'icon' => 'tag',
                'title' => 'Catalog',
                'description' => 'Configure Your Catalog Settings'
            ],
            [
                'key' => 'review',
                'priority' => 2,
                'icon' => 'setting-group-emotion',
                'title' => 'Reviews',
                'description' => 'Set up your reviews page'
            ],
            [
                'key' => 'location_block',
                'priority' => 2,
                'icon' => 'setting-abandon',
                'title' => 'Shipping Exclusions',
                'description' => 'Select locations you don’t want to ship to'
            ],
        ];
    }

    public function collectSettingType() {
        return [
            [
                'key' => 'catalog_collection',
                'template' => 'catalog/setting_type/collection',
                'validator' => ['Observer_Catalog_Backend', 'validateProductCollection']
            ],
            [
                'key' => 'frequently_bought_type',
                'template' => 'catalog/setting_type/frequently_bought_together'
            ],
            [
                'key' => 'catalog_product_type',
                'template' => 'catalog/setting_type/product_type',
                'validator' => ['Observer_Catalog_Backend', 'validateProductType']
            ],
            [
                'key' => 'catalog_email_request_review',
                'template' => 'catalog/setting_type/email_request_review',
            ],
            [
                'key' => 'catalog_product_plus_minus_price_by_country',
                'template' => 'catalog/setting_type/plus_minus_price_by_country',
                'validator' => ['Observer_Catalog_Backend', 'validatePlusMinusPriceTable']
            ],
            [
                'key' => 'catalog_google_feed_table',
                'template' => 'catalog/setting_type/google_feed_table',
                'validator' => ['Observer_Catalog_Backend', 'validateGoogleFeedTable']
            ],
            [
                'key' => 'catalog_collection_beta_feed',
                'template' => 'catalog/setting_type/collection_beta_feed',
                'validator' => ['Observer_Catalog_Backend', 'validateCollectionBetaFeed']
            ],
            [
                'key' => 'catalog_collection_feed_table',
                'template' => 'catalog/setting_type/collection_feed_table',
                'validator' => ['Observer_Catalog_Backend', 'validateCollectionFeedTable']
            ],
            [
                'key' => 'catalog_feed_block_product_keywords',
                'template' => 'catalog/setting_type/feed_block_keyword',
                'validator' => ['Observer_Catalog_Backend', 'validateFeedBlockProductKeywords']
            ],
            [
                'key' => 'trust_pilot_rating_value',
                'template' => 'catalog/setting_type/trust_pilot_rating_value'
            ],
            [
                'key' => 'subscriber_discount',
                'template' => 'catalog/setting_type/subscriber_discount',
                'validator' => ['Observer_Catalog_Backend', 'validateSubscriberDiscount']
            ],
            [
                'key' => 'place_of_manufacture',
                'template' => 'catalog/setting_type/place_of_manufacture',
                'validator' => ['Observer_Catalog_Backend', 'validatePlaceOfManufacture']
            ],
            [
                'key' => 'tip',
                'template' => 'catalog/setting_type/tip',
                'validator' => ['Observer_Catalog_Backend', 'validateTipPercent']
            ],
            [
                'key' => 'tip_country',
                'template' => 'catalog/setting_type/tip_country',
            ],
            [
                'key' => 'tip_maximum',
                'template' => 'catalog/setting_type/tip_maximum',
                'validator' => ['Observer_Catalog_Backend', 'validateTipMaximum']
            ],
            [
                'key' => 'select_countries',
                'template' => 'catalog/setting_type/select_countries',
                'validator' => ['Observer_Catalog_Backend', 'validateCountries']
            ],
            [
                'key' => 'place_of_manufacture_default',
                'template' => 'catalog/setting_type/place_of_manufacture_default',
                'validator' => ['Observer_Catalog_Backend', 'validatePlaceOfManufactureDefault']
            ],
            [
                'key' => 'catalog/campaign/fleeceBlanket_50x60/list_country_by_premium',
                'template' => 'catalog/campaign/setting_type/listCountrySalePremium',
                'validator' => ['Observer_Catalog_Backend', 'validateCountry']
            ],
            [
                'key' => 'shipping_block_countries',
                'template' => 'catalog/setting_type/shipping/block_countries',
                'validator' => ['Observer_Catalog_Backend', 'validateCountry']
            ],
            [
                'key' => 'catalog_shipping_quantity_rate',
                'template' => 'catalog/setting_type/shipping/quantity_rate_table',
                'validator' => ['Observer_Catalog_Backend', 'validateQuantityRateTable']
            ],
            [
                'key' => 'catalog_shipping_delivery_time',
                'template' => 'catalog/setting_type/shipping/delivery_time',
                'validator' => ['Observer_Catalog_Backend', 'validateDeliveryTime']
            ],
            [
                'key' => 'catalog_cut_off_time_table',
                'template' => 'catalog/setting_type/shipping/cut_off_time_table',
                'validator' => ['Observer_Catalog_Backend', 'validateCutOffTimeTable']
            ],
            [
                'key' => 'shipping_block_countries',
                'template' => 'catalog/setting_type/shipping/block_countries',
                'validator' => ['Observer_Catalog_Backend', 'validateCountry']
            ],
            [
                'key' => 'select_members',
                'template' => 'catalog/setting_type/select_members'
            ],
            [
                'key' => 'catalog_minsold_bypass_product',
                'template' => 'catalog/setting_type/minsold_bypass_product'
            ],
            [
                'key' => 'catalog_minsold_quantity',
                'template' => 'catalog/setting_type/minsold_quantity'
            ],
        ];
    }

    public static function collectSettingItem() {
        return [
            // Start setting auto listing, discard product
            [
                'section' => 'catalog',
                'key' => 'auto_listing_discarded_product',
                'type' => 'group',
                'title' => 'Auto Listing, Discarded Product'
            ],
            [
                'section' => 'catalog',
                'group' => 'auto_listing_discarded_product',
                'key' => 'catalog/auto_listing/enable',
                'type' => 'switcher',
                'title' => 'Automatically List Products',
                'default' => 1,
                'line_before' => true
            ],
            [
                'section' => 'catalog',
                'group' => 'auto_listing_discarded_product',
                'key' => 'catalog/auto_listing/quantity_sold',
                'type' => 'number',
                'min' => 1,
                'title' => 'Minimum quantity sold for a product to be listed'
            ],

            [
                'section' => 'catalog',
                'group' => 'auto_listing_discarded_product',
                'key' => 'catalog/auto_discard/enable',
                'type' => 'switcher',
                'title' => 'Automatically Discard Underperforming Products',
                'full_row' => true,
                'default' => 1,
                'line_before' => true
            ],
            [
                'section' => 'catalog',
                'group' => 'auto_listing_discarded_product',
                'key' => 'catalog/auto_discard/quantity_sold',
                'type' => 'number',
                'min' => 1,
                'title' => 'Minimum quantity unsold to discard product'
            ],
            [
                'section' => 'catalog',
                'group' => 'auto_listing_discarded_product',
                'key' => 'catalog/auto_discard/time_to_discard',
                'type' => 'number',
                'min' => 1,
                'title' => 'Number of days unsold for product to be discarded'
            ],
            // End setting auto listing, discard product
            [
                'section' => 'catalog',
                'key' => 'general',
                'type' => 'group',
                'title' => 'Currency Conversion'
            ],
            [
                'section' => 'catalog',
                'group' => 'general',
                'key' => 'catalog/convert_currency/enable',
                'type' => 'switcher',
                'title' => 'Enable Currency Conversion',
                'full_row' => true,
                'default' => 1,
                'line_before' => true
            ],
            [
                'section' => 'catalog',
                'group' => 'general',
                'key' => 'catalog/convert_currency/auto_convert_by_location',
                'type' => 'switcher',
                'title' => 'Automatically Convert Currency based on Location',
                'full_row' => true,
                'default' => 1
            ],
            [
                'section' => 'catalog',
                'key' => 'product_setup',
                'type' => 'group',
                'title' => 'Set Product Defaults',
            ],
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/product_default/listing',
                'type' => 'switcher',
                'title' => 'List Products by Default',
                'full_row' => true,
                'default' => 1
            ],
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/product_default/listing_admin',
                'type' => 'switcher',
                'title' => 'Listing Status only editable by administrators ',
                'full_row' => true,
                'default' => 0
            ],
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/product_listing/country_render_beta_product',
                'type' => 'select_countries',
                'full_row' => true,
                'title' => 'Select countries to show beta product in listing page'
            ],
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/product/disable_compare_at_price',
                'type' => 'switcher',
                'title' => 'Hide Compare At Price',
                'full_row' => true,
                'line_before' => true
            ],
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/cart/enable_related_product',
                'type' => 'switcher',
                'title' => 'Display suggested products in cart',
                'full_row' => true,
                'line_before' => true
            ],
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/detail/enable_recently_product',
                'type' => 'switcher',
                'title' => 'Display recently viewed products in product page',
                'full_row' => true
            ],
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/product/enable_product_detail_v4',
                'type' => 'switcher',
                'title' => 'Enable Product Detail v4',
                'full_row' => true,
                'line_before' => true
            ],
            /*//Start Buy design
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/product/default_buy_design_price',
                'type' => 'text',
                'title' => 'Default buy design price ($)',
                'line_before' => true,
                'full_row' => true,
                'validator' => ['Observer_Catalog_Backend', 'validateFloatPrice']
            ],
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/product/enable_buy_design',
                'type' => 'switcher',
                'title' => 'Enable buy design'
            ],
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/product/list_product_type_design',
                'type' => 'catalog_product_type',
                'title' => 'Choose those product type for buy design',
                'multiple' => true,
                'full_row' => true
            ],
            //End Buy design*/
            //Start Trust Pilot
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/product/enable_trust_pilot_signature',
                'sync_master' => true,
                'type' => 'switcher',
                'title' => 'Enable trust pilot signature',
                'line_before' => true,
                'full_row' => true,
            ],
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/product/trust_pilot_rating_value',
                'sync_master' => true,
                'type' => 'trust_pilot_rating_value',
                'title' => 'Trust pilot rating value',
            ],
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/product/trust_pilot_domain',
                'sync_master' => true,
                'type' => 'text',
                'title' => 'Trust pilot domain'
            ],
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/product/trust_pilot_review_url',
                'sync_master' => true,
                'type' => 'text',
                'title' => 'Trust pilot review url',
                'full_row' => true,
            ],
            [
                'section' => 'catalog',
                'group' => 'product_setup',
                'key' => 'catalog/product/live_preview_members_vendor',
                'type' => 'select_members',
                'full_row' => true,
                'title' => 'Select members vendor can display live preview',
                'line_before' => true
            ],
            //End Trust Pilot
            [
                'section' => 'catalog',
                'group' => 'place_of_manufacture',
                'key' => 'place_of_manufacture',
                'type' => 'group',
                'title' => 'Place of manufacture',
            ],
            [
                'section' => 'catalog',
                'group' => 'place_of_manufacture',
                'key' => 'place_of_manufacture/enable',
                'type' => 'switcher',
                'title' => 'Show Place of Manufacture'
            ],
            [
                'section' => 'catalog',
                'group' => 'place_of_manufacture',
                'key' => 'place_of_manufacture/default',
                'type' => 'place_of_manufacture_default',
                'full_row' => true,
                'title' => 'Default Place of Manufacture'
            ],
            [
                'section' => 'catalog',
                'group' => 'place_of_manufacture',
                'key' => 'place_of_manufacture',
                'type' => 'place_of_manufacture',
                'full_row' => true,
                'title' => 'Place of Manufacture'
            ],
            [
                'section' => 'catalog',
                'group' => 'tip',
                'key' => 'tip',
                'type' => 'group',
                'title' => 'Tip',
            ],
            [
                'section' => 'catalog',
                'group' => 'tip',
                'key' => 'tip/enable',
                'type' => 'switcher',
                'full_row' => true,
                'title' => 'Show tipping at check out'
            ],
            [
                'section' => 'catalog',
                'group' => 'tip',
                'key' => 'tip/title',
                'type' => 'text',
                'full_row' => true,
                'required' => true,
                'title' => 'Tip title'
            ],
            [
                'section' => 'catalog',
                'group' => 'tip',
                'key' => 'tip/description',
                'type' => 'text',
                'full_row' => true,
                'required' => true,
                'title' => 'Tip description'
            ],
            [
                'section' => 'catalog',
                'group' => 'tip',
                'key' => 'tip/table',
                'type' => 'tip',
                'full_row' => true,
                'title' => 'Tip Percent'
            ],
            [
                'section' => 'catalog',
                'group' => 'tip',
                'key' => 'tip/country',
                'type' => 'tip_country',
                'full_row' => true,
                'title' => 'Active Country'
            ],
            [
                'section' => 'catalog',
                'key' => 'store_info',
                'type' => 'group',
                'title' => 'Store address',
                'description' => 'This address will appear on your invoices. You can edit the address used to calculate shipping rates in your shipping settings'
            ],
            [
                'section' => 'catalog',
                'group' => 'store_info',
                'key' => 'catalog/store/legal_name',
                'type' => 'text',
                'title' => 'Legal name of business',
                'full_row' => true
            ],
            [
                'section' => 'catalog',
                'group' => 'store_info',
                'key' => 'catalog/store/address',
                'type' => 'address',
                'title' => 'Address',
                'line_before' => true,
                'full_row' => true
            ],
            [
                'section' => 'catalog',
                'key' => 'discount_email',
                'type' => 'group',
                'title' => 'Discount email',
                'description' => 'Percentage discount for each type of email. Your percentage discount will appear as 1, 10, 20 ...',
            ],
            [
                'section' => 'catalog',
                'key' => 'catalog_abtest',
                'type' => 'group',
                'title' => 'Catalog Ab testing'
            ],
            [
                'section' => 'catalog',
                'key' => 'synchronize_service',
                'type' => 'group',
                'title' => 'Synchronize Service',
                'description' => 'Config Synchronize service using kafka',
            ],
            [
                'section' => 'catalog',
                'group' => 'catalog_abtest',
                'key' => 'catalog/abtest/enable_tab',
                'type' => 'switcher',
                'title' => 'Enable abtest tab',
                'line_before' => true,
                'sync_master' => false
            ],
            [
                'section' => 'catalog',
                'group' => 'catalog_abtest',
                'key' => 'catalog/abtest/list_product_tab',
                'type' => 'text',
                'title' => 'List products abtest tab ',
                'full_row' => true,
                'desc' => 'Format: [product id 1],[product id 2],[product id 3]. Example: 2110,3000,4516'
            ],
            [
                'section' => 'catalog',
                'key' => 'catalog_video',
                'type' => 'group',
                'title' => 'Video Config',
                'description' => 'Config product video',
            ],
            [
                'section' => 'catalog',
                'group' => 'catalog_video',
                'key' => 'catalog/video_config/max_file_size',
                'type' => 'number',
                'title' => 'Set video max file size',
                'full_row' => true,
                'min' => 0,
                'validator' => ['Observer_Catalog_Backend', 'validateInteger'],
                'desc' => 'Set max file size for product video in MB (Megabyte)'
            ],            
            [
                'section' => 'catalog',
                'key' => 'collection_banner',
                'type' => 'group',
                'title' => 'Collection Banner',
            ],
            [
                'section' => 'catalog',
                'group' => 'collection_banner',
                'key' => 'catalog/collection_banner/enable',
                'type' => 'switcher',
                'title' => 'Enable Banner',
                'full_row' => true
            ],
            [
                'section' => 'catalog',
                'group' => 'collection_banner',
                'key' => 'catalog/collection_banner/title',
                'type' => 'text',
                'title' => 'Title',
                'line_before' => true,
                'full_row' => true
            ],
            [
                'section' => 'catalog',
                'group' => 'collection_banner',
                'key' => 'catalog/collection_banner/url',
                'type' => 'text',
                'title' => 'Url',
                'full_row' => true
            ],
            [
                'section' => 'catalog',
                'group' => 'collection_banner',
                'key' => 'catalog/collection_banner/pc_image',
                'type' => 'image',
                'max_file_size' => '10', // Mb
                'title' => 'PC default collection banner',
                'extension' => 'png,jpg,gif',
                'trim' => true,
            ],
            [
                'section' => 'catalog',
                'group' => 'collection_banner',
                'key' => 'catalog/collection_banner/mobile_image',
                'type' => 'image',
                'max_file_size' => '10', // Mb
                'title' => 'Mobile default collection banner',
                'extension' => 'png,jpg,gif',
                'trim' => true,
            ],
            [
                'section' => 'catalog',
                'group' => 'discount_email',
                'key' => 'catalog/setting_type/subscriber_discount',
                'type' => 'number',
                'title' => 'Email subscriber',
                'validator' => ['Observer_Catalog_Backend', 'validateInteger'],
                'full_row' => false
            ],
            [
                'section' => 'catalog',
                'group' => 'discount_email',
                'key' => 'catalog/discount_email/thank_you',
                'type' => 'number',
                'title' => 'Email thank you',
                'validator' => ['Observer_Catalog_Backend', 'validateInteger'],
                'sync_master' => true,
                'full_row' => false
            ],
            [
                'section' => 'location_block',
                'key' => 'blocks_countries',
                'type' => 'group',
                'title' => 'Shipping Exclusions',
                'description' => 'Select locations you don’t want to ship to'
            ],
            [
                'section' => 'location_block',
                'group' => 'blocks_countries',
                'key' => 'shipping/block_countries',
                'type' => 'shipping_block_countries',
                'title' => 'Blocks Countries',
                'show_change' => true,
                'data_type' => 'json',
                'full_row' => true,
            ],
            [
                'section' => 'shipping',
                'key' => 'table_rate',
                'type' => 'group',
                'title' => 'Free Shipping Rate'
            ],
            [
                'section' => 'shipping',
                'key' => 'quantity_table_rate',
                'type' => 'group',
                'title' => 'Shipping Quantity Rate'
            ],
            [
                'section' => 'shipping',
                'key' => 'delivery_time',
                'type' => 'group',
                'title' => 'Delivery Time'
            ],
            [
                'section' => 'shipping',
                'group' => 'table_rate',
                'key' => 'shipping/table_rate/free_shipping',
                'type' => 'text',
                'title' => 'Free shipping for order with subtotal over',
                'full_row' => true,
            ],
            [
                'section' => 'shipping',
                'group' => 'table_rate',
                'key' => 'shipping/table_rate/free_shipping/enable',
                'type' => 'switcher',
                'title' => 'Enable Free Shipping by Table Rate',
                'full_row' => true,
            ],
            [
                'section' => 'shipping',
                'group' => 'quantity_table_rate',
                'key' => 'shipping/shipping_by_quantity/table',
                'type' => 'catalog_shipping_quantity_rate',
                'title' => 'Shipping by Quantity Rate',
                'full_row' => true,
                'line_after' => true,
            ],
            [
                'section' => 'shipping',
                'group' => 'delivery_time',
                'key' => 'shipping/delivery_time',
                'type' => 'catalog_shipping_delivery_time',
                'title' => 'Shipping Delivery Time',
                'full_row' => true,
                'line_after' => true,
            ],

            [
                'section' => 'shipping',
                'key' => 'cut_off_time',
                'type' => 'group',
                'title' => 'Cut Off Time',
                'description' => '<b>Code:</b><br />{{tdate}}: Nov 20, 2019<br />{{date}}: 20/11/2019<br />{{tsdate}}: Nov 20<br />{{sdate}}: 20/11<br />{{tdate_time}}:23:00 Nov 20, 2019<br />{{date_time}}: 23:00 20/11/2019<br />{{tsdate_time}}:23:00 Nov 20<br />{{sdate_time}}: 23:00 20/11'
            ],
            [
                'section' => 'shipping',
                'group' => 'cut_off_time',
                'key' => 'shipping/cut_off_time/enable',
                'type' => 'switcher',
                'title' => 'Enable Cut Off time message',
                'line_after' => true
            ],
            [
                'section' => 'shipping',
                'group' => 'cut_off_time',
                'key' => 'shipping/cut_off_time/title',
                'type' => 'text',
                'title' => 'Title',
                'full_row' => true
            ],
            [
                'section' => 'shipping',
                'group' => 'cut_off_time',
                'key' => 'shipping/cut_off_time/message_before_time',
                'type' => 'text',
                'title' => 'Message before cut off time',
                'full_row' => true
            ],
            [
                'section' => 'shipping',
                'group' => 'cut_off_time',
                'key' => 'shipping/cut_off_time/message_after_time',
                'type' => 'text',
                'title' => 'Message after cut off time',
                'full_row' => true
            ],
            [
                'section' => 'shipping',
                'group' => 'cut_off_time',
                'key' => 'shipping/cut_off_time/table',
                'type' => 'catalog_cut_off_time_table',
                'title' => 'Cut off time table',
                'full_row' => true,
                'line_after' => true
            ],
            [
                'section' => 'catalog',
                'group' => 'synchronize_service',
                'key' => 'catalog/synchronize_service/maximum_request',
                'type' => 'number',
                'title' => 'Maximum times call synchronize service',
                'full_row' => true,
                'default' => 0
            ],
        ];
    }

    public static function validateProductCollection($value, $setting_item) {
        if (!is_array($value)) {
            $value = [$value];
        }

        $value = array_map(function ($value) {
            return trim($value);
        }, $value);

        $value = array_filter($value, function ($value) {
            return $value !== '';
        });

        $value = array_unique($value);
        $value = array_values($value);

        if (!isset($setting_item['multiple']) || !$setting_item['multiple']) {
            $value = $value[0];
        }

        return $value;
    }

    public static function validateSubscriberDiscount($value, $setting_item) {
        return intval($value) > 0 ? intval($value) : '';
    }

    public static function validateFloatPrice($value, $setting_item) {
        return round(abs(doubleval($value)), 2);
    }

    public static function validatePlusMinusPriceTable($value, $setting_item) {
        if (!is_array($value)) {
            return [];
        }

        $rows = [];

        foreach ($value as $row) {
            if (!isset($row['country'])) {
                continue;
            }
            $hash_id = $row['hash_id'];
            $blocked_product_type = $row['blocked_product_type'];
            unset($row['hash_id']);
            unset($row['blocked_product_type']);

            $countries = implode('_', $row['country']);

            unset($row['country']);
            $d = [];
            foreach ($row as $item) {
                $d[$item['product_type']] = ['price' => $item['price'], 'price_type' => $item['price_type']];
            }

            $rows[$countries]['product_type'] = $d;
            $rows[$countries]['hash_id'] = $hash_id;
            $rows[$countries]['blocked_product_type'] = $blocked_product_type;
        }

        return $rows;
    }

    public static function validateGoogleFeedTable($value, $setting_item) {

        if (!is_array($value)) {
            return [];
        }

        $rows = [];

        foreach ($value as $row) {
            if (!isset($row['product_type'])) {
                continue;
            }

            $row['product_type'] = trim($row['product_type']);
            $row['google_product_cat_id'] = trim($row['google_product_cat_id']);
            $row['commerce_tax_category'] = trim($row['commerce_tax_category']);

            if (!$row['product_type']) {
                continue;
            }

            if (!isset($rows[$row['product_type']])) {
                $rows[$row['product_type']] = [];
            }

            $rows[$row['product_type']] = [
                'google_product_cat_id' => $row['google_product_cat_id'],
                'shipping_label' => OSC::helper('catalog/common')->escapeString($row['shipping_label'], 50),
                'commerce_tax_category' => $row['commerce_tax_category']
            ];
        }

        return $rows;
    }

    public static function validateCollectionFeedTable($value, $setting_item) {
        if (!is_array($value)) {
            return [];
        }

        $rows = [];
        $collection_ids_selected = [];
        foreach ($value as $key => $row) {
            $row['title'] = OSC::helper('catalog/common')->escapeString($row['title'], 255);
            if (!$row['collection_id'] || !$row['title'] || in_array($row['collection_id'], $collection_ids_selected)) {
                continue;
            }
            if (isset($row['prefix']) && $row['prefix'] == 1) {
                $row['prefix'] = true;
            } else {
                $row['prefix'] = false;
            }
            $rows[$key] = $row;
            array_push($collection_ids_selected, $row['collection_id']);
        }

        return $rows;
    }

    public static function validateCollectionBetaFeed($value, $setting_item) {
        if (!is_array($value)) {
            return [];
        }

        $rows = [];
        $collection_ids_selected = [];
        foreach ($value as $key => $row) {
            $row['google_cat_id'] = trim($row['google_cat_id']);
            $row['shipping_label'] = trim($row['shipping_label']);
            $row['collection_id'] = trim($row['collection_id']);
            if (!$row['collection_id'] || in_array($row['collection_id'], $collection_ids_selected)) {
                continue;
            }

            $rows[$key] = $row;
            array_push($collection_ids_selected, $row['collection_id']);
        }

        return $rows;
    }

    public static function validateFeedBlockProductKeywords($value, $setting_item) {
        if (!is_array($value)) {
            return [];
        }

        $value = array_map(function ($value) {
            return trim($value);
        }, $value);

        return $value;
    }

    public static function validateProductType($value, $setting_item) {
        if (!is_array($value)) {
            $value = [$value];
        }

        $value = array_map(function ($value) {
            return trim($value);
        }, $value);
        $value = array_filter($value, function ($value) {
            return $value !== '';
        });
        $value = array_unique($value);
        $value = array_values($value);

        if (!isset($setting_item['multiple']) || !$setting_item['multiple']) {
            $value = $value[0];
        }

        return $value;
    }

    public static function validatePlaceOfManufactureDefault($value, $setting_item) {
        $countries = OSC::helper('core/country')->getCountries();

        if (!is_array($countries) || count($countries) == 0){
            return '';
        }

        $value = trim($value);

        if (!in_array($value, array_keys($countries))){
            throw new Exception('Country not found');
        }

        return $value;
    }

    public static function validatePlaceOfManufacture($value, $setting_item)
    {
        $countries = OSC::helper('core/country')->getCountries();
        if (!is_array($value) || count($countries) == 0) {
            return [];
        }

        foreach ($value as $key => $row) {
            if (!isset($row['country_customer']) || !is_array($row['country_customer']) || !isset($row['country_place_of_manufacture']) || !is_array($row['country_place_of_manufacture'])) {
                unset($value[$key]);
            }

            foreach ($row['country_customer'] as $key1 => $country_code_customer) {
                $country = trim($country_code_customer);
                if (empty($country) || !in_array($country_code_customer, array_keys($countries))) {
                    unset($value[$key]['country_customer'][$key1]);
                }
            }

            foreach ($row['country_place_of_manufacture'] as $key2 => $country_place_of_manufacture) {
                $country = trim($country_place_of_manufacture);
                if ($country == '') {
                    throw new Exception('Please choose place of manufacture');
                }
                if (empty($country) || !in_array($country_place_of_manufacture, array_keys($countries))) {
                    unset($value[$key]['country_place_of_manufacture'][$key2]);
                }
            }
        }

        foreach ($value as $key => $row) {
            if (count($row['country_customer']) < 1 || count($row['country_place_of_manufacture']) < 1) {
                unset($value[$key]);
            }
        }

        return OSC::encode($value);
    }

    public static function validateTipPercent($value, $setting_item)
    {
        foreach ($value as $key => $percent) {
            if (is_string($percent)) {
                $value[$key] = preg_replace('/[^0-9]/', '', $percent);
            }
        }
        return OSC::encode($value);
    }

    public static function validateTipMaximum($value, $setting_item)
    {
        if (is_string($value)) {
            $value = preg_replace('/[^0-9\.]/', '', $value);
        }
        return $value;
    }

    public static function validateCountries($value, $setting_item)
    {
        $countries = OSC::helper('core/country')->getCountries();
        if (!is_array($value) || count($countries) == 0) {
            return [];
        }

        $value = array_filter($value, function ($value) {
            return $value != '';
        });

        $value = array_map(function ($value) use ($countries) {
            if (!in_array(trim($value), array_keys($countries))) {
                throw new Exception('Country invalid');
            }
            return trim($value);
        }, $value);

        return $value;
    }

    public static function validateReasonCancelOrder($value, $setting_item) {
        if (!is_array($value)) {
            return [];
        }

        $rows = [];

        foreach ($value as $row) {
            if (!isset($row['reason']) || $row['reason'] == '' ||  !isset($row['solution']) || $row['solution'] == '') {
                continue;
            }

            if ($row['check_flag_box'] == 'on'){
                $row['check_flag_box'] = 1;
            }

            $row['reason'] = trim($row['reason']);
            $row['solution'] = trim($row['solution']);

            $rows[] = [
                'reason' => $row['reason'],
                'solution' => $row['solution'],
                'check_flag_box' => $row['check_flag_box']
            ];
        }

        $rows = OSC::helper('catalog/order')->uniqueMultidimArray($rows, 'reason');

        return $rows;
    }

    /**
     * @throws Exception
     */
    public static function validateCustomLabel($value, $setting_item)
    {
        $value_array = explode(',', trim($value));
        foreach ($value_array as $key => $_value) {
            $_value = intval($_value);
            if ($_value < 1) {
                $value_array[$key] = $_value;
                unset($value_array[$key]);
            }
        }
        if (count($value_array) > self::MAX_COLLECTION_ID_IN_CUSTOM_LABEL) {
            throw new Exception('Total Collection ID need less than or equal '. self::MAX_COLLECTION_ID_IN_CUSTOM_LABEL);
        }
        asort($value_array);
        return implode(',', $value_array);
    }

    public static function validateInteger($value, $setting_item)
    {
        $value = intval(trim($value));
        if ($value < 0 || !is_int($value)) {
            $value = 0;
        }

        return intval(trim($value));
    }

    public static function validateBypassMinSold($value, $setting_item): string
    {
        $value = trim($value);
        preg_match_all("/([0-9]+)-?/", $value, $matches);
        if (!empty($matches[1])) {
            $values = array_filter($matches[1], function ($val) {
                return trim($val);
            });
            return implode('-', array_unique($values));
        }
        return '';
    }

    public static function validateCountry($value) {
        $countries = OSC::helper('core/country')->getCountries();
        if (!is_array($countries) || count($countries) == 0){
            return [];
        }

        $value = array_map(function($value) {
            return trim($value);
        }, $value);

        $value = array_filter($value, function($value) {
            return $value !== '';
        });

        $value = array_unique($value);
        $value = array_values($value);

        $value = array_intersect($countries, $value);

        return $value;
    }

    public static function validateQuantityRateTable($value, $setting_item) {
        if (!is_array($value)) {
            return [];
        }

        $rows = [];
        $count = 0;

        foreach ($value as $row) {
            $data = [];

            if (!OSC::helper('core/country')->isLocationExists($row['location_data']) && $row['location_data'] !== '*') {
                continue;
            }

            $data['location_data'] = trim($row['location_data']);

            foreach ($row['fee_configs'] as $fee_config) {
                $shipping_configs = [];
                if(!$fee_config['type_fee_ship']){
                    $fee_config['type_fee_ship'] = 0;
                }
                if (!isset($fee_config['product_types']) || !isset($fee_config['type_fee_ship']) || !isset($fee_config['base'])|| !isset($fee_config['plus']) || $fee_config['type_fee_ship'] < 0 || ( $fee_config['type_fee_ship'] == 1 && ($fee_config['base'] <= 0 || $fee_config['plus'] <= 0)) || ( $fee_config['type_fee_ship'] == 0 && ($fee_config['base'] < 0 || $fee_config['plus'] < 0))) {
                    continue;
                }

                $data['product_types'] = $fee_config['product_types'];
                $data['shipping_configs_type'] = intval($fee_config['type_fee_ship']);
                $data['shipping_configs_dynamic']['base'] =  floatval($fee_config['base']);
                $data['shipping_configs_dynamic']['plus'] =  floatval($fee_config['plus']);
                if ($data['location_data'] === '*' && in_array('*', $fee_config['product_types'])) {
                    $count++;
                }

                if ($count > 1) {
                    throw new Exception('A group containing "All Locations" and "All Product Types" already exists. You cannot create another group with these exact configurations.');
                }

                $count_shipping_configs = count($fee_config['quantity']);
                if (count($fee_config['quantity']) !== $count_shipping_configs ||
                    count($fee_config['price']) !== $count_shipping_configs
                ) {
                    continue;
                }

                foreach ($fee_config['quantity'] as $key => $quantity) {
                    if (intval($quantity) < 1) {
                        throw new Exception('Quantity value must be greater than 0');
                    }

                    $shipping_configs[$quantity]['price'] = $fee_config['price'][$key];
                }
                foreach ($fee_config['price'] as $key => $price) {
                    if (intval($price) < 1) {
                        throw new Exception('Product price must be greater than 0');
                    }
                }

                $data['shipping_configs'] = $shipping_configs;

                $rows[] = $data;
            }
        }

        if (OSC::helper('catalog/productType')->isProductTypeInTwoLocation($rows)) {
            throw new Exception('There has been an attempt to add an existing Product Type to another group. This action cannot be completed because a Product Type can only exist in a singular group.');
        }

        foreach ($rows as $key =>  $row){
            if (count($row) < 1) {
                unset($rows[$key]);
            }
        }

        return $rows;
    }

    public static function validateDeliveryTime($value, $setting_item) {
        if (!is_array($value)) {
            return [];
        }

        $rows = [];
        $count = 0;

        foreach ($value as $row) {
            $data = [];

            if (!OSC::helper('core/country')->isLocationExists($row['location_data']) && $row['location_data'] !== '*') {
                continue;
            }

            $data['location_data'] = trim($row['location_data']);

            foreach ($row['delivery_configs'] as $fee_config) {
                if (!isset($fee_config['product_types']) || count($fee_config['product_types']) === 0) {
                    continue;
                }

                $data['product_types'] = $fee_config['product_types'];

                if (intval($fee_config['processing']) < 1) {
                    throw new Exception('Processing time value must be greater than 0 (days)');
                }

                $data['processing'] = intval($fee_config['processing']);

                if (intval($fee_config['estimate']) < 1) {
                    throw new Exception('Estimated time must be greater than 0 (days)');
                }

                $data['estimate'] = intval($fee_config['estimate']);

                if ($data['location_data'] === '*' && in_array('*', $data['product_types'])) {
                    $count++;
                }

                if ($count > 1) {
                    throw new Exception('A group containing "All Locations" and "All Product Types" already exists. You cannot create another group with these exact configurations.');
                }

                $rows[] = $data;
            }
        }

        if (OSC::helper('catalog/productType')->isProductTypeInTwoLocation($rows)) {
            throw new Exception('There has been an attempt to add an existing Product Type to another group. This action cannot be completed because a Product Type can only exist in a singular group.');
        }

        foreach ($rows as $key =>  $row){
            if (count($row) < 1) {
                unset($rows[$key]);
            }
        }

        return $rows;
    }

    public static function validateCutOffTimeTable($value, $setting_item) {
        if (!is_array($value)) {
            return [];
        }

        $rows = [];

        foreach ($value as $row) {
            if (!isset($row['location']) || !isset($row['product_types']) || !isset($row['date'])) {
                continue;
            }

            if (!$row['location'] || !$row['product_types'] || !$row['date']) {
                continue;
            }

            $cot = [
                'product_types' => $row['product_types'],
            ];
            foreach ($row['location'] as $key => $location) {
                $location = trim($location);
                $date = trim($row['date'][$key]);

                $date = explode(' ', $date);

                if (count($date) != 2) {
                    continue;
                }

                $td = new DateTime('now', new DateTimeZone(OSC::helper('core/setting')->get('core/timezone')));
                $date[0] = explode('/', $date[0]);
                if (isset($date[1])) {
                    $date[1] = explode(':', $date[1]);
                } else {
                    $date[1] = explode(':', $td->format('H:i'));
                }

                if (count($date[0]) != 3 || !checkdate($date[0][1], $date[0][0], $date[0][2]) || count($date[1]) != 2 || $date[1][0] > 23 || $date[1][0] < 0 || $date[1][1] > 59 || $date[1][1] < 0) {
                    continue;
                }

                $date[0] = implode('/', $date[0]);
                if (isset($date[1])) {
                    $date[1] = implode(':', $date[1]);
                } else {
                    $date[1] = explode(':', $td->format('H:i'));
                }

                $date = implode(' ', $date);

                if (in_array('manual', $row['product_types'])) {
                    if (empty(trim($row['product_ids'][$key]))) {
                        throw new Exception('Product IDs is empty!');
                    }

                    $product_ids = array_unique(preg_split('/[\D]+/', trim($row['product_ids'][$key])));

                    $products = OSC::model('catalog/product')->getCollection()->addField('product_id')->load($product_ids);

                    if (count($product_ids) !== $products->length()) {
                        throw new Exception('Some product ID in [' . implode(', ', $product_ids) . '] not exist');
                    }
                    $cot['product_types'] = ['manual'];
                    $cot['meta_data'][] = [
                        'product_ids' => $product_ids,
                        'location' => $location,
                        'time' => $date
                    ];
                } else {
                    $cot[$location] = $date;
                }
            }
            $rows[] = $cot;
        }
        return $rows;
    }

    public static function collectMenu() {
        $menus = [];

        if (OSC::controller()->checkPermission('catalog/super|catalog/order', false)) {
            $menus[] = array(
                'key' => 'catalog_order',
                'position' => 998,
                'icon' => 'box-regular',
                'title' => 'Orders',
                'url' => OSC::getUrl('catalog/backend_order/index'),
            );
        }

        if (OSC::controller()->checkPermission('catalog', false)) {
            $menus[] = array(
                'key' => 'catalog',
                'position' => 997,
                'icon' => 'tag',
                'title' => 'Catalog',
                'url' => OSC::getUrl('catalog/backend/index'),
            );
        }

        if (OSC::controller()->checkPermission('catalog/super|catalog/product', false)) {
            $menus[] = array(
                'key' => 'catalog/product',
                'parent_key' => 'catalog',
                'title' => 'Products',
                'url' => OSC::getUrl('catalog/backend_product/list'),
            );
        }

        if (OSC::controller()->checkPermission('catalog/super|catalog/product/full|catalog/review', false)) {
            $menus[] = array(
                'key' => 'catalog/review',
                'parent_key' => 'catalog',
                'title' => 'Reviews',
                'url' => OSC::getUrl('catalog/backend_review/list'),
            );
        }

        if (OSC::controller()->checkPermission('catalog/super|catalog/collection', false)) {
            $menus[] = array(
                'key' => 'catalog/collection',
                'parent_key' => 'catalog',
                'title' => 'Collections',
                'url' => OSC::getUrl('catalog/backend_collection/list'),
            );
        }

        if (OSC::controller()->checkPermission('catalog/super|catalog/product/full|catalog/product/pack', false)) {
            $menus[] = [
                'key' => 'catalog/product/pack',
                'parent_key' => 'catalog',
                'title' => 'Pack Management',
                'url' => OSC::getUrl('catalog/backend_pack/index'),
            ];
        }

        if (OSC::controller()->checkPermission('catalog/super|catalog/discount', false)) {
            $menus[] = array(
                'key' => 'catalog_discount',
                'position' => 996,
                'icon' => 'badge-percent-solid',
                'title' => 'Discounts',
                'url' => OSC::getUrl('catalog/backend_discount_code/index'),
            );
        }

        $product_config_url = '';
        if (OSC::helper('user/authentication')->getMember()->isAdmin()) {
            if ($product_config_url === '') {
                $product_config_url = OSC::getUrl('catalog/backend_productTypeVariantPrice/index');
            }
            $menus[] = array(
                'key' => 'product_config/product_type_variant_price',
                'parent_key' => 'product_config',
                'position' => 900,
                'title' => 'Product Type Variant Price',
                'url' => OSC::getUrl('catalog/backend_productTypeVariantPrice/index'),
            );
        }

        if (OSC::controller()->checkPermission('catalog/product_config/product_type_description', false)) {
            if ($product_config_url === '') {
                $product_config_url = OSC::getUrl('catalog/backend_productTypeDescription/index');
            }
            $menus[] = array(
                'key' => 'product_config/product_type_description',
                'parent_key' => 'product_config',
                'position' => 900,
                'title' => 'Product Type Description',
                'url' => OSC::getUrl('catalog/backend_productTypeDescription/index'),
            );
        }

        if (OSC::controller()->checkPermission('catalog/product_config/product_type_description/map', false)) {
            if ($product_config_url === '') {
                $product_config_url = OSC::getUrl('catalog/backend_productTypeDescriptionMap/index');
            }
            $menus[] = array(
                'key' => 'product_config/product_type_description_map',
                'parent_key' => 'product_config',
                'position' => 900,
                'title' => 'Product Type Description Map',
                'url' => OSC::getUrl('catalog/backend_productTypeDescriptionMap/index'),
            );
        }

        if (OSC::controller()->checkPermission('catalog/product_config', false)) {
            $menus[] = array(
                'key' => 'product_config',
                'position' => 995,
                'icon' => 'tweaking',
                'title' => 'Product Setup',
                'url' => $product_config_url ? $product_config_url : '#',
            );
        }

        if (OSC::controller()->checkPermission('catalog/super|catalog/product', false)) {
            $menus[] = array(
                'key' => 'catalog/uploadImage',
                'parent_key' => 'catalog',
                'title' => 'Upload Images',
                'url' => OSC::getUrl('catalog/backend_image/post'),
            );
        }

        if (OSC::controller()->checkPermission('catalog/super|catalog/print_template_beta/list', false)) {
            $menus[] = array(
                'key' => 'catalog/printTemplateIBeta',
                'parent_key' => 'catalog',
                'title' => 'Print Template Beta',
                'url' => OSC::getUrl('catalog/backend_printTemplateBeta/list'),
            );
        }

        if (OSC::controller()->checkPermission('catalog/super|catalog/product', false)) {
            $menus[] = array(
                'key' => 'catalog/analyticOrder',
                'parent_key' => 'catalog',
                'title' => 'Analytic Order',
                'url' => OSC::getUrl('catalog/backend_analytic/post'),
            );
        }

        return $menus;
    }

    public static function navCollectItemType($params) {
        $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
        $params['items'][] = array(
            'icon' => 'tag',
            'title' => 'Products',
            'browse_url' => OSC::getUrl('catalog/backend_product/browse'),
            'root_item' => array(
                'icon' => 'tag',
                'title' => 'All Products',
                'url' => OSC_FRONTEND_BASE_URL . '/' . $current_lang_key . '/collection/all'
            )
        );

        $params['items'][] = array(
            'icon' => 'tags',
            'title' => 'Collections',
            'browse_url' => OSC::getUrl('catalog/backend_collection/browse'),
            'root_item' => array(
                'icon' => 'tags',
                'title' => 'All Collections',
                'url' => OSC_FRONTEND_BASE_URL . '/catalog/collections'
            )
        );

        $params['items'][] = array(
            'icon' => 'tags',
            'title' => 'Track order',
            'url' => OSC_FRONTEND_BASE_URL . '/' . $current_lang_key . '/tracking-order'
        );
    }

    public static function collectCustomerGroup($params) {
        $params['groups']['new'] = [
            'title' => 'New',
            'verifier' => ['Helper_Catalog_Customer', 'isInGroup__new']
        ];

        $params['groups']['returning'] = [
            'title' => 'Returning',
            'verifier' => ['Helper_Catalog_Customer', 'isInGroup__returning']
        ];

        $params['groups']['local'] = [
            'title' => 'Local customer (in shop country)',
            'verifier' => ['Helper_Catalog_Customer', 'isInGroup__local']
        ];

        $params['groups']['abandoned_checkouts'] = [
            'title' => 'Abandoned checkouts',
            'verifier' => ['Helper_Catalog_Customer', 'isInGroup__abandonedCheckouts']
        ];

        $params['groups']['email_subscribers'] = [
            'title' => 'Email subscribers',
            'verifier' => ['Helper_Catalog_Customer', 'isInGroup__emailSubscribers']
        ];
    }

    public static function collectPermKey($params) {
        $params['permission_map']['catalog'] = [
            'label' => 'Catalog',
            'items' => [
                'product' => [
                    'label' => 'Product',
                    'items' => [
                        'view_all' => 'View All',
                        'view_group' => 'View Group',
                        'add' => 'Add',
                        //'apply_reorder' => 'Apply reorder for all campaign', // Disabled because this permission only assign to admin
                        'edit' => [
                            'label' => 'Edit',
                            'items' => ['bulk' => 'Bulk edit']
                        ],
                        'quick_edit' => 'Quick Edit',
                        'delete' => [
                            'label' => 'Delete',
                            'items' => ['bulk' => 'Bulk delete']
                        ],
                        'listing' => [
                            'label' => 'Listing',
                            'items' => ['bulk' => 'Bulk Listing']
                        ],
                        'set_sref_dest' => 'Set Sref Source Dest'
                    ]
                ],
                'collection' => [
                    'label' => 'Collection',
                    'items' => [
                        'add' => 'Add',
                        'edit' => 'Edit',
                        'quick_edit' => 'Quick Edit',
                        'delete' => 'Delete'
                    ]
                ],
                'order' => 'Order',
                'review' => [
                    'label' => 'Review',
                    'items' => [
                        'add' => 'Add',
                        'edit' => 'Edit',
                        'delete' => 'Delete',
                        'approve' => 'Approve'
                    ]
                ],
                'discount' => [
                    'label' => 'Discount code',
                    'items' => [
                        'add' => 'Add',
                        'edit' => 'Edit',
                        'delete' => 'Delete'
                    ]
                ],
                'product_config' => [
                    'label' => 'Product Config',
                    'items' => [
                        'product_type_description' => [
                            'label' => 'Product Type Description',
                            'items' => [
                                'add' => 'Add',
                                'edit' => 'Edit',
                                'delete' => 'Delete',
                                'map' => [
                                    'label' => 'Map',
                                    'items' => [
                                        'edit' => 'Edit'
                                    ]
                                ]
                            ]
                        ],
                    ]
                ],
                'facebook_pixel' => [
                    'label' => 'Facebook Pixel',
                    'items' => [
                        'add' => 'Add',
                        'edit' => 'Edit',
                        'delete' => 'Delete',
                        'map' => 'Map'
                    ]
                ],
                'print_template_beta' => [
                    'label' => 'Print Template Beta',
                    'items' => [
                        'list' => [
                            'label' => 'List',
                            'items' => [
                                'edit' => 'Edit',
                                'add' => 'Add'
                            ]
                        ]
                    ]
                ],
            ]
        ];

        if (OSC::isPrimaryStore()) {
            $params['permission_map']['catalog']['items']['product']['items'] = array_merge($params['permission_map']['catalog']['items']['product']['items'], [
                'rerender' => 'Rerender Mockup',
                'importDataSEO' => 'Import SEO Data',
                'exportDataSEO' => 'Export SEO Data',
                'optimize_SEO_product' => 'Optimize SEO product',
                'semitest' => [
                    'label' => 'Product Semitest',
                    'items' => [
                        'print_template_config' => [
                            'label' => 'Print Template Config',
                            'items' => ['edit_supplier' => 'Edit Supplier']
                        ]
                    ]
                ],
                'full' => 'Full permission',
                'renderMockupByListProduct' => 'Render Mockup By List Product',
                'export_layer' => [
                    'label' => 'Amazon',
                    'items' => [
                        'list' => 'Amazon Progress',
                        'recon' => 'Recon queue',
                        'delete' => 'Delete queue'
                    ]
                ],

            ]);
            $params['permission_map']['catalog']['items']['collection']['items'] = array_merge($params['permission_map']['catalog']['items']['collection']['items'], [
                'importDataSEO' => 'Import SEO Data',
                'exportDataSEO' => 'Export SEO Data',
            ]);
            $params['permission_map']['catalog']['items'] = array_merge($params['permission_map']['catalog']['items'], [
                'super' => 'Super permission'
            ]);
        }
    }

    public static function resyncProductFeed() {
        OSC::helper('catalog/product')->resyncFeed();
    }

}
