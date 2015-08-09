<?php

namespace Prime\Tests\View\Engine;

use Prime\View\Engine\PhpEngine;
use Prime\View\Resolver\TemplatePathResolver;

class PhpEngineTest extends \PHPUnit_Framework_TestCase
{
    protected $engine;

    public function setUp()
    {
        $this->engine = new PhpEngine(new TemplatePathResolver(
            __DIR__ . '/../_templates'
        ));   
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('Prime\View\Engine\PhpEngine', $this->engine);
    }

    public function testSetVariable()
    {
        $this->engine->clearVars();

        $this->engine->set('foo', 'bar');
        $this->assertSame('bar', $this->engine->get('foo'));
    }

    public function testSetVariableOverwritesPreviouslySetVariableWithTheSameName()
    {
        $this->engine->set('abc', 'cba');
        $this->engine->set('abc', 'abc');

        $this->assertSame('abc', $this->engine->get('abc'));
    }

    public function testSetVars()
    {
        $this->engine->clearVars();

        $this->engine->setVars(array('var1' => 1, 'var2' => 2));
        
        $this->assertTrue(isset($this->engine->var1));
        $this->assertSame(1, $this->engine->var1);

        $this->assertTrue(isset($this->engine->var2));
        $this->assertSame(2, $this->engine->var2);
    }   
    
    public function testSetVarsMergesWithAndOverwritesPreviouslySetVariables()
    {
        $this->engine->clearVars();

        $this->engine->set('foo', 'bar');
        $this->engine->set('baz', 'qux');

        $this->engine->setVars(array('fu' => 'bar', 'baz' => 'quux'));

        $this->assertSame(
            array('foo' => 'bar', 'baz' => 'quux', 'fu' => 'bar'), 
            $this->engine->getVars()
        );
    } 

    public function testSetVariableDirectlyAsProperty()
    {
        $this->engine->baz = 'qux';
        $this->assertSame('qux', $this->engine->get('baz'));
    }

    public function testGetVariableDirectlyAsProperty()
    {
        $this->engine->my = 'var';
        $this->assertSame('var', $this->engine->my);
    }

    public function testVariableIsSet()
    {
        $this->engine->is = 'set';
        $this->assertTrue(isset($this->engine->is));
    }

    public function testVariableUnset()
    {
        $this->engine->un = 'set';
        $this->assertTrue(isset($this->engine->un));

        unset($this->engine->un);
        $this->assertTrue($this->engine->un === null);
    }

    public function testUnsetUndefinedVariableReturnNull()
    {
        $this->engine->clearVars();

        $this->engine->set('foo', 'bar');
        unset($this->engine->fo);

        $this->assertSame(array('foo' => 'bar'), $this->engine->getVars());
    }

    public function testGetVariableReturnsNullWhenVariableIsUndefined()
    {
        $this->engine->clearVars();

        $this->assertSame(null, $this->engine->get('undefined'));
    }

    public function testSetMultipleVariablesAtOnce()
    {
        $this->engine->clearVars();

        $vars = array('foo' => 'bar', 'baz' => 123);
        $this->engine->setVars($vars);

        $this->assertSame($vars, $this->engine->getVars());
    }

    public function testClearVars()
    {
        $this->engine->set('foo', 'bar');
        $this->assertTrue(!empty($this->engine->getVars()));

        $this->engine->clearVars();
        $this->assertSame(array(), $this->engine->getVars());
    }

    public function testRenderRendersTemplateAndSetsProvidedVariables()
    {
        $content = $this->engine->render('sample', array('type' => 'sample'));
        $this->assertSame('This is a sample document', $content);
    }

    public function testRenderClearVarsWhenIsDone()
    {
        $this->engine->set('foo', 'bar');

        $this->assertTrue(isset($this->engine->foo));
        $this->assertSame('bar', $this->engine->get('foo'));

        $this->engine->render('sample');

        $this->assertNull($this->engine->foo);
        $this->assertSame(array(), $this->engine->getVars());
    }

    /**
     * @expectedException   \Exception
     */
    public function testRenderThrowsExceptionIfExceptionCaughtWhileRenderingTemplate()
    {
        $this->engine->render('exception');
    }

    /**
     * @expectedException   \UnexpectedValueException
     * @expectedExceptionMessageRegExp #File include failed .*#
     */
    public function testRenderThrowsExceptionIfFailedToIncludeFileAndNoContent()
    {
        $this->engine->render('empty');
    }
}
