<?php

namespace Prime;

use Prime\Container\ContainerInterface;
use Prime\Container\Exception\ServiceNotFound;

/** 
 * Container that can be used as a Service Locator or a Dependency Injection 
 * container, as needed. Very simple, only the necessary functions.
 */
class Container implements ContainerInterface
{
    /**
     * Dictionary containing all the services that were registered
     * 
     * @var array
     */
    protected $services = array();


    /**
     * Register a new service
     * 
     * @param string $name    Name of the service to register
     * @param mixed  $service Definition for the service
     */
    public function set($name, $service)
    {
        $this->services[$name] = $service;

        return $this;
    }

    /**
     * Retrieve a service that was previously registered
     * 
     * @param  string $name   Name of the service to retrieve
     * @param  array  $params List of parameters to pass to the service
     *
     * @throws ServiceNotFound Service not registered
     * 
     * @return mixed     Throws exception if the service is not registered or 
     *                   the instantiated service (if it's the case)
     */
    public function get($name, $params = array())
    {
        if (!$this->has($name)) {
            throw new ServiceNotFound($name);
        }

        $service = $this->services[$name];
        if ($service instanceof \Closure || (!is_object($service) && is_callable($service))) {
            $this->services[$name] = $service = call_user_func_array($service, $params);
        }

        return $service;
    }

    /**
     * Check whether a service is registered or not
     * 
     * @param  string  $name Name of the service to check for
     * @return boolean
     */
    public function has($name)
    {
        return isset($this->services[$name]);
    }
}
