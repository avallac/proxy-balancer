<?php

namespace AVAllAC\ProxyBalancer\Controller;

use AVAllAC\ProxyBalancer\Model\Proxy;
use AVAllAC\ProxyBalancer\Model\ProxyCollection;
use AVAllAC\ProxyBalancer\Service\ProxyManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class RootControllerTest extends TestCase
{
    public function testStatus()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $proxyManager = $this->createMock(ProxyManager::class);
        $proxyManager->method('getServices')->willReturn(['p1' => 1, 'p2' => 2]);
        $proxyManager->method('countAllowed')->will($this->returnValueMap([['p1', 2], ['p2', 4]]));
        $rootController = new RootController($proxyManager);
        $this->assertEquals('{"p1":"2","p2":"4"}', $rootController->services($request));
    }

    public function testDebug()
    {
        $collection = new ProxyCollection();
        $collection->add(new Proxy('uri1', 'default', 1000000, 100));
        $collection->add(new Proxy('uri3', 'default', 1000025, 105));
        $request = $this->createMock(ServerRequestInterface::class);
        $proxyManager = $this->createMock(ProxyManager::class);
        $proxyManager->method('getProxyList')->willReturn(['p1' => $collection]);
        $rootController = new RootController($proxyManager);
        $b1 = '{"uri":"uri1","allowUseAfter":"1970-01-12T13:46:40+00:00","metric":100}';
        $b2 = '{"uri":"uri3","allowUseAfter":"1970-01-12T13:47:05+00:00","metric":105}';
        $this->assertEquals('{"p1":[' . $b1 . ',' . $b2 . ']}', $rootController->debug($request));
    }

    public function testGet()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $proxyManager = $this->createMock(ProxyManager::class);
        $proxyManager->method('get')->with('p1')->willReturn('data');
        $rootController = new RootController($proxyManager);
        $this->assertEquals('data', $rootController->get($request, 'p1'));
    }

    public function testComplaint()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(['uri' => 'uri1']);
        $proxyManager = $this->createMock(ProxyManager::class);
        $proxyManager->method('get')->with('p1')->willReturn('data');
        $rootController = new RootController($proxyManager);
        $this->assertEquals('"ok"', $rootController->complaint($request, 'p1'));
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function testComplaintException()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $proxyManager = $this->createMock(ProxyManager::class);
        $rootController = new RootController($proxyManager);
        $this->assertEquals('"ok"', $rootController->complaint($request, 'p1'));
    }

    public function testReport()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(['uri' => 'uri1', 'result' => '10']);
        $proxyManager = $this->createMock(ProxyManager::class);
        $proxyManager->method('get')->with('p1')->willReturn('data');
        $rootController = new RootController($proxyManager);
        $this->assertEquals('"ok"', $rootController->report($request, 'p1'));
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function testReportExceptionUri()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $proxyManager = $this->createMock(ProxyManager::class);
        $rootController = new RootController($proxyManager);
        $this->assertEquals('"ok"', $rootController->report($request, 'p1'));
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function testReportExceptionResult()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(['uri' => 'uri1']);
        $proxyManager = $this->createMock(ProxyManager::class);
        $rootController = new RootController($proxyManager);
        $this->assertEquals('"ok"', $rootController->report($request, 'p1'));
    }
}