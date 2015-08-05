<?php

namespace Prime\Tests\EventManager;

use Prime\EventManager;
use Prime\EventManager\Event;
use Prime\EventManager\EventInterface;

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

    public function testTriggerGetsEventListenersTriggered()
    {
        $this->events->attach('foo', function() { return 'on foo'; });

        $this->assertSame('on foo', $this->events->trigger('foo'));
    }

    public function testTriggerUsesDefaultEventClass()
    {
        $this->events->attach('foo', function($ev) { 
            return $ev instanceof Event; 
        });

        $this->assertTrue($this->events->trigger('foo'));
    }

    public function testTriggerUsesCustomEventClassWhenProvided()
    {
        $event = $this->getMock('Prime\EventManager\EventInterface');

        $this->events->attach('foo', function($ev) { 
            return $ev; 
        });

        $this->assertInstanceOf(get_class($event), $this->events->trigger('foo', $event));
    }

    public function testTriggerForOneEventListenerReturnsOneResponse()
    {
        $this->events->attach('foo', function() { return 'on foo'; });

        $this->assertTrue(is_string($this->events->trigger('foo')));
    }

    public function testTriggerForManyEventListenerReturnsArrayOfResponses()
    {
        $this->events->attach('foo', function() { return 'on foo 1'; });
        $this->events->attach('foo', function() { return 'on foo 2'; });

        $responses = $this->events->trigger('foo');
        $this->assertTrue(is_array($responses));
        $this->assertSame(2, count($responses));
    }

    public function testTriggerGetsAllEventListenersTriggered()
    {
        $this->events->attach('foo', function() { return 'on foo 1'; });
        $this->events->attach('foo', function() { return 'on foo 2'; });

        $responses = $this->events->trigger('foo');
        $this->assertSame(2, count($responses));
        $this->assertSame('on foo 1on foo 2', implode('', $responses));
    }

    public function testTriggerWithCallbackForMultipleListenersBreaksOnTrue()
    {
        $this->events->attach('foo', function() { return 'on foo 1'; });
        $this->events->attach('foo', function() { return 'on foo 2'; });

        $response = $this->events->trigger('foo', array(), function($resp) { 
            return true; 
        });

        $this->assertSame('on foo 1', $response);
    }

    public function testTriggerWithCallbackForMultipleListenersDoNotBreakIfFalseReturn()
    {
        $this->events->attach('foo', function() { return 'on foo 1'; });
        $this->events->attach('foo', function() { return 'on foo 2'; });

        $response = $this->events->trigger('foo', array(), function($resp) { 
            return false; 
        });

        $responses = $this->events->trigger('foo');
        $this->assertSame(2, count($responses));
        $this->assertSame('on foo 1on foo 2', implode('', $responses));
    }

    public function testTriggerWithParamsListenerReturnsParams()
    {
        $this->events->attach('foo', function($ev) { return $ev->getParams(); });
        $params = array('bar' => 'baz');

        $this->assertSame($params, $this->events->trigger('foo', $params));
    }

    public function testTriggerNonExistentEventReturnsNull()
    {
        $this->assertSame(null, $this->events->trigger('foo'));
    }

    public function testAttachedListenersWithDifferentPrioritiesTriggersInOrder()
    {
        $this->events->attach('foo', function() { return 'first'; }, 1);
        $this->events->attach('foo', function() { return 'second'; }, 2);

        $this->assertSame('secondfirst', implode('', $this->events->trigger('foo')));
    }

    public function testGetEvents()
    {
        $this->events->attach('foo', function() {});
        $this->events->attach('bar', function() {});

        $this->assertSame(array('foo', 'bar'), $this->events->getEvents());
    }

    public function testGetEventListeners()
    {
        $this->events->attach('foo', function() {});

        $this->assertSame(1, count($this->events->getEventListeners('foo')));
    }

    public function testGetEventListenersForNonExistentEvent()
    {
        $this->assertSame(0, count($this->events->getEventListeners('bar')));
    }

    public function testClearEventListenersWithAttachedEvent()
    {
        $this->events->attach('foo', function() {});
        $this->assertSame(1, count($this->events->getEventListeners('foo')));

        $this->events->clearEventListeners('foo');
        $this->assertSame(0, count($this->events->getEventListeners('foo')));
    }

    public function testClearEventListenersWithNotAttachedEvent()
    {
        $this->events->attach('foo', function() {});
        $this->assertSame(1, count($this->events->getEventListeners('foo')));

        $this->events->clearEventListeners('bar');
        $this->assertSame(0, count($this->events->getEventListeners('bar')));
    }    
}
