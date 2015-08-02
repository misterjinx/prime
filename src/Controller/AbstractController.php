<?php

namespace Prime\Controller;

use Prime\Container\ContainerAware;

abstract class AbstractController extends ContainerAware
{
    public function __get($name)
    {
        if (in_array($name, array('view', 'request', 'response', 'events'))) {
            return $this->container->get($name);
        }
    }
}
