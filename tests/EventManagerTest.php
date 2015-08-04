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
            'onFoo', 'onBar', 'onBaz', 'getAttachedEvents'
        ));        
        $listener->expects($this->once())
                 ->method('getAttachedEvents')
                 ->will($this->returnValue(array(
                    'foo' => 'onFoo', // as string
                    'bar' => array('onBar'), // as array 
                    'baz' => array('onBaz', 2) // as array with priority
                 )));
        $listener->expects($this->any())
                 ->method('onFoo')
                 ->will($this->returnValue('on foo'));
        $listener->expects($this->any())
                 ->method('onBar')
                 ->will($this->returnValue('on bar'));
        $listener->expects($this->any())
                 ->method('onBaz')
                 ->will($this->returnValue('on baz'));

        $this->events->attachListener($listener);

        $events = $this->events->getEvents();

        foreach (array('foo', 'bar', 'baz') as $event) {
            $this->assertTrue(in_array($event, $events));    
            $listeners = $this->events->getEventListeners($event);
            $this->assertSame(1, count($listeners));

            foreach ($listeners as $listen) {
                $this->assertSame(get_class($listener), get_class($listen[0]));
                $this->assertSame('on'.ucwords($event), $listen[1]);
            }
        }
    }

    public function testGetEvents()
    {
        $this->events->attach('foo', function() {});
        $this->events->attach('bar', function() {});

        $this->assertSame(array('foo', 'bar'), $this->events->getEvents());
    }
}
