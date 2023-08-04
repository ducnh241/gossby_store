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
 * @copyright	Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
define('OSC_INNER', 1);
define('OSC_SITE_PATH', dirname(__FILE__));
define('OSC_SITE_KEY', 'osecore');

include OSC_SITE_PATH . '/app.php';

$map = [
    'general/social/facebook' => 'theme/social/facebook',
    'general/social/youtube' => 'theme/social/youtube',
    'general/social/twitter' => 'theme/social/twitter',
    'general/contact/name' => 'theme/contact/name',
    'general/contact/about' => 'theme/about',
    'general/contact/address' => 'theme/contact/address',
    'general/contact/email' => 'theme/contact/email',
    'general/contact/fax' => 'theme/contact/fax',
    'general/facebook/app_id' => 'facebook_api/app_id',
    'seo/metadata/title' => 'theme/metadata/title',
    'seo/metadata/keywords' => 'theme/metadata/keyword',
    'seo/metadata/description' => 'theme/metadata/description',
    'seo/metadata/image_url' => 'theme/metadata/image',
    'catalog/store/address' => 'catalog/store/address',
    'general/site/name' => 'theme/site_name',
    'footer/column_1/title' => 'theme/footer/column1/title',
    'footer/column_1/text' => 'theme/footer/column1/content',
    'footer/column_2/title' => 'theme/footer/column2/title',
    'footer/column_2/nav' => 'theme/footer/column2/content',
    'footer/column_3/title' => 'theme/footer/column3/title',
    'footer/column_3/nav' => 'theme/footer/column3/content',
    'footer/copyright/value' => 'theme/footer/copyright',
    'payment/paypal/api_id' => 'payment/paypal/client_id',
    'payment/paypal/api_secret' => 'payment/paypal/client_secret',
    'payment/stripe/public_key' => 'payment/stripe/public_key',
    'payment/stripe/secret_key' => 'payment/stripe/secret_key',
    'shipping/table_rate/rate' => 'shipping/table_rate/data',
    'shipping/table_rate/subtotal_for_free' => 'shipping/table_rate/free_shipping',
    'catalog/facebook/pixel' => 'tracking/facebook_pixel/code',
    'catalog/order/code_pattern' => 'catalog/order_code/prefix',
    'payment/paypal/sandbox' => 'payment/paypal/sandbox',
    'general/googleanalytics/code' => 'tracking/google/analytic/code',
    'general/site/timezone' => 'core/timezone',
    'tracking/luckyorange/code' => 'tracking/luckyorange/code',
    'marketing/sms/enable' => 'catalog/twilio/abandoned_cart',
    'marketing/sms/twilio_sid' => 'catalog/twilio/sid',
    'marketing/sms/twilio_token' => 'catalog/twilio/token',
    'marketing/sms/twilio_sms_sid' => 'catalog/twilio/service_id',
    'marketing/sms/twilio_sender_number' => 'catalog/twilio/sender_number',
    'header/top_menu/nav' => 'theme/header/top_menu',
    'header/main_menu/nav' => 'theme/header/main_menu',
    'order/email/timezone' => 'catalog/auto_export_order/timezone',
    'order/email/receiver' => 'catalog/auto_export_order/receiver',
    'order/email/active' => 'catalog/auto_export_order',
    'general/google_tagmanager/enable' => 'tracking/google/tag_manager',
    'general/google_tagmanager/code' => 'tracking/google/tag_manager/code',
    'general/additional_head_tag/code' => 'theme/additional_head_tag',
    'feed/facebook_rss' => 'catalog/facebook_feed/collection',
    'feed/google_rss' => 'catalog/google_feed/collection'
];

/* @var $DB OSC_Database */
$DB = OSC::core('database');

$DB->select('*', 'setting');

$setting_items = $DB->fetchArrayAll();

$collection = OSC::model('core/setting')->getCollection()->load();

foreach ($setting_items as $setting_item) {
    if (!$setting_item['setting_key']) {
        continue;
    }

    if (!isset($map[$setting_item['setting_key']])) {
        echo $setting_item['setting_key'] . " NOT FOUND<br />";
        continue;
    }

    $new_key = $map[$setting_item['setting_key']];

    if ($setting_item['setting_key'] == 'catalog/order/code_pattern') {
        $setting_item['setting_value'] = str_replace('{{number}}', '', $setting_item['setting_value']);
    } else if ($setting_item['setting_key'] == 'catalog/store/address') {
        $setting_item['setting_value'] = OSC::decode($setting_item['setting_value'], true);
    }

    $model = $collection->getItemByUkey($new_key);

    try {
        if ($model instanceof Model_Core_Setting) {
            $model->setData('setting_value', $setting_item['setting_value'])->save();
        } else {
            $model = $collection->getNullModel();

            $model->setData([
                'setting_key' => $new_key,
                'setting_value' => $setting_item['setting_value']
            ])->save();
        }
    } catch (Exception $ex) {
        echo $setting_item['setting_key'] . ": {$ex->getMessage()}<br />";
    }
}

foreach (['payment/paypal', 'payment/stripe', 'shipping/table_rate', 'tracking/google/analytic', 'tracking/facebook_pixel'] as $key) {
    $model = $collection->getItemByUkey($key);

    try {
        if ($model instanceof Model_Core_Setting) {
            $model->setData('setting_value', 1)->save();
        } else {
            $model = $collection->getNullModel();

            $model->setData([
                'setting_key' => $key,
                'setting_value' => 1
            ])->save();
        }
    } catch (Exception $ex) {
        echo $key . ": {$ex->getMessage()}<br />";
    }
}

echo "DONE";
