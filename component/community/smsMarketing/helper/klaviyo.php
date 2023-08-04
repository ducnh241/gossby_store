<?php

class Helper_SMSMarketing_Klaviyo extends OSC_Object
{
    public function createQueue($cart, $phone_prefix_countries = [])
    {
        $enable_klaviyo_sms = OSC::helper('core/setting')->get('marketing/klaviyo/sms/enable');
        $klaviyo_sms_list_id = OSC::helper('core/setting')->get('marketing/klaviyo/sms/list_id');
        $klaviyo_sms_api_key = OSC::helper('core/setting')->get('marketing/klaviyo/sms/api_key');

        if (!$cart instanceof Model_Catalog_Cart || !$enable_klaviyo_sms || !$klaviyo_sms_list_id || !$klaviyo_sms_api_key) {
            return;
        }

        $email = $cart->data['email'];
        if ($cart->data['billing_phone']) {
            $full_name_segments = explode(' ', $cart->data['billing_full_name'], 2);
            $phone_number = $cart->data['billing_phone'];
            $country_code = $cart->data['billing_country_code'];
        } else {
            $full_name_segments = explode(' ', $cart->data['shipping_full_name'], 2);
            $phone_number = $cart->data['shipping_phone'];
            $country_code = $cart->data['shipping_country_code'];
        }
        $first_name = isset($full_name_segments[0]) ? $full_name_segments[0] : '';
        $last_name = isset($full_name_segments[1]) ? $full_name_segments[1] : '';

        if ($email && $first_name && $last_name && $phone_number) {
            $phone_prefix = (isset($phone_prefix_countries[$country_code]) && $phone_prefix_countries[$country_code]) ? '+' . $phone_prefix_countries[$country_code] : '+1';
            $data = [
                'api_key' => $klaviyo_sms_api_key,
                'profiles' => [
                    'email' => $email,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone_number' => $phone_prefix . ltrim($phone_number, '0'),
                    'sms_consent' => true,
                    '$consent_method' => 'API',
                    '$consent_timestamp' => date('c'),
                    'recovery_cart' => $cart->getRecoveryUrl() . '.sms'
                ]
            ];

            try {
                $DB = OSC::core('database');
                $data_insert = [
                    'queue_flag' => 0,
                    'error_message' => '',
                    'data' => OSC::encode($data),
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                $DB->insert('catalog_klaviyo_sms_queue', $data_insert, 'insert_catalog_klaviyo_sms_queue');
                $queue_id = $DB->getInsertedId();
                if ($queue_id > 0) {
                    OSC::core('cron')->addQueue('smsMarketing/klaviyoPush', null, ['requeue_limit' => -1, 'estimate_time' => 60 * 60]);
                }
            } catch (Exception $ex) {
            }
        }
    }
}

