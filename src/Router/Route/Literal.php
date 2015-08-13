<?php

namespace Prime\Router\Route;

use Prime\Router\Route;

class Literal extends Route
{
    /**
     * Check directly if the desired path matches the defined path
     * 
     * @param  string $uri
     * @return boolean
     */
    public function match($path)
    {
        return $path === $this->path;
    }

    /**
     * For a literal route there is nothing to match against, 
     * so return the default params 
     * 
     * @return array
     */
    public function getMatches()
    {        
        return $this->getDefaults();
    }

    /**
     * Being a static route there is not much to assemble
     * 
     * @param  array  $params
     * @return string
     */
    public function assemble($params = array())
    {
        return $this->path;
    }
}
