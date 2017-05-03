<?php

namespace Fixtures\Domains;

use Aura\Payload\Payload;
use Aura\Payload_Interface\PayloadStatus;

class DispatchInputTest
{
    public function __invoke($input)
    {
        $payload = new Payload();
        $payload->setStatus(PayloadStatus::SUCCESS);

        return $payload->setOutput([
            'custom_input' => $input['input']
        ]);
    }
}