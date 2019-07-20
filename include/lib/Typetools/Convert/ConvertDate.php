<?php
/**
 * @copyright Claus-Christoph Kuethe 2007
 * @license http://www.gnu.org/licenses/lgpl.html
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 */

/**
 * ConvertDate
 * 
 * Conversion class to convert dates between different formats (German, US, ISO)
 */
class ConvertDate implements Convert {
	const GERMAN = ValidateDate::GERMAN;
	const US = ValidateDate::US;
	const ISO = ValidateDate::ISO;
	private $from;
	private $to;
	function __construct($from, $to) {
		Assert::isEnum($from, "from", array(self::GERMAN, self::US, self::ISO));
		Assert::isEnum($to, "to", array(self::GERMAN, self::US, self::ISO));
		$this->from = $from;
		$this->to = $to;
	}
	
	function convert($convertee) {
		if($this->to==$this->from) {
			return $convertee;
		}
		$convert = new ValidateDate($this->from);
		$date = $convert->split($convertee);
		switch($this->to) {
			case self::GERMAN:
				return $date["day"].".".$date["month"].".".$date["year"];
			break;
			case self::US:
				return $date["month"]."/".$date["day"]."/".$date["year"];
			break;
			case self::ISO:
				return $date["year"]."-".$date["month"]."-".$date["day"];
			break;
		}
	}
}
?>