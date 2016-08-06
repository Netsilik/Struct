<?php
namespace TestNamespace;

use Simple;
use stdClass;
use DateTime;
use Annotated;
use Netsilik\Lib\Struct;

class SymmetricEncryptionTest extends \PHPUnit_Framework_TestCase
{
	
	public function testCannotAssignValueToPrivateProperty()
	{
		$this->expectException('PHPUnit_Framework_Error');
		$this->expectExceptionMessage('Cannot assign value to private property Simple::$a');
		
		$struct = new Simple();
		
		$struct->a = 'test';
	}
	
	public function testAssignValueToUnlistedProtectedProperty()
	{
		$this->expectException('PHPUnit_Framework_Error');
		$this->expectExceptionMessage('Cannot assign value to protected property Simple::$b');
		
		$struct = new Simple();
		
		$struct->b = 'test';
	}
	
	public function testAssignValueToListedProtectedProperty()
	{
		$struct = new Simple();
		
		$struct->c = 'test';
		
		$this->assertEquals('test', $struct->c);
	}
	
	public function testAssignValueToPublicProperty()
	{
		$struct = new Simple();
		
		$struct->d = 'test';
		
		$this->assertEquals('test', $struct->d);
	}
	
	public function testGetNonExistingProperty()
	{
		$this->expectException('PHPUnit_Framework_Error');
		$this->expectExceptionMessage('Cannot access non-existing property Simple::$DOES_NOT_EXIST');
		
		$struct = new Simple();
		
		$a = $struct->DOES_NOT_EXIST;
	}
	
	public function testSetNonExistingProperty()
	{
		$this->expectException('PHPUnit_Framework_Error');
		$this->expectExceptionMessage('Cannot access non-existing property Simple::$DOES_NOT_EXIST');
		
		$struct = new Simple();
		
		$struct->DOES_NOT_EXIST = 'test';
	}
	
	public function testCustomPublicSetter()
	{
		$struct = new Simple();
		
		$struct->e = 'test';
		
		$this->assertEquals('customPublic', $struct->e);
	}
	
	public function testCustomProtectedSetter()
	{
		$struct = new Simple();
		
		$struct->f = 'test';
		
		$this->assertEquals('customProtected', $struct->f);
	}
	
	public function testAnnotatedSetterFailScalar()
	{
		$this->expectException('PHPUnit_Framework_Error');
		$this->expectExceptionMessage('Protected property Annotated::$a needs to be of type string, integer given');
		
		$struct = new Annotated();
		
		$struct->a = 123;
		
	}
	
	public function testAnnotatedIncorrectlyNamedVariable()
	{
		$this->expectException('PHPUnit_Framework_Error_Notice');
		$this->expectExceptionMessage('Specified variable name \'wrong\' in DocBlock does not match actual variable name \'b\'');
		
		$struct = new Annotated();
		
		$struct->b = 123;
		
	}
	
	public function testAnnotatedMultiUnnamedVariableScalarFail()
	{
		
		$this->expectException('PHPUnit_Framework_Error');
		$this->expectExceptionMessage('Protected property Annotated::$c needs to be of type string, integer given');
		
		$struct = new Annotated();
		
		$struct->c = 123;
	}
	
	public function testAnnotatedMultiVariableUnnamed()
	{
		$struct = new Annotated();
		
		$struct->f = 123;
		
		$this->assertEquals(123, $struct->f);
	}
	
	public function testAnnotatedNoVariableName()
	{
		$this->expectException('PHPUnit_Framework_Error');
		$this->expectExceptionMessage('Protected property Annotated::$g needs to be of type string, integer given');
		
		$struct = new Annotated();
		
		$struct->g = 123;
	}
	
	public function testAnnotatedMultiVariableScalarFail()
	{
		
		$this->expectException('PHPUnit_Framework_Error');
		$this->expectExceptionMessage('Protected property Annotated::$h needs to be of type integer, string given');
		
		$struct = new Annotated();
		
		$struct->h = 'test';
	}
	
	public function testAnnotatedObjectFail()
	{
		
		$this->expectException('PHPUnit_Framework_Error');
		$this->expectExceptionMessage('Protected property Annotated::$j needs to be of type DateTime, stdClass given');
		
		$struct = new Annotated();
		
		$struct->j = new stdClass();
	}
	
	public function testAnnotatedMultiTypeFail()
	{
		$this->expectException('PHPUnit_Framework_Error');
		$this->expectExceptionMessage('Protected property Annotated::$k needs to be of type string|array, integer given');
		
		$struct = new Annotated();
		
		$struct->k = 123;
	}
	
	public function testAnnotatedMultiTypeOk()
	{
		$struct = new Annotated();
		
		$struct->k = array(123);
		
		$this->assertEquals(array(123), $struct->k);
	}
	
	public function testAnnotatedMultiTypeNullOk()
	{
		$struct = new Annotated();
		
		$struct->l = null;
		
		$this->assertNull($struct->l);
		
		$struct->l = new DateTime();
		
		$this->assertInstanceOf(DateTime::class, $struct->l);
	}
	
	public function testAnnotatedDefaultNullFail()
	{
		
		$this->expectException('PHPUnit_Framework_Error');
		$this->expectExceptionMessage('Protected property Annotated::$m needs to be of type DateTime, NULL given');
		
		$struct = new Annotated();
		
		$struct->m = null;
	}
}
