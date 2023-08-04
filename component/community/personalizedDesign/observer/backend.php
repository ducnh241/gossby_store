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
class Observer_PersonalizedDesign_Backend {

    public static function collectMenu() {
        $menus = [];

        if (OSC::controller()->checkPermission('personalized_design', false)) {
            $menus[] = array(
                'key' => 'personalized_design',
                'icon' => 'magic-solid',
                'position' => 994,
                'title' => 'Personalized Design',
                'url' => OSC::getUrl('personalizedDesign/backend/index'),
            );
        }

        if ((OSC::controller()->checkPermission('personalized_design/rerender', false) && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) {
            $menus[] = array(
                'key' => 'personalized_design/rerender_log',
                'parent_key' => 'personalized_design',
                'title' => 'Rerender queue log',
                'url' => OSC::getUrl('personalizedDesign/rerenderLog/index'),
            );
        }

        if ((OSC::controller()->checkPermission('personalized_design/sync_queue', false) && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) {
            $menus[] = [
                'key' => 'personalized_design/sync_queue',
                'parent_key' => 'personalized_design',
                'title' => 'Personalized design sync queue list',
                'url' => OSC::getUrl('personalizedDesign/sync/index'),
            ];
        }

        return $menus;
    }

    public static function productPostFrmRender($params) {
        $design_item = null;

        if (is_array($params['model']->data['tags'])) {
            foreach ($params['model']->data['tags'] as $idx => $tag) {
                if (preg_match('/^meta:personalizedDesign:(\d+)$/i', $tag, $matches)) {
                    unset($params['model']->data['tags'][$idx]);

                    if ($design_item === null) {
                        try {
                            $design_item = OSC::model('personalizedDesign/design')->load($matches[1]);
                            $design_item = [
                                'id' => $design_item->getId(),
                                'title' => $design_item->data['title']
                            ];
                        } catch (Exception $ex) {
                            $design_item = null;
                        }
                    }
                }
            }
        }

        $params['columns']['sidebar'][] = OSC::helper('backend/template')->build('personalizedDesign/productPostFrm', ['design_item' => $design_item]);
    }

    public static function productPostFrmSaveData($params) {
        $tags = $params['model']->data['tags'];

        if (!is_array($tags)) {
            $tags = [];
        }

        foreach ($tags as $idx => $tag) {
            if (preg_match('/^meta:personalizedDesign:(\d+)$/i', $tag)) {
                unset($tags[$idx]);
            }
        }

        $design_id = intval(OSC::core('request')->get('personalized_design_id'));

        if ($design_id > 0) {
            $tags[] = 'meta:personalizedDesign:' . $design_id;
        }

        $params['model']->setData('tags', $tags);
    }

    public static function collectPermKey($params) {
        $params['permission_map']['personalized_design'] = [
            'label' => 'Personalized Design',
            'items' => [
                'view_all' => 'View All',
                'view_group' => 'View Group',
                'add' => 'Add',
                'edit' => [
                    'label' => 'Edit',
                    'items' => [
                        'locked' => [
                            'label' => 'Edit locked',
                            'items' => [
                                'edit_layer' => 'Edit Layer',
                                'remove_layer' => 'Remove Layer'
                            ]
                        ]
                    ]
                ],
                'delete' => 'Delete',
                'view_report' => 'View report'
            ]
        ];
        if (OSC::isPrimaryStore()) {
            $params['permission_map']['personalized_design']['items'] = array_merge($params['permission_map']['personalized_design']['items'], [
                'rerender' => [
                    'label' => 'Rerender all orders by design',
                    'items' => [
                        'delete_log' => 'Delete rerender log'
                    ]
                ],
                "sync_queue" => [
                    'label' => 'Personalized Design Sync Queue',
                    'items' => [
                        'delete' => 'Delete personalized sync queue',
                        'requeue' => 'Requeue personalized sync queue'
                    ]
                ],
                'full' => 'Full manage',
                'amazon' => 'Amazon'
            ]);
        }
    }

}
