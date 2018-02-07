<?php

namespace Polus\Adr\ResponseHandler;

use Polus\Adr\ResponseHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class CliResponseHandler implements ResponseHandlerInterface
{
    public function handle(ResponseInterface $response): void
    {
        $stream = $response->getBody();
        $stream->rewind();
        while (! $stream->eof()) {
            echo $stream->read(8192);
        }
    }
}
