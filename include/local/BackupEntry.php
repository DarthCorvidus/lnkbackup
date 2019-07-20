<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class BackupEntry {
	private $date;
	private $type;
	private $path;
	const DAILY = "daily";
	const WEEKLY = "weekly";
	const MONTHLY = "monthly";
	const YEARLY = "yearly";
	function __construct($path) {
		$this->path = $path;
		$exp = explode(".", basename($path));
		$this->date = Date::fromIsodate($exp[0]);
		if(!isset($exp[1])) {
			$this->type = self::DAILY;
			return;
		}
		if(!in_array($exp[1], array(self::WEEKLY, self::MONTHLY, self::YEARLY))) {
			throw new Exception();
		}
		$this->type = $exp[1];
	}
	
	function getPeriod():string {
		return $this->type;
	}
	
	function getDate():Date {
		return $this->date;
	}
	
	function getPath(): string {
		return $this->path;
	}
	
	function getBasename(): string {
		return basename($this->path);
	}
	
	function isDaily(): bool {
		return $this->type == self::DAILY;
	}
	
	function isWeekly(): bool {
		return $this->type == self::WEEKLY;
	}
	
	function isMonthly(): bool {
		return $this->type == self::MONTHLY;
	}

	function isYearly(): bool {
		return $this->type == self::YEARLY;
	}

}