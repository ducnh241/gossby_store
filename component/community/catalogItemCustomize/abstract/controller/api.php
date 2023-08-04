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

abstract class Abstract_CatalogItemCustomize_Controller_Api extends OSC_Controller {
    
    public function __construct() {
        parent::__construct();

        $api_token = $this->_request->headerGet('Osc-Api-Token');
        
        if (! $api_token) {
            $this->_ajaxError('API Token is empty');
        }
        
        try {
            $store_info = OSC::getStoreInfo();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $request_data = [];

        $this->_request->reset();

        if ($this->_request->headerGet('Content-Type') == 'application/json') {
            $request_data = file_get_contents('php://input');

            $request_params = OSC::decode($request_data, true);
        } else {
            $request_data = $_SERVER['QUERY_STRING'];

            parse_str($request_data, $request_params);
        }

        if (!is_array($request_params) || count($request_params) < 1) {
            $this->_ajaxError('Request data is incorrect');
        }
        
        $checksum = static::makeRequestChecksum($request_data, $store_info['secret_key']);

        if ($api_token != $checksum) {
            $this->_ajaxError('API Token is not match');
        }

        foreach ($request_params as $key => $value) {
            $this->_request->set($key, $value);
        }
    }

}
