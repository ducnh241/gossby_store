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
class Observer_Report_Common {

    public static function addTrack() {
        OSC::helper('frontend/template')->addComponent('report_track');
    }

    public static function collectReactJSExtraData($params) {
        if (!OSC::core('request')->get('track_evt')) {
            return;
        }

        if(! defined('OSC_REACTJS')) {
        	define('OSC_REACTJS', 1);
        }

        $record_event = OSC::helper('report/common')->getRecordEvent();

        $social_code = OSC::core('observer')->dispatchEvent('frontend/tracking', $record_event);
        $social_code = array_values(array_filter($social_code));

        $record_event = OSC::helper('report/common')->eventEncode($record_event);
        
        $params['data']['evt'] = [
        	'evt_data' => $record_event,
        	'social_code' => $social_code
        ];
    }

    /**
     * Use with case ab test not set in first visit page
     * @param array $params
     * @return void
     */
    public static function incrementVisitAB(array $params) {
        $ab_key = $params['ab_key'];
        $ab_value = $params['ab_value'];

        if (strpos($ab_key, Helper_AutoAb_ProductPrice::PREFIX_COOKIE_KEY_ABTEST) !== false) {
            return;
        }

        $report_keys = ['new_visitor', 'unique_visitor', 'visit']; // ignore increment pageView because api /frontend/record will count it

        $added_timestamp = time();
        $added_timestamp -= $added_timestamp % (60 * 15);

        $referer = OSC::helper('report/common')->getReferer();
        $referer_host = $referer ? $referer['host'] : 'direct';

        $DB = OSC::core('database')->getWriteAdapter();

        foreach ($report_keys as $key) {
            try {
                $DB->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "report_record_new_ab (report_key, ab_key, ab_value, report_value, added_timestamp) VALUES(:report_key, :ab_key, :ab_value, :report_value, :added_timestamp) ON DUPLICATE KEY UPDATE report_value=(report_value + :report_value)", ['report_key' => $key, 'ab_key' => $ab_key, 'ab_value' => $ab_value, 'report_value' => 1, 'added_timestamp' => $added_timestamp], 'update_report_record');
            } catch (Exception $ex) { }

            try {
                $DB->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "report_record_new_referer_ab (report_key, ab_key, ab_value, referer, report_value, added_timestamp) VALUES(:report_key, :ab_key, :ab_value, :referer, :report_value, :added_timestamp) ON DUPLICATE KEY UPDATE report_value=(report_value + :report_value)", ['report_key' => $key, 'ab_key' => $ab_key, 'ab_value' => $ab_value, 'referer' => $referer_host, 'report_value' => 1, 'added_timestamp' => $added_timestamp], 'update_report_record');
            } catch (Exception $ex) { }
        }

    }

}
