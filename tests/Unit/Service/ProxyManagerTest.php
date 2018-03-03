<?php

namespace AVAllAC\ProxyBalancer\Service;

use PHPUnit\Framework\TestCase;

class ProxyManagerTest extends TestCase
{
    public function testConstruct()
    {
        $time = $this->createMock(MicroTime::class);
        $storedMetrics = ['avito' => ['proxy1' => 10.5]];
        $pb = new ProxyManager($time, ['avito' => 11], ['proxy1', 'proxy2'], $storedMetrics);
        $this->assertSame(['avito' => ['proxy2' => 0.0, 'proxy1' => 10.5]], $pb->exportMetric());
    }

    public function testGet()
    {
        $time = $this->createMock(MicroTime::class);
        $time->method('get')->willReturn(10000);
        $storedMetrics = ['avito' => ['proxy1' => 10.5]];
        $pb = new ProxyManager($time, ['avito' => 11], ['proxy1', 'proxy2'], $storedMetrics);
        $this->assertEquals('proxy2', $pb->get('avito'));
        $this->assertEquals('proxy1', $pb->get('avito'));
        $this->assertEquals(null, $pb->get('avito'));
    }

    public function testReport()
    {
        $time = $this->createMock(MicroTime::class);
        $time->method('get')->willReturn(10000);
        $pb = new ProxyManager($time, ['avito' => 11], ['proxy1', 'proxy2'], []);
        $this->assertSame(['avito' => ['proxy1' => 0.0, 'proxy2' => 0.0]], $pb->exportMetric());
        $pb->setAnswerStatistic('avito', 'proxy1', 30);
        $this->assertSame(['avito' => ['proxy2' => 0.0, 'proxy1' => 0.3]], $pb->exportMetric());
        $pb->setAnswerStatistic('avito', 'proxy2', 60);
        $this->assertSame(['avito' => ['proxy1' => 0.3, 'proxy2' => 0.6]], $pb->exportMetric());
        $pb->setAnswerStatistic('avito', 'proxy2', 60);
        $this->assertSame(['avito' => ['proxy1' => 0.3, 'proxy2' => 1.194]], $pb->exportMetric());
        $pb->setAnswerStatistic('avito', 'proxy4', 60);
    }

    public function testAllowed()
    {
        $time = $this->createMock(MicroTime::class);
        $time->method('get')->willReturn(10000);
        $storedMetrics = ['avito' => ['proxy1' => 10.5]];
        $pb = new ProxyManager($time, ['avito' => 11], ['proxy1', 'proxy2'], $storedMetrics);
        $this->assertEquals(2, $pb->countAllowed('avito'));
        $pb->get('avito');
        $this->assertEquals(1, $pb->countAllowed('avito'));
        $pb->get('avito');
        $this->assertEquals(0, $pb->countAllowed('avito'));
    }

    public function testReportBad()
    {
        $time = $this->createMock(MicroTime::class);
        $time->method('get')->willReturn(10000);
        $pm = new ProxyManager($time, ['avito' => 11], ['proxy1', 'proxy2'], []);
        $this->assertSame(['avito' => ['proxy1' => 0.0, 'proxy2' => 0.0]], $pm->exportMetric());
        $pm->reportBadProxy('avito', 'proxy1');
        $this->assertEquals(1, $pm->countAllowed('avito'));
        $this->assertSame(['avito' => ['proxy1' => 0.0, 'proxy2' => 0.0]], $pm->exportMetric());
    }

    public function testGetService()
    {
        $time = $this->createMock(MicroTime::class);
        $pm = new ProxyManager($time, ['avito' => 11], ['proxy1', 'proxy2'], []);
        $this->assertSame(['avito' => 11], $pm->getServices());
    }

    public function testGetProxyList()
    {
        $time = $this->createMock(MicroTime::class);
        $pm = new ProxyManager($time, ['avito' => 11], ['proxy1', 'proxy2'], []);
        $proxyListAvito = $pm->getProxyList()['avito'];
        $this->assertSame(2, count($proxyListAvito));
        $this->assertSame('proxy1', $proxyListAvito[0]->uri);
        $this->assertSame('proxy2', $proxyListAvito[1]->uri);
    }
}
