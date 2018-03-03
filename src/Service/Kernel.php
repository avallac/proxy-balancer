<?php declare(strict_types=1);

namespace AVAllAC\ProxyBalancer\Service;

use AVAllAC\ProxyBalancer\Exception\UnauthorizedException;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class Kernel
{
    private $router;
    private $username;
    private $password;

    public function __construct(UrlMatcherInterface $router, string $username, string $password)
    {
        $this->router = $router;
        $this->username = $username;
        $this->password = $password;
    }

    protected function authEncode()
    {
        return 'Basic ' . base64_encode($this->username . ':' . $this->password);
    }

    public function handle(ServerRequestInterface $request) : string
    {
        $authString = $this->authEncode();
        if ($authString === $request->getHeader('authorization')[0]) {
            $params = ['request' => $request];
            $route = $request->getUri()->getPath();
            $matched = $this->router->match($route);
            $params = array_merge($params, $matched);
            unset($params['_controller']);
            unset($params['_route']);
            return \call_user_func_array($matched['_controller'], $params);
        } else {
            throw new UnauthorizedException();
        }
    }
}