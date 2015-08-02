<?php

namespace Prime\Tests\Router\Route;

use Prime\Router\Route\Simple;

class SimpleTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchNormal()
    {
        // normal route, no filters            
        $route = new Simple('/test/{named}', array(
            'controller' => 'test',
            'action' => 'named'
        ));

        $this->assertTrue($route->match('/test/foo'));
    }

    public function testMatchWithFiltersPassedIndividually()
    {
        // add filters individually
        $route = new Simple('/test/{id}', array(
            'controller' => 'test',
            'action' => 'named'
        ));
        $route->filter('id', '[0-9]+');

        $this->assertTrue($route->match('/test/10'));
        $this->assertFalse($route->match('/test/foo'));
        $this->assertFalse($route->match('/test/12foo'));
        $this->assertFalse($route->match('/test/foo12'));
    }        

    public function testMatchWithFiltersPassedInConstructor()
    {
        // add filters in constructor
        $route = new Simple('/test/{x}', array(
            'controller' => 'test',
            'action' => 'x'
        ), array(
            'x' => '[a-zA-Z]+'
        ));

        $this->assertTrue($route->match('/test/foo'));
        $this->assertFalse($route->match('/test/123'));
        $this->assertFalse($route->match('/test/as123'));
        $this->assertFalse($route->match('/test/123as'));
    }

    public function testMatchGetParam()
    {
        $route = new Simple('/{foo}/{bar}/page/{page}', array(
            'controller' => 'fooos',
            'action' => 'baaar'
        ));
        $route->filter('page', '[0-9]+');

        $this->assertFalse($route->match('/baz/qux'));
        $this->assertFalse($route->match('/baz/qux/page/abc'));
        $this->assertTrue($route->match('/baz/qux/page/1'));
        $this->assertTrue($route->match('/baz/qux/page/11'));
        $this->assertSame('baz', $route->getParam('foo'));
        $this->assertSame('qux', $route->getParam('bar'));
        $this->assertSame('11', $route->getParam('page'));

        $this->assertSame('fooos', $route->getController());
        $this->assertSame('baaar', $route->getAction());
    }

    public function testAssemble()
    {
        $route = new Simple('/{foo}/{bar}/baz');

        $this->assertSame('/foo/bar/baz', $route->assemble(array(
            'foo' => 'foo',
            'bar' => 'bar',
            'baz' => 'baz'
        )));

        $this->assertSame('/{foo}/{bar}/baz', $route->assemble());
    }
}
