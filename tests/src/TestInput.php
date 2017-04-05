<?php
namespace Polus\Adr\Test;

use Psr\Http\Message\ServerRequestInterface;

class TestInput
{
    public function __invoke(ServerRequestInterface $request)
    {
        return [
            'id' => (int) $request->getAttribute('id'),
            'date' => date('c'),
        ];
    }
}
