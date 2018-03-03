<?php

namespace AVAllAC\ProxyBalancer\Service;

use PHPUnit\Framework\TestCase;

class MicroTimeTest extends TestCase
{
    public function testGet()
    {
        $microTime = new MicroTime();
        $timeStamp1 = $microTime->get();
        $timeStamp2 = microtime(true);
        $this->assertLessThan(1, $timeStamp2 - $timeStamp1);
    }

    public function testGetInitTime()
    {
        $microTime = new MicroTime();
        $timeStamp = microtime(true);
        $this->assertLessThan(1, $timeStamp - $microTime->getInitTime());
    }
}