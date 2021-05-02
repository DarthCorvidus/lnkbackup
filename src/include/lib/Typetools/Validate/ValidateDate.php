<?php
/**
 * @copyright (c) 2007, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license LGPL
 */
/**
 * ValidateDate checks whether a date conforms to either US (07/04/1776), German
 * (03.10.1990) or ISO (1970-01-01). It also performs some plausibility checks
 * and won't accept invalid dates such as 2007-02-31.
 */
class ValidateDate implements Validate {
	private $type;
	const GERMAN = 1;
	const US = 2;
	const ISO = 3;
	function __construct($type) {
		Assert::isEnum($type, "type", array(1, 2, 3));
		$this->type = $type;
	}
	
	function split($date) {
		switch($this->type) {
			case self::GERMAN:
				if (!preg_match("/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/", $date)) {
					throw new ValidateException("Well formed german date required (dd.mm.yyyy)");
				}
				$split = explode(".", $date);
				return array("year"=>$split[2], "month"=>$split[1], "day"=>$split[0]);
			break;
			case self::US:
				if (!preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $date)) {
					throw new ValidateException("Well formed USA date required (mm/dd/yyyy)");
				}
				$split = explode("/", $date);
				return array("year"=>$split[2], "month"=>$split[0], "day"=>$split[1]);
			break;
			case self::ISO:
				if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)) {
					throw new ValidateException("Well-formed ISO 8601 date required (yyyy-mm-dd)");
				}
				$split = explode("-", $date);
				return array("year"=>$split[0], "month"=>$split[1], "day"=>$split[2]);
			break;
		}
	}
	
	private function date($year, $month, $day) {
		if($month<=0 or $month > 12) {
			throw new ValidateException("month (".$month.") is out of range");
		}
		$ts = strtotime($year."-".$month."-01");
		$days = date("t", $ts);
		if($day<=0 or $day > $days) {
			throw new ValidateException("day is out of range (".$day.")");
		}
	} 
	
	function validate($date) {
		$array = $this->split($date);
		$this->date($array["year"], $array["month"], $array["day"]);
	}
}
?>