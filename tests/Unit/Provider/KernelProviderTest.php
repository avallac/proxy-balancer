<?php

namespace AVAllAC\ProxyBalancer\Provider;

use AVAllAC\ProxyBalancer\Service\Kernel;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class KernelProviderTest extends TestCase
{
    public function testRegister()
    {
        $pimple = new Container();
        $pimple['router'] =  $this->createMock(UrlMatcher::class);
        $pimple['config'] = ['auth' => ['username' => 'admin', 'password' => 'password']];
        $kp = new KernelProvider();
        $kp->register($pimple);
        $this->assertInstanceOf(Kernel::class, $pimple['kernel']);
    }
}