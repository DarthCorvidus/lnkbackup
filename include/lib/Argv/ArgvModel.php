<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Interface for model as expected by Argv.
 */
interface ArgvModel {
	/**
	 * The amount of defined parameters; valued parameters only.
	 */
	function getParamCount():int;
	/**
	 * Get a specific arg model.
	 * @param int $arg
	 */
	function getArgModel(int $arg): ArgModel;
	/**
	 * return an array of pure boolean parameters without a value, which will
	 * evaluate to true if set.
	 */
	function getBoolean(): array;
}
