<?php
class Controller_Frontend_Authentication extends Abstract_Core_Controller {
    public function __construct(){
        parent::__construct();
    }

    public function actionLogin(){
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
                    throw new Exception('The password is empty');
                }

                try {
                    $model = OSC::model('user/member')->loadByEmail($email);
                } catch (Exception $ex) {
                    if ($ex->getCode() == 404) {
                        throw new Exception('Your email or password do not match our records');
                    }

                    throw new Exception($this->_('core.err_exec_failed'));
                }

                try {
                    OSC::helper('user/member_common')->authenticate($model, $password);
                } catch (Exception $ex) {
                    throw new Exception('Your email or password do not match our records');
                }


                if (!$model->data['activated']) {
                    throw new Exception('The account is not activated');
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

       $this->output(OSC::core('template')->build('user/authentication', array('errors' => $errors)));

    }


}
