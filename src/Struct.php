<?php

namespace Netsilik\Lib;

/**
 * @package       Lib
 * @copyright (c) 2011-2018 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */
abstract class Struct
{
	/**
	 * @var array $_settableProperties The list of protected class properties that can be assigned a variable through the magic __set method
	 */
	protected $_settableProperties = [];
	
	/**
	 * Get the value of a protected class property
	 *
	 * @param string $name The name of the variable to get the value from
	 */
	public function __get($name)
	{
		if (!property_exists($this, $name) || $name === '_settableProperties') {
			trigger_error('Cannot access non-existing property ' . get_class($this) . '::$' . $name, E_USER_ERROR);
		}
		
		return $this->$name;
	}
	
	/**
	 * Set the value of a protected class property, when it is listed in the $_settableProperties array
	 *
	 * @param string $name  The name of the variable
	 * @param mixed  $value The value to assign to this class property
	 */
	public function __set($name, $value)
	{
		if (!property_exists($this, $name) || $name === '_settableProperties') {
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
	 * Get the expected type for the specified class property, based on the docblock
	 *
	 * @param string $propertyName The name of the parameter
	 *
	 * @return string The type expected
	 */
	private function _getExpectedType($propertyName)
	{
		$rc       = new \ReflectionClass($this);
		$docBlock = $rc->getProperty($propertyName)->getDocComment();
		
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
}
