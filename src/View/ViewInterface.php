<?php

namespace Prime\View;

interface ViewInterface
{
    /**
     * Render the specified template
     * 
     * @param  string $template 
     * @param  array  $data     
     * @return string           
     */
    public function render($template, $data = array());
}
