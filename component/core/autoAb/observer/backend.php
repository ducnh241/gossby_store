<?php

class Observer_AutoAb_Backend {
    public static function collectMenu()
    {
        $menus = [];

        if (OSC::controller()->checkPermission('autoAb/super|autoAb/productPrice|catalog/super|catalog/product/full|catalog/product/edit', false) && OSC::isPrimaryStore()) {
            $menus[] = [
                'key' => 'autoAb',
                'position' => 990,
                'icon' => 'ab-test',
                'title' => 'Auto AB Test',
                'url' => OSC::getUrl('autoAb/backend_productPrice/list'),
            ];
        }

        if (OSC::controller()->checkPermission('autoAb/super|autoAb/productPrice/full|autoAb/productPrice/list|catalog/super|catalog/product/full|catalog/product/edit', false) && OSC::isPrimaryStore()) {
            $menus[] = [
                'key' => 'autoAb/productPrice',
                'parent_key' => 'autoAb',
                'icon' => '',
                'title' => 'Product Price',
                'url' => OSC::getUrl('autoAb/backend_productPrice/list'),
            ];
        }

        if (OSC::controller()->checkPermission('abProduct/super|abProduct/product/full|autoAb/product/list|catalog/super|catalog/product/full|catalog/product/edit', false) && OSC::isPrimaryStore()) {
            $menus[] = [
                'key' => 'autoAb/testProduct',
                'parent_key' => 'autoAb',
                'icon' => '',
                'title' => 'AB Test Product',
                'url' => OSC::getUrl('autoAb/backend_abProduct/list'),
            ];
        }

        return $menus;
    }

    public static function collectPermKey($params) {
        if (!OSC::isPrimaryStore()) {
            return null;
        }
        $params['permission_map']['autoAb'] = [
            'label' => 'Auto AB Test',
            'items' => [
                'productPrice' => [
                    'label' => 'Product Price',
                    'items' => [
                        'add' => 'Add',
                        'ab_semi_product' => 'Access AB Test Beta Product',
                        'list' => 'List',
                        'view_tracking' => 'View Tracking',
                        'edit' => 'Edit',
                        'delete' => 'Delete',
                        'full' => 'Full permission'
                    ]
                ],
                'super' => 'Super permission'
            ]
        ];

        $params['permission_map']['abProduct'] = [
            'label' => 'AB Test Product',
            'items' => [
                'product' => [
                    'label' => 'AB Test Product',
                    'items' => [
                        'add' => 'Add',
                        'list' => 'List',
                        'view_tracking' => 'View Tracking',
                        'edit' => 'Edit',
                        'delete' => 'Delete',
                        'full' => 'Full permission'
                    ]
                ],
                'super' => 'Super permission'
            ]
        ];
    }
}
