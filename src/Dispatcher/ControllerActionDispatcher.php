<?php

namespace Prime\Dispatcher;

use Prime\Controller\ControllerActionResolver;
use Prime\Dispatcher\Exception\HandlerNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class ControllerActionDispatcher implements DispatcherInterface
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

                $object->setRequest($request);
                $object->setResponse($response);
                
                // perform initialisation if needed
                $object->init();

                // run pre action logic
                $object->beforeAction();

                // run action                            
                $object->$actionMethodName();

                // run post action logic
                $object->afterAction();

                // done
                // $this->setDispatched();

                // return the response
                return $object->getResponse();
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
