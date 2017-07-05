<?php

namespace Polus\Adr;

use Aura\Di\Container;
use Aura\Di\Factory;
use Aura\Router\Map;
use Aura\Router\Route;
use Aura\Router\RouterContainer;
use BadMethodCallException;
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
     */
    public function __construct($vendorNs, $mode = 'production', ServerRequestInterface $request = null)
    {
        $this->container = new Container(new Factory);

        if (isset($this->modeMap[$mode])) {
            $this->addConfig($this->modeMap[$mode]);
        } else {
            if ($mode == 'development') {
                $this->addConfig($vendorNs . '\_Config\Dev');
            } else {
                $this->addConfig($vendorNs . '\_Config\Production');
            }
        }
        $this->addConfig($vendorNs . '\_Config\Common');
        $this->addConfig('Polus\Adr\_Config\Common');

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

    public function addMiddleware(callable $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * @param string $class
     */
    public function addConfig($class)
    {
        if (class_exists($class)) {
            $config = $this->container->newInstance($class);
            $config->define($this->container);
            $this->configs[] = $config;
        }
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
        $relayBuilder = $this->container->get('relay');
        $queue = $this->middlewares;
        $queue[] = new Middleware\Dispatcher($this->getDispatcher());
        $relay = $relayBuilder->newInstance($queue);

        $response = new Response();
        $response = $relay($this->request, $response);

        return $response;
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

            return $this->map->$method(md5($path), $path, $domain);
        }
        throw new BadMethodCallException();
    }
}
