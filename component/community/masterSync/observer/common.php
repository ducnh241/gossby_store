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
class Observer_MasterSync_Common {

    public static function settingUpdated() {
        OSC::helper('masterSync/common')->addQueue('masterSync/store_setting');
    }

    public static function collectSettingSyncData($params) {
        $params['collected_data']['setting'] = [];

        $keys = [];

        $response = OSC::core('observer')->dispatchEvent('collect_setting_item');

        foreach ($response as $items) {
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (!is_array($item) || !isset($item['key']) || !isset($item['sync_master']) || !$item['sync_master']) {
                    continue;
                }

                $item['key'] = trim($item['key']);

                if (!$item['key']) {
                    continue;
                }

                $keys[] = $item['key'];
            }
        }

        $keys = array_unique(array_values($keys));

        foreach (OSC::helper('core/setting')->getCollection(true) as $model) {
            if (in_array($model->data['setting_key'], $keys, true)) {
                $params['collected_data']['setting'][$model->data['setting_key']] = $model->data['setting_value'];
            }
        }
    }

}
