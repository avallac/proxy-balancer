<?php

namespace AVAllAC\ProxyBalancer;

use AVAllAC\ProxyBalancer\Controller\RootController;
use AVAllAC\ProxyBalancer\Exception\UnauthorizedException;
use AVAllAC\ProxyBalancer\Model\ProxyManager;
use AVAllAC\ProxyBalancer\Model\Time;
use Pimple\Container;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

class App extends Container
{
    protected $routes;
    protected $matcher;

    public function initRoutes()
    {
        $this->routes = new RouteCollection();
        $controller = new RootController($this);
        $route = new Route('/', ['_controller' => [$controller, 'status']]);
        $this->routes->add('status', $route);
        $route = new Route('/debug', ['_controller' => [$controller, 'debug']]);
        $this->routes->add('debug', $route);
        $route = new Route('/get/{service}', ['_controller' => [$controller, 'get']]);
        $this->routes->add('get', $route);
        $route = new Route('/report/{service}', ['_controller' => [$controller, 'report']]);
        $this->routes->add('report', $route);
        $route = new Route('/complaint/{service}', ['_controller' => [$controller, 'complaint']]);
        $this->routes->add('complaint', $route);
    }

    public function __construct()
    {
        parent::__construct();
        $this['config'] = Yaml::parseFile(__DIR__ . '/../etc/config.yml');
        $this->initRoutes();
        $context = new RequestContext('/');
        $this->matcher = new UrlMatcher($this->routes, $context);
        $metrics = json_decode(file_get_contents(__DIR__ . '/../statistic.json'), true) ?? [];
        $services = $this['config']['service'];
        $proxy = $this->readProxyList(__DIR__ . '/../etc/proxy.list');
        $this[ProxyManager::class] = new ProxyManager(new Time(), $services, $proxy, $metrics);

    }

    private function readProxyList($fileName)
    {
        return explode("\n", trim(file_get_contents($fileName)));

    }

    protected function authEncode($username, $password)
    {
        return 'Basic ' . base64_encode($username . ':' . $password);
    }

    public function handle(ServerRequestInterface $request)
    {
        $auth = $this['config']['auth'];
        $authString = $this->authEncode($auth['username'], $auth['password']);
        if ($authString === $request->getHeader('authorization')[0]) {
            $params = ['request' => $request];
            $route = $request->getUri()->getPath();
            $matched = $this->matcher->match($route);
            $params = array_merge($params, $matched);
            unset($params['_controller']);
            unset($params['_route']);
            return \call_user_func_array($matched['_controller'], $params);
        } else {
            throw new UnauthorizedException();
        }
    }
}