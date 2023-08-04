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
class Observer_Klaviyo_Backend {

    public static function collectSettingItem() {
        return [
            [
                'section' => 'klaviyo',
                'key' => 'klaviyo',
                'type' => 'group',
                'title' => 'Klaviyo',
                'description' => 'Set up Klaviyo Services'
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'tracking/klaviyo/code',
                'type' => 'text',
                'title' => 'Api key',
                'sync_master' => true
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'tracking/klaviyo/sref_id',
                'type' => 'number',
                'title' => 'Sref id',
                'sync_master' => true
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'tracking/klaviyo/abandon_sref_id',
                'type' => 'number',
                'title' => 'Abandon Sref id',
                'sync_master' => true
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'tracking/klaviyo/new_arrival_sref_id',
                'type' => 'number',
                'title' => 'New arrivals Sref id',
                'sync_master' => true
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'tracking/klaviyo/enable_4_onsite_behaviors',
                'type' => 'switcher',
                'title' => 'Enable Klaviyo tracking',
                'line_before' => true,
                'sync_master' => true,
                'row_before_desc' => 'Enable tracking 4 onsite behaviors by Klaviyo'
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'tracking/klaviyo/skip_amazon',
                'type' => 'switcher',
                'title' => 'Disable outgoing emails by Amazon',
                'sync_master' => true,
                'line_before' => true,
                'full_row' => true,
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'tracking/klaviyo/enable',
                'type' => 'switcher',
                'title' => 'Enable Klaviyo',
                'line_before' => true,
                'sync_master' => true,
                'row_before_desc' => 'When Klaviyo flows are enabled, emails will be sent using Klaviyo only. If you disable Klaviyo flows, outgoing emails will be sent by both Amazon and Klaviyo. To prevent outgoing emails from being sent by Klaviyo, please disable the integration on Klaviyo’s own system.'
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'klaviyo/enable_subscribe',
                'type' => 'switcher',
                'title' => 'Enable Flow Subscribe',
                'sync_master' => true,
                'full_row' => true
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'klaviyo/enable_trust_pilot',
                'type' => 'switcher',
                'title' => 'Enable Flow Send Trust Pilot Mail',
                'sync_master' => true,
                'full_row' => true
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'klaviyo/enable_request_review',
                'type' => 'switcher',
                'title' => 'Enable Flow Request Review',
                'sync_master' => true,
                'full_row' => true
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'klaviyo/enable_customer_reviewed',
                'type' => 'switcher',
                'title' => 'Enable Flow Customer reviewed (Thank review)',
                'sync_master' => true,
                'full_row' => true
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'klaviyo/enable_placed_order',
                'type' => 'switcher',
                'title' => 'Enable Flow Placed Order (Confirm & Thankyou)',
                'sync_master' => true,
                'full_row' => true
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'klaviyo/enable_refund',
                'type' => 'switcher',
                'title' => 'Enable Flow Refund',
                'sync_master' => true,
                'full_row' => true
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'klaviyo/enable_cancel_order',
                'type' => 'switcher',
                'title' => 'Enable Flow Cancellation',
                'sync_master' => true,
                'full_row' => true
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'klaviyo/enable_fulfilled_order',
                'type' => 'switcher',
                'title' => 'Enable Flow Fulfilled Order',
                'sync_master' => true,
                'full_row' => true
            ],
            [
                'section' => 'klaviyo',
                'group' => 'klaviyo',
                'key' => 'klaviyo/enable_active_account',
                'type' => 'switcher',
                'title' => 'Enable Flow Active Account CRM',
                'full_row' => true
            ],
        ];
    }

    public static function collectSettingSection()
    {
        return [
            [
                'key' => 'klaviyo',
                'priority' => 70,
                'icon' => 'setting-klaviyo',
                'title' => 'Klaviyo',
                'description' => 'Set up Klaviyo mail services'
            ]
        ];
    }

    public static function collectPermKey($params) {
        $params['permission_map']['klaviyo'] = [
            'label' => 'Klaviyo',
            'items' => [
                'recron' => 'Recron',
                'requeue' => 'Requeue',
                'delete' => 'Delete'
            ]
        ];
    }

    public static function collectMenu() {
        $menus = [];

        if ((OSC::controller()->checkPermission('klaviyo', false) && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) {
            $menus[] = array(
                'key' => 'catalog_order/klaviyo',
                'parent_key' => 'catalog_order',
                'title' => 'Klaviyo List',
                'url' => OSC::getUrl('klaviyo/common/list'),
            );
        }

        return $menus;
    }
}
