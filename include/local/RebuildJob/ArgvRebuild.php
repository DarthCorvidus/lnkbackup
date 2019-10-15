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
		$this->args["max"] = new ArgString();
		$this->args["max"]->setValidate(new ValidateInteger());
		$this->positional[] = new ArgFile();
		$this->positionalNames[] = "backup";
	}

	public function getBoolean(): array {
		return array("weekly", "monthly", "yearly", "run");
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