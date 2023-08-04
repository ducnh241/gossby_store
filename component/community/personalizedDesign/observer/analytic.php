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
class Observer_PersonalizedDesign_Analytic {

    public static function afterPlaceOrder(Model_Catalog_Order $order) {
        $queue_data = [];
        $meta_ids = [];

        foreach ($order->getLineItems() as $line_item) {
            if ($line_item->isCrossSellMode()) {
                continue;
            }
            if($line_item->data['order_item_meta_id'] > 0) {
                $meta_ids[] = intval($line_item->data['order_item_meta_id']);
            }
        }

        if(count($meta_ids) < 1) {
            return;
        }

        $meta_collection = OSC::model('catalog/order_item_meta')->getCollection()->loadByMetaIds($meta_ids);
        
        foreach ($meta_collection as $meta) {
            foreach ($meta->data['custom_data'] as $custom_data) {
                if (
                    $custom_data['key'] == 'campaign' &&
                    isset($custom_data['data']['print_template']) &&
                    is_array($custom_data['data']['print_template']) &&
                    isset($custom_data['data']['print_template']['segment_source']) &&
                    is_array($custom_data['data']['print_template']['segment_source']) &&
                    count($custom_data['data']['print_template']['segment_source']) > 0
                ) { // campaign
                    foreach($custom_data['data']['print_template']['segment_source'] as $segment) {
                        if(is_array($segment) && isset($segment['source']) && is_array($segment['source']) && isset($segment['source']['type']) && $segment['source']['type'] == 'personalizedDesign') {
                            $queue_data[] = self::_parseConfigData($segment['source']);
                        }
                    }

                    break;
                } else if (
                    $custom_data['key'] == 'personalized_design' &&
                    isset($custom_data['data']) &&
                    is_array($custom_data['data'])
                ) { // semitest
                    foreach ($custom_data['data'] as $segment) {
                        if (isset($segment) && is_array($segment)) {
                            $queue_data[] = self::_parseConfigData($segment);
                        }
                    }

                    break;
                }
            }
        }

        if(count($queue_data) > 0) {
            OSC::model('personalizedDesign/analyticProcessQueue')->setData([
                'queue_data' => $queue_data,
                'added_timestamp' => time()
            ])->save();
        }
    }

    private function _parseConfigData($config_data) {
        $queue_item = [
            'design_id' => $config_data['design_id'],
            'operator' => 'increment',
            'data' => []
        ];

        foreach($config_data['config'] as $k => $v) {
            if(! isset($config_data['config_preview'][$k])) {
                $queue_item['data'][$k] = [
                    'layer' => 'Unknown layer',
                    'form' => 'Unknown form',
                    'parsed_value' => 'Unknown value',
                    'value_hash' => '',
                    'value' => $v
                ];
            } else {
                if($config_data['config_preview'][$k]['type'] == 'input') {
                    continue;
                }

                $queue_item['data'][$k] = [
                    'layer' => $config_data['config_preview'][$k]['layer'],
                    'form' => $config_data['config_preview'][$k]['form'],
                    'parsed_value' => $config_data['config_preview'][$k]['value'],
                    'value_hash' => $config_data['config_preview'][$k]['hash'] ?? '',
                    'value' => $v
                ];
            }
        }

        return $queue_item;
    }
}
