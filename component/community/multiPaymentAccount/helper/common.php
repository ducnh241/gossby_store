<?php

class Helper_MultiPaymentAccount_Common extends OSC_Object {
    
    protected $_account_provided = [];

    public function cleanAccountCache() {
        OSC::sessionRemove('multi_payment_account/stripe');
        OSC::sessionRemove('multi_payment_account/paypal');
        OSC::sessionRemove('multi_payment_account/paypalPro');
    }
    
    public function getAccountProvided() {
        return $this->_account_provided;
    }
    
    protected function _addAccountProvided($account_type, $account) {
        $this->_account_provided[] = ['type' => $account_type, 'info' => $account];
        return $this;
    }

    public function getAccount(string $account_type, $country_code = null) {
        static $cached = [];

        if (!$country_code) {
            $country_code = 'default';
        }

        if (!isset($cached[$country_code])) {
            $cached[$country_code] = OSC::helper('multiPaymentAccount/account')->getAccounts($country_code == 'default' ? null : $country_code, Helper_Catalog_Checkout::getFootprintKey());
        }

        if (!in_array($account_type, ['paypal', 'stripe', 'paypalPro'], true)) {
            throw new Exception('Payment method [' . $account_type . '] is not exists');
        }

        $account = OSC::sessionGet('multi_payment_account/' . $account_type);

        if ($account) {
            $this->_addAccountProvided($account_type, $account);
            return $account;
        }

        $default_account = OSC::helper('core/setting')->get('multipaymentaccount/default/' . $account_type);

        try {
            if (md5(OSC::encode($default_account)) != md5(OSC::encode($cached[$country_code][$account_type]['default']))) {
                OSC::helper('core/setting')->set('multipaymentaccount/default/' . $account_type, $cached[$country_code][$account_type]['default']);
            }

            $account = $cached[$country_code][$account_type]['normal'] ? $cached[$country_code][$account_type]['normal'] : $cached[$country_code][$account_type]['default'];
        } catch (Exception $ex) {
            if (!$default_account) {
                $account = null;

                //If Does not exist any account, set setting for this account type to null
                OSC::helper('core/setting')->set('multipaymentaccount/default/' . $account_type, null);
                OSC::sessionRemove('multi_payment_account/' . $account_type);
            } else {
                $account = $default_account;
            }
        }

        if ($account) {
            OSC::sessionSet('multi_payment_account/' . $account_type, $account);
        }

        $this->_addAccountProvided($account_type, $account);

        return $account;
    }

    public function markAccountError($account, $error_message = '') {
        try {
            OSC::helper('multiPaymentAccount/account')->markAccountError($account['log_id'], $error_message);
        } catch (Exception $ex) { }

        OSC::sessionRemove('multi_payment_account/' . $account['type']);
    }

    /**
     * This function response all country has taxes,
     * These countries will be used Paypal Pro gateway
     *
     * @return array
     * @throws OSC_Exception_Runtime
     */
    public function getCountryHasTax() {
        $locations_has_tax = [];
        $collection = OSC::model('catalog/tax')->getCollection()->load();

        foreach ($collection as $tax) {
            $locations_has_tax[] = $tax->data['destination_location_data'];
        }

        return OSC::helper('core/country')->getCountryCodeByLocation($locations_has_tax);
    }

    /**
     * When a default account has disabled or deleted from Master, an API will call to Store to delete this account in Setting DB
     * @param $accountType
     */
    public function changeDefaultAccount($accountType) {
        OSC::helper('core/setting')->set('multipaymentaccount/default/' . $accountType['account_type'], null);
        OSC::sessionRemove('multi_payment_account/' . $accountType['account_type']);

        OSC::helper('core/setting')->removeCache();
    }
}