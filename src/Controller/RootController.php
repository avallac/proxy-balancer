<?php

namespace AVAllAC\ProxyBalancer\Controller;

use AVAllAC\ProxyBalancer\Service\ProxyManager;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class RootController
{
    protected $proxyManager;

    public function __construct(ProxyManager $proxyManager)
    {
        $this->proxyManager = $proxyManager;
    }

    public function services(ServerRequestInterface $request)
    {
        $result = [];
        foreach (array_keys($this->proxyManager->getServices()) as $service) {
            $result[$service] = $this->proxyManager->countAllowed($service);
        }
        return json_encode($result);
    }

    public function debug(ServerRequestInterface $request)
    {
        $result = [];
        foreach ($this->proxyManager->getProxyList() as $service => $list) {
            $result[$service] = [];
            usort($list, function ($a, $b) {
                return $a->allowUseAfter > $b->allowUseAfter;
            });
            foreach ($list as $item) {
                $result[$service][] = [
                    'uri' => $item->uri,
                    'allowUseAfter' => date("c", $item->allowUseAfter),
                    'metric' => $item->metric
                ];
            }
        }
        return json_encode($result);
    }

    public function get(ServerRequestInterface $request, string $service)
    {
        return $this->proxyManager->get($service);
    }

    public function complaint(ServerRequestInterface $request, string $service)
    {
        if (!isset($request->getParsedBody()['uri'])) {
            throw new InvalidParameterException("uri required");
        }
        $uri = urldecode($request->getParsedBody()['uri']);
        $this->proxyManager->reportBadProxy($service, $uri);
        return json_encode('ok');
    }

    public function report(ServerRequestInterface $request, string $service)
    {
        if (!isset($request->getParsedBody()['uri'])) {
            throw new InvalidParameterException("uri required");
        }
        if (!isset($request->getParsedBody()['result'])) {
            throw new InvalidParameterException("result required");
        }
        $result = $request->getParsedBody()['result'];
        $uri = urldecode($request->getParsedBody()['uri']);
        $this->proxyManager->setAnswerStatistic($service, $uri, $result);
        return json_encode('ok');
    }
}