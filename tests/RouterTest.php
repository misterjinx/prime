<?php

namespace Prime\Tests;

use Prime\Router;
use Prime\Router\Route\Literal;
use Prime\Router\Route\Simple;
use Prime\Router\Route\Complex;
use Prime\Router\Route\Exception\ResourceNotFoundException;
use Prime\Router\Route\Exception\InvalidRouteException;
use Zend\Diactoros\ServerRequest;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    protected $instance;

    public function setUp()
    {
        $this->instance = new Router();
    }

    public function tearDown()
    {
        $this->instance->cleanUp();
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('Prime\Router', $this->instance);
    }

    public function testDefaultRoutesDefinedOnInstantiation()
    {
        $router = new Router(true);

        $this->assertSame(2, $router->routesCount());

        $this->assertTrue($router->hasRoute('default.controller'));
        $this->assertTrue($router->hasRoute('default.controller.action'));
    }

    public function testDefaultRoutesNotDefinedOnInstantiation()
    {
        $router = new Router(false);

        $this->assertSame(0, $router->routesCount());

        $this->assertFalse($router->hasRoute('default.controller'));
        $this->assertFalse($router->hasRoute('default.controller.action'));
    }

    public function testAddRoute()
    {
        $this->instance->add('foo', new Literal('/foo'));

        $this->assertTrue($this->instance->hasRoute('foo'));
    }

    public function testAddedRouteIsTheSameWhenGetRoute()
    {
        $route = new Literal('/foo/bar');

        $this->instance->add('foo.bar', $route);

        $this->assertSame($route, $this->instance->getRoute('foo.bar'));
    }

    public function testAddedRouteOverwritesPreviouslyAddedRouteWithTheSameName()
    {
        $routeA = new Literal('/foo/bar');
        $routeB = new Literal('/baz/qux');

        $this->instance->add('foo.bar', $routeA);
        $this->instance->add('foo.bar', $routeB);

        $this->assertSame($routeB, $this->instance->getRoute('foo.bar'));
    }

    public function testHasRoute()
    {
        $route = new Literal('/foo/bar/baz/qux');

        $this->instance->add('foo.bar.baz.qux', $route);

        $this->assertTrue($this->instance->hasRoute('foo.bar.baz.qux'));
    }

    public function testGetRoute()
    {
        $route = new \Prime\Router\Route\Literal('/baz/qux');

        $this->instance->add('baz.qux', $route);

        $this->assertSame($route, $this->instance->getRoute('baz.qux'));
    }

    public function testRoutesCount()
    {
        $route = new Literal('/baz/qux');

        $this->instance->add('baz.qux', $route);

        $this->assertSame(1, $this->instance->routesCount());

        // define default routes 
        $router = new Router(true);
        $router->add('baz.qux', $route);

        // two defaults routes plus one previously added
        $this->assertSame(3, $router->routesCount());            
    }

    public function testClearRoutes()
    {
        $this->instance->clearRoutes();

        $this->assertEmpty($this->instance->getRoutes());
    }

    public function testClearMatchedRoute()
    {
        $route = new Literal('/foo/bar');
        $this->instance->add('foo.bar', $route);

        $request = new ServerRequest([], [], '/foo/bar');        
        $this->assertTrue($this->instance->match($request));

        $this->instance->clearMatchedRoute();

        $this->assertFalse($this->instance->getMatchedRoute());
    }

    public function testCleanUp()
    {
        $route = new Literal('/foo');

        $this->instance->add('foo', $route);

        $request = new ServerRequest([], [], '/foo');        
        $this->assertTrue($this->instance->match($request));
        $this->assertSame($route, $this->instance->getMatchedRoute());

        $this->instance->cleanUp();

        $this->assertEmpty($this->instance->getRoutes());   
        $this->assertFalse($this->instance->getMatchedRoute());
    }

    public function testMatchLiteral()
    {
        $routeA = new Literal('/foo');
        $routeB = new Literal('/foo/bar');        

        $this->instance->add('foo', $routeA);
        $this->instance->add('foo.bar', $routeB);

        $requestA = new ServerRequest([], [], '/foo');    

        $this->assertTrue($this->instance->match($requestA));
        $this->assertSame($routeA, $this->instance->getMatchedRoute());

        $requestB = new ServerRequest([], [], '/foo/bar');

        $this->assertTrue($this->instance->match($requestB));
        $this->assertSame($routeB, $this->instance->getMatchedRoute());
    } 

    public function testMatchSimple()
    {
        $route = new Simple('/foo/{bar}/baz');        
        $this->instance->add('foo.bar.baz', $route);

        $request = new ServerRequest([], [], '/foo/nothing/baz');

        $this->assertTrue($this->instance->match($request));        
        $this->assertSame($route, $this->instance->getMatchedRoute());  
    }   

    public function testMatchComplex()
    {
        $route = new Complex('/foo/{chapter}/{section}.{format}');
        $this->instance->add('foo.chapter.section', $route);

        $request = new ServerRequest([], [], '/foo/bar/some-section.html');

        $this->assertTrue($this->instance->match($request));        
        $this->assertSame($route, $this->instance->getMatchedRoute());
    }

    public function testMatchRouteWithSpecificHttpMethod()
    {
        $route = new Literal('/foo');
        $this->instance->add('foo', $route, 'POST');

        $request = new ServerRequest([], [], '/foo', 'POST');

        $this->assertTrue($this->instance->match($request));
        $this->assertSame($route, $this->instance->getMatchedRoute());
    }

    /**
     * @expectedException   Prime\Router\Route\Exception\ResourceNotFoundException
     */
    public function testNoMatchThrowsException()
    {
        $this->instance->add('foo', new Literal('/foo'));        
        $this->instance->add('foo', new Literal('/bar'));        

        $request = new ServerRequest([], [], '/baz');

        $this->instance->match($request);
    }

    /**
     * @expectedException   Prime\Router\Route\Exception\ResourceNotFoundException
     */
    public function testNoMatchRouteWithDifferentHttpMethodThrowsException()
    {
        $route = new Literal('/bar');
        $this->instance->add('bar', $route, 'GET');

        $request = new ServerRequest([], [], '/bar', 'POST');

        $this->instance->match($request);
    }

    /**
     * @expectedException   Prime\Router\Route\Exception\InvalidRouteException
     */
    public function testAddRouteWrongNameTypeThrowsException()
    {
        $this->instance->add(111, new Literal('/foo'));
    }

    /**
     * @expectedException   Prime\Router\Route\Exception\InvalidRouteException
     */
    public function testAddRouteWrongHttpMethodTypeThrowsException()
    {
        $this->instance->add('foo', new Literal('/foo'), 111);
    }
}
