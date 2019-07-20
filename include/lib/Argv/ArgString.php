<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ArgString
 *
 * @author hm
 */
class ArgString implements ArgModel {
	private $name;
	private $default;
	private $mandatory = false;
	private $validate;
	private $convert;
	public function __construct(string $name, $default="") {
		$this->name = $name;
		$this->default = $default;
	}

	public function setMandatory(bool $mandatory) {
		$this->mandatory = $mandatory;
	}
	
	public function setValidate(Validate $validate) {
		$this->validate = $validate;
	}
	
	public function setConvert(Convert $convert) {
		$this->convert = $convert;
	}
	
	public function getConvert(): Convert {
		return $this->convert;
	}

	public function getDefault(): string {
		return $this->default;
	}

	public function getLongName(): string {
		return $this->name;
	}

	public function getShortName(): string {
		
	}

	public function getValidate(): Validate {
		return $this->validate;
	}

	public function hasConvert(): bool {
		return $this->convert!=NULL;
	}

	public function hasDefault(): bool {
		return $this->default!=="";
	}

	public function hasValidate(): bool {
		return $this->validate!=NULL;
	}

	public function isMandatory(): bool {
		return $this->mandatory;
	}

}
