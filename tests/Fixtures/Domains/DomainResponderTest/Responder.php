<?php

namespace Fixtures\Domains\DomainResponderTest;

use Aura\Payload_Interface\PayloadInterface;
use Polus\Adr\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Responder implements ResponderInterface
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ) {
        return $response;
    }
}