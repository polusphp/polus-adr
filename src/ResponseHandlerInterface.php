<?php
declare(strict_types=1);

namespace Polus\Adr;

use Psr\Http\Message\ResponseInterface;

interface ResponseHandlerInterface
{
    public function handle(ResponseInterface $response): void;
}
