<?php

namespace Shipu\SslWPayment\HttpManager;

use GuzzleHttp\Client;

class RequestHandler
{
    public $baseUrl;

    public $http;

    public function __construct()
    {
        if(config('sslwpayment.sandbox')) {
            $this->baseUrl = 'https://sandbox.sslcommerz.com/';
        } else {
            $this->baseUrl = 'https://securepay.sslcommerz.com/';
        }
        $this->http = new Client(['base_uri' => $this->baseUrl, 'timeout' => 2.0]);
    }
}
