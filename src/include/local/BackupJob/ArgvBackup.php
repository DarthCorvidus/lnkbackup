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
		$this->args["force-date"] = new ArgDate();
		$this->args["force-date"]->setDefault(date("Y-m-d"));
		$this->positional[] = new ArgFile();
		$this->positionalNames[] = "config";
	}
	public function getBoolean(): array {
		return array("silent");
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
