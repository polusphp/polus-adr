<?php

namespace Polus\Adr\_Config;

use Aura\Di\Container;
use Aura\Router\RouterContainer;
use Aura\Router\Rule;
use Middlewares\JsonPayload;
use Middlewares\UrlEncodePayload;
use Middlewares\Whoops;
use Polus\Adr\ActionDispatcher;
use Polus\Adr\ActionHandlerFactory;
use Polus\Adr\Exception\MissingConfigValue;
use Polus\Adr\Resolver;
use Polus\Adr\ResponseHandler\CliResponseHandler;
use Polus\Adr\ResponseHandler\HttpResponseHandler;
use Polus\Middleware\HttpError;
use Polus\Middleware\ProductionErrorHandler;
use Polus\Middleware\Router;
use Polus\Middleware\Status404;
use Polus\Polus_Interface\ResolverInterface;
use Polus\Polus_Interface\ConfigInterface;
use Polus\Router\AliasRule;
use Polus\Router\Route;
use Psr\Container\ContainerInterface;

class Common implements ConfigInterface
{
    public function config(ContainerInterface $container): ContainerInterface
    {
        if (!$container instanceof Container) {
            throw new \InvalidArgumentException("Config class not meant to be invoke outside polus core");
        }
        $di = $container;

        if (!$container->has('polus/adr:middlewares')) {
            $di->set('polus/adr:middlewares', function () use ($container) {
                $queue = [];
                if ($container->has('mode:middlewares')) {
                    $queue = $container->get('mode:middlewares');
                }
                if ($container->has('polus/adr:whoops')) {
                    $queue[] = $container->get('polus/adr:whoops');
                }
                if ($container->has('mode:middlewares:preRouter')) {
                    $queue = array_merge($queue, $container->get('mode:middlewares:preRouter'));
                }

                if ($container->has('polus/adr:http_error_handler')) {
                    $queue[] = $container->get('polus/adr:http_error_handler');
                } else {
                    if (!$container->has('polus/adr:stream_factory')) {
                        throw new MissingConfigValue("Polus needs a stream factory");
                    }
                    $queue[] = new HttpError($container->get('polus/adr:stream_factory'));
                }

                $queue[] = new Router($container->get('polus/adr:router_container'));
                if ($container->has('mode:middlewares:postRouter')) {
                    $queue = array_merge($queue, $container->get('mode:middlewares:postRouter'));
                }
                $queue[] = new UrlEncodePayload();
                $queue[] = new JsonPayload();
                if ($container->has('mode:middlewares:preDispatcher')) {
                    $queue = array_merge($queue, $container->get('mode:middlewares:preDispatcher'));
                }
                return $queue;
            });
        } else {
            $di->set('polus/adr:middlewares', $container->get('polus/adr:middlewares'));
        }

        if (!$container->has('polus/adr:response_handler')) {
            if (php_sapi_name() === 'cli') {
                $di->set('polus/adr:response_handler', new CliResponseHandler());
            } else {
                $di->set('polus/adr:response_handler', new HttpResponseHandler());
            }
        } else {
            $di->set('polus/adr:response_handler', $container->get('polus/adr:response_handler'));
        }

        if (!$container->has('polus/adr:response_factory')) {
            throw new MissingConfigValue("Polus needs a response factory");
        }
        if (!$container->has('polus/adr:request_factory')) {
            throw new MissingConfigValue("Polus needs a request factory");
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
        if (!$container->has('polus/adr:action_handler_factory')) {
            $di->set('polus/adr:action_handler_factory', $di->lazyNew(ActionHandlerFactory::class, [
                'responseFactory' => $di->lazyGet('polus/adr:response_factory')
            ]));
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
        $di->set('polus/adr:dispatcher', $di->lazyNew(ActionDispatcher::class));

        return $di;
    }
}
