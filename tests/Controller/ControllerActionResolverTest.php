<?php

namespace Prime\Tests\Controller;

use Prime\Controller\ControllerActionResolver;
use Zend\Diactoros\ServerRequest;

class ControllerActionResolverTest extends \PHPUnit_Framework_TestCase
{
    protected $resolver;

    public function setUp()
    {
        $this->resolver = new ControllerActionResolver();        
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('Prime\Controller\ControllerActionResolver', $this->resolver);
    }

    public function testConstructorDoesNotSetNamespaceByDefault()
    {
        $this->assertNull($this->resolver->getNamespace());
    }

    public function testConstructorSetsNamespaceIfProvided()
    {
        $resolver = new ControllerActionResolver(true, 'foo');
        $this->assertSame('foo', $resolver->getNamespace());
    }

    public function testSetNamespace()
    {
        $this->resolver->setNamespace('bar');
        $this->assertSame('bar', $this->resolver->getNamespace());    
    }

    public function testSetNamespaceChangesPreviouslySetNamespace()
    {
        $this->resolver->setNamespace('baz');
        $this->assertSame('baz', $this->resolver->getNamespace());

        $this->resolver->setNamespace('qux');
        $this->assertSame('qux', $this->resolver->getNamespace());
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testSetNamespaceOtherThanStringThrowsException()
    {
        $this->resolver->setNamespace(123);
    }

    public function testGetControllerReturnsFormattedControllerName()
    {
        $cases = array(
            'foo' => 'Foo', 
            'foo_bar' => 'FooBar',
            'foo-bar' => 'FooBar',
            'foo bar' => 'FooBar',
            'Foo_Bar' => 'FooBar'
        );

        foreach ($cases as $raw => $formatted) {
            $request = new ServerRequest();
            $request = $request->withAttribute('controller', $raw);

            $this->assertSame($formatted . 'Controller', $this->resolver->getController($request));
        }        
    }

    public function testGetControllerReturnsNamespacedFormattedControllerNameIfNamespaceUsed()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('controller', 'bar');

        $this->resolver->setNamespace('foo');
        $this->assertSame('foo\BarController', $this->resolver->getController($request));    
    }

    public function testGetControllerReturnsFalseIfRequestHasNoControllerAttribute()
    {        
        $this->assertFalse($this->resolver->getController(new ServerRequest()));
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testGetControllerThrowsExceptionIfRequestControllerAttributeIsNotString()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('controller', 123);

        $this->resolver->getController($request);
    }

    public function testGetActionReturnsFormattedActionName()
    {
        $cases = array(
            'foo' => 'foo', 
            'foo_bar' => 'fooBar',
            'foo-bar' => 'fooBar',
            'foo bar' => 'fooBar',
            'Foo_Bar' => 'fooBar'
        );

        foreach ($cases as $raw => $formatted) {
            $request = new ServerRequest();
            $request = $request->withAttribute('action', $raw);

            $this->assertSame($formatted . 'Action', $this->resolver->getAction($request));
        }        
    }

    public function testGetActionReturnsDefaultActionIfRequestHasNoActionAttribute()
    {
        $this->assertSame('indexAction', $this->resolver->getAction(new ServerRequest()));  
    }

    public function testGetActionReturnsFalseIfRequestHasNoActionAttributeAndNoDefaultAction()
    {
        $resolver = new ControllerActionResolver(false);
        $this->assertFalse($resolver->getAction(new ServerRequest()));  
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testGetActionThrowsExceptionIfRequestActionAttributeIsNotString()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('action', 123);

        $this->resolver->getAction($request);
    }
}
