<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
class ArgFile extends ArgString implements Validate {
	private $exist = TRUE;
	private $type = NULL;
	private $readable = TRUE;
	const TYPE_FILE = 1;
	const TYPE_DIRECTORY = 2;
	function __construct() {
		parent::__construct();
		parent::setValidate($this);
	}

	public function setType(int $type) {
		$this->type = $type;
	}
	
	public function validate($validee) {
		if($this->exist==TRUE && !file_exists($validee)) {
			throw new ValidateException($validee." does not exist.");
		}
		
		if($this->type==self::TYPE_DIRECTORY && !is_dir($validee)) {
			throw new ValidateException($validee." is no directory");
		}
		
		if($this->type==self::TYPE_FILE && !is_file($validee)) {
			throw new ValidateException($validee." is no file");
		}
	}
}
