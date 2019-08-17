<?php

namespace Netsilik\Lib;

/**
 * @package       Lib
 * @copyright (c) 2011-2018 Netsilik (http://netsilik.nl)
 * @license       MIT
 */

use Iterator;
use ReflectionClass;
use ReflectionProperty;

abstract class Struct implements Iterator
{
	/**
	 * @var array $_settableProperties The list of protected class properties that can be assigned a variable through the magic __set method
	 */
	protected $_settableProperties = [];
	
	/**
	 * @var int $_iteratorPosition The index or the Iterator interface implementation
	 */
	private $_iteratorPosition = 0;
	
	/**
	 * @var bool $_iteratorInitiated Flag that indicates if the iterator init step has been completed
	 */
	private $_iteratorInitiated = false;
	
	/**
	 * @var array $_properties ...
	 */
	private $_properties = [];
	
	/**
	 * @var ReflectionClass $_rc The instance of ReflectionClass($this)
	 */
	private $_rc;
	
	/**
	 * Set the value of a protected class property, when it is listed in the $_settableProperties array
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return $this
	 */
	public function __call(string $name, array $arguments) : Struct
	{
		if ('set' !== substr($name, 0, 3)) {
			if (method_exists($this, $name)) {
				trigger_error('Call to ' . ($this->_isProtectedMethod($name) ? 'protected' : 'private') . ' method ' . get_class($this) . '::' . $name . '() from outside of class context', E_USER_ERROR);
			}
			trigger_error('Call to undefined method ' . get_class($this) . '::' . $name . '()', E_USER_ERROR);
		}
		
		$property = lcfirst(substr($name, 3));
		
		if ($property === '_settableProperties' || !property_exists($this, $property)) {
			trigger_error('Call to undefined method ' . get_class($this) . '::' . $name . '()', E_USER_ERROR);
		}
		
		if (1 !== ($argCount = count($arguments))) {
			trigger_error('Too few arguments to function ' . get_class($this) . '::' . $name . '(), ' . $argCount . ' passed in and exactly 1 expected', E_USER_ERROR);
		}
		
		$this->__set($property, $arguments[0]);
		
		return $this;
	}
	
	/**
	 * Get the value of a protected class property
	 *
	 * @param string $name The name of the variable to get the value from
	 */
	public function __get($name)
	{
		if ($name === '_settableProperties' || !property_exists($this, $name)) {
			trigger_error('Cannot access non-existing property ' . get_class($this) . '::$' . $name, E_USER_ERROR);
		}
		
		return $this->$name;
	}
	
	/**
	 * @param string $name
	 *
	 * @return bool True if a settable property exists and considered set as by isset(), false otherwise
	 */
	public function __isset(string $name)
	{
		if ($name === '_settableProperties' || !property_exists($this, $name)) {
			return false;
		}
		
		return isset($this->$name);
	}
	
	/**
	 * Set the value of a protected class property, when it is listed in the $_settableProperties array
	 *
	 * @param string $name  The name of the variable
	 * @param mixed  $value The value to assign to this class property
	 */
	public function __set($name, $value)
	{
		if ($name === '_settableProperties' || !property_exists($this, $name)) {
			trigger_error('Cannot access non-existing property ' . get_class($this) . '::$' . $name, E_USER_ERROR);
		}
		
		// Detect if custom setter is defined
		$setter = 'set' . ucfirst($name);
		if (method_exists($this, $setter) && in_array($setter, get_class_methods($this))) {
			call_user_func([$this, $setter], $value);
		} else {
			if (!$this->_isProtectedProperty($name) || !in_array($name, $this->_settableProperties)) {
				trigger_error('Cannot assign value to ' . ($this->_isProtectedProperty($name) ? 'protected' : 'private') . ' property ' . get_class($this) . '::$' . $name, E_USER_ERROR);
			}
			
			$expectedType = $this->_getExpectedType($name);
			$actualType   = $this->_getValueType($value);
			if (!$this->_isExpectedType($expectedType, $actualType)) {
				trigger_error('Protected property ' . get_class($this) . '::$' . $name . ' needs to be of type ' . $expectedType . ', ' . $actualType . ' given', E_USER_ERROR);
			}
			
			$this->$name = $value;
		}
	}
	
	/**
	 * Set the value for an existing settable property to null
	 * @param string $name
	 */
	public function __unset(string $name)
	{
		if ($name !== '_settableProperties' && property_exists($this, $name) && $this->_isProtectedProperty($name) && in_array($name, $this->_settableProperties)) {
			$this->$name = null;
		}
	}
	
	/**
	 * Get the expected type for the specified class property, based on the docblock
	 *
	 * @param string $propertyName The name of the parameter
	 *
	 * @return string The type expected
	 */
	private function _getExpectedType($propertyName)
	{
		$docBlock = $this->_getReflectionClass()->getProperty($propertyName)->getDocComment();
		
		$matched = null;
		if (false === preg_match_all('/\*\s+@var\s+([a-z_][a-z0-9_|\\\\]*)(?:\s+\$([a-z_][a-z0-9_]*))?/i', $docBlock, $matched, PREG_SET_ORDER)) {
			trigger_error('Could not interpret DocBlock', E_USER_ERROR);
		}
		
		$type = 'unspecified';
		if (1 == count($matched)) {
			$match = $matched[0];
			if (isset($match[2]) && ($match[2] <> $propertyName)) {
				trigger_error("Specified variable name '{$match[2]}' in DocBlock does not match actual variable name '{$propertyName}'", E_USER_NOTICE);
			}
			$type = $match[1];
		} else {
			foreach ($matched as $match) {
				if (isset($match[2]) && $match[2] == $propertyName) {
					$type = $match[1];
					break;
				}
			}
		}
		
		return ($type <> 'int' ? $type : 'integer');
	}
	
	/**
	 * Get the type of the value given
	 *
	 * @param string $value The value to check the type of
	 *
	 * @return string The type
	 */
	private function _getValueType($value)
	{
		if (null === $value || is_scalar($value) || is_array($value) || is_resource($value)) {
			return (gettype($value) <> 'double' ? gettype($value) : 'float');
		}
		
		if (is_object($value)) {
			return get_class($value);
		}
		
		return 'unknown';
	}
	
	/**
	 * Check to see if a value is of the correct type for the given class property
	 *
	 * @param string $expectedType The type the property is expected be
	 * @param string $actualType   The type of the value
	 *
	 * @return bool True if the value is of the expected type, the type is 'unspecified', false otherwise
	 */
	private function _isExpectedType($expectedType, $actualType)
	{
		if ('unspecified' == $expectedType) {
			return true;
		}
		
		foreach (explode('|', $expectedType) as $expectedType) {
			if ('mixed' == $expectedType || ('null' == $expectedType && 'NULL' == $actualType) || $actualType == $expectedType) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Check to see if a class property is either public or protected
	 *
	 * @param string $property The name of the property
	 *
	 * @return bool True if the property visibility is protected, false otherwise
	 */
	private function _isProtectedProperty($property)
	{
		return array_key_exists($property, get_object_vars($this));
	}
	
	/**
	 * Check to see if a class method is either public or protected
	 *
	 * @param string $method The name of the method
	 *
	 * @return bool True if the method visibility is protected, false otherwise
	 */
	private function _isProtectedMethod($method)
	{
		return $this->_getReflectionClass()->getMethod($method)->isProtected();
	}
	
	/**
	 * Get the ReflectionClass($this) instance
	 *
	 * @return \ReflectionClass
	 */
	private function _getReflectionClass()
	{
		if (!($this->_rc instanceof ReflectionClass)) {
			$this->_rc = new ReflectionClass($this);
		}
		
		return $this->_rc;
	}
	
	/**
	 * Initiate the iterator information using reflection
	 */
	private function _initIterator()
	{
		$this->_iteratorInitiated = true;
		
		$properties = $this->_getReflectionClass()->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
		foreach ($properties as $property) {
			if ('_settableProperties' === $property->getName()) {
				continue;
			}
			$this->_properties[] = $property;
		}
	}
	
	/**
	 * Return the value of the current Iterator element
	 *
	 * @return mixed
	 */
	public function current()
	{
		if (!$this->_iteratorInitiated) {
			$this->_initIterator();
		}
		
		$propertyName = $this->_properties[ $this->_iteratorPosition ]->getName();
		
		return $this->$propertyName;
	}
	
	/**
	 * Return the key of the current Iterator element
	 *
	 * @return string
	 */
	public function key() : string
	{
		if (!$this->_iteratorInitiated) {
			$this->_initIterator();
		}
		
		return $this->_properties[ $this->_iteratorPosition ]->getName();
	}
	
	/**
	 * Move the to the next Iterator element
	 *
	 * @return void
	 */
	public function next()
	{
		if (!$this->_iteratorInitiated) {
			$this->_initIterator();
		}
		
		$this->_iteratorPosition++;
	}
	
	/**
	 * Rewind the to the first Iterator element
	 *
	 * @return void
	 */
	public function rewind()
	{
		if (!$this->_iteratorInitiated) {
			$this->_initIterator();
		}
		
		$this->_iteratorPosition = 0;
	}
	
	/**
	 * Checks if current Iterator element exists
	 *
	 * @return bool
	 */
	public function valid() : bool
	{
		if (!$this->_iteratorInitiated) {
			$this->_initIterator();
		}
		
		return ($this->_iteratorPosition < count($this->_properties));
	}
}
