<?php

namespace Prime\Tests\Router\Route;

use Prime\Router\Route\Regex;

class RegexTest extends \PHPUnit_Framework_TestCase
{

    public function testInstantiation()
    {
        $route = new Regex('/ba[rz]', 'format');
        $this->assertInstanceOf('Prime\Router\Route\Regex', $route);
    }

    /**
     * @expectedException   Prime\Router\Route\Exception\InvalidRouteException
     */
    public function testConstructorWithFormatNotStringThrowsException()
    {
        $route = new Regex('/foo', 123);
    }

    public function testMatch()
    {
        $route = new Regex('/article/(?<id>[0-9]+)', 'format', array(
            'controller' => 'articles',
            'action' => 'view'
        ));

        $this->assertTrue($route->match('/article/100'));
        $this->assertTrue($route->match('/article/9'));
        $this->assertFalse($route->match('/article/abcd'));
        $this->assertFalse($route->match('/article/as123'));
    }    

    public function testMatchGetParam()
    {
        $route = new Regex('/article/(?<id>[0-9]+)', 'format', array(
            'controller' => 'articles',
            'action' => 'view'
        ));

        $this->assertTrue($route->match('/article/101'));
        $this->assertSame('101', $route->getParam('id'));
        $this->assertNotSame('1001', $route->getParam('id'));
        $this->assertSame('articles', $route->getController());
        $this->assertSame('view', $route->getAction());
    }

    public function testAssemble()
    {
        $route = new Regex('/article/(?<foo>[a-zA-Z0-9]+)', '/article/{foo}');

        $this->assertSame('/article/bar', $route->assemble(array('foo' => 'bar')));
        $this->assertSame('/article/{foo}', $route->assemble());
    }
}
