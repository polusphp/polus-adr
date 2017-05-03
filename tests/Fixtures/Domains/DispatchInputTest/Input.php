<?php

namespace Fixtures\Domains\DispatchInputTest;

class Input
{
    public function __invoke()
    {
        return [
            'input' => 'test'
        ];
    }
}