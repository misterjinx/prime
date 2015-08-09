<?php

namespace Prime\Tests;

use Prime\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    protected $instance;

    public function setUp()
    {
        $this->instance = new Container();
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('Prime\Container', $this->instance);
    }

    public function testSet()
    {
        $this->instance->set('foo', 'bar');

        $this->assertTrue($this->instance->has('foo'));
    }

    public function testGet()
    {
        $this->instance->set('foo', 'baz');

        $this->assertSame('baz', $this->instance->get('foo'));
    }

    public function testGetCallableServiceCallbacksWithProvidedParams()
    {
        $this->instance->set('callme', function($param) {
            return $param;
        });

        $return = $this->instance->get('callme', array('foo'));
        $this->assertSame('foo', $return);
    }

    /**
     * @expectedException   Prime\Container\Exception\ServiceNotFoundException
     */
    public function testGetUndefinedServiceThrowsException()
    {
        $this->instance->get('foo');
    }

    public function testHasReturnsTrueForDefinedService()
    {        
        $this->instance->set('baz', 'qux');
        $this->assertTrue($this->instance->has('baz'));
    }

    public function testHasReturnsFalseForUndefinedService()
    {        
        $this->assertFalse($this->instance->has('bar'));
    }
}
