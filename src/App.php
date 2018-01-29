<?php

namespace Polus\Adr;

use Aura\Di\Container;
use Aura\Router\Map;
use Aura\Router\Route;
use Aura\Router\RouterContainer;
use BadMethodCallException;
use Northwoods\Broker\Broker;
use Polus\Adr\_Config\Common;
use Polus\Config\ContainerBuilder;
use Polus\Middleware;
use Polus\Polus_Interface\DispatchInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

/**
 * Class App
 *
 * @method Route get(string $route, mixed $domain)
 * @method Route post(string $route, mixed $domain)
 * @method Route patch(string $route, mixed $domain)
 * @method Route delete(string $route, mixed $domain)
 */
class App
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var RouterContainer
     *
     */
    protected $routerContainer;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var array
     */
    protected $configs = [];

    /**
     * @var array
     */
    protected $modeMap = [];

    /**
     * Psr7 middleware queue
     * @var array
     */
    protected $middlewares = [];

    /**
     * Dispatcher
     * @var DispatchInterface
     */
    protected $dispatcher;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param string $vendorNs
     * @param string $mode
     * @param ServerRequestInterface|null $request
     * @throws \Aura\Di\Exception\ServiceNotFound
     * @throws \Aura\Di\Exception\SetterMethodNotFound
     */
    public function __construct($vendorNs, $mode = 'production', ServerRequestInterface $request = null)
    {
        $configs = [];
        if (isset($this->modeMap[$mode])) {
            $configs[] = $this->modeMap[$mode];
        } else {
            if ($mode == 'development') {
                $configs[] = $vendorNs . '\_Config\Dev';
            } else {
                $configs[] = $vendorNs . '\_Config\Production';
            }
        }
        $configs[] = $vendorNs . '\_Config\Common';
        $configs[] = new Common();

        $builder = new ContainerBuilder();
        $this->container = $builder->newConfiguredInstance($configs, true);
        $this->dispatcher = $this->container->get('polus/adr:dispatcher');
        $this->routerContainer = $this->container->get('polus/adr:router_container');
        $this->request = $request;
        $this->map = $this->routerContainer->getMap();
        $this->middlewares = $this->container->get('polus/adr:middlewares');
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getDispatcher(): DispatchInterface
    {
        return $this->dispatcher;
    }

    public function getMap(): Map
    {
        return $this->map;
    }

    /**
     * For testing purpose
     * @param ServerRequestInterface $request
     */
    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function addMiddleware(callable $middleware, callable $condition = null)
    {
        $this->middlewares[] = [$middleware, $condition];
    }

    /**
     * @param callable $rule
     * @param string $position
     * @return boolean
     */
    public function addRouterRule(callable $rule, string $position = 'append'): bool
    {
        $ruleIterator = $this->routerContainer->getRuleIterator();
        $ruleIterator->$position($rule);
        return true;
    }

    public function run(): ResponseInterface
    {
        $broker = new Broker($this->container);
        foreach ($this->middlewares as $middleware) {
            if (is_array($middleware)) {
                $broker->when($middleware[1], $middleware[0]);
            } else {
                $broker->always($middleware);
            }
        }
        $broker->always(new Middleware\Dispatcher($this->getDispatcher()));

        return $broker->handle($this->request, function () {
            $response = new Response();
            return $response->withStatus(404);
        });
    }

    public function __call($method, $args)
    {
        $allowed = ['get', 'post', 'put', 'delete', 'patch', 'head', 'options'];
        if (in_array($method, $allowed)) {
            $path = $args[0];
            $domain = $args[1];
            if (!($domain instanceof Action)) {
                $domain = new Action($domain, $args[2] ?? null, $args[3] ?? null);
            }

            return $this->map->$method(md5($method . $path), $path, $domain);
        }
        throw new BadMethodCallException();
    }
}
