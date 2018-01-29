<?php

namespace Polus\Adr\_Config;

use Aura\Di\Container;
use Aura\Router\RouterContainer;
use Aura\Router\Rule;
use Franzl\Middleware\Whoops\Middleware;
use Polus\Adr\Dispatcher;
use Polus\Adr\Resolver;
use Polus\Middleware\CliResponseSender;
use Polus\Middleware\Router;
use Polus\Middleware\Status404;
use Polus\Polus_Interface\ResolverInterface;
use Polus\Polus_Interface\ConfigInterface;
use Polus\Router\AliasRule;
use Polus\Router\Route;
use Relay\Middleware\FormContentHandler;
use Relay\Middleware\JsonContentHandler;
use Relay\Middleware\ResponseSender;
use Psr\Container\ContainerInterface;

class Common implements ConfigInterface
{
    public function config(ContainerInterface $container): ContainerInterface
    {
        if (!$container instanceof Container) {
            throw new \InvalidArgumentException("Config class not meant to be invoke outside core");
        }
        $di = $container;

        if (!$container->has('polus/adr:middlewares')) {
            $di->set('polus/adr:middlewares', function () use ($container) {
                $queue = [];
                if ($container->has('mode:middlewares')) {
                    $queue = $container->get('mode:middlewares');
                }
                if (php_sapi_name() !== 'cli') {
                    $queue[] = new ResponseSender();
                } else {
                    $queue[] = new CliResponseSender();
                }
                $queue[] = new Middleware();
                if ($container->has('mode:middlewares:preRouter')) {
                    $queue = array_merge($queue, $container->get('mode:middlewares:preRouter'));
                }
                $queue[] = new Router($container->get('polus/adr:router_container'));
                if ($container->has('mode:middlewares:postRouter')) {
                    $queue = array_merge($queue, $container->get('mode:middlewares:postRouter'));
                }
                $queue[] = new Status404();
                $queue[] = new FormContentHandler();
                $queue[] = new JsonContentHandler(true);
                if ($container->has('mode:middlewares:preDispatcher')) {
                    $queue = array_merge($queue, $container->get('mode:middlewares:preDispatcher'));
                }
                return $queue;
            });
        } else {
            $di->set('polus/adr:middlewares', $container->get('polus/adr:middlewares'));
        }

        if (!$container->has('polus/adr:dispatch_resolver')) {
            $di->set('polus/adr:dispatch_resolver', $di->lazyNew(Resolver::class, [
                'resolver' => function ($cls) use ($di) {
                    return $di->newInstance($cls);
                },
            ]));
        } else {
            $di->set('polus/adr:dispatch_resolver', $container->get('polus/adr:dispatch_resolver'));
        }

        if (!$container->has('polus/adr:router_container')) {
            $di->set('polus/adr:router_container', function () use ($container) {
                $basePath = $container->has('basePath') ? $container->get('basePath') : null;

                $routerContainer = new RouterContainer($basePath);
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
        } else {
            $di->set('polus/adr:router_container', $container->get('polus/adr:router_container'));
        }

        $di->types[ResolverInterface::class] = $di->lazyGet('polus/adr:dispatch_resolver');
        $di->set('polus/adr:dispatcher', $di->lazyNew(Dispatcher::class));

        return $di;
    }
}
