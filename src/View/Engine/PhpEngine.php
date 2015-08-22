<?php

namespace Prime\View\Engine;

use Prime\View\Engine\EngineInterface;
use Prime\View\Resolver\ResolverInterface;

class PhpEngine implements EngineInterface
{
    /**
     * View content variables
     * 
     * @var array
     */
    protected $vars = array();

    /**
     * Resolver to use to translate the template name to storage location
     * 
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * Instantiate the rendering engine
     * 
     * @param ResolverInterface $resolver
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
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
        return isset($this->vars[$key]);
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
        unset($this->vars[$key]);
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
        $this->vars[(string) $name] = $value;
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
        if (array_key_exists($key, $this->vars)) {
            return $this->vars[$key];
        }

        return null;
    }

    /**
     * Set multiple variables at once 
     * 
     * @param array $data
     */
    public function setVars($vars = array())
    {
        foreach ($vars as $name => $value) {
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
        return $this->vars;
    }

    /**
     * Clear all assigned variables or only the ones specified
     *
     * @param array $names clear only specified variables
     * @return void
     */
    public function clearVars($names = array())
    {
        if (is_array($names) && !empty($names)) {
            // clear only specified variables
            foreach ($names as $name) {
                if (isset($this->vars[$name])) {
                    unset($this->vars[$name]);
                }
            }            
        } else {
            $this->vars = array();
        }
    }

    public function render($template, $vars = array())
    {        
        $file = $this->resolver->resolve($template);
 
        if ($vars && is_array($vars)) {
            $addedVars = array_keys($vars);
            $this->setVars($vars);
        }

        $content = '';

        try {
            ob_start();
            $included = include $file;
            $content = ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        if ($included === false && empty($content)) {
            throw new \UnexpectedValueException(sprintf(
                'File include failed when trying to render template [%s]',
                $file));
        }

        /// remove current set variables
        if (isset($addedVars)) {
            $this->clearVars($addedVars);
        } 

        return $content;
    }
}
