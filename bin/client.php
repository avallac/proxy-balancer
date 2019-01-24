<?php

require __DIR__ . '/../vendor/autoload.php';

$proxy = new \AVAllAC\ProxyBalancerClient\ProxyService('http://localhost:8080', 'proxy', 'proxy');
$service = $proxy->getProxy('google');
var_dump($service, $proxy->report('google', $service, 1));
