<?php

namespace AVAllAC\ProxyBalancer\Controller;

use AVAllAC\ProxyBalancer\Model\ProxyManager;
use Psr\Http\Message\ServerRequestInterface;

class RootController
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    protected function checkInput($request, $required)
    {
        foreach ($required as $item) {
            if (!isset($request->getParsedBody()['uri'])) {
                throw new \Exception($item . " required");
            }
        }
    }

    public function status(ServerRequestInterface $request)
    {
        $result = [];
        /** @var ProxyManager $proxyManager */
        $proxyManager = $this->app[ProxyManager::class];
        foreach (array_keys($proxyManager->getServices()) as $service) {
            $result[$service] = $proxyManager->countAllowed($service);
        }
        return print_r($result, true);
    }

    public function debug(ServerRequestInterface $request)
    {
        $out = '';
        /** @var ProxyManager $proxyManager */
        $proxyManager = $this->app[ProxyManager::class];
        foreach ($proxyManager->getProxyList() as $service => $list) {
            $out .= $service . "\n";
            foreach ($list as $item) {
                $out .= '-> ' . $item->uri . '|';
                $out .= $item->allowUseAfter . '|';
                $out .= $item->metric . "\n";
            }
        }
        return $out;
    }

    public function get(ServerRequestInterface $request, string $service)
    {
        /** @var ProxyManager $proxyManager */
        $proxyManager = $this->app[ProxyManager::class];
        return $proxyManager->get($service);
    }

    public function complaint(ServerRequestInterface $request, string $service)
    {
        $this->checkInput($request, ['uri']);
        $uri = urldecode($request->getParsedBody()['uri']);
        /** @var ProxyManager $proxyManager */
        $proxyManager = $this->app[ProxyManager::class];
        $proxyManager->reportBadProxy($service, $uri);
        return 'ok';
    }

    public function report(ServerRequestInterface $request, string $service)
    {
        $this->checkInput($request, ['result', 'uri']);
        $result = $request->getParsedBody()['result'];
        $uri = urldecode($request->getParsedBody()['uri']);
        /** @var ProxyManager $proxyManager */
        $proxyManager = $this->app[ProxyManager::class];
        $proxyManager->setAnswerStatistic($service, $uri, $result);
        return 'ok';
    }
}