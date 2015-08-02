<?php

namespace Prime\Tests\Router\Route;

use Prime\Router\Route\Complex;

class ComplexTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $route = new Complex(
            '/docs/{section}/{title}.{format}', 
        array(
            'controller' => 'docs',
            'action' => 'section'
        ), 
        array(
            'format' => '(html|xml|json)'
        ));

        $this->assertTrue($route->match('/docs/chapter-ten/final-words.html'));
        $this->assertTrue($route->match('/docs/chapter-ten/final-words.xml'));
        $this->assertTrue($route->match('/docs/chapter-ten/final-words.json'));
        $this->assertFalse($route->match('/docs/chapter-ten/final-words.htmla'));
        $this->assertFalse($route->match('/docs/final-words.htmla'));
        $this->assertFalse($route->match('/chapter-ten/final-words.html'));
    }

    public function testMatchGetParam()
    {
        $route = new Complex(
            '/article/{slug}-{id}', 
        array(
            'controller' => 'articles',
            'action' => 'view'
        ), 
        array(
            'id' => '[0-9]+'
        ));        

        $this->assertFalse($route->match('/article/lorem-ipsum-1234foo'));
        $this->assertFalse($route->match('/article/1234'));
        $this->assertFalse($route->match('/lorem-ipsum-1234'));
        
        $this->assertTrue($route->match('/article/lor-3-m-ip-su-4m-1234'));
        $this->assertSame('lor-3-m-ip-su-4m', $route->getParam('slug'));
        $this->assertSame('1234', $route->getParam('id'));

        $this->assertTrue($route->match('/article/lorem-ipsum-1234'));
        $this->assertSame('lorem-ipsum', $route->getParam('slug'));
        $this->assertSame('1234', $route->getParam('id'));
    }

    public function testAssemble()
    {
        $route = new Complex('/article/{slug}-{id}');

        $this->assertSame('/article/title-123', $route->assemble(array(
            'slug' => 'title',
            'id' => 123
        )));

        $this->assertSame('/article/{slug}-12', $route->assemble(array(
            'id' => 12,
            'foo' => 'bar'
        )));
    }
}
