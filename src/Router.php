<?php

namespace Prime;

use Prime\Router\Route;
use Prime\Http\Request;

class Router
{
    /**
     * List of all routes defined
     * @var array
     */
    protected $routes = array();
    
    /**
     * Matched route if any, false otherwise
     * @var bool|Prime\Router\Route
     */
    protected $matchedRoute = false;

    public function __construct($defineDefaults = false)
    {
        if ($defineDefaults) {
            $this->defineDefaultRoutes();
        }        
    }

    public function add($name, Route $route)
    {
        $this->routes[$name] = $route;
    }

    public function match(Request $request)
    {
        $path = $request->getUri()->getPath();

        foreach ($this->routes as $name => $route) {
            if ($route->match($path)) {
                $this->matchedRoute = $name;
                break;
            }
        }
                
        return $this->matchedRoute ? true : false;
    }    

    public function getMatchedRoute()
    {
        return $this->matchedRoute && $this->hasRoute($this->matchedRoute)
               ? $this->getRoute($this->matchedRoute) : false;
    }

    public function hasRoute($name)
    {
        return isset($this->routes[$name]);            
    }

    public function getRoute($name)
    {
        return $this->routes[$name];
    }

    public function routesCount()
    {
        return count($this->routes);
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function clearRoutes()
    {
        $this->routes = array();
    }

    public function clearMatchedRoute()
    {
        $this->matchedRoute = false;
    }

    public function clean()
    {
        $this->clearRoutes();
        $this->clearMatchedRoute();
    }

    public function defineDefaultRoutes()
    {
        $this->add('default.controller', 
            new \Prime\Router\Route\Simple('/{controller}')
        );
        $this->add('default.controller.action', 
            new \Prime\Router\Route\Simple('/{controller}/{action}')
        );
    }
}
