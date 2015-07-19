<?php

namespace Prime\View\Resolver;

use Prime\View\Resolver\ResolverInterface;

class TemplatePathResolver implements ResolverInterface
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
     * Initialize the resolver
     * 
     * @param string $templatesPath 
     * @param string $fileExtension 
     */
    public function __construct($templatesPath = null, $fileExtension = 'phtml')
    {
        if ($templatesPath) {
            $this->setTemplatesPath($templatesPath);
        }

        $this->setFileExtension($fileExtension);
    }

    public function resolve($name)
    {
        if (!$name || !is_string($name)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid template name provided [%s]', $name));            
        }

        if (preg_match('#\.\.[\\\/]#', $name)) {
            throw new \DomainException(sprintf(
                'Template name includes parent directory traversal ' .
                '("../", "..\\" notation) [%s]', $name));
        }

        $templatesPath = $this->getTemplatesPath();
        if (!$templatesPath) {
            throw new \RuntimeException('Templates path is not defined');            
        }

        $path = $templatesPath . $name . '.' . $this->getFileExtension();

        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf(
                'Template file not found [%s]', $path));
        }

        return $path;
    }

    public function setTemplatesPath($path)
    {
        if (!is_string($path) || !file_exists($path)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid templates path provided [%s]. Please make sure the ' .
                'path is string and exists.', $path));
        } 

        $this->templatesPath = $this->normalizePath($path);
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

        $this->fileExtension = ltrim($fileExtension, '.');
    }

    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    protected function normalizePath($path)
    {
        $path  = $this->removeExtraTrailing($path);
        $path .= DIRECTORY_SEPARATOR;

        return $path;
    }

    /**
     * If the path ends with a slash or forward slash, remove it
     * 
     * @param  string $path
     * @return string
     */
    protected function removeExtraTrailing($path)
    {
        // linux
        $path = rtrim($path, '/');
        // win
        $path = rtrim($path, '\\');

        return $path;
    }
}
