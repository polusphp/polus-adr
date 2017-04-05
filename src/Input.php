<?php
namespace Polus\Adr;

use Psr\Http\Message\ServerRequestInterface;

class Input
{
    public function __invoke(ServerRequestInterface $request)
    {
        return array_replace(
            (array) $request->getQueryParams(),
            (array) $request->getParsedBody(),
            (array) $request->getUploadedFiles(),
            (array) $request->getCookieParams(),
            (array) $request->getAttributes()
        );
    }
}
