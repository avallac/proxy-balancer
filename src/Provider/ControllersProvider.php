<?php declare(strict_types=1);

namespace AVAllAC\ProxyBalancer\Provider;

use AVAllAC\ProxyBalancer\Controller\RootController;
use AVAllAC\ProxyBalancer\Controller\StatusController;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ControllersProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['rootController'] = function () use ($pimple) {
            return new RootController($pimple['proxyManager']);
        };

        $pimple['statusController'] = function () use ($pimple) {
            return new StatusController($pimple['microTime'], $pimple['proxyManager']);
        };
    }
}