<?php

namespace Prime\Http\Exception;

use Prime\Http\Exception\HttpException;

class NotFoundHttpException extends HttpException
{
    public function __construct($message = 'Not Found', $code = 0, \Exception $previous = null)
    {
        parent::__construct(404, $message, $code, $previous);
    }
}