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
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSECORE Core
 *
 * @package Helper_Core_Session
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Helper_Core_Date extends OSC_Date {

    public function __construct() {
        parent::__construct();
    }

    public function dateTimeToTimestamp($date = 0, $time = 0, $separator_date = '/', $separator_time = ':')
    {
        if ($date == 0 && $time == 0) {
            $timestamp = 0;
        } else {
            $date = explode($separator_date, $date);
            $time = explode($separator_time, $time);

            for ($i = 0; $i < 3; $i++) {
                if (!isset($date[$i])) {
                    $date[$i] = 0;
                } else {
                    $date[$i] = abs(intval($date[$i]));
                }
            }
            for ($i = 0; $i < 2; $i++) {
                if (!isset($time[$i])) {
                    $time[$i] = 0;
                } else {
                    $time[$i] = abs(intval($time[$i]));
                }
            }

            $timestamp = mktime($time[0], $time[1], 0, $date[1], $date[0], $date[2]);
            if ($timestamp === false || $timestamp < 0) {
                $timestamp = 0;
            }
        }
        return $timestamp;
    }

}
