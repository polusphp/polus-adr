<?php

namespace Test\_Config;

use Aura\Di\Container;
use Aura\Di\ContainerConfigInterface;


class Common implements  ContainerConfigInterface
{

    /**
     *
     * Define params, setters, and services before the Container is locked.
     *
     * @param Container $di The DI container.
     *
     * @return null
     *
     */
    public function define(Container $di)
    {
        // TODO: Implement define() method.
    }

    /**
     *
     * Modify service objects after the Container is locked.
     *
     * @param Container $di The DI container.
     *
     * @return null
     *
     */
    public function modify(Container $di)
    {
        // TODO: Implement modify() method.
    }
}