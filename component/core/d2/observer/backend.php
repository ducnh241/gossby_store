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
class Observer_D2_Backend {

    public static function collectMenu() {
        $menus = [
            [
                'key' => 'd2',
                'icon' => 'tag',
                'position' => 992,
                'title' => 'D2',
                'url' => OSC::getUrl('d2/backend/index'),
            ],
            [
                'parent_key' => 'd2',
                'key' => 'd2/product',
                'title' => 'Products',
                'url' => OSC::getUrl('d2/backend/index'),
            ],
            [
                'parent_key' => 'd2',
                'key' => 'd2/resource',
                'title' => 'Resource',
                'url' => OSC::getUrl('d2/backend_resource/index'),
            ],
        ];

        if (OSC::helper('user/authentication')->getMember()->isAdmin()) {
            $menus[] = [
                'parent_key' => 'd2',
                'key' => 'd2/progress_queue',
                'title' => 'Progress Queue ',
                'url' => OSC::getUrl('d2/backend_product/listBulkQueue'),
            ];
        }

        return $menus;
    }

    public static function collectPermKey($params) {
        $params['permission_map']['d2'] = [
            'label' => 'D2',
            'items' => [
                'product' => [
                    'label' => 'Product',
                    'items' => [
                        'add' => 'Add',
                        'edit' => 'Edit',
                        'delete' => 'Delete'
                    ]
                ],
                'resource' => [
                    'label' => 'Resource',
                    'items' => [
                        'add' => 'Add',
                        'edit' => 'Edit',
                        'delete' => 'Delete'
                    ]
                ]
            ]
        ];
    }

}
