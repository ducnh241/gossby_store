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
 * OSC_Framework::Date
 *
 * @package OSC_Date
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Date extends OSC_Object {

    /**
     *
     * @var int 
     */
    protected $_timezone = 0;

    /**
     *
     * @var string 
     */
    protected $_long_format = 'D, F d, Y @ h:i A';

    /**
     *
     * @var string 
     */
    protected $_short_format = 'd.m.y - H:i';

    /**
     *
     * @var int 
     */
    protected $_default_timezone = 0;

    public function getTimezoneList() {
        static $list = null;

        if ($list === null) {
            $list = array();

            foreach (timezone_identifiers_list() as $timezone) {
                $t = new DateTimeZone($timezone);
                $list[$timezone] = $t->getOffset(new DateTime('now', $t));
            }
        }

        return $list;
    }

    public function getCurrentTimezone() {
        return date_default_timezone_get();
    }

    public function setTimezone($timezone) {
        return date_default_timezone_set($timezone);
    }

    /**
     * 
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param int $month
     * @param int $day
     * @param int $year
     * @param int $timezone
     * @return int
     */
    public function mktime($hour, $minute, $second, $month, $day, $year, $timezone = null) {
        return mktime($hour, $minute, $second, $month, $day, $year) - ( ( $timezone === null ? $this->_timezone : intval($timezone) ) * 60 * 60 );
    }

    /**
     * 
     * @param int $timestamp
     * @param string $format
     * @param int $timezone
     * @return string
     */
    public function parse($timestamp = null, $format = false, $timezone = null) {
        if ($timestamp === null || !$timestamp || $timestamp < 1) {
            $timestamp = time();
        }

        if (is_bool($format)) {
            $format = $format ? $this->_long_format : $this->_short_format;
        } else {
            $format = strval($format);
        }

        return gmdate($format, $timestamp + ($timezone === null ? $this->_timezone : intval($timezone)) * 60 * 60);
    }

    /**
     * 
     * @param int $timestamp
     * @return string
     */
    public function timestampToDate($timestamp) {
        return $this->parse($timestamp, 'd/m/Y');
    }

    /**
     * 
     * @param int $d
     * @param int $m
     * @param int $y
     * @param int $timezone
     * @return int
     */
    public function dateToTimestamp($d, $m, $y, $timezone = null) {
        return mktime(0, 0, 1, $m, $d, $y) - (($timezone === null ? $this->_timezone : intval($timezone)) * 60 * 60);
    }

    public function timeElapsed($datetime, $full = false) {
        $now = new DateTime;
        $provide_date = new DateTime(preg_match('/[^0-9]/', $datetime) ? $datetime : ('@' . $datetime));
        $diff = $now->diff($provide_date);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];
        
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }
        
        $more = '';

        if (!$full && count($string) > 1) {
            //$more = 'more ';
            $string = array_slice($string, 0, 1);
        }

        return $string ? ($more . implode(', ', $string) . ($provide_date->getTimestamp() < $now->getTimestamp() ? ' ago' : ' next')) : 'just now';
    }

    public function getMicrotime() {
        return str_replace('.', '', (string) round(microtime(true), 5));
    }

    public function getLastXDays($days = 7, $format = 'Y-m-d') {
        $month = date('m');
        $day = date('d');
        $year = date('Y');
        $date_array = array();
        for ($i = 0; $i <= $days - 1; $i++) {
            $date_array[] = date($format, mktime(0, 0, 0, $month, ( $day - $i), $year));
        }

        return array_reverse($date_array);
    }

    public function getDayToDay($date1, $date2, $format = 'Y-m-d') {
        $start = $date1;
        $time1 = strtotime($start);

        $date1 = new DateTime($date1);
        $date2 = new DateTime($date2);
        $num_date = $date1->diff($date2);
        $num_date = $num_date->days;

        $date_array = array();
        for ($i = 0; $i <= $num_date; $i++) {
            $this_day = $time1 + $i * 24 * 60 * 60;
            $day_i = date('d', $this_day);
            $month = date('m', $this_day);
            $year = date('Y', $this_day);
            $date_array[] = date($format, mktime(0, 0, 0, $month, $day_i, $year));
        }

        return $date_array;
    }
}
