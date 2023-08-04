<?php


class Observer_Shop_Backend {

    public static function collectMenu($params) {
        if ((OSC::getShop()->data['shop_type'] === 1) || !OSC::controller()->checkPermission('shop', false)) {
            return null;
        }

        $permissions = [];
        if (OSC::controller()->checkPermission('shop/account', false) || OSC::controller()->checkPermission('shop/profit/list', false)) {
            $permissions[] = [
                'key' => 'shop_payout',
                'icon' => 'payout',
                'title' => 'Payout',
                'url' => OSC::getUrl('shop/profit/index', [], true)
            ];
        }

        if (OSC::controller()->checkPermission('shop/profit', false)) {
            $permissions[] = [
                'key' => 'shop_payout/dashboard',
                'parent_key' => 'shop_payout',
                'title' => 'Dashboard',
                'url' => OSC::getUrl('shop/profit/index', [], true),
            ];
            $permissions[] = [
                'key' => 'shop_payout/list',
                'parent_key' => 'shop_payout',
                'title' => 'Profit List',
                'url' => OSC::getUrl('shop/profit/list', [], true),
            ];
        }

        if (OSC::controller()->checkPermission('shop/account', false)) {
            $permissions[] = [
                'key' => 'shop_payout/accounts',
                'parent_key' => 'shop_payout',
                'title' => 'Accounts',
                'url' => OSC::getUrl('shop/backend_account/index', [], true),
            ];
        }

        return $permissions;
    }

    public static function collectPermKey($params)
    {
        $params['permission_map']['shop'] = [
            'label' => 'Payout',
            'items' => [
                'shop_payout' => [
                    'label' => 'Dashboard',
                ],
                'profit' => [
                    'label' => 'Profit List',
                ],
                'account' => [
                    'label' => 'Accounts',
                    'items' => [
                        'add' => 'Add',
                        'edit' => 'Edit',
                        'delete' => [
                            'label' => 'Delete',
                            'items' => [
                                'bulk' => 'Bulk delete'
                            ]
                        ],
                    ]
                ]
            ],
        ];
    }

}
