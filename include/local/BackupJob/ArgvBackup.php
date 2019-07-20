<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class ArgvBackup implements ArgvModel {
	private $args;
	function __construct() {
		$forceDate = new ArgDate("force-date", date("Y-m-d"));
		$this->args[] = $forceDate;
	}
	
	public function getArgModel(int $arg): ArgModel {
		return $this->args[$arg];
	}

	public function getParamCount(): int {
		return count($this->args);
	}

	public function getBoolean(): array {
		return array();
	}

}
