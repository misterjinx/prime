<?php

namespace Prime\Tests\View;

use Prime\View\ViewContent;

class ViewContentTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $vc = new ViewContent('foo');
        $this->assertInstanceOf('Prime\View\ViewContent', $vc);
    }

    public function testConstructorSetsProvidedTemplate()
    {
        $vc = new ViewContent('foo');
        $this->assertSame('foo', $vc->getTemplate());        
    }

    public function testContructorSetsProvidedData()
    {
        $vc = new ViewContent('foo', array('foo' => 'bar'));
        $this->assertSame(array('foo' => 'bar'), $vc->getVars());
    }

    public function testContructorSetsProvidedCaptureTo()
    {
        $vc = new ViewContent('foo', array(), 'bar');
        $this->assertSame('bar', $vc->getCaptureTo());
    }

    public function testConstructorDoesNotModifyDefaultCaptureToIfNotProvided()
    {
        $vc = new ViewContent('foo');
        $this->assertSame('content', $vc->getCaptureTo());
    }

    public function testSetTemplate()
    {
        $vc = new ViewContent('foo');
        $vc->setTemplate('bar');

        $this->assertSame('bar', $vc->getTemplate());
    }

    public function testGetTemplate()
    {
        $vc = new ViewContent('baz');
        $this->assertSame('baz', $vc->getTemplate());
    }

    public function testSetAndGetVariable()
    {
        $vc = new ViewContent('foo');
        $vc->set('bar', 'baz');

        $this->assertSame('baz', $vc->get('bar'));
    }

    public function testGetUndefinedVariableReturnsDefaultValue()
    {
        $vc = new ViewContent('foo');
        $this->assertSame(null, $vc->get('baz'));
        $this->assertSame(false, $vc->get('qux', false));
    }

    public function testSetAndGetVars()
    {
        $vc = new ViewContent('foo');
        $vc->setVars(array('bar' => 'baz', 'baz' => 'qux'));

        $this->assertSame(array('bar' => 'baz', 'baz' => 'qux'), $vc->getVars());
    }

    public function testClearVars()
    {
        $vc = new ViewContent('foo', array('bar' => 'baz'));        
        $this->assertSame(array('bar' => 'baz'), $vc->getVars());

        $vc->clearVars();
        $this->assertSame(array(), $vc->getVars());
    }

    public function testSetAndGetCaptureTo()
    {
        $vc = new ViewContent('foo');
        $vc->setCaptureTo('bar');

        $this->assertSame('bar', $vc->getCaptureTo());
    }
}
