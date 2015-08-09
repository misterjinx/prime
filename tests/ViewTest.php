<?php

namespace Prime\Tests;

use Prime\View;
use Prime\View\ViewContent;
use Prime\View\Engine\PhpEngine;
use Prime\View\Resolver\TemplatePathResolver;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    protected $view;

    public function setUp()
    {
        $this->resolver = new TemplatePathResolver(__DIR__ . '/View/_templates');
        $this->engine = new PhpEngine($this->resolver);
        $this->view = new View($this->engine);
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('Prime\View', $this->view);
    }

    public function testConstructorSetsEngineIfGiven()
    {
        $this->assertTrue($this->view->getEngine() !== null);
        $this->assertInstanceOf('Prime\View\Engine\EngineInterface', $this->view->getEngine());
    }

    public function testConstructorDoesNotSetEngineIfNotGiven()
    {
        $view = new View();
        $this->assertTrue($view->getEngine() === null);
    }

    public function testDefaultLayoutIsEnabled()
    {
        $this->assertTrue($this->view->useLayout());
    }

    public function testDefaultTemplateLayout()
    {
        $this->assertSame('layout/layout', $this->view->getLayoutTemplate());
    }

    public function testSetLayoutTemplate()
    {
        $this->view->setLayoutTemplate('custom-layout');
        $this->assertSame('custom-layout', $this->view->getLayoutTemplate());
    }

    public function testSetLayoutTemplateWorksOnlyIfNotNull()
    {
        $this->view->setLayoutTemplate('foo');
        $this->assertSame('foo', $this->view->getLayoutTemplate());

        $this->view->setLayoutTemplate();
        $this->assertSame('foo', $this->view->getLayoutTemplate());
    }

    public function testDisableLayout()
    {
        $this->assertTrue($this->view->useLayout());

        $this->view->disableLayout();
        $this->assertFalse($this->view->useLayout());
    }

    public function testSetVariable()
    {
        $this->view->clearVars();

        $this->view->set('foo', 'bar');
        $this->assertSame('bar', $this->view->get('foo'));
    }

    public function testSetVariableOverwritesPreviouslySetVariableWithTheSameName()
    {
        $this->view->set('abc', 'cba');
        $this->view->set('abc', 'abc');

        $this->assertSame('abc', $this->view->get('abc'));
    }

    public function testSetVars()
    {
        $this->view->clearVars();

        $this->view->setVars(array('var1' => 1, 'var2' => 2));
        
        $this->assertTrue(isset($this->view->var1));
        $this->assertSame(1, $this->view->var1);

        $this->assertTrue(isset($this->view->var2));
        $this->assertSame(2, $this->view->var2);
    }   
    
    public function testSetVarsMergesWithAndOverwritesPreviouslySetVariables()
    {
        $this->view->clearVars();

        $this->view->set('foo', 'bar');
        $this->view->set('baz', 'qux');

        $this->view->setVars(array('fu' => 'bar', 'baz' => 'quux'));

        $this->assertSame(
            array('foo' => 'bar', 'baz' => 'quux', 'fu' => 'bar'), 
            $this->view->getVars()
        );
    } 

    public function testSetVariableDirectlyAsProperty()
    {
        $this->view->baz = 'qux';
        $this->assertSame('qux', $this->view->get('baz'));
    }

    public function testGetVariableDirectlyAsProperty()
    {
        $this->view->my = 'var';
        $this->assertSame('var', $this->view->my);
    }

    public function testVariableIsSet()
    {
        $this->view->is = 'set';
        $this->assertTrue(isset($this->view->is));
    }

    public function testVariableUnset()
    {
        $this->view->un = 'set';
        $this->assertTrue(isset($this->view->un));

        unset($this->view->un);
        $this->assertTrue($this->view->un === null);
    }

    public function testUnsetUndefinedVariableReturnNull()
    {
        $this->view->clearVars();

        $this->view->set('foo', 'bar');
        unset($this->view->fo);

        $this->assertSame(array('foo' => 'bar'), $this->view->getVars());
    }

    public function testGetVariableReturnsNullWhenVariableIsUndefined()
    {
        $this->view->clearVars();

        $this->assertSame(null, $this->view->get('undefined'));
    }

    public function testSetMultipleVariablesAtOnce()
    {
        $this->view->clearVars();

        $vars = array('foo' => 'bar', 'baz' => 123);
        $this->view->setVars($vars);

        $this->assertSame($vars, $this->view->getVars());
    }

    public function testClearVars()
    {
        $this->view->set('foo', 'bar');
        $this->assertTrue(!empty($this->view->getVars()));

        $this->view->clearVars();
        $this->assertSame(array(), $this->view->getVars());
    }

    public function testDefaultThereAreNoChildren()
    {
        $this->assertSame(0, count($this->view->getChildren()));
        $this->assertFalse($this->view->hasChildren());
    }

    public function testAddChild()
    {
        $vc = new ViewContent('sample');
        $this->view->addChild($vc);

        $this->assertSame(1, count($this->view->getChildren()));
        $this->assertSame(array($vc), $this->view->getChildren());
        $this->assertTrue($this->view->hasChildren());        
    }

    public function testRender()
    {
        $this->view->clearVars();
        $this->view->clearChildren();

        $this->view->addChild(new ViewContent('sample', array('type' => 'sample')));
        $content = $this->view->render('layout');

        $this->assertSame('<div>This is a sample document</div>', $content);
    }

    public function testRenderSetsProvidedVariables()
    {
        $vars = array('one' => 'two', 'two' => 'ten');
        $this->view->render('layout', $vars);

        $this->assertTrue(isset($this->view->one));
        $this->assertSame('two', $this->view->one);

        $this->assertTrue(isset($this->view->two));
        $this->assertSame('ten', $this->view->two);
    }

    public function testRenderDoesNotRenderChildrenWhenThisIsNotWanted()
    {
        $this->view->addChild(new ViewContent('sample'));
        $content = $this->view->render('layout', array(), false);

        $this->assertSame('<div></div>', $content);
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testRenderWithNoEngineSetThrowsException()
    {
        $view = new View();
        $view->render('foo');
    }

    public function testRenderChildren()
    {
        $this->view->clearVars();
        $this->view->clearChildren();

        $child = new ViewContent('sample', array('type' => 'sample'));
        $this->view->addChild($child);

        $this->view->renderChildren();

        $captureTo = $child->getCaptureTo();
        $this->assertTrue(isset($this->view->$captureTo));
        $this->assertSame('This is a sample document', $this->view->$captureTo);
    }
}
