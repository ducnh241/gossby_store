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
 * @copyright    Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class OSC_Controller_Alias
{

    /**
     *
     * @var OSC_Request
     */
    protected $_request = null;

    public function __construct()
    {
        $this->_request = OSC::core('request');
        OSC::core('language')->load('core/common');
    }

    public function process(&$request_string)
    {

        OSC::core('language')->processLanguage($request_string);

        OSC::core('observer')->dispatchEvent('parse_redirect_url', $request_string, false);

        $parsed_request_string = OSC::core('observer')->dispatchEvent('parse_shorten_url', $request_string, false);

        if ($parsed_request_string) {
            $request_string = $parsed_request_string;
            return;
        }

        if (!preg_match('/[^\w-]/', $request_string)) {

            try {
                OSC::register('REWRITE-URL', $request_string);
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    OSC::core('debug')->triggerError($ex->getMessage(), $ex->getCode());
                }

                $request_string = '';
            }

        }
    }

}
