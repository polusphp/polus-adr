<?php

namespace Fixtures\Domains\DispatchResponderTest;

use Aura\Payload_Interface\PayloadInterface;
use Polus\Adr\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;

class Responder implements ResponderInterface
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ) {
        return new TextResponse($payload->getOutput());
    }
}