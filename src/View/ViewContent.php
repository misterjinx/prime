<?php

namespace Prime\View;

use Prime\View\ViewContentInterface;

class ViewContent implements ViewContentInterface
{
    /**
     * Variable name to assign the rendered content to parent view
     * 
     * @var string
     */
    protected $captureTo = 'content';

    /**
     * Template name
     * 
     * @var string
     */
    protected $template;

    /**
     * View content variables
     * 
     * @var array
     */
    protected $data = array();

    public function __construct($template, $data = array(), $captureTo = null)
    {
        $this->setTemplate($template);
        $this->setVars($data);
        
        if ($captureTo) {
            $this->setCaptureTo($captureTo);
        }
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set a new variable
     * 
     * @param string $name
     * @param string $value
     * @return \Prime\View
     */
    public function set($name, $value)
    {
        $this->data[(string) $name] = $value;
        return $this;
    }

    /**
     * Get a variable if is set
     * 
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        $key = (string) $name;
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return null;
    }

    /**
     * Set multiple variables at once 
     * 
     * @param array $data
     */
    public function setVars($data = array())
    {
        foreach ($data as $name => $value) {
            $this->set($name, $value);
        }
    }

    /**
     * Get all assigned variables
     * 
     * @return array
     */
    public function getVars()
    {
        return $this->data;
    }

    /**
     * Clear all assigned variables
     * 
     * @return void
     */
    public function clearVars()
    {
        $this->data = array();
    }

    /**
     * Set variable name to assign current rendered content to parent view
     * 
     * @param string $name 
     */
    public function setCaptureTo($name)
    {
        $this->captureTo = (string) $name;
    }

    /**
     * Get name of the variable to capture content to parent view
     * 
     * @return string
     */
    public function getCaptureTo()
    {
        return $this->captureTo;
    }
}
