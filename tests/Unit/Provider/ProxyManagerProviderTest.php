<?php

namespace AVAllAC\ProxyBalancer\Provider;

use AVAllAC\ProxyBalancer\Service\ProxyManager;
use AVAllAC\ProxyBalancer\Service\MicroTime;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

class ProxyManagerProviderTest extends TestCase
{
    public function testRegister()
    {
        $pimple = new Container();
        $pimple['microTime'] =  $this->createMock(MicroTime::class);
        $pimple['config'] = ['service' => []];
        $pimple['proxy_list'] = [];
        $pimple['statistic'] = [];
        $cmp = new ProxyManagerProvider();
        $cmp->register($pimple);
        $this->assertInstanceOf(ProxyManager::class, $pimple['proxyManager']);
    }
}