<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Interface for return value of Argv::getArgModel.
 *
 * ArgvModel allows to define parameters along with conversion and validation.
 * Examples may be to validate that a parameter like --date= contains a valid
 * date.
 */
interface ArgModel {
	/**
	 * „Long“ name of a parameter, expecting two hyphens (ie. --date).
	 */
	function getLongName():string;
	/**
	 * returns a default value if it has one. If, for instance, --date is used,
	 * it could be set to „now“.
	 */
	function getDefault(): string;
	/**
	 * Should return whether an instance of ArgModel has a default value or not.
	 */
	function hasDefault():bool;
	/**
	 * Should return whether an instance of ArgModel has an instance of Validate
	 * defined.
	 */
	function hasValidate():bool;
	/**
	 * Should return an instance of Validate if there is one; Argv will then
	 * call it on the value given by a parameter. getValidate won't be called if
	 * hasValidate returns false.
	 */
	function getValidate():Validate;
	/**
	 * Should return whether an instance of ArgModel has an instance of Convert
	 * defined.
	 */
	function hasConvert():bool;
	/**
	 * Should return an instance of Convert if there is one; Argv will then use
	 * it to convert any input.
	 */
	function getConvert():Convert;
	function isMandatory():bool;
}
