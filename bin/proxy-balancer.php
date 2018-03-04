<?php

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server;

require __DIR__ . '/../vendor/autoload.php';

$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple['statistic'] = json_decode(file_get_contents(__DIR__ . '/../statistic.json'), true) ?? [];
$pimple['proxy_list'] =  explode("\n", trim(file_get_contents(__DIR__ . '/../etc/proxy.list')));
$pimple->register(new \AVAllAC\ProxyBalancer\Provider\ProxyManagerProvider());
$pimple->register(new \AVAllAC\ProxyBalancer\Provider\RoutingProvider());
$pimple->register(new \AVAllAC\ProxyBalancer\Provider\KernelProvider());
$pimple->register(new \AVAllAC\ProxyBalancer\Provider\MicroTimeProvider());
$pimple->register(new \AVAllAC\ProxyBalancer\Provider\ControllersProvider());

$loop = Factory::create();
$server = new Server(function (ServerRequestInterface $request) use ($pimple) {
    try {
        $response = $pimple['kernel']->handle($request);
        return new Response(200, ['Content-Type' => 'text/plain'], $response);
    } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
        return new Response(404, ['Content-Type' => 'text/plain'], $e->getMessage());
    } catch (\Symfony\Component\Routing\Exception\InvalidParameterException $e) {
        return new Response(400, ['Content-Type' => 'text/plain'], $e->getMessage());
    } catch (\AVAllAC\ProxyBalancer\Exception\UnauthorizedException $e) {
        return new Response(401, ['Content-Type' => 'text/plain'], '401 Unauthorized');
    } catch (\Throwable $e) {
        return new Response(503, ['Content-Type' => 'text/plain'], $e->getMessage());
    }
});

$loop->addPeriodicTimer(3600, function () use ($pimple) {
    $export = $pimple['proxyManager']->exportMetric();
    file_put_contents(__DIR__ . '/../statistic.json', json_encode($export));
});

$socket = new \React\Socket\Server('0.0.0.0:' . $pimple['config']['listenPort'], $loop);
$server->listen($socket);
print 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
if (!$pimple['config']['debug']) {
    $child_pid = pcntl_fork();
    if ($child_pid) {
        exit();
    }
    print "My pid: " . getmypid() . PHP_EOL;
    posix_setsid();
    fclose(STDIN);
    fclose(STDOUT);
    fclose(STDERR);
}
$loop->run();
