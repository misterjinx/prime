<?php

namespace Prime;

use \Prime\EventManager\EventManagerInterface;
use \Prime\Utils\PriorityQueue;

class EventManager implements EventManagerInterface
{
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
    public function attach($event, $callback = null, $priority = 1)
    {
        if ($callback === null || !is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf('%s expects a callback',
                __METHOD__));
        }

        if (!isset($this->events[$event]) || 
            !$this->events[$event] instanceof \Prime\Utils\PriorityQueue) {
            $this->events[$event] = new \Prime\Utils\PriorityQueue();
        }

        $this->events[$event]->insert($callback, $priority);

        return true;
    }

    /**
     * Trigger the specified event
     * 
     * @param  string $event    Name of the event (has to be previously attached)
     * @param  array  $params   Optional parameters
     * @param  mixed  $callback Callback to call for each of the attached 
     *                          callbacks responses
     * @return mixed           
     */
    public function trigger($event, $params = array(), $callback = null)    
    {
        if ($callback !== null && !is_callable($callback)) {
            throw new \InvalidArgumentException('Invalid callback provided');
        }

        if (isset($this->events[$event])) {
            foreach ($this->events[$event] as $eventCallback) {
                $ev = new \Prime\EventManager\Event($event, $params);
                $response = call_user_func($eventCallback, $ev);

                if ($callback && call_user_func($callback, $response)) {
                    break; // perhaps should add this as a method parameter 
                           // to decide if should break after the first true 
                           // response or not
                }
            }

            return $response;
        }

        return null;
    }
}
