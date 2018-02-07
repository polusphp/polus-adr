<?php

namespace Polus\Adr\ResponseHandler;

use Polus\Adr\ResponseHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class HttpResponseHandler implements ResponseHandlerInterface
{
    public function handle(ResponseInterface $response): void
    {
        $version = $response->getProtocolVersion();
        $status = $response->getStatusCode();
        $phrase = $response->getReasonPhrase();

        header("HTTP/{$version} {$status} {$phrase}");

        foreach ($response->getHeaders() as $name => $values) {
            $name = str_replace('-', ' ', $name);
            $name = ucwords($name);
            $name = str_replace(' ', '-', $name);
            foreach ($values as $value) {
                header("{$name}: {$value}", false);
            }
        }

        $stream = $response->getBody();
        $stream->rewind();
        while (! $stream->eof()) {
            echo $stream->read(8192);
        }
    }
}
