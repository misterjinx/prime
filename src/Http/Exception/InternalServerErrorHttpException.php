<?php

namespace Prime\Http\Exception;

use Prime\Http\Exception\HttpException;

class InternalServerErrorHttpException extends HttpException
{
    public function __construct($message = 'Internal server error', $code = 0, \Exception $previous = null)
    {
        parent::__construct(500, $message, $code, $previous);
    }
}