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
	private function __construct() {
		#$parse = parse_ini_file($file);
		#$this->source = $parse["source"];
		#$this->target = $parse["target"];
		#if(empty($parse["exclude"])) {
		#	return;
		#}
		#if(!file_exists($parse["exclude"])) {
		#	throw new Exception("exclude file ".$parse["exclude"]." does not exist");
		#}
		#$this->exclude = $parse["exclude"];
	}
	
	static function fromArray(array $array): JobConfig {
		$importGeneric = new ImportGeneric();
		
		$source = UserValue::asMandatory();
		$source->setValidate(new ValidateSource());
		
		$target = UserValue::asMandatory();
		$target->setValidate(new ValidatePath(ValidatePath::DIR));
		
		$exclude = UserValue::asOptional();
		$exclude->setValidate(new ValidatePath(ValidatePath::FILE));
				
		$importGeneric->addScalar("source",  $source);
		$importGeneric->addScalar("target", $target);
		$importGeneric->addScalar("exclude", $exclude);
		$import = new Import($array, $importGeneric);
		$imported = $import->getArray();
				
		$config = new JobConfig();
		$config->source = $imported["source"];
		$config->target = $imported["target"];
		if(isset($imported["exclude"])) {
			$config->exclude = $array["exclude"];
		}
	return $config;
	}
	
	static function fromFile($filename): JobConfig {
		$array = parse_ini_file($filename);
	return self::fromArray($array);
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
