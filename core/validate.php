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
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSC_Framework::Validate
 *
 * @package OSC_Validate
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Validate extends OSC_Object {

    /**
     * 
     * @param string $value
     * @throws Exception
     */
    public function validEmail(string $value) {
        if (!$value) {
            throw new Exception('Email is empty');
        }

        // check exist space, '@', '.@', '@.', '@-'
        if (
            substr_count($value, '@') != 1 ||   // only has one '@'
            strpos($value, ' ') > -1 ||    // not include space
            strpos($value, '.@') > -1 ||   // '.' and '@' are not close
            strpos($value, '@.') > -1 ||   // '.' and '@' are not close
            strpos($value, '@-') > -1      // '-' is not behind '@'
        ) {
            throw new Exception('Email is incorrect format');
        }

        $value_array = explode('@', $value);
        if (count($value_array) !== 2) {
            throw new Exception('Email is incorrect format');
        }

        $local_name = $value_array[0];
        $domain_name = $value_array[1];

        // check local name
        $local_regex = "/^([a-zA-Z0-9\/.!#%&*+=?^_~`{}|-])+$/i";
        $spec_regex = "/^([\/.!#%&*+=?^_~`{}|-])+$/i";
        if (
            strlen($local_name) < 1 ||  // min length = 1
            strlen($local_name) > 64 || // max length = 64
            preg_match($spec_regex, substr($local_name, 0, 1)) ||    // first char is not special char
            !preg_match($local_regex, $local_name)   // regex
        ) {
            throw new Exception('Email is incorrect format');
        }

        // check "." - not start, not multiple
        $local_parts = explode('.', $local_name);
        foreach($local_parts as $part) {
            if ($part === '') {
                throw new Exception('Email is incorrect format');
            }
        }

        // check domain name
        if (substr_count($domain_name, '.-') > 0 || substr_count($domain_name, '-.') > 0) {
            throw new Exception('Email is incorrect format');
        }
        $domain_regex = "/^([a-zA-Z0-9.-])+$/i";
        $domain_number = "/^([0-9])+$/i";
        $domain_spec = "/^([.-])+$/i";
        if (
            !strpos($domain_name, '.') || // at least one dot
            strlen($domain_name) < 3 ||     // min length = 3
            strlen($domain_name) > 253 ||   // max length = 253
            preg_match($domain_spec, substr($domain_name, -1)) ||   // first char is not special char
            preg_match($domain_spec, substr($domain_name, 0, 1)) || // last char is not special char
            preg_match($domain_number, substr($domain_name, 0, 1)) || // last char is not number
            preg_match($domain_number, $domain_name) ||     // regex not only number
            !preg_match($domain_regex, $domain_name)        // regex
        ) {
            throw new Exception('Email is incorrect format');
        }

        // check "." - not start, not multiple
        $domain_parts = explode('.', $domain_name);
        foreach($domain_parts as $part) {
            if ($part === '') {
                throw new Exception('Email is incorrect format');
            }
        }
    }

    /**
     * 
     * @param string $value
     * @return boolean
     */
    public function validUrl($value) {
        $value = trim($value);

        if (!$value) {
            return false;
        }

        if (!preg_match('/^((ftp|(http(s)?)):\/\/)?(\.?([a-z0-9-]+))+\.[a-z]{2,6}(:[0-9]{1,5})?\/?(.*)*$/iu', $value)) {
            return false;
        }

        return true;
    }

    /**
     * Clean date string
     *
     * @param  String $theDate
     * @param  Array $map
     * @return Integer
     */
    public function cleanDate($the_date, $map = array('d' => 0, 'm' => 1, 'y' => 2)) {
        $the_date = str_replace('-', '/', $the_date);
        $the_date = preg_replace("/[^0-9\/]/", '', $the_date);
        $the_date = preg_replace("/\/{2,}/", '/', $the_date);
        $the_date = preg_replace("/^\/|\/$/", '', $the_date);

        $the_date = explode('/', $the_date);

        if (count($the_date) == 3 && checkdate($the_date[$map['m']], $the_date[$map['d']], $the_date[$map['y']])) {
            $the_date = OSC::core('date')->dateToTimestamp($the_date[$map['d']], $the_date[$map['m']], $the_date[$map['y']]);
        } else {
            $the_date = 0;
        }

        return $the_date;
    }

}
