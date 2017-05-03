<?php

namespace Polus\Adr;

use Aura\Payload\Payload;
use Fixtures\Domains\DomainInputTest;
use Fixtures\Domains\DomainResponderTest;
use Fixtures\Input\TestInput;
use Polus\Adr\Responder\JsonResponder;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultArguments()
    {
        $action = new Action(function($input) {
            return new Payload();
        });

        $this->assertEquals(Input::class, $action->getInput());
        $this->assertEquals(JsonResponder::class, $action->getResponder());
        $this->assertTrue(is_callable($action->getDomain()));
    }

    public function testCustomInput()
    {
        $action = new Action(function($input) {
            return new Payload();
        }, TestInput::class);

        $this->assertEquals(TestInput::class, $action->getInput());
        $this->assertEquals(JsonResponder::class, $action->getResponder());
        $this->assertTrue(is_callable($action->getDomain()));
    }

    public function testAutoResolveInput()
    {
        $action = new Action(DomainInputTest::class);

        $this->assertEquals(JsonResponder::class, $action->getResponder());
        $this->assertEquals(DomainInputTest\Input::class, $action->getInput());
        $this->assertEquals(DomainInputTest::class, $action->getDomain());
    }

    public function testAutoResolveResponder()
    {
        $action = new Action(DomainResponderTest::class);

        $this->assertEquals(Input::class, $action->getInput());
        $this->assertEquals(DomainResponderTest\Responder::class, $action->getResponder());
        $this->assertEquals(DomainResponderTest::class, $action->getDomain());
    }
}
