<?php

namespace Prime\Tests;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    protected $_instance;


    public function setUp()
    {
        $this->_instance = new \Prime\Router(true);
    }

    public function tearDown()
    {
        $this->_instance->clean();
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('Prime\Router', $this->_instance);
    }

    public function testDefaultRoutesDefinedOnInstantiation()
    {
        $this->assertSame(2, $this->_instance->routesCount());

        $this->assertTrue($this->_instance->hasRoute('default.controller'));
        $this->assertTrue($this->_instance->hasRoute('default.controller.action'));
    }

    public function testDefaultRoutesNotDefinedOnInstantiation()
    {
        $router = new \Prime\Router(false);

        $this->assertSame(0, $router->routesCount());

        $this->assertFalse($router->hasRoute('default.controller'));
        $this->assertFalse($router->hasRoute('default.controller.action'));
    }

    public function testAddRoute()
    {
        $this->_instance->add('foo', new \Prime\Router\Route\Literal('/foo'));

        $this->assertTrue($this->_instance->hasRoute('foo'));
    }

    public function testAddedRouteMatchesRequestedRoute()
    {
        $route = new \Prime\Router\Route\Literal('/foo/bar');

        $this->_instance->add('foo.bar', $route);

        $this->assertSame($route, $this->_instance->getRoute('foo.bar'));
    }

    public function testAddedRouteOverwritesPreviouslyAddedRouteWithTheSameName()
    {
        $routeA = new \Prime\Router\Route\Literal('/foo/bar');
        $routeB = new \Prime\Router\Route\Literal('/baz/qux');

        $this->_instance->add('foo.bar', $routeA);
        $this->_instance->add('foo.bar', $routeB);

        $this->assertSame($routeB, $this->_instance->getRoute('foo.bar'));
    }

    public function testHasRoute()
    {
        $route = new \Prime\Router\Route\Literal('/foo/bar/baz/qux');

        $this->_instance->add('foo.bar.baz.qux', $route);

        $this->assertTrue($this->_instance->hasRoute('foo.bar.baz.qux'));
    }

    public function testGetRoute()
    {
        $route = new \Prime\Router\Route\Literal('/baz/qux');

        $this->_instance->add('baz.qux', $route);

        $this->assertSame($route, $this->_instance->getRoute('baz.qux'));
    }

    public function testRoutesCount()
    {
        $route = new \Prime\Router\Route\Literal('/baz/qux');

        $this->_instance->add('baz.qux', $route);

        $this->assertSame(3, $this->_instance->routesCount());

        $router = new \Prime\Router(false);
        $router->add('baz.qux', $route);

        $this->assertSame(1, $router->routesCount());            
    }

    public function testClearRoutes()
    {
        $this->_instance->clearRoutes();

        $this->assertEmpty($this->_instance->getRoutes());
    }

    public function testClearMatchedRoute()
    {
        $route = new \Prime\Router\Route\Literal('/foo/bar');
        $request = new \Prime\Http\Request([], [], '/foo/bar');

        $this->assertTrue($this->_instance->match($request));

        $this->_instance->clearMatchedRoute();

        $this->assertFalse($this->_instance->getMatchedRoute());

    }

    public function testMatch()
    {
        $routeA = new \Prime\Router\Route\Literal('/foo');
        $routeB = new \Prime\Router\Route\Literal('/foo/bar');
        $routeC = new \Prime\Router\Route\Simple('/foo/{bar}/baz');
        $routeD = new \Prime\Router\Route\Complex('/foo/{chapter}/{section}.{format}');

        // make sure there are no default routes defined for this tests
        $this->_instance->clean();

        $this->_instance->add('foo', $routeA);
        $this->_instance->add('foo.bar', $routeB);
        $this->_instance->add('foo.bar.baz', $routeC);
        $this->_instance->add('foo.chapter.section', $routeD);

        $request = new \Prime\Http\Request([], [], '/foo/bar');

        $this->assertTrue($this->_instance->match($request));
        $this->assertSame($routeB, $this->_instance->getMatchedRoute());

        $this->_instance->clean();

        $this->_instance->add('foo.chapter.section', $routeD);

        $this->assertFalse($this->_instance->match($request));
        $this->assertFalse($this->_instance->getMatchedRoute());

        $request = new \Prime\Http\Request([], [], '/foo/bar/some-section.html');

        $this->assertTrue($this->_instance->match($request));        
        $this->assertSame($routeD, $this->_instance->getMatchedRoute());

        $this->_instance->clean();

        $this->_instance->add('foo.bar.baz', $routeC);

        $request = new \Prime\Http\Request([], [], '/foo/nothing/baz');

        $this->assertTrue($this->_instance->match($request));        
        $this->assertSame($routeC, $this->_instance->getMatchedRoute());        
    }
}
