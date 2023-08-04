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
class Observer_Catalog_Report {

    public static function recordProductView($params) {
        try {
            $product_id = $params['event_data']['product_id'];

            try {
                OSC::model('catalog/product')->load($product_id)->increment('views');
            } catch (Exception $ex) { }

            //ADD TRACKING AD
            try {
                OSC::helper('report/adTracking')->trackProductView($product_id);
            } catch (Exception $ex) { }

            OSC::helper('report/common')->incrementProductRecord('catalog/item/view', $product_id, 1);

            /* @var $DB OSC_Database */
            $DB = OSC::core('database');

            $DB->select('*', 'catalog_product_unique_visit', ['condition' => '`track_key` = :track_key AND product_id = :product_id', 'params' => ['track_key' => $params['track_key'], 'product_id' => $product_id]], null, 1, 'fetch_product_unique_visit');

            $record = $DB->fetchArray('fetch_product_unique_visit');

            $is_unique_visitor = false;

            if ($record) {
                if ($record['visit_timestamp'] == $params['visit_timestamp']) {
                    return;
                }

                $is_new_visit = true;
                $update_data = ['visit_timestamp' => $params['visit_timestamp']];

                if ($record['unique_timestamp'] != $params['unique_timestamp']) {
                    $is_unique_visitor = true;
                    $update_data['unique_timestamp'] = $params['unique_timestamp'];
                }

                $DB->update('catalog_product_unique_visit', $update_data, 'record_id = ' . $record['record_id'], 1, 'update_product_unique_visit');
            } else {
                $is_new_visit = true;
                $is_unique_visitor = true;

                $DB->insert('catalog_product_unique_visit', [
                    'track_key' => $params['track_key'],
                    'product_id' => $product_id,
                    'unique_timestamp' => $params['unique_timestamp'],
                    'visit_timestamp' => $params['visit_timestamp'],
                    'added_timestamp' => time()
                ], 'insert_product_unique_visit');
            }

            if ($is_new_visit) {
                OSC::helper('report/common')->incrementProductRecord('catalog/item/visit', $product_id, 1);
            }

            if ($is_unique_visitor) {
                OSC::helper('report/common')->incrementProductRecord('catalog/item/unique_visitor', $product_id, 1);
            }
        } catch (Exception $ex) { }
    }

    public static function recordAddToCart($params) {
        try {
            OSC::helper('report/common')->incrementProductRecord('catalog/add_to_cart', 0, 1);
        } catch (Exception $ex) {

        }

        //ADD TRACKING AD
        try {
            OSC::helper('report/adTracking')->trackAddToCart($params['event_data']['product_id'], $params['event_data']['quantity']);
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('recordAddToCart', $params);
        }
    }

    public static function recordCheckoutInitialize($params) {
        try {
            OSC::helper('report/common')->incrementProductRecord('catalog/checkout_initialize', 0, 1);
        } catch (Exception $ex) {

        }

        //ADD TRACKING AD
        try {
            OSC::helper('report/adTracking')->trackCheckoutInitialize($params['event_data']['cart_id']);
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('recordCheckoutInitialize', $params);
        }
    }

    public static function recordPurchase($params) {
        //ADD TRACKING AD
        try {
            $event_data = $params['event_data'];
            OSC::helper('report/adTracking')->trackPurchase([
                'order_id' => $event_data['order_id'] ?? '',
                'total_price' => $event_data['total_price'] ?? '',
                'subtotal_price' => $event_data['subtotal_price'] ?? '',
                'quantity' => $event_data['quantity'] ?? ''
            ]);
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('recordPurchase', $params);
        }
    }
}