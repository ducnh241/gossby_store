<?php

class Helper_PaypalPro_Payment extends Abstract_Catalog_Payment
{

    public function __construct()
    {
        parent::__construct();
    }

    public function setAccount($account)
    {
        parent::setAccount($account);

        return $this;
    }

    public function getPriority()
    {
        return 100;
    }

    public function getKey()
    {
        return 'paypalPro';
    }

    public function getTextTitle()
    {
        return 'Credit card';
    }

    public function getHtmlTitle()
    {
        return OSC::helper('frontend/template')->build('paypalPro/title');
    }

    public function getPaymentForm()
    {
        return OSC::helper('frontend/template')->build('paypalPro/form');
    }

    protected function getEnpointUrl() {
        return OSC_ENV != 'production' ? 'https://pilot-payflowpro.paypal.com' : 'https://payflowpro.paypal.com';
    }

    protected function parseResponse($str)
    {
        $workstr = $str;
        $out = array();

        while (strlen($workstr) > 0) {
            $loc = strpos($workstr, '=');
            if ($loc === FALSE) {
                // Truncate the rest of the string, it's not valid
                $workstr = "";
                continue;
            }

            $substr = substr($workstr, 0, $loc);
            $workstr = substr($workstr, $loc + 1); // "+1" because we need to get rid of the "="

            if (preg_match('/^(\w+)\[(\d+)]$/', $substr, $matches)) {
                // This one has a length tag with it.  Read the number of characters
                // specified by $matches[2].
                $count = intval($matches[2]);

                $out[$matches[1]] = substr($workstr, 0, $count);
                $workstr = substr($workstr, $count + 1); // "+1" because we need to get rid of the "&"
            } else {
                // Read up to the next "&"
                $count = strpos($workstr, '&');
                if ($count === FALSE) { // No more "&"'s, read up to the end of the string
                    $out[$substr] = $workstr;
                    $workstr = "";
                } else {
                    $out[$substr] = substr($workstr, 0, $count);
                    $workstr = substr($workstr, $count + 1); // "+1" because we need to get rid of the "&"
                }
            }
        }

        return $out;
    }

    /**
     * Pay follow header request docs:
     * https://www.paypal.com/sm/smarthelp/article/how-do-i-implement-paypal's-payflow-pro-and-website-payments-pro-payflow-edition-https-interface-ts1348
     * @return array
     */
    protected function buildHeaders()
    {
        $headers = array();

        $headers[] = "Content-Type: text/namevalue"; //or maybe text/xml
        $headers[] = "X-VPS-Timeout: 30";
        $headers[] = "X-VPS-VIT-OS-Name: Linux";  // Name of your OS
        $headers[] = "X-VPS-VIT-OS-Version: Nginx";  // OS Version
        $headers[] = "X-VPS-VIT-Client-Type: PHP/cURL";  // What you are using
        $headers[] = "X-VPS-VIT-Client-Version: 0.01";  // For your info
        $headers[] = "X-VPS-VIT-Client-Architecture: x86";  // For your info
        $headers[] = "X-VPS-VIT-Client-Certification-Id: 13fda2433fc2123d8b191d2d011b7fdc"; // get this from payflowintegrator@paypal.com
        $headers[] = "X-VPS-VIT-Integration-Product: DLS_paypalPro";  // For your info, would populate with application name
        $headers[] = "X-VPS-VIT-Integration-Version: 0.01"; // Application version

        return $headers;
    }

    protected function _getToken(array $payment_info) {
        $request = OSC::core('request');
        $amount = $payment_info['total_price'];
        $cc = $request->get('card');
        $cc['expire_date'] = [$cc['expiry_date_month'], $cc['expiry_date_year']];

        // build hash
        $tempstr = $cc['number'] . $amount . date('YmdGis') . "_request_token";
        $requestId = md5($tempstr);

        $account = $this->getAccount();

        $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
        $checkout_url = OSC_FRONTEND_BASE_URL . '/' . $current_lang_key . '/checkout';
        $data = [
            'USER' => $account['account_info']['user'],
            'VENDOR' => $account['account_info']['vendor'],
            'PARTNER' => $account['account_info']['partner'],
            'PWD' => $account['account_info']['password'],
            'TENDER' => 'C', // C = credit card, P = PayPal
            'TRXTYPE' => 'A', //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void

            "CREATESECURETOKEN" => "Y",
            "SECURETOKENID" => OSC::makeUniqid('paypalPro'), //Should be unique, never used before
            "RETURNURL" => $checkout_url,
            "CANCELURL" => $checkout_url,
            "ERRORURL" => $checkout_url,
            'SILENTTRAN' => TRUE,

            'AMT' => $amount,
            'CURRENCY' => $payment_info['currency_code'],

            'BILLTOFIRSTNAME'=> $payment_info['billing_address']['first_name'],
            'BILLTOLASTNAME'=> $payment_info['billing_address']['last_name'],
            'BILLTOSTREET'  => $payment_info['billing_address']['address1'],
            'BILLTOSTREET2' => $payment_info['billing_address']['address2'],
            'BILLTOCITY'    => $payment_info['billing_address']['city'],
            'BILLTOSTATE'   => $payment_info['billing_address']['province'],
            'BILLTOZIP'     => $payment_info['billing_address']['zip'],

            'SHIPTOFIRSTNAME'=> $payment_info['shipping_info']['address']['first_name'],
            'SHIPTOLASTNAME'=> $payment_info['shipping_info']['address']['last_name'],
            'SHIPTOSTREET'  => $payment_info['shipping_info']['address']['address1'],
            'SHIPTOSTREET2' => $payment_info['shipping_info']['address']['address2'],
            'SHIPTOCITY'    => $payment_info['shipping_info']['address']['city'],
            'SHIPTOSTATE'   => $payment_info['billing_address']['province'],
            'SHIPTOZIP'     => $payment_info['shipping_info']['address']['zip'],
            'VERBOSITY' => 'HIGH'
        ];

        $headers = static::buildHeaders();
        $headers[] = "X-VPS-Request-ID: " . $requestId;

        $parrams = [
            'request_method' => 'POST',
            'headers' => $headers,
            'timeout' => 45,
            'data' => $data
        ];

        $response = null;
        $request = OSC::core('network')->curl($this->getEnpointUrl(), $parrams);
        if (!isset($request['content'])) {
            throw new Exception('Response data is incorrect: ' . print_r($request['content'], 1));
        }

        $response = $this->parseResponse($request['content']);
        if($response['RESULT'] == 0) {
            return [
                'token' => $response['SECURETOKEN'],
                'tokenId' => $response['SECURETOKENID'],
                'message' => $response['RESPMSG']
            ];
        } else {
            throw new Exception($response['RESPMSG']);
        }
    }

    public function authorize(array $payment_info)
    {
        //$token = $this->_getToken($payment_info);
        $request = OSC::core('request');
        $amount = $payment_info['total_price'];
        $cc = $request->get('card');
        $cc['expire_date'] = [$cc['expiry_date_month'], $cc['expiry_date_year']];

        // build hash
        $tempstr = $cc['number'] . $amount . date('YmdGis') . "_authorize";
        $requestId = md5($tempstr);

        $account = $this->getAccount();

        $data = [
            'USER' => $account['account_info']['user'],
            'VENDOR' => $account['account_info']['vendor'],
            'PARTNER' => $account['account_info']['partner'],
            'PWD' => $account['account_info']['password'],

//            'SECURETOKEN' => $token['token'],
//            'SECURETOKENID' => $token['tokenId'],

            'TENDER' => 'C', // C = credit card, P = PayPal
            'TRXTYPE' => 'A', //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
            'ACCT' => str_replace(' ', '', $cc['number']),
            'EXPDATE' => trim($cc['expire_date'][0]) . trim($cc['expire_date'][1]), //mmyy
            'CVV2' => $cc['cvc'],
            'AMT' => $amount,
            'CURRENCY' => $payment_info['currency_code'],
            'TAXAMT'        => $payment_info['price_summary']['tax'],
            'CUSTIP'        => OSC::getClientIP(),
            'PHONENUM'      => '',
            'EMAIL'         => $payment_info['email'],
            'INVNUM'        => $payment_info['invoice_number'],

            'BILLTOFIRSTNAME'=> $payment_info['billing_address']['first_name'],
            'BILLTOLASTNAME'=> $payment_info['billing_address']['last_name'],
            'BILLTOSTREET'  => $payment_info['billing_address']['address1'],
            'BILLTOSTREET2' => $payment_info['billing_address']['address2'],
            'BILLTOCITY'    => $payment_info['billing_address']['city'],
            'BILLTOSTATE'   => $payment_info['billing_address']['province'],
            'BILLTOZIP'     => $payment_info['billing_address']['zip'],
            'BILLTOCOUNTRY' => $payment_info['billing_address']['country_code'],

            'SHIPTOFIRSTNAME'=> $payment_info['shipping_info']['address']['first_name'],
            'SHIPTOLASTNAME'=> $payment_info['shipping_info']['address']['last_name'],
            'SHIPTOSTREET'  => $payment_info['shipping_info']['address']['address1'],
            'SHIPTOSTREET2' => $payment_info['shipping_info']['address']['address2'],
            'SHIPTOCITY'    => $payment_info['shipping_info']['address']['city'],
            'SHIPTOSTATE'   => $payment_info['billing_address']['province'],
            'SHIPTOZIP'     => $payment_info['shipping_info']['address']['zip'],
            'SHIPTOCOUNTRY' => $payment_info['shipping_info']['address']['country_code'],
            'VERBOSITY' => 'HIGH'
        ];

        $headers = static::buildHeaders();
        $headers[] = "X-VPS-Request-ID: " . $requestId;

        $parrams = [
            'request_method' => 'POST',
            'headers' => $headers,
            'timeout' => 45,
            'data' => $data
        ];

        $response = null;
        $request = OSC::core('network')->curl($this->getEnpointUrl(), $parrams);
        if (!isset($request['content'])) {
            throw new Exception('Response data is incorrect: ' . print_r($request['content'], 1));
        }

        $response = $this->parseResponse($request['content']);

        if( $response['RESULT'] == 0 || $response['RESULT'] == 126 ) {
            return [
                'payment_data' => $response,
                'fraud_data' => [
                    'score' => null,
                    'info' => null
                ]
            ];
        } else {
            throw new Exception($response['RESPMSG']);
        }
    }

    public function charge(array $order_info)
    {
    }

    /**
     *
     * @param mixed $payment_data
     * @param float $amount
     * @param string $currency_code
     * @return mixed
     */
    public function void($payment_data, float $amount, string $currency_code, int $added_timestamp)
    {
        $payment_info = $payment_data;

        if (empty($payment_info['payment_data'])) {
            return $payment_data;
        }

        $payment_data = $payment_info['payment_data'];

        if ($added_timestamp < (time() - (60 * 60 * 24 * 7))) {
            $payment_data['void_note'] = 'Expired transaction';
            return $payment_data;
        }

        // build hash
        $requestId = md5($payment_data['AUTHCODE'] . $amount . date('YmdGis') . "3");

        $account = $this->getAccount();

        $data = [
            'USER' => $account['account_info']['user'],
            'VENDOR' => $account['account_info']['vendor'],
            'PARTNER' => $account['account_info']['partner'],
            'PWD' => $account['account_info']['password'],
            'TENDER' => 'C', // C = credit card, P = PayPal
            'TRXTYPE' => 'V', //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
            'ORIGID' => $payment_data['PNREF'],
            'VERBOSITY' => 'HIGH'
        ];


        $headers = static::buildHeaders();
        $headers[] = "X-VPS-Request-ID: " . $requestId;

        $parrams = [
            'request_method' => 'POST',
            'headers' => $headers,
            'timeout' => 45,
            'data' => $data
        ];

        $response = null;
        $request = OSC::core('network')->curl($this->getEnpointUrl(), $parrams);

        if (!isset($request['content'])) {
            throw new Exception('Response data is incorrect: ' . print_r($request['content'], 1));
        }

        $response = $this->parseResponse($request['content']);
        if( isset($response['RESULT']) && $response['RESULT'] == 0) {
            $payment_data['void_id'] = $response;
            return $payment_data;
        } else {
            throw new Exception($response['RESPMSG']);
        }
    }

    /**
     *
     * @param mixed $payment_data
     * @param float $amount
     * @param string $currency_code
     * @return mixed
     */
    public function capture($payment_data, float $amount, string $currency_code)
    {
        $payment_info = $payment_data;

        if (empty($payment_info['payment_data'])) {
            return $payment_data;
        }

        $payment_data = $payment_info['payment_data'];

        // build hash
        $requestId = md5($payment_data['AUTHCODE'] . $amount . date('YmdGis') . "2");

        $account = $this->getAccount();

        $data = [
            'USER' => $account['account_info']['user'],
            'VENDOR' => $account['account_info']['vendor'],
            'PARTNER' => $account['account_info']['partner'],
            'PWD' => $account['account_info']['password'],
            'TENDER' => 'C', // C = credit card, P = PayPal
            'TRXTYPE' => 'D', //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
            'ORIGID' => $payment_data['PNREF'],
            'VERBOSITY' => 'HIGH'
        ];


        $headers = static::buildHeaders();
        $headers[] = "X-VPS-Request-ID: " . $requestId;

        $parrams = [
            'request_method' => 'POST',
            'headers' => $headers,
            'timeout' => 45,
            'data' => $data
        ];

        $response = null;
        $request = OSC::core('network')->curl($this->getEnpointUrl(), $parrams);

        if (!isset($request['content'])) {
            throw new Exception('Response data is incorrect: ' . print_r($request['content'], 1));
        }

        $response = $this->parseResponse($request['content']);

        if( isset($response['RESULT']) && $response['RESULT'] == 0) {
            return $response;
        } else {
            throw new Exception($response['RESPMSG']);
        }
    }

    /**
     *
     * @param mixed $payment_data
     * @param float $amount
     * @param string $currency_code
     * @param string $reason
     * @return mixed
     */
    public function refund($payment_data, float $amount, string $currency_code, string $description, string $reason = '')
    {
        $payment_info = $payment_data;

        if (empty($payment_info['payment_data'])) {
            return $payment_data;
        }

        $payment_data = $payment_info['payment_data'];

        // build hash
        $requestId = md5($payment_data['AUTHCODE'] . $amount . date('YmdGis') . "4");

        $account = $this->getAccount();

        $data = [
            'USER' => $account['account_info']['user'],
            'VENDOR' => $account['account_info']['vendor'],
            'PARTNER' => $account['account_info']['partner'],
            'PWD' => $account['account_info']['password'],
            'TENDER' => 'C', // C = credit card, P = PayPal
            'TRXTYPE' => 'C', //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
            'ORIGID' => $payment_data['PNREF'],
            'AMT' => $amount,
            'VERBOSITY' => 'HIGH'
        ];

        $headers = static::buildHeaders();
        $headers[] = "X-VPS-Request-ID: " . $requestId;

        $parrams = [
            'request_method' => 'POST',
            'headers' => $headers,
            'timeout' => 45,
            'data' => $data
        ];

        $response = null;
        $request = OSC::core('network')->curl($this->getEnpointUrl(), $parrams);

        if (!isset($request['content'])) {
            throw new Exception('Response data is incorrect: ' . print_r($request['content'], 1));
        }

        $response = $this->parseResponse($request['content']);
        if( isset($response['RESULT']) && $response['RESULT'] == 0) {

            if (!isset($payment_data['refund_ids'])) {
                $payment_data['refund_ids'] = [];
            }
            $payment_data['refund_ids'][] = $response;

            return $payment_data;
        } else {
            throw new Exception($response['RESPMSG']);
        }
    }


    public function update(array $order_info, array $payment_data)
    {
        // TODO: Implement update() method.
    }
}
