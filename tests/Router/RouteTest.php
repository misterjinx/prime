<?php

namespace Prime\Tests\Router\Route;

use Prime\Router\Route;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    protected $route;

    public function setUp()
    {
        $this->route = $this->getMockRoute('/foo', array('bar' => 'baz'));   
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('Prime\Router\Route', $this->route);
    }

    public function testConstructorSetsPath()
    {
        $this->assertSame('/foo', $this->route->getPath());
    }

    public function testConstructorSetsDefaults()
    {
        $this->assertSame(array('bar' => 'baz'), $this->route->getDefaults());
    }

    /**
     * @expectedException   Prime\Router\Route\Exception\InvalidRouteException
     */
    public function testConstructorWithPathNotStringThrowsException()
    {
        $this->getMockRoute(123);
    }

    /**
     * @expectedException   Prime\Router\Route\Exception\InvalidRouteException
     */
    public function testConstructorWithDefaultsNotArrayOrCallableOrClosureThrowsException()
    {
        $this->getMockRoute('/foo', 'bar');
    }

    public function testGetCallbackReturnsValidCallbacks()
    {
        $callable = array($this, 'callme');
        $route_callable = $this->getMockRoute('/foo', $callable);
        $this->assertSame($callable, $route_callable->getCallback());

        $closure = function() { return 'closure'; };
        $route_closure = $this->getMockRoute('/foo', $closure);
        $this->assertSame($closure, $route_closure->getCallback());
    }

    public function testGetCallbackReturnsNullIfNoValidCallbackSet()
    {
        $route = $this->getMockRoute('/foo', array('bar' => 'baz'));
        $this->assertSame(null, $route->getCallback());
    }

    public function getMockRoute($path, $defaults = array())
    {
        $route = $this->getMockBuilder('Prime\Router\Route')
            ->setConstructorArgs(array($path, $defaults))
            ->getMockForAbstractClass();

        $route->expects($this->any())
             ->method('match')
             ->will($this->returnValue(true));

        $route->expects($this->any())
             ->method('assemble')
             ->will($this->returnValue(true));

        return $route;             
    }

    public function callme()
    {
        return 'maybe';
    }
}
