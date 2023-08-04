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
 * OSECORE Controller
 *
 * @package Controller_User_Backend_Authentication
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Controller_User_Authentication extends Abstract_Frontend_Controller {

    public function actionSignIn() {
        if (OSC::helper('user/authentication')->getMember()->getId() > 0) {
            if ($this->_request->isAjax()) {
                $this->_ajaxResponse(array('url' => OSC::$base_url));
            }

            static::redirect(OSC::$base_url);
        }

        $email = $this->_request->get('email');

        $errors = '';

        if ($email !== null) {
            try {
                $password = $this->_request->getRaw('password');

                OSC::core('validate')->validEmail($email);

                if (!$password) {
                    throw new Exception($this->_('usr.err_password_empty'));
                }

                try {
                    $model = OSC::model('user/member')->loadByEmail($email);
                } catch (Exception $ex) {
                    if ($ex->getCode() == 404) {
                        throw new Exception($this->_('usr.auth_err_account_not_match'));
                    }

                    throw new Exception($this->_('core.err_exec_failed'));
                }

                try {
                    OSC::helper('user/member_common')->authenticate($model, $password);
                } catch (Exception $ex) {
                    throw new Exception($this->_('usr.auth_err_account_not_match'));
                }

                if (!$model->data['activated']) {
                    throw new Exception($this->_('usr.auth_err_account_unactivated'));
                }

                OSC::helper('user/authentication')->signIn($model->getId(), true);

                if ($this->_request->isAjax()) {
                    $this->_ajaxResponse(array('url' => OSC::$base_url));
                }

                static::redirect(OSC::$base_url);
            } catch (Exception $ex) {
                if ($this->_request->isAjax()) {
                    $this->_ajaxError($ex->getMessage());
                }

                $errors = $ex->getMessage();

                if (is_array($errors)) {
                    $errors = implode('<br /><br />', $errors);
                }
            }
        }

        $this->output($this->getTemplate()->build('user/authentication', array('errors' => $errors)));
    }

    public function actionSignOut() {
        OSC::helper('user/authentication')->signOut();
        
        static::redirect(OSC::$base_url);
    }

}
