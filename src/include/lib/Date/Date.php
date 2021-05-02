<?php
/**
 * @copyright (c) 2008, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license LGPL
 */

/**
 * Date
 * 
 * Class representing a Date.
 * 
 * NOTE: This class may seem superfluos because of PHP's DateTime. However, it
 * was written long before (around 2008) DateTime was added to PHP and has
 * served me well so far and has some functions which DateTime lacks.
 */
class Date {
	const DAY = 1;
	const WEEK = 2;
	const MONTH = 3;
	const YEAR = 4;
	const QUARTER = 5;
	private $units = array(self::DAY, self::WEEK, self::MONTH, self::QUARTER, self::YEAR);
	private $defaultUnit = self::DAY;
	private $numeric;
	/**
	* construct Date. If everything is left empty, current date is used
	*/
	function __construct(int $year=NULL, int $month=NULL, int $day=NULL) {
		if($year==NULL)	{
			$now = time();
			$day = date("d", $now);
			$month = date("m", $now);
			$year = date("Y", $now);
		}
	$this->numeric = gregoriantojd($month, $day, $year);
	}
	/**
	 * Returns the date as julian days.
	 * @return int
	 */
	function getNumeric():int {
		return $this->numeric;
	}

	static function fromIsodate(string $isodate): Date {
		$tmp = explode("-", $isodate);
		if(count($tmp)!=3) {
			throw new Exception("invalid isodate, must be YYYY-MM-DD");	
		}
		$date = new Date($tmp[0], $tmp[1], $tmp[2]);
	return $date;
	}
	
	static function fromInt($days): Date {
		$date = new Date();
		$date->numeric = $days;
	return $date;
	}
	
	private function isUnit(int $unit) {
		if(!in_array($unit, $this->units)) {
			throw new LogicException("parameter must be class unit constant");
		}
	return true;
	}
	
	/**
	* get ISO compliant date
	* 
	* returns date as ISO 8601 (YYYY-MM-DD)
	* @return string
	*/
	function getIsodate() {
		$array = cal_from_jd($this->numeric, CAL_GREGORIAN);
	return sprintf("%d-%02d-%02d", $array["year"], $array["month"], $array["day"]);
	}

	/**
	 * 
	 * @param type $datestring refer to the syntax of PHP's date function.
	 * @return string
	 */
	function getDate($datestring):string {
		$ts = strtotime($this->getIsodate());
		return date($datestring, $ts);
	}
	
	/**
	 * get quarter
	 * 
	 * return current quarter
	 * @return integer
	 */
	
	function getQuarter() {
		$quarter = ceil($this->getMonth()/3);
	return $quarter;
	}

	private function addMonth()	{
		$array = cal_from_jd($this->numeric, CAL_GREGORIAN);
		if($array["date"]=="0/0/0") {
			trigger_error("calendar value out of bounds", E_USER_ERROR);
		}
			if($array["month"]==12) {
				$this->numeric = gregoriantojd(1, $array["day"], $array["year"]+1);
			} else {
				$days = cal_days_in_month(CAL_GREGORIAN, $array["month"], $array["year"]);
					if($array["day"]==$days) {
						$ndays = cal_days_in_month(CAL_GREGORIAN, $array["month"]+1, $array["year"]);
						$this->numeric = gregoriantojd($array["month"]+1, $ndays, $array["year"]);
					} else {
						$this->numeric = gregoriantojd($array["month"]+1, $array["day"], $array["year"]);
					}
			}
	}
	
	private function subMonth() {
		$array = cal_from_jd($this->numeric, CAL_GREGORIAN);
		if($array["month"]==1) {
			$this->numeric = gregoriantojd(12, $array["day"], $array["year"]-1);
		} else {
			$days = cal_days_in_month(CAL_GREGORIAN, $array["month"], $array["year"]);
					if($array["day"]==$days) {
						$ldays = cal_days_in_month(CAL_GREGORIAN, $array["month"]-1, $array["year"]);
						$this->numeric = gregoriantojd($array["month"]-1, $ldays, $array["year"]);
					} else {
						$this->numeric = gregoriantojd($array["month"]-1, $array["day"], $array["year"]);
					}
		}
	}


	/**
	* adds one year
	*/
	private function addYear($add = 1) {
		$array = cal_from_jd($this->numeric, CAL_GREGORIAN);
		$this->numeric = gregoriantojd($array["month"], $array["day"], $array["year"]+$add);
	}

	private function subYear($sub = 1) {
		$array = cal_from_jd($this->numeric, CAL_GREGORIAN);
		// determine length of February for a given year, add amount to 337 days, which will result in a year or leap year
		$this->numeric = gregoriantojd($array["month"], $array["day"], $array["year"]-$sub);
	}
	
	
	
	/**
	* floor
	* 
	* floor sets a date to the first day of a given period, ie
	* MONTH = xxxx-xx-01, YEAR = xxxx-01-01.
	* @param integer $unit class constant denoting unit
	*/
	function floor($unit) {
		$this->isUnit($unit);
		$array = cal_from_jd($this->numeric, CAL_GREGORIAN);
		$numeric = $this->numeric;
		switch($unit) {
			case self::WEEK:
				$numeric -= $this->getDate("N")-1;
			break;
			case self::MONTH:
				$numeric = gregoriantojd($array["month"], 1, $array["year"]);
			break;
			case self::YEAR:
				$numeric = gregoriantojd(1, 1, $array["year"]);
			break;
			case self::QUARTER:
				$quarter = ceil($this->getDate("j")/3)-1;
				$temp = $this->floor(self::YEAR, TRUE);
				$temp->addUnit($quarter*3, self::MONTH);
				$numeric = $temp->getNumeric();
			break;
		break;
		}
	$this->numeric = $numeric;
	}



	/**
	* Go to end of Unit
	* 
	* The value is changed to the end of a given unit, ie 
	* MONTH = xxxx-xx-{28-31}, YEAR = xxxx-12-31.
	* @param integer $unit
	* 
	* 
	*/
	function ceil($unit) {
		$this->isUnit($unit);
		$array = cal_from_jd($this->numeric, CAL_GREGORIAN);
		$numeric = $this->numeric;
		switch($unit) {
			case self::WEEK:
				$numeric += (7-$this->getDate("N"));
			break;
			case self::MONTH:
				$days = cal_days_in_month(CAL_GREGORIAN, $array["month"], $array["year"]);
				$numeric = gregoriantojd($array["month"], $days, $array["year"]);
			break;
			case self::YEAR:
				$numeric = gregoriantojd(12, 31, $array["year"]);
			break;
			case self::QUARTER:
				$quarter = ceil($this->getMonth()/3)-1;
				$temp = $this->floor(self::YEAR, self::GIVE);
				$temp->addUnit(($quarter*3)+3, self::MONTH);
				$temp->subtractUnit(1, self::DAY);
				$numeric = $temp->getNumeric();
			break;
		}
	$this->numeric = $numeric;
	}

	/**
	* add unit(s)
	* 
	* Add will add any amount of unit. Note that when you are at the last day of a month
	* and add a month, you will end up on the last day of the next month, regardless
	* how long your current or next month is
	* @param integer $add
	* @param integer $unit
	*/
	function addUnit($add, $unit) {
		$this->isUnit($unit);
		switch($unit)
		{
			case self::DAY:
				$this->numeric += 1*$add;
			break;
			case self::WEEK:
				$this->numeric += 7*$add;
			break;
			case self::MONTH:
				for($i=0;$i<1*abs($add);$i++) {
					$this->addMonth();
				}
			break;
			case self::QUARTER:
				for($i=0;$i<3*abs($add);$i++) {
					$this->addMonth();
				}
			break;
			case self::YEAR:
				$this->addYear($add);
			break;
		$this->numeric++;
		break;
		}
	}

	/**
	* subtract
	* 
	* subtract any amount of units
	* @param integer $sub
	* @param integer $unit
	*/
	function subtractUnit($sub, $unit) {
		$this->isUnit($unit);
		switch($unit) {
			case self::DAY:
				$this->numeric -= 1*$sub;
			break;
			case self::WEEK:
				$this->numeric -= 7*$sub;
			break;
			case self::MONTH:
				for($i=0;$i<1*abs($sub);$i++) {
					$this->subMonth();
				}
			break;
			case self::QUARTER:
				for($i=0;$i<3*abs($sub);$i++) {
					$this->subMonth();
				}
			break;
			case self::YEAR:
				$this->subYear($sub);
			break;
		$this->numeric++;
		break;
		}
	}

	/**
	* returns ISO 8601 date
	* @return string
	*/
	function __toString(){
		return $this->getIsodate();
	}
}
?>