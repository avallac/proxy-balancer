<?php declare(strict_types=1);

namespace AVAllAC\ProxyBalancer\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RoutingProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['router'] = function () use ($pimple) {
            $routes = new RouteCollection();
            $route = new Route('/status', ['_controller' => [$pimple['statusController'], 'status']]);
            $routes->add('status', $route);
            $route = new Route('/', ['_controller' => [$pimple['rootController'], 'services']]);
            $routes->add('services', $route);
            $route = new Route('/debug', ['_controller' => [$pimple['rootController'], 'debug']]);
            $routes->add('debug', $route);
            $route = new Route('/get/{service}', ['_controller' => [$pimple['rootController'], 'get']]);
            $routes->add('get', $route);
            $route = new Route('/report/{service}', ['_controller' => [$pimple['rootController'], 'report']]);
            $routes->add('report', $route);
            $route = new Route('/complaint/{service}', ['_controller' => [$pimple['rootController'], 'complaint']]);
            $routes->add('complaint', $route);
            return new UrlMatcher($routes, new RequestContext('/'));
        };
    }
}