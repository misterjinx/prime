<?php

namespace Prime;

use Prime\Container;
use Prime\Container\ContainerAware;
use Prime\Container\ContainerInterface;
use Prime\Container\Exception\ServiceNotFoundException;
use Prime\Controller\ControllerActionResolver;
use Prime\Dispatcher\ControllerActionDispatcher;
use Prime\Dispatcher\Exception\HandlerNotFoundException;
use Prime\Dispatcher\DispatcherInterface;
use Prime\EventManager;
use Prime\EventManager\EventManagerInterface;
use Prime\EventManager\ResponseEvent;
use Prime\EventManager\ResponseEventListener;
use Prime\Http\Exception\NotFoundHttpException;
use Prime\Http\Exception\InternalServerErrorHttpException;
use Prime\Http\Exception\HttpExceptionInterface;
use Prime\Router;
use Prime\Router\Route\Exception\ResourceNotFoundException;
use Prime\View\ViewInterface;
use Prime\View\ViewContentInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmitterInterface;
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

        if (!$this->container->has('events')) {
            $this->setEventManager(new EventManager());
        }

        if (!$this->container->has('emitter')) {
            $this->setEmitter(new SapiEmitter());
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

    public function setEmitter(EmitterInterface $emitter)
    {
        $this->container->set('emitter', $emitter);
    }

    public function setView(ViewInterface $view)
    {
        $this->container->set('view', $view);
    }

    public function getContainer()
    {
        return $this->container;
    } 

    /**
     * Execute the application and handle the request. If respond is false,
     * the Response will be returned instead of emitted directly.
     * 
     * @param  ServerRequestInterface|null $request 
     * @param  boolean                     $respond 
     * @return mixed
     */
    public function run(ServerRequestInterface $request = null, $respond = true)
    {        
        try {
            $response = $this->handle($request);
        } catch (\Exception $e) {
            $response = $this->handleException($e);
        }
        
        if (!$respond) {
            return $response;            
        }

        $this->respond($response);        
    }

    /**
     * Handle request
     * 
     * @param  ServerRequestInterface|null $request
     * @return Response
     */
    protected function handle(ServerRequestInterface $request = null)
    {
        if ($request !== null) {
            $this->setRequest($request);
        }

        $request = $this->container->get('request');
        $router  = $this->container->get('router');
        $events  = $this->container->get('events');

        try {
            $router->match($request);
        } catch (ResourceNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }            

        $route = $router->getMatchedRoute();
        foreach ($route->getMatches() as $param => $value) {
            $request = $request->withAttribute($param, $value);
        }

        return $this->dispatch($request);
    }      

    /**
     * Receive a request object and dispatches to the corresponding handler
     * returning the received Response.
     * 
     * @param  ServerRequestInterface $request
     * @return Response
     */
    protected function dispatch(ServerRequestInterface $request)
    {
        $response   = $this->container->get('response');
        $dispatcher = $this->container->get('dispatcher');

        if ($dispatcher instanceof ContainerAware) {
            $dispatcher->setContainer($this->container);
        }

        try {
            $received = $dispatcher->dispatch($request, $response);
        } catch (HandlerNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage()); 
        }

        if (!$received instanceof ResponseInterface) {
            // perhaps view content
            if ($received instanceof ViewContentInterface) {
                try {
                    $view = $this->container->get('view');

                    if ($view->useLayout()) {
                        $view->addChild($received);
                    
                        $response->getBody()->write($view->render(
                            $view->getLayoutTemplate()
                        ));
                    } else {
                        $response->getBody()->write($view->render(
                            $received->getTemplate(), $received->getVars()
                        ));
                    }

                    // update the received with the proper response                            
                    $received = clone $response;
                } catch (ServiceNotFoundException $e) {
                    throw new \LogicException(sprintf(
                        'You returned a %s object, but there is no view ' .
                        'defined', get_class($received)));
                }
            }

            if (!$received instanceof ResponseInterface) {
                $message = 'Controllers must return a Response ' 
                         . 'object, %s given.';
                if ($received === null) {
                    $message .= ' Please add a return statement in your ' 
                             . 'controller.';
                }

                throw new \LogicException(sprintf($message, gettype($received))); 
            }
        }

        return $received;        
    }

    protected function respond(ResponseInterface $response)
    {
        $emitter = $this->container->get('emitter');
        $emitter->emit($response);
    }

    /**
     * Handle trying to convert exception to a proper Response
     * 
     * @param  \Exception $e 
     * @return Response
     */
    protected function handleException(\Exception $e)
    {
        $e = $this->processException($e);

        $request = $this->container->get('request')
                 ->withAttribute('controller', 'error')
                 ->withAttribute('action', 'error')
                 ->withAttribute('exception', $e);

        // update request to container;                 
        $this->container->set('request', $request);

        $response = $this->dispatch($request);

        $status = 500;
        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
        }

        return $response->withStatus($status);
    }

    protected function processException(\Exception $e)
    {
        if (!$e instanceof HttpExceptionInterface) {
            $e = new InternalServerErrorHttpException($e->getMessage(), 0, $e);
        }

        return $e;
    }
}
