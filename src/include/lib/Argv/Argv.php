<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Argv extracts parameters from $argv, as defined in an ArgvModel. As of yet,
 * it only handles long parameters, such as --param, both with or without a
 * value.<br />
 * Argv does basic plausibility checks; it throws Exceptions if boolean
 * parameters are used with a value and vice versa or if unknown parameters are
 * used.
 */
class Argv {
	private $model;
	private $argv;
	private $availablePositional = array();
	private $availableNamed = array();
	private $availableBoolean = array();
	function __construct(array $argv, ArgvModel $model) {
		$this->model = $model;
		$this->argv = array_slice($argv, 1);
		$this->getAvailable();
		$this->sanityCheck();
		$this->validate();
	}
	
	private function getAvailable() {
		foreach($this->argv as $key => $value) {
			if(substr($value, 0, 2)=="--") {
				$this->getAvailableNamedOrBoolean($value);
				continue;
			}
			$this->availablePositional[] = $value;
		}
		$this->getDefaults();
	}
	
	private function getDefaults() {
		foreach ($this->model->getArgNames() as $name) {
			if(isset($this->availableNamed[$name])) {
				continue;
			}
			if(!$this->model->getNamedArg($name)->hasDefault()) {
				continue;
			}
			$this->availableNamed[$name] = $this->model->getNamedArg($name)->getDefault();
		}
	}
	
	private function getAvailableNamedOrBoolean(string $value) {
		$exp = explode("=", $value, 2);
		if(count($exp)==1) {
			$this->availableBoolean[] = substr($value, 2);
			return;
		}
		$this->availableNamed[substr($exp[0], 2)] = $exp[1];
	}
	
	private function sanityCheck() {
		$this->booleanSanity();
		$this->positionalSanity();
		$this->namedSanity();
	}
	
	private function booleanSanity() {
		$defined = $this->model->getBoolean();
		foreach($this->availableBoolean as $value) {
			if(!in_array($value, $defined)) {
				throw new ArgvException("unknown boolean parameter --".$value);
			}
		}
	}
	
	private function positionalSanity() {
		$defined = $this->model->getPositionalCount();
		for($i=0;$i<$defined;$i++) {
			if(!isset($this->availablePositional[$i])) {
				throw new ArgvException("Argument ".($i+1)." (".$this->model->getPositionalName($i).") missing");
			}
		}
		if(count($this->availablePositional)>$defined) {
			throw new ArgvException("Argument ".($defined+1)." not expected");
		}
	}
	
	private function namedSanity() {
		$defined = $this->model->getArgNames();
		foreach($defined as $name) {
			$arg = $this->model->getNamedArg($name);
			if(!isset($this->availableNamed[$name]) && $arg->isMandatory()) {
				throw new ArgvException("mandatory argument --".$name." missing");
			}
		}
		foreach (array_keys($this->availableNamed) as $value) {
			if(!in_array($value, $defined)) {
				throw new ArgvException("argument --".$value." not expected");
			}
		}
	}
	
	private function validate() {
		foreach($this->availablePositional as $pos => $value) {
			$arg = $this->model->getPositionalArg($pos);
			if(!$arg->hasValidate()) {
				continue;
			}
			try {
				$arg->getValidate()->validate($value);
			} catch (ValidateException $e) {
				throw new ArgvException("argument ".($pos+1)." (".$this->model->getPositionalName($pos)."): ".$e->getMessage());
			}
		}

		foreach($this->availableNamed as $name => $value) {
			$arg = $this->model->getNamedArg($name);
			if(!$arg->hasValidate()) {
				continue;
			}
			try {
				$arg->getValidate()->validate($value);
			} catch (ValidateException $e) {
				throw new ArgvException("--".$name.": ".$e->getMessage());
			}
			
		}

	}

	private function convert() {
		foreach($this->availablePositional as $pos => $value) {
			$arg = $this->model->getPositionalArg($pos);
			if(!$arg->hasConvert()) {
				continue;
			}
			$this->availablePositional[$pos] = $arg->getConvert()->convert($this->availablePositional[$pos]);
		}

		foreach($this->availableNamed as $name => $value) {
			$arg = $this->model->getNamedArg($name);
			if(!$arg->hasConvert()) {
				continue;
			}
			$this->availableNamed[$name] = $arg->getConvert()->convert($this->availableNamed[$name]);
		}
	}

	/**
	 * Checks whether a certain parameter is available or not. A parameter is
	 * available if it was used by the calling user or if it's ArgModel has a
	 * default value.
	 * @param type $key
	 * @return bool
	 */
	function hasValue($key):bool {
		return isset($this->availableNamed[$key]);
	}
	/**
	 * Gets the value of a specific parameter. Note that parameters which are
	 * not available will throw an exception, so hasValue should be called
	 * beforehand if a value is not mandatory and has no default value.
	 * 
	 * @param type $key
	 * @return string
	 * @throws Exception
	 */
	function getValue($key): string {
		if(!$this->hasValue($key)) {
			throw new Exception("argument value ".$key." doesn't exist");
		}
	return $this->availableNamed[$key];
	}
	
	function hasPositional(int $pos) {
		return isset($this->availablePositional[$pos]);
	}
	
	function getPositional(int $pos) {
		if(!$this->hasPositional($pos)) {
			throw new Exception("positional argument ".$pos." doesn't exist");
		}
	return $this->availablePositional[$pos];
	}
	
	/**
	 * getBoolean will evaluate to true if a parameter was set (like --force),
	 * and to false, if it was not set. It will throw an Exception if it was
	 * not defined in an instance of ArgvModel.
	 * @param type $key
	 * @return bool
	 * @throws Exception
	 */
	function getBoolean($key):bool {
		if(!in_array($key, $this->model->getBoolean())) {
			throw new Exception("boolean argument ".$key." is not defined");
		}
		return in_array($key, $this->availableBoolean);
	}
}