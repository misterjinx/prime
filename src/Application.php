<?php

namespace Prime;

use Prime\Container;
use Prime\Container\ContainerInterface;
use Prime\Router;
use Prime\Dispatcher;
use Prime\CallbackDispatcher;
use Prime\Dispatcher\DispatcherInterface;
use Prime\EventManager\EventManagerInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\SapiEmitter;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


class Application
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        
        if (!$this->container->has('router')) {
            $this->setRouter(new Router(true));
        }

        if (!$this->container->has('request')) {
            $this->setRequest(ServerRequestFactory::fromGlobals());
        }

        if (!$this->container->has('response')) {
            $this->setResponse(new Response());
        }

        if (!$this->container->has('dispatcher')) {
            $this->setDispatcher(new Dispatcher());
        }
    }

    public function setRouter(Router $router)
    {
        $this->container->set('router', $router);
    }

    public function setRequest(ServerRequestInterface $request)
    {
        $this->container->set('request', $request);
    }

    public function setResponse(ResponseInterface $response)
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
            $dispatcher->setHandler(array(
                'controller' => $matched->getController(),
                'action' => $matched->getAction(),
                'params' => $matched->getMatches()
            ));
        }

        try {
            $response = $dispatcher->dispatch($request, $response);
        } catch (\Exception $exception) {
            $response = $dispatcher->dispatchError($request, $response, $exception);
        }

        $emitter = new SapiEmitter();
        $emitter->emit($response);
    }
}
