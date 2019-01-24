<?php

namespace AVAllAC\ProxyBalancer\Service;

use AVAllAC\ProxyBalancer\Model\Proxy;
use AVAllAC\ProxyBalancer\Model\ProxyCollection;
use AVAllAC\ProxyBalancer\Model\ProxyStatistic;

class ProxyManager
{
    protected $time;

    /** @var ProxyCollection[] */
    protected $proxyList = [];

    protected $services;

    public function __construct(MicroTime $time, array $services, array $proxyList, array $metrics)
    {
        $this->time = $time;
        $this->services = $services;
        foreach (array_keys($this->services) as $service) {
            $this->proxyList[$service] = new ProxyCollection();
            foreach ($proxyList as $e) {
                if (preg_match('|(.+)#(.+)|', $e, $m)) {
                    $metric = $metrics[$service][$m[1]] ?? 0;
                    $this->proxyList[$service]->add(new Proxy($m[1], $m[2], 0, $metric));
                } else {
                    $metric = $metrics[$service][$e] ?? 0;
                    $this->proxyList[$service]->add(new Proxy($e, 'default', 0, $metric));
                }
            }
            $this->proxyList[$service]->sort();
        }
    }

    public function exportMetric() : array
    {
        $output = [];
        foreach (array_keys($this->services) as $service) {
            $output[$service] = [];

            foreach ($this->proxyList[$service] as $id => $proxy) {

                $output[$service][$proxy->getUri()] = $proxy->getMetric();
            }
        }
        return $output;
    }

    public function get(string $service) : ?string
    {
        $time = $this->time->get();
        foreach ($this->proxyList[$service] as $proxy) {
            if ($proxy->getAllowUseAfter() < $time) {
                $proxy->setAllowUseAfter($time + $this->services[$service]);
                return $proxy->getUri();
            }
        }
        return null;
    }

    public function countAllowed(string $service) : ?string
    {
        $result = 0;
        $time = $this->time->get();
        foreach ($this->proxyList[$service] as $proxy) {
            if ($proxy->getAllowUseAfter() < $time) {
                $result ++;
            }
        }
        return $result;
    }

    public function reportBadProxy($service, $uri)
    {
        $time = $this->time->get();
        foreach ($this->proxyList[$service] as $proxy) {
            if ($proxy->getUri() === $uri) {
                $proxy->setAllowUseAfter($time + 3600);
            }
        }
    }

    public function setAnswerStatistic($service, $uri, $time)
    {
        foreach ($this->proxyList[$service] as $proxy) {
            if ($proxy->getUri() === $uri) {
                $proxy->setMetric($proxy->getMetric() * 0.99 + $time * 0.01);
                $this->proxyList[$service]->sort();
                return;
            }
        }
    }

    public function getStatisticByGroups()
    {
        $output = [];
        $time = $this->time->get();
        foreach (array_keys($this->services) as $service) {
            $proxyStatistic = new ProxyStatistic();
            foreach ($this->proxyList[$service] as $proxy) {
                $proxyStatistic->add($proxy->getTag(), $proxy->getMetric(), $proxy->getAllowUseAfter() < $time);
                $output[$service][$proxy->getTag()] = $proxy->getMetric();
            }
            $output[$service] = $proxyStatistic->export();
        }
        return $output;
    }

    public function getServices()
    {
        return $this->services;
    }

    /**
     * @return ProxyCollection[]
     */
    public function getProxyList(): array
    {
        return $this->proxyList;
    }
}