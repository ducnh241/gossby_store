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
class Controller_User_Backend_Authentication extends Abstract_Backend_Controller {

    protected $_check_hash = false;
    protected $_check_perm = false;

    public function actionIndex() {
        Abstract_Backend_Controller::removeBackendTicket();

        if ($this->_request->get('act') == 'process') {
            $this->_processSignIn();
        }

        $this->_form();
    }

    protected function _error($error_message) {
        if ($this->_request->isAjax()) {
            $this->_ajaxError($error_message);
        }

        $this->_form($error_message);
    }

    protected function _processSignIn() {
        $email = $this->_request->get('email');
        $password = $this->_request->decodeValue($this->_request->get('password'));

        if (!$email) {
            $this->_error(OSC::core('language')->get('usr.err_email_empty'));
        }

        try {
            OSC::core('validate')->validEmail($email);
        } catch (Exception $ex) {
            $this->_error($ex->getMessage());
        }

        if (!$password) {
            $this->_error(OSC::core('language')->get('usr.err_password_empty'));
        }

        try {
            $model = OSC::model('user/member')->loadByEmail($email);
        } catch (Exception $ex) {
            if ($ex->getCode() == 404) {
                $this->_error(OSC::core('language')->get('usr.auth_err_account_not_match'));
            }

            $this->_error(OSC::core('language')->get('core.err_exec_failed'));
        }

        try {
            OSC::helper('user/member_common')->authenticate($model, $password);
        } catch (Exception $ex) {
            $this->_error(OSC::core('language')->get('usr.auth_err_account_not_match'));
        }

        if (!$model->data['activated']) {
            $this->_error(OSC::core('language')->get('usr.auth_err_account_unactivated'));
        }
        /*
          if ($m->getSuspended()) {
          $this->_output->error(str_replace('%d', SNSCore::core('date')->parse($m->getSuspendExpireTimestamp()), $lang['err_account_suspended']));
          } */

        OSC::helper('user/authentication')->signIn($model->getId(), true);

        Abstract_Backend_Controller::setBackendTicket();

        $redirect_url = $this->getUrl('backend/index/index');

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse($redirect_url);
        }

        static::redirect($redirect_url);
    }

    protected function _form($error = null) {
        $lang = OSC::core('language')->get();

        if (!$error) {
            $error = $this->_request->get('err');

            if ($error) {
                $error = OSC::core('language')->get($error);
            } else {
                $error = '';
            }
        }

        $tpl = $this->getTemplate();

        $tpl->addBreadcrumb(array('unlock-alt', $lang['usr.sign_in']));

        OSC::helper('user/authentication')->signOut();

        $buffer = $tpl->build('user/authentication', array('error' => $error, 'email' => $this->_request->get('email'), 'remember' => $this->_request->get('remember') ? 1 : 0));

        $this->output($buffer, false);
    }

    public function actionChangePassword() {
        $this->getTemplate()->setPageTitle('Account management');
        $this->getTemplate()->addBreadcrumb(['file-text', 'Change your password']);

        /* @var $model Model_User_Member */
        $model = $this->getAccount();

        $group_collection = OSC::model('user/group')->getCollection()->addCondition('group_id', OSC::systemRegistry('root_group')['guest'], OSC_Database::NEGATION_MARK . OSC_Database::OPERATOR_EQUAL);

        if (!$this->getAccount()->isRoot()) {
            $group_collection->addCondition('group_id', OSC::systemRegistry('root_group')['admin'], OSC_Database::NEGATION_MARK . OSC_Database::OPERATOR_EQUAL);
        }

        $group_collection->load();

        if ($this->_request->get('new_password')) {
            try {
                $data = array();
                $current_password = $this->_request->get('current_password');
                $new_password = $this->_request->get('new_password');
                $confirmation_new_password = $this->_request->get('confirmation_new_password');

                if ($new_password != $confirmation_new_password) {
                    throw new Exception('Your confirmation password does not match the new password');
                }

                /* @var $DB OSC_Database_Adapter */
                $DB = OSC::core('database')->getWriteAdapter();

                $condition = $DB->getCondition(true)
                    ->addCondition('member_id', $model->getId(), OSC_Database::OPERATOR_EQUAL)
                    ->addCondition('password_hash', md5($current_password), OSC_Database::OPERATOR_EQUAL);
                $result = $DB->count('*', $model->getTableName(), $condition, 'check_member_existed');
                $DB->free('check_member_existed');

                if (intval($result) === 0) {
                    throw new Exception('The current password you have entered is incorrect');
                }

                if(isset($new_password)) {
                    $data['password_hash'] = 1;
                    $model->register('RAW_PASSWORD', $new_password);
                }

                $model->setData($data)->save();

                $message = 'Your password has been updated successfully.';
                $this->addMessage($message);

                static::redirect($this->getUrl('user/backend_authentication/index'));
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $output_html = $this->getTemplate()->build('user/member/change_password', array(
            'form_title' => 'Update account #' . $model->getId() . ': ' . $model->getData('username', true),
            'model' => $model,
        ));

        $this->output($output_html);
    }

    public function actionShowForgetPasswordForm() {
        $this->output(
            $this->getTemplate()->build('user/authentication/forgetPassword'),
            false
        );
    }

    protected function _validateForgetPasswordForm($email) {
        if (!$email) {
            $this->_error(OSC::core('language')->get('usr.err_email_empty'));
        }

        try {
            // validate email
            OSC::core('validate')->validEmail($email);
        } catch (Exception $exception) {
            $this->_error($exception->getMessage());
        }
    }

    protected function _updateNewPasswordForMember(Model_User_Member $member, $new_password) {
        $member->register('RAW_PASSWORD', $new_password);
        $member->setData([
            'password_hash' => 1,
        ])->save();
    }

    protected function _sendNewPasswordEmail(array $user_data, $new_password) {
        $site_name = OSC::core('language')->get('usr.reset_password_email_subject');

        OSC::helper('postOffice/email')->create([
            'priority' => 1000,
            'subject' => $site_name,
            'receiver_email' => $user_data['email'],
            'receiver_name' => $user_data['username'],
            'html_content' => OSC::core('template')->build(
                'user/email_template/forgetPassword',
                [
                    'username' => $user_data['username'],
                    'site_name' => $site_name,
                    'new_password' => $new_password,
                ]
            ),
        ]);
    }

    public function actionSendForgetPasswordEmail() {
        $member = null;
        $email = $this->_request->get('email');

        // validate forget password form
        $this->_validateForgetPasswordForm($email);

        try {
            // get member
            $member = OSC::model('user/member')->loadByEmail($email);
        } catch (Exception $exception) {
            if ($exception->getCode() === 404) {
                $this->_error(OSC::core('language')->get('usr.auth_err_email_not_match'));
            }

            $this->_error(OSC::core('language')->get('core.err_exec_failed'));
        }

        // generate new password
        $new_password = OSC::randKey();

        try {
            // update new password for member
            $this->_updateNewPasswordForMember($member, $new_password);

            // send reset password email to member
            $this->_sendNewPasswordEmail(
                [
                    'username' => $member->getData('username'),
                    'email' => $member->getData('email'),
                ],
                $new_password
            );
        } catch (Exception $exception) {
            $this->_error($exception->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse([
                'message' => OSC::core('language')->get('usr.auth_send_reset_password_email_success'),
            ]);
        }

        static::redirect($this->getUrl('user/backend_authentication/index'));
    }
}
