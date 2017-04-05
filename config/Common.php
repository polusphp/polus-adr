<?php

namespace Polus\Adr\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Common extends Config
{
    public function define(Container $di)
    {
        $di->types['Polus\ResolverInterface'] = $di->lazyNew('Polus\DispatchResolver', [
            'resolver' => function ($cls) use ($di) {
                return $di->newInstance($cls);
            },
        ]);
        $di->set('polus/adr:dispatcher', $di->lazyNew('Polus\Adr\Dispatcher'));
    }
}
