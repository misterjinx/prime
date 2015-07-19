<?php

require_once __DIR__ . '/../../vendor/autoload.php';

class Controller
{
    protected $request;
    protected $response;

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

    public function init()
    {
        
    }

    public function beforeAction()
    {
        
    }

    public function afterAction()
    {

    }
}

class IndexController extends Controller
{
    public function indexAction()
    {
        return new \Prime\View\ViewContent('index', array(
            'hello' => 'world'
        ));
    }

    public function forwardAction()
    {
        
    }
}

class ErrorController extends Controller
{
    public function errorAction()
    {
        
    }
}

$container = new \Prime\Container();
$container->set('view', new \Prime\View(
    new \Prime\View\Engine\PhpEngine(
        new \Prime\View\Resolver\TemplatePathResolver(__DIR__ . '/../app/views')
    )
));

$app = new \Prime\Application($container);
$app->run();
