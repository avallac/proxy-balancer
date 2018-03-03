<?php declare(strict_types=1);

namespace AVAllAC\ProxyBalancer\Controller;

use AVAllAC\ProxyBalancer\Service\MicroTime;

class StatusController
{
    private $microTime;

    public function __construct(MicroTime $microTime)
    {
        $this->microTime = $microTime;
    }

    public function status() : string
    {
        return json_encode([
            'uptime' => $this->microTime->get() - $this->microTime->getInitTime()
        ]);
    }
}
