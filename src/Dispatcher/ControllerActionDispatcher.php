<?php

namespace Prime\Dispatcher;

use Prime\Container\ContainerAware;
use Prime\Controller\ControllerActionResolver;
use Prime\Dispatcher\Exception\HandlerNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class ControllerActionDispatcher extends ContainerAware implements DispatcherInterface
{   
    /**
     * Determine the actual controller class and action method to use
     * 
     * @var ControllerActionResolver
     */
    protected $resolver;

    public function __construct(ControllerActionResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function dispatch(ServerRequestInterface $request, ResponseInterface $response = null)
    {
        $controllerClassName = $this->resolver->getController($request);
        $actionMethodName = $this->resolver->getAction($request);

        if (class_exists($controllerClassName)) {
            if (method_exists($controllerClassName, $actionMethodName)) {
                $object = new $controllerClassName();

                if ($object instanceof ContainerAware) {
                    $object->setContainer($this->container);
                }
                
                // perform initialisation if exists
                if (method_exists($object, 'init')) {
                    $object->init();
                }                

                // run pre action logic if exists
                if (method_exists($object, 'beforeAction')) {
                    $object->beforeAction();
                }

                // run action                            
                $response = $object->$actionMethodName();

                // run post action logic if exists
                if (method_exists($object, 'afterAction')) {
                    $object->afterAction();
                }
                 
                return $response;
            } else {
                throw new HandlerNotFoundException(sprintf(
                    '%s class has no method %s defined', 
                    $controllerClassName, $actionMethodName), 404);
            }
        } else {
            throw new HandlerNotFoundException(sprintf('%s does not exists',
                $controllerClassName), 404);
        }
    }
}
