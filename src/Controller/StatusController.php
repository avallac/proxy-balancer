<?php declare(strict_types=1);

namespace AVAllAC\ProxyBalancer\Controller;

use AVAllAC\ProxyBalancer\Service\MicroTime;
use AVAllAC\ProxyBalancer\Service\ProxyManager;

class StatusController
{
    private $microTime;
    private $proxyManager;

    public function __construct(MicroTime $microTime, ProxyManager $proxyManager)
    {
        $this->microTime = $microTime;
        $this->proxyManager = $proxyManager;
    }

    public function status() : string
    {
        return json_encode([
            'uptime' => $this->microTime->get() - $this->microTime->getInitTime(),
            'statistic' => $this->proxyManager->getStatisticByGroups()
        ]);
    }
}
