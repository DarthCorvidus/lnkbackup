<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class ArgvRebuild implements ArgvModel {
	private $args;
	private $positional;
	private $positionalNames;
	public function __construct() {
		$this->args["max"] = UserValue::asOptional();
		$this->args["max"]->setValidate(new ValidateInteger());
		
		$this->positional[0] = UserValue::asMandatory();
		$this->positional[0]->setValidate(new ValidatePath(ValidatePath::BOTH));
		
		$this->positionalNames[] = "backup";
	}

	public function getBoolean(): array {
		return array("weekly", "monthly", "yearly", "run", "silent");
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