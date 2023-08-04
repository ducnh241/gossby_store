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
 * OSC Backend Controller
 *
 * @package Controller_Backend_Index
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Controller_Backend_IgnoreHash extends Abstract_Backend_Controller {

    protected $_check_hash = false;

    protected $_enable_pre_hash = false;

    public function actionExtendHashTimeout() {
        $hash_key = $this->_request->get('hash');
        if (!$hash_key) {
            $this->_ajaxError();
        }

        $_hash = OSC::sessionGet('request_hash');
        if ($_hash['key'] !== $hash_key || $_hash['expire'] < time()) {
            $this->_ajaxError();
        }
        OSC::preHash($this->_check_hash, $this->_hash_failed_forward);

        $this->_ajaxResponse();
    }
}
