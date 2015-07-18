<?php

namespace Prime\Controller;

use Psr\Http\Message\ServerRequestInterface;

interface ResolverInterface
{
    public function getController(ServerRequestInterface $request);
}
