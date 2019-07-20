<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class JobConfig {
	private $target;
	private $source;
	private $exclude;
	function __construct($file) {
		$parse = parse_ini_file($file);
		$this->source = $parse["source"];
		$this->target = $parse["target"];
		if(empty($parse["exclude"])) {
			return;
		}
		if(!file_exists($parse["exclude"])) {
			throw new Exception("exclude file ".$parse["exclude"]." does not exist");
		}
		$this->exclude = $parse["exclude"];
	}
	
	function getSource():string {
		return $this->source;
	}
	
	function getTarget():string {
		return $this->target;
	}

	function hasExclude(): bool {
		return $this->exclude!=NULL;
	}
	
	function getExclude(): string {
		return $this->exclude;
	}
	
}
