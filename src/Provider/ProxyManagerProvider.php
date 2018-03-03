<?php declare(strict_types=1);

namespace AVAllAC\ProxyBalancer\Provider;

use AVAllAC\ProxyBalancer\Service\ProxyManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ProxyManagerProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['proxyManager'] = function () use ($pimple) {
            return new ProxyManager(
                $pimple['microTime'],
                $pimple['config']['service'],
                $pimple['proxy_list'],
                $pimple['statistic']
            );
        };
    }
}
