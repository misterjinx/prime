<?php

namespace Prime\View;

interface ViewContentInterface
{
    public function setTemplate($template);

    public function getTemplate();

    public function setVars($data = array());

    public function getVars();

    public function clearVars();

    public function setCaptureTo($name);

    public function getCaptureTo();
}
