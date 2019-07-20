<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license LGPL
 */

/**
 * Description of ArgDate
 *
 * @author hm
 */
class ArgDate implements ArgModel {
	private $default;
	private $name;
	private $mandatory = false;
	public function __construct(string $name, string $default = "") {
		$this->name = $name;
		$this->default = $default;
	}
	
	public function setMandatory(bool $mandatory) {
		$this->mandatory = $mandatory;
	}
	
	public function getConvert(): Convert {
		
	}

	public function getDefault(): string {
		return $this->default;
	}

	public function getLongName(): string {
		return $this->name;
	}

	public function isMandatory(): bool {
		return $this->mandatory;
	}

	public function getShortName(): string {
		return "";
	}

	public function getValidate(): \Validate {
		return new ValidateDate(ValidateDate::ISO);
	}

	public function hasConvert(): bool {
		return false;
	}

	public function hasDefault(): bool {
		return $this->default!=="";
	}

	public function hasValidate(): bool {
		return true;
	}

}
