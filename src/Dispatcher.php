<?php

namespace Polus\Adr;

use Aura\Payload\Payload;
use Aura\Payload_Interface\PayloadStatus;
use Aura\Router\Route;
use Polus\DispatchInterface;
use Polus\ResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Dispatcher implements DispatchInterface
{
    protected $resolver;

    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param Route $route
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function dispatch(Route $route, ServerRequestInterface $request, ResponseInterface $response)
    {
        $action = $route->handler;
        if (!($action instanceof Action)) {
            return $response->withStatus(500);
        }
        $domain = false;
        if ($action->getDomain()) {
            $domain = $this->resolver->resolve($action->getDomain());
        }
        if (is_callable($domain)) {
            $input = $this->resolver->resolve($action->getInput());
            $payload = $domain($input($request));
        } else {
            $payload = new Payload();
            $payload->setStatus(PayloadStatus::SUCCESS);
        }

        $responder = $this->resolver->resolve($action->getResponder());

        return $responder($request, $response, $payload);
    }
}
