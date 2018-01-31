<?php

namespace AVAllAC\ProxyBalancer\Tests\Unit\Model;

use AVAllAC\ProxyBalancer\Model\Time;
use \Mockery as m;
use AVAllAC\ProxyBalancer\Model\ProxyManager;
use AVAllAC\ProxyBalancer\Tests\BaseTestCase;

class ProxyBalancerTest extends BaseTestCase
{
    public function testConstruct()
    {
        $time = m::mock(Time::class);
        $storedMetrics = ['avito' => ['proxy1' => 10.5]];
        $pb = new ProxyManager($time, ['avito' => 11], ['proxy1', 'proxy2'], $storedMetrics);
        $this->assertSame(['avito' => ['proxy2' => 0.0, 'proxy1' => 10.5]], $pb->exportMetric());
    }

    public function testGet()
    {
        $time = m::mock(Time::class, ['get' => 10000]);
        $storedMetrics = ['avito' => ['proxy1' => 10.5]];
        $pb = new ProxyManager($time, ['avito' => 11], ['proxy1', 'proxy2'], $storedMetrics);
        $this->assertEquals('proxy2', $pb->get('avito'));
        $this->assertEquals('proxy1', $pb->get('avito'));
        $this->assertEquals(null, $pb->get('avito'));
    }

    public function testReport()
    {
        $time = m::mock(Time::class, ['get' => 10000]);
        $pb = new ProxyManager($time, ['avito' => 11], ['proxy1', 'proxy2'], []);
        $this->assertSame(['avito' => ['proxy1' => 0.0, 'proxy2' => 0.0]], $pb->exportMetric());
        $pb->setAnswerStatistic('avito', 'proxy1', 30);
        $this->assertSame(['avito' => ['proxy2' => 0.0, 'proxy1' => 0.3]], $pb->exportMetric());
        $pb->setAnswerStatistic('avito', 'proxy2', 60);
        $this->assertSame(['avito' => ['proxy1' => 0.3, 'proxy2' => 0.6]], $pb->exportMetric());
        $pb->setAnswerStatistic('avito', 'proxy2', 60);
        $this->assertSame(['avito' => ['proxy1' => 0.3, 'proxy2' => 1.194]], $pb->exportMetric());
    }

    public function testAllowed()
    {
        $time = m::mock(Time::class, ['get' => 10000]);
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
        $time = m::mock(Time::class, ['get' => 10000]);
        $pb = new ProxyManager($time, ['avito' => 11], ['proxy1', 'proxy2'], []);
        $this->assertSame(['avito' => ['proxy1' => 0.0, 'proxy2' => 0.0]], $pb->exportMetric());
        $pb->reportBadProxy('avito', 'proxy1');
        $this->assertEquals(1, $pb->countAllowed('avito'));
        $this->assertSame(['avito' => ['proxy2' => 0.0, 'proxy1' => 0.6]], $pb->exportMetric());

    }
}
