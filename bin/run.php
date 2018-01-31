<?php

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server;

require __DIR__ . '/../vendor/autoload.php';

$app = new \AVAllAC\ProxyBalancer\App();

$loop = Factory::create();
$server = new Server(function (ServerRequestInterface $request) use ($app) {
    try {
        $response = $app->handle($request);
        return new Response(200, ['Content-Type' => 'text/plain'], $response);
    } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
        return new Response(404, ['Content-Type' => 'text/plain'], $e->getMessage());
    } catch (\AVAllAC\ProxyBalancer\Exception\UnauthorizedException $e) {
        return new Response(401, ['Content-Type' => 'text/plain'], '401 Unauthorized');
    } catch (\Throwable $e) {
        return new Response(503, ['Content-Type' => 'text/plain'], $e->getMessage());
    }
});

$loop->addPeriodicTimer(3600, function () use ($app) {
    $export = $app[\AVAllAC\ProxyBalancer\Model\ProxyManager::class]->exportMetric();
    file_put_contents(__DIR__ . '/../statistic.json', json_encode($export));
});

$socket = new \React\Socket\Server('0.0.0.0:8080', $loop);
$server->listen($socket);
echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
$loop->run();
