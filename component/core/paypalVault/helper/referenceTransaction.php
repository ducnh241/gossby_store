<?php

require_once(dirname(__FILE__) . '/../lib/Payment.php');
require_once(dirname(__FILE__) . '/../lib/BillingAgreement.php');
require_once(dirname(__FILE__) . '/../lib/BillingAgreementToken.php');

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalVault\Vault\Payment;
use PayPalVault\Vault\BillingAgreement;
use PayPalVault\Vault\BillingAgreementToken;

class Helper_PaypalVault_ReferenceTransaction {
    public function createBillingAgreementToken(PayPalHttpClient $client, $body) {
        try {
            $request = new BillingAgreementToken();
            $request->body = $body;

            $response = $client->execute($request);

            return $response;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function createBillingAgreement(PayPalHttpClient $client, $token_id) {
        try {
            $request = new BillingAgreement();
            $request->body = [
                "token_id" => $token_id
            ];

            $response = $client->execute($request);

            return $response;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function createPayment(PayPalHttpClient $client, $body) {
        try {
            $request = new Payment();
            $request->body = $body;

            $response = $client->execute($request);

            return $response;
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}