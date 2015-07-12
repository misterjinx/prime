<?php

namespace Prime;

use Prime\Dispatcher\DispatcherInterface;
use Prime\Dispatcher\Exception\DispatchLimitException;
use Prime\Dispatcher\Exception\HandlerNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Dispatcher implements DispatcherInterface
{
    protected $defaultController = 'index';
    protected $defaultAction = 'index';

    protected $errorController = 'error';
    protected $errorAction = 'error';

    protected $controllerSuffix = 'Controller';
    protected $actionSuffix = 'Action';

    protected $controller;
    protected $action;
    protected $params;

    protected $finished = false;
    protected $forwarded = false;

    /**
     * How many dispatches have been made
     * 
     * @var integer
     */
    protected $numDispatches = 0;

    /**
     * How many dispatches to allow for one request
     * 
     * @var integer
     */
    protected $dispatchLimit = 32;

    /**
     * Dispatch the provided request 
     * 
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @return 
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response)
    {
        while (!$this->isDispatched()) {
            $this->numDispatches++;

            if ($this->numDispatches <= $this->dispatchLimit) {
                $controller = $this->getControllerName();
                if (!$controller) {
                    $controller = $this->getDefaultController();
                    $this->setControllerName($controller);
                }

                $action = $this->getActionName();
                if (!$action) {
                    $action = $this->getDefaultAction();
                    $this->setActionName($action);
                }

                $controllerClassName = $this->formatControllerClassName();
                $actionMethodName = $this->formatActionMethodName();

                if (class_exists($controllerClassName)) {
                    if (method_exists($controllerClassName, $actionMethodName)) {
                        $object = new $controllerClassName();

                        $object->setRequest($request);
                        $object->setResponse($response);
                        $object->setDispatcher($this);
                        
                        // perform initialisation stuff if needed
                        $object->init();

                        // run pre action logic
                        $object->beforeAction();

                        // run action                            
                        $object->$actionMethodName();

                        // run post action logic
                        $object->afterAction();

                        // done
                        $this->setDispatched();

                        // return the response
                        return $object->getResponse();
                    } else {
                        throw new HandlerNotFoundException(sprintf(
                            '%s has no action %s defined', 
                            $controllerClassName, $actionMethodName), 404);
                    }
                } else {
                    throw new HandlerNotFoundException(sprintf('%s does not exists',
                        $controllerClassName), 404);
                }
            } else {
                throw new DispatchLimitException('Dispatch limit has been reached', 500);
            }            
        }
    }

    public function dispatchError(ServerRequestInterface $request, ResponseInterface $response, $exception)
    {        
        if ($exception->getCode() === 0) {
            // most probably uncaught exception during execution, so set 500 error code
            $exception = new \RuntimeException($exception->getMessage(), 500, $exception);
        }

        $error = new \ArrayObject(array(
            'code' => $exception->getCode(),
            'exception' => $exception
        ), \ArrayObject::ARRAY_AS_PROPS);

        $this->setControllerName($this->getErrorController());
        $this->setActionName($this->getErrorAction());
        $this->setParam('error', $error);

        return $this->dispatch($request, $response->withStatus($exception->getCode()));
    }

    public function forward($handler = array(), ServerRequestInterface $request, ResponseInterface $response)
    {
        if (isset($handler['controller'])) {
            $this->setControllerName($handler['controller']);
        }

        if (isset($handler['action'])) {
            $this->setActionName($handler['action']);
        }

        $this->setDispatched(false);
        $this->setForwarded();
        $this->dispatch($request, $response);
    }

    public function isDispatched()
    {
        return $this->finished;
    }

    public function setDispatched($value = true)
    {
        $this->finished = (bool) $value;
    }

    public function setForwarded($value = true)
    {
        $this->forwarded = (bool) $value;
    }

    public function isForwarded()
    {
        return $this->forwarded;
    }

    public function setHandler($handler = array())
    {
        if (isset($handler['controller'])) {
            $this->setControllerName($handler['controller']);
        }

        if (isset($handler['action'])) {
            $this->setActionName($handler['action']);
        }

        if (isset($handler['params'])) {
            $this->setParams($handler['params']);
        }
    }

    public function setControllerName($name)
    {
        $this->controller = $name;
    }

    public function getControllerName()
    {
        return $this->controller;
    }

    public function setActionName($name)
    {
        $this->action = $name;
    }

    public function getActionName()
    {
        return $this->action;
    }

    public function getDefaultController()
    {
        return $this->defaultController;
    }

    public function setDefaultController($name)
    {
        $this->defaultController = $name;
    }

    public function getDefaultAction()
    {
        return $this->defaultAction;
    }

    public function setDefaultAction($name)
    {
        $this->defaultAction = $name;
    }

    public function setErrorController($name)
    {
        $this->errorController = $name;
    }

    public function getErrorController()
    {
        return $this->errorController;
    }

    public function getErrorAction()
    {
        return $this->errorAction;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    protected function formatControllerClassName()
    {
        $name = $this->getControllerName();

        return $this->camelize($name) . $this->getControllerClassNameSuffix();
    }

    protected function formatActionMethodName()
    {
        $name = $this->getActionName();
        
        return $this->camelize($name, false) . $this->getActionMethodSuffix();
    }

    protected function getControllerClassNameSuffix()
    {
        return $this->controllerSuffix;
    }

    protected function getActionMethodSuffix()
    {
        return $this->actionSuffix;
    }

    protected function camelize($string, $full = true)
    {
        $parts = explode('_', str_replace(array('- '), '_', $string));
        $camel = array_map('ucfirst', array_map('strtolower', $parts));
        
        $camelcase = implode('', $camel);

        return $full ? $camelcase : lcfirst($camelcase);
    }
}