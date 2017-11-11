<?php

namespace Shipu\SslWPayment;

use Shipu\SslWPayment\HttpManager\RequestHandler;
use Shipu\SslWPayment\HttpManager\Response;

abstract class AbstractApi
{
    protected $client;
    protected $parameters = [];
    protected $config;
    private $requestMethods = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'HEAD',
        'OPTIONS',
        'PATCH',
    ];

    public function __construct()
    {
        $this->client = new RequestHandler();
    }

    public function __call($func, $params)
    {
        $method = strtoupper($func);

        if (in_array($method, $this->requestMethods)) {
            $parameters[] = $method;
            $parameters[] = $params[0];

            return call_user_func_array([$this, 'makeMethodRequest'], $parameters);
        }
    }

    public function formParams($params = array())
    {
        if (is_array($params)) {
            $this->parameters['form_params'] = $params;

            return $this;
        }

        return false;
    }

    public function headers($params = array())
    {
        if (is_array($params)) {
            $this->parameters['headers'] = $params;

            return $this;
        }

        return false;
    }

    public function query($params = array())
    {
        if (is_array($params)) {
            $this->parameters['query'] = $params;
            return $this;
        }

        return false;
    }

    public function makeMethodRequest($method, $uri)
    {
        $this->parameters['timeout'] = 60;
        $response = new Response($this->client->http->request($method, $uri, $this->parameters));

        return $response->data;
    }
}
