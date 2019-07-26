<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class ArgvTrim implements ArgvModel {
	private $args = array();
	function __construct() {
		$max = new ArgString("max", 0);
		$max->setValidate(new ValidateInteger());
		$this->args[] = $max;
		$from = new ArgDate("from");
		$this->args[] = $from;
		$date = new Date();
		$to = new ArgDate("to", $date->getDate("Y-m-d"));
		$this->args[] = $to;
		$days = new ArgString("days", -1);
		$days->setValidate(new ValidateInteger());
		$this->args[] = $days;
		$weeks = new ArgString("weeks", -1);
		$weeks->setValidate(new ValidateInteger());
		$this->args[] = $weeks;
		$months = new ArgString("months", -1);
		$months->setValidate(new ValidateInteger());
		$this->args[] = $months;
		$years = new ArgString("years", -1);
		$years->setValidate(new ValidateInteger());
		$this->args[] = $years;
		$subdir = new ArgString("subdir");
		$this->args[] = $subdir;
	}

	public function getArgModel(int $arg): \ArgModel {
		return $this->args[$arg];
	}

	public function getBoolean(): array {
		return array("execute");
	}

	public function getParamCount(): int {
		return count($this->args);
	}

}
