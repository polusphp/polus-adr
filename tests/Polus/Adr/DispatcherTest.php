<?php

namespace Polus\Adr;

use Aura\Payload\Payload;
use Aura\Payload_Interface\PayloadStatus;
use Aura\Router\Route;
use Fixtures\Domains\DispatchInputTest;
use Fixtures\Domains\DispatchResponderTest;
use Fixtures\Domains\DomainDispatchTest;
use Fixtures\Domains\EmptyDomain;
use Polus\Polus_Interface\DispatchInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

class DispatcherTest extends TestCase
{
    private $resolver;
    /**
     * @var Route
     */
    private $route;
    private $request;
    private $response;

    protected function setUp()
    {
        $this->request = ServerRequestFactory::fromGlobals();
        $this->route = new Route();
        $this->resolver = new Resolver(function ($cls) {
            if (strpos($cls, 'JsonResponder')) {
                return new $cls(new PayloadStatusToHttpStatus());
            } else {
                return new $cls();
            }
        });
        $this->response = new Response();
    }

    public function testCreateDispatcher()
    {
        $dispatcher = new ActionDispatcher(new Resolver(function ($resolver) {
            return false;
        }));

        $this->assertInstanceOf(DispatchInterface::class, $dispatcher);
        /*
                try {
                    new Dispatcher();
                    $this->fail('Should fail with missing resolver');
                } catch (\Error $e) {
                    $this->assertContains('must implement interface ' . ResolverInterface::class, $e->getMessage());
                }
        */
    }

    public function testDispatchActionWithCallableDomain()
    {
        $dispatcher = new ActionDispatcher($this->resolver);
        $this->route->handler(new Action(function($input) {
            return (new Payload())->setStatus(PayloadStatus::CREATED)->setOutput(['test' => true]);
        }));

        $response = $dispatcher->dispatch($this->route, $this->request, $this->response);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertEquals('{"status":"success","data":{"test":true}}', $response->getBody()->__toString());
    }

    public function testDispatchActionWithClassName()
    {
        $dispatcher = new ActionDispatcher($this->resolver);
        $this->route->handler(new Action(DomainDispatchTest::class));

        $response = $dispatcher->dispatch($this->route, $this->request, $this->response);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertEquals('{"status":"fail","code":"NOT_AUTHENTICATED","data":{"test":false,"messages":null,"input":null}}', $response->getBody()->__toString());
    }

    public function testDispatchCustomInput()
    {
        $dispatcher = new ActionDispatcher($this->resolver);
        $this->route->handler(new Action(DispatchInputTest::class));

        $response = $dispatcher->dispatch($this->route, $this->request, $this->response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals('{"status":"success","data":{"custom_input":"test"}}', $response->getBody()->__toString());
    }

    public function testDispatchCustomResponder()
    {
        $dispatcher = new ActionDispatcher($this->resolver);
        $this->route->handler(new Action(DispatchResponderTest::class));

        $response = $dispatcher->dispatch($this->route, $this->request, $this->response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals('Custom responder output', $response->getBody()->__toString());
    }

    public function testInputExceptionResponse()
    {
        $dispatcher = new ActionDispatcher($this->resolver);
        $this->route->handler(new Action(EmptyDomain::class, function() {
            throw new \DomainException('Input exception test');
        }));

        $response = $dispatcher->dispatch($this->route, $this->request, $this->response);

        $this->assertSame(400, $response->getStatusCode());
        $body = $response->getBody()->__toString();
        $json = json_decode($body, true);

        $this->assertEquals('fail', $json['status']);
        $this->assertEquals('Input exception test', $json['data']['messages']);
        $this->assertArrayHasKey('exception', $json['data']);
    }
}
