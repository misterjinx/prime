<?php

namespace Prime\View\Engine;

interface EngineInterface
{
    public function render($template, $vars = array());
}
