<?php

namespace AVAllAC\ProxyBalancer\Model;

class ProxyStatistic
{
    private $proxy = [];

    public function add(string $tag, float $metric, bool $allowed)
    {
        $this->proxy[$tag] = $this->proxy[$tag] ?? [];
        $this->proxy[$tag][] = [
            'metric' => $metric,
            'allowed' => $allowed
        ];
    }

    public function export()
    {
        $result = [];
        foreach ($this->proxy as $tag => $array) {
            $allowed = 0;
            $metric = 0;
            $count = count($this->proxy);
            foreach ($array as $item) {
                $metric += $item['metric'];
                if ($item['allowed']) {
                    $allowed++;
                }
            }
            $result[$tag] = [
                'allowed' => $allowed,
                'allowedPercent' => sprintf('%.2f', $allowed/$count * 100),
                'metric' => sprintf('%.2f', $metric/$count),
            ];
        }
        return $result;
    }
}