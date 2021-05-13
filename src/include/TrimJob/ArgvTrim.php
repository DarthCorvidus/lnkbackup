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
		$this->args["max"] = new UserValue(FALSE);
		$this->args["max"]->setValidate(new ValidateInteger());
		
		$this->args["from"] = new UserValue(FALSE);
		$this->args["from"]->setValidate(new ValidateDate(ValidateDate::ISO));
		
		$this->args["to"] = new UserValue(FALSE);
		$this->args["to"]->setValidate(new ValidateDate(ValidateDate::ISO));
		$date = new JulianDate();
		$this->args["to"]->setValue($date->getFormat("Y-m-d"));
		
		$amount = new UserValue(FALSE);
		$amount->setValidate(new ValidateInteger());
		
		$this->args["days"] = clone $amount;
		$this->args["weeks"] = clone $amount;
		$this->args["months"] = clone $amount;
		$this->args["years"] = clone $amount;
		$this->args["subdir"] = new UserValue(FALSE);
		
		$this->positional[0] = new UserValue();
		$this->positional[0]->setValidate(new ValidatePath(ValidatePath::DIR));
		
		$this->positionalNames[] = "backup";
	}

	public function getBoolean(): array {
		return array("run");
	}

	public function getArgNames(): array {
		return array_keys($this->args);
	}

	public function getNamedArg(string $name): UserValue {
		return $this->args[$name];
	}

	public function getPositionalArg(int $i): UserValue {
		return $this->positional[$i];
	}

	public function getPositionalCount(): int {
		return count($this->positional);
	}

	public function getPositionalName(int $i): string {
		return $this->positionalNames[$i];
	}

}
