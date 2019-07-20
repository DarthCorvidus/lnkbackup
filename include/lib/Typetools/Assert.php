<?php
/**
 * @author Claus-Christoph Kuethe
 * @copyright Claus-Christoph Kuethe 2007
 * @license http://www.gnu.org/licenses/lgpl.html
 * @filesource
 * @package Typetools
 */
/**
 * Assert
 * 
 * Assert was originally intended to mitigate PHPs incomplete type hinting. It
 * may not be as necessary anymore as it had been, yet it offers a few more
 * validations and is therefore still useful in some cases.
 */
class Assert {
	/** is Integer
	 * 
	 * assert that the given value is an integer
	 * @param mixed $value
	 * @param integer $param which parameter, to correctly formulate the exception
	 */
	static function isInteger($value, $param) {
		if(!is_int($value)) {
			throw new Exception(self::genericError("integer", $param));	
		}
	}
	static function isInt($value, $param) {
		if(!is_int($value)) {
			throw new Exception(self::genericError("integer", $param));	
		}
	}
	/**
	 * is Float
	 * 
	 * assert that the given value is a float
	 */
	static function isFloat($value, $param) {
		if(!is_int($value)) {
			throw new Exception(self::genericError("float", $param));	
		}
	}

	/**
	 * is Numeric
	 * 
	 * assert that the given value is a numeric value (float or integer)
	 */
	static function isNumeric($value, $param) {
		if(!is_numeric($value)) {
			throw new Exception(self::genericError("numeric", $param));	
		}
	}
	/**
	 * is String
	 * 
	 * assert that the given value is a string
	 */
	static function isString($value, $param) {
		if(!is_string($value)) {
			throw new Exception(self::genericError("string", $param));	
		}
	}
	/**
	 * is Scalar
	 * 
	 * assert that the given value is a scalar
	 */
	static function isScalar($value, $param) {
		if(!is_scalar($value)) {
			throw new Exception(self::genericError("scalar", $param));	
		}
	}
	
	/**
	 * is boolean
	 * 
	 * assert that the given value is boolean
	 */
	static function isBoolean($value, $param) {
		if(!is_bool($value)) {
			throw new Exception(self::genericError("boolean", $param));	
		}
	}
	
	/**
	 * is positive
	 * 
	 * assert that the given value is a positive number
	 */
	static function isPositive($value, $param) {
		Assert::isNumeric($value, 1);
		if($value<0) {
			throw new Exception(self::genericError("positive value", $param));
		}
	}

	/**
	 * is negative
	 * 
	 * assert that the given value is a negative number
	 */
	static function isNegative($value, $param) {
		Assert::isNumeric($value, 1);
		if($value>0) {
			throw new Exception(self::genericError("negative value", $param));
		}
	}

	/**
	 * is semantic
	 * 
	 * assert that the given value contains a semantic value (from the
	 * perspective of  a human): string, float, integer (a stricter variant of
	 * isScalar)
	 */
	static function isSemantic($value, $param) {
		if(!(is_numeric($value) or is_string($value))) {
			throw new Exception(self::genericError("semantic value [float, int or string]", $param));
		}
	}
	static function isCallable($value, $param) {
		if(!is_callable($value)) {
			throw new Exception(self::genericError("valid callback", $param));
		}
		
	}
	
	static function isTypename($value, $param) {
		Assert::isString($value, 1);
		$types = array("array", "bool", "float", "int", "null", "object", "resource", "string");
		if(!in_array($value, $types)) {
			throw new Exception("parameter ".$param." needs to name of one of PHP's types (".implode($types, ", ").")");
		}
	}
	
	static function FileExists($value, $param) {
		Assert::isString($value, 1);
		if(!file_exists($value)) {
			throw self::getException(__METHOD__, $param);
		}
		
	}
	static function isFile($value, $param) {
		Assert::isString($value, 1);
		if(!file_exists($value)) {
			throw self::getException(__METHOD__, $param);	
		}
		if(is_dir($value)) {
			throw self::getException(__METHOD__, $param);
		}	
	}
	
	static function isDirectory($value, $param) {
		Assert::isString($value, 1);
		Assert::FileExists($value, 1);
		if(!is_dir($value)) {
			throw self::getException(__METHOD__, $param);
		}	
	}
	
	static function isReadable($value, $param) {
		if(!is_readable($value)) {
			throw self::getException(__METHOD__, $param);
		}	
	}

	static function isExecutable($value, $param) {
		if(!is_executable($value)) {
			throw self::getException(__METHOD__, $param);
		}	
	}
	
	static function getException($assertion, $param) {
		return new Exception("parameter ".$param." failed to pass ".$assertion);
	}
	
	static function genericError($type, $param) {
		if(is_string($param)) {
			$param = '$'.$param;
		}
	return "parameter ".$param." needs to be ".$type;
	}
	/**
	 * isEnum
	 * 
	 * Asserts that the parameter is one out of a set.
	 *
	 * @param mixed $value
	 * @param mixed $param
	 * @param array $array the set
	 * @param array $constants if the allowed values exists as constant, they can be listed here to ease debugging 
	 */
	static function isEnum($value, $param, array $array, array $constants = NULL) {
		if(!in_array($value, $array)) {
			if(!is_array($constants)) {
				throw new Exception("parameter ".$param." needs to be element of array (".implode(",", $array).")");
			}
		throw new Exception("parameter ".$param." needs to be one of the following constants:".implode(", ", $constants));
		}
	}
	/**
	 * checks whether a value is a class constant of a given class
	 *
	 * @param mixed $value
	 * @param mixed $param parameter id or name
	 * @param mixed $class object or classname
	 */
	static function isClassConstant($value, $param, $class) {
		if(is_string($param)) {
			$param = '$'.$param;
		}
		if(is_object($class)) {
			$class = get_class($class);
		}
		$reflection = new ReflectionClass($class);
		if(!in_array($value, $reflection->getConstants())) {
			$keys = array_keys($reflection->getConstants());
			throw new ErrorException("parameter ".$param." needs to be one of ".$class."::{".implode(", ", $keys)."}");
		}
	}
	/**
	 * Although type hinting is the best choice to check for interfaces, classnames
	 * passed as string can be asserted to have certain interfaces;
	 * @param type $class Name of a class or instance
	 * @param type $interface Name of the interface
	 * @throws ErrorException
	 */
	static function hasInterface($class, $interface) {
		$implements = class_implements($class);
		if(!in_array($interface, $implements)) {
			throw new ErrorException("Class ".$class." needs to implement ".$interface);
		}
	}
}

?>
