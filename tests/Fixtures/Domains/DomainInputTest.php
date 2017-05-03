<?php

namespace Fixtures\Domains;

use Aura\Payload\Payload;

class DomainInputTest
{
    public function __invoke($input)
    {
        return new Payload();
    }
}