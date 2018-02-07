<?php
declare(strict_types=1);

namespace Polus\Adr;

use Aura\Router\Route;
use Polus\Polus_Interface\DispatchInterface;
use Polus\Polus_Interface\ResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ActionDispatcher implements DispatchInterface
{
    protected $resolver;

    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function dispatch(
        Route $route,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $action = $route->handler;
        if (!($action instanceof RequestHandlerInterface)) {
            return $response->withStatus(500);
        }
        return $action->handle($request);
    }
}
