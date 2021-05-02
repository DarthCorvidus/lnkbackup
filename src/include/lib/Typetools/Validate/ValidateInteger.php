<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license LGPL
 */
/**
 * ValidateInteger
 * Validates whether a value is an integer or not.
 */
class ValidateInteger implements Validate {
	function validate($value) {
		try {
			Typecast::getInt($value);
		} catch (Exception $e) {
			throw new ValidateException($value." is not an integer value");
		}
	}
}
?>