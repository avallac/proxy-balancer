<?php

namespace AVAllAC\ProxyBalancer\Model;

class ProxyCollection implements \Iterator
{
    private $position;
    private $proxy = [];

    public function __construct()
    {
        $this->position = 0;
    }

    /**
     * @return Proxy[]
     */
    public function get()
    {
        return $this->proxy;
    }

    public function add(Proxy $proxy)
    {
        $this->proxy[] = $proxy;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current(): Proxy
    {
        return $this->proxy[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position++;
    }

    public function valid(): bool
    {
        return isset($this->proxy[$this->position]);
    }

    public function sort()
    {
        usort($this->proxy, function (Proxy $a, Proxy $b) {
            return $a->getMetric() > $b->getMetric();
        });

    }
}