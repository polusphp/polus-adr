<?php

namespace Fixtures\Domains;

use Aura\Payload\Payload;

class DispatchResponderTest
{
    public function __invoke()
    {
        return (new Payload())->setOutput('Custom responder output');
    }
}