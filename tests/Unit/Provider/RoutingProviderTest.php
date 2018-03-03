<?php

namespace AVAllAC\ProxyBalancer\Provider;

use AVAllAC\ProxyBalancer\Controller\RootController;
use AVAllAC\ProxyBalancer\Controller\StatusController;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class RoutingProviderTest extends TestCase
{
    public function testRegister()
    {
        $pimple = new Container();
        $pimple['rootController'] = $this->createMock(RootController::class);
        $pimple['statusController'] = $this->createMock(StatusController::class);
        $rp = new RoutingProvider();
        $rp->register($pimple);
        $this->assertInstanceOf(UrlMatcher::class, $pimple['router']);
    }
}