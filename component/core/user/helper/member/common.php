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
 * OSC::User
 * 
 * @package Helper_User_Member_Common
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Helper_User_Member_Common {

    /**
     *
     * @param string $username
     * @param string $replacement
     * @return string
     */
    public function cleanUsername($username, $replacement = '') {
        return strtolower(preg_replace('/[^a-zA-Z0-9_]/i', $replacement, $username));
    }

    public function authenticate($model, $password) {
        $secret_code = '';

        if ($model->data['auth_secret_key'] != '') {
            $secret_code = substr($password, 0, 6);
            $password = substr($password, 6);

            $google_auth = new PHPGangsta_GoogleAuthenticator();

            if (!$google_auth->verifyCode($model->data['auth_secret_key'], $secret_code, 2)) {
                throw new OSC_Exception_Condition('Secret code is not match');
            }
        }

        if ($model->data['password_hash'] != md5($password)) {
            throw new OSC_Exception_Condition('Password is not match');
        }
    }
}
