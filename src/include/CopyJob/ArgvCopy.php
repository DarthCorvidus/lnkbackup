<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class ArgvCopy implements ArgvModel {
	private $positional = array();
	private $named = array();
	private $positionalNames = array();
	public function __construct() {
		$this->named["max"] = UserValue::asOptional();
		$this->named["max"]->setValidate(new ValidateInteger());
		
		$this->named["from"] = UserValue::asOptional();
		$this->named["from"]->setValidate(new ValidateDate(ValidateDate::ISO));
		
		$this->named["to"] = UserValue::asOptional();
		$this->named["to"]->setValidate(new ValidateDate(ValidateDate::ISO));
		
		$this->positional[0] = UserValue::asMandatory();
		$this->positional[0]->setValidate(new ValidatePath(ValidatePath::BOTH));
		
		$this->positional[1] = UserValue::asMandatory();
		$this->positional[1]->setValidate(new ValidatePath(ValidatePath::BOTH));
		$this->positionalNames = array("source", "target");
	}

	public function getArgNames(): array {
		return array_keys($this->named);
	}

	public function getNamedArg(string $name): UserValue {
		return $this->named[$name];
	}

	public function getPositionalCount(): int {
		return count($this->positional);
	}
	
	public function getPositionalArg(int $i): UserValue {
		return $this->positional[$i];
	}
	
	public function getPositionalName(int $i): string {
		return $this->positionalNames[$i];
	}
	
	public function getBoolean(): array {
		return array("run", "daily", "weekly", "monthly", "yearly", "progress", "silent");
	}
}