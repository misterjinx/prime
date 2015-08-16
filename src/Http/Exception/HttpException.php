<?php

namespace Prime\Http\Exception;

use Prime\Http\Exception\HttpExceptionInterface;

class HttpException extends \RuntimeException implements HttpExceptionInterface
{
    protected $statusCode;

    public function __construct($statusCode, $message = "", $code = 0, \Exception $previous = null)
    {
        $this->setStatusCode($statusCode);

        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($code)
    {
        $this->statusCode = (int) $code;
    }
}
