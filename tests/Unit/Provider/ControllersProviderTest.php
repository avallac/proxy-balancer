<?php

namespace AVAllAC\ProxyBalancer\Provider;

use AVAllAC\ProxyBalancer\Controller\RootController;
use AVAllAC\ProxyBalancer\Controller\StatusController;
use AVAllAC\ProxyBalancer\Service\ProxyManager;
use AVAllAC\ProxyBalancer\Service\MicroTime;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

class ControllersProviderTest extends TestCase
{
    public function testRegister()
    {
        $pimple = new Container();
        $pimple['microTime'] =  $this->createMock(MicroTime::class);
        $pimple['proxyManager'] =  $this->createMock(ProxyManager::class);
        $pimple['config'] = ['cacheLifeTime' => 10];
        $cp = new ControllersProvider();
        $cp->register($pimple);
        $this->assertInstanceOf(StatusController::class, $pimple['statusController']);
        $this->assertInstanceOf(RootController::class, $pimple['rootController']);
    }
}