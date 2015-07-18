<?php

namespace Prime\View\Engine;

use Prime\View\Engine\EngineInterface;

class PhpEngine implements EngineInterface
{
    public function render($template, $vars = array())
    {
        $content = '';

        try {
            ob_start();
            $included = include $template;
            $content = ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        if ($included === false && empty($content)) {
            throw new \UnexpectedValueException(sprintf(
                'File include failed when trying to render template [%s]',
                $template));
        }

        return $content;
    }
}
