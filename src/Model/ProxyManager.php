<?php

namespace AVAllAC\ProxyBalancer\Model;

class ProxyManager
{
    protected $time;
    protected $proxyList = [];
    protected $services;

    public function __construct(Time $time, array $services, array $proxyList, array $metrics)
    {
        $this->time = $time;
        $this->services = $services;
        foreach (array_keys($this->services) as $service) {
            $this->proxyList[$service] = [];
            foreach ($proxyList as $e) {
                $metric = $metrics[$service][$e] ?? 0;
                $this->proxyList[$service][] = new Proxy($e, $metric);
            }
            usort($this->proxyList[$service], function ($a, $b) {
                return $a->metric > $b->metric;
            });
        }
    }

    public function exportMetric() : array
    {
        $output = [];
        foreach (array_keys($this->services) as $service) {
            $output[$service] = [];
            foreach ($this->proxyList[$service] as $proxy) {
                $output[$service][$proxy->uri] = $proxy->metric;
            }
        }
        return $output;
    }

    public function get(string $service) : ?string
    {
        $time = $this->time->get();
        foreach ($this->proxyList[$service] as $proxy) {
            if ($proxy->allowUseAfter < $time) {
                $proxy->allowUseAfter = $time + $this->services[$service];
                return $proxy->uri;
            }
        }
        return null;
    }

    public function countAllowed(string $service) : ?string
    {
        $result = 0;
        $time = $this->time->get();
        foreach ($this->proxyList[$service] as $proxy) {
            if ($proxy->allowUseAfter < $time) {
                $result ++;
            }
        }
        return $result;
    }

    public function reportBadProxy($service, $uri)
    {
        $time = $this->time->get();
        foreach ($this->proxyList[$service] as $proxy) {
            if ($proxy->uri === $uri) {
                $proxy->allowUseAfter = $time + 3600;
                $this->setAnswerStatistic($service, $uri, 60);
            }
        }
    }

    public function setAnswerStatistic($service, $uri, $time)
    {
        foreach ($this->proxyList[$service] as $proxy) {
            if ($proxy->uri === $uri) {
                $proxy->metric = $proxy->metric * 0.99 + $time * 0.01;
                usort($this->proxyList[$service], function ($a, $b) {
                    return $a->metric > $b->metric;
                });
                return;
            }
        }
    }

    public function getServices()
    {
        return $this->services;
    }

    public function getProxyList()
    {
        return $this->proxyList;
    }
}