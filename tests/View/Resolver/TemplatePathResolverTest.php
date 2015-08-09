<?php

namespace Prime\Tests\View\Resolver;

use Prime\View\Resolver\TemplatePathResolver;

class TemplatePathResolverTest extends \PHPUnit_Framework_TestCase
{
    protected $resolver;

    public function setUp()
    {
        $this->resolver = new TemplatePathResolver();
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('Prime\View\Resolver\TemplatePathResolver', $this->resolver);
    }

    public function testConstructorSetsDefaultFileExtension()
    {
        $this->assertSame('phtml', $this->resolver->getFileExtension());
    }

    public function testConstructorDoesNotSetTemplatesPathIfNotProvided()
    {
        $this->assertNull($this->resolver->getTemplatesPath());
    }

    public function testConstructorSetsTemplatesPathIfProvided()
    {
        $resolver = new TemplatePathResolver(__DIR__);
        $this->assertTrue($resolver->getTemplatesPath() !== null);
    }

    public function testSetTemplatesPathNormalizesThePath()
    {
        $path = __DIR__;
        $this->resolver->setTemplatesPath($path);

        $this->assertSame($path . '/', $this->resolver->getTemplatesPath());
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testSetTemplatesPathThrowsExceptionIfNotString()
    {
        $this->resolver->setTemplatesPath(false);
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testSetTemplatesPathThrowsExceptionIfPathDoesNotExists()
    {
        $this->resolver->setTemplatesPath('/foo/bar');
    }

    public function testSetFileExtension()
    {
        $this->resolver->setFileExtension('txt');
        $this->assertSame('txt', $this->resolver->getFileExtension());
    }

    /**
     * @expectedException   \InvalidArgumentException
     */    
    public function testSetFileExtensionNotStringThrowsException()
    {
        $this->resolver->setFileExtension(123);
    }

    /**
     * @expectedException   \InvalidArgumentException
     */    
    public function testResolveThrowsExceptionIfNameIsNotString()
    {
        $this->resolver->resolve(123);            
    }

    /**
     * @expectedException   \InvalidArgumentException
     */    
    public function testResolveThrowsExceptionIfNameIsNotTrue()
    {
        $this->resolver->resolve(false);            
    }

    /**
     * @expectedException   \DomainException
     */
    public function testResolveThrowsExceptionIfNameIncludesDirectoryTraversal()
    {
        $this->resolver->resolve('../foo/bar');
    }

    /**
     * @expectedException   \RuntimeException
     * @expectedExceptionMessage Templates path is not defined
     */
    public function testResolveThrowsExceptionIfTemplatesPathIsNotDefined()
    {
        $resolver = new TemplatePathResolver();
        $resolver->resolve('foo');
    }

    /**
     * @expectedException   \RuntimeException
     * @expectedExceptionMessageRegExp /Template file not found \[.*\]/
     */
    public function testResolveThrowsExceptionIfResolvedFileDoesNotExists()
    {
        $resolver = new TemplatePathResolver(__DIR__ . '/../_templates');
        $resolver->resolve('foo');
    }
}
