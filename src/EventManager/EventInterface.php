<?php

namespace Prime\EventManager;

interface EventInterface
{
    public function setName($name);

    public function getName();

    public function setParams($params);

    public function getParams();

    public function getParam($name, $default = null);
}
