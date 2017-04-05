<?php

namespace Polus\Adr;

use Aura\Di\Container;
use Aura\Di\Factory;
use Aura\Router\Map;
use Aura\Router\RouterContainer;
use Polus\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class App
{
    /**
     * @var Sender
     */
    public $sender;

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
     * @var mixed
     */
    protected $errorHandler;

    /**
     * @var string
     */
    protected $config_dir = '';

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
     * @var Polus\DispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param string $vendorNs
     * @param string $mode
     */
    public function __construct($vendorNs, $mode = 'production', $request = null)
    {
        $this->container = new container(new Factory);

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
        $this->addConfig('Polus\_Config\Common');
        $this->addConfig('Polus\Adr\_Config\Common');

        $this->dispatcher = $this->container->get('polus/adr:dispatcher');
        $this->routerContainer = $this->container->get('polus:router_container');
        $this->request = $request ?? $this->container->get('polus:request');
        $this->map = $this->routerContainer->getMap();
        $this->middlewares = $this->container->get('polus:middlewares');
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * For testing purpose
     * @param ServerRequestInterface $request [description]
     */
    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function getMap()
    {
        return $this->map;
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
     * @param $position
     * @return boolean
     */
    public function addRouterRule(callable $rule, $position = 'append')
    {
        $ruleIterator = $this->routerContainer->getRuleIterator();
        $ruleIterator->$position($rule);
        return true;
    }

    /**
     * @return void
     */
    public function run()
    {
        $relayBuilder = $this->container->get('relay');
        $queue = $this->middlewares;
        $queue[] = new Middleware\Dispatcher($this->getDispatcher());
        $relay = $relayBuilder->newInstance($queue);

        $response = new Response();
        $response = $relay($this->request, $response);
    }

    public function get($path, $domain, $input = null, $responder = null)
    {
        if (!($domain instanceof Action)) {
            $domain = new Action($domain, $input, $responder);
        }

        return $this->map->get(md5($path), $path, $domain);
    }

    public function post($path, $domain, $input = null, $responder = null)
    {
        if (!($domain instanceof Action)) {
            $domain = new Action($domain, $input, $responder);
        }

        return $this->map->post(md5($path), $path, $domain);
    }

    public function put($path, $domain, $input = null, $responder = null)
    {
        if (!($domain instanceof Action)) {
            $domain = new Action($domain, $input, $responder);
        }

        return $this->map->put(md5($path), $path, $domain);
    }

    public function delete($path, $domain, $input = null, $responder = null)
    {
        if (!($domain instanceof Action)) {
            $domain = new Action($domain, $input, $responder);
        }

        return $this->map->delete(md5($path), $path, $domain);
    }

    public function patch($path, $domain, $input = null, $responder = null)
    {
        if (!($domain instanceof Action)) {
            $domain = new Action($domain, $input, $responder);
        }

        return $this->map->patch(md5($path), $path, $domain);
    }

    public function head($path, $domain, $input = null, $responder = null)
    {
        if (!($domain instanceof Action)) {
            $domain = new Action($domain, $input, $responder);
        }

        return $this->map->head(md5($path), $path, $domain);
    }

    public function options($path, $domain, $input = null, $responder = null)
    {
        if (!($domain instanceof Action)) {
            $domain = new Action($domain, $input, $responder);
        }

        return $this->map->options(md5($path), $path, $domain);
    }
}
