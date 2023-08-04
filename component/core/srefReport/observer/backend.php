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
class Observer_SrefReport_Backend {

    public static function collectSettingSection() {
        return [
            [
                'key' => 'analytics',
                'priority' => 999,
                'icon' => 'analytics',
                'title' => 'Analytics',
                'description' => 'Manage your analytics'
            ]
        ];
    }

    public static function collectSettingItem()
    {
        return [
            [
                'section' => 'analytics',
                'key' => 'power_bi_general',
                'type' => 'group',
                'title' => 'Manage Your Power Bi'
            ],
            [
                'section' => 'analytics',
                'group' => 'power_bi_general',
                'key' => 'power_bi_manage',
                'type' => 'power_bi_manage',
                'title' => 'Manage Power Bi',
                'full_row' => true
            ]
        ];
    }

    public function collectSettingType()
    {
        return [
            [
                'key' => 'power_bi_manage',
                'template' => 'srefReport/setting_type/power_bi',
                'validator' => [Observer_SrefReport_Backend, 'validateItem']
            ]
        ];
    }

    public static function validateItem($value, $setting_item)
    {
        $rows = [];
        $members = OSC::model('user/member')->getListMemberHasPerm('power_bi');
        $member_ids = array_column($members->toArray(), 'member_id');

        foreach ($value as $row) {
            $name = trim($row['name']);

            if(!$name || $name == '') {
                throw new Exception('Report name không được rỗng');
            }
            $rows[$row['ukey']]['name'] = $name;

            $url = $row['url'];
            if(!OSC::isUrl($url)) {
                throw new Exception('Power Bi Url không được rỗng');
            }
            $rows[$row['ukey']]['url'] = $row['url'];

            $viewer = $row['viewer'] ?? null;
            if (is_array($viewer)) {
                foreach ($viewer as $k => $item) {
                    if (!in_array($item, $member_ids)) {
                        unset($viewer[$k]);
                    }
                }
            }

            $rows[$row['ukey']]['viewer'] = $viewer;
        }

        return $rows;
    }

    public static function collectMenu() {
        $has_menu = false;
        $menus = [];
        if (OSC::controller()->checkPermission('srefReport|report', false)) {
            $has_menu = true;
            $menus = array_merge($menus, [
                [
                    'key' => 'srefReport/dashboard',
                    'parent_key' => 'srefReport',
                    'title' => 'Dashboard',
                    'url' => OSC::getUrl('srefReport/backend/index', [], true)
                ],
                [
                    'key' => 'srefReport/product',
                    'parent_key' => 'srefReport',
                    'title' => 'Products',
                    'url' => OSC::getUrl('srefReport/backend/productList', [], true)
                ],
                [
                    'key' => 'srefReport/adTracking',
                    'parent_key' => 'srefReport',
                    'title' => 'Ad Tracking',
                    'url' => OSC::getUrl('srefReport/backend/adTracking', [], true)
                ],
                [
                    'key' => 'srefReport/marketingPoint',
                    'parent_key' => 'srefReport',
                    'title' => 'Marketing Point',
                    'url' => OSC::getUrl('srefReport/backend/marketingPoint', [], true)
                ]
            ]);
        }

        if (OSC::helper('srefReport/common')->currentUserHasPowerBiPerm() && OSC::controller()->checkPermission('power_bi', false)) {
            $has_menu = true;
            $menus = array_merge($menus, [
                [
                    'key' => 'srefReport/powerBi',
                    'parent_key' => 'srefReport',
                    'title' => 'Power Bi',
                    'url' => OSC::getUrl('srefReport/backend/getPowerBi', [], true)
                ]
            ]);
        }

        if ($has_menu === true) {
            return array_merge([
                [
                    'key' => 'srefReport',
                    'divide' => true,
                    'position' => 2,
                    'title' => 'Analytics',
                    'icon' => 'analytics',
                    'url' => OSC::controller()->checkPermission('srefReport|report', false) ? OSC::getUrl('srefReport/backend/index', [], true) : OSC::getUrl('srefReport/backend/getPowerBi', [], true)
                ]
            ], $menus);
        }

        return [];
    }

    public static function collectPermKey($params) {
        $params['permission_map']['srefReport'] = [
            'label' => 'Sref Analytics'
        ];
    }
}
