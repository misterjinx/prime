<?php

namespace Prime\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

interface DispatcherInterface
{
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response);
}
