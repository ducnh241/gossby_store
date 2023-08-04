<?php

namespace PayPalVault\Vault;

use PayPalHttp\HttpRequest;

class Payment extends HttpRequest
{
    function __construct()
    {
        parent::__construct("/v1/payments/payment", "POST");

        $this->headers["Content-Type"] = "application/json";
    }

    public function prefer($prefer)
    {
        $this->headers["Prefer"] = $prefer;
    }
}