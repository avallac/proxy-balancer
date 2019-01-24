<?php

namespace AVAllAC\ProxyBalancer\Service;

use PHPUnit\Framework\TestCase;

class ProxyManagerTest extends TestCase
{
    public function testConstruct()
    {
        $time = $this->createMock(MicroTime::class);
        $storedMetrics = ['google' => ['proxy1' => 10.5]];
        $pb = new ProxyManager($time, ['google' => 11], ['proxy1', 'proxy2#tag1'], $storedMetrics);
        $this->assertSame(['google' => ['proxy2' => 0.0, 'proxy1' => 10.5]], $pb->exportMetric());
    }

    public function testGet()
    {
        $time = $this->createMock(MicroTime::class);
        $time->method('get')->willReturn(10000);
        $storedMetrics = ['google' => ['proxy1' => 10.5]];
        $pb = new ProxyManager($time, ['google' => 11], ['proxy1', 'proxy2'], $storedMetrics);
        $this->assertEquals('proxy2', $pb->get('google'));
        $this->assertEquals('proxy1', $pb->get('google'));
        $this->assertEquals(null, $pb->get('google'));
    }

    public function testReport()
    {
        $time = $this->createMock(MicroTime::class);
        $time->method('get')->willReturn(10000);
        $pb = new ProxyManager($time, ['google' => 11], ['proxy1', 'proxy2'], []);
        $this->assertSame(['google' => ['proxy1' => 0.0, 'proxy2' => 0.0]], $pb->exportMetric());
        $pb->setAnswerStatistic('google', 'proxy1', 30);
        $this->assertSame(['google' => ['proxy2' => 0.0, 'proxy1' => 0.3]], $pb->exportMetric());
        $pb->setAnswerStatistic('google', 'proxy2', 60);
        $this->assertSame(['google' => ['proxy1' => 0.3, 'proxy2' => 0.6]], $pb->exportMetric());
        $pb->setAnswerStatistic('google', 'proxy2', 60);
        $this->assertSame(['google' => ['proxy1' => 0.3, 'proxy2' => 1.194]], $pb->exportMetric());
        $pb->setAnswerStatistic('google', 'proxy4', 60);
    }

    public function testAllowed()
    {
        $time = $this->createMock(MicroTime::class);
        $time->method('get')->willReturn(10000);
        $storedMetrics = ['google' => ['proxy1' => 10.5]];
        $pb = new ProxyManager($time, ['google' => 11], ['proxy1', 'proxy2'], $storedMetrics);
        $this->assertEquals(2, $pb->countAllowed('google'));
        $pb->get('google');
        $this->assertEquals(1, $pb->countAllowed('google'));
        $pb->get('google');
        $this->assertEquals(0, $pb->countAllowed('google'));
    }

    public function testReportBad()
    {
        $time = $this->createMock(MicroTime::class);
        $time->method('get')->willReturn(10000);
        $pm = new ProxyManager($time, ['google' => 11], ['proxy1', 'proxy2'], []);
        $this->assertSame(['google' => ['proxy1' => 0.0, 'proxy2' => 0.0]], $pm->exportMetric());
        $pm->reportBadProxy('google', 'proxy1');
        $this->assertEquals(1, $pm->countAllowed('google'));
        $this->assertSame(['google' => ['proxy1' => 0.0, 'proxy2' => 0.0]], $pm->exportMetric());
    }

    public function testGetService()
    {
        $time = $this->createMock(MicroTime::class);
        $pm = new ProxyManager($time, ['google' => 11], ['proxy1', 'proxy2'], []);
        $this->assertSame(['google' => 11], $pm->getServices());
    }

    public function testGetProxyList()
    {
        $time = $this->createMock(MicroTime::class);
        $pm = new ProxyManager($time, ['google' => 11], ['proxy1', 'proxy2'], []);
        $proxyListGoogle = $pm->getProxyList()['google']->get();
        $this->assertSame(2, count($proxyListGoogle));
        $this->assertSame('proxy1', $proxyListGoogle[0]->getUri());
        $this->assertSame('proxy2', $proxyListGoogle[1]->getUri());
    }

    public function testGetStatisticByGroups()
    {
        $time = $this->createMock(MicroTime::class);
        $storedMetrics = ['google' => ['proxy1' => 10.5, 'proxy2' => 0.45]];
        $pb = new ProxyManager($time, ['google' => 11], ['proxy1', 'proxy2#tag1'], $storedMetrics);
        $this->assertSame(
            ['google' => ['allowed' => 2, 'allowedPercent' => '100.00', 'metric' => '5.47']],
            $pb->getStatisticByGroups()
        );
    }
}
