<?php
namespace Polus\Adr\Test\Domain\InputTest;

use Psr\Http\Message\ServerRequestInterface;

class Input
{
    public function __invoke(ServerRequestInterface $request)
    {
        return [
            'name' => $request->getAttribute('id'),
            'date' => date('c'),
        ];
    }
}
