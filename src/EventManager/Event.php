<?php

namespace Prime\EventManager;

use Prime\EventManager\EventInterface;

class Event implements EventInterface
{
    protected $name;
    protected $params;

    public function __construct($name = null, $params = array())
    {
        if ($name) {
            $this->setName($name);
        }

        if ($params) {
            $this->setParams($params);
        }
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setParams($params = array())
    {
        if (!is_array($params)) {
            throw new \InvalidArgumentException(sprintf(
                'Event params must be an array, %s received', gettype($params)));
        }

        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getParam($name, $default = null)
    {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }

        return $default;
    }
}
