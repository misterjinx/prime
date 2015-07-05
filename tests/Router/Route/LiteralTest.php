<?php

namespace Prime\Tests\Router\Route;

class LiteralTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $route = new \Prime\Router\Route\Literal('/test', array(
            'controller' => 'test',
            'action' => 'test'
        ));

        $this->assertTrue($route->match('/test'));
        $this->assertFalse($route->match('/tests'));
    }

    public function testAssemble()
    {
        $route = new \Prime\Router\Route\Literal('/foo');

        $this->assertSame('/foo', $route->assemble());
        $this->assertSame('/foo', $route->assemble(array('foo' => 'bar')));
        $this->assertNotSame('/foo/bar', $route->assemble());
    }
}
