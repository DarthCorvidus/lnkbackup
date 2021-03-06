<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */

/**
 * Entry Filter
 * 
 * Match an instance of BackupEntry against a set of conditions. Conditions are
 * linked together via AND, ie every condition has to match. Undefined
 * conditions are ignored.
 */
class EntryFilter {
	private $period;
	private $periods = array();
	private $from;
	private $to;
	private $subdir;
	/**
	 * Set Subdir
	 * 
	 * BackupEntry has to have a certain subdirectory to match
	 * @param string $subdir
	 */
	function setSubdir(string $subdir) {
		$this->subdir = $subdir;
	}
	
	/**
	 * Add Period
	 * 
	 * BackupEntry has to have one or more periods to match.
	 * @param string $period daily, weekly, monthly, yearly
	 */
	function addPeriod(string $period) {
		$this->periods[] = $period;
	}
	
	/**
	 * Set Period
	 * 
	 * BackupEntry has to have specific period to match
	 * @param string $period daily, weekly, monthly, yearly
	 */
	function setPeriod(string $period){ 
		$this->period = $period;
	}
	
	/**
	 * Set From
	 * 
	 * BackupEntry has to have the same or a higher date to match.
	 * 
	 * @param JulianDate $date
	 */
	function setFrom(JulianDate $date) {
		$this->from = $date;
	}

	/**
	 * Set To
	 * 
	 * BackupEntry has to have the same or a lower date to match
	 * @param JulianDate $date
	 */
	function setTo(JulianDate $date) {
		$this->to = $date;
	}
	
	/**
	 * Match
	 * 
	 * Check if a BackupEntry matches. Evaluates to FALSE if one condition is
	 * not met. Undefined conditions won't be taken into account.
	 * @param BackupEntry $entry
	 * @return boolean
	 */
	function match(BackupEntry $entry): bool {
		if(!empty($this->periods) && !in_array($entry->getPeriod(), $this->periods)) {
			return false;
		}
		if($this->period!=NULL && $this->period!=$entry->getPeriod()) {
			return false;
		}
		if($this->from!=NULL && $entry->getDate()->toInt()<$this->from->toInt()) {
			return false;
		}
		
		if($this->to!=NULL && $entry->getDate()->toInt()>$this->to->toInt()) {
			return false;
		}
		
		if($this->subdir!=NULL && !$entry->hasSubdir($this->subdir)) {
			return false;
		}
	return true;
	}
}
