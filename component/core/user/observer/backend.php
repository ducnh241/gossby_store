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

/**
 * OSC_User
 *
 * @package Observer_User_Backend_AppItem
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Observer_User_Backend {

    public static function collectMenu($params) {
        $admin_group = OSC::helper('user/authentication')->getMember()->getGroupAdmin();
        if (!OSC::helper('user/authentication')->getMember()->isAdmin() && !$admin_group) {
            return null;
        }

        $menus = [
            [
                'key' => 'user',
                'icon' => 'user',
                'position' => 3,
                'title' => 'Users & Groups',
                'divide' => true,
                'url' => OSC::getUrl('user/backend_member/index', [], true),
            ]
        ];

        if (OSC::helper('user/authentication')->getMember()->isAdmin() || $admin_group && $admin_group->getId() > 0) {
            $menus[] = [
                'key' => 'user/member',
                'parent_key' => 'user',
                'title' => 'Users',
                'url' => OSC::getUrl('user/backend_member/index', [], true),
            ];
        }

        if (OSC::helper('user/authentication')->getMember()->isAdmin()) {
            $menus = array_merge($menus, [
                [
                    'key' => 'user/group',
                    'parent_key' => 'user',
                    'title' => 'Groups',
                    'url' => OSC::getUrl('user/backend_group/index', [], true),
                ],
                [
                    'key' => 'user/admin',
                    'parent_key' => 'user',
                    'title' => 'Group Administrators',
                    'url' => OSC::getUrl('adminGroup/backend_adminGroup/index'),
                ],
                [
                    'key' => 'user/permmask',
                    'parent_key' => 'user',
                    'title' => 'Permission Mask',
                    'url' => OSC::getUrl('user/backend_permissionMask/index', [], true),
                ],
                [
                    'key' => 'user/perm_analytic',
                    'parent_key' => 'user',
                    'title' => 'Analytics Viewing Permissions',
                    'url' => OSC::getUrl('user/backend_permissionAnalytic/index', [], true),
                ]
            ]);
        }
        return $menus;
    }

}
