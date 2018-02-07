<?php
declare(strict_types=1);

namespace Polus\Adr;

use Aura\Di\Container;
use Aura\Router\Map;
use Aura\Router\Route;
use Aura\Router\RouterContainer;
use BadMethodCallException;
use Ellipse\Dispatcher;
use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\ServerRequestFactoryInterface;
use Polus\Adr\_Config\Common;
use Polus\Config\ContainerBuilder;
use Polus\Middleware;
use Polus\Polus_Interface\DispatchInterface;
use Polus\Polus_Interface\ResolverInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class App
 *
 * @method Route get(string $route, mixed $domain)
 * @method Route post(string $route, mixed $domain)
 * @method Route patch(string $route, mixed $domain)
 * @method Route delete(string $route, mixed $domain)
 * @method Route attach(string $route, callable $domain)
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
     * @var ResponseHandlerInterface
     */
    protected $responseHandler;

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var ServerRequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var ActionHandlerFactory
     */
    protected $actionHandlerFactory;

    /**
     * @param string $vendorNs
     * @param string $mode
     * @throws \Aura\Di\Exception\ServiceNotFound
     * @throws \Aura\Di\Exception\SetterMethodNotFound
     */
    public function __construct($vendorNs, $mode = 'production')
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
        $this->responseHandler = $this->container->get('polus/adr:response_handler');

        $this->responseFactory = $this->container->get('polus/adr:response_factory');
        $this->requestFactory = $this->container->get('polus/adr:request_factory');

        $this->actionHandlerFactory = $this->container->get('polus/adr:action_handler_factory');

        $this->resolver = $this->container->get('polus/adr:dispatch_resolver');

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

    public function run()
    {
        $this->request = $this->requestFactory->createServerRequestFromArray($_SERVER);
        $this->middlewares[] = new Middleware\Dispatcher($this->getDispatcher());
        $middlewareDispatcher = new Dispatcher(new Dispatcher\FallbackResponse($this->responseFactory->createResponse(404)), $this->middlewares);

        $response = $middlewareDispatcher->handle($this->request);

        $this->responseHandler->handle($response);
    }

    public function __call($method, $args)
    {
        $allowed = ['get', 'post', 'put', 'delete', 'patch', 'head', 'options'];
        if (in_array($method, $allowed)) {
            $path = $args[0];
            $action = $args[1];

            if (!$action instanceof ActionHandler) {
                if (!($action instanceof Action)) {
                    $action = new Action($action, $args[2] ?? null, $args[3] ?? null);
                }

                $action = $this->actionHandlerFactory->newInstance($action);
            }

            if ($args[2] instanceof \Traversable || is_array($args[2])) {
                $action = new Dispatcher($action, $args[2]);
            }

            return $this->map->$method(md5($method . $path), $path, $action);
        } elseif ($method === 'attach') {
            $pathPrefix = $args[0];
            $clb = $args[1];
            if (!is_callable($clb)) {
                throw new \Exception("Invalid argument most be callable");
            }
            $middlewares = [];
            if ($args[2] instanceof \Traversable || is_array($args[2])) {
                $middlewares = $args[2];
            }

            return $this->map->attach(md5($pathPrefix), $pathPrefix, function($map) use($middlewares, $clb) {
                $clb($map, $middlewares);
            });
        }
        throw new BadMethodCallException();
    }
}
