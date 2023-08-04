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
class Observer_Twitter_Common
{

    public static function initialize($tracking_events)
    {
        $pixel_ids = [];

        $twitter_events = ['PageView' => null];

        $enable_twitter_pixel = intval(OSC::helper('core/setting')->get('tracking/twitter/enable')) === 1;
        $twitter_pixels = OSC::helper('core/setting')->get('tracking/twitter/pixels');

        if (!$enable_twitter_pixel || !$twitter_pixels) {
            return;
        }

        $flag_react = defined('OSC_REACTJS') && OSC_REACTJS == 1;

        foreach ($tracking_events as $event => $event_data) {
            switch ($event) {
                case "catalog/purchase":
                    $order_id = isset($event_data['order_id']) ? $event_data['order_id'] : $event_data;
                    $order = OSC_Database_Model::getPreLoadedModel('catalog/order', $order_id);

                    if (!($order instanceof Model_Catalog_Order)) {
                        continue;
                    }

                    $twitter_events['Purchase'] = [
                        'value' => $order->getFloatSubtotalPrice(),
                        'currency' => 'USD',
                        'num_items' => 0,
                    ];

                    foreach ($order->getLineItems() as $line_item) {
                        $twitter_events['Purchase']['num_items'] += $line_item->data['quantity'];
                    }
                    break;
                default:
                    break;
            }
        }

        $twitter_pixels = explode("\n", $twitter_pixels);

        foreach ($twitter_pixels as $twitter_pixel) {
            $twitter_pixel = trim($twitter_pixel);
            if ($twitter_pixel){
                $pixel_ids[] = $twitter_pixel;
            }
        }

        if (count($pixel_ids) < 1) {
            return;
        }
        $pixel_ids = array_unique($pixel_ids);

        if ($flag_react) {
            unset($twitter_events['PageView']);
            return [
                'social_chanel' => 'twitter',
                'position' => 'header',
                'events' => $twitter_events,
                'pixels' => $pixel_ids,
            ];
        }

        $tracking_codes = [];
        $tracking_init = [];

        foreach ($twitter_events as $event_key => $event_data) {
            if ($event_data) {
                $event_data = ',' . OSC::encode($event_data);
            } else {
                $event_data = '';
            }

            $tracking_codes[] = "twq('track', '{$event_key}'{$event_data})";
        }

        foreach ($pixel_ids as $pixel_id) {
            $tracking_init[] = "twq('init', '{$pixel_id}')";
        }

        $tracking_codes = implode(";", $tracking_codes);
        $tracking_init = implode(";", $tracking_init);

        OSC::core('template')->push(<<<EOF
!function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);
},s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='//static.ads-twitter.com/uwt.js',
a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');
// Insert Twitter Pixel ID and Standard Event data below

{$tracking_init}

{$tracking_codes}
EOF
            , 'js_separate');
    }
}

