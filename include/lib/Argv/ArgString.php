<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
class ArgString implements ArgModel {
	private $default;
	private $mandatory = false;
	private $validate;
	private $convert;
	public function __construct() {
	}

	public function setMandatory(bool $mandatory) {
		$this->mandatory = $mandatory;
	}
	
	public function setConvert(Convert $convert) {
		$this->convert = $convert;
	}

	public function hasConvert(): bool {
		return $this->convert!==NULL;
	}
	
	public function getConvert(): Convert {
		return $this->convert;
	}

	public function setDefault(string $default) {
		$this->default = $default;
	}

	public function hasDefault(): bool {
		return $this->default!==NULL;
	}
	
	public function getDefault(): string {
		return $this->default;
	}

	public function setValidate(Validate $validate) {
		$this->validate = $validate;
	}

	public function hasValidate(): bool {
		return $this->validate!=NULL;
	}

	public function getValidate(): Validate {
		return $this->validate;
	}

	public function isMandatory(): bool {
		return $this->mandatory;
	}

}
