<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Designed to get the length of the longest string of a set of strings.
 */
class LongestString {
	private $longest = 0;
	private $charset;
	/**
	 * 
	 * @param string $charset Character Set as expected by mb_strlen.
	 */
	function __construct(string $charset = "UTF-8") {
		$this->charset = $charset;
	}
	
	/**
	 * 
	 * @param string $string
	 */
	function addString(string $string) {
		$len = mb_strlen($string, $this->charset);
		if($len>$this->longest) {
			$this->longest = $len;
		}
	}
	
	/**
	 * 
	 * @param array $array
	 */
	function addArray(array $array) {
		foreach($array as $key => $value) {
			$this->addString($value);
		}
	}
	
	/**
	 * 
	 * @return int
	 */
	function getLongest(): int {
		return $this->longest;
	}
	
	/**
	 * set stored length to zero
	 */
	function reset() {
		$this->longest = 0;
	}
}