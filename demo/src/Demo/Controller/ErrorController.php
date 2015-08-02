<?php

namespace Demo\Controller;

use Demo\Controller\Controller;

class ErrorController extends Controller
{
    public function errorAction()
    {
        return new \Prime\View\ViewContent('error', array(
            'error' => $this->request->getAttribute('exception')->getMessage()
        ));
    }
}
