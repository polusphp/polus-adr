<?php

namespace Polus\Adr;

use Polus\Polus_Interface\ResolverInterface;

class Resolver implements ResolverInterface
{
    protected $resolver;

    public function __construct(callable $resolver)
    {
        $this->resolver = $resolver;
    }

    public function resolve($cls)
    {
        $resolver = $this->resolver;
        return $resolver($cls);
    }
}
