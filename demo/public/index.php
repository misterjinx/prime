<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$router = new \Prime\Router(true);
$router->add('home', new \Prime\Router\Route\Literal('/', array(
    'controller' => 'index'
)));

$dispatcher = new \Prime\Dispatcher\ControllerActionDispatcher(
    new \Prime\Controller\ControllerActionResolver(true, 'Demo\Controller')
);
$view = new \Prime\View(new \Prime\View\Engine\PhpEngine(
    new \Prime\View\Resolver\TemplatePathResolver(__DIR__ . '/../app/views')
));

$container = new \Prime\Container();
$container->set('router', $router);
$container->set('dispatcher', $dispatcher);
$container->set('view', $view);

$app = new \Prime\Application($container);
$app->run();
