<?php

namespace Polus\Adr;

use Aura\Router\Exception\RouteAlreadyExists;
use Zend\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    protected function setUp()
    {
    }

    public function testAllowMultipleProtocolPerRoute()
    {
        $app = new App('Test', 'test');
        $app->get('/', function() {});
        try {
            $app->post('/', function() {});
        } catch (RouteAlreadyExists $rae) {
            $this->fail("Route already exists: " . $rae->getMessage());
        }

        $this->assertTrue(true);
    }
}
