<?php

namespace Prime;

use \Prime\EventManager\Event;
use \Prime\EventManager\EventInterface;
use \Prime\EventManager\EventListenerInterface;
use \Prime\EventManager\EventManagerInterface;
use \Prime\Utils\PriorityQueue;

class EventManager implements EventManagerInterface
{
    /**
     * Default priority for attached events
     */
    const DEFAULT_PRIORITY = 1;

    /**
     * List with the registered events. The key is the name of an event. 
     * The value will be an instance of \Prime\Utils\PriorityQueue because 
     * for each event can be attached multiple callbacks and priority is 
     * important.
     * 
     * @var array
     */
    protected $events = array();

    /**
     * Register a callback to be executed when the provided event is triggered
     * 
     * @param  string  $event    Name of the event
     * @param  mixed   $callback Callback to execute when event is triggered
     * @param  integer $priority Priority of the callback
     * @return boolean
     */
    public function attach($event, $callback = null, $priority = self::DEFAULT_PRIORITY)
    {
        if ($callback === null || !is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf('%s expects a valid '
                . 'callback', __METHOD__));
        }

        if (!is_numeric($priority)) {
            throw new \InvalidArgumentException(sprintf('Event priority must be '
                . 'numeric, %s provided', $priority));
        }

        if (!isset($this->events[$event]) || 
            !$this->events[$event] instanceof PriorityQueue) {
            $this->events[$event] = new PriorityQueue();
        }

        $this->events[$event]->insert($callback, $priority);

        return true;
    }

    public function attachListener(EventListenerInterface $listener)
    {
        foreach ($listener->getAttachedEvents() as $event => $value) {
            if (is_string($value)) { 
                $this->attach($event, array($listener, $value));
            } elseif (is_array($value)) { 
                $this->attach($event, array($listener, $value[0]), 
                    array_key_exists(1, $value) ? $value[1] : self::DEFAULT_PRIORITY);
            }
        }
    }

    /**
     * Trigger the specified event
     * 
     * @param  string                $event    Name of the event 
     *                                         (has to be previously attached)
     * @param  array|EventInterface  $params   Optional parameters or Event object
     * @param  mixed                 $callback Callback to call for each of the 
     *                                         attached callbacks responses
     * @return mixed           
     */
    public function trigger($event, $params = array(), $callback = null)    
    {
        if ($callback !== null && !is_callable($callback)) {
            throw new \InvalidArgumentException('Invalid callback provided');
        }

        $responses = array();

        if (isset($this->events[$event])) {
            if ($params instanceof EventInterface) {
                $ev = $params;
                $ev->setName($event);
            } else {
                // default event
                $ev = new Event($event, $params);
            }

            foreach ($this->events[$event] as $k => $eventCallback) {
                $responses[$k] = call_user_func($eventCallback, $ev);
                if ($callback && call_user_func($callback, $responses[$k])) {
                    break; // perhaps should add this as a method parameter 
                           // to decide if should break after the first true 
                           // response or not
                }
            }

            return count($responses) > 1 ? $responses : reset($responses);
        }

        return null;
    }

    public function getEvents()
    {
        return array_keys($this->events);
    }

    public function getEventListeners($event)
    {
        if (isset($this->events[$event])) {
            return $this->events[$event];
        }

        return new PriorityQueue();
    }

    public function clearEventListeners($event)
    {
        if (isset($this->events[$event])) {
            unset($this->events[$event]);
        }
    }
}
