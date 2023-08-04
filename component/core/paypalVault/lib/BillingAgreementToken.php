<?php

namespace PayPalVault\Vault;

use PayPalHttp\HttpRequest;

class BillingAgreementToken extends HttpRequest
{
    function __construct()
    {
        parent::__construct("/v1/billing-agreements/agreement-tokens", "POST");

        $this->headers["Content-Type"] = "application/json";
    }

    public function prefer($prefer)
    {
        $this->headers["Prefer"] = $prefer;
    }
}