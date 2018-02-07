<?php

namespace Polus\Adr;

use Interop\Http\Factory\ResponseFactoryInterface;
use Polus\Polus_Interface\ResolverInterface;

class ActionHandlerFactory
{
    private $responseFactory;
    private $resolver;

    public function __construct(ResolverInterface $resolver, ResponseFactoryInterface $responseFactory)
    {
        $this->resolver = $resolver;
        $this->responseFactory = $responseFactory;
    }

    public function newInstance(ActionInterface $action): ActionHandler
    {
        return new ActionHandler($action, $this->resolver, $this->responseFactory);
    }
}