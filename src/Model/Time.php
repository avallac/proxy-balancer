<?php

namespace AVAllAC\ProxyBalancer\Model;

class Time
{
    public function get() : float
    {
        return microtime(true);
    }
}
