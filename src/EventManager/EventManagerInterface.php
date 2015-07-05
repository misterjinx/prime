<?php

namespace Prime\EventManager;

interface EventManagerInterface
{
    public function attach($event, $callback = null, $priority = 1);

    public function trigger($event, $params = array(), $callback = null);
}
