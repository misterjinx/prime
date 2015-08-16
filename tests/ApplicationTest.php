<?php

namespace Prime\Tests;

use Prime\Application;
use Prime\Container;
use Prime\Controller\AbstractController;
use Prime\Controller\ControllerActionResolver;
use Prime\Dispatcher\ControllerActionDispatcher;
use Prime\View;
use Prime\View\ViewContent;
use Prime\View\Engine\PhpEngine;
use Prime\View\Resolver\TemplatePathResolver;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    protected $app;

    public function setUp()
    {
        $container = new Container();
        $container->set('dispatcher', new ControllerActionDispatcher(
            new ControllerActionResolver(true, 'Prime\Tests')
        ));

        $this->app = new Application($container);
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('Prime\Application', $this->app);
    }

    public function testConstructorSetsDefaultContainerServicesIfEmptyContainerIsProvided()
    {
        $app = new Application(new Container());
        $container = $app->getContainer();

        $this->assertInstanceOf('Prime\Router', $container->get('router'));
        $this->assertInstanceOf('Zend\Diactoros\ServerRequest', $container->get('request'));
        $this->assertInstanceOf('Zend\Diactoros\Response', $container->get('response'));
        $this->assertInstanceOf('Prime\Dispatcher\ControllerActionDispatcher', $container->get('dispatcher'));
        $this->assertInstanceOf('Prime\EventManager', $container->get('events'));
        $this->assertInstanceOf('Zend\Diactoros\Response\SapiEmitter', $container->get('emitter'));
    }    

    public function testRunReturnsResponseIfSpecified()
    {        
        $response = $this->app->run(new ServerRequest([], [], '/foo'), false);

        $this->assertTrue($response instanceof ResponseInterface);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRunEmitsResponseDirectlyIfNotSpecifiedOtherwise()
    {        
        $this->expectOutputString('bar');

        $this->app->run(new ServerRequest([], [], '/foo'));
    }

    public function testRunWithErrorHandleException()
    {
        $response = $this->app->run(new ServerRequest([], [], '/foobar'), false);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(
            '404 Prime\Tests\FoobarController does not exists', 
            (string) $response->getBody()
        );
    }

    public function testRunHandleNoMatchThrowsHttpNotFoundExceptionWhichIsProperlyHandled()
    {
        $response = $this->app->run(new ServerRequest([], [], '/'), false);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(
            '404 No routes found to match /',
            (string) $response->getBody()
        );
    }

    public function testDispatchedControllerReturnsViewContentWhichGetsUsedByTheResponseWithNoLayout()
    {
        $view = new View(new PhpEngine(
            new TemplatePathResolver(__DIR__ . '/View/_templates')
        ));
        $view->disableLayout();

        $this->app->setView($view);
        $response = $this->app->run(new ServerRequest([], [], '/foo/view'), false);

        $this->assertSame('This is a view document', (string) $response->getBody());
    }

    public function testDispatchedControllerReturnsViewContentWhichGetsUsedByTheResponseWithLayout()
    {
        $view = new View(new PhpEngine(
            new TemplatePathResolver(__DIR__ . '/View/_templates')
        ));
        $view->setLayoutTemplate('layout');

        $this->app->setView($view);
        $response = $this->app->run(new ServerRequest([], [], '/foo/view'), false);

        $this->assertSame('<div>This is a view document</div>', (string) $response->getBody());
    }

    public function testDispatchedControllerReturnsViewContentButThereIsNoViewServiceRegisteredIsHandledByException()
    {
        $response = $this->app->run(new ServerRequest([], [], '/foo/view'), false);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(
            '500 You returned a Prime\View\ViewContent object, but there is no view defined', 
            (string) $response->getBody()
        );
    }

    public function testDispatchedControllerReturnsDifferentObjectAndIsHandledByException()
    {
        $response = $this->app->run(new ServerRequest([], [], '/foo/dummy'), false);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(
            '500 Controllers must return a Response object, NULL given. Please add a return statement in your controller.', 
            (string) $response->getBody()
        );
    }
}

class FooController
{
    public function indexAction()
    {
        $response = new Response();
        $response->getBody()->write('bar');

        return $response;
    }

    public function viewAction()
    {
        return new ViewContent('sample', array(
            'type' => 'view'
        ));
    }

    public function dummyAction()
    {
        
    }
}

class ErrorController extends AbstractController
{
    public function errorAction()
    {
        $error = $this->request->getAttribute('exception');

        $response = new Response();
        $response->getBody()->write(
            sprintf('%d %s', $error->getStatusCode(), $error->getMessage())
        );

        return $response;        
    }
}
