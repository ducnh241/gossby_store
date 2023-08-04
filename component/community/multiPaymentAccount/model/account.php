<?php

class Model_MultiPaymentAccount_Account extends Abstract_Core_Model {

    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';
    protected $_table_name = 'payment_account';
    protected $_pk_field = 'record_id';

    const ACCOUNT_TYPE = [
        'paypal' => 'PayPal',
        'stripe' => 'Stripe',
        'paypalPro' => 'Paypal Pro'
    ];

    public function getTypeTitle() {
        return static::ACCOUNT_TYPE[$this->data['account_type']];
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['account_info'])) {
            $data['account_info'] = OSC::encode($data['account_info']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['account_info'])) {
            $data['account_info'] = OSC::decode($data['account_info']);
        }
    }

    protected function _accountInfoValidator_paypal(&$account_info) {
        if (!is_array($account_info) || !isset($account_info['client_id']) || !isset($account_info['client_secret'])) {
            throw new Exception('Account info :: data format is incorrect');
        }

        $account_info['client_id'] = trim($account_info['client_id']);
        $account_info['client_secret'] = trim($account_info['client_secret']);
        $account_info['fraudnet_value'] = trim($account_info['fraudnet_value']);

        if (!$account_info['client_id']) {
            throw new Exception('Account info :: client ID is empty');
        }

        if (!$account_info['client_secret']) {
            throw new Exception('Account info :: client secret is empty');
        }

        $account_info = [
            'is_pro' => $account_info['is_pro'],
            'client_id' => $account_info['client_id'],
            'client_secret' => $account_info['client_secret'],
            'fraudnet_value' => $account_info['fraudnet_value']
        ];
    }

    protected function _accountInfoValidator_paypalPro(&$account_info) {
        if (!is_array($account_info) || !isset($account_info['user']) || !isset($account_info['vendor']) || !isset($account_info['partner']) || !isset($account_info['password'])) {
            throw new Exception('Account info :: data format is incorrect');
        }

        $account_info['user'] = trim($account_info['user']);
        $account_info['vendor'] = trim($account_info['vendor']);
        $account_info['partner'] = trim($account_info['partner']);
        $account_info['password'] = trim($account_info['password']);

        if (!$account_info['user']) {
            throw new Exception('Account info :: User is empty');
        }

        if (!$account_info['vendor']) {
            throw new Exception('Account info :: Vendor is empty');
        }

        if (!$account_info['partner']) {
            throw new Exception('Account info :: Partner is empty');
        }

        if (!$account_info['password']) {
            throw new Exception('Account info :: Password is empty');
        }

        $account_info = [
            'user' => $account_info['user'],
            'vendor' => $account_info['vendor'],
            'partner' => $account_info['partner'],
            'password' => $account_info['password']
        ];
    }

    protected function _accountInfoValidator_stripe(&$account_info) {
        if (!is_array($account_info) || !isset($account_info['public_key']) || !isset($account_info['secret_key']) || !isset($account_info['webhooks_secret_key'])) {
            throw new Exception('Account info :: data format is incorrect');
        }

        $account_info['public_key'] = trim($account_info['public_key']);
        $account_info['secret_key'] = trim($account_info['secret_key']);
        $account_info['webhooks_secret_key'] = trim($account_info['webhooks_secret_key']);

        if (!$account_info['public_key']) {
            throw new Exception('Account info :: public key is empty');
        }

        if (!$account_info['secret_key']) {
            throw new Exception('Account info :: secret key is empty');
        }

        $account_info = [
            'is_pro' => $account_info['is_pro'], // Stripe pro account use for all country has taxes and customer not user VISA and MASTER CARD
            'public_key' => $account_info['public_key'],
            'secret_key' => $account_info['secret_key'],
            'webhooks_secret_key' => $account_info['webhooks_secret_key']
        ];
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        $account_type = null;

        if (isset($data['account_type'])) {
            if (!isset(static::ACCOUNT_TYPE[$data['account_type']])) {
                $errors[] = 'Account type is not exists';
            } else {
                $account_type = $data['account_type'];
            }
        }

        if (isset($data['account_info'])) {
            if (!$account_type) {
                $account_type = $this->getData('account_type', true);
            }

            if (!isset(static::ACCOUNT_TYPE[$account_type])) {
                $errors[] = 'Account type is not exists';
            } else if (!method_exists($this, '_accountInfoValidator_' . $account_type)) {
                $errors[] = 'Account validator is not exists';
            } else {
                try {
                    $validator = '_accountInfoValidator_' . $account_type;
                    $this->$validator($data['account_info']);
                } catch (Exception $ex) {
                    $errors[] = $ex->getMessage();
                }
            }
        }

        if (isset($data['title'])) {
            $data['title'] = trim($data['title']);

            if (!$data['title']) {
                $errors[] = 'Account title is empty';
            }
        }

        if (isset($data['daily_max_transaction'])) {
            $data['daily_max_transaction'] = intval($data['daily_max_transaction']);

            if ($data['daily_max_transaction'] < 0) {
                $data['daily_max_transaction'] = 0;
            }
        }

        if (isset($data['daily_max_amount'])) {
            $data['daily_max_amount'] = round(floatval($data['daily_max_amount']), 2);

            if ($data['daily_max_amount'] < 0) {
                $data['daily_max_amount'] = 0;
            }
        }

        if (isset($data['priority'])) {
            $data['priority'] = intval($data['priority']);
        }

        if (isset($data['member_id'])) {
            $data['member_id'] = intval($data['member_id']);

            if ($data['member_id'] < 1) {
                $errors[] = 'Member ID is incorrect';
            }
        }

        foreach (['default_flag', 'activated_flag'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]) == 1 ? 1 : 0;
            }
        }

        foreach (['added_timestamp', 'modified_timestamp'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        /**
         * Disabled edit country of PayPal Pro
         */
        if($account_type == 'paypalPro') {
            $data['country_codes'] = OSC::helper('multiPaymentAccount/common')->getCountryHasTax();
        }

        if(is_array($data['country_codes']) && count($data['country_codes']) > 0) {
            $data['country_codes'] = ','.implode(",",$data['country_codes']).',';
        } else {
            $data['country_codes'] = null;
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'member_id' => 'Member ID is empty',
                    'title' => 'Account title is empty',
                    'account_type' => 'Account type is empty',
                    'account_info' => 'Account info is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'date_key' => 0,
                    'total_transaction' => 0,
                    'total_amount' => 0,
                    'today_transaction' => 0,
                    'today_amount' => 0,
                    'daily_max_transaction' => 0,
                    'daily_max_amount' => 0,
                    'priority' => 0,
                    'activated_flag' => 1,
                    'default_flag' => 0,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            } else {
                unset($data['account_type']);
                unset($data['total_transaction']);
                unset($data['total_amount']);
                unset($data['today_transaction']);
                unset($data['today_amount']);

                if (!isset($data['modified_timestamp'])) {
                    $data['modified_timestamp'] = time();
                }
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

}
