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
class Observer_Frontend_Backend {

    public static function collectSettingSection() {
        return [
            [
                'key' => 'theme',
                'priority' => 2,
                'icon' => 'magic-solid',
                'title' => 'Theme',
                'description' => 'Your store theme'
            ]
        ];
    }

    public static function collectSettingItem() {
        $setting_items = [
            [
                'section' => 'theme',
                'key' => 'general',
                'type' => 'group',
                'title' => 'General'
            ],
            [
                'section' => 'theme',
                'group' => 'general',
                'key' => 'theme/site_name',
                'type' => 'text',
                'title' => 'Site name',
                'sync_master' => true,
                'full_row' => true
            ],
            [
                'row_before_desc' => 'All uploaded artworks must have a minimum resolution of 72 dpi. Accepted file types are jpg, png and svg. Your logos and meta image must follow a ratio of 3.5 : 1 (width x height) and your favicon must be a square image.',
                'section' => 'theme',
                'group' => 'general',
                'key' => 'theme/logo',
                'type' => 'image',
                'title' => 'Logo',
                'desc' => '(Width = 30 - 400px, Height = 30 - 400px)',
                'line_before' => true,
                'min_width' => 30,
                'max_width' => 400,
                'min_height' => 30,
                'max_height' => 400,
                'sync_master' => true
            ],
            [
                'section' => 'theme',
                'group' => 'general',
                'key' => 'theme/logo/small',
                'type' => 'image',
                'title' => 'Small logo',
                'desc' => '(Width = 30 - 400px, Height = 30 - 400px)',
                'min_width' => 30,
                'max_width' => 400,
                'min_height' => 30,
                'max_height' => 400,
                'sync_master' => true
            ],
            [
                'section' => 'theme',
                'group' => 'general',
                'key' => 'theme/logo/email',
                'type' => 'image',
                'title' => 'Logo for email',
                'desc' => '(Width = 30 - 400px, Height = 30 - 400px)',
                'extension' => 'png,jpg',
                'min_width' => 30,
                'max_width' => 400,
                'min_height' => 30,
                'max_height' => 400,
                'sync_master' => true
            ],
            [
                'section' => 'theme',
                'group' => 'general',
                'key' => 'theme/favicon',
                'type' => 'image',
                'title' => 'Favicon',
                'desc' => '(Width = 32px, Height = 32px)',
                'extension' => 'png,ico',
                'min_width' => 32,
                'max_width' => 32,
                'min_height' => 32,
                'max_height' => 32,
                'trim' => true
            ],
            [
                'section' => 'theme',
                'group' => 'general',
                'key' => 'theme/metadata/image',
                'type' => 'image',
                'title' => 'Meta image',
                'desc' => '(Width = 630 - 1200px, Height = 630px)',
                'extension' => 'png,jpg',
                'min_width' => 630,
                'max_width' => 1200,
                'min_height' => 630,
                'max_height' => 630,
                'trim' => true
            ],
            [
                'section' => 'theme',
                'key' => 'metadata',
                'type' => 'group',
                'title' => 'Metadata'
            ],
            [
                'section' => 'theme',
                'group' => 'metadata',
                'key' => 'theme/metadata/title',
                'type' => 'text',
                'title' => 'Title',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'group' => 'metadata',
                'key' => 'theme/metadata/keyword',
                'type' => 'textarea',
                'title' => 'Keywords',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'group' => 'metadata',
                'key' => 'theme/metadata/description',
                'type' => 'textarea',
                'title' => 'Description',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'group' => 'metadata',
                'key' => 'theme/metadata/robots',
                'type' => 'textarea',
                'title' => 'Robots.txt',
                'full_row' => true,
                'validator' => [Observer_Frontend_Backend, 'validateRobotTxt'],
                'after_save' => [Observer_Frontend_Backend, 'generateRobotTxt']
            ],
            [
                'section' => 'theme',
                'key' => 'info',
                'type' => 'group',
                'title' => 'Information',
                'description' => 'Information about your company/business'
            ],
            [
                'section' => 'theme',
                'group' => 'info',
                'key' => 'theme/contact/name',
                'type' => 'text',
                'title' => 'Company name',
                'sync_master' => true,
            ],
            [
                'section' => 'theme',
                'group' => 'info',
                'key' => 'theme/contact/email',
                'type' => 'text',
                'sync_master' => true,
                'title' => 'Email'
            ],
            [
                'section' => 'theme',
                'group' => 'info',
                'key' => 'theme/contact/customer_service_email',
                'type' => 'text',
                'sync_master' => true,
                'title' => 'Customer service email'
            ],
            [
                'section' => 'theme',
                'group' => 'info',
                'key' => 'theme/contact/help_email',
                'type' => 'text',
                'sync_master' => true,
                'title' => 'Help mail'
            ],
            [
                'section' => 'theme',
                'group' => 'info',
                'key' => 'theme/contact/noreply_email',
                'type' => 'text',
                'sync_master' => true,
                'title' => 'Noreply email'
            ],
            [
                'section' => 'theme',
                'group' => 'info',
                'key' => 'theme/contact/address',
                'type' => 'text',
                'sync_master' => true,
                'title' => 'Address'
            ],
            [
                'section' => 'theme',
                'group' => 'info',
                'key' => 'theme/contact/fax',
                'type' => 'text',
                'sync_master' => true,
                'title' => 'Fax number'
            ],
            [
                'section' => 'theme',
                'group' => 'info',
                'key' => 'theme/contact/phone_numbers',
                'type' => 'textarea',
                'title' => 'Phone number',
                'desc' => 'Add multiple phone numbers. Each phone number corresponds with one line',
                'full_row' => true,
                'validator' => [Observer_Frontend_Backend, 'validatePhoneNumbers']
            ],
            [
                'section' => 'theme',
                'group' => 'info',
                'key' => 'theme/about',
                'type' => 'editor',
                'title' => 'Short description about company',
                'line_before' => true,
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'key' => 'header',
                'type' => 'group',
                'title' => 'Header'
            ],
            [
                'section' => 'theme',
                'group' => 'header',
                'key' => 'theme/header/top_menu',
                'type' => 'navigation',
                'title' => 'Top menu',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'group' => 'header',
                'key' => 'theme/header/main_menu',
                'type' => 'navigation',
                'title' => 'Main menu',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'group' => 'header',
                'key' => 'theme/header/amp_menu',
                'type' => 'navigation',
                'title' => 'Menu for AMP',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'group' => 'header',
                'key' => 'theme/header/announcement_bar/content',
                'type' => 'text',
                'title' => 'Announcement bar content',
                'full_row' => true,
                'line_before' => true
            ],
            [
                'section' => 'theme',
                'group' => 'header',
                'key' => 'theme/header/announcement_bar/background_color',
                'type' => 'color',
                'title' => 'Background color',
            ],
            [
                'section' => 'theme',
                'group' => 'header',
                'key' => 'theme/header/announcement_bar/text_color',
                'type' => 'color',
                'title' => 'Text color',
            ],
            [
                'section' => 'theme',
                'group' => 'header',
                'key' => 'theme/header/announcement_bar',
                'type' => 'switcher',
                'title' => 'Display announcement bar',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'key' => 'footer',
                'type' => 'group',
                'title' => 'Footer'
            ],
            [
                'section' => 'theme',
                'group' => 'footer',
                'key' => 'theme/footer/column1/title',
                'type' => 'text',
                'title' => 'Title',
                'full_row' => true,
                'row_before_title' => 'Column 01'
            ],
            [
                'section' => 'theme',
                'group' => 'footer',
                'key' => 'theme/footer/column1/content',
                'type' => 'editor',
                'title' => 'Content',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'group' => 'footer',
                'key' => 'theme/footer/column2/title',
                'type' => 'text',
                'title' => 'Title',
                'full_row' => true,
                'row_before_title' => 'Column 02',
                'line_before' => true
            ],
            [
                'section' => 'theme',
                'group' => 'footer',
                'key' => 'theme/footer/column2/content',
                'type' => 'navigation',
                'title' => 'Navigation',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'group' => 'footer',
                'key' => 'theme/footer/column3/title',
                'type' => 'text',
                'title' => 'Title',
                'full_row' => true,
                'row_before_title' => 'Column 03',
                'line_before' => true
            ],
            [
                'section' => 'theme',
                'group' => 'footer',
                'key' => 'theme/footer/column3/content',
                'type' => 'navigation',
                'title' => 'Navigation',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'group' => 'footer',
                'key' => 'theme/footer/copyright',
                'type' => 'text',
                'title' => 'Copyright',
                'full_row' => true,
                'line_before' => true
            ],
            [
                'section' => 'theme',
                'group' => 'footer',
                'key' => 'theme/footer/trustpilot/enable',
                'type' => 'switcher',
                'title' => 'Enable trustpilot review',
                'full_row' => true,
                'line_before' => true
            ],
            [
                'section' => 'theme',
                'group' => 'footer',
                'key' => 'theme/footer/trustpilot/template_id',
                'type' => 'text',
                'title' => 'Trustpilot template id',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'group' => 'footer',
                'key' => 'theme/footer/trustpilot/businessunit_id',
                'type' => 'text',
                'title' => 'Trustpilot business unit id',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'group' => 'footer',
                'key' => 'theme/footer/trustpilot/url_store',
                'type' => 'text',
                'title' => 'Trustpilot URL Store',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'key' => 'homepage_v2',
                'type' => 'group',
                'title' => 'Homepage v2'
            ],
            [
                'section' => 'theme',
                'group' => 'homepage_v2',
                'key' => 'theme/homepage_v2/top_menu',
                'type' => 'navigation',
                'title' => 'Top menu',
            ],
            [
                'section' => 'theme',
                'group' => 'homepage_v2',
                'key' => 'theme/homepage_v2/main_menu',
                'type' => 'navigation',
                'title' => 'Main menu',
            ],
            [
                'section' => 'theme',
                'group' => 'homepage_v2',
                'key' => 'theme/homepage_v2/mobile_menu',
                'type' => 'navigation',
                'title' => 'Mobile menu',
            ],
            [
                'section' => 'theme',
                'group' => 'homepage_v2',
                'key' => 'theme/homepage_v2/footer_menu',
                'type' => 'navigation',
                'title' => 'Footer menu',
            ],
            [
                'section' => 'theme',
                'group' => 'homepage_v2',
                'key' => 'theme/homepage_v2/announcement_bar_content',
                'type' => 'text',
                'title' => 'Announcement bar content',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'group' => 'footer',
                'key' => 'theme/footer/enable_widget_dmca',
                'type' => 'switcher',
                'title' => 'Enable DMCA',
                'full_row' => true,
                'line_before' => true
            ],
            [
                'section' => 'theme',
                'group' => 'footer',
                'key' => 'theme/footer/widget_dmca',
                'type' => 'textarea',
                'title' => 'DMCA embed code',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'key' => 'social',
                'type' => 'group',
                'title' => 'Social Networks'
            ],
            [
                'section' => 'theme',
                'group' => 'social',
                'key' => 'theme/social/facebook',
                'type' => 'text',
                'title' => 'Facebook'
            ],
            [
                'section' => 'theme',
                'group' => 'social',
                'key' => 'theme/social/facebook_group',
                'type' => 'text',
                'title' => 'Facebook Group'
            ],
            [
                'section' => 'theme',
                'group' => 'social',
                'key' => 'theme/social/twitter',
                'type' => 'text',
                'title' => 'Twitter'
            ],
            [
                'section' => 'theme',
                'group' => 'social',
                'key' => 'theme/social/youtube',
                'type' => 'text',
                'title' => 'Youtube channel'
            ],
            [
                'section' => 'theme',
                'group' => 'social',
                'key' => 'theme/social/instagram',
                'type' => 'text',
                'title' => 'Instagram'
            ],
            [
                'section' => 'theme',
                'group' => 'social',
                'key' => 'theme/social/pinterest',
                'type' => 'text',
                'title' => 'Pinterest'
            ],
            [
                'key' => 'format/block_ip_country',
                'section' => 'general',
                'type' => 'group',
                'title' => 'Country IP Block',
                'description' => 'Select countries to block visitors from'
            ],
            [
                'section' => 'general',
                'key' => 'error_payment_notifications',
                'type' => 'group',
                'title' => 'Error Payment Notifications',
            ],
            [
                'section' => 'general',
                'group' => 'error_payment_notifications',
                'key' => 'error_payment_notifications/telegram_group_id',
                'type' => 'text',
                'title' => 'Select Telegram Group Id',
                'full_row' => true
            ],
            [
                'section' => 'general',
                'group' => 'format/block_ip_country',
                'key' => 'list/block_ip_countries',
                'type' => 'list_block_ip_countries',
                'title' => 'Blocks Countries',
                'show_change' => true,
                'data_type' => 'json',
                'full_row' => true
            ],
            [
                'key' => 'format/block_auto_convert_price_country',
                'section' => 'general',
                'type' => 'group',
                'title' => 'Automatic Currency Conversion Exclusion',
                'description' => 'Select countries to disable Automatic Currency Conversion'
            ],
            [
                'section' => 'general',
                'group' => 'format/block_auto_convert_price_country',
                'key' => 'list/block_countries_auto_convert_price',
                'type' => 'list_block_countries_auto_convert_price',
                'title' => 'Automatic Currency Conversion Exclusion',
                'show_change' => true,
                'data_type' => 'json',
                'full_row' => true
            ],
            [
                'section' => 'theme',
                'key' => 'live_chat',
                'type' => 'group',
                'title' => 'Live Chat'
            ],
            [
                'section' => 'theme',
                'group' => 'live_chat',
                'key' => 'theme/live_chat/enable',
                'type' => 'switcher',
                'title' => 'Enable live chat',
                'full_row' => true
            ]
        ];
        return $setting_items;
    }

    public function collectSettingType() {
        return [
            [
                'key' => 'list_block_ip_countries',
                'template' => 'frontend/setting_type/block_ip_countries',
                'validator' => [Observer_Frontend_Backend, 'validateCountry']
            ],
            [
                'key' => 'list_block_countries_auto_convert_price',
                'template' => 'frontend/setting_type/block_countries_auto_convert_price',
                'validator' => [Observer_Frontend_Backend, 'validateCountry']
            ]
        ];
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

    public static function validatePhoneNumbers($values)
    {
        $values = trim($values);
        $values = explode("\n", $values);

        $values = array_map(function ($value) {
            return trim($value);
        }, $values);

        $values = array_filter($values, function ($value) {
            return $value != '';
        });

        return implode("\n", $values);
    }

    public static function checkAddressIp(){
        echo 3;
    }

    public static function validateRobotTxt($values)
    {
        $values = trim($values);
        $values = explode("\n", $values);

        $values = array_map(function ($value) {
            return trim($value);
        }, $values);

        return implode("\n", $values);
    }

    public static function generateRobotTxt()
    {
        OSC::core('cron')->addQueue('core/generateRobotTxt');
    }

    public static function navCollectItemType($params) {
        $params['items'][] = array(
            'icon' => 'file-regular',
            'title' => 'HTML Pages',
            'browse_url' => OSC::getUrl('frontend/backend/htmlPageBrowse')
        );
    }
}