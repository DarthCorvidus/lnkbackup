<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class ArgvBackup implements ArgvModel {
	private $args;
	private $positional;
	private $positionalNames;
	function __construct() {
		$this->args["force-date"] = new UserValue();
		$this->args["force-date"]->setValidate(new ValidateDate(ValidateDate::ISO));
		$this->args["force-date"]->setValue(date("Y-m-d"));
		$this->positional[0] = new UserValue();
		$this->positional[0]->setValidate(new ValidatePath(ValidatePath::BOTH));
		
		$this->positionalNames[] = "config";
	}
	public function getBoolean(): array {
		return array("silent");
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
