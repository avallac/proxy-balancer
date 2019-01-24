<?php

namespace AVAllAC\ProxyBalancer\Controller;

use AVAllAC\ProxyBalancer\Service\MicroTime;
use AVAllAC\ProxyBalancer\Service\ProxyManager;
use PHPUnit\Framework\TestCase;

class StatusControllerTest extends TestCase
{
    public function testStatus()
    {
        $proxyManager = $this->createMock(ProxyManager::class);
        $microTime = $this->createMock(MicroTime::class);
        $microTime->method('getInitTime')->willReturn(1000);
        $microTime->method('get')->willReturn(2000);
        $statusController = new StatusController($microTime, $proxyManager);
        $this->assertEquals('{"uptime":1000}', $statusController->status());
    }
}