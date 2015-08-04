<?php

namespace Prime\Tests\EventManager;

use Prime\EventManager\Event;

class EventTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorWithBothArgumentsGiven()
    {
        $name = 'foo';
        $params = array('bar' => 'baz');

        $event = new Event($name, $params);

        $this->assertSame($name, $event->getName());
        $this->assertSame($params, $event->getParams());
    }

    public function testConstructorWithNoArgumentsGiven()
    {
        $event = new Event();

        $this->assertSame(null, $event->getName());
        $this->assertTrue(empty($event->getParams()));
    }

    public function testSetName()
    {
        $event = new Event();
        $event->setName('foo');

        $this->assertSame('foo', $event->getName());
    }

    public function testSetNameOverwritesPreviousSetName()
    {
        $event = new Event('foo');
        $event->setName('bar');

        $this->assertSame('bar', $event->getName());
    }

    public function testGetName()
    {
        $event = new Event('foo');

        $this->assertSame('foo', $event->getName());
    }

    public function testSetParams()
    {
        $params = array('foo' => 'bar');

        $event = new Event();
        $event->setParams($params);

        $this->assertSame($params, $event->getParams());
    }

    public function testSetParamsOverwritesPreviousSetParams()
    {
        $params = array('foo' => 'bar');

        $event = new Event('foo', array('baz' => 'qux'));
        $event->setParams($params);

        $this->assertSame($params, $event->getParams());
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testSetParamsNotArrayThrowsException()
    {
        $event = new Event('foo', 'bar');
    }

    public function testGetParams()
    {
        $params = array('bar' => 'baz');

        $event = new Event('foo', $params);

        $this->assertSame($params, $event->getParams());
    }

    public function testGetParam()
    {
        $event = new Event('foo', array('foo' => 'bar'));

        $this->assertSame('bar', $event->getParam('foo'));
    }

    public function testGetUndefinedParamsReturnsDefaultValue()
    {
        $event = new Event('foo', array('foo' => 'bar'));

        $this->assertSame(null, $event->getParam('bar'));   
        $this->assertSame('baz', $event->getParam('bar', 'baz'));   
    }
}
