<?php

namespace Prime\EventManager;

use Prime\EventManager\EventListenerInterface;
use Prime\EventManager\ResponseEvent;

class ResponseEventListener implements EventListenerInterface
{
    public function onResponse(ResponseEvent $event)
    {
        // called when response event is triggered
    }

    public function getAttachedEvents()
    {
        // list of events and callbacks
        // @see interface description for more info
        return array('response' => 'onResponse');
    }
}
