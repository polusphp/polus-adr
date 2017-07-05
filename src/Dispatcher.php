<?php

namespace Polus\Adr;

use Aura\Payload\Payload;
use Aura\Payload_Interface\PayloadStatus;
use Aura\Router\Route;
use DomainException;
use InvalidArgumentException;
use Polus\Polus_Interface\DispatchInterface;
use Polus\Polus_Interface\ResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Dispatcher implements DispatchInterface
{
    protected $resolver;

    public function __construct(ResolverInterface $resolver)
    {
        if (is_null($resolver)) {
            throw new InvalidArgumentException('Missing resolver');
        }

        $this->resolver = $resolver;
    }

    public function dispatch(
        Route $route,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $action = $route->handler;
        if (!($action instanceof Action)) {
            return $response->withStatus(500);
        }

        $domain = false;
        if ($action->getDomain()) {
            $domain = $action->getDomain();
            if (!is_callable($domain)) {
                $domain = $this->resolver->resolve($domain);
            }
        }

        if (is_callable($domain)) {
            try {
                $input = $action->getInput();
                if (!is_callable($input)) {
                    $input = $this->resolver->resolve($input);
                }
                $payload = $domain($input($request));
            } catch (DomainException $de) {
                $payload = new Payload();
                $payload->setStatus(PayloadStatus::FAILURE);
                $payload->setMessages($de->getMessage());
                $payload->setOutput([
                    'exception' => [
                        'message' => $de->getMessage(),
                        'code' => $de->getCode(),
                    ]
                ]);
                $payload->setInput($input ?? []);
            }
        } else {
            $payload = new Payload();
            $payload->setStatus(PayloadStatus::SUCCESS);
        }

        $responder = $this->resolver->resolve($action->getResponder());

        return $responder($request, $response, $payload);
    }
}
