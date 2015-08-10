<?php

namespace Prime\Tests\Dispatcher;

use Prime\Container;
use Prime\Controller\ControllerActionResolver;
use Prime\Dispatcher\ControllerActionDispatcher;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;

class ControllerActionDispatcherTest extends \PHPUnit_Framework_TestCase
{
    protected $dispatcher;

    public function setUp()
    {
        $this->dispatcher = new ControllerActionDispatcher(
            new ControllerActionResolver(true, 'Prime\Tests\Dispatcher')
        );
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('Prime\Dispatcher\ControllerActionDispatcher', $this->dispatcher);
    }

    public function testDispatch()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('controller', 'foo')
                           ->withAttribute('action', 'bar');

        $response = new Response();           
        $return = $this->dispatcher->dispatch($request, $response);

        $this->assertSame('response', $return);
    }

    public function testDispatchSetsContainerIfControllerImplementsContainerAware()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('controller', 'bar')
                           ->withAttribute('action', 'foo');

        $container = new Container();
        $this->dispatcher->setContainer($container);

        $return = $this->dispatcher->dispatch($request, new Response());

        $this->assertSame($container, $return);
    }

    public function testDispatchCallsControllerInitPreAndPostMethodsIfTheyExist()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('controller', 'baz')
                           ->withAttribute('action', 'qux');

        $return = $this->dispatcher->dispatch($request, new Response());

        $this->assertSame('initbeforeaction', $return);

        // @todo: incomplete, have to check that afterAction is called also
    }

    /**
     * @expectedException   Prime\Dispatcher\Exception\HandlerNotFoundException
     */
    public function testDispatchThrowsExceptionIfControllerClassDoesNotExists()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('controller', 'not_found');

        $this->dispatcher->dispatch($request, new Response());
    }

    /**
     * @expectedException   Prime\Dispatcher\Exception\HandlerNotFoundException
     */
    public function testDispatchThrowsExceptionIfControllerHasNoCorrespondingActionDefined()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('controller', 'foo')
                           ->withAttribute('action', 'not_found');

        $this->dispatcher->dispatch($request, new Response());
    }
}

class FooController
{
    public function barAction()
    {
        return 'response';
    }
}

class BarController extends \Prime\Container\ContainerAware
{
    public function fooAction()
    {
        return $this->container;
    }
}

class BazController
{
    public function init()
    {
        $this->output = 'init';
    }

    public function beforeAction()
    {
        $this->output .= 'before';
    }

    public function quxAction()
    {
        $this->output .= 'action';
        return $this->output;
    }

    public function afterAction()
    {
        $this->output .= 'after';
    }
}