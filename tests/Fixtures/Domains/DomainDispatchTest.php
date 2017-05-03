<?php

namespace Fixtures\Domains;

use Aura\Payload\Payload;
use Aura\Payload_Interface\PayloadStatus;

class DomainDispatchTest
{
    public function __invoke()
    {
        return (new Payload())->setStatus(PayloadStatus::NOT_AUTHENTICATED)->setOutput(['test' => false]);
    }
}