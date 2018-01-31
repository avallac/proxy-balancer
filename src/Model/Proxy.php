<?php

namespace AVAllAC\ProxyBalancer\Model;

class Proxy
{
    public $uri = '';
    public $allowUseAfter = 0;
    public $metric = 0;

    public function __construct(string $uri, float $metric = 0.0)
    {
        $this->uri = $uri;
        $this->metric = $metric;
    }
}
