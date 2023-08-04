<?php

class Observer_Marketing_Backend
{
    public static function collectMenu()
    {
        if (!OSC::helper('user/authentication')->getMember()->getGroup()->isAdmin()) {
            return null;
        }

        $menu = [];

        $menu[] = [
                'key' => 'marketing',
                'position' => 1000,
                'icon' => 'mail-bulk-regular',
                'title' => 'Marketing',
                'url' => OSC::getUrl('marketing/backend/index', [], true)
            ];

        $menu[] = array(
            'key' => 'marketing/setting',
            'parent_key' => 'marketing',
            'position' => 900,
            'title' => 'Settings',
            'url' => OSC::getUrl('core/backend_setting/config', ['section' => 'marketing'], true)
        );

        return $menu;
    }

    public static function collectSettingSection()
    {
        return [
            [
                'key' => 'marketing',
                'priority' => 8,
                'icon' => 'setting-marketing',
                'title' => 'Marketing',
                'description' => 'Choose Marketing model settings'
            ]
        ];
    }

    public function collectSettingType()
    {
        return [
            [
                'key' => 'marketing_point',
                'template' => 'marketing/setting_type/add_point',
                'validator' => [Observer_Marketing_Backend, 'validateItem']
            ]
        ];
    }

    public static function collectSettingItem()
    {
        return [
            [
                'section' => 'marketing',
                'key' => 'general',
                'type' => 'group',
                'title' => 'Marketing points'
            ],
            [
                'section' => 'marketing',
                'group' => 'general',
                'key' => 'marketing_point',
                'type' => 'marketing_point',
                'title' => 'Setup marketing point',
                'desc' => 'Add default global point to product',
                'full_row' => true
            ]
        ];
    }

    public static function validateProductType($value) {
        if (!is_array($value)) {
            $value = [$value];
        }

        $value = array_map(function($value) {
            return trim($value);
        }, $value);
        $value = array_filter($value, function($value) {
            return $value !== '';
        });
        $value = array_unique($value);
        $value = array_values($value);

        return $value;
    }

    public static function validateCollection($value) {
        if (!is_array($value)) {
            $value = [$value];
        }

        $value = array_map(function($value) {
            return intval(trim($value));
        }, $value);
        $value = array_filter($value, function($value) {
            return $value !== '';
        });
        $value = array_unique($value);
        $value = array_values($value);

        return $value;
    }

    public static function validateItem($value, $setting_item)
    {
        $rows = [];

        foreach ($value as $row) {
            $name = trim($row['name']);

            if ($name == '') {
                throw new Exception('Group name can not blank.');
            }

            $rows[$row['ukey']]['name'] = $name;
            $rows[$row['ukey']]['product_type'] = self::validateProductType($row['product_type']);
            $rows[$row['ukey']]['collection'] = self::validateCollection($row['collection']);
            $rows[$row['ukey']]['product_ids'] = array_unique($row['product_ids']);

            $var = [];
            foreach ($row as $item) {
                if (!isset($item['day']) || !isset($item['point'])) {
                    continue;
                }

                $item['day'] = trim($item['day']);
                $item['point'] = trim($item['point']);
                $item['sref'] = trim($item['sref']);
                $item['vendor'] = trim($item['vendor']);

                if ($item['day'] <= 0) {
                    throw new Exception('Number of days must be greater than 0');
                }

                if (!$item['day'] || !$item['point']) {
                    continue;
                }

                if (array_key_exists($item['day'], $var)) {
                    throw new Exception('The number of days in a group cannot coincide.');
                }

                $var[$item['day']] = [
                    'day' => $item['day'],
                    'point' => OSC::helper('catalog/common')->floatToInteger(floatval($item['point'])), // Convert to integer
                    'sref' => $item['sref'],
                    'vendor' => $item['vendor']
                ];
            }

            if (empty($var) || count($var) == 0) {
                throw new Exception('It is necessary to enter at least one day and point rate to apply settings.');
            }

            // Sort $var by day ASC
            ksort($var);

            $ark = [];
            foreach ($var as $key => $val) {
                $ark[] = $key;
            }

            //Unique day and value in one group
            $ark = array_unique($ark);
            $arkSort = Helper_Core_Array::sortSingleArray($ark);

            if (!($ark === $arkSort)) {
                throw new Exception('The number of days must increase gradually.');
            }

            $rows[$row['ukey']]['value'] = $var;
        }

        try {
            OSC::model('backend/log')->setData(array('content' => 'Marketing Point: Update Settings', 'log_data' => $rows))->save();
        } catch (Exception $ex) {
        }

        return $rows;
    }
}