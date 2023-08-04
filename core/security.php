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
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSECORE Core
 *
 * @package OSECORE_Core_Security
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Security {

    /**
     * Encode permission array
     *
     * @param  array $data
     * @return string
     */
    public function encodePermArray($data) {
        $encoded = array();

        foreach ($data as $k => $v) {
            $v = implode(',', array_unique($v));
            $encoded[] = "{$k}:{$v}";
        }

        $encoded = implode(';', $encoded);

        return $encoded;
    }

    /**
     * Decode permission array
     *
     * @param  string $data
     * @return array
     */
    public function decodePermArray($data) {
        $data = explode(';', $data);

        $decoded = array();

        foreach ($data as $k => $v) {
            $v = explode(':', $v);

            if ($v[0] == '' || $v[1] == '') {
                continue;
            }

            $decoded[$v[0]] = explode(',', $v[1]);
        }

        return $decoded;
    }

    /**
     * Check captcha code
     *
     * @param  boolean $autoBreak
     * @return boolean
     */
    public function checkCaptchaCode($autoBreak = true) {
        if (!$_SESSION['CAPTCHA_CODE']) {
            return false;
        }

        $CAPTCHA_CODE = $_SESSION['CAPTCHA_CODE'];

        unset($_SESSION['CAPTCHA_CODE']);

        $CAPTCHA_CODE_confirm = strtoupper(preg_replace('/[^A-Z]/', '', OSC::core('request')->get('captcha_code')));

        if (!$CAPTCHA_CODE_confirm || $CAPTCHA_CODE != $CAPTCHA_CODE_confirm) {
            if ($autoBreak) {
                OSC::core('output')->error('err_seccode_incorrect');
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

}
