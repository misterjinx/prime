<?php

namespace Prime\EventManager;

interface EventListenerInterface
{
    /**
     * Returns an array with events that will be attached.
     *
     * Array keys represent name of the events while the values consist of
     * method names from the current listener. The value can be defined as 
     * string or as an array (especially if you want to set the priority also):
     *
     * array('event' => 'method'),
     * array('event' => array('method'[, 'priority']))
     *     
     * @return [type] [description]
     */
    public function getAttachedEvents();
}
