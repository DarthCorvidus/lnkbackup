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
		$this->named["max"] = new ArgString();
		$this->named["max"]->setValidate(new ValidateInteger());
		$this->named["from"] = new ArgDate();
		$this->named["to"] = new ArgDate();
		$this->positional[0] = new ArgFile();
		$this->positional[0]->setType(ArgFile::TYPE_DIRECTORY);
		$this->positional[1] = new ArgFile();
		$this->positional[1]->setType(ArgFile::TYPE_DIRECTORY);
		$this->positionalNames = array("source", "target");
	}

	public function getArgNames(): array {
		return array_keys($this->named);
	}

	public function getNamedArg(string $name): \ArgModel {
		return $this->named[$name];
	}

	public function getPositionalCount(): int {
		return count($this->positional);
	}
	
	public function getPositionalArg(int $i): ArgModel {
		return $this->positional[$i];
	}
	
	public function getPositionalName(int $i): string {
		return $this->positionalNames[$i];
	}
	
	public function getBoolean(): array {
		return array("run", "daily", "weekly", "monthly", "yearly", "progress");
	}
}