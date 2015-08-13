<?php

namespace Prime\Http\Exception;

use Prime\Http\Exception\HttpExceptionInterface;

class HttpException extends \RuntimeException implements HttpExceptionInterface
{
    protected $status;

    public function __construct($status, $message = "", $code = 0, \Exception $previous = null)
    {
        $this->status = (int) $status;

        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode()
    {
        return $this->status;
    }
}
