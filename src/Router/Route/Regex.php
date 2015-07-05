<?php

namespace Prime\Router\Route;

use Prime\Router\Route;
use Prime\Router\Route\Exception\InvalidRouteException;

class Regex extends Route
{
    /**
     * Not used at the moment
     * 
     * The format to use when assembling the route
     * @var string
     */
    protected $format;


    public function __construct($path, $format, $defaults = array())
    {
        if (!is_string($format)) {
            throw new InvalidRouteException('Regex route format has to be string');
        }

        $this->format = $format;

        parent::__construct($path, $defaults);
    }

    public function match($path)
    {
        // start fresh
        $this->clearMatches();

        if (preg_match('#^'.$this->path.'$#', $path, $matches)) {
            foreach ($matches as $key => $value) {
                if (is_numeric($key) || is_int($key) || $value === '') {
                    unset($matches[$key]);
                } else {
                    $this->matches[$key] = $value;
                }
            }

            return true;
        }

        return false;
    }    

    public function assemble($params = array())
    {
        $path = $this->format;        
        foreach ($params as $param => $value) {
            $path = str_replace('{'.$param.'}', $value, $path);
        }

        return $path;
    }
}
