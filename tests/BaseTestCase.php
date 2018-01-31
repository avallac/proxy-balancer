<?php

namespace AVAllAC\ProxyBalancer\Tests;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    public function tearDown()
    {
        \Mockery::close();
    }

    protected function getMethod($class, $methodName)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}
