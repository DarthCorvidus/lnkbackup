<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class ArgvUsage implements ArgvModel {
	private $args = array();
	function __construct() {
		$from = new ArgDate("from");
		$this->args[] = $from;
		$date = new Date();
		$to = new ArgDate("to", $date->getDate("Y-m-d"));
		$this->args[] = $to;
		$subdir = new ArgString("subdir");
		$this->args[] = $subdir;
	}

	public function getArgModel(int $arg): \ArgModel {
		return $this->args[$arg];
	}

	public function getBoolean(): array {
		return array("daily", "weekly", "monthly", "yearly");
	}

	public function getParamCount(): int {
		return count($this->args);
	}

}
