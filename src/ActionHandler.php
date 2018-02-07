<?php

namespace Polus\Adr;

use Aura\Payload\Payload;
use Aura\Payload_Interface\PayloadStatus;
use DomainException;
use Interop\Http\Factory\ResponseFactoryInterface;
use Polus\Polus_Interface\ResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ActionHandler implements RequestHandlerInterface
{
    private $action;
    private $responseFactory;
    private $resolver;

    public function __construct(ActionInterface $action, ResolverInterface $resolver, ResponseFactoryInterface $responseFactory)
    {
        $this->action = $action;
        $this->resolver = $resolver;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $domain = false;
        if ($this->action->getDomain()) {
            $domain = $this->action->getDomain();
            if (!is_callable($domain)) {
                $domain = $this->resolver->resolve($domain);
            }
        }

        if (is_callable($domain)) {
            try {
                $input = $this->action->getInput();
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

        $responder = $this->resolver->resolve($this->action->getResponder());

        return $responder($request, $this->responseFactory->createResponse(), $payload);

    }
}