<?php

namespace Polus\Adr\Test\Domain;

use Aura\Payload\Payload;
use Aura\Payload_Interface\PayloadStatus;

class InputTest
{
    public function __invoke(array $data)
    {
        $payload = new Payload();
        return $payload
            ->setStatus(PayloadStatus::SUCCESS)
            ->setOutput([
                'message' => 'Input test: ' . $data['name'],
                'date' => $data['date'],
            ]);
    }
}
