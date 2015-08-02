<?php

namespace Prime;

use Prime\View\ViewInterface;
use Prime\View\ViewContent;
use Prime\View\Engine\EngineInterface;

/**
 * Default view rendering engine, uses PHP as a template engine.
 */
class View implements ViewInterface
{    
    /**
     * View variables
     * 
     * @var array
     */
    protected $data = array();

    /**
     * Child views
     * 
     * @var array
     */
    protected $children = array();

    /**
     * Whether existing children were rendered or not
     * 
     * @var boolean
     */
    protected $renderedChildren = false;

    /**
     * What type of engine to use to render the views
     * 
     * @var EngineInterface
     */
    protected $engine;

    /**
     * Default layout template to render if needed
     * 
     * @var string
     */
    protected $layout = 'layout/layout';    

    /**
     * Instantiate the view
     * 
     * @param EngineInterface|null $engine 
     */
    public function __construct($engine = null)
    {
        if ($engine) {
            $this->setEngine($engine);
        }
    }

    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Set a variable by setting it directly as a property
     *  
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Get a variable by accesing it directly as a property
     * 
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Check whether a variable is set
     * 
     * @param  string  $name 
     * @return boolean       
     */
    public function __isset($name)
    {
        $key = (string) $name;
        return isset($this->data[$key]);
    }

    /**
     * Unset a variable 
     * 
     * @param string $name
     */
    public function __unset($name)
    {
        if (!$this->__isset($name)) {
            return;            
        }

        $key = (string) $name;
        unset($this->data[$key]);
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
     * Add a child content to current view
     * 
     * @param ViewContent $child     
     * @param string        $captureTo 
     */
    public function addChild(ViewContent $child)
    {     
        $this->children[] = $child;
        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function hasChildren()
    {
        return !empty($this->children);
    }

    public function render($template, $data = array(), $renderChildren = true)
    {
        $engine = $this->getEngine();
        if (!$engine instanceof EngineInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid or no view rendering engine, %s provided', $engine));
        }

        if ($data && is_array($data)) {
            $this->setVars($data);
        }

        if ($this->hasChildren() && $renderChildren && !$this->renderedChildren) {
            $this->renderChildren();
        }

        return $engine->render($template, $this->getVars());
    }

    public function renderChildren()
    {
        foreach ($this->children as $child) {
            $content = $this->render($child->getTemplate(), $child->getVars(), false);
            $this->set($child->getCaptureTo(), $content);
        }

        $this->renderedChildren = true;
    }

    public function useLayout()
    {
        return (bool) $this->layout;
    }

    public function disableLayout()
    {
        $this->setLayoutTemplate(false);
    }

    public function setLayoutTemplate($template = null)
    {
        if ($template !== null) {
            $this->layout = $template;
        }
    }

    public function getLayoutTemplate()
    {
        return $this->layout;
    }
}
