<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class ArgvRebuild implements ArgvModel {
	private $args;
	public function __construct() {
		$max = new ArgString("max", -1);
		$max->setValidate(new ValidateInteger());
		$this->args[] = $max;
	}

	public function getArgModel(int $arg): \ArgModel {
		return $this->args[$arg];
	}

	public function getBoolean(): array {
		return array("weekly", "monthly", "yearly", "run");
	}

	public function getParamCount(): int {
		return count($this->args);
	}

}