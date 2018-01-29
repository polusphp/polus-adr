<?php

namespace Polus\Adr\_Config;

use Aura\Di\Container;
use Aura\Di\ContainerConfigInterface;
use Aura\Router\RouterContainer;
use Aura\Router\Rule;
use Franzl\Middleware\Whoops\Middleware;
use Polus\Adr\Dispatcher;
use Polus\Adr\Resolver;
use Polus\Middleware\CliResponseSender;
use Polus\Middleware\Router;
use Polus\Middleware\Status404;
use Polus\Polus_Interface\ResolverInterface;
use Polus\Router\AliasRule;
use Polus\Router\Route;
use Relay\Middleware\FormContentHandler;
use Relay\Middleware\JsonContentHandler;
use Relay\Middleware\ResponseSender;

class Common implements ContainerConfigInterface
{
    public function define(Container $di)
    {
        if (!isset($di->params[Router::class]['router'])) {
            $di->params[Router::class]['router'] = $di->lazyGet('polus/adr:router_container');
        }
        if (!$di->has('polus/adr:middlewares')) {
            $di->set('polus/adr:middlewares', function () use ($di) {
                $queue = [];
                if ($di->has('mode:middlewares')) {
                    $queue = $di->get('mode:middlewares');
                }
                if (php_sapi_name() !== 'cli') {
                    $queue[] = $di->newInstance(ResponseSender::class);
                } else {
                    $queue[] = $di->newInstance(CliResponseSender::class);
                }
                $queue[] = $di->newInstance(Middleware::class);
                if ($di->has('mode:middlewares:preRouter')) {
                    $queue = array_merge($queue, $di->get('mode:middlewares:preRouter'));
                }
                $queue[] = $di->newInstance(Router::class);
                if ($di->has('mode:middlewares:postRouter')) {
                    $queue = array_merge($queue, $di->get('mode:middlewares:postRouter'));
                }
                $queue[] = $di->newInstance(Status404::class);
                $queue[] = $di->newInstance(FormContentHandler::class);
                $queue[] = $di->newInstance(JsonContentHandler::class, [
                    'assoc' => true,
                ]);
                if ($di->has('mode:middlewares:preDispatcher')) {
                    $queue = array_merge($queue, $di->get('mode:middlewares:preDispatcher'));
                }
                return $queue;
            });
        }

        if (!$di->has('polus/adr:dispatch_resolver')) {
            $di->set('polus/adr:dispatch_resolver', $di->lazyNew(Resolver::class, [
                'resolver' => function ($cls) use ($di) {
                    return $di->newInstance($cls);
                },
            ]));
        }
        $di->types[ResolverInterface::class] = $di->lazyGet('polus/adr:dispatch_resolver');

        if (!$di->has('polus/adr:router_container')) {
            $di->set('polus/adr:router_container', function () use ($di) {
                $routerContainer = $di->newInstance(RouterContainer::class);
                $routerContainer->setRouteFactory(function () {
                    return new Route();
                });
                $routerContainer->getRuleIterator()->set([
                    new Rule\Secure(),
                    new Rule\Host(),
                    new AliasRule(),
                    new Rule\Allows(),
                    new Rule\Accepts(),
                ]);

                return $routerContainer;
            });
        }

        $di->set('polus/adr:dispatcher', $di->lazyNew(Dispatcher::class));
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
