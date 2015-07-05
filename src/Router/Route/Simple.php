<?php

namespace Prime\Router\Route;

use Prime\Router\Route;

class Simple extends Route
{
    protected $filters = array();

    public function __construct($path, $defaults = array(), $filters = array())
    {
        parent::__construct($path, $defaults);

        if ($filters) {
            foreach ($filters as $placeholder => $pattern) {
                $this->filter($placeholder, $pattern);
            }
        }
    }

    public function filter($placeholder, $pattern)
    {
        $this->filters[$placeholder] = $pattern;
    }

    public function clearFilters()
    {
        $this->filters = array();
    }

    public function match($path)
    {
        // start fresh
        $this->clearMatches();

        $parts = array_filter(explode('/', $path));
        if (count($parts) != count($this->parts)) {
            return false;
        }

        foreach ($parts as $key => $value) {
            $part = $this->parts[$key];

            // we check only for unnamed parameters to match
            if ($part[0] !== '{' && $part[strlen($part) -1] !== '}') {
                if ($part !== $value) {
                    return false; 
                }
            } else {
                // it is a named parameter match
                // check if there is a filter for this parameter or not
                $partName = trim($part, '{}');
                if (isset($this->filters[$partName])) {
                    $pattern = $this->filters[$partName];
                    if (preg_match('#^'.$pattern.'$#', $value)) {
                        // value is ok, still a match
                        $this->matches[$partName] = $value;
                    } else {
                        // value doesnt match filter
                        return false;
                    }
                } else {
                    // we don't care if there is no filter set
                    $this->matches[$partName] = $value;
                }
            }
        }

        return true;
    }   

    public function assemble($params = array())
    { 
        $path = $this->path;
        foreach ($params as $param => $value) {
            $path = str_replace('{'.$param.'}', $value, $path);
        }

        return $path;
    } 
}
