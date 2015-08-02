<?php

namespace Prime\Container;

use Prime\Container\ContainerAwareInterface;

Class ContainerAware implements ContainerAwareInterface
{
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
