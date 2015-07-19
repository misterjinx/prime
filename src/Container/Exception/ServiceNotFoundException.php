<?php

namespace Prime\Container\Exception;

use Interop\Container\Exception\NotFoundException;

class ServiceNotFoundException 
    extends \InvalidArgumentException implements NotFoundException
{

}
