<?php

namespace Prime\Router;

use Prime\Router\Route\Exception\InvalidRouteException;

abstract class Route
{
    const URL_DELIMITER = '/';

    protected $path;
    protected $parts;
    protected $defaults = array();
    protected $matches = array();


    public function __construct($path, $defaults = array())
    {
        if (!is_string($path)) {
            throw new InvalidRouteException('Route path has to be string');
        }

        if (!(is_array($defaults) || is_callable($defaults) || $defaults instanceof \Closure)) {
            throw new InvalidRouteException('Route default values has to be array, callable or closure');
        }

        $this->path = $path;
        $this->defaults = $defaults;
        $this->parts = array_filter(explode(self::URL_DELIMITER, $path));

        return $this; 
    }

    public function getPath()
    {
        return $this->path;
    }    

    public function getDefaults()
    {
        return $this->defaults;
    }

    public function getDefault($name)
    {
        return isset($this->defaults[$name]) ? $this->defaults[$name] : false;
    }

    public function getCallback()
    {
        if (is_callable($this->defaults) || $this->defaults instanceof \Closure) {
            return $this->defaults;
        }

        return null;
    }

    public function getMatches()
    {
        return $this->matches;
    }

    public function getMatch($name)
    {
        return isset($this->matches[$name]) ? $this->matches[$name] : false;
    }

    public function clearMatches()
    {
        $this->matches = array();
    }

    public function getParam($name)
    {
        $value = $this->getMatch($name);
        return $value ?: $this->getDefault($name);
    }

    /**
     * Shortcut method to get the controller from the params list
     * 
     * @return string|boolean
     */
    public function getController()
    {
        return $this->getParam('controller');
    }

    /**
     * Shortcut method to get the action from the params list
     * 
     * @return string|boolean
     */
    public function getAction()
    {
        return $this->getParam('action');
    }

    /**
     * Match uri against defined route
     * 
     * @param  string $uri
     * @return boolean
     */
    abstract public function match($uri);

    /**
     * Generate route uri based on route definition and provided params
     * 
     * @param  array  $params
     * @return string
     */
    abstract public function assemble($params = array());
}
