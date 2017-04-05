<?php

namespace Polus\Adr\Test\Responder;

use Aura\Payload_Interface\PayloadInterface;
use Polus\Adr\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ResponderTest implements ResponderInterface
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ) {
        $response = $response->withHeader('Content-Type', 'application/json');
        // Overwrite the body instead of making a copy and dealing with the stream.
        $response->getBody()->write(json_encode([
            'custom-responder' => true,
        ]));

        return $response;
    }
}
