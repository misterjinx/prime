<?php

namespace Prime\Dispatcher;

use Prime\Dispatcher\DispatcherInterface;
use Prime\Dispatcher\Exception\HandlerNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class CallbackDispatcher implements DispatcherInterface
{   
    /**
     * Callback handler
     * 
     * @var callable
     */
    protected $handler;

    /**
     * Dispatch the provided request 
     * 
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @return 
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response)
    {
        return call_user_func($this->handler, $request, $response);            
    }

    /**
     * Register the callback
     * @param callable $handler
     */
    public function setHandler(callable $handler)
    {
        $this->handler = $handler;
    }    

    public function getHandler()
    {
        return $this->handler;
    }
}
