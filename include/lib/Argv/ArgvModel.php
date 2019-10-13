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
	public function getArgNames(): array;
	
	public function getNamedArg(string $name): ArgModel;

	public function getPositionalCount(): int;
	
	public function getPositionalArg(int $i): ArgModel;
	
	public function getPositionalName(int $i): string;
	
	/**
	 * return an array of pure boolean parameters without a value, which will
	 * evaluate to true if set.
	 */
	function getBoolean(): array;
}
