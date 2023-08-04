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
class Observer_Filter_Backend
{

    public static function collectMenu()
    {
        $menus = [];

        if (OSC::controller()->checkPermission('filter', false)) {
            $menus[] = array(
                'key' => 'filter',
                'icon' => 'filter',
                'title' => 'Filter & Search',
                'url' => OSC::getUrl('filter/tag/index'),
            );
        }

        if (OSC::controller()->checkPermission('filter/tag', false)) {
            $menus[] = array(
                'parent_key' => 'filter',
                'key' => 'filter/tag',
                'title' => 'Tags',
                'url' => OSC::getUrl('filter/tag/index'),
            );
        }

        if (OSC::controller()->checkPermission('filter/auto_tag', false)) {
            $menus[] = array(
                'parent_key' => 'filter',
                'key' => 'filter/auto_tag',
                'title' => 'Auto Tags',
                'url' => OSC::getUrl('filter/autoTag/index'),
            );
        }

        if (OSC::controller()->checkPermission('filter/setting_search', false)) {
            $menus[] = array(
                'parent_key' => 'filter',
                'key' => 'filter/setting_search',
                'title' => 'Setting Search',
                'url' => OSC::getUrl('filter/search/index'),
            );
        }

        if (OSC::controller()->checkPermission('filter/gift_finder', false)) {
            $menus[] = array(
                'parent_key' => 'filter',
                'key' => 'filter/gift_finder',
                'title' => 'Gift Finder',
                'url' => OSC::getUrl('filter/giftFinder/index'),
            );
        }

        return $menus;
    }

    public static function collectPermKey($params)
    {
        $params['permission_map']['filter'] = [
            'label' => 'Filter',
            'items' => [
                'tag' => [
                    'label' => 'Tag',
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
                'setting_search' => 'Setting Search',
                'gift_finder' => 'Gift Finder',
                'auto_tag' => [
                    'label' => 'Auto Tag',
                    'items' => [
                        'setting_fields' => 'Setting Fields'
                    ]
                ]
            ]
        ];
    }

}
