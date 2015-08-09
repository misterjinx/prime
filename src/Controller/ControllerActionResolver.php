<?php

namespace Prime\Controller;

use Prime\Controller\ResolverInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControllerActionResolver implements ResolverInterface
{
    /**
     * Controller class names end in 'Controller'
     * @var string
     */
    protected $controllerNameSuffix = 'Controller';

    /**
     * Action method names end in 'Action'
     * @var string
     */
    protected $methodNameSuffix = 'Action';

    /**
     * Default action name to be used if enabled
     * @var string
     */
    protected $defaultAction = 'index';

    /**
     * Whether to use the default action name when no action is found
     * @var boolean
     */
    protected $useDefaultAction = true;

    /**
     * Namespace to use for Controller class names
     * @var string
     */
    protected $namespace;

    /**
     * Initialize options
     * 
     * @param boolean $useDefaultAction
     */
    public function __construct($useDefaultAction = true, $namespace = null)
    {
        $this->useDefaultAction = (bool) $useDefaultAction;
        
        if ($namespace !== null) {
            $this->setNamespace($namespace);
        }
    }    

    public function setNamespace($namespace)
    {
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException(sprintf(
                'Namespace has to be string, %s given', 
                gettype($namespace)));
        }

        $this->namespace = (string) $namespace;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Get controller class name
     * 
     * @param  ServerRequestInterface $request
     * @return mixed
     * @throws \InvalidArgumentException 
     */
    public function getController(ServerRequestInterface $request)
    {
        $controller = $request->getAttribute('controller');
        if (!$controller) {
            return false;
        }

        if (!is_string($controller)) {
            throw new \InvalidArgumentException(sprintf(
                'Controller name has to be string, %s given', 
                gettype($controller)));
        }

        return $this->formatControllerClassName($controller);   
    }

    /**
     * Get action method name
     * 
     * @param  ServerRequestInterface $request
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getAction(ServerRequestInterface $request)
    {
        $action = $request->getAttribute('action');
        if (!$action) {
            if (!$this->useDefaultAction) {
                return false;
            } 

            $action = $this->defaultAction;
        }

        if (!is_string($action)) {
            throw new \InvalidArgumentException(sprintf(
                'Action name has to be string, %s given', 
                gettype($action)));
        }

        return $this->formatActionMethodName($action);
    }

    protected function formatControllerClassName($name)
    {
        $namespace = $this->namespace ? $this->namespace . '\\' : '';

        return $namespace . $this->camelize($name) . $this->controllerNameSuffix;
    }

    protected function formatActionMethodName($name)
    {
        return $this->camelize($name, false) . $this->methodNameSuffix;
    }

    protected function camelize($string, $full = true)
    {
        $parts = explode('_', str_replace(array('-', ' '), '_', $string));
        $camel = array_map('ucfirst', array_map('strtolower', $parts));
        
        $camelcase = implode('', $camel);

        return $full ? $camelcase : lcfirst($camelcase);
    }
}
