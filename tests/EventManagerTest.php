<?php

namespace Prime\Tests\EventManager;

use Prime\EventManager;

class EventManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->events = new EventManager();
    }

    public function testAttachEvent()
    {
        $this->events->attach('foo', function() {
            return 'bar';
        });

        $this->assertTrue(in_array('foo', $this->events->getEvents()));
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testAttachEventWithInvalidCallbackThrowsException()
    {
        $this->events->attach('foo', 'bar');
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testAttachEventWithBadPriorityThrowsException()
    {
        $this->events->attach('foo', function() {
            return $bar;
        }, 'prio');
    }

    public function testAttachListener()
    {
        $listener = $this->getMock('Prime\EventManager\EventListenerInterface', array(
            'onFoo', 'getAttachedEvents'
        ));        
        $listener->expects($this->once())
                 ->method('getAttachedEvents')
                 ->will($this->returnValue(array(
                    'foo' => 'onFoo'
                 )));
        $listener->expects($this->any())
                 ->method('onFoo')
                 ->will($this->returnValue('bar'));                 

        $this->events->attachListener($listener);

        $this->assertTrue(in_array('foo', $this->events->getEvents()));                 
    }

    public function testGetEvents()
    {
        $this->events->attach('foo', function() {});
        $this->events->attach('bar', function() {});

        $this->assertSame(array('foo', 'bar'), $this->events->getEvents());
    }
}
