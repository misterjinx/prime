<?php

namespace Prime\Container;

use Prime\Container\ContainerInterface;

interface ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container = null);
}
