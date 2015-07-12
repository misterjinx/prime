<?php

require_once __DIR__ . '/../../vendor/autoload.php';

class Controller
{
    protected $request;
    protected $response;
    protected $dispatcher;

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function init()
    {
        
    }

    public function beforeAction()
    {
        
    }

    public function afterAction()
    {

    }

    public function forward($handler = array())
    {
        $this->dispatcher->forward($handler, $this->request, $this->response);
    }
}

class IndexController extends Controller
{
    public function indexAction()
    {
        echo 'index';
        $this->forward(array('action' => 'forward'));
    }

    public function forwardAction()
    {
        echo 'forward';
    }
}

class ErrorController extends Controller
{
    public function errorAction()
    {
        echo 'error';
    }
}


$app = new \Prime\Application(new \Prime\Container());
$app->run();
