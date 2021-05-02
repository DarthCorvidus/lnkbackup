<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license LGPL
 */
/**
 * Validate is supposed to implement validators for input coming from unsafe
 * sources such as $_GET/$_POST or $argv.
 */
interface Validate {
	/**
	 * pretty straight forward. validate is expected to do nothing if
	 * validation succeeds, and to throw a ValidateException if it fails.
	 *
	 * @param mixed $validee
	 * @throws ValidateException
	 */
	function validate($validee);
}
?>