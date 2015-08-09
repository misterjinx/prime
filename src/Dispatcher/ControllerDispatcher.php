<?php

namespace Prime\Dispatcher;

use Prime\Controller\ControllerResolver;
use Prime\Dispatcher\DispatcherInterface;
use Prime\Dispatcher\Exception\HandlerNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class ControllerDispatcher implements DispatcherInterface
{   
    /**
     * Determine the handler to use
     * 
     * @var ControllerResolver
     */
    protected $resolver;

    public function __construct(ControllerResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Dispatch the provided request 
     * 
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @return 
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response)
    {
        // get the handler, it can be a callback, a closure or invokable
        $handler = $this->resolver->getController($request);
        return call_user_func($handler, $request, $response);
    }
}
