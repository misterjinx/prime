<?php

namespace Demo\Controller;

use Demo\Controller\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        return new \Prime\View\ViewContent('index', array(
            'hello' => 'world'
        ));
    }

    public function forwardAction()
    {
        
    }
}
