<?php

namespace Prime;

use Prime\Router\Route;
use Prime\Router\Route\Exception\InvalidRouteException;
use Psr\Http\Message\ServerRequestInterface;

class Router
{
    /**
     * List of all defined routes. The key is the name of the route and the
     * value will consist of an array with the route as the first element and
     * the HTTP method(s) as the second element.
     * 
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

    public function add($name, Route $route, $httpMethod = null)
    {
        if ($httpMethod !== null && !(is_string($httpMethod) || is_array($httpMethod))) {
            throw new InvalidRouteException('The HTTP method has to be a string or an array');
        }

        if ($httpMethod && !is_array($httpMethod)) {
            $httpMethod = array_map('strtolower', array($httpMethod));
        }
        
        $this->routes[$name] = array(
            'route' => $route, 'method' => $httpMethod
        );
    }

    public function match(ServerRequestInterface $request)
    {
        $method = $request->getMethod();
        $path   = $request->getUri()->getPath();        

        foreach ($this->routes as $name => $route) {
            // check first if any http method restrictions
            if ($route['method'] && !in_array(strtolower($method), $route['method'])) {
                break;
            }

            // then try to match the path
            if ($route['route']->match($path)) {
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
        return $this->routes[$name]['route'];
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
