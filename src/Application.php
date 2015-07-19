<?php

namespace Prime;

use Prime\Container;
use Prime\Container\ContainerInterface;
use Prime\Container\Exception\ServiceNotFoundException;
use Prime\Router;
use Prime\Router\Route\Exception\ResourceNotFoundException;
use Prime\Controller\ControllerActionResolver;
use Prime\Dispatcher\ControllerActionDispatcher;
use Prime\Dispatcher\Exception\HandlerNotFoundException;
use Prime\Dispatcher\DispatcherInterface;
use Prime\View\ViewContentInterface;
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
            $this->setDispatcher(new ControllerActionDispatcher(
                new ControllerActionResolver()
            ));
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
        $request    = $this->container->get('request');
        $response   = $this->container->get('response');
        $router     = $this->container->get('router');
        $dispatcher = $this->container->get('dispatcher');

        try {
            $router->match($request);

            $route = $router->getMatchedRoute();
            foreach ($route->getMatches() as $param => $value) {
                $request = $request->withAttribute($param, $value);
            }

            $received = $dispatcher->dispatch($request, $response);

            if (!$received instanceof ResponseInterface) {
                // perhaps view content
                if ($received instanceof ViewContentInterface) {
                    try {
                        $view = $this->container->get('view');
                        $view->addChild($received);
                        
                        $response->getBody()->write(
                            $view->render('layout')
                        );

                        // update the received with the proper response                            
                        $received = clone $response;
                    } catch (ServiceNotFoundException $e) {
                        throw new \LogicException(sprintf(
                            'You returned a %s object, but there is no view ' .
                            'defined.', get_class($received)));
                    }
                }

                if (!$received instanceof ResponseInterface) {
                    $message = 'Controllers must return a ResponseInterface ' 
                             . 'object, %s given.';
                    if ($received === null) {
                        $message .= 'Please add a return statement in your ' 
                                 . 'controller.';
                    }

                    throw new \LogicException(sprintf($message, gettype($received))); 
                }
            }

            $response = $received;
        } catch (ResourceNotFoundException $e) { // no route match
            $response = $response->withStatus(404);
        } catch (HandlerNotFoundException $e) { // handler not defined
            $response = $response->withStatus(404);
        } catch (\Exception $e) {
            $response = $response->withStatus(500);
        }

        $this->respond($response);        
    }

    protected function respond(ResponseInterface $response)
    {
        $emitter = new SapiEmitter();
        $emitter->emit($response);
    }
}
