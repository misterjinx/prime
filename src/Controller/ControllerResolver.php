<?php

namespace Prime\Controller;

use Prime\Controller\ResolverInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControllerResolver implements ResolverInterface
{
    public function getController(ServerRequestInterface $request)
    {
        $controller = $request->getAttribute('controller');
        if (!$controller) {
            return false;
        }

        if (is_array($controller)) {
            return $controller;
        }

        if (is_object($controller)) {
            if (method_exists($controller, '__invoke')) {
                return $controller;
            }

            throw new \InvalidArgumentException(sprintf(
                'Controller [%s] for URI [%s] is not callable.', 
                get_class($controller), $request->getUri()->getPath()));
        }

        return $controller;
    }
}
