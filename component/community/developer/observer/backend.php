<?php

class Observer_Developer_Backend {

    public static function collectMenu() {
        $menus = [];
        if ((OSC::controller()->checkPermission('developer/cron_manager', false) && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) {
            $menus[] = [
                'key' => 'developer/cron_manager',
                'parent_key' => 'developer/developer',
                'title' => 'Cron manage',
                'url' => OSC::getUrl('core/backend_cron/list', [], true)
            ];
        }
        if ((OSC::controller()->checkPermission('developer/master_sync', false) && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) {
            $menus[] = [
                'key' => 'developer/master_sync',
                'parent_key' => 'developer/developer',
                'title' => 'Master Sync',
                'url' => OSC::getUrl('masterSync/backend_index/list', [], true),
            ];
        }
        if ((OSC::controller()->checkPermission('developer', false) && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) {
            $menus[] = [
                'key' => 'developer/developer',
                'icon' => 'debug-normal',
                'title' => 'Developer',
                'position' => 999,
                'url' => count($menus) > 0 ? $menus[0]['url'] : OSC::getUrl('developer/backend_index/index', [], true)
            ];
        }
        return $menus;
    }

    public static function collectPermKey($params) {
        if (OSC::isPrimaryStore()) {
            $params['permission_map']['developer'] = [
                'label' => 'Developer',
                'items' => [
                    'cron_manager' => [
                        'label' => 'Cron manager',
                        'items' => [
                            'delete' => 'Delete Queue',
                            'requeue' => 'Requeue Queue',
                        ]
                    ],
                    'master_sync' => [
                        'label' => 'Master Sync',
                        'items' => [
                            'delete' => 'Delete Queue',
                            'requeue' => 'Requeue Queue',
                        ]
                    ],
                ]
            ];
        } else {
            return [];
        }
    }
}
