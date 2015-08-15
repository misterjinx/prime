<?php

namespace Prime\Tests\Router\Route;

use Prime\Router\Route\Literal;

class LiteralTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $route = new Literal('/foo');
        $this->assertInstanceOf('Prime\Router\Route\Literal', $route);
    }

    public function testConstructorSetsDefaultsIfProvided()
    {
        $route = new Literal('/foo', array('bar' => 'baz'));
        $this->assertSame(array('bar' => 'baz'), $route->getDefaults());
    }

    public function testMatch()
    {
        $route = new Literal('/test', array(
            'controller' => 'test',
            'action' => 'test'
        ));

        $this->assertTrue($route->match('/test'));
        $this->assertFalse($route->match('/tests'));
    }

    public function testMatchGetMatches()
    {
        $route = new Literal('/', array(
            'controller' => 'foo'
        ));

        $this->assertTrue($route->match('/'));
        $this->assertSame('foo', $route->getMatch('controller'));
        $this->assertFalse($route->getMatch('action'));
        $this->assertSame(array('controller' => 'foo'), $route->getMatches());
    }

    public function testMatchesReturnsDefaults()
    {
        $route = new Literal('/foo', array(
            'controller' => 'foo'
        ));

        $this->assertTrue($route->match('/foo'));

        $defaults = $route->getDefaults();
        $matches = $route->getMatches();
        $this->assertSame($defaults, $matches);
    }    

    public function testAssemble()
    {
        $route = new Literal('/foo');

        $this->assertSame('/foo', $route->assemble());
        $this->assertSame('/foo', $route->assemble(array('foo' => 'bar')));
        $this->assertNotSame('/foo/bar', $route->assemble());
    }
}
