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
class Observer_Catalog_Review {

    public static function afterPlaceOrder(Model_Catalog_Order $order)
    {
        OSC::helper('catalog/product_review')->requestReviewAfterPlaceOrder($order);
    }

    public static function collectSettingItem() {
        //Start Review
        return [
            [
                'section' => 'review',
                'key' => 'review_page',
                'type' => 'group',
                'title' => 'Review Page',
                'description' => 'Customize default settings for Review Page'
            ],
            [
                'section' => 'review',
                'group' => 'review_page',
                'key' => 'review/title',
                'type' => 'text',
                'title' => 'Title',
                'full_row' => true,
            ],
            [
                'section' => 'review',
                'group' => 'review_page',
                'key' => 'review/meta_title',
                'type' => 'text',
                'title' => 'Meta title',
                'full_row' => true,
            ],
            [
                'section' => 'review',
                'group' => 'review_page',
                'key' => 'review/meta_description',
                'type' => 'textarea',
                'title' => 'Meta Description',
                'full_row' => true,
            ],
            [
                'section' => 'review',
                'group' => 'review_page',
                'key' => 'review/meta_image',
                'type' => 'image',
                'title' => 'Meta Image',
                'extension' => 'png,jpg',
                'trim' => true,
                'full_row' => true
            ],
            [
                'section' => 'review',
                'key' => 'review_setup',
                'type' => 'group',
                'title' => 'Review setup',
                'description' => 'Configure default settings for product reviews'
            ],
            [
                'section' => 'review',
                'group' => 'review_setup',
                'key' => 'catalog/product_review/require_photo',
                'type' => 'switcher',
                'title' => 'Require photo(s) with review',
            ],
            [
                'section' => 'review',
                'group' => 'review_setup',
                'key' => 'catalog/product_review/review_code_percentage',
                'type' => 'text',
                'title' => 'Discount code after reviewing (%)',
                'validator' => [Observer_Catalog_Review, 'validatePositiveNumber'],
                'full_row' => true
            ],
            [
                'section' => 'review',
                'group' => 'review_setup',
                'key' => 'catalog/product_review/review_black_list',
                'type' => 'textarea',
                'title' => 'Block keywords (separated by comma)',
                'full_row' => true
            ],
            [
                'section' => 'review',
                'group' => 'review_setup',
                'key' => 'catalog/product_review/list_email_warning_black_list',
                'type' => 'textarea',
                'title' => 'List of emails containing blocked keywords in reviews (separated by comma)',
                'full_row' => true
            ],
            [
                'section' => 'review',
                'group' => 'review_setup',
                'key' => 'catalog/product_review/order_request_review',
                'type' => 'catalog_email_request_review',
                'title' => 'When to send Review Request email',
            ],
            [
                'section' => 'review',
                'group' => 'review_setup',
                'key' => 'catalog/product_review/days_after_fulfill',
                'type' => 'text',
                'title' => 'How many days after order is delivered',
                'validator' => [Observer_Catalog_Review, 'validatePositiveNumber']
            ],
        ];
    }

    public static function validatePositiveNumber($value, $setting_item) {
        return abs(doubleval($value));
    }
}
