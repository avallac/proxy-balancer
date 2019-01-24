<?php

namespace AVAllAC\ProxyBalancer\Model;

class ProxyStatistic
{
    private $proxy = [];

    public function add(string $tag, float $metric, bool $allowed)
    {
        $this->proxy[$tag] = [
            'metric' => $metric,
            'allowed' => $allowed
        ];
    }

    public function export()
    {
        $allowed = 0;
        $metric = 0;
        $count = count($this->proxy);
        foreach ($this->proxy as $item) {
            $metric += $item['metric'];
            $allowed ++;
        }
        return [
            'allowed' => $allowed,
            'allowedPercent' => sprintf('%.2f', $allowed/$count * 100),
            'metric' => sprintf('%.2f', $metric/$count),
        ];
    }
}