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
class Observer_Page_Backend
{
    public static function collectMenu() {
        if (OSC::controller()->checkPermission('page', false)) {
            $menus = [
                [
                    'key' => 'page',
                    'icon' => 'files',
                    'position' => 992,
                    'title' => 'Pages',
                    'url' => OSC::getUrl('page/backend/index'),
                ],
                [
                    'parent_key' => 'page',
                    'key' => 'page/page',
                    'title' => 'Pages',
                    'url' => OSC::getUrl('page/backend/index'),
                ],
            ];
            if (OSC::controller()->checkPermission('page/contact_us', false)) {
                $menus = array_merge($menus, [
                    [
                        'parent_key' => 'page',
                        'key' => 'page/contact_us',
                        'title' => 'Contact Us',
                        'url' => OSC::getUrl('contactUs/backend_index/index'),
                    ]
                ]);
            }
        } else {
            $menus = [];
        }

        if (OSC::controller()->checkPermission('page/home_page/update', false)) {
            $menus = array_merge($menus, [
                [
                    'parent_key' => 'page',
                    'key' => 'page/homepage_v3',
                    'title' => 'Homepage V3',
                    'url' => OSC::getUrl('page/homepageV3/index', [], true)
                ]
            ]);
        }

        return $menus;
    }

    public static function navCollectItemType($params) {
        $params['items'][] = array(
            'icon' => 'file-regular',
            'title' => 'Pages',
            'browse_url' => OSC::getUrl('page/backend/browse')
        );
    }

    public static function collectPermKey($params) {
        $params['permission_map']['page'] = [
            'label' => 'Pages',
            'items' => [
                'add' => 'Add',
                'edit' => 'Edit',
                'delete' => 'Delete',
                'home_page' => [
                    'label' => 'Homepage V3',
                    'items' => [
                        'update' => 'Update',
                    ]
                ],
                'contact_us' => 'Contact Us'
            ]
        ];
    }
}