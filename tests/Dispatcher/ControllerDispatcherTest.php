<?php

namespace Prime\Tests\Dispatcher;

use Prime\Dispatcher\ControllerDispatcher;
use Prime\Controller\ControllerResolver;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;

class ControllerDispatcherTest extends \PHPUnit_Framework_TestCase
{
    protected $dispatcher;

    public function setUp()
    {
        $this->dispatcher = new ControllerDispatcher(new ControllerResolver());
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('Prime\Dispatcher\ControllerDispatcher', $this->dispatcher);
    }

    public function testDispatch()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('controller', new TestController());

        $response = new Response();           
        $return = $this->dispatcher->dispatch($request, $response);

        $this->assertSame($response, $return);
    }
}

class TestController
{
    public function __invoke($request, $response) 
    {
        return $response;
    }
}
