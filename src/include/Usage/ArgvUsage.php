<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class ArgvUsage implements ArgvModel {
	private $args = array();
	private $positional = array();
	private $positionalNames = array();
	function __construct() {
		$this->args["from"] = UserValue::asOptional();
		
		$date = new JulianDate();
		$to = UserValue::asOptional();
		$to->setValue($date->getIsodate());
		
		$this->args["to"] = $to;
		$this->args["subdir"] = UserValue::asOptional();
		$this->positional[0] = UserValue::asMandatory();
		$this->positional[0]->setValidate(new ValidatePath(ValidatePath::DIR));
		
		$this->positionalNames[] = "backup";
	}
	public function getBoolean(): array {
		return array("daily", "weekly", "monthly", "yearly");
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
