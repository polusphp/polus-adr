<?php

namespace Polus\Adr\Test\Domain;

use Aura\Payload\Payload;
use Aura\Payload_Interface\PayloadStatus;

class Test
{
    public function __invoke(array $data)
    {
        $payload = new Payload();
        return $payload
            ->setStatus(PayloadStatus::SUCCESS)
            ->setOutput([
                'message' => 'Hello ' . $data['id'],
            ]);
    }
}
