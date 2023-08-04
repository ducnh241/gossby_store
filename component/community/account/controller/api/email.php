<?php

class Controller_Account_Api_Email extends Abstract_Core_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function actionSend() {
        try {
            $type = intval($this->_request->get('type'));

            $data = $this->_request->get('data', []);

            if (count($data) < 1) {
                throw new Exception('Data is incorrect');
            }

            $receiver_email = $data['email'];

            $email_arr = explode('@', $receiver_email, 2);

            $data['name'] = isset($data['name']) && $data['name'] != '' ? $data['name'] : $email_arr[0];

            $shop = OSC::getShop();

            $shop_name = ucwords($shop->data['shop_name']);

            $is_send_by_klaviyo = false;

            switch ($type) {
                case Helper_Account_Common::EMAIL_CREATED_ACCOUNT :
                    if (!isset($data['name']) || !isset($data['email']) || !isset($data['token'])) {
                        throw new Exception('Data is incorrect');
                    }
                    $title = 'Verify your '.$shop_name.' account';
                    $template = 'catalog/email_template/html/account/verify_account';

                    try {
                        $klaviyo_api_key = OSC::helper('klaviyo/common')->getApiKey();
                        if ($klaviyo_api_key) {
                            OSC::helper('klaviyo/common')->create([
                                'token' => $klaviyo_api_key,
                                'event' => 'Verify Account CRM By Order',
                                'customer_properties' => [
                                    '$email' => $data['email']
                                ],
                                'properties' => [
                                    'receiver_email' => $data['email'],
                                    'receiver_name' => $data['name'],
                                    'title' => $title,
                                    'token' => $data['token'],
                                    'url_verify' => OSC_FRONTEND_BASE_URL . '/account/created-account' . '?token=' . $data['token'],
                                    'email_cs' => OSC::helper('core/setting')->get('theme/contact/customer_service_email')
                                ]
                            ]);
                            $is_send_by_klaviyo = true;
                        }
                    } catch (Exception $ex) {}
                    break;
                case Helper_Account_Common::EMAIL_ACTIVE_ACCOUNT :
                    if (!isset($data['name']) || !isset($data['email']) || !isset($data['token'])) {
                        throw new Exception('Data active account incorrect');
                    }
                    $title = 'Verify your '.$shop_name.' account';
                    $template = 'catalog/email_template/html/account/active_account';
                    break;
                case Helper_Account_Common::EMAIL_RESET_PASSWORD :
                    if (!isset($data['name']) || !isset($data['email']) || !isset($data['token'])) {
                        throw new Exception('Data reset password incorrect');
                    }
                    $title = 'Reset your '. $shop_name .' password';
                    $template = 'catalog/email_template/html/account/reset_password';
                    break;
                case Helper_Account_Common::EMAIL_CHANGE_EMAIL :
                    if (!isset($data['name']) || !isset($data['email']) || !isset($data['token'])) {
                        throw new Exception('Data change email incorrect');
                    }
                    $title = 'Confirm your new email address';
                    $template = 'catalog/email_template/html/account/change_email';
                    break;
                case Helper_Account_Common::EMAIL_CHANGED_EMAIL :
                    if (!isset($data['name']) || !isset($data['new_email']) || !isset($data['old_email'])) {
                        throw new Exception('Data changed email incorrect');
                    }
                    $receiver_email = $data['new_email'];
                    $title = 'Account Notice: Email changed';
                    $template = 'catalog/email_template/html/account/change_email_confirmed';
                    OSC::helper('postOffice/email')->create([
                        'priority' => 1000,
                        'subject' => $title,
                        'receiver_email' => $data['old_email'],
                        'receiver_name' => $data['name'],
                        'html_content' => OSC::core('template')->build(
                            'catalog/email_template/html/account/main', [
                                'template' => $template,
                                'title' => $title,
                                'shop_name' => $shop_name,
                                'receiver_name' => $data['name'],
                                'email' => $receiver_email,
                                'old_email' => $data['old_email'],
                                'token' => $data['token'],
                                'is_marketing_email' => false,
                                'big_logo' => false
                            ]
                        )
                    ]);
                    break;
                case Helper_Account_Common::EMAIL_CHANGED_PASSWORD :
                    if (!isset($data['name']) || !isset($data['email'])) {
                        throw new Exception('Data changed password incorrect');
                    }
                    $title = 'Account Notice: Password updated';
                    $template = 'catalog/email_template/html/account/change_password';
                    break;
                default:
                    throw new Exception('Action type is incorrect');
            }

            if (!$is_send_by_klaviyo) {
                OSC::helper('postOffice/email')->create([
                    'priority' => 1000,
                    'subject' => $title,
                    'receiver_email' => $receiver_email,
                    'receiver_name' => $data['name'],
                    'html_content' => OSC::core('template')->build(
                        'catalog/email_template/html/account/main', [
                            'template' => $template,
                            'title' => $title,
                            'shop_name' => $shop_name,
                            'receiver_name' => $data['name'],
                            'email' => $receiver_email,
                            'old_email' => $data['old_email'],
                            'token' => $data['token'],
                            'is_marketing_email' => false,
                            'big_logo' => false
                        ]
                    )
                ]);
                try {
                    $path_file_mailer = OSC_SITE_PATH . (OSC_ENV == 'production' ? '/../../code' : '')  . '/.mailer.py';
                    exec('python3 ' . $path_file_mailer . ' --folder '.OSC_SITE_PATH. ' --email' . $receiver_email);
                } catch (Exception $ex) {}
            }
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
        $this->_ajaxResponse([]);
    }
}