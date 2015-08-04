<?php

namespace Prime\EventManager;

use Prime\EventManager\Event;

class ResponseEvent extends Event
{
    protected $request;
    protected $response;

    public function __construct($response, $request)
    {
        $this->setResponse($response);    
        $this->setRequest($request);
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
