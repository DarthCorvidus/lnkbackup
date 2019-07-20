<?php
/**
 * @author Claus-Christoph Kuethe
 * @copyright Claus-Christoph Kuethe 2008
 * @license http://www.gnu.org/licenses/lgpl.html
 */
/**
 * Typecast
 * 
 * Typecast is supposed to test whether a scalar value can be cast to a certain
 * type, similar to the coercion mechanism of PHP 7 and higher.
 */
class Typecast {
	static function toInt(&$value) {
		if(is_int($value)) {
			return $value;
		}
		Assert::isScalar($value, 1);
		$string = (string)$value;
		$new = (int)$value;
		if($string!=(string)$new) {
			throw new InvalidArgumentException(self::getCommonMessage($value, "int"));
		}
		settype($value, "int");
	}
	
	static function getInt($value) {
		self::toInt($value);
	return $value;
	}
	
	static function toFloat(&$value) {
		if(is_float($value)) {
			return $value;
		}
		$string = (string)$value;
		$new = (float)$value;
		if($string!=(string)$new) {
			throw new InvalidArgumentException(self::getCommonMessage($value, "float"));
		}
		settype($value, "float");
	}
	
	static function getFloat($value) {
		self::toFloat($value);
	return $value;
	}
	
	static function toNull(&$value) {
		if($value===NULL) {
			return;
		}
		Assert::isScalar($value, 1);
		if($value==="") {
			$value = NULL;
			return;
		}
	throw new InvalidArgumentException(self::getCommonMessage($value, "NULL"));
	}
	
	static function getNull($value) {
		self::toNull($value);
	return $value;
	}
	
	static function getCommonMessage(&$value, $type) {
		if(is_string($value)) {
			$message = "(string)'".substr($value, 0, 10)."...'";
		}
		if(is_float($value)) {
			$message = "(float)".$value;
		}
		if(is_int($value)) {
			$message = "(float)".$value;
		}
		if(is_null($value)) {
			$message = "(null)";
		}
		if(is_bool($value)) {
			$message = "(bool)".var_export($value, TRUE);;
		}
		//nonscalars cannot be casted, so no further messages are necessary.
	return "cannot cast ".$message." to ".$type." without loss of information";
	}
}
?>