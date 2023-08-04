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

abstract class Abstract_CrossSell_Controller_Api extends OSC_Controller {

    public function __construct() {
        parent::__construct();

        $header_all = $this->_request->headerGetAll();
        $api_token = $header_all['Osc-Service'];
        $signature= $header_all['Osc-Request-Signature'];

        if (!defined('CROSS_SELL_KEY')) {
            throw new Exception('CROSS_SELL_KEY key not found');
        }

        if (!defined('CROSS_SELL_SECRET')) {
            throw new Exception('CROSS_SELL_SECRET not found');
        }

        if ($api_token != CROSS_SELL_KEY) {
            $this->_ajaxError('OSC-Service is incorrect', 400);
        }

        $this->_request->reset();

        if ($this->_request->headerGet('Content-Type') == 'application/json') {
            $request_data = file_get_contents('php://input');

            $request_params = OSC::decode($request_data, true);
        } else {
            $request_data = $_SERVER['QUERY_STRING'];

            parse_str($request_data, $request_params);
        }

        if (!is_array($request_params) || count($request_params) < 1) {
            $this->_ajaxError('Request data is incorrect', 400);
        }

        $checksum = static::makeRequestChecksum($request_data, CROSS_SELL_SECRET);

        if ($signature != $checksum) {
            $this->_ajaxError('API Token is not match: ' . $checksum, 400);
        }

        foreach ($request_params as $key => $value) {
            $this->_request->set($key, $value);
        }
    }

}
