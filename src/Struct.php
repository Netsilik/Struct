<?php
namespace Netsilik\Lib;

/**
 * @package Lib
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license EUPL-1.1 (European Union Public Licence, v1.1)
 */
 
abstract class Struct {
	
	/**
	 * @var array $_settableProperties The list of protected class properties that can be assigned a variable through the magic __set method
	 */
	protected $_settableProperties = array();
	
	/**
	 * Get the value of a protected class property
	 * @param string $name The name of the variable to get the value from
	 */
	public function __get($name) {
		return $this->$name;
	}
	
	/**
	 * Set the value of a protected class property, when it is listed in the $_settableProperties array
	 * @param string $name The name of the variable
	 * @param mixed $value The value to assign to this class property
	 */
	public function __set($name, $value) {
		if ( ! property_exists($this, $name) || $name === '_settableProperties') {
			trigger_error('Cannot access non-existing property '.get_class($this).'::$'.$name, E_USER_ERROR);
		}
		
		// Detect if custom setter is defined
		$setter = 'set'.ucfirst($name);
		if ( method_exists($this, $setter) && in_array($setter, get_class_methods($this)) ) {			
			call_user_func(array($this, $setter), $value);
		} else {
			if ( ! in_array($name, $this->_settableProperties)) {
				trigger_error('Cannot assign value to '.($this->_isProtectedProperty($name) ? 'protected' : 'private').' property '.get_class($this).'::$'.$name, E_USER_ERROR);
			}
			if ($value !== null && ! is_scalar($value)) {
				trigger_error('Protected property '.get_class($this).'::$'.$name.' needs to be of type '.$type, E_USER_ERROR);
			}
			
			$this->$name = $value;
		}
	}
	
	/**
	 * Check to see if a class property is either public or protected
	 * @param string $property The name of the property
	 */
	private function _isProtectedProperty($property) {
		return array_key_exists($property, get_object_vars($this));
	}
}
