<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class ArgvTrim implements ArgvModel {
	private $args = array();
	private $positional = array();
	private $positionalNames = array();
	function __construct() {
		$this->args["max"] = new ArgGeneric();
		$this->args["max"]->setValidate(new ValidateInteger());
		$this->args["from"] = new ArgDate();
		$this->args["to"] = new ArgDate();
		$date = new JulianDate();
		$this->args["to"]->setDefault($date->getFormat("Y-m-d"));
		$amount = new ArgGeneric();
		$amount->setValidate(new ValidateInteger());
		$this->args["days"] = $amount;
		$this->args["weeks"] = $amount;
		$this->args["months"] = $amount;
		$this->args["years"] = $amount;
		$this->args["subdir"] = new ArgGeneric();
		$this->positional[] = new ArgFile();
		$this->positionalNames[] = "backup";
	}

	public function getBoolean(): array {
		return array("run");
	}

	public function getArgNames(): array {
		return array_keys($this->args);
	}

	public function getNamedArg(string $name): \ArgModel {
		return $this->args[$name];
	}

	public function getPositionalArg(int $i): \ArgModel {
		return $this->positional[$i];
	}

	public function getPositionalCount(): int {
		return count($this->positional);
	}

	public function getPositionalName(int $i): string {
		return $this->positionalNames[$i];
	}

}
