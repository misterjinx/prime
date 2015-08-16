<?php

namespace Prime\Http\Exception;

interface HttpExceptionInterface
{
    public function getStatusCode();
    public function setStatusCode($code);
}
