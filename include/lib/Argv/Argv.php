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
	private $long;
	private $argv;
	private $argvResult = array();
	private $boolean = array();
	private $longParams = array();
	function __construct(array $argv, ArgvModel $model) {
		$this->model = $model;
		$this->argv = array_slice($argv, 1);
		for($i = 0; $i<$this->model->getParamCount();$i++) {
			$this->longParams[] = $this->model->getArgModel($i)->getLongName();
		}
		foreach($this->argv as $key => $value) {
			if(substr($value, 0, 2)=="--") {
				$this->extractAll($value);
			}
		}
		$this->extract();
	}
	
	private function extractAll($value) {
		$explode = explode("=", $value, 2);
		$name = substr($explode[0], 2);
		//checks whether a parameter is defined.
		if(!in_array($name, $this->longParams) && !in_array($name, $this->model->getBoolean())) {
			throw new InvalidArgumentException("Unknown parameter --".$name);
		}
		//checks if a boolean parameter is without a value.
		if(count($explode)==2 && in_array($name, $this->model->getBoolean())) {
			throw new InvalidArgumentException("Boolean parameter --".$name." must not have value");
		}
		//checks whether a non-boolean parameter has a value.
		if(count($explode)==1 && in_array($name, $this->longParams)) {
			throw new InvalidArgumentException("Parameter --".$name." expects value");
		}
		
		if(count($explode)==2) {
			$this->long[$name] = $explode[1];
		} else {
			$this->long[$name] = true;
		}
	}
	
	private function extractValue(ArgModel $arg) {
		if(!isset($this->long[$arg->getLongName()]) && $arg->isMandatory()) {
			throw new Exception("--".$arg->getLongName()." is mandatory");
		}
		if(!isset($this->long[$arg->getLongName()]) && $arg->hasDefault()) {
			$this->argvResult[$arg->getLongName()] = $arg->getDefault();
			return;
		}
		if(!isset($this->long[$arg->getLongName()])) {
			return;
		}
		if($arg->hasValidate()) {
			$arg->getValidate()->validate($this->long[$arg->getLongName()]);
		}
		
		if($arg->hasConvert()) {
			$this->argvResult[$arg->getLongName()] = $arg->getConvert()->convert($this->long[$arg->getLongName()]);
			return;
		}
		$this->argvResult[$arg->getLongName()] = $this->long[$arg->getLongName()];
	}
	
	private function extract() {
		for($i=0;$i<$this->model->getParamCount();$i++) {
			$arg = $this->model->getArgModel($i);
			$this->extractValue($arg);
		}
		foreach($this->model->getBoolean() as $value) {
			if(!isset($this->long[$value])) {
				$this->boolean[$value] = false;
				continue;
			}
			$this->boolean[$value] = true;
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
		return isset($this->argvResult[$key]);
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
	return $this->argvResult[$key];
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
		if(!isset($this->boolean[$key])) {
			throw new Exception("boolean argument ".$key." is not defined");
		}
	return $this->boolean[$key];
	}
}