<?php

namespace Prime;

use Prime\Container;
use Prime\Container\ContainerInterface;
use Prime\Router;
use Prime\Http\Request;
use Prime\Http\Response as PrimeResponse;
use Prime\Dispatcher;
use Prime\Dispatcher\DispatcherInterface;
use Prime\EventManager\EventManagerInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;


class Application
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        
        if (!$this->container->has('router')) {
            $this->setRouter(new Router());
        }

        if (!$this->container->has('request')) {
            $this->setRequest(new Request());
        }

        if (!$this->container->has('response')) {
            $this->setResponse(new PrimeResponse());
        }

        if (!$this->container->has('dispatcher')) {
            $this->setDispatcher(new Dispatcher());
        }
    }

    public function setRouter(Router $router)
    {
        $this->container->set('router', $router);
    }

    public function setRequest(ServerRequest $request)
    {
        $this->container->set('request', $request);
    }

    public function setResponse(Response $response)
    {
        $this->container->set('response', $response);
    }

    public function setDispatcher(DispatcherInterface $dispatcher)
    {
        $this->container->set('dispatcher', $dispatcher);
    }

    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->container->set('events', $eventManager);
    }

    public function run()
    {
        $request  = $this->container->get('request');
        $response = $this->container->get('response');
        $router   = $this->container->get('router');
        $dispatcher = $this->container->get('dispatcher');

        $router->match($request);
        
        $matched = $router->getMatchedRoute();
        if ($matched) {
            $dispatcher->setControllerName($matched->getController());
            $dispatcher->setActionName($matched->getAction());
            $dispatcher->setParams($matched->getMatches());
        }

        try {
            $dispatcher->dispatch($request, $response);
        } catch (\Exception $e) {
            $dispatcher->dispatchError($e);
        }
    }
}
