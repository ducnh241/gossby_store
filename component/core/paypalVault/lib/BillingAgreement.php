<?php

namespace PayPalVault\Vault;

use PayPalHttp\HttpRequest;

class BillingAgreement extends HttpRequest
{
    function __construct()
    {
        parent::__construct("/v1/billing-agreements/agreements", "POST");

        $this->headers["Content-Type"] = "application/json";
    }

    public function prefer($prefer)
    {
        $this->headers["Prefer"] = $prefer;
    }
}