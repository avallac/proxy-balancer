<?php

namespace AVAllAC\ProxyBalancer\Controller;

use AVAllAC\ProxyBalancer\Service\MicroTime;
use PHPUnit\Framework\TestCase;

class StatusControllerTest extends TestCase
{
    public function testStatus()
    {
        $microTime = $this->createMock(MicroTime::class);
        $microTime->method('getInitTime')->willReturn(1000);
        $microTime->method('get')->willReturn(2000);
        $statusController = new StatusController($microTime);
        $this->assertEquals('{"uptime":1000}', $statusController->status());
    }
}