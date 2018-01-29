<?php

namespace Test\_Config;

use Polus\Polus_Interface\ConfigInterface;
use Psr\Container\ContainerInterface;

class Common implements ConfigInterface
{
    public function config(ContainerInterface $container): ContainerInterface
    {
        return $container;
    }
}