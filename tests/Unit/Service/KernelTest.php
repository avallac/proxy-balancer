<?php

namespace AVAllAC\ProxyBalancer\Service;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class KernelTest extends TestCase
{
    public function testHandle()
    {
        $function = function () {
            return 'ok';
        };
        $uri = $this->createMock(UriInterface::class);
        $router = $this->createMock(UrlMatcher::class);
        $router->method('match')->willReturn(['_controller' => $function, '_route' => 'test']);
        $kernel = new Kernel($router, 'user', 'pass');
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);
        $request->method('getHeader')->willReturn(['Basic dXNlcjpwYXNz']);
        $this->assertEquals('ok', $kernel->handle($request));
    }

    /**
     * @expectedException \AVAllAC\ProxyBalancer\Exception\UnauthorizedException
     */
    public function testHandleException()
    {
        $router = $this->createMock(UrlMatcher::class);
        $router->method('match')->willReturn(['_controller' => null, '_route' => 'test']);
        $kernel = new Kernel($router, 'user', 'pass');
        $request = $this->createMock(ServerRequestInterface::class);
        $kernel->handle($request);
    }
}
