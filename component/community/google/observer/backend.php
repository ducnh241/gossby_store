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
class Observer_Google_Backend {

    public static function collectSettingItem() {
        return [
            [
                'section' => 'tracking',
                'key' => 'tag_manager',
                'type' => 'group',
                'title' => 'Google Tag Manager',
                'description' => 'Tracking with Google services'
            ],
            [
                'section' => 'tracking',
                'group' => 'tag_manager',
                'key' => 'tracking/google/tag_manager/code',
                'type' => 'text',
                'title' => 'Tracking key',
                'full_row' => true
            ],
            [
                'section' => 'tracking',
                'key' => 'google_survey',
                'type' => 'group',
                'title' => 'Google Survey',
                'description' => 'Invitation survey by Google Merchant'
            ],
            [
                'section' => 'tracking',
                'group' => 'google_survey',
                'key' => 'tracking/google/verification',
                'type' => 'text',
                'title' => 'Google Site Verification',
                'full_row' => true
            ],
            [
                'section' => 'tracking',
                'group' => 'google_survey',
                'key' => 'tracking/google/survey/merchant_id',
                'type' => 'text',
                'title' => 'Merchant ID',
                'full_row' => true
            ],
            [
                'section' => 'tracking',
                'group' => 'google_survey',
                'key' => 'tracking/google/survey',
                'type' => 'switcher',
                'title' => 'Enable Google Survey',
                'full_row' => true
            ]
        ];
    }

}
