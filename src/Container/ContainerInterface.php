<?php

namespace Prime\Container;

use Interop\Container\ContainerInterface as InteropContainerInterface;

interface ContainerInterface extends InteropContainerInterface
{
    public function set($name, $service);
}
