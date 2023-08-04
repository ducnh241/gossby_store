<?php

class Helper_MultiPaymentAccount_Account extends OSC_Object {

    /**
     * New follow, support get all accounts from group
     * Return default account and normal account for all payment methods
     */
    public function getAccounts($countryCode, $footprint_key)
    {
        try {
            OSC::core('debug')->setLogPrefix('mpaymentGetAccounts_'.$footprint_key);
            OSC::core('debug')->setSlowProcessTimeTrigger(2);

            if (!$footprint_key) {
                throw new Exception('Footprint key is empty');
            }

            $accounts = [];
            $accountTypes = ['paypal', 'stripe']; // Always return PayPal and Stripe account

            try {
                if ($countryCode) {
                    $paymentAccount = $this->getPaymentAccounts($countryCode);
                    $accountTypes = array_unique(array_merge($accountTypes, array_keys($paymentAccount)));
                } else {
                    $paymentAccount = [];
                }

                $defaultAccountCollection = OSC::model('multiPaymentAccount/account')
                    ->getCollection()
                    ->setCondition(['condition' => " account_type IN('" . implode("','", $accountTypes) . "') AND default_flag = 1"])
                    ->load();

                if ($defaultAccountCollection->length() > 0) {
                    foreach ($defaultAccountCollection as $defaultAccount) {
                        $accounts[$defaultAccount->data['account_type']]['default'] = $this->getAccountData($defaultAccount);
                    }
                }

                if (count($paymentAccount) > 0) {
                    /* @var $DB OSC_Database */
                    $DB = OSC::core('database')->getAdapter('db_master');

                    /**
                     * Render list accounts for each payment method
                     */
                    foreach ($paymentAccount as $type => $account) {
                        if ($account) {
                            $DB->insert('payment_account_log', [
                                'shop_id' => OSC::getShop()->getId(),
                                'order_id' => 0,
                                'amount' => 0,
                                'error_message' => null,
                                'footprint_key' => $footprint_key,
                                'account_id' => $account['record_id'],
                                'flag' => 0,
                                'date_key' => date('Ymd'),
                                'added_timestamp' => time(),
                                'modified_timestamp' => time()
                            ], 'insert_log');

                            $log_id = $DB->getInsertedId();

                            $accounts[$type]['normal'] = [
                                'id' => $account['record_id'],
                                'log_id' => $log_id,
                                'type' => $type,
                                'title' => $account['title'],
                                'account_info' => $account['account_info']
                            ];
                        }
                    }
                }

            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            /**
             * Country code not valid or don't have any group and account support this country code
             * And don't have any account as default flag
             */
            if (count($accounts) < 1) {
                OSC::helper('core/telegram')->sendMessage(OSC::$base_url . ": ERROR_GET_PAYMENT_ACCOUNT: No Account was found to return for country code: " . $countryCode . " - Footprint Key: " . $footprint_key, OSC::helper('core/setting')->get('error_payment_notifications/telegram_group_id'));
                throw new Exception('No payment account was found to return');
            }
        } catch (Exception $ex) {

        }

        return $accounts;
    }

    public function markAccountError($log_id, $error_message) {
        try {

            if ($log_id < 1) {
                throw new Exception('Log ID is empty');
            }

            /* @var $DB OSC_Database */
            $DB = OSC::core('database')->getAdapter('db_master');

            try {
                $DB->update('payment_account_log', ['flag' => -1, 'error_message' => $error_message], 'record_id=' . $log_id, 1);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            $DB->query("SELECT flag, footprint_key, account_id FROM " . OSC::systemRegistry('db_prefix') . "payment_account_log WHERE flag != 0 AND account_id = (SELECT account_id FROM " . OSC::systemRegistry('db_prefix') . "payment_account_log WHERE record_id={$log_id}) ORDER BY record_id DESC LIMIT 100");

            if ($DB->rowCount() > 0) {
                $account_id = null;

                $counter = 0;

                $unique_footprint_keys = [];

                while ($row = $DB->fetchArray()) {
                    if ($row['flag'] == 1) {
                        break;
                    }

                    if (in_array($row['footprint_key'], $unique_footprint_keys, true)) {
                        continue;
                    }

                    $unique_footprint_keys[] = $row['footprint_key'];

                    if (++$counter >= 10) {
                        $account_id = $row['account_id'];
                        break;
                    }
                }

                if ($account_id) {
                    //Notify admin when payment account auto disabled
                    $paymentAccount = OSC::model('multiPaymentAccount/account')->load($account_id);

                    $disabledSubject = 'Payment account has many error and need to check - ' . date('Y-m-d H:i:s');
                    $disabledContent = "Account name: " . $paymentAccount->data['title'] . "\n";
                    $disabledContent .= "Time: " . date('Y-m-d H:i:s') . "\n";
                    $disabledContent .= "This Payment account has many errors. Please check the account because auto disable by system was turned off.\n";

                    $this->sendMailError($disabledSubject, $disabledContent);
                }
            }
        } catch (Exception $ex) {

        }
    }

    public function sendMailError($subject,$content) {
        return;
        // Todo: get setting backend
        $receive = OSC::helper('core/setting')->get('general/mailer/payment_gateway');
        if(!$receive) {
            $receive = OSC::helper('core/setting')->get('general/mailer/address');
        }
        OSC::helper('core/mailer')->send($receive, $subject, $content);
    }

    public function getGroupsByStore($storeId) {
        $model = OSC::model('multiPaymentAccount/group');
        $collections = $model->getCollection()->setCondition("store_ids LIKE '%,{$storeId},%'")->load();

        $groups = [];

        foreach ($collections as $item) {
            $paymentAccounts = explode(',', substr($item->data['payment_account_ids'],1,-1));

            $groups[] = [
                'group_id' => $item->getId(),
                'group_name' => $item->data['group_name'],
                'payment_account_ids' => $paymentAccounts
            ];
        }

        return $groups;
    }

    public function getPaymentAccounts($countryCode)
    {
        $result = [];
        /**
         * Get all Groups with activated_flag = 1
         */
        $groupCollection = OSC::model('multiPaymentAccount/groups')
            ->getCollection()
            ->addCondition('activated_flag', 1, OSC_Database::OPERATOR_EQUAL)
            ->addCondition('group_id', Model_MultiPaymentAccount_Groups::ID_GROUP_PAYMENT_STORE_DE, OSC_Database::OPERATOR_NOT_EQUAL)
            ->load()
            ->toArray();

        if (count($groupCollection) <= 0) {
            return [];
        }

        /**
         * Filter groups support for this $countryCode
         */
        $groups = [];
        foreach ($groupCollection as $group) {
            if ($group['location_data'] == '*') {
                $groups[] = $group;
            } else {
                $countryCodes = OSC::helper('core/country')->getCountryCodeByLocation([$group['location_data']]);
                if (in_array($countryCode, $countryCodes)) {
                    $groups[] = $group;
                }
            }
        }

        if (count($groups) <= 0) {
            return [];
        }

        foreach ($groups as $group) {
            $result[] = $this->getGroupAccount($group);
        }

        /**
         * Return random one item of result list
         */
        return $result[array_rand($result)];
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

    public static function collectSyncDefaultAccount($params) {
        $params['collected_data']['account_type'] = $params['sync_data']['account_type'];
    }

    public function changeDefaultAccount($paramsSync) {
        $key = 'syncMultiPaymentDefaultAccount';

        $shopIds = OSC::model('shop/shop')
            ->getCollection()
            ->addField('shop_id')
            ->load()
            ->toArray();

        $shopIds = array_column($shopIds, 'shop_id');
        foreach ($shopIds as $shopId) {
            try {
                OSC::helper('masterSync/common')->addQueue(
                    $shopId,
                    $key,
                    $paramsSync,
                    [
                        'overwrite',
                        'ukey' => $key . ':' . OSC::makeUniqid(),
                        'running_time' => 5,
                        'priority' => Helper_MasterSync_Common::PRIORITY_HIGHEST
                    ]
                );
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
        }
    }

    /**
     * @param array $groupData
     * @param array $accountData
     * @return array $accounts
     */
    public function getGroupAccount($groupData)
    {

        $allAccountIds = [];
        $accountData = [];

        /** Get all account ID in this group */
        foreach ($groupData['meta_data'] as $type => $value) {
            foreach ($value['normal_account'] as $accountId) {
                if (!in_array($accountId, $allAccountIds)) {
                    $allAccountIds[] = $accountId;
                }
            }

            foreach ($value['default_account'] as $accountId) {
                if (!in_array($accountId, $allAccountIds)) {
                    $allAccountIds[] = $accountId;
                }
            }
            $accountData[$type] = [];
        }

        $date_key = date('Ymd');
        $collection = OSC::model('multiPaymentAccount/account')
            ->getCollection()
            ->setCondition(['condition' => " record_id IN(" . implode(',', $allAccountIds) . ")  AND activated_flag = 1 AND (daily_max_transaction = 0 OR daily_max_transaction > IF(date_key = {$date_key}, today_transaction, 0)) AND (daily_max_amount = 0 OR daily_max_amount > IF(date_key = {$date_key}, today_amount, 0)) "])
            ->load()
            ->toArray();
        $activeAccounts = [];
        if (count($collection) > 0) {
            foreach ($collection as $account) {
                $accountData[$account['account_type']][$account['record_id']] = $account;
                $activeAccounts[] = $account['record_id'];
            }
        }

        $accounts = [];
        foreach ($groupData['meta_data'] as $type => $value) {
            $accountType = [];
            foreach ($value['normal_account'] as $accountId) {
                if (in_array($accountId, $activeAccounts)) {
                    $accountType[$type][] = $accountId;
                }
            }

            if (count($accountType[$type]) < 1) {
                /** If all account in this [type] has disabled, use default account in this [type] (Default account always live) */
                if(count($value['default_account']) > 0) {
                    $accounts[$type] = $accountData[$type][$value['default_account'][0]];
                }
            } else {
                /** Random an account in Normal account list */
                if(count($accountData[$type]) > 0) {
                    $accounts[$type] = $accountData[$type][array_rand($accountData[$type])];
                } else {
                    /** All normal account out of Transaction or Amount -> return default Acc */
                    if(count($value['default_account']) > 0) {
                        $accounts[$type] = $accountData[$type][$value['default_account'][0]];
                    }
                }
            }
        }

        return $accounts;
    }

    /**
     * @param Model_MultiPaymentAccount_Account $payment_account
     * @return array
     */
    public function getAccountData(Model_MultiPaymentAccount_Account $payment_account) {
        return [
            'id' => $payment_account->getId(),
            'log_id' => 0,
            'type' => $payment_account->data['account_type'],
            'title' => $payment_account->data['title'],
            'account_info' => $payment_account->data['account_info']
        ];
    }
}
