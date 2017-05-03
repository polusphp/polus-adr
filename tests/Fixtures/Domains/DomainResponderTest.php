<?php

namespace Fixtures\Domains;

use Aura\Payload\Payload;

class DomainResponderTest
{
    public function __invoke()
    {
        return new Payload();
    }
}