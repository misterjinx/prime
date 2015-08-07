<?php

namespace Prime\Tests\Router\Route;

use Prime\Router\Route\Simple;

class SimpleTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $route = new Simple('/foo');
        $this->assertInstanceOf('Prime\Router\Route\Simple', $route);
    }

    public function testConstructorSetsDefaultsIfProvided()
    {
        $route = new Simple('/foo', array('bar' => 'baz'));
        $this->assertSame(array('bar' => 'baz'), $route->getDefaults());
    }

    public function testConstructorSetsProvidedFilters()
    {
        $route = new Simple('/foo', array(), array('bar' => '[0-9]+'));
        $this->assertSame(array('bar' => '[0-9]+'), $route->getFilters());
    }

    public function testSetFilterIndividually()
    {
        $route = new Simple('foo');
        $this->assertSame(array(), $route->getFilters());

        $route->filter('bar', '[a-z]+');
        $filters = $route->getFilters();

        $this->assertTrue(array_key_exists('bar', $filters));
        $this->assertSame('[a-z]+', $filters['bar']);
    }    

    public function testSetAndGetFilters()
    {
        $route = new Simple('/foo');
        $route->setFilters(array('bar' => '[0-9]+'));

        $this->assertSame(array('bar' => '[0-9]+'), $route->getFilters());
    }

    public function testClearFilters()
    {
        $route = new Simple('/foo', array(), array('bar' => '.*'));
        $this->assertSame(array('bar' => '.*'), $route->getFilters());

        $route->clearFilters();
        $this->assertSame(array(), $route->getFilters());
    }

    public function testMatchNormal()
    {
        // normal route, no filters            
        $route = new Simple('/test/{named}', array(
            'controller' => 'test',
            'action' => 'named'
        ));

        $this->assertTrue($route->match('/test/foo'));
    }

    public function testMatchDifferentNamedParameterReturnsFalse()
    {
        $route = new Simple('/foo/{bar}', array(
            'controller' => 'foo',
            'action' => 'bar'
        ));

        $this->assertFalse($route->match('/bar/baz'));        
    }

    public function testMatchWithFilters()
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
