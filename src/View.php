<?php

namespace Prime;

use Prime\ViewInterface;

/**
 * Default view rendering engine, uses PHP as a template engine.
 */
class View implements ViewInterface
{
    /**
     * Path where the templates are stored
     * 
     * @var string
     */
    protected $templatesPath;

    /**
     * Extension for templates files
     * 
     * @var string
     */
    protected $fileExtension;

    /**
     * View variables
     * 
     * @var array
     */
    protected $data = array();

    /**
     * Instantiate the view
     * 
     * @param string $templatesPath 
     */
    public function __construct($templatesPath = null, $fileExtension = 'phtml')
    {
        if ($templatesPath) {
            $this->setTemplatesPath($templatesPath);
        }

        $this->setFileExtension($fileExtension);
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
        if (array_key_exists($key, $data)) {
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

    public function setTemplatesPath($path)
    {
        if (!is_string($path) || !file_exists($path)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid templates path provided [%s]. Please make sure the ' .
                'path is string and exists.', $path));
        } 

        $this->templatesPath = $path;
    }

    public function getTemplatesPath()
    {
        return $this->templatesPath;
    }

    public function setFileExtension($fileExtension)
    {
        if ($fileExtension && !is_string($fileExtension)) {
            throw \InvalidArgumentException(sprintf(
                'Invalid file extension provided [%s]', $fileExtension));
        }

        $this->fileExtension = $fileExtension;
    }

    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    public function render($template, $data = array())
    {
        if ($data && is_array($data)) {
            $this->setVars($data);
        }

        $file = $this->resolveTemplate();
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

        return $content;
    }

    protected function resolveTemplate($template)
    {        
        if (!$template || !is_string($template)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid template name provided [%s]', $template));            
        }

        if (preg_match('#\.\.[\\\/]#', $template)) {
            throw new \DomainException(sprintf(
                'Template name includes parent directory traversal ' .
                '("../", "..\\" notation) [%s]', $template));
        }

        $templatesPath = $this->getTemplatesPath();
        if (!$templatesPath) {
            throw new \RuntimeException('Templates path is not defined');            
        }

        $templatesPath = $this->removeExtraTrailing($templatesPath);

        $path = $templatesPath . DIRECTORY_SEPARATOR 
              . $template . '.' . $this->getFileExtension();

        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf(
                'Template file not found [%s]', $path));
        }

        return $path;
    }

    /**
     * If the path ends with a slash, remove it
     * 
     * @param  string $path
     * @return string
     */
    protected function removeExtraTrailing($path)
    {
        if (substr($path, -1) == '/') {
            $path = substr($path, 0, -1);
        }

        return $path;
    }
}
