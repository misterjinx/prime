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
                get_class($controller)));
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
            return false;
        }

        if (!is_string($action)) {
            throw new \InvalidArgumentException(sprintf(
                'Action name has to be string, %s given', 
                get_class($action)));
        }

        return $this->formatActionMethodName($action);
    }

    protected function formatControllerClassName($name)
    {
        return $this->camelize($name) . $this->controllerNameSuffix;
    }

    protected function formatActionMethodName($name)
    {
        return $this->camelize($name, false) . $this->methodNameSuffix;
    }

    protected function camelize($string, $full = true)
    {
        $parts = explode('_', str_replace(array('- '), '_', $string));
        $camel = array_map('ucfirst', array_map('strtolower', $parts));
        
        $camelcase = implode('', $camel);

        return $full ? $camelcase : lcfirst($camelcase);
    }
}
