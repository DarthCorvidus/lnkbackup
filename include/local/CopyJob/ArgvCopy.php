<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class ArgvCopy implements ArgvModel {
	private $args = array();
	public function __construct() {
		$max = new ArgString("max", -1);
		$max->setValidate(new ValidateInteger());
		$this->args[] = $max;
		$from = new ArgDate("from");
		$this->args[] = $from;
		$to = new ArgDate("to");
		$this->args[] = $to;
		
	}

	public function getArgModel(int $arg): \ArgModel {
		return $this->args[$arg];
	}

	public function getBoolean(): array {
		return array("run", "daily", "weekly", "monthly", "yearly", "progress");
	}

	public function getParamCount(): int {
		return count($this->args);
	}

}