<?php

namespace AVAllAC\ProxyBalancer\Model;

class Proxy
{
    private $uri = '';
    private $allowUseAfter = 0;
    private $metric = 0.0;
    private $tag;

    public function __construct(string $uri, string $tag, int $allowUseAfter, float $metric = 0.0)
    {
        $this->uri = $uri;
        $this->metric = $metric;
        $this->allowUseAfter = $allowUseAfter;
        $this->tag = $tag;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getAllowUseAfter(): int
    {
        return $this->allowUseAfter;
    }

    public function setAllowUseAfter(int $allowUseAfter): void
    {
        $this->allowUseAfter = $allowUseAfter;
    }

    public function getMetric(): float
    {
        return $this->metric;
    }

    public function setMetric(float $metric): void
    {
        $this->metric = $metric;
    }

    public function getTag(): string
    {
        return $this->tag;
    }
}
