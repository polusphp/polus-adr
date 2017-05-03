<?php

namespace Fixtures\Domains;

use Aura\Payload\Payload;

class EmptyDomain
{
    public function __invoke()
    {
        return new Payload();
    }
}