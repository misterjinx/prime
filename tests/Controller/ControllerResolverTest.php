<?php

namespace Prime\Tests\Controller;

use Prime\Controller\ControllerResolver;
use Zend\Diactoros\ServerRequest;

class ControllerResolverTest extends \PHPUnit_Framework_TestCase
{
    protected $resolver;

    public function setUp()
    {
        $this->resolver = new ControllerResolver();        
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('Prime\Controller\ControllerResolver', $this->resolver);
    }

    public function testGetControllerReturnsRequestControllerAttribute()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('controller', 'Foo');

        $this->assertSame('Foo', $this->resolver->getController($request));
    }

    public function testGetControllerReturnsFalseIfRequestHasNoControllerAttribute()
    {        
        $this->assertFalse($this->resolver->getController(new ServerRequest()));
    }

    public function testGetControllerReturnsArrayIfRequestHasControllerAsArray()
    {       
        $request = new ServerRequest(); 
        $request = $request->withAttribute('controller', array('foo', 'bar'));

        $this->assertSame(array('foo', 'bar'), $this->resolver->getController($request));
    }

    public function testGetControllerReturnsObjectIfRequestHasControllerAsInvokableObject()
    {
        $object = $this->getMockBuilder('stdClass')
                     ->setMethods(array('__invoke'))
                     ->getMock();

        $request = new ServerRequest();
        $request = $request->withAttribute('controller', $object);

        $this->assertSame($object, $this->resolver->getController($request));
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testGetControllerThrowsExceptionIfRequestHasControllerAsObjectButNotInvokable()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('controller', new \stdClass());

        $this->resolver->getController($request);
    }
}
