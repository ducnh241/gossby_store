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
class Observer_Addon_Backend
{

    public static function collectSettingItem()
    {
        return [];
    }

    public static function collectMenu()
    {
        $menus = [];

        if (OSC::controller()->checkPermission('addon_service', false)) {
            $menus[] = [
                'key' => 'product_config/addon_service',
                'parent_key' => 'product_config',
                'position' => 900,
                'title' => 'Add-on Services',
                'url' => OSC::getUrl('addon/backend_service/index'),
            ];
        }

        return $menus;
    }

    public static function collectPermKey($params)
    {
        $params['permission_map']['addon_service'] = [
            'label' => 'Add-on Service',
            'items' => [
                'report' => 'Report',
                'view' => 'View',
                'add' => 'Add',
                'edit' => 'Edit',
                'delete' => 'Delete'
            ]
        ];
    }
}
