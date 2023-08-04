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
class Controller_Firebase_Common extends Abstract_Frontend_Controller {

    public function actionTest() {
        try {
            $response = OSC::helper('firebase/common')->memberSendMessage(1, 'Test notification', 'Current timestamp: ' . time(), OSC::$base_url, array('verify_code' => 1));
            var_dump($response);
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function actionVerify() {
        $this->_ajaxResponse($this->getAccount()->getId() == $this->_request->get('verify_code'));
    }

    public function actionRegister() {
        if ($this->getAccount()->getId() < 1) {
            $this->_ajaxError($this->_('core.err_no_permission'));
        }

        $token = $this->_request->get('token');

        if (!$token) {
            $this->_ajaxError($this->_('core.err_data_incorrect'));
        }

        $model = OSC::model('firebase/token');

        try {
            $model->loadByUkey($token);
        } catch (Exception $ex) {
            if ($ex->getCode() !== 404) {
                $this->_ajaxError($ex->getMessage());
            }

            $model->setData(array(
                'token' => $token,
                'member_id' => $this->getAccount()->getId()
            ));
        }

        $model->setData('updated_timestamp', time());

        try {
            $model->save();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse();
    }

    public function actionUnregister() {
        $token = $this->_request->get('token');

        if (!$token) {
            $this->_ajaxError($this->_('core.err_data_incorrect'));
        }

        $model = OSC::model('firebase/token');

        try {
            $model->loadByUkey($token)->delete();
        } catch (Exception $ex) {
            if ($ex->getCode() !== 404) {
                $this->_ajaxError($ex->getMessage());
            }
        }

        $this->_ajaxResponse();
    }

}
